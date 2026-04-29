<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'regions', 'is_active', 'priority'];

    protected function casts(): array
    {
        return [
            'regions' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id')->orderBy('position');
    }

    public function activeMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id')
            ->where('is_active', true)
            ->orderBy('position');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
