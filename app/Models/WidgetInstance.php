<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WidgetInstance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'widget_area_id',
        'widget_type',
        'title',
        'config',
        'order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'order' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the widget area this instance belongs to
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(WidgetArea::class, 'widget_area_id');
    }
}
