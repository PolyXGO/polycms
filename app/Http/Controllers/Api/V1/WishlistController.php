<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * List user's wishlist items
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $items = Wishlist::where('user_id', $user->id)
            ->with(['product.media', 'variant'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json($items);
    }

    /**
     * Toggle wishlist item (add if not exists, remove if exists)
     */
    public function toggle(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $validated['product_id'])
            ->where('variant_id', $validated['variant_id'] ?? null)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'message' => 'Removed from wishlist',
                'wishlisted' => false,
            ]);
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
            'variant_id' => $validated['variant_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Added to wishlist',
            'wishlisted' => true,
        ]);
    }

    /**
     * Check if product is in user's wishlist
     */
    public function check(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['wishlisted' => false]);
        }

        $exists = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json(['wishlisted' => $exists]);
    }
}
