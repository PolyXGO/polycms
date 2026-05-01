<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
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
        
        return '/' . $base . '/' . $this->slug;
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
