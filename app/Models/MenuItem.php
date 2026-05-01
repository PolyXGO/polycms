<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Post;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'type',
        'linkable_id',
        'linkable_type',
        'target',
        'icon',
        'css_class',
        'order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the menu this item belongs to
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get child menu items
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get the linked entity (polymorphic)
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the effective URL (dynamically resolved for linked entities, or custom URL)
     *
     * For linkable items (post, page, product, category), the URL is always resolved
     * dynamically from the linked entity so that permalink changes are reflected immediately.
     * The stored 'url' field is only used for 'custom' type items.
     */
    public function getEffectiveUrlAttribute(): ?string
    {
        // For linkable items, always resolve dynamically
        if ($this->linkable_type) {
            if ($this->linkable_id) {
                // Single entity
                $entity = $this->linkable;
                if ($entity) {
                    $dynamicUrl = match ($this->linkable_type) {
                        Post::class => $entity->frontend_url,
                        Product::class => $entity->frontend_url ?? null,
                        Category::class => $entity->frontend_url ?? null,
                        default => null,
                    };

                    if ($dynamicUrl) {
                        return $dynamicUrl;
                    }
                }
            } else {
                // Archive logic: type is set (e.g. Post, Product) but no specific ID
                if ($this->linkable_type === Post::class || $this->type === 'post') {
                    return url(trim(theme_permalink_structure()['posts']['archive'] ?? 'posts', '/'));
                }
                if ($this->linkable_type === Product::class || $this->type === 'product') {
                    return url(trim(theme_permalink_structure()['products']['archive'] ?? 'products', '/'));
                }
            }
        }

        // Also handle cases where type is set directly but linkable_type might be empty
        if (!$this->linkable_id) {
            if ($this->type === 'post') {
                return url(trim(theme_permalink_structure()['posts']['archive'] ?? 'posts', '/'));
            }
            if ($this->type === 'product') {
                return url(trim(theme_permalink_structure()['products']['archive'] ?? 'products', '/'));
            }
        }

        // Fallback to stored custom URL
        $url = $this->url ?: null;

        return $url;
    }
}
