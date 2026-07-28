<?php

use Illuminate\Support\Facades\Route;
use Modules\Polyx\PaypalGateway\Http\Controllers\PaypalPaymentController;
use Modules\Polyx\PaypalGateway\Http\Controllers\PaypalWebhookController;

/*
|--------------------------------------------------------------------------
| PayPal API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1/payment/paypal')->name('api.v1.payment.paypal.')->group(function () {
    // Protected routes (require auth)
    Route::middleware(['api', 'auth:sanctum'])->group(function () {
        Route::post('/create-order', [PaypalPaymentController::class, 'createOrder'])
            ->name('create-order');
        Route::post('/capture', [PaypalPaymentController::class, 'capturePayment'])
            ->name('capture');
    });

    // Public webhook route
    Route::post('/webhook', [PaypalWebhookController::class, 'handle'])
        ->name('webhook')
        ->withoutMiddleware(['web', 'csrf']);
});
