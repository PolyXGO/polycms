<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'service_id',
        'name', 'variant_label', 'sku', 'quantity', 'refunded_qty', 'price', 'total', 'metadata'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'refunded_qty' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function service()
    {
        return $this->belongsTo(ProductService::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
