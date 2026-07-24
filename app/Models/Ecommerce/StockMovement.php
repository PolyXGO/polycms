<?php

namespace App\Models\Ecommerce;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'location_code',
        'movement_type',
        'direction',
        'quantity',
        'quantity_signed',
        'before_qty',
        'after_qty',
        'reference_type',
        'reference_id',
        'order_id',
        'order_item_id',
        'user_id',
        'reason_code',
        'note',
        'idempotency_key',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'direction' => 'integer',
        'quantity' => 'integer',
        'quantity_signed' => 'integer',
        'before_qty' => 'integer',
        'after_qty' => 'integer',
        'reference_id' => 'integer',
        'order_id' => 'integer',
        'order_item_id' => 'integer',
        'user_id' => 'integer',
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
