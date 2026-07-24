<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class ProductCoupon extends Model
{
    protected $fillable = [
        'code', 'title', 'description',
        'type', 'value',
        'min_order_value', 'max_discount_value',
        'usage_limit', 'usage_limit_per_user', 'usage_count',
        'scope_config', 'restricted_emails', 'starts_at', 'expires_at', 'is_active',
        'is_exclusive', 'is_public'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_discount_value' => 'decimal:2',
        'scope_config' => 'array',
        'restricted_emails' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_exclusive' => 'boolean',
        'is_public' => 'boolean',
    ];
}
