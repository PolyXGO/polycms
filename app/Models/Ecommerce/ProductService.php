<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductService extends Model
{
    protected $fillable = [
        'product_id', 'code', 'name', 'price',
        'access_type', 'duration_value', 'duration_unit',
        'trial_period_days', 'is_recurring',
        'capabilities', 'license_policy'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_recurring' => 'boolean',
        'capabilities' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
