<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'attribute_values',
        'price',
        'sale_price',
        'cost_price',
        'stock_quantity',
        'stock_status',
        'manage_stock',
        'weight',
        'image_id',
        'is_active',
        'is_default',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'attribute_values' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'manage_stock' => 'boolean',
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'position' => 'integer',
        ];
    }

    protected $appends = ['effective_price', 'display_name'];

    // ─── Relationships ───────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    // ─── Accessors ───────────────────────────────────────────

    /**
     * Price inheritance: variant sale_price > variant price > parent product price
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->sale_price !== null && (float) $this->sale_price > 0) {
            return (float) $this->sale_price;
        }
        if ($this->price !== null && (float) $this->price > 0) {
            return (float) $this->price;
        }

        // Inherit from parent product
        return (float) ($this->product?->effective_price ?? 0);
    }

    /**
     * Human-readable display: "Red / XL"
     */
    public function getDisplayNameAttribute(): string
    {
        $values = $this->attribute_values ?? [];
        return implode(' / ', array_values($values));
    }

    // ─── Business Logic ──────────────────────────────────────

    /**
     * Check if variant is available for purchase
     */
    public function isInStock(): bool
    {
        if (!$this->manage_stock) {
            return $this->stock_status === 'in_stock';
        }

        return $this->stock_status === 'in_stock' && $this->stock_quantity > 0;
    }

    /**
     * Check if variant is on sale
     */
    public function isOnSale(): bool
    {
        return $this->sale_price !== null
            && (float) $this->sale_price > 0
            && (float) $this->sale_price < (float) ($this->price ?? $this->product?->price ?? 0);
    }

    // ─── Scopes ──────────────────────────────────────────────

    /**
     * Only active variants (SECURITY: filter out disabled)
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Only in-stock variants
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }
}
