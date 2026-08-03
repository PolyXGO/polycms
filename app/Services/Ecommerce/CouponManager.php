<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\ProductCoupon;
use App\Models\Ecommerce\Order;
use Carbon\Carbon;

class CouponManager
{
    /**
     * Get all allowed product IDs including all translation variants (same translation_group_id).
     */
    protected function getAllowedProductIds(ProductCoupon $coupon): array
    {
        $allowedIds = $coupon->scope_config['product_ids'] ?? [];
        if (empty($allowedIds)) {
            return [];
        }

        // Include translation products (same translation_group_id) automatically
        $translationGroupIds = \App\Models\Product::withoutGlobalScope('locale')
            ->whereIn('id', $allowedIds)
            ->whereNotNull('translation_group_id')
            ->pluck('translation_group_id')
            ->toArray();

        if (!empty($translationGroupIds)) {
            $translatedIds = \App\Models\Product::withoutGlobalScope('locale')
                ->whereIn('translation_group_id', $translationGroupIds)
                ->pluck('id')
                ->toArray();
            $allowedIds = array_unique(array_merge($allowedIds, $translatedIds));
        }

        return array_map('intval', $allowedIds);
    }

    /**
     * Apply a coupon to an order (or cart).
     */
    public function applyCoupon(Order $order, $code, $appliedByUser = null)
    {
        $coupon = ProductCoupon::where('code', $code)->where('is_active', true)->first();
        
        if (!$coupon) {
            throw new \Exception("Coupon invalid.");
        }
        
        $userForValidation = $appliedByUser ?? $order->user;
        $this->validateCoupon($coupon, $order->subtotal_amount, $userForValidation, [], null, $order);
        
        $discount = $this->calculateDiscount($coupon, $order->subtotal_amount, $order);
        
        return [
            'coupon' => $coupon,
            'discount_amount' => $discount
        ];
    }

    public function validateCoupon(ProductCoupon $coupon, $subtotal, $user = null, $existingCoupons = [], $guestEmail = null, $order = null)
    {
        // Date Check
        $now = Carbon::now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) throw new \Exception("Coupon not started.");
        if ($coupon->expires_at && $now->gt($coupon->expires_at)) throw new \Exception("Coupon expired.");
        
        // Limits
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) throw new \Exception("Coupon usage limit reached.");
        
        // Product Restriction check
        $allowedProductIds = $this->getAllowedProductIds($coupon);
        if (!empty($allowedProductIds) && $order && $order->relationLoaded('items')) {
            $hasEligibleItem = false;
            foreach ($order->items as $item) {
                if (in_array((int)$item->product_id, $allowedProductIds, true)) {
                    $hasEligibleItem = true;
                    break;
                }
            }
            if (!$hasEligibleItem) {
                throw new \Exception("This coupon is not applicable to any items in your order.");
            }
        }

        // Resolve Target Customer and Acting User info
        $targetEmail = $order ? ($order->user?->email ?? data_get($order->billing_address, 'email')) : ($user ? $user->email : $guestEmail);
        $targetUserId = $order ? $order->user_id : ($user ? $user->id : null);
        $actingEmail = $user ? $user->email : null;

        // Limit per user check
        if ($coupon->usage_limit_per_user && $coupon->usage_limit_per_user > 0) {
            if ($targetUserId || $targetEmail) {
                $userUsageCount = Order::query()
                    ->where(function ($q) use ($targetUserId, $targetEmail) {
                        if ($targetUserId) {
                            $q->where('user_id', $targetUserId);
                        }
                        if ($targetEmail) {
                            $q->orWhere('billing_address->email', $targetEmail);
                        }
                    })
                    ->where(function ($q) use ($coupon) {
                        $q->where('discount_code', $coupon->code)
                          ->orWhere('discount_code', 'like', "%{$coupon->code}%");
                    })
                    ->whereNotIn('status', ['cancelled'])
                    ->count();

                if ($userUsageCount >= $coupon->usage_limit_per_user) {
                    throw new \Exception("This customer has reached the usage limit ({$coupon->usage_limit_per_user} time) for this coupon code.");
                }
            }
        }

        // Min Order Info
        if ($coupon->min_order_value && $subtotal < $coupon->min_order_value) throw new \Exception("Order value too low.");

        // Email Restriction
        if (!empty($coupon->restricted_emails)) {
            $allowed = false;
            if ($targetEmail && in_array($targetEmail, $coupon->restricted_emails)) {
                $allowed = true;
            }
            if ($actingEmail && in_array($actingEmail, $coupon->restricted_emails)) {
                $allowed = true;
            }

            if (!$allowed) {
                throw new \Exception("This coupon is not available for this user or email.");
            }
        }

        // Exclusive Logic
        if ($coupon->is_exclusive && !empty($existingCoupons)) {
             throw new \Exception("Exclusive discounts cannot be combined with other discount codes.");
        }

        // Check against existing coupons
        foreach ($existingCoupons as $existing) {
             if ($existing->is_exclusive) {
                 throw new \Exception("An exclusive coupon is already applied.");
             }
             if ($existing->code === $coupon->code) {
                 throw new \Exception("Coupon already applied.");
             }
        }
        
        return true;
    }

    public function calculateDiscount(ProductCoupon $coupon, $subtotal, ?Order $order = null)
    {
        $allowedProductIds = $this->getAllowedProductIds($coupon);
        
        // If product restrictions exist and an order is provided, calculate subtotal only for eligible products
        if (!empty($allowedProductIds) && $order && $order->relationLoaded('items')) {
            $eligibleSubtotal = 0;
            foreach ($order->items as $item) {
                if (in_array((int)$item->product_id, $allowedProductIds, true)) {
                    $eligibleSubtotal += ($item->price * $item->quantity);
                }
            }
            $subtotal = $eligibleSubtotal;
        }

        if ($coupon->type === 'fixed_amount') {
            return min($coupon->value, $subtotal);
        }
        
        // Percent
        $amount = $subtotal * ($coupon->value / 100);
        
        if ($coupon->max_discount_value && $amount > $coupon->max_discount_value) {
            $amount = $coupon->max_discount_value;
        }
        
        return $amount;
    }
}
