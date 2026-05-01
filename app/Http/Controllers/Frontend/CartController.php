<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display the cart page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        Inertia::setRootView('layouts.inertia');
        return Inertia::render('Cart', [
            'user' => $user,
            'gateways' => \App\Models\Ecommerce\PaymentGateway::where('is_active', true)->get(),
            'continueShoppingUrl' => theme_permalink_url('products', '', 'archive'),
        ]);
    }
}
