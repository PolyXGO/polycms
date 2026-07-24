<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttributeGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'group_id')->orderBy('position');
    }
}
