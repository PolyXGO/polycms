<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\ProductLicense;
use App\Models\Ecommerce\UserSubscription;
use Illuminate\Support\Str;

class LicenseManager
{
    public function __construct(protected EmailManager $emailManager) {}

    /**
     * Issue a license for a subscription.
     */
    public function issueLicense(UserSubscription $subscription)
    {
        // Check if service policy allows license
        $policy = $subscription->service->license_policy; 
        // e.g., ['max_activations' => 5]
        
        if (!$policy) {
            return null;
        }

        $license = ProductLicense::create([
            'subscription_id' => $subscription->id,
            'license_key' => $this->generateKey(),
            'max_activations' => $policy['max_activations'] ?? 1,
            'status' => 'active'
        ]);

        // Send Email
        $this->emailManager->sendLicenseKeyEmail($license);

        return $license;
    }

    public function generateKey()
    {
        // Format: PFBS-XXXX-XXXX-XXXX (Poly Feng Shui)
        return strtoupper('KEY-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
    }

    public function activateLicense($key, $domain, $hwid = null)
    {
        $license = ProductLicense::where('license_key', $key)->first();
        
        if (!$license || $license->status !== 'active') {
            throw new \Exception("Invalid or inactive license.");
        }
        
        if ($license->activation_count >= $license->max_activations) {
            throw new \Exception("Max activations reached.");
        }

        $license->activations()->create([
            'domain' => $domain,
            'hardware_id' => $hwid
        ]);
        
        $license->increment('activation_count');
        
        return true;
    }
    public function deactivateLicense($activationId)
    {
        $activation = \App\Models\Ecommerce\LicenseActivation::find($activationId);
        if (!$activation) {
            return false;
        }

        $license = $activation->license;
        
        $activation->delete();
        $license->decrement('activation_count');
        
        return true;
    }
}
