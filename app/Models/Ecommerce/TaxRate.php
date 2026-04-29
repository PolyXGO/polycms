<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'name', 'country', 'state', 'rate',
        'is_compound', 'is_active', 'priority',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get rate as percentage string: "10%"
     */
    public function getPercentageAttribute(): string
    {
        return round((float) $this->rate * 100, 2) . '%';
    }
}
