<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserTransaction extends Model
{
    protected $fillable = [
        'user_id', 'order_id', 'gateway', 'transaction_ref',
        'amount', 'status', 'payload'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
