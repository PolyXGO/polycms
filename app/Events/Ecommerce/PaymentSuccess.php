<?php

namespace App\Events\Ecommerce;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\UserTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccess
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public UserTransaction $transaction;
    public array $payload;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param UserTransaction $transaction
     * @param array $payload
     */
    public function __construct(Order $order, UserTransaction $transaction, array $payload = [])
    {
        $this->order = $order;
        $this->transaction = $transaction;
        $this->payload = $payload;
    }
}
