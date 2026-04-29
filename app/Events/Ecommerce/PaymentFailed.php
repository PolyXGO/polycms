<?php

namespace App\Events\Ecommerce;

use App\Models\Ecommerce\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public string $gateway;
    public string $errorMessage;
    public array $context;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param string $gateway
     * @param string $errorMessage
     * @param array $context
     */
    public function __construct(Order $order, string $gateway, string $errorMessage, array $context = [])
    {
        $this->order = $order;
        $this->gateway = $gateway;
        $this->errorMessage = $errorMessage;
        $this->context = $context;
    }
}
