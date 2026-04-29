<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    protected $fillable = [
        'zone_id', 'name', 'type', 'cost', 'rules',
        'free_threshold', 'estimated_days', 'is_active', 'position',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'free_threshold' => 'decimal:2',
            'rules' => 'array',
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
