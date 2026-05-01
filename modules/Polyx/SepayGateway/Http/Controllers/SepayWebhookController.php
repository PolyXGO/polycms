<?php

namespace Modules\Polyx\SepayGateway\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Polyx\SepayGateway\SepayGateway;

class SepayWebhookController extends Controller
{
    protected SepayGateway $gateway;

    public function __construct()
    {
        $this->gateway = new SepayGateway();
    }

    /**
     * Handle webhook callback from SePay.
     * Route: POST /api/webhooks/sepay
     */
    public function handle(Request $request): JsonResponse
    {
        // Verify webhook authenticity
        if (!$this->gateway->verifyWebhook($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Log webhook for debugging
        \Log::info('SePay Webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Process webhook
        $result = $this->gateway->handleWebhook($request);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        // Return 204 for non-matching orders (SePay expects this)
        return response()->json($result, 204);
    }

    /**
     * Ping endpoint for testing webhook accessibility.
     * Route: GET /api/webhooks/sepay/ping
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'SePay webhook endpoint is accessible',
            'timestamp' => now()->toIso8601String(),
            'webhook_url' => route('sepay.webhook'),
        ]);
    }

    /**
     * Check payment status (for frontend polling).
     * Route: GET /api/sepay/status/{order}
     */
    public function checkStatus(Order $order): JsonResponse
    {
        // Verify ownership
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json($this->gateway->getPaymentStatus($order));
    }
}
