<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'code', 'total_amount', 'subtotal_amount', 'currency',
        'discount_code', 'discount_amount', 'tax_amount', 'refunded_total',
        'status', 'payment_method', 'payment_status',
        'refund_status', 'refund_reason', 'refunded_at', 'last_refunded_at', 'refund_transaction_ref',
        'customer_note', 'billing_address', 'shipping_address', 'guest_email',
        'shipping_amount', 'shipping_method', 'tracking_number', 'tracking_url',
        'shipped_at', 'delivered_at', 'admin_note',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'refunded_total' => 'decimal:2',
        'refunded_at' => 'datetime',
        'last_refunded_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'billing_address' => 'array',
        'shipping_address' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(UserTransaction::class);
    }

    public function invoices()
    {
        return $this->hasMany(OrderInvoice::class);
    }

    public function coupons()
    {
        return $this->hasMany(OrderCoupon::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function notes()
    {
        return $this->hasMany(OrderNote::class)->orderByDesc('created_at');
    }
}
