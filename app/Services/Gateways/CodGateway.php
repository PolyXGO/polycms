<?php

namespace App\Services\Gateways;

use App\Models\Ecommerce\Order;

class CodGateway
{
    /**
     * Handle payment creation.
     */
    public function createPayment(Order $order): array
    {
        // For COD, we just acknowledge the order.
        return [
            'success' => true,
            'payment_method' => 'cod',
            'message' => 'Order placed successfully. Please pay upon delivery.',
            'redirect_url' => route('checkout.success', ['code' => $order->code]),
            'is_offline' => true,
        ];
    }
}
