<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\ProductCoupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductCoupon::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('search')) {
            $query->where('code', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
        }

        $coupons = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($coupons);
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|unique:product_coupons,code|max:50',
            'title' => 'required|string|max:255',
            'type' => 'required|in:percent,fixed_amount',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_value' => 'nullable|numeric|min:0',
            'restricted_emails' => 'nullable|array',
            'restricted_emails.*' => 'email',
            'is_active' => 'boolean',
            'is_exclusive' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $coupon = ProductCoupon::create($validated);

        return response()->json($coupon, 201);
    }

    /**
     * Display the specified coupon.
     */
    public function show($id): JsonResponse
    {
        return response()->json(ProductCoupon::findOrFail($id));
    }

    /**
     * Update the specified coupon.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $coupon = ProductCoupon::findOrFail($id);

        // Validation rule: unique code except current id
        $validated = $request->validate([
            'code' => 'required|max:50|unique:product_coupons,code,'.$id,
            'title' => 'required|string|max:255',
            'type' => 'required|in:percent,fixed_amount',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_value' => 'nullable|numeric|min:0',
            'restricted_emails' => 'nullable|array',
            'restricted_emails.*' => 'email',
            'is_active' => 'boolean',
            'is_exclusive' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        
        // Locking mechanism if usage > 0 (as per proposal) could be added here
        if ($coupon->usage_count > 0) {
            // Unset fields that shouldn't change if used: value, type
            unset($validated['value'], $validated['type']);
        }

        $coupon->update($validated);

        return response()->json($coupon);
    }

    /**
     * Remove the specified coupon.
     */
    public function destroy($id): JsonResponse
    {
        $coupon = ProductCoupon::findOrFail($id);
        if ($coupon->usage_count > 0) {
            return response()->json(['message' => 'Cannot delete used coupon'], 422);
        }
        $coupon->delete();

        return response()->json(null, 204);
    }
}
