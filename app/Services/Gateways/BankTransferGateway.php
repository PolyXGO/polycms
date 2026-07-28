<?php

namespace App\Services\Gateways;

use App\Models\Ecommerce\Order;

class BankTransferGateway
{
    /**
     * Handle payment creation.
     */
    public function createPayment(Order $order): array
    {
        // For Bank Transfer, we provide instructions.
        return [
            'success' => true,
            'payment_method' => 'bank_transfer',
            'message' => 'Order placed successfully. Please proceed with bank transfer.',
            'redirect_url' => route('checkout.success', ['code' => $order->code]),
            'is_offline' => true,
        ];
    }
}
