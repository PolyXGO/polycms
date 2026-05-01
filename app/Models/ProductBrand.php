<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasDynamicImageUrls;

class ProductBrand extends Model
{
    use HasFactory, SoftDeletes, HasDynamicImageUrls;

    /**
     * Get the image URL, fixing the host if necessary
     */
    public function getImageAttribute($value): ?string
    {
        return $this->fixImageUrl($value);
    }

    protected $table = 'product_brands';

    protected $fillable = [
        'name',
        'slug',
        'summary',
        'description',
        'parent_id',
        'order',
        'image',
        'is_featured',
        'products_count',
    ];

    protected $appends = ['frontend_url'];

    /**
     * Get the frontend URL for the brand
     */
    public function getFrontendUrlAttribute(): string
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        
        $base = trim($permalinks['product_brands']['single'] ?? 'product-brands', '/');
        
        return '/' . $base . '/' . $this->slug;
    }

    /**
     * Get the parent brand
     */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'parent_id');
    }

    /**
     * Get direct child brands
     */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductBrand::class, 'parent_id')
            ->orderBy('order')
            ->orderBy('name');
    }

    /**
     * Get products for this brand
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_brand', 'brand_id', 'product_id');
    }

    /**
     * Get brands in tree structure (for display)
     */
    public static function getTree(): \Illuminate\Support\Collection
    {
        $brands = static::query()
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return static::buildTree($brands);
    }

    /**
     * Build tree structure from flat collection
     */
    protected static function buildTree(\Illuminate\Support\Collection $brands): \Illuminate\Support\Collection
    {
        $tree = collect([]);
        $indexed = [];

        // Index all brands by ID
        foreach ($brands as $brand) {
            $indexed[$brand->id] = $brand;
            $brand->setRelation('children', collect([]));
        }

        // Build tree
        foreach ($brands as $brand) {
            if ($brand->parent_id && isset($indexed[$brand->parent_id])) {
                $indexed[$brand->parent_id]->children->push($brand);
            } else {
                $tree->push($brand);
            }
        }

        return $tree;
    }
}
