<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'size',
        'type',
        'alt_text',
        'caption',
        'description',
        'metadata',
        'width',
        'height',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected $appends = ['url', 'thumbnail_url', 'featured_url'];

    /**
     * Get the user that uploaded the media
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get products that use this media
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_media', 'media_id', 'product_id')
            ->withPivot('is_primary', 'order');
    }

    /**
     * Get the full URL to the media file
     */
    public function getUrlAttribute(): string
    {
        // Use asset() to generate proper URL with current app URL
        // For public disk, files are in storage/app/public and symlinked to public/storage
        if ($this->disk === 'public') {
            // Use request()->getSchemeAndHttpHost() to get current domain
            $baseUrl = request()->getSchemeAndHttpHost();
            return $baseUrl . '/storage/' . $this->path;
        }
        
        // For private disk, use API route to serve the file
        if ($this->disk === 'local' || $this->disk === 'private') {
            $baseUrl = request()->getSchemeAndHttpHost();
            return $baseUrl . '/api/v1/media/' . $this->id . '/serve';
        }
        
        // For other disks, use Storage URL
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the thumbnail URL (Proportional WebP)
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->type !== 'image') {
            return $this->url;
        }

        $thumbPath = $this->metadata['thumbnails']['thumb'] ?? null;
        if ($thumbPath && Storage::disk($this->disk)->exists($thumbPath)) {
            return request()->getSchemeAndHttpHost() . '/storage/' . $thumbPath;
        }

        // On-the-fly thumbnail generation for older images
        try {
            $mediaService = app(\App\Services\MediaService::class);
            $thumbs = $mediaService->generateThumbnails($this);
            if (!empty($thumbs['thumb'])) {
                return request()->getSchemeAndHttpHost() . '/storage/' . $thumbs['thumb'];
            }
        } catch (\Throwable $e) {
            // Ignore fallback errors
        }

        return $this->url;
    }

    /**
     * Get the featured image URL (Proportional WebP)
     */
    public function getFeaturedUrlAttribute(): string
    {
        if ($this->type !== 'image') {
            return $this->url;
        }

        $featPath = $this->metadata['thumbnails']['featured'] ?? null;
        if ($featPath && Storage::disk($this->disk)->exists($featPath)) {
            return request()->getSchemeAndHttpHost() . '/storage/' . $featPath;
        }

        return $this->thumbnail_url;
    }

    /**
     * Scope for images only
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Check if media is an image
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }
}
