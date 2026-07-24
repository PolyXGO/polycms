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
use App\Traits\HasTranslations;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

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
        'locale',
        'translation_group_id',
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
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::saved(function ($product) {
            if ($product->translation_group_id) {
                // Sync physical and inventory fields across all translations
                $syncFields = [
                    'manage_stock', 'stock_quantity', 'stock_status', 'stock_low_threshold',
                    'sku', 'weight', 'length', 'width', 'height'
                ];
                $changes = [];
                foreach ($syncFields as $field) {
                    if ($product->wasChanged($field)) {
                        $changes[$field] = $product->getAttribute($field);
                    }
                }

                if (!empty($changes)) {
                    // Update all other translations quietly to avoid infinite recursion
                    static::withoutGlobalScope('locale')
                        ->where('translation_group_id', $product->translation_group_id)
                        ->where('id', '!=', $product->id)
                        ->get()
                        ->each(function ($trans) use ($changes) {
                            $trans->fill($changes)->saveQuietly();
                        });
                }
            }
        });
    }

    /**
     * Get the user that owns the product
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for the product
     */
    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Ecommerce\OrderItem::class, 'product_id');
    }

    /**
     * Get the total sales count of the product
     */
    public function getSalesCountAttribute(): int
    {
        $localSales = (int) $this->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'failed']);
            })
            ->sum('quantity');

        $externalSales = (int) data_get($this->settings, 'external_sales', 0);
        $salesOffset = (int) data_get($this->settings, 'sales_offset', 0);

        return $localSales + $externalSales + $salesOffset;
    }

    /**
     * Get the merged average rating of the product
     */
    public function getAvgRatingAttribute($value): float
    {
        $localAvg = (float) $value;
        $localCount = (int) ($this->attributes['review_count'] ?? 0);

        $externalAvg = (float) data_get($this->settings, 'external_rating', 0);
        $externalCount = (int) data_get($this->settings, 'external_rating_count', 0);

        // Fallback: If external rating is 0.0 but external count is > 0,
        // compute the average rating of the market reviews from the static registry.
        if ($externalAvg === 0.0 && $externalCount > 0) {
            $envatoId = data_get($this->settings, 'envato_item_id');
            if ($envatoId) {
                $staticReviews = \Modules\Polyx\MarketIntegration\Services\MarketService::getStaticReviews((string) $envatoId);
                if (!empty($staticReviews)) {
                    $ratingsSum = array_sum(array_column($staticReviews, 'rating'));
                    $externalAvg = $ratingsSum / count($staticReviews);
                }
            }
        }

        $totalCount = $localCount + $externalCount;
        if ($totalCount === 0) {
            return 0.0;
        }

        $mergedAvg = (($localAvg * $localCount) + ($externalAvg * $externalCount)) / $totalCount;
        return round($mergedAvg, 1);
    }

    /**
     * Get the merged review/rating count of the product
     */
    public function getReviewCountAttribute($value): int
    {
        $localCount = (int) $value;
        $externalCount = (int) data_get($this->settings, 'external_rating_count', 0);
        return $localCount + $externalCount;
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
        'featured_image_url',
    ];

    /**
     * Get the featured image URL (primary image, first media, setting default, or filtered fallback)
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        $primary = $this->primaryImage() ?? ($this->relationLoaded('media') ? $this->media->first() : $this->media()->first());
        $imageUrl = $primary?->featured_url ?? $primary?->thumbnail_url ?? $primary?->url;

        if (empty($imageUrl)) {
            $defaultImage = app(\App\Services\SettingsService::class)->get('ecommerce_default_product_image');
            if ($defaultImage && $defaultImage !== '') {
                $imageUrl = (string) $defaultImage;
            }
        }

        $processedUrl = media_url($imageUrl, 'featured');
        $filteredUrl = \App\Facades\Hook::applyFilters('product.featured_image_url', $processedUrl ?: null, $this);
        return $filteredUrl ?: null;
    }

    /**
     * Get thumbnail URL (150x150 max WebP)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $primary = $this->primaryImage() ?? ($this->relationLoaded('media') ? $this->media->first() : $this->media()->first());
        $imageUrl = $primary?->thumbnail_url ?? $primary?->url;

        if (empty($imageUrl)) {
            $defaultImage = app(\App\Services\SettingsService::class)->get('ecommerce_default_product_image');
            if ($defaultImage && $defaultImage !== '') {
                $imageUrl = (string) $defaultImage;
            }
        }

        return media_url($imageUrl, 'thumb') ?: null;
    }

    /**
     * Get the frontend URL for the product
     */
    public function getFrontendUrlAttribute(): string
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        $productsSingleBase = trim($permalinks['products']['single'] ?? 'products', '/');
        
        $url = '/' . $productsSingleBase . '/' . $this->slug;
        return \App\Facades\Hook::applyFilters('product.frontend_url', $url, $this);
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

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(\Modules\Polyx\ProjectHub\Models\Project::class, 'project_products', 'product_id', 'project_id')
            ->withPivot(['label', 'is_primary', 'order'])
            ->withTimestamps();
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
