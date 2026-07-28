<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'slug',
        'display_type',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'group_id' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeGroup::class, 'group_id');
    }

    /**
     * Values belonging to this attribute (e.g. Red, Blue, Black for Color)
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id')->orderBy('position');
    }

    /**
     * Products that use this attribute
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Product::class,
            'product_attribute_product',
            'attribute_id',
            'product_id'
        )->withPivot('selected_value_ids', 'position')->withTimestamps();
    }

    /**
     * Scope: ordered by position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
