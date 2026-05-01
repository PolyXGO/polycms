<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasDynamicImageUrls;

class Post extends Model
{
    use HasFactory, SoftDeletes, HasDynamicImageUrls;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'type',
        'status',
        'excerpt',
        'content_raw',
        'content_html',
        'published_at',
        'scheduled_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'featured_image',
        'views',
        'order',
        'layout',
        'layout_template_id',
        'show_featured_image',
        'template_theme',
    ];

    protected function casts(): array
    {
        return [
            'content_raw' => 'array',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'views' => 'integer',
            'order' => 'integer',
            'layout_template_id' => 'integer',
            'show_featured_image' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the post
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function layoutTemplate(): BelongsTo
    {
        return $this->belongsTo(LayoutAsset::class, 'layout_template_id');
    }

    /**
     * Get the categories for the post
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_category');
    }

    /**
     * Get the tags for the post
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PostTag::class, 'post_tag', 'post_id', 'tag_id');
    }

    /**
     * Scope for published posts
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope for posts by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if post is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published'
            && ($this->published_at === null || $this->published_at <= now());
    }

    /**
     * Get the featured image URL, fixing the host if necessary
     */
    public function getFeaturedImageAttribute($value): ?string
    {
        return $this->fixImageUrl($value);
    }

    /**
     * Get the OG image URL, fixing the host if necessary
     */
    public function getOgImageAttribute($value): ?string
    {
        return $this->fixImageUrl($value);
    }

    protected $appends = ['frontend_url'];

    /**
     * Get the frontend URL for the post/page
     */
    public function getFrontendUrlAttribute(): string
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        
        if ($this->type === 'page') {
            $base = trim($permalinks['pages']['single'] ?? '', '/');
        } else {
            $base = trim($permalinks['posts']['single'] ?? 'posts', '/');
        }
        
        $path = $base ? '/' . $base . '/' . $this->slug : '/' . $this->slug;
        
        return \App\Facades\Hook::applyFilters('post.frontend_url', $path, $this);
    }

    /**
     * Get the meta data for the post
     */
    public function meta()
    {
        return $this->hasMany(PostMeta::class);
    }

    /**
     * Get a specific meta value.
     * Uses the loaded collection when available (O(1), no query) to avoid N+1.
     */
    public function getMeta(string $key, $default = null)
    {
        if ($this->relationLoaded('meta')) {
            return $this->meta->firstWhere('meta_key', $key)?->meta_value ?? $default;
        }
        // Fallback: direct query only when relation not eager-loaded
        return $this->meta()->where('meta_key', $key)->value('meta_value') ?? $default;
    }

    /**
     * Set a meta value (upsert).
     */
    public function setMeta(string $key, ?string $value): void
    {
        $this->meta()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value]
        );

        // Refresh loaded relation if it was loaded
        if ($this->relationLoaded('meta')) {
            $this->load('meta');
        }
    }

    /**
     * Delete a meta key.
     */
    public function deleteMeta(string $key): void
    {
        $this->meta()->where('meta_key', $key)->delete();

        if ($this->relationLoaded('meta')) {
            $this->load('meta');
        }
    }
}
