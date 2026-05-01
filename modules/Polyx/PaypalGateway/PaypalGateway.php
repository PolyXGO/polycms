<?php

namespace Modules\Polyx\PaypalGateway;

use App\Contracts\Ecommerce\PaymentGatewayInterface;
use App\Models\Ecommerce\Order;
use Modules\Polyx\PaypalGateway\Services\PaypalApiClient;

class PaypalGateway implements PaymentGatewayInterface
{
    protected PaypalApiClient $client;

    public function __construct()
    {
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

    /**
     * Verify the webhook/IPN callback.
     */
    public function verifyWebhook($request): bool|array
    {
        $webhookId = config('paypal.webhook_id');
        
        if (empty($webhookId)) {
            // If no webhook ID configured, skip signature verification
            // (not recommended for production)
            return $request->all();
        }

        $isValid = $this->client->verifyWebhookSignature(
            $request->header('PAYPAL-TRANSMISSION-ID'),
            $request->header('PAYPAL-TRANSMISSION-TIME'),
            $webhookId,
            $request->getContent(),
            $request->header('PAYPAL-CERT-URL'),
            $request->header('PAYPAL-TRANSMISSION-SIG'),
            $request->header('PAYPAL-AUTH-ALGO')
        );

        if (!$isValid) {
            return false;
        }

        return $request->all();
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
