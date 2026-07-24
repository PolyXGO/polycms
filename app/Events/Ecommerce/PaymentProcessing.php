<?php

namespace App\Events\Ecommerce;

use App\Models\Ecommerce\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProcessing
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public string $gateway;
    public array $data;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param string $gateway
     * @param array $data
     */
    public function __construct(Order $order, string $gateway, array $data = [])
    {
        $this->order = $order;
        $this->gateway = $gateway;
        $this->data = $data;
    }
}
