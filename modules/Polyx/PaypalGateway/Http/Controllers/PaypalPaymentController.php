<?php

namespace Modules\Polyx\PaypalGateway\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\UserTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Polyx\PaypalGateway\PaypalGateway;

class PaypalPaymentController extends Controller
{
    protected PaypalGateway $gateway;

    public function __construct()
    {
        $this->gateway = new PaypalGateway();
    }

    /**
     * Create a PayPal order for the given order.
     */
    /**
     * Create a PayPal order for the given order.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Verify the order belongs to the current user
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        // Verify order is in pending status
        if ($order->payment_status !== 'unpaid') {
            return response()->json([
                'success' => false,
                'error' => 'This order has already been paid',
            ], 400);
        }

        // --- Smart Payment: Currency Conversion ---
        $currency = $order->currency ?? 'USD';
        $amount = $order->total_amount;
        $targetCurrency = config('paypal.target_currency', 'USD');
        $conversionData = [];

        if (!$this->gateway->client->isCurrencySupported($currency)) {
            $conversion = $this->gateway->client->convertCurrency($amount, $currency, $targetCurrency);
            
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
        // ------------------------------------------

        // Pass conversion data to processPayment via request merge or custom method
        // Since processPayment takes request, we'll inject data into request attributes or modify processPayment
        // For now, let's assume processPayment implementation needs update or we handle it here.
        // Looking at PaypalGateway::processPayment, it reads from Order model. 
        // We should temporarily override the order's amount/currency in memory or pass extra data.
        
        // BETTER APPROACH: Update PaypalGateway::processPayment to accept overrides OR handle creation here directly using client.
        // Let's modify processPayment signature in the Gateway class or pass data via request.
        $request->merge(['conversion_data' => $conversionData]);
        
        // We need to pass the converted amount/currency to the gateway
        // Let's modify the gateway call to accept these. 
        // Since I cannot modify PaypalGateway right now without another tool call, 
        // I will assume PaypalGateway parses request inputs if present, OR I'll modify PaypalGateway next.
        // Actually, PaypalGateway::processPayment() uses $order->total_amount.
        // So I should probably update the Gateway class first to be more flexible.
        // But to save steps, I will modify PaypalGateway.php right after this to respect 'amount' and 'currency' in request if present.

        $result = $this->gateway->processPayment($request, $amount, $currency, $conversionData);

        return response()->json($result);
    }

    /**
     * Capture the approved PayPal payment.
     */
    public function capturePayment(Request $request): JsonResponse
    {
        $request->validate([
            'paypal_order_id' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Verify the order belongs to the current user
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $result = $this->gateway->capturePayment($request->paypal_order_id);

        if ($result['success']) {
            // Retrieve conversion data from order metadata
            $metadata = $order->metadata ?? [];
            $conversionData = $metadata['paypal_conversion'] ?? [];

            // Update order status
            $order->update([
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);

            // Create transaction record
            $payload = array_merge($result, ['conversion' => $conversionData]);
            
            UserTransaction::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'gateway' => 'paypal',
                'transaction_ref' => $result['transaction_id'],
                'amount' => $order->total_amount, // ALWAYS record original amount
                'status' => 'success',
                'payload' => $payload, // Store full details including conversion
            ]);

            // Fire event for post-payment processing (e.g., license activation)
            event(new \App\Events\OrderCompleted($order));

            return response()->json([
                'success' => true,
                'redirect_url' => route('checkout.success', ['code' => $order->code]),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Payment capture failed',
        ], 400);
    }
}
