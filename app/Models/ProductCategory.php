<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Traits\HasDynamicImageUrls;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes, HasDynamicImageUrls;

    /**
     * Get the image URL, fixing the host if necessary
     */
    public function getImageAttribute($value): ?string
    {
        return $this->fixImageUrl($value);
    }

    protected $table = 'product_categories';

    protected $fillable = [
        'name',
        'slug',
        'summary',
        'description',
        'parent_id',
        'path',
        'depth',
        'order',
        'image',
        'products_count',
        'template_theme',
    ];

    protected $appends = ['frontend_url'];

    /**
     * Get the frontend URL for the product category
     */
    public function getFrontendUrlAttribute(): string
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        
        $base = trim($permalinks['product_categories']['single'] ?? 'product-categories', '/');
        
        return '/' . $base . '/' . $this->slug;
    }

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'depth' => 'integer',
            'products_count' => 'integer',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->updatePathAndDepth();
        });

        static::updating(function ($category) {
            if ($category->isDirty('parent_id')) {
                $category->updatePathAndDepth();
            }
        });

        static::saved(function ($category) {
            // Update path for all descendants if parent changed
            if ($category->wasChanged('path')) {
                $category->updateDescendantsPath();
            }
        });
    }

    /**
     * Get the parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**
     * Get all ancestors (parent, grandparent, etc.)
     */
    public function ancestors(): Collection
    {
        if (!$this->path) {
            return collect([]);
        }

        $ids = explode('/', $this->path);
        // Remove current category ID
        array_pop($ids);

        if (empty($ids)) {
            return collect([]);
        }

        return static::whereIn('id', $ids)
            ->orderBy('depth')
            ->get();
    }

    /**
     * Get direct child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')
            ->orderBy('order')
            ->orderBy('name');
    }

    /**
     * Get all descendant categories (children, grandchildren, etc.)
     */
    public function descendants(): Collection
    {
        if (!$this->path) {
            return collect([]);
        }

        return static::where('path', 'like', $this->path . '/%')
            ->orderBy('depth')
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all descendant IDs (for query optimization)
     */
    public function descendantIds(): array
    {
        if (!$this->path) {
            return [];
        }

        return static::where('path', 'like', $this->path . '/%')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get sibling categories (same parent)
     */
    public function siblings(): Collection
    {
        return static::where('id', '!=', $this->id)
            ->where('parent_id', $this->parent_id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get root categories (no parent)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('order')->orderBy('name');
    }

    /**
     * Scope for categories at specific depth
     */
    public function scopeAtDepth($query, int $depth)
    {
        return $query->where('depth', $depth);
    }

    /**
     * Scope for ancestors of a category
     */
    public function scopeAncestorsOf($query, ProductCategory $category)
    {
        if (!$category->path) {
            return $query->whereRaw('1 = 0'); // Empty result
        }

        $ids = explode('/', $category->path);
        array_pop($ids); // Remove current category

        return $query->whereIn('id', $ids)
            ->orderBy('depth');
    }

    /**
     * Scope for descendants of a category
     */
    public function scopeDescendantsOf($query, ProductCategory $category)
    {
        if (!$category->path) {
            return $query->where('id', $category->id);
        }

        return $query->where('path', 'like', $category->path . '/%')
            ->orWhere('id', $category->id);
    }

    /**
     * Get categories in tree structure (for display)
     */
    public static function getTree(?int $maxDepth = null): Collection
    {
        $query = static::query();

        if ($maxDepth !== null) {
            $query->where('depth', '<=', $maxDepth);
        }

        $categories = $query->orderBy('depth')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return static::buildTree($categories);
    }

    /**
     * Build tree structure from flat collection
     */
    protected static function buildTree(Collection $categories): Collection
    {
        $tree = collect([]);
        $indexed = [];

        // Index all categories by ID
        foreach ($categories as $category) {
            $indexed[$category->id] = $category;
            $category->setRelation('children', collect([]));
        }

        // Build tree
        foreach ($categories as $category) {
            if ($category->parent_id && isset($indexed[$category->parent_id])) {
                $indexed[$category->parent_id]->children->push($category);
            } else {
                $tree->push($category);
            }
        }

        return $tree;
    }

    /**
     * Get products in this category (and optionally descendants)
     */
    public function products(bool $includeDescendants = false): BelongsToMany
    {
        $query = $this->belongsToMany(Product::class, 'product_category', 'category_id', 'product_id');

        if ($includeDescendants) {
            $categoryIds = array_merge([$this->id], $this->descendantIds());
            $query->whereIn('category_id', $categoryIds);
        }

        return $query;
    }

    /**
     * Get total products count including descendants
     */
    public function getTotalProductsCountAttribute(): int
    {
        $categoryIds = array_merge([$this->id], $this->descendantIds());

        return DB::table('product_category')
            ->whereIn('category_id', $categoryIds)
            ->distinct('product_id')
            ->count('product_id');
    }

    /**
     * Check if category is root
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Check if category is ancestor of another category
     */
    public function isAncestorOf(ProductCategory $category): bool
    {
        if (!$category->path || !$this->path) {
            return false;
        }

        return str_starts_with($category->path, $this->path . '/');
    }

    /**
     * Update path and depth based on parent
     */
    protected function updatePathAndDepth(): void
    {
        if ($this->parent_id) {
            $parent = static::find($this->parent_id);
            if ($parent) {
                $this->path = $parent->path ? $parent->path . '/' . $this->id : (string)$this->id;
                $this->depth = $parent->depth + 1;
            } else {
                $this->path = (string)$this->id;
                $this->depth = 0;
            }
        } else {
            $this->path = (string)$this->id;
            $this->depth = 0;
        }
    }

    /**
     * Update path for all descendants
     */
    protected function updateDescendantsPath(): void
    {
        $descendants = $this->descendants();

        foreach ($descendants as $descendant) {
            $descendant->updatePathAndDepth();
            $descendant->saveQuietly(); // Save without triggering events
        }
    }

    /**
     * Get full name with ancestors (e.g., "Parent > Child > Grandchild")
     */
    public function getFullNameAttribute(): string
    {
        $ancestors = $this->ancestors();
        $names = $ancestors->pluck('name')->toArray();
        $names[] = $this->name;

        return implode(' > ', $names);
    }
}
