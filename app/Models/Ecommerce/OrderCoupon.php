<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_coupon_id',
        'code',
        'discount_amount'
    ];
    
    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function productCoupon()
    {
        return $this->belongsTo(ProductCoupon::class);
    }
}
