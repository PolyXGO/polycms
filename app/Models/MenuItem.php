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
     * Get the effective URL (either custom URL or linked entity URL)
     */
    public function getEffectiveUrlAttribute(): ?string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->linkable) {
            // Return URL based on linkable type
            return match ($this->linkable_type) {
                Post::class => route('posts.show', $this->linkable->slug),
                Product::class => route('products.show', $this->linkable->slug),
                Category::class => route('categories.show', $this->linkable->slug),
                default => null,
            };
        }

        return null;
    }
}
