<?php

namespace Modules\Polyx\PaypalGateway\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaypalApiClient
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $mode = config('paypal.mode', 'sandbox');
        $this->baseUrl = $mode === 'live' 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';
        
        $this->clientId = config("paypal.{$mode}.client_id", '');
        $this->clientSecret = config("paypal.{$mode}.client_secret", '');
    }

    /**
     * Get OAuth access token.
     */
    public function getAccessToken(): ?string
    {
        $cacheKey = 'paypal_access_token_' . md5($this->clientId);

        return Cache::remember($cacheKey, 3500, function () {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('PayPal: Failed to get access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        });
    }

    /**
     * Create a PayPal order.
     */
    public function createOrder(array $data): ?array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders", $data);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PayPal: Failed to create order', [
            'status' => $response->status(),
            'body' => $response->body(),
            'data' => $data,
        ]);

        return null;
    }

    /**
     * Capture an approved PayPal order.
     */
    public function captureOrder(string $orderId): ?array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        $response = Http::withToken($token)
            ->withHeaders(['Prefer' => 'return=representation'])
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PayPal: Failed to capture order', [
            'order_id' => $orderId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Get order details.
     */
    public function getOrder(string $orderId): ?array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/v2/checkout/orders/{$orderId}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Refund a captured payment.
     */
    public function refundCapture(string $captureId, array $data = []): ?array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/payments/captures/{$captureId}/refund", $data);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PayPal: Failed to refund capture', [
            'capture_id' => $captureId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(
        string $transmissionId,
        string $transmissionTime,
        string $webhookId,
        string $webhookEvent,
        string $certUrl,
        string $transmissionSig,
        string $authAlgo
    ): bool {
        $token = $this->getAccessToken();

        if (!$token) {
            return false;
        }

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", [
                'transmission_id' => $transmissionId,
                'transmission_time' => $transmissionTime,
                'cert_url' => $certUrl,
                'auth_algo' => $authAlgo,
                'transmission_sig' => $transmissionSig,
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($webhookEvent, true),
            ]);

        if ($response->successful()) {
            return $response->json('verification_status') === 'SUCCESS';
        }

        Log::error('PayPal: Webhook signature verification failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return false;
    }
    /**
     * Check if currency is supported by PayPal.
     */
    public function isCurrencySupported(string $currency): bool
    {
        // List of PayPal supported currencies
        $supported = [
            'AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF',
            'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN',
            'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'
        ];
        
        return in_array(strtoupper($currency), $supported);
    }

    /**
     * Convert amount to target currency using simple exchange rate.
     */
    public function convertCurrency(float $amount, string $from, string $to): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        
        if ($from === $to) {
            return [
                'amount' => $amount,
                'rate' => 1.0
            ];
        }

        // Get custom exchange rates from config
        $rates = config('paypal.exchange_rates', []);
        
        // E.g., 'VND_USD' => 0.00004
        $pair = "{$from}_{$to}";
        
        if (isset($rates[$pair])) {
            $rate = $rates[$pair];
        } else {
            // Fallback for demo/common conversions if not configured
            if ($pair === 'VND_USD') {
                $rate = 0.00004; // 1 VND = 0.00004 USD (approx 25,000 VND = 1 USD)
            } else {
                 Log::warning("PayPal: No exchange rate found for {$pair}");
                 // Default to 1:1 if unknown (risky but better than crash?) 
                 //Ideally should throw exception or handle gracefully
                 $rate = 1.0; 
            }
        }
        
        return [
            'amount' => round($amount * $rate, 2),
            'rate' => $rate
        ];
    }
}
