<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'version',
        'author',
        'description',
        'type',
        'is_active',
        'role',
        'status',
        'path',
        'screenshot',
        'meta',
        'template_registry',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
        'template_registry' => 'array',
        'priority' => 'integer',
    ];

    /**
     * Scope to get active theme (legacy — returns first active for compatibility)
     */
    public function scopeActive($query, string $type = 'frontend')
    {
        return $query->where('type', $type)->where('is_active', true);
    }

    /**
     * Scope to get Main Theme
     */
    public function scopeMain($query)
    {
        return $query->where('role', 'main')->where('is_active', true);
    }

    /**
     * Scope to get all active Sub Themes
     */
    public function scopeActiveSubs($query)
    {
        return $query->where('role', 'sub')->where('is_active', true);
    }

    /**
     * Scope to get themes by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get installed themes
     */
    public function scopeInstalled($query)
    {
        return $query->where('status', 'installed');
    }

    /**
     * Check if this theme is the Main Theme
     */
    public function isMain(): bool
    {
        return $this->role === 'main' && $this->is_active;
    }

    /**
     * Check if this theme is an active Sub Theme
     */
    public function isSub(): bool
    {
        return $this->role === 'sub' && $this->is_active;
    }

    /**
     * Get the full path to theme directory.
     * Includes a self-healing fallback if DB path contains backup suffixes or points to missing dirs.
     */
    public function getFullPathAttribute(): string
    {
        $rawPath = (string) $this->path;
        $fullPath = base_path($rawPath);

        if (!is_dir($fullPath) || str_contains($rawPath, '.backup')) {
            $standardPath = base_path("themes/{$this->slug}");
            if (is_dir($standardPath)) {
                return $standardPath;
            }
        }

        return $fullPath;
    }

    /**
     * Get the path to theme views directory
     */
    public function getViewsPathAttribute(): string
    {
        return $this->full_path . '/resources/views';
    }

    /**
     * Get the path to theme public assets directory
     */
    public function getPublicPathAttribute(): string
    {
        return $this->full_path . '/public';
    }

    /**
     * Get screenshot URL
     */
    public function getScreenshotUrlAttribute(): ?string
    {
        if (!$this->screenshot) {
            return null;
        }

        // If screenshot is relative, make it relative to theme public path
        if (str_starts_with($this->screenshot, '/')) {
            return $this->screenshot;
        }

        return '/themes/' . $this->slug . '/' . $this->screenshot;
    }
}