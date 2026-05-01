<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Exceptions\Ecommerce\InsufficientStockException;
use App\Facades\Hook;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get or create the current cart
     *
     * PERFORMANCE: Eager load products with media in one query
     */
    public function getCart(Request $request): Cart
    {
        $user = $request->user();

        if ($user) {
            return Cart::with(['items.product.media', 'items.variant.image'])
                ->firstOrCreate(['user_id' => $user->id]);
        }

        $sessionId = $request->session()->getId();

        return Cart::with(['items.product.media', 'items.variant.image'])
            ->active()
            ->firstOrCreate(
                ['session_id' => $sessionId],
                ['expires_at' => now()->addDays(30)]
            );
    }

    /**
     * Add item to cart with full validation
     *
     * SECURITY:
     * - Validates product exists and is published
     * - Validates variant belongs to product
     * - Validates stock availability
     * - Caps quantity at 99
     */
    public function addItem(Cart $cart, int $productId, int $quantity = 1, ?int $variantId = null, array $metadata = []): CartItem
    {
        $quantity = max(1, min($quantity, 99));

        // SECURITY: Validate product is published
        $product = Product::published()->findOrFail($productId);

        // SECURITY: Validate variant belongs to this product
        $variant = null;
        if ($variantId) {
            $variant = $product->activeVariants()->findOrFail($variantId);
        }

        // SECURITY: Validate stock
        $stockEntity = $variant ?? $product;
        if ($stockEntity->manage_stock && $stockEntity->stock_quantity < $quantity) {
            throw new InsufficientStockException(
                "Only {$stockEntity->stock_quantity} items available for \"{$product->name}\"",
                $stockEntity->stock_quantity
            );
        }

        // Check for existing item to update quantity (DB-agnostic, no LEAST/GREATEST)
        $existing = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($existing) {
            $newQty = min($existing->quantity + $quantity, 99);
            $existing->update([
                'quantity' => $newQty,
                'metadata' => !empty($metadata) ? $metadata : $existing->metadata,
            ]);
            $item = $existing;
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'metadata' => !empty($metadata) ? $metadata : null,
            ]);
        }

        Hook::doAction('cart.item.added', $item, $cart);

        return $item->fresh(['product.media', 'variant']);
    }

    /**
     * Update item quantity
     */
    public function updateItem(Cart $cart, int $itemId, int $quantity): CartItem
    {
        $quantity = max(1, min($quantity, 99));

        $item = $cart->items()->findOrFail($itemId);
        $item->load(['product', 'variant']);

        // SECURITY: Validate stock
        $stockEntity = $item->variant ?? $item->product;
        if ($stockEntity && $stockEntity->manage_stock && $stockEntity->stock_quantity < $quantity) {
            throw new InsufficientStockException(
                "Only {$stockEntity->stock_quantity} items available",
                $stockEntity->stock_quantity
            );
        }

        $oldQuantity = $item->quantity;
        $item->update(['quantity' => $quantity]);

        Hook::doAction('cart.item.updated', $item, $oldQuantity, $quantity);

        return $item;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Cart $cart, int $itemId): bool
    {
        $item = $cart->items()->findOrFail($itemId);

        Hook::doAction('cart.item.removed', $item, $cart);

        return (bool) $item->delete();
    }

    /**
     * Clear all items from cart
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();

        Hook::doAction('cart.cleared', $cart);
    }

    /**
     * Merge guest cart into user cart on login
     *
     * PERFORMANCE: Single transaction, avoid duplicates
     */
    public function mergeOnLogin(User $user, string $sessionId): void
    {
        DB::transaction(function () use ($user, $sessionId) {
            $guestCart = Cart::where('session_id', $sessionId)
                ->with('items')
                ->first();

            if (!$guestCart || $guestCart->items->isEmpty()) {
                return;
            }

            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $guestItem) {
                $existing = CartItem::where('cart_id', $userCart->id)
                    ->where('product_id', $guestItem->product_id)
                    ->where('variant_id', $guestItem->variant_id)
                    ->first();

                if ($existing) {
                    // Keep the higher quantity
                    $existing->update([
                        'quantity' => max($existing->quantity, $guestItem->quantity),
                    ]);
                } else {
                    CartItem::create([
                        'cart_id' => $userCart->id,
                        'product_id' => $guestItem->product_id,
                        'variant_id' => $guestItem->variant_id,
                        'quantity' => $guestItem->quantity,
                        'metadata' => $guestItem->metadata,
                    ]);
                }
            }

            // Delete guest cart (cascade deletes items)
            $guestCart->delete();

            Hook::doAction('cart.merged', $userCart, $guestCart);
        });
    }

    /**
     * Calculate cart totals
     *
     * PERFORMANCE: Single query with eager loading
     * SECURITY: Prices always from DB, never from client
     */
    public function calculateTotals(Cart $cart): array
    {
        $cart->loadMissing(['items.product.media', 'items.variant']);

        $subtotal = 0;
        $itemCount = 0;
        $validItems = [];

        foreach ($cart->items as $item) {
            $product = $item->product;

            // Skip invalid or unpublished products
            if (!$product || $product->status !== 'published') {
                continue;
            }

            // SECURITY: Price from DB, not from client
            $price = $item->variant
                ? $item->variant->effective_price
                : $product->effective_price;

            $lineTotal = round($price * $item->quantity, 2);
            $subtotal += $lineTotal;
            $itemCount += $item->quantity;

            // Build image URL
            $imageUrl = null;
            if ($item->variant?->image) {
                $imageUrl = $item->variant->image->url ?? null;
            }
            if (!$imageUrl && $product->relationLoaded('media')) {
                $primaryImage = $product->media->first(fn($m) => $m->pivot->is_primary ?? false);
                $imageUrl = $primaryImage?->url ?? $product->media->first()?->url;
            }

            $validItems[] = [
                'id' => $item->id,
                'product_id' => $product->id,
                'variant_id' => $item->variant_id,
                'name' => $product->name,
                'slug' => $product->slug,
                'variant_name' => $item->variant?->display_name,
                'sku' => $item->variant?->sku ?? $product->sku,
                'price' => $price,
                'quantity' => $item->quantity,
                'line_total' => $lineTotal,
                'image_url' => $imageUrl,
                'in_stock' => $item->isInStock(),
                'frontend_url' => $product->frontend_url,
            ];
        }

        $totals = [
            'subtotal' => round($subtotal, 2),
            'item_count' => $itemCount,
            'items' => $validItems,
        ];

        return Hook::applyFilters('cart.totals', $totals, $cart);
    }
}
