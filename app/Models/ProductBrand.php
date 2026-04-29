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
     * Get products for this brand
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_brand', 'brand_id', 'product_id');
    }
}
