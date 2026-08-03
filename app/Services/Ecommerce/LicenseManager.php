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
        $service = $subscription->service;
        $policy = $service?->license_policy ?: ['type' => 'domain', 'max_activations' => 5];

        // Check if license already exists for this subscription
        $existing = ProductLicense::where('subscription_id', $subscription->id)->first();
        if ($existing) {
            return $existing;
        }

        $license = ProductLicense::create([
            'subscription_id' => $subscription->id,
            'license_key' => $this->generateKey(),
            'max_activations' => $policy['max_activations'] ?? 5,
            'status' => 'active'
        ]);

        // Send Email
        try {
            $this->emailManager->sendLicenseKeyEmail($license);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send license email: " . $e->getMessage());
        }

        return $license;
    }

    public function generateKey()
    {
        // Format: PFBS-XXXX-XXXX-XXXX (Poly Feng Shui)
        return strtoupper('MTX-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
    }

    public function activateLicense($key, $domain, $hwid = null)
    {
        $license = ProductLicense::where('license_key', $key)->first();
        
        if (!$license || $license->status !== 'active') {
            throw new \Exception("Invalid or inactive license.");
        }
        
        if ($license->subscription) {
            if ($license->subscription->status !== 'active') {
                throw new \Exception("Subscription is not active.");
            }
            if ($license->subscription->expires_at && $license->subscription->expires_at->isPast()) {
                throw new \Exception("Subscription has expired.");
            }
        }
        
        if ($license->activation_count >= $license->max_activations) {
            throw new \Exception("Max activations reached.");
        }

        // Generate a cryptographically secure activation token (64-char hex)
        $activationToken = bin2hex(random_bytes(32));

        $activation = $license->activations()->create([
            'domain' => $domain,
            'hardware_id' => $hwid,
            'activation_token' => $activationToken,
        ]);
        
        $license->increment('activation_count');
        
        return $activation;
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

    /**
     * Verify if a license key is active and assigned/activated for a specific domain.
     * 
     * @param string $key License key
     * @param string $domain Domain to verify
     * @param string|null $activationToken Optional activation token for enhanced security
     */
    public function verifyLicense($key, $domain, $activationToken = null)
    {
        if (empty($key)) {
            return ['valid' => false, 'message' => 'License key is required.'];
        }

        $license = ProductLicense::where('license_key', $key)->first();

        if (!$license || $license->status !== 'active') {
            return ['valid' => false, 'message' => 'Invalid or inactive license key.'];
        }

        if ($license->subscription) {
            if ($license->subscription->status !== 'active') {
                return ['valid' => false, 'message' => 'Associated subscription is inactive.'];
            }
            if ($license->subscription->expires_at && $license->subscription->expires_at->isPast()) {
                return ['valid' => false, 'message' => 'Associated subscription has expired.'];
            }
        }

        $cleanDomain = strtolower(preg_replace('#^https?://#', '', trim($domain)));
        $activation = $license->activations()->where('domain', $cleanDomain)->first();

        if (!$activation) {
            // Also check raw domain
            $activation = $license->activations()->where('domain', $domain)->first();
        }

        if (!$activation) {
            return ['valid' => false, 'message' => "License is active, but domain {$domain} is not activated."];
        }

        // If activation_token exists on the activation record, enforce strict token verification
        if (!empty($activation->activation_token)) {
            if (empty($activationToken) || !hash_equals($activation->activation_token, $activationToken)) {
                return ['valid' => false, 'message' => 'Activation token is missing or invalid. Please re-activate your license.'];
            }
        }

        return ['valid' => true, 'message' => 'License verified successfully.', 'license' => $license];
    }
}
