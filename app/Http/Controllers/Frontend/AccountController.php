<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Display the user's order history.
     */
    public function orders(Request $request): Response
    {
        $orders = \App\Models\Ecommerce\Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->with(['items.product.media']) 
            ->paginate(10);
            
        return Inertia::render('Account/OrderList', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display order details.
     */
    public function orderDetail($code): Response
    {
        $order = \App\Models\Ecommerce\Order::where('code', $code)
            ->where('user_id', Auth::id())
            ->with(['items.product.media', 'items.service'])
            ->firstOrFail();

        // Fetch Gateways for instructions
        $bankGateway = \App\Models\Ecommerce\PaymentGateway::where('code', 'bank_transfer')->first();
        $codGateway = \App\Models\Ecommerce\PaymentGateway::where('code', 'cod')->first();

        return Inertia::render('Account/OrderDetail', [
            'order' => $order,
            'bank_transfer_config' => $bankGateway ? $bankGateway->config : null,
            'cod_config' => $codGateway ? $codGateway->config : null,
        ]);
    }

    /**
     * Display the user's subscriptions.
     */
    public function subscriptions(Request $request): Response
    {
        $subscriptions = \App\Models\Ecommerce\UserSubscription::where('user_id', Auth::id())
            ->with(['product', 'service'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Account/SubscriptionList', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Display the user's licenses.
     */
    public function licenses(Request $request): Response
    {
        $licenses = \App\Models\Ecommerce\ProductLicense::whereHas('subscription', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->with(['subscription.product', 'activations'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Account/LicenseList', [
            'licenses' => $licenses,
        ]);
    }
    
    public function deactivateLicense(Request $request, $id)
    {
        $manager = app(\App\Services\Ecommerce\LicenseManager::class);
        $activation = \App\Models\Ecommerce\LicenseActivation::where('id', $id)->firstOrFail();
        
        // Security: Check if user owns the license
        if ($activation->license->user_id !== Auth::id()) {
            abort(403);
        }

        $manager->deactivateLicense($id);

        return back()->with('success', 'Domain deactivated successfully.');
    }
}
