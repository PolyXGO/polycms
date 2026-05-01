<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'allow_refund',
        'refund_window_days',
        'refund_policy_note',
        'published_at',
        'scheduled_at',
        'order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'weight',
        'length',
        'width',
        'height',
        'views',
        'type',
        'handler_class',
        'layout',
        'settings',
        'template_theme',
    ];

    protected function casts(): array
    {
        return [
            'description_blocks' => 'array',
            'settings' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'manage_stock' => 'boolean',
            'stock_low_threshold' => 'integer',
            'featured' => 'boolean',
            'allow_refund' => 'boolean',
            'refund_window_days' => 'integer',
            'order' => 'integer',
            'weight' => 'decimal:2',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'views' => 'integer',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the product
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category', 'product_id', 'category_id');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(ProductBrand::class, 'product_brand', 'product_id', 'brand_id');
    }

    /**
     * Get the tags for the product
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag', 'product_id', 'tag_id');
    }

    /**
     * Get the media/images for the product
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_media', 'product_id', 'media_id')
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

    protected $appends = [
        'effective_price',
        'frontend_url',
        'effective_refund_window_days',
        'effective_refund_policy_note',
    ];

    /**
     * Get the frontend URL for the product
     */
    public function getFrontendUrlAttribute(): string
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        $productsSingleBase = trim($permalinks['products']['single'] ?? 'products', '/');
        
        return '/' . $productsSingleBase . '/' . $this->slug;
    }

    /**
     * Get the effective price (sale price if set, otherwise regular price)
     */
    public function getEffectivePriceAttribute(): float
    {
        $price = $this->sale_price ?? $this->price;
        return (float) $price;
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

    public function getEffectiveRefundWindowDaysAttribute(): ?int
    {
        if ($this->refund_window_days !== null) {
            return (int) $this->refund_window_days;
        }

        $fallback = app(\App\Services\SettingsService::class)->get('refund_policy_default_window_days', 7);
        return $fallback !== null ? (int) $fallback : null;
    }

    public function getEffectiveRefundPolicyNoteAttribute(): ?string
    {
        if (!empty($this->refund_policy_note)) {
            return (string) $this->refund_policy_note;
        }

        $fallback = app(\App\Services\SettingsService::class)->get('refund_policy_default_note', null);
        return $fallback !== null && $fallback !== '' ? (string) $fallback : null;
    }
    /**
     * Get the service configuration for the product (single)
     */
    public function service(): HasOne
    {
        return $this->hasOne(\App\Models\Ecommerce\ProductService::class);
    }

    /**
     * Get service configurations (if multiple allowed in future)
     */
    public function services(): HasMany
    {
        return $this->hasMany(\App\Models\Ecommerce\ProductService::class);
    }

    // ─── Variant Relationships ────────────────────────────────

    /**
     * Get all variants for this product
     */
    public function variants(): HasMany
    {
        return $this->hasMany(\App\Models\Ecommerce\ProductVariant::class)->orderBy('position');
    }

    /**
     * Get global variant attributes selected for this product
     */
    public function variantAttributes(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Ecommerce\ProductAttribute::class,
            'product_attribute_product',
            'product_id',
            'attribute_id'
        )->withPivot('selected_value_ids', 'position', 'is_specification')
         ->withCasts([
             'selected_value_ids' => 'array',
             'is_specification' => 'boolean'
         ])
         ->withTimestamps();
    }

    /**
     * Sycns the flat product_attribute_value_index table based on selection
     */
    public function syncAttributeValueIndex(array $attributesData): void
    {
        // 1. Delete existing indexes for this product
        \Illuminate\Support\Facades\DB::table('product_attribute_value_index')
            ->where('product_id', $this->id)
            ->delete();

        // 2. Build flat entries
        $inserts = [];
        foreach ($attributesData as $attrData) {
            if (!isset($attrData['attribute_id']) || empty($attrData['selected_value_ids'])) {
                continue;
            }
            foreach ($attrData['selected_value_ids'] as $valueId) {
                if (!$valueId) continue;
                $inserts[] = [
                    'product_id' => $this->id,
                    'attribute_id' => (int) $attrData['attribute_id'],
                    'attribute_value_id' => (int) $valueId,
                ];
            }
        }

        // 3. Batch insert
        if (!empty($inserts)) {
            \Illuminate\Support\Facades\DB::table('product_attribute_value_index')->insert($inserts);
        }
    }


    /**
     * Get only active variants
     */
    public function activeVariants(): HasMany
    {
        return $this->hasMany(\App\Models\Ecommerce\ProductVariant::class)
            ->where('is_active', true)
            ->orderBy('position');
    }

    /**
     * Check if product is a variable type (has variants)
     */
    public function isVariable(): bool
    {
        return $this->type === 'variable';
    }

    /**
     * Get price range for variable products
     * Returns ['min' => 19.99, 'max' => 49.99] or null for simple products
     */
    public function getPriceRangeAttribute(): ?array
    {
        if (!$this->isVariable()) {
            return null;
        }

        $variants = $this->relationLoaded('activeVariants')
            ? $this->activeVariants
            : $this->activeVariants()->get();

        if ($variants->isEmpty()) {
            return null;
        }

        $prices = $variants
            ->map(fn($v) => $v->effective_price)
            ->filter(fn($p) => $p > 0);

        if ($prices->isEmpty()) {
            return null;
        }

        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
        ];
    }
}
