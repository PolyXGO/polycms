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

    protected $appends = ['url'];

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
