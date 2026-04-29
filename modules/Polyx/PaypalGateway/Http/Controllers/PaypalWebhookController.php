<?php

namespace Modules\Polyx\PaypalGateway\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\UserTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Polyx\PaypalGateway\PaypalGateway;

class PaypalWebhookController extends Controller
{
    protected PaypalGateway $gateway;

    public function __construct()
    {
        $this->gateway = new PaypalGateway();
    }

    /**
     * Handle incoming PayPal webhook events.
     */
    public function handle(Request $request): JsonResponse
    {
        Log::info('PayPal Webhook received', [
            'event_type' => $request->input('event_type'),
        ]);

        // Verify webhook signature
        $verified = $this->gateway->verifyWebhook($request);

        if ($verified === false) {
            Log::warning('PayPal Webhook: Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $eventType = $request->input('event_type');
        $resource = $request->input('resource', []);

        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handleCaptureCompleted($resource);
                break;

            case 'PAYMENT.CAPTURE.REFUNDED':
                $this->handleCaptureRefunded($resource);
                break;

            case 'PAYMENT.CAPTURE.DENIED':
                $this->handleCaptureDenied($resource);
                break;

            case 'CHECKOUT.ORDER.APPROVED':
                // Order approved but not captured yet
                Log::info('PayPal: Order approved', ['id' => $resource['id'] ?? null]);
                break;

            default:
                Log::info('PayPal Webhook: Unhandled event type', ['type' => $eventType]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle payment capture completed event.
     */
    protected function handleCaptureCompleted(array $resource): void
    {
        $captureId = $resource['id'] ?? null;
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        if (!$orderId) {
            Log::warning('PayPal Webhook: No order ID in capture', $resource);
            return;
        }

        // Find the order by PayPal order ID stored in metadata
        $order = Order::whereJsonContains('metadata->paypal_order_id', $orderId)->first();

        if (!$order) {
            Log::warning('PayPal Webhook: Order not found', ['paypal_order_id' => $orderId]);
            return;
        }

        // Update order if not already paid
        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);

            // Check if transaction already exists
            $existingTransaction = UserTransaction::where('order_id', $order->id)
                ->where('transaction_ref', $captureId)
                ->first();

            if (!$existingTransaction) {
                UserTransaction::create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'gateway' => 'paypal',
                    'transaction_ref' => $captureId,
                    'amount' => $resource['amount']['value'] ?? $order->total_amount,
                    'status' => 'success',
                    'payload' => json_encode($resource),
                ]);
            }

            // Fire event
            event(new \App\Events\OrderCompleted($order));

            Log::info('PayPal Webhook: Order completed', ['order_code' => $order->code]);
        }
    }

    /**
     * Handle payment refund event.
     */
    protected function handleCaptureRefunded(array $resource): void
    {
        $captureId = $resource['id'] ?? null;

        // Find transaction by capture ID
        $transaction = UserTransaction::where('transaction_ref', $captureId)->first();

        if ($transaction) {
            $order = $transaction->order;
            
            if ($order) {
                $order->update([
                    'payment_status' => 'refunded',
                ]);

                // Create refund transaction record
                UserTransaction::create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'gateway' => 'paypal',
                    'transaction_ref' => $resource['id'] ?? null,
                    'amount' => -1 * ($resource['amount']['value'] ?? 0),
                    'status' => 'success',
                    'payload' => json_encode($resource),
                ]);

                Log::info('PayPal Webhook: Order refunded', ['order_code' => $order->code]);
            }
        }
    }

    /**
     * Handle payment denied event.
     */
    protected function handleCaptureDenied(array $resource): void
    {
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        if ($orderId) {
            $order = Order::whereJsonContains('metadata->paypal_order_id', $orderId)->first();

            if ($order && $order->payment_status === 'unpaid') {
                $order->update(['status' => 'failed']);
                
                Log::info('PayPal Webhook: Payment denied', ['order_code' => $order->code]);
            }
        }
    }
}
