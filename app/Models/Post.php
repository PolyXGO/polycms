<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasDynamicImageUrls;
use App\Traits\HasTranslations;

class Post extends Model
{
    use HasFactory, SoftDeletes, HasDynamicImageUrls, HasTranslations;

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
        'show_title',
        'template_theme',
        'locale',
        'translation_group_id',
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
            'show_title' => 'boolean',
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

    /**
     * Get the effective featured image URL with optional variant sizing ('featured', 'thumb', 'full')
     */
    public function getEffectiveFeaturedImage(string $variant = 'featured'): ?string
    {
        $rawUrl = null;
        if (!empty($this->featured_image)) {
            $rawUrl = $this->featured_image;
        } else {
            // 1. Check Primary Category (Mark as Primary)
            $primaryCategory = $this->primary_category;
            if ($primaryCategory) {
                $meta = $primaryCategory->meta ?? [];
                if (!empty($meta['default_featured_image'])) {
                    $rawUrl = $meta['default_featured_image'];
                } elseif (!empty($primaryCategory->image)) {
                    $rawUrl = $primaryCategory->image;
                }
            }

            // 2. Fallback to other assigned categories if primary has no image
            if (empty($rawUrl)) {
                $categories = $this->relationLoaded('categories')
                    ? $this->categories
                    : $this->categories()->get();

                foreach ($categories as $category) {
                    if ($primaryCategory && $category->id === $primaryCategory->id) {
                        continue;
                    }
                    $meta = $category->meta ?? [];
                    if (!empty($meta['default_featured_image'])) {
                        $rawUrl = $meta['default_featured_image'];
                        break;
                    } elseif (!empty($category->image)) {
                        $rawUrl = $category->image;
                        break;
                    }
                }
            }

            // 3. Fallback to global reading default post image setting
            if (empty($rawUrl)) {
                $rawUrl = get_option('reading_default_post_image', null, 'reading');
            }
        }

        if (empty($rawUrl)) {
            return null;
        }

        $processedUrl = media_url($rawUrl, $variant);
        return \App\Facades\Hook::applyFilters('post.featured_image_url', $processedUrl ?: null, $this, $variant) ?: ($processedUrl ?: null);
    }

    /**
     * Featured image URL attribute (565x375 max WebP)
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->getEffectiveFeaturedImage('featured');
    }

    /**
     * Thumbnail image URL attribute (150x150 max WebP)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->getEffectiveFeaturedImage('thumb');
    }

    /**
     * Get the primary category for the post (falls back to first/deepest category)
     */
    public function getPrimaryCategoryAttribute(): ?Category
    {
        $primaryId = $this->getMeta('primary_category_id');
        if ($primaryId) {
            $categories = $this->relationLoaded('categories') ? $this->categories : $this->categories()->get();
            $primary = $categories->firstWhere('id', (int) $primaryId);
            if ($primary) {
                return $primary;
            }
            $found = Category::withoutGlobalScope('locale')->find((int) $primaryId);
            if ($found) {
                return $found;
            }
        }

        $categories = $this->relationLoaded('categories') ? $this->categories : $this->categories()->get();
        return $categories->sortByDesc('depth')->first() ?? $categories->first();
    }
}
