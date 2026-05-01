<?php

namespace App\Services\Ecommerce;

use App\Facades\Hook;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\UserTransaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OrderRefundService
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected InventoryLedgerService $inventoryLedgerService
    ) {}

    public function preview(Order $order, array $input): array
    {
        $order->loadMissing(['items.product', 'transactions']);

        $requestedItems = collect($input['items'] ?? [])->keyBy('order_item_id');
        $includeAllRefundable = $requestedItems->isEmpty();
        $lines = [];
        $totalRefund = 0.0;

        foreach ($order->items as $item) {
            $refundedQty = (int) ($item->refunded_qty ?? 0);
            $refundableQty = max(0, (int) $item->quantity - $refundedQty);

            if ($refundableQty <= 0) {
                continue;
            }

            $requestedQty = $includeAllRefundable
                ? $refundableQty
                : (int) Arr::get($requestedItems->get($item->id), 'qty', 0);

            if ($requestedQty <= 0) {
                continue;
            }

            if ($requestedQty > $refundableQty) {
                throw new \InvalidArgumentException("Refund quantity exceeds refundable quantity for item #{$item->id}");
            }

            $unitAmount = ((float) $item->total) / max(1, (int) $item->quantity);
            $lineAmount = round($unitAmount * $requestedQty, 2);

            $lines[] = [
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->name,
                'requested_qty' => $requestedQty,
                'refundable_qty' => $refundableQty,
                'unit_amount' => round($unitAmount, 2),
                'line_amount' => $lineAmount,
                'stockable' => $item->product
                    ? ($this->inventoryLedgerService->isStockableProduct($item->product, ['order' => $order, 'order_item' => $item])
                        && (bool) $item->product->manage_stock)
                    : false,
            ];

            $totalRefund += $lineAmount;
        }

        if (empty($lines)) {
            throw new \InvalidArgumentException('No refundable items selected.');
        }

        $preview = [
            'order_id' => $order->id,
            'order_code' => $order->code,
            'currency' => $order->currency,
            'restock' => (bool) ($input['restock'] ?? false),
            'gateway_refund' => (bool) ($input['gateway_refund'] ?? false),
            'items' => $lines,
            'total_refund' => round($totalRefund, 2),
            'already_refunded_total' => (float) ($order->refunded_total ?? 0),
            'max_refundable_total' => max(0, round((float) $order->total_amount - (float) ($order->refunded_total ?? 0), 2)),
        ];

        $preview = Hook::applyFilters('order.refund.preview.result', $preview, $order, $input);
        Hook::doAction('order.refund.previewed', $order, $preview, $input);

        return $preview;
    }

    public function refund(Order $order, array $input, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($order, $input, $actorId): array {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $order->load(['items.product', 'transactions']);

            $preview = $this->preview($order, $input);
            $refundAmount = (float) $preview['total_refund'];
            $maxRefundable = (float) $preview['max_refundable_total'];

            if ($refundAmount <= 0) {
                throw new \InvalidArgumentException('Refund amount must be greater than 0.');
            }
            if ($refundAmount > $maxRefundable) {
                throw new \InvalidArgumentException('Refund amount exceeds max refundable total.');
            }

            $reason = trim((string) ($input['reason'] ?? ''));
            $gatewayRefund = (bool) ($input['gateway_refund'] ?? false);
            $restock = (bool) ($input['restock'] ?? false);

            Hook::doAction('order.refund.executing', $order, $preview, [
                'reason' => $reason,
                'gateway_refund' => $gatewayRefund,
                'restock' => $restock,
                'actor_id' => $actorId,
            ]);

            $transactionRef = null;
            $gatewayCode = $order->payment_method ?: 'manual';

            if ($gatewayRefund) {
                $successfulTxn = UserTransaction::query()
                    ->where('order_id', $order->id)
                    ->where('status', 'success')
                    ->where('amount', '>', 0)
                    ->latest('id')
                    ->first();

                if (!$successfulTxn || empty($successfulTxn->transaction_ref)) {
                    throw new \RuntimeException('No successful payment transaction found for gateway refund.');
                }

                $gatewayCode = $successfulTxn->gateway ?: $gatewayCode;
                $gateway = $this->paymentManager->getGateway($gatewayCode);
                $gatewayResult = $gateway->refund($successfulTxn->transaction_ref, $refundAmount, $reason);

                if ($gatewayResult === false) {
                    throw new \RuntimeException('Gateway refund failed.');
                }

                $transactionRef = is_string($gatewayResult)
                    ? $gatewayResult
                    : ('refund-' . $successfulTxn->transaction_ref . '-' . now()->format('YmdHis'));
            }

            $refundTxn = UserTransaction::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'gateway' => $gatewayCode,
                'transaction_ref' => $transactionRef,
                'amount' => -1 * $refundAmount,
                'status' => 'success',
                'payload' => [
                    'kind' => 'refund',
                    'reason' => $reason,
                    'restock' => $restock,
                    'gateway_refund' => $gatewayRefund,
                    'items' => $preview['items'],
                ],
            ]);

            $refundedItemCount = 0;
            foreach ($preview['items'] as $line) {
                $orderItem = $order->items->firstWhere('id', $line['order_item_id']);
                if (!$orderItem) {
                    continue;
                }

                $orderItem->refunded_qty = min(
                    (int) $orderItem->quantity,
                    (int) $orderItem->refunded_qty + (int) $line['requested_qty']
                );
                $orderItem->save();
                $refundedItemCount++;

                if ($restock && $line['stockable']) {
                    $this->inventoryLedgerService->applyMovement([
                        'product_id' => $line['product_id'],
                        'movement_type' => 'refund',
                        'direction' => 1,
                        'quantity' => (int) $line['requested_qty'],
                        'reference_type' => 'order_item',
                        'reference_id' => $orderItem->id,
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'user_id' => $actorId,
                        'reason_code' => 'refund_restock',
                        'note' => $reason ?: 'Refund restock',
                        'idempotency_key' => 'refund:' . $refundTxn->id . ':item:' . $orderItem->id,
                        'meta' => [
                            'order_code' => $order->code,
                            'refund_transaction_id' => $refundTxn->id,
                        ],
                    ]);
                }
            }

            $newRefundedTotal = round((float) ($order->refunded_total ?? 0) + $refundAmount, 2);
            $orderTotal = (float) $order->total_amount;
            $isFullRefund = $newRefundedTotal >= round($orderTotal, 2);

            $order->refunded_total = $newRefundedTotal;
            $order->refund_status = $isFullRefund ? 'full' : 'partial';
            $order->payment_status = $isFullRefund ? 'refunded' : $order->payment_status;
            $order->refund_reason = $reason ?: $order->refund_reason;
            $order->refunded_at = now();
            $order->last_refunded_at = now();
            $order->refund_transaction_ref = $transactionRef ?: $order->refund_transaction_ref;
            $order->save();

            \App\Facades\Hook::doAction('order.refund.completed', $order, [
                'transaction' => $refundTxn,
                'restock' => $restock,
                'gateway_refund' => $gatewayRefund,
                'refunded_item_count' => $refundedItemCount,
            ]);

            $summary = [
                'total_refund' => $refundAmount,
                'restock' => $restock,
                'gateway_refund' => $gatewayRefund,
                'refund_status' => $order->refund_status,
                'refunded_total' => (float) $order->refunded_total,
            ];

            $notificationContext = $this->buildNotificationContext(
                $order,
                $refundTxn,
                $summary,
                $preview,
                $input,
                $actorId
            );
            $this->dispatchNotificationHooks('completed', $order, $notificationContext);

            return [
                'order' => $order->fresh(['items', 'transactions']),
                'transaction' => $refundTxn,
                'summary' => $summary,
            ];
        });
    }

    protected function buildNotificationContext(
        Order $order,
        UserTransaction $refundTxn,
        array $summary,
        array $preview,
        array $input,
        ?int $actorId
    ): array {
        $customerName = $order->user?->name
            ?? ($order->billing_address['full_name'] ?? 'Guest');

        return [
            'event' => 'order_refund_completed',
            'template_key' => 'ORDER_REFUND_PROCESSED',
            'order_id' => $order->id,
            'order_code' => $order->code,
            'user_id' => $order->user_id,
            'user_email' => $order->user?->email ?: $order->guest_email,
            'user_name' => $customerName,
            'currency' => $order->currency,
            'reason' => (string) ($input['reason'] ?? ''),
            'actor_id' => $actorId,
            'transaction_ref' => $refundTxn->transaction_ref,
            'refund_transaction_id' => $refundTxn->id,
            'summary' => $summary,
            'preview' => $preview,
            'meta' => [
                'account_orders_url' => url('/account/orders'),
                'admin_orders_url' => url('/admin/orders/' . $order->id),
            ],
        ];
    }

    protected function dispatchNotificationHooks(string $event, Order $order, array $context): void
    {
        $context = Hook::applyFilters('order.refund.notification.context', $context, $event, $order);
        $channels = Hook::applyFilters(
            'order.refund.notification.channels',
            ['email_customer', 'email_admin', 'admin_notice'],
            $event,
            $order,
            $context
        );

        Hook::doAction('order.refund.notification.dispatch', $event, $channels, $context, $order);
        Hook::doAction("order.refund.notification.dispatch.{$event}", $channels, $context, $order);
    }
}
