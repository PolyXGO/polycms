<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description_blocks',
        'description_html',
        'price',
        'sale_price',
        'cost_price',
        'stock_status',
        'stock_quantity',
        'manage_stock',
        'stock_low_threshold',
        'status',
        'featured',
        'order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'weight',
        'length',
        'width',
        'height',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'description_blocks' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'manage_stock' => 'boolean',
            'stock_low_threshold' => 'integer',
            'featured' => 'boolean',
            'order' => 'integer',
            'weight' => 'decimal:2',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'views' => 'integer',
        ];
    }

    /**
     * Get the user that owns the product
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the categories for the product
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    /**
     * Get the tags for the product
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag');
    }

    /**
     * Get the media/images for the product
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class)
            ->withPivot('is_primary', 'order')
            ->orderByPivot('order');
    }

    /**
     * Get the primary image
     */
    public function primaryImage(): ?Media
    {
        return $this->media()->wherePivot('is_primary', true)->first();
    }

    /**
     * Scope for published products
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for in stock products
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }

    /**
     * Get the effective price (sale price if set, otherwise regular price)
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Check if product is on sale
     */
    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        if (!$this->manage_stock) {
            return $this->stock_status === 'in_stock';
        }

        return $this->stock_status === 'in_stock' && $this->stock_quantity > 0;
    }
}
