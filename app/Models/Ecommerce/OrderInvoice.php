<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class OrderInvoice extends Model
{
    protected $fillable = [
        'order_id', 'invoice_number',
        'billing_snapshot', 'total_amount', 'tax_amount',
        'file_url', 'attachments', 'template_name',
        'status', 'issued_at'
    ];

    protected $casts = [
        'billing_snapshot' => 'array',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'attachments' => 'array',
        'issued_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
