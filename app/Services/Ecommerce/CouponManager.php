<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\ProductCoupon;
use App\Models\Ecommerce\Order;
use Carbon\Carbon;

class CouponManager
{
    /**
     * Get allowed product IDs for a coupon.
     */
    public function getAllowedProductIds(ProductCoupon $coupon): array
    {
        $scopeConfig = $coupon->scope_config ?? [];
        $raw = $scopeConfig['product_ids'] ?? [];
        if (empty($raw) || !is_array($raw)) {
            return [];
        }
        return array_map('intval', $raw);
    }

    public function applyCouponToOrder(Order $order, string $code, $appliedByUser = null)
    {
        $coupon = ProductCoupon::where('code', $code)->where('is_active', true)->first();

        if (!$coupon) {
            throw new \Exception("Invalid coupon code.");
        }

        $userForValidation = $appliedByUser ?? $order->user;
        $this->validateCoupon($coupon, $order->subtotal_amount, $userForValidation, [], null, $order);
        
        $discount = $this->calculateDiscount($coupon, $order->subtotal_amount, $order);
        
        return [
            'coupon' => $coupon,
            'discount_amount' => $discount
        ];
    }

    public function validateCoupon(ProductCoupon $coupon, $subtotal, $user = null, $existingCoupons = [], $guestEmail = null, $orderOrItems = null)
    {
        // Date Check
        $now = Carbon::now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) throw new \Exception("Coupon not started.");
        if ($coupon->expires_at && $now->gt($coupon->expires_at)) throw new \Exception("Coupon expired.");
        
        // Limits
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) throw new \Exception("Coupon usage limit reached.");
        
        // Product Restriction check
        $allowedProductIds = $this->getAllowedProductIds($coupon);
        if (!empty($allowedProductIds) && $orderOrItems) {
            $hasEligibleItem = false;
            if (is_array($orderOrItems)) {
                foreach ($orderOrItems as $item) {
                    $pId = is_array($item) ? ($item['product_id'] ?? null) : ($item->product_id ?? null);
                    if ($pId && in_array((int)$pId, $allowedProductIds, true)) {
                        $hasEligibleItem = true;
                        break;
                    }
                }
            } elseif (is_object($orderOrItems) && method_exists($orderOrItems, 'relationLoaded') && $orderOrItems->relationLoaded('items')) {
                foreach ($orderOrItems->items as $item) {
                    if (in_array((int)$item->product_id, $allowedProductIds, true)) {
                        $hasEligibleItem = true;
                        break;
                    }
                }
            } else {
                $hasEligibleItem = true;
            }

            if (!$hasEligibleItem) {
                throw new \Exception("Promo code '{$coupon->code}' is not applicable to any items in your cart.");
            }
        }

        // Resolve Target Customer and Acting User info
        $targetEmail = (is_object($orderOrItems) && isset($orderOrItems->user)) 
            ? ($orderOrItems->user?->email ?? data_get($orderOrItems->billing_address, 'email')) 
            : ($user ? $user->email : $guestEmail);
        $targetUserId = (is_object($orderOrItems) && isset($orderOrItems->user_id)) 
            ? $orderOrItems->user_id 
            : ($user ? $user->id : null);
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
                    $timesStr = $coupon->usage_limit_per_user > 1 ? "{$coupon->usage_limit_per_user} times" : "once";
                    throw new \Exception("You have already used the promo code '{$coupon->code}' on a previous order (limit: {$timesStr} per customer).");
                }
            }
        }

        // Min Order Info
        if ($coupon->min_order_value && $subtotal < $coupon->min_order_value) {
            throw new \Exception("Your order does not meet the minimum requirement of " . format_currency((float)$coupon->min_order_value) . " for promo code '{$coupon->code}'.");
        }

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
                throw new \Exception("Promo code '{$coupon->code}' is reserved for specific customer accounts.");
            }
        }

        // Exclusive Logic
        if ($coupon->is_exclusive && !empty($existingCoupons)) {
             throw new \Exception("Exclusive promo code '{$coupon->code}' cannot be combined with other discounts.");
        }

        // Check against existing coupons
        foreach ($existingCoupons as $existing) {
             if ($existing->is_exclusive) {
                 throw new \Exception("An exclusive promo code is already applied to this order.");
             }
             if ($existing->code === $coupon->code) {
                 throw new \Exception("Promo code '{$coupon->code}' is already applied.");
             }
        }
        
        return true;
    }

    public function calculateDiscount(ProductCoupon $coupon, $subtotal, $orderOrItems = null)
    {
        $allowedProductIds = $this->getAllowedProductIds($coupon);
        
        // If product restrictions exist, calculate subtotal only for eligible products
        if (!empty($allowedProductIds) && $orderOrItems) {
            $eligibleSubtotal = 0;
            if (is_array($orderOrItems)) {
                foreach ($orderOrItems as $item) {
                    $pId = is_array($item) ? ($item['product_id'] ?? null) : ($item->product_id ?? null);
                    if ($pId && in_array((int)$pId, $allowedProductIds, true)) {
                        $qty = is_array($item) ? ($item['quantity'] ?? 1) : ($item->quantity ?? 1);
                        $price = is_array($item) ? ($item['original_price'] ?? $item['price'] ?? 0) : ($item->price ?? 0);
                        $eligibleSubtotal += ((float)$price * (int)$qty);
                    }
                }
            } elseif (is_object($orderOrItems) && method_exists($orderOrItems, 'relationLoaded') && $orderOrItems->relationLoaded('items')) {
                foreach ($orderOrItems->items as $item) {
                    if (in_array((int)$item->product_id, $allowedProductIds, true)) {
                        $eligibleSubtotal += ((float)$item->price * (int)$item->quantity);
                    }
                }
            }
            if ($eligibleSubtotal > 0) {
                $subtotal = $eligibleSubtotal;
            }
        }

        if ($coupon->type === 'percent') {
            $discount = $subtotal * ($coupon->value / 100);
        } else {
            $discount = $coupon->value;
        }

        // Apply max discount cap if defined
        if ($coupon->max_discount_value && $coupon->max_discount_value > 0) {
            $discount = min($discount, (float)$coupon->max_discount_value);
        }

        // Discount cannot exceed subtotal
        return min($discount, $subtotal);
    }
}
