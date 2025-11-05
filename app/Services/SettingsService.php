<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Settings Service - Manages application settings
 * Designed to be extensible for adding new setting groups
 */
class SettingsService
{
    protected string $cacheKey = 'polycms.settings';
    protected int $cacheTtl = 3600; // 1 hour

    /**
     * Get all settings grouped by group
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllSettings(): array
    {
        return Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            $settings = Setting::all();
            $grouped = [];

            foreach ($settings as $setting) {
                if (!isset($grouped[$setting->group])) {
                    $grouped[$setting->group] = [];
                }

                $grouped[$setting->group][$setting->key] = [
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'label' => $setting->label,
                    'description' => $setting->description,
                ];
            }

            return $grouped;
        });
    }

    /**
     * Get settings by group
     *
     * @param string $group
     * @return array<string, mixed>
     */
    public function getGroupSettings(string $group): array
    {
        $allSettings = $this->getAllSettings();
        return $allSettings[$group] ?? [];
    }

    /**
     * Get a single setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string $type
     * @return Setting
     */
    public function set(string $key, $value, string $group = 'general', string $type = 'string'): Setting
    {
        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
            ]
        );

        $this->clearCache();
        return $setting;
    }

    /**
     * Set multiple settings at once
     *
     * @param array<string, mixed> $settings Array of ['key' => 'value'] or ['key' => ['value' => ..., 'group' => ..., 'type' => ...]]
     * @param string $group Default group for settings
     * @return void
     */
    public function setMultiple(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $data) {
            if (is_array($data) && isset($data['value'])) {
                $this->set(
                    $key,
                    $data['value'],
                    $data['group'] ?? $group,
                    $data['type'] ?? 'string'
                );
            } else {
                $this->set($key, $data, $group);
            }
        }

        $this->clearCache();
    }

    /**
     * Register a setting definition
     * Useful for registering settings with labels and descriptions
     *
     * @param string $key
     * @param mixed $defaultValue
     * @param string $group
     * @param string $type
     * @param string|null $label
     * @param string|null $description
     * @return Setting
     */
    public function register(
        string $key,
        $defaultValue,
        string $group = 'general',
        string $type = 'string',
        ?string $label = null,
        ?string $description = null
    ): Setting {
        $setting = Setting::firstOrNew(['key' => $key]);

        // Only set default if setting doesn't exist
        if (!$setting->exists) {
            $setting->value = $defaultValue;
        }

        $setting->group = $group;
        $setting->type = $type;
        $setting->label = $label ?? $setting->label;
        $setting->description = $description ?? $setting->description;
        $setting->save();

        $this->clearCache();
        return $setting;
    }

    /**
     * Clear settings cache
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Get default general settings structure
     * This can be extended for other groups
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultGeneralSettings(): array
    {
        return [
            'site_title' => [
                'key' => 'site_title',
                'value' => 'PolyCMS',
                'type' => 'string',
                'label' => 'Site Title',
                'description' => 'The name of your site',
            ],
            'tagline' => [
                'key' => 'tagline',
                'value' => 'Just another PolyCMS site',
                'type' => 'string',
                'label' => 'Tagline',
                'description' => 'In a few words, explain what this site is about.',
            ],
            'site_icon' => [
                'key' => 'site_icon',
                'value' => null,
                'type' => 'string',
                'label' => 'Site Icon',
                'description' => 'The icon/favicon for your site',
            ],
            'timezone' => [
                'key' => 'timezone',
                'value' => 'UTC',
                'type' => 'string',
                'label' => 'Timezone',
                'description' => 'Choose a city in the same timezone as you.',
            ],
            'date_format' => [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'label' => 'Date Format',
                'description' => 'The format date information is displayed.',
            ],
            'time_format' => [
                'key' => 'time_format',
                'value' => 'H:i',
                'type' => 'string',
                'label' => 'Time Format',
                'description' => 'The format time information is displayed.',
            ],
            'week_starts_on' => [
                'key' => 'week_starts_on',
                'value' => '1', // Monday
                'type' => 'string',
                'label' => 'Week Starts On',
                'description' => 'The day of the week the calendar week should start on.',
            ],
        ];
    }

    /**
     * Initialize default settings if they don't exist
     */
    public function initializeDefaults(): void
    {
        $defaults = $this->getDefaultGeneralSettings();

        foreach ($defaults as $key => $definition) {
            $this->register(
                $definition['key'],
                $definition['value'],
                'general',
                $definition['type'],
                $definition['label'],
                $definition['description']
            );
        }
    }
}
