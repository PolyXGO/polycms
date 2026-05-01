<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\ProductCoupon;
use App\Models\Ecommerce\Order;
use Carbon\Carbon;

class CouponManager
{
    /**
     * Apply a coupon to an order (or cart).
     */
    public function applyCoupon(Order $order, $code)
    {
        $coupon = ProductCoupon::where('code', $code)->where('is_active', true)->first();
        
        if (!$coupon) {
            throw new \Exception("Coupon invalid.");
        }
        
        $this->validateCoupon($coupon, $order->subtotal_amount, $order->user);
        
        $discount = $this->calculateDiscount($coupon, $order->subtotal_amount);
        
        return [
            'coupon' => $coupon,
            'discount_amount' => $discount
        ];
    }

    public function validateCoupon(ProductCoupon $coupon, $subtotal, $user = null, $existingCoupons = [], $guestEmail = null)
    {
        // Date Check
        $now = Carbon::now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) throw new \Exception("Coupon not started.");
        if ($coupon->expires_at && $now->gt($coupon->expires_at)) throw new \Exception("Coupon expired.");
        
        // Limits
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) throw new \Exception("Coupon usage limit reached.");
        
        // Min Order Info
        if ($coupon->min_order_value && $subtotal < $coupon->min_order_value) throw new \Exception("Order value too low.");

        // Email Restriction
        if (!empty($coupon->restricted_emails)) {
            $email = $user ? $user->email : $guestEmail;

            if (!$email) {
                 throw new \Exception("This coupon requires login or a valid email address.");
            }
            
            if (!in_array($email, $coupon->restricted_emails)) {
                throw new \Exception("This coupon is not available for your email.");
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

    public function calculateDiscount(ProductCoupon $coupon, $subtotal)
    {
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
