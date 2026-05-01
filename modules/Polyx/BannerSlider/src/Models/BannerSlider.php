<?php

declare(strict_types=1);

namespace Modules\Polyx\BannerSlider\Models;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BannerSlider extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'banner_sliders';

    protected $fillable = [
        'title',
        'type',
        'image_id',
        'link',
        'link_target',
        'link_rel',
        'order',
        'active',
        'start_date',
        'end_date',
        'description',
        'content',
        'button_text',
        'button_link',
        'button_target',
        'button_rel',
        'background_color',
        'button_bg_color',
        'button_text_color',
        'text_color',
        'gradient_color',
        'gradient_degree',
        'button_gradient_color',
        'button_gradient_degree',
        'button_hover_bg_color',
        'button_hover_text_color',
        'button_hover_gradient_color',
        'countdown_enabled',
        'countdown_date',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'order' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'gradient_degree' => 'integer',
            'button_gradient_degree' => 'integer',
            'countdown_enabled' => 'boolean',
            'countdown_date' => 'datetime',
        ];
    }

    /**
     * Get the image media
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /**
     * Scope for active banners
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for scheduled banners (respecting date ranges)
     */
    public function scopeScheduled($query)
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $now);
        });
    }

    /**
     * Scope for ordered banners
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Check if banner is currently active (considering dates and active status)
     */
    public function isActive(): bool
    {
        if (!$this->active) {
            return false;
        }

        $now = now();

        // Check start date
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        // Check end date
        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }
}
