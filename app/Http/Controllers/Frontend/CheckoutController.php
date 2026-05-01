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
     */
    public function index(Request $request): Response
    {
        // We can pass initial data like user addresses if logged in
        $user = $request->user();
        
        Inertia::setRootView('layouts.inertia');
        return Inertia::render('Checkout', [
            'user' => $user,
            'gateways' => \App\Models\Ecommerce\PaymentGateway::where('is_active', true)->get(),
        ]);
    }

    public function success(Request $request, $code): Response
    {
        $order = \App\Models\Ecommerce\Order::where('code', $code)->firstOrFail();
        
        // Security check: ensure user owns order if logged in
        // if ($request->user() && $order->user_id !== $request->user()->id) abort(403);
        
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
