<?php

namespace App\Contracts;

use App\Models\Ecommerce\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Get the gateway code/identifier.
     */
    public function getCode(): string;

    /**
     * Get the gateway display name.
     */
    public function getName(): string;

    /**
     * Check if the gateway is properly configured.
     */
    public function isConfigured(): bool;

    /**
     * Create a payment for an order.
     */
    public function createPayment(Order $order): array;

    /**
     * Process webhook callback.
     */
    public function handleWebhook(Request $request): array;

    /**
     * Verify webhook authenticity.
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Get payment status (for polling).
     */
    public function getPaymentStatus(Order $order): array;
}
