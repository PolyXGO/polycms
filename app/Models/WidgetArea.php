<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WidgetArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'key',
        'description',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * Get widget instances in this area
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(WidgetInstance::class)->where('active', true)->orderBy('order');
    }

    /**
     * Get all widget instances including inactive
     */
    public function allWidgets(): HasMany
    {
        return $this->hasMany(WidgetInstance::class)->orderBy('order');
    }
}
