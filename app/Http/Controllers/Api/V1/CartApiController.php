<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Ecommerce\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Services\Ecommerce\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Get current cart with calculated totals
     */
    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request);
        $totals = $this->cartService->calculateTotals($cart);

        return response()->json([
            'cart_id' => $cart->id,
            'currency' => $cart->currency,
            ...$totals,
        ]);
    }

    /**
     * Add item to cart
     *
     * Rate limit: 30/min via middleware
     */
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'metadata' => 'nullable|array',
        ]);

        try {
            $cart = $this->cartService->getCart($request);

            $item = $this->cartService->addItem(
                $cart,
                (int) $validated['product_id'],
                (int) ($validated['quantity'] ?? 1),
                isset($validated['variant_id']) ? (int) $validated['variant_id'] : null,
                $validated['metadata'] ?? []
            );

            // Return updated totals
            $totals = $this->cartService->calculateTotals($cart->fresh());

            return response()->json([
                'message' => 'Item added to cart',
                'item' => $item,
                ...$totals,
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'available_quantity' => $e->getAvailableQuantity(),
            ], 422);
        }
    }

    /**
     * Update item quantity
     */
    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        try {
            $cart = $this->cartService->getCart($request);

            $this->cartService->updateItem(
                $cart,
                $itemId,
                (int) $validated['quantity']
            );

            $totals = $this->cartService->calculateTotals($cart->fresh());

            return response()->json([
                'message' => 'Cart updated',
                ...$totals,
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'available_quantity' => $e->getAvailableQuantity(),
            ], 422);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->cartService->getCart($request);
        $this->cartService->removeItem($cart, $itemId);

        $totals = $this->cartService->calculateTotals($cart->fresh());

        return response()->json([
            'message' => 'Item removed',
            ...$totals,
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request);
        $this->cartService->clearCart($cart);

        return response()->json([
            'message' => 'Cart cleared',
            'subtotal' => 0,
            'item_count' => 0,
            'items' => [],
        ]);
    }

    /**
     * Get item count only (lightweight endpoint for header badge)
     */
    public function count(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request);

        return response()->json([
            'count' => $cart->items->sum('quantity'),
        ]);
    }
}
