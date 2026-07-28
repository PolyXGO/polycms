<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\StockMovement;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryLedgerService
{
    public function isStockableProduct(Product $product, array $context = []): bool
    {
        $type = strtolower((string) $product->type);

        // Variable products manage stock per-variant
        if ($type === 'variable') {
            return true;
        }

        $default = in_array($type, ['product', 'physical'], true);

        return (bool) \App\Facades\Hook::applyFilters('inventory.is_stockable_product', $default, $product, $context);
    }

    public function applyMovement(array $payload): StockMovement
    {
        return DB::transaction(function () use ($payload): StockMovement {
            if (!empty($payload['idempotency_key'])) {
                $existing = StockMovement::query()
                    ->where('idempotency_key', $payload['idempotency_key'])
                    ->first();
                if ($existing) {
                    return $existing;
                }
            }

            $quantity = max(1, (int) ($payload['quantity'] ?? 0));
            $direction = ((int) ($payload['direction'] ?? -1)) >= 0 ? 1 : -1;

            // Support variant-level stock management
            $variantId = $payload['variant_id'] ?? null;
            $variant = null;

            if ($variantId) {
                $variant = \App\Models\Ecommerce\ProductVariant::query()
                    ->lockForUpdate()
                    ->findOrFail($variantId);
            }

            $product = Product::query()->lockForUpdate()->findOrFail($payload['product_id']);

            // Determine stock entity: variant takes priority if provided
            $stockEntity = $variant ?? $product;
            $before = (int) $stockEntity->stock_quantity;
            $after = $before + ($direction * $quantity);

            if ($after < 0) {
                $entityLabel = $variant
                    ? "variant #{$variant->id} ({$variant->display_name})"
                    : "product #{$product->id}";
                throw new \RuntimeException("Insufficient stock for {$entityLabel}");
            }

            $data = [
                'product_id' => $product->id,
                'location_code' => $payload['location_code'] ?? 'default',
                'movement_type' => $payload['movement_type'] ?? 'adjustment',
                'direction' => $direction,
                'quantity' => $quantity,
                'quantity_signed' => $direction * $quantity,
                'before_qty' => $before,
                'after_qty' => $after,
                'reference_type' => $payload['reference_type'] ?? null,
                'reference_id' => $payload['reference_id'] ?? null,
                'order_id' => $payload['order_id'] ?? null,
                'order_item_id' => $payload['order_item_id'] ?? null,
                'user_id' => $payload['user_id'] ?? null,
                'reason_code' => $payload['reason_code'] ?? null,
                'note' => $payload['note'] ?? null,
                'idempotency_key' => $payload['idempotency_key'] ?? null,
                'meta' => array_merge($payload['meta'] ?? [], $variantId ? ['variant_id' => $variantId] : []) ?: null,
                'created_at' => now(),
            ];

            $data = \App\Facades\Hook::applyFilters('inventory.before_movement', $data, $payload);

            $movement = StockMovement::create($data);

            // Update stock on the correct entity (variant or product)
            $stockEntity->stock_quantity = $after;
            if ((bool) $stockEntity->manage_stock) {
                if ($after <= 0 && $stockEntity->stock_status !== 'on_backorder') {
                    $stockEntity->stock_status = 'out_of_stock';
                }
                if ($after > 0 && $stockEntity->stock_status === 'out_of_stock') {
                    $stockEntity->stock_status = 'in_stock';
                }
            }
            $stockEntity->save();

            \App\Facades\Hook::doAction('inventory.after_movement', $movement, $payload);

            return $movement;
        });
    }

    public function listForProduct(int $productId, array $filters = []): Collection
    {
        $query = StockMovement::query()
            ->where('product_id', $productId)
            ->orderByDesc('created_at');

        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        if (!empty($filters['reference_type'])) {
            $query->where('reference_type', $filters['reference_type']);
        }

        if (!empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->get();
    }

    public function listForOrder(int $orderId): Collection
    {
        return StockMovement::query()
            ->where('order_id', $orderId)
            ->orderByDesc('created_at')
            ->get();
    }
}
