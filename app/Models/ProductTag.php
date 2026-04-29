<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_tags';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected $appends = ['frontend_url'];

    /**
     * Get the frontend URL for the tag
     */
    public function getFrontendUrlAttribute(): string
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        
        $base = trim($permalinks['tags']['product'] ?? 'product-tags', '/');
        
        return '/' . $base . '/' . $this->slug;
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get products with this tag
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tag', 'tag_id', 'product_id');
    }
}
