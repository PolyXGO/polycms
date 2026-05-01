<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LayoutAsset extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'kind',
        'name',
        'slug',
        'key',
        'category',
        'description',
        'layout',
        'content_raw',
        'content_html',
        'preview_image',
        'meta',
        'applies_to',
        'is_system',
        'source_type',
        'source_name',
    ];

    protected function casts(): array
    {
        return [
            'content_raw' => 'array',
            'meta' => 'array',
            'applies_to' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'layout_template_id');
    }

    public function scopeParts($query)
    {
        return $query->where('kind', 'part');
    }

    public function scopeTemplates($query)
    {
        return $query->where('kind', 'template');
    }

    public function isPart(): bool
    {
        return $this->kind === 'part';
    }

    public function isTemplate(): bool
    {
        return $this->kind === 'template';
    }
}
