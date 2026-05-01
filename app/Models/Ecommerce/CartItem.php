<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'metadata' => 'array',
        ];
    }

    // ─── Relationships ───────────────────────────────────────

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // ─── Accessors ───────────────────────────────────────────

    /**
     * Get unit price from DB (SECURITY: never trust client price)
     */
    public function getUnitPriceAttribute(): float
    {
        if ($this->variant) {
            return $this->variant->effective_price;
        }

        return (float) ($this->product?->effective_price ?? 0);
    }

    /**
     * Get line total
     */
    public function getLineTotalAttribute(): float
    {
        return round($this->unit_price * $this->quantity, 2);
    }

    /**
     * Check stock availability
     */
    public function isInStock(): bool
    {
        $stockEntity = $this->variant ?? $this->product;

        if (!$stockEntity) {
            return false;
        }

        if (!$stockEntity->manage_stock) {
            return $stockEntity->stock_status === 'in_stock';
        }

        return $stockEntity->stock_quantity >= $this->quantity;
    }
}
