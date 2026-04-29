<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id', 'service_id', 'product_id',
        'starts_at', 'expires_at', 'status',
        'is_auto_renew', 'gateway_profile_id', 'renewed_from_subscription_id'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_auto_renew' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(ProductService::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
