<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class OptionRepository
{
    protected const CACHE_PREFIX = 'polycms.options.';

    /**
     * Retrieve an option value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @param  string  $group
     * @return mixed
     */
    public static function get(string $key, mixed $default = null, string $group = 'core'): mixed
    {
        try {
            $settingsService = app(\App\Services\SettingsService::class);
            $definition = $settingsService->getDefinition($key);
            if ($definition && isset($definition['group']) && $definition['group'] !== $group) {
                $group = $definition['group'];
            }
        } catch (\Throwable $e) {
            // Ignore during early bootstrap
        }

        $settings = Cache::remember(
            self::CACHE_PREFIX . $group,
            now()->addMinutes(30),
            fn () => self::loadGroup($group)
        );

        try {
            $settingsService = app(\App\Services\SettingsService::class);
            if ($settingsService->isTranslatableKey($key)) {
                $locale = app()->getLocale();
                $defaultLocale = cache()->remember('default_language_code', 3600, function () {
                    try {
                        if (!\Illuminate\Support\Facades\Schema::hasTable('languages')) {
                            return 'en';
                        }
                        return \App\Models\Language::where('is_default', true)->value('code') ?? 'en';
                    } catch (\Exception $e) {
                        return 'en';
                    }
                });

                if ($locale !== $defaultLocale) {
                    $localizedKey = "{$key}_{$locale}";
                    if (array_key_exists($localizedKey, $settings) && $settings[$localizedKey] !== null && $settings[$localizedKey] !== '') {
                        return $settings[$localizedKey];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore during early bootstrap
        }

        return Arr::get($settings, $key, $default);
    }

    /**
     * Persist an option value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  string  $group
     * @return void
     */
    public static function set(string $key, mixed $value, string $group = 'core'): void
    {
        Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => self::encodeValue($value)]
        );

        Cache::forget(self::CACHE_PREFIX . $group);
    }

    /**
     * Delete an option by key.
     *
     * @param  string  $key
     * @param  string  $group
     * @return void
     */
    public static function delete(string $key, string $group = 'core'): void
    {
        Setting::query()
            ->where('group', $group)
            ->where('key', $key)
            ->delete();

        Cache::forget(self::CACHE_PREFIX . $group);
    }

    /**
     * Load all options in a group.
     *
     * @param  string  $group
     * @return array<string, mixed>
     */
    protected static function loadGroup(string $group): array
    {
        return Setting::query()
            ->where('group', $group)
            ->pluck('value', 'key')
            ->map(fn ($value) => self::decodeValue($value))
            ->toArray();
    }

    protected static function decodeValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => $value,
        };
    }

    protected static function encodeValue(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        return (string) $value;
    }
}

