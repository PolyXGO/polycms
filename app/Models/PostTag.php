<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Traits\HasTranslations;

class PostTag extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $table = 'post_tags';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'locale',
        'translation_group_id',
    ];

    protected $appends = ['frontend_url'];

    /**
     * Get the frontend URL for the tag
     */
    public function getFrontendUrlAttribute(): string
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        
        $base = trim($permalinks['tags']['post'] ?? 'tags', '/');
        
        $url = '/' . $base . '/' . $this->slug;
        return \App\Facades\Hook::applyFilters('tag.frontend_url', $url, $this);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get posts with this tag
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag', 'tag_id', 'post_id');
    }
}
