<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\UserSubscription;
use App\Models\Ecommerce\ProductLicense;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Orders
        $ordersCount = Order::where('user_id', $user->id)->count();
        $ordersTotal = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->sum('total_amount');

        // Subscriptions
        $subscriptionsCount = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        
        // Calculate total spent on subscriptions - simple approximation using recurring price for active subs
        // A more accurate way would be to sum successful transactions related to subscriptions
        // Note: UserSubscription doesn't have recurring_price directly, it belongs to product/service
        // However, for this dashboard overview, we might need to join or load relationship. 
        // For simplicity and performance in this specific query context, let's try to get it via join if possible or just use a simpler metric if join is too complex for now.
        // Actually, let's use the 'price' from related product/service.
        $subscriptionsTotal = UserSubscription::where('user_subscriptions.user_id', $user->id)
            ->where('user_subscriptions.status', 'active')
            ->join('products', 'user_subscriptions.product_id', '=', 'products.id')
            ->sum('products.price'); // Assuming recurring price is stored in product/service price for now

        // Licenses
        $licensesCount = ProductLicense::join('user_subscriptions', 'product_licenses.subscription_id', '=', 'user_subscriptions.id')
            ->where('user_subscriptions.user_id', $user->id)
            ->where('product_licenses.status', 'active')
            ->count();
            
        // Calculate total spent on licenses (assuming product price)
        // License is linked to subscription which is linked to product
        $licensesTotal = ProductLicense::join('user_subscriptions', 'product_licenses.subscription_id', '=', 'user_subscriptions.id')
            ->join('products', 'user_subscriptions.product_id', '=', 'products.id')
            ->where('user_subscriptions.user_id', $user->id)
            ->sum('products.price');


        return Inertia::render('Dashboard', [
            'statistics' => [
                'orders' => [
                    'count' => $ordersCount,
                    'total' => $ordersTotal,
                ],
                'subscriptions' => [
                    'count' => $subscriptionsCount,
                    'total' => $subscriptionsTotal,
                ],
                'licenses' => [
                    'count' => $licensesCount,
                    'total' => $licensesTotal,
                ],
            ],
        ]);
    }
}
