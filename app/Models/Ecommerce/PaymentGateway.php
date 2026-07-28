<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code', 'name', 'description', 'is_active',
        'config', 'handler_class', 'sort_order', 'is_default', 'logo'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'config' => 'array',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'sort_order' => 0,
        'is_default' => false,
    ];
}

