<?php

namespace Modules\Polyx\PaypalGateway;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Ecommerce\Order;
use Modules\Polyx\PaypalGateway\Services\PaypalApiClient;

class PaypalGateway implements PaymentGatewayInterface
{
    protected PaypalApiClient $client;

    public function __construct()
    {
        // Dynamically load config from Database
        try {
            $gateway = \App\Models\Ecommerce\PaymentGateway::where('code', 'paypal')->first();
            if ($gateway && !empty($gateway->config)) {
                $mode = $gateway->config['mode'] ?? 'sandbox';
                
                $clientId = ($mode === 'live')
                    ? ($gateway->config['live_client_id'] ?? $gateway->config['client_id'] ?? '')
                    : ($gateway->config['sandbox_client_id'] ?? $gateway->config['client_id'] ?? '');

                $clientSecret = ($mode === 'live')
                    ? ($gateway->config['live_client_secret'] ?? $gateway->config['client_secret'] ?? '')
                    : ($gateway->config['sandbox_client_secret'] ?? $gateway->config['client_secret'] ?? '');

                $webhookId = ($mode === 'live')
                    ? ($gateway->config['live_webhook_id'] ?? $gateway->config['webhook_id'] ?? '')
                    : ($gateway->config['sandbox_webhook_id'] ?? $gateway->config['webhook_id'] ?? '');

                config([
                    'paypal.mode' => $mode,
                    "paypal.{$mode}.client_id" => $clientId,
                    "paypal.{$mode}.client_secret" => $clientSecret,
                    "paypal.webhook_id" => $webhookId,
                ]);
            }
        } catch (\Exception $e) {
            // DB might not be ready or migrated
        }

        $this->client = app(PaypalApiClient::class);
    }

    /**
     * Get the unique code of the gateway.
     */
    public function getCode(): string
    {
        return 'paypal';
    }

    /**
     * Get the gateway display name.
     */
    public function getName(): string
    {
        return 'PayPal';
    }

    /**
     * Check if the gateway is properly configured.
     */
    public function isConfigured(): bool
    {
        $mode = config('paypal.mode', 'sandbox');
        $clientId = config("paypal.{$mode}.client_id");
        $clientSecret = config("paypal.{$mode}.client_secret");
        return !empty($clientId) && !empty($clientSecret);
    }

    /**
     * Create a payment for an order.
     */
    public function createPayment(Order $order): array
    {
        $currency = $order->currency ?? 'USD';
        $amount = $order->total_amount;
        $targetCurrency = config('paypal.target_currency', 'USD');
        $conversionData = [];

        if (!$this->client->isCurrencySupported($currency)) {
            $conversion = $this->client->convertCurrency($amount, $currency, $targetCurrency);
            
            $amount = $conversion['amount']; // Converted amount
            $currency = $targetCurrency;     // Target currency
            
            $conversionData = [
                'is_converted' => true,
                'original_amount' => $order->total_amount,
                'original_currency' => $order->currency,
                'exchange_rate' => $conversion['rate'],
                'target_currency' => $targetCurrency,
                'converted_amount' => $amount
            ];
        }

        $paypalOrder = $this->client->createOrder([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $order->code,
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                    'description' => "Order {$order->code}",
                ],
            ],
            'application_context' => [
                'return_url' => route('checkout.success', ['code' => $order->code]),
                'cancel_url' => route('checkout'),
                'brand_name' => config('app.name'),
                'user_action' => 'PAY_NOW',
            ],
        ]);

        if (!$paypalOrder || !isset($paypalOrder['id'])) {
            throw new \Exception('Failed to create PayPal order: ' . json_encode($paypalOrder));
        }

        $metadata = $order->metadata ?? [];
        $metadata['paypal_order_id'] = $paypalOrder['id'];
        
        if (!empty($conversionData)) {
            $metadata['paypal_conversion'] = $conversionData;
        }

        $order->update([
            'payment_method' => 'paypal',
            'metadata' => $metadata,
        ]);

        $approveUrl = $this->getApproveUrl($paypalOrder);

        return [
            'success' => true,
            'payment_method' => 'paypal',
            'paypal_order_id' => $paypalOrder['id'],
            'redirect_url' => $approveUrl,
            'approve_url' => $approveUrl,
        ];
    }

    /**
     * Process webhook callback.
     */
    public function handleWebhook(\Illuminate\Http\Request $request): array
    {
        return $request->all();
    }

    /**
     * Get payment status (for polling).
     */
    public function getPaymentStatus(Order $order): array
    {
        $metadata = $order->metadata ?? [];
        $paypalOrderId = $metadata['paypal_order_id'] ?? null;
        if (!$paypalOrderId) {
            return ['status' => 'unpaid'];
        }

        try {
            $paypalOrder = $this->client->getOrder($paypalOrderId);
            return [
                'status' => $paypalOrder['status'] ?? 'unknown',
                'paypal_order' => $paypalOrder,
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Render the PayPal Smart Payment Buttons for the checkout page.
     */
    /**
     * Render the PayPal Smart Payment Buttons for the checkout page.
     */
    public function getFormHtml($order): string
    {
        $mode = config('paypal.mode', 'sandbox');
        $clientId = config("paypal.{$mode}.client_id");
        
        $currency = $order->currency ?? 'USD';
        $amount = $order->total_amount;
        $targetCurrency = config('paypal.target_currency', 'USD');
        
        // Check support and convert if needed for the frontend SDK
        if (!$this->client->isCurrencySupported($currency)) {
            $conversion = $this->client->convertCurrency($amount, $currency, $targetCurrency);
            $currency = $targetCurrency;
            // We don't need to pass converted amount here as JS SDK createOrder will call our API
            // which handles the conversion logic again. 
            // BUT, the JS SDK script tag needs the correct currency to initialize.
        }

        $buttonConfig = config('paypal.button', []);

        return view('paypal::payment-form', [
            'order' => $order,
            'clientId' => $clientId,
            'currency' => $currency,
            'buttonColor' => $buttonConfig['color'] ?? 'gold',
            'buttonShape' => $buttonConfig['shape'] ?? 'rect',
            'buttonLabel' => $buttonConfig['label'] ?? 'paypal',
            'buttonHeight' => $buttonConfig['height'] ?? 45,
        ])->render();
    }

    /**
     * Process the payment request by creating a PayPal order.
     */
    /**
     * Process the payment request by creating a PayPal order.
     */
    public function processPayment($request, $amount = null, $currency = null, $conversionData = []): mixed
    {
        $orderId = $request->input('order_id');
        $order = Order::findOrFail($orderId);

        // Use provided amount/currency or fallback to order defaults
        $amount = $amount ?? $order->total_amount;
        $currency = $currency ?? ($order->currency ?? 'USD');

        // Create PayPal order
        $paypalOrder = $this->client->createOrder([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $order->code,
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                    'description' => "Order {$order->code}",
                ],
            ],
            'application_context' => [
                'return_url' => route('checkout.success', ['code' => $order->code]),
                'cancel_url' => route('checkout'),
                'brand_name' => config('app.name'),
                'user_action' => 'PAY_NOW',
            ],
        ]);

        if (!$paypalOrder || !isset($paypalOrder['id'])) {
            return [
                'success' => false,
                'error' => 'Failed to create PayPal order',
            ];
        }

        // Prepare metadata update
        $metadata = $order->metadata ?? [];
        $metadata['paypal_order_id'] = $paypalOrder['id'];
        
        if (!empty($conversionData)) {
            $metadata['paypal_conversion'] = $conversionData;
        }

        // Update order with PayPal order ID and conversion metadata
        $order->update([
            'payment_method' => 'paypal',
            'metadata' => $metadata,
        ]);

        return [
            'success' => true,
            'paypal_order_id' => $paypalOrder['id'],
            'approve_url' => $this->getApproveUrl($paypalOrder),
        ];
    }

    /**
     * Capture the approved PayPal payment.
     */
    public function capturePayment(string $paypalOrderId): array
    {
        $result = $this->client->captureOrder($paypalOrderId);

        if (!$result || ($result['status'] ?? '') !== 'COMPLETED') {
            return [
                'success' => false,
                'error' => 'Payment capture failed',
            ];
        }

        return [
            'success' => true,
            'transaction_id' => $result['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
            'status' => $result['status'],
            'payer' => $result['payer'] ?? [],
        ];
    }

    public function verifyWebhook(\Illuminate\Http\Request $request): bool
    {
        $webhookId = config('paypal.webhook_id');
        
        if (empty($webhookId)) {
            // If no webhook ID configured, skip signature verification
            // (not recommended for production)
            return true;
        }

        return $this->client->verifyWebhookSignature(
            $request->header('PAYPAL-TRANSMISSION-ID'),
            $request->header('PAYPAL-TRANSMISSION-TIME'),
            $webhookId,
            $request->getContent(),
            $request->header('PAYPAL-CERT-URL'),
            $request->header('PAYPAL-TRANSMISSION-SIG'),
            $request->header('PAYPAL-AUTH-ALGO')
        ) === true;
    }

    /**
     * Process a refund.
     */
    public function refund($transactionRef, $amount, $reason = ''): bool
    {
        $result = $this->client->refundCapture($transactionRef, [
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency_code' => 'USD', // Should be from order
            ],
            'note_to_payer' => $reason,
        ]);

        return $result && ($result['status'] ?? '') === 'COMPLETED';
    }

    /**
     * Extract the approval URL from PayPal order response.
     */
    protected function getApproveUrl(array $paypalOrder): ?string
    {
        foreach ($paypalOrder['links'] ?? [] as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }
        return null;
    }
}
