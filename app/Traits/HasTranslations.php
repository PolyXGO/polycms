<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

trait HasTranslations
{
    /**
     * Boot the trait to assign default locale and translation_group_id.
     */
    protected static function bootHasTranslations(): void
    {
        static::creating(function ($model) {
            if (empty($model->locale)) {
                $model->locale = App::getLocale() ?: 'en';
            }
            if (empty($model->translation_group_id)) {
                $model->translation_group_id = (string) Str::uuid();
            }
        });

        static::addGlobalScope('locale', function ($builder) {
            if (static::shouldApplyLocaleFilter()) {
                $builder->where($builder->getQuery()->from . '.locale', App::getLocale() ?: 'en');
            }
        });
    }

    /**
     * Determine if query locale filter should be applied on the current request.
     */
    protected static function shouldApplyLocaleFilter(): bool
    {
        if (App::runningInConsole() && !App::runningUnitTests()) {
            return false;
        }

        $request = request();
        if (!$request) {
            return false;
        }

        $firstSegment = $request->segment(1);

        // Exclude admin, api, install, themes, health check (up)
        // Also exclude if request expects JSON (typically API) or is a sitemap request
        if ($firstSegment === 'admin' ||
            $firstSegment === 'api' ||
            $firstSegment === 'install' ||
            $firstSegment === 'themes' ||
            $firstSegment === 'up' ||
            str_contains($request->path(), 'sitemap') ||
            $request->expectsJson()) {
            return false;
        }

        return true;
    }

    /**
     * Get all other translations for the entity.
     */
    public function translations()
    {
        return $this->hasMany(static::class, 'translation_group_id', 'translation_group_id')
            ->withoutGlobalScope('locale')
            ->where('id', '!=', $this->id);
    }

    /**
     * Check if a translation exists for the given locale.
     */
    public function hasTranslation(string $locale): bool
    {
        return static::withoutGlobalScope('locale')
            ->where('translation_group_id', $this->translation_group_id)
            ->where('locale', $locale)
            ->exists();
    }

    /**
     * Get the translation model for the given locale.
     */
    public function getTranslation(string $locale)
    {
        return static::withoutGlobalScope('locale')
            ->where('translation_group_id', $this->translation_group_id)
            ->where('locale', $locale)
            ->first();
    }

    /**
     * Scope query to only include items in the current locale or specified locale.
     */
    public function scopeInLocale($query, ?string $locale = null)
    {
        return $query->withoutGlobalScope('locale')->where('locale', $locale ?: App::getLocale());
    }

    /**
     * Get a simplified list of translations for API resources.
     */
    public function getTranslationsList(): array
    {
        if (empty($this->translation_group_id)) {
            return [];
        }

        return static::withoutGlobalScope('locale')
            ->where('translation_group_id', $this->translation_group_id)
            ->where('id', '!=', $this->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'locale' => $item->locale,
                    'slug' => $item->slug,
                    'title' => $item->title ?? $item->name,
                ];
            })
            ->toArray();
    }
}
