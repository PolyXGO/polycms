<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        'gateway',
        'level',
        'message',
        'context',
        'transaction_id',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(UserTransaction::class, 'transaction_id');
    }
}
