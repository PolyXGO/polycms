<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\PaymentGateway;
use App\Models\Ecommerce\UserTransaction;
use Illuminate\Support\Facades\App;

class PaymentManager
{
    public function __construct(protected OrderInventorySyncService $orderInventorySyncService) {}

    /**
     * Get instance of gateway handler.
     */
    public function getGateway($code)
    {
        $gateway = PaymentGateway::where('code', $code)->where('is_active', true)->first();
        if (!$gateway || !$gateway->handler_class) {
            throw new \Exception("Payment gateway {$code} not found or inactive.");
        }
        
        return App::make($gateway->handler_class);
    }

    /**
     * Process payment for an order.
     */
    public function processPayment(Order $order, $gatewayCode, $request)
    {
        $handler = $this->getGateway($gatewayCode);
        
        // Dispatch Processing Event
        event(new \App\Events\Ecommerce\PaymentProcessing($order, $gatewayCode, $request->all()));

        // Log attempt
        $transaction = UserTransaction::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'gateway' => $gatewayCode,
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        try {
            $response = $handler->createPayment($order);
            
            // Log success initiation (if needed, or just let the gateway handle it)
             \App\Models\Ecommerce\PaymentLog::create([
                'gateway' => $gatewayCode,
                'level' => 'info',
                'message' => 'Payment initiated',
                'context' => ['order_id' => $order->id, 'amount' => $order->total_amount],
                'transaction_id' => $transaction->id
            ]);

            // Handling response varies by gateway (redirect vs direct)
            return $response;
        } catch (\Exception $e) {
            $transaction->update(['status' => 'failed', 'payload' => ['error' => $e->getMessage()]]);
            
            // Dispatch Failed Event
            event(new \App\Events\Ecommerce\PaymentFailed($order, $gatewayCode, $e->getMessage(), ['request' => $request->all()]));

             // Log failure
             \App\Models\Ecommerce\PaymentLog::create([
                'gateway' => $gatewayCode,
                'level' => 'error',
                'message' => 'Payment processing failed: ' . $e->getMessage(),
                'context' => ['order_id' => $order->id, 'trace' => $e->getTraceAsString()],
                'transaction_id' => $transaction->id
            ]);

            throw $e;
        }
    }

    /**
     * Mark order as paid.
     */
    public function markAsPaid(Order $order, $transactionRef, $gatewayCode, $payload = [])
    {
        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $gatewayCode,
            'status' => 'processing', // Move to processing, waiting for activation
        ]);

        // Sync stock-out when payment is confirmed (idempotent)
        $this->orderInventorySyncService->syncSaleForOrder($order, null, 'payment_mark_paid');

        // Update or create transaction record
        $transaction = UserTransaction::updateOrCreate(
            ['order_id' => $order->id, 'gateway' => $gatewayCode, 'transaction_ref' => $transactionRef],
            [
                'user_id' => $order->user_id,
                'amount' => $order->total_amount,
                'status' => 'success',
                'payload' => $payload
            ]
        );

        // Log success
        \App\Models\Ecommerce\PaymentLog::create([
            'gateway' => $gatewayCode,
            'level' => 'info',
            'message' => 'Payment completed successfully',
            'context' => ['order_id' => $order->id, 'ref' => $transactionRef],
            'transaction_id' => $transaction->id
        ]);

        // Dispatch Success Event
        event(new \App\Events\Ecommerce\PaymentSuccess($order, $transaction, $payload));

        // Trigger OrderPaid event (Legacy/Existing)
        // event(new \App\Events\OrderPaid($order));
    }
}
