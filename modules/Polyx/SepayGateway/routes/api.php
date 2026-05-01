<?php

use Illuminate\Support\Facades\Route;
use Modules\Polyx\SepayGateway\Http\Controllers\SepayWebhookController;

/*
|--------------------------------------------------------------------------
| SePay Gateway API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')->group(function () {
    // Webhook endpoint for SePay callbacks
    // URL: /api/webhooks/sepay
    Route::post('/sepay', [SepayWebhookController::class, 'handle'])
        ->withoutMiddleware(['auth:sanctum', 'throttle:api'])
        ->name('sepay.webhook');
    
    // Ping endpoint for testing webhook accessibility
    Route::get('/sepay/ping', [SepayWebhookController::class, 'ping'])
        ->withoutMiddleware(['auth:sanctum'])
        ->name('sepay.webhook.ping');
});

// Payment status check (for frontend polling)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/sepay/status/{order}', [SepayWebhookController::class, 'checkStatus'])
        ->name('sepay.status');
});
