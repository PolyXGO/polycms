<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasTranslations;

class Tag extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'locale',
        'translation_group_id',
    ];

    protected $appends = ['frontend_url'];

    /**
     * Get the frontend URL for the tag
     */
    public function getFrontendUrlAttribute(): string
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        
        if ($this->type === 'product') {
            $base = trim($permalinks['tags']['product'] ?? 'product-tags', '/');
        } else {
            $base = trim($permalinks['tags']['post'] ?? 'tags', '/');
        }
        
        $url = '/' . $base . '/' . $this->slug;
        return \App\Facades\Hook::applyFilters('tag.frontend_url', $url, $this);
    }

    /**
     * Get posts with this tag
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }

    /**
     * Get products with this tag
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tag');
    }

    /**
     * Scope for tags by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
