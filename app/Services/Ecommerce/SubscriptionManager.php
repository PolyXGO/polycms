<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\UserSubscription;
use App\Models\Ecommerce\ProductService;
use Carbon\Carbon;

class SubscriptionManager
{
    /**
     * Activate a subscription for a user based on partial order item data.
     */
    public function activateSubscription($user, $serviceId, $productId)
    {
        $service = ProductService::find($serviceId);
        
        $startsAt = Carbon::now();
        $expiresAt = null;

        if ($service->access_type === 'subscription') {
            $duration = $service->duration_value;
            $unit = $service->duration_unit; // day, month, year
            
            if ($unit && $duration) {
                $expiresAt = $startsAt->copy()->add($unit . 's', $duration);
            }

            // Check if user has any prior subscription for this specific product to enforce one-time trial limit
            $hasPriorSubscription = UserSubscription::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            if (!$hasPriorSubscription && $service->trial_period_days > 0) {
                if ($expiresAt) {
                    $expiresAt->addDays($service->trial_period_days);
                } else {
                    $expiresAt = $startsAt->copy()->addDays($service->trial_period_days);
                }
            }
        }

        return UserSubscription::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'product_id' => $productId,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'status' => 'active',
            'is_auto_renew' => $service->is_recurring,
        ]);
    }

    /**
     * Renew a subscription.
     */
    public function renewSubscription(UserSubscription $subscription)
    {
        // Logic to extend expiry date
        $service = $subscription->service;
        $unit = $service->duration_unit;
        $duration = $service->duration_value;

        $newExpiresAt = $subscription->expires_at->copy()->add($unit . 's', $duration);

        // Option 1: Update existing
        // $subscription->update(['expires_at' => $newExpiresAt]);
        
        // Option 2: Create new linked subscription (Better for history)
        $newSub = $subscription->replicate();
        $newSub->starts_at = $subscription->expires_at; // Chain it
        $newSub->expires_at = $newExpiresAt;
        $newSub->renewed_from_subscription_id = $subscription->id;
        $newSub->save();
        
        // Only mark as expired if the current expiry date has already passed.
        // Otherwise, keep it active so the user does not lose access during their current billing period.
        if ($subscription->expires_at && $subscription->expires_at->isPast()) {
            $subscription->update(['status' => 'expired']);
        }
        
        return $newSub;
    }
}
