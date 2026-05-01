<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderManager
{
    public function __construct(protected EmailManager $emailManager) {}

    /**
     * Create a new order from cart data.
     */
    /**
     * Create a new order from cart data.
     */
    public function createOrder($user, $cartItems, $data = [], $suppressEmail = false)
    {
        return DB::transaction(function () use ($user, $cartItems, $data, $suppressEmail) {
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'guest_email' => $data['guest_email'] ?? null,
                'code' => $this->generateOrderCode(),
                'status' => 'pending',
                'currency' => $data['currency'] ?? 'USD',
                'subtotal_amount' => 0, // Calculated below
                'total_amount' => 0,
                'payment_method' => $data['payment_gateway'] ?? null,
                'payment_status' => 'unpaid',
                'customer_note' => $data['note'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
            ]);

            $totals = $this->calculateTotals($cartItems, $data);

            foreach ($cartItems as $item) {
                // $item structure: ['product_id', 'service_id', 'quantity', 'price', 'name']
                $metadata = $item['metadata'] ?? [];
                
                if (isset($item['variant_id'])) {
                    $metadata['variant_id'] = $item['variant_id'];
                }
                if (isset($item['image_url'])) {
                    $metadata['image_url'] = $item['image_url'];
                }
                if (isset($item['sku'])) {
                    $metadata['sku'] = $item['sku'];
                }
                if (isset($item['variant_label'])) {
                    $metadata['variant_label'] = $item['variant_label'];
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'service_id' => $item['service_id'] ?? null,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'metadata' => empty($metadata) ? null : $metadata,
                ]);
            }

            $order->update([
                'subtotal_amount' => $totals['subtotal'],
                'discount_amount' => $totals['discount'],
                'tax_amount' => $totals['tax'],
                'total_amount' => $totals['total'],
                'discount_code' => $data['discount_code'] ?? null,
            ]);

            // Trigger Email via Queue (non-blocking)
            if (!$suppressEmail) {
                \App\Jobs\SendOrderEmailJob::dispatch($order->id, 'order_placed');
            }

            return $order;
        });
    }

    /**
     * Calculate order totals from items and data.
     */
    public function calculateTotals(array $items, array $data = []): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $lineTotal = $item['price'] * $item['quantity'];
            $subtotal += $lineTotal;
        }

        // Apply Discount logic (Integration point for CouponManager)
        $discountAmount = $data['discount_amount'] ?? 0;
        
        // Example Tax Logic (Flat or %) - simplistic for now
        $taxAmount = $data['tax_amount'] ?? 0;

        $total = max(0, $subtotal + $taxAmount - $discountAmount);

        return [
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'tax' => $taxAmount,
            'total' => $total,
        ];
    }

    public function generateOrderCode()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    public function updateStatus(Order $order, $status)
    {
        $oldStatus = $order->status;
        $order->update(['status' => $status]);
        
        // Trigger status change email via queue
        if ($status !== $oldStatus && in_array($status, ['processing', 'completed'])) {
            \App\Jobs\SendOrderEmailJob::dispatch($order->id, 'order_status_changed', [
                'new_status' => $status,
            ]);
        }
    }
}
