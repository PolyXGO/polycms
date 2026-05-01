<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\Order;
use Illuminate\Support\Facades\DB;

class OrderInventorySyncService
{
    public function __construct(protected InventoryLedgerService $inventoryLedgerService) {}

    /**
     * Deduct stock for stockable order items.
     * Idempotent per order item via stable idempotency key.
     */
    public function syncSaleForOrder(Order $order, ?int $actorId = null, string $source = 'order_status'): array
    {
        return DB::transaction(function () use ($order, $actorId, $source): array {
            $order->loadMissing(['items.product']);

            $synced = 0;
            $skipped = 0;

            foreach ($order->items as $item) {
                if (!$item->product) {
                    $skipped++;
                    continue;
                }

                $product = $item->product;
                $stockable = $this->inventoryLedgerService->isStockableProduct($product, [
                    'order' => $order,
                    'order_item' => $item,
                    'source' => $source,
                ]);

                if (!$stockable || !(bool) $product->manage_stock) {
                    $skipped++;
                    continue;
                }

                $this->inventoryLedgerService->applyMovement([
                    'product_id' => $product->id,
                    'movement_type' => 'sale',
                    'direction' => -1,
                    'quantity' => max(1, (int) $item->quantity),
                    'reference_type' => 'order_item',
                    'reference_id' => $item->id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'user_id' => $actorId,
                    'reason_code' => 'sale_checkout',
                    'note' => 'Order sale stock-out',
                    'idempotency_key' => 'sale:' . $order->id . ':item:' . $item->id,
                    'meta' => [
                        'order_code' => $order->code,
                        'source' => $source,
                    ],
                ]);

                $synced++;
            }

            return [
                'synced_items' => $synced,
                'skipped_items' => $skipped,
            ];
        });
    }
}
