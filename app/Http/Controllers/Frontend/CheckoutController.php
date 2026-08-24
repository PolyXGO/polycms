<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     *
     * @param Request $request
     * @return Response|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // We allow rendering checkout; client-side Pinia/localStorage handles cart state
        // and displays empty cart notice if needed without breaking direct checkout transitions.

        // We can pass initial data like user addresses if logged in
        $user = $request->user();
        if ($user) {
            $user->load('addresses');
        }
        
        Inertia::setRootView('layouts.inertia');
        return Inertia::render('Checkout', [
            'user' => $user,
            'gateways' => \App\Models\Ecommerce\PaymentGateway::where('is_active', true)->get(),
        ]);
    }

    public function success(Request $request, $code): Response
    {
        $order = \App\Models\Ecommerce\Order::where('code', $code)->firstOrFail();

        // Auto-fulfill $0 / free orders if not already fulfilled
        if (($order->total_amount <= 0 || $order->payment_method === 'free') && ($order->payment_status !== 'paid' || $order->status !== 'completed')) {
            $oldStatus = $order->status;
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => 'free',
                'status' => 'completed',
            ]);

            \App\Facades\Hook::doAction('order_status_updated', $order, $oldStatus, 'completed');
            app(\App\Services\Ecommerce\OrderFulfillmentService::class)->fulfillOrder($order);
            $order->refresh();
        }
        
        // Security check: ensure user owns order if logged in
        // if ($request->user() && $order->user_id !== $request->user()->id) abort(403);
        
        // Auto-capture PayPal payment if returning with token
        $paypalOrderId = $request->query('token');
        if ($paypalOrderId && $order->payment_method === 'paypal' && $order->payment_status !== 'paid') {
            try {
                $gateway = app(\Modules\Polyx\PaypalGateway\PaypalGateway::class);
                $result = $gateway->capturePayment($paypalOrderId);
                
                if ($result['success']) {
                    $metadata = $order->metadata ?? [];
                    $conversionData = $metadata['paypal_conversion'] ?? [];

                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'completed',
                    ]);

                    $payload = array_merge($result, ['conversion' => $conversionData]);
                    
                    $transaction = \App\Models\Ecommerce\UserTransaction::where('order_id', $order->id)
                        ->where('gateway', 'paypal')
                        ->where('status', 'pending')
                        ->first();

                    if ($transaction) {
                        $transaction->update([
                            'transaction_ref' => $result['transaction_id'],
                            'status' => 'success',
                            'payload' => $payload,
                        ]);
                    } else {
                        \App\Models\Ecommerce\UserTransaction::create([
                            'user_id' => $order->user_id,
                            'order_id' => $order->id,
                            'gateway' => 'paypal',
                            'transaction_ref' => $result['transaction_id'],
                            'amount' => $order->total_amount,
                            'status' => 'success',
                            'payload' => $payload,
                        ]);
                    }

                    event(new \App\Events\OrderCompleted($order));
                    $order->refresh();
                } else {
                    \Illuminate\Support\Facades\Log::error("PayPal Capture failed: " . ($result['error'] ?? 'Unknown error'));
                    session()->flash('error', $result['error'] ?? 'PayPal payment capture failed.');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to capture PayPal payment: " . $e->getMessage());
                session()->flash('error', 'An error occurred while capturing the payment: ' . $e->getMessage());
            }
        }

        Inertia::setRootView('layouts.inertia');

        // Fetch Gateways for instructions
        $bankGateway = \App\Models\Ecommerce\PaymentGateway::where('code', 'bank_transfer')->first();
        $codGateway = \App\Models\Ecommerce\PaymentGateway::where('code', 'cod')->first();

        return Inertia::render('Checkout/Success', [
            'order' => $order,
            'bank_transfer_config' => $bankGateway ? $bankGateway->config : null,
            'cod_config' => $codGateway ? $codGateway->config : null,
        ]);
    }
}
