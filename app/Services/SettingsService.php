<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\Hook;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Settings Service - Manages application settings
 * Designed to be extensible for adding new setting groups
 */
use App\Services\Mail\MailProtocolRegistry;

/**
 * Settings Service - Manages application settings
 * Designed to be extensible for adding new setting groups
 */
class SettingsService
{
    protected string $cacheKey = 'polycms.settings.db';
    protected string $cacheMapKey = 'polycms.settings.map';
    protected int $cacheTtl = 3600; // 1 hour
    protected ?array $settingsMap = null;
    protected ?bool $settingsTableExists = null;
    protected MailProtocolRegistry $mailProtocolRegistry;

    public function __construct(MailProtocolRegistry $mailProtocolRegistry)
    {
        $this->mailProtocolRegistry = $mailProtocolRegistry;
    }

    /**
     * Get all settings grouped by group
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllSettings(): array
    {
        try {
            $cachedSettings = Cache::remember($this->cacheKey, $this->cacheTtl, function () {
                if (!$this->hasSettingsTable()) {
                    return collect([]);
                }
                return Setting::all();
            });

            $definitions = $this->getDefaultDefinitions();
            $grouped = [];

            foreach ($definitions as $group => $items) {
                foreach ($items as $key => $definition) {
                    $normalized = $this->normalizeDefinition($key, $definition);
                    $normalized['group'] = $group;
                    $grouped[$group][$normalized['key']] = $normalized;
                }
            }

            foreach ($cachedSettings as $setting) {
                $group = $setting->group ?? 'general';
                $key = $setting->key;

                if (!isset($grouped[$group])) {
                    $grouped[$group] = [];
                }

                $existing = $grouped[$group][$key] ?? $this->normalizeDefinition($key, ['value' => $setting->value]);
                
                // Readonly fields should always use their dynamically generated values, never DB values.
                if (($existing['type'] ?? '') === 'readonly') {
                    continue;
                }

                $grouped[$group][$key] = array_merge($existing, [
                    'key' => $key,
                    'value' => $setting->value,
                    'type' => $setting->type ?? $existing['type'] ?? 'string',
                    'label' => $setting->label ?? $existing['label'] ?? $key,
                    'description' => $setting->description ?? $existing['description'] ?? '',
                    'group' => $group,
                ]);
            }

            return $grouped;
        } catch (\Exception $e) {
            return [];
        }
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
        try {
            if (!$this->hasSettingsTable()) {
                return $default;
            }

            $settingsMap = $this->getSettingsMap();

            return array_key_exists($key, $settingsMap)
                ? $settingsMap[$key]
                : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    protected function hasSettingsTable(): bool
    {
        if ($this->settingsTableExists !== null) {
            return $this->settingsTableExists;
        }

        try {
            $this->settingsTableExists = \Illuminate\Support\Facades\Schema::hasTable('settings');
        } catch (\Throwable $e) {
            $this->settingsTableExists = false;
        }

        return $this->settingsTableExists;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getSettingsMap(): array
    {
        if ($this->settingsMap !== null) {
            return $this->settingsMap;
        }

        if (!$this->hasSettingsTable()) {
            $this->settingsMap = [];
            return $this->settingsMap;
        }

        $this->settingsMap = Cache::remember($this->cacheMapKey, $this->cacheTtl, function () {
            if (!$this->hasSettingsTable()) {
                return [];
            }

            $map = [];
            $settings = Setting::query()->get(['key', 'value', 'type']);

            foreach ($settings as $setting) {
                $map[$setting->key] = $setting->value;
            }

            return $map;
        });

        return $this->settingsMap;
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
    public function set(string $key, $value, string $group = 'general', string $type = 'string', bool $dispatchHook = true): Setting
    {
        if ($dispatchHook) {
            Hook::doAction('setting.updating', $key, $value, $group, $type);
        }

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'type' => $type,
                'value' => $value,
                'group' => $group,
            ]
        );

        // Sync: invalidate OptionRepository cache for this group so get_option() is instantly fresh
        \Illuminate\Support\Facades\Cache::forget('polycms.options.' . $group);

        $this->clearCache();

        if ($dispatchHook) {
            Hook::doAction('settings.saved', [
                'mode' => 'single',
                'group' => $group,
                'key' => $key,
                'value' => $value,
                'type' => $type,
            ]);
        }

        return $setting;
    }

    /**
     * Get a setting definition by key
     *
     * @param string $key
     * @param array|null $default
     * @return array|null
     */
    protected function getDefinition(string $key): ?array
    {
        $allDefinitions = $this->getDefaultDefinitions();
        foreach ($allDefinitions as $group => $items) {
            if (isset($items[$key])) {
                return $this->normalizeDefinition($key, $items[$key]);
            }
        }
        return null;
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
        $changedKeys = [];

        foreach ($settings as $key => $data) {
            if (is_array($data) && array_key_exists('value', $data)) {
                $this->set(
                    $key,
                    $data['value'],
                    $data['group'] ?? $group,
                    $data['type'] ?? 'string',
                    false
                );
            } else {
                // Look up type from definition if possible
                $definition = $this->getDefinition($key);
                $type = $definition['type'] ?? 'string';
                $this->set($key, $data, $group, $type, false);
            }

            $changedKeys[] = $key;
        }

        $this->clearCache();

        Hook::doAction('settings.saved', [
            'mode' => 'batch',
            'group' => $group,
            'keys' => $changedKeys,
        ]);
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
        Cache::forget($this->cacheMapKey);
        $this->settingsMap = null;
        $this->settingsTableExists = null;
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
            'brand_logo' => [
                'key' => 'brand_logo',
                'value' => '/assets/defaults/polycms-logo.png',
                'type' => 'string',
                'label' => 'Brand Logo',
                'description' => 'Upload a logo for your brand. If no logo is set, the brand name will be displayed instead.',
            ],
            'brand_name' => [
                'key' => 'brand_name',
                'value' => 'POLYCMS',
                'type' => 'string',
                'label' => 'Brand Name',
                'description' => 'Custom brand name to display when no logo is available. Defaults to "POLYCMS" if empty.',
            ],
            'show_brand_label' => [
                'key' => 'show_brand_label',
                'value' => true,
                'type' => 'boolean',
                'label' => 'Show Site Name next to Logo',
                'description' => 'When enabled, the brand name text will be displayed alongside the logo in the admin sidebar.',
            ],
            'admin_email' => [
                'key' => 'admin_email',
                'value' => null,
                'type' => 'string',
                'label' => 'Admin Email',
                'description' => 'Email address for the site administrator.',
            ],
            'site_language' => [
                'key' => 'site_language',
                'value' => 'en',
                'type' => 'string',
                'label' => 'Site Language',
                'description' => 'The default language for your site. Modules and themes can use this for localization.',
            ],
            'site_language_direction' => [
                'key' => 'site_language_direction',
                'value' => 'ltr',
                'type' => 'string',
                'label' => 'Front Site Language Direction',
                'description' => 'Text direction for the frontend site. This will be applied to the CSS direction property.',
            ],
            'admin_topbar_dark_mode' => [
                'key' => 'admin_topbar_dark_mode',
                'value' => true,
                'type' => 'boolean',
                'label' => 'Show Topbar Dark/Light Mode Toggle',
                'description' => 'Enable or disable the theme toggle button on the Admin Topbar.',
            ],
            'site_icon' => [
                'key' => 'site_icon',
                'value' => '/assets/defaults/polycms-logo.png',
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
     * Parse PHP string size (2M, 2G) to MB
     */
    protected function parseSizeToMb(string $sizeStr): int
    {
        $sizeStr = trim($sizeStr);
        if (empty($sizeStr)) return 2;
        
        $unit = strtolower(substr($sizeStr, -1));
        $value = (int)$sizeStr;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
                break;
            case 'm':
                break;
            case 'k':
                $value = max(1, (int)round($value / 1024));
                break;
        }
        
        return max(1, $value);
    }

    /**
     * Get default permalink settings structure
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultReadingSettings(): array
    {
        return [
            'reading_show_on_front' => [
                'key' => 'reading_show_on_front',
                'value' => 'posts',
                'type' => 'string', // 'posts' or 'page'
                'label' => 'Your homepage displays',
                'description' => 'Choose what to show on your homepage.',
            ],
            'reading_page_on_front' => [
                'key' => 'reading_page_on_front',
                'value' => null,
                'type' => 'integer',
                'label' => 'Homepage',
                'description' => 'The page to display as your homepage.',
            ],
            'reading_page_for_posts' => [
                'key' => 'reading_page_for_posts',
                'value' => null,
                'type' => 'integer',
                'label' => 'Posts page',
                'description' => 'The page to display your blog posts.',
            ],
            'reading_posts_per_page' => [
                'key' => 'reading_posts_per_page',
                'value' => 10,
                'type' => 'integer',
                'label' => 'Blog pages show at most',
                'description' => 'Number of posts to show on your blog pages.',
            ],
            'reading_feed_limit' => [
                'key' => 'reading_feed_limit',
                'value' => 10,
                'type' => 'integer',
                'label' => 'Syndication feeds show the most recent',
                'description' => 'Number of items to show in your RSS feeds.',
            ],
            'reading_feed_full_content' => [
                'key' => 'reading_feed_full_content',
                'value' => true,
                'type' => 'boolean',
                'label' => 'For each post in a feed, include',
                'description' => 'Whether to include full text or excerpt in your RSS feeds.',
            ],
            'reading_search_engine_noindex' => [
                'key' => 'reading_search_engine_noindex',
                'value' => true,
                'type' => 'boolean',
                'label' => 'Search engine visibility',
                'description' => 'Allow search engines to index this site.',
            ],
        ];
    }

    /**
     * Initialize default settings if they don't exist
     */
    public function initializeDefaults(): void
    {
        $definitions = $this->getDefaultDefinitions();

        foreach ($definitions as $group => $settings) {
            foreach ($settings as $key => $definition) {
                $normalized = $this->normalizeDefinition($key, $definition);

                $this->register(
                    $normalized['key'],
                    $normalized['value'],
                    $group,
                    $normalized['type'],
                    $normalized['label'],
                    $normalized['description']
                );
            }
        }
    }

    /**
     * Get all default setting definitions
     *
     * @return array<string, array<string, array{key: string, value: mixed, type: string, label: string, description: string}>>
     */
    protected function getDefaultDefinitions(): array
    {
        $defaults = [
            'general' => $this->getDefaultGeneralSettings(),
            'theme_options' => $this->getDefaultThemeSettings(),
            'media' => $this->getDefaultMediaSettings(),
            'permalinks' => $this->getDefaultPermalinkSettings(),
            'email' => $this->getDefaultEmailSettings(),
            'reading' => $this->getDefaultReadingSettings(),
            'ecommerce' => $this->getDefaultEcommerceSettings(),
            'refund_policy' => $this->getDefaultRefundPolicySettings(),
            'global_faqs' => $this->getDefaultGlobalFaqSettings(),
            'global_tabs' => $this->getDefaultGlobalTabSettings(),
            'template_defaults' => $this->getDefaultTemplateSettings(),
            'auth_appearance' => $this->getDefaultAuthAppearanceSettings(),
            'api' => $this->getDefaultApiSettings(),
        ];

        return Hook::applyFilters('settings.defaults', $defaults, $this);
    }

    /**
     * Get default auth appearance settings structure
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultAuthAppearanceSettings(): array
    {
        return [
            'auth_login_text' => [
                'key' => 'auth_login_text',
                'value' => "PolyCMS with love\nCopyright 2026 © polycms.org.",
                'type' => 'textarea',
                'label' => 'Login Page Footer Text',
                'description' => 'Text displayed at the bottom right of the login screen. You can use multiple lines.'
            ],
            'auth_show_version' => [
                'key' => 'auth_show_version',
                'value' => true,
                'type' => 'boolean',
                'label' => 'Show System Version',
                'description' => 'Display the PolyCMS and Laravel framework version numbers.'
            ],
            'auth_layout_position' => [
                'key' => 'auth_layout_position',
                'value' => 'right',
                'type' => 'select',
                'label' => 'Login Layout Position',
                'description' => 'Position of the login form on the page',
                'options' => [
                    ['label' => 'Center (Floating Card)', 'value' => 'center'],
                    ['label' => 'Left (Split Screen)', 'value' => 'left'],
                    ['label' => 'Right (Split Screen)', 'value' => 'right'],
                ],
            ],
            'auth_bg_mode' => [
                'key' => 'auth_bg_mode',
                'value' => 'random',
                'type' => 'select',
                'label' => 'Background Mode',
                'description' => 'How the background image is chosen',
                'options' => [
                    ['label' => 'Single Image', 'value' => 'fixed'],
                    ['label' => 'Random single from Gallery', 'value' => 'random'],
                    ['label' => 'Slideshow (Fade Effect)', 'value' => 'slideshow'],
                ],
            ],
            'auth_bg_fixed_image' => [
                'key' => 'auth_bg_fixed_image',
                'value' => null,
                'type' => 'image',
                'label' => 'Fixed Background Image',
                'description' => 'Used when Background Mode is set to Fixed',
            ],
            'auth_bg_images' => [
                'key' => 'auth_bg_images',
                'value' => [
                    '/assets/defaults/polycms-bg-1.jpg',
                    '/assets/defaults/polycms-bg-2.jpg',
                ],
                'type' => 'gallery',
                'label' => 'Background Images Gallery',
                'description' => 'Images to randomize when Background Mode is set to Random',
            ],
            'auth_bg_overlay' => [
                'key' => 'auth_bg_overlay',
                'value' => 50,
                'type' => 'number',
                'label' => 'Background Overlay Opacity (%)',
                'description' => 'Dark overlay opacity over the background image (0-100)',
            ],
            'auth_card_glassmorphism' => [
                'key' => 'auth_card_glassmorphism',
                'value' => 10,
                'type' => 'number',
                'label' => 'Form Glassmorphism Opacity (%)',
                'description' => 'Transparency of the form card (0-100)',
            ],
            'auth_show_logo' => [
                'key' => 'auth_show_logo',
                'value' => true,
                'type' => 'boolean',
                'label' => 'Show Brand Logo/Name',
                'description' => 'Display the brand logo or name above the login form',
            ],
        ];
    }

    /**
     * Get default API settings structure
     *
     * Core API governance settings. Token management and advanced API features
     * are provided by the Data Builder module (Pro edition).
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultApiSettings(): array
    {
        return [
            'api_enabled' => [
                'key'         => 'api_enabled',
                'value'       => true,
                'type'        => 'boolean',
                'label'       => 'Enable REST API',
                'description' => 'Master switch for the public /api/v1 endpoints. When disabled, all external API requests return 503.',
            ],
            'api_rate_limit' => [
                'key'         => 'api_rate_limit',
                'value'       => 60,
                'type'        => 'number',
                'label'       => 'Rate Limit (requests/minute)',
                'description' => 'Maximum number of API requests per token per minute. Applies globally unless overridden per-token by the Data Builder module.',
                'input_props' => ['min' => 10, 'max' => 1000, 'step' => 10],
            ],
            'api_allowed_origins' => [
                'key'         => 'api_allowed_origins',
                'value'       => '*',
                'type'        => 'string',
                'label'       => 'Allowed Origins (CORS)',
                'description' => 'Comma-separated list of allowed origins for cross-origin API requests. Use * to allow all origins.',
            ],
            'api_max_page_size' => [
                'key'         => 'api_max_page_size',
                'value'       => 100,
                'type'        => 'number',
                'label'       => 'Max Page Size',
                'description' => 'Maximum number of records that can be returned in a single paginated API response.',
                'input_props' => ['min' => 10, 'max' => 500, 'step' => 10],
            ],
        ];
    }

    /**
     * Get default media settings structure
     *
     * @return array<string, array{key: string, value: mixed, type: string, label?: string, description?: string}>
     */
    public function getDefaultMediaSettings(): array
    {
        $drivers = Hook::applyFilters('settings.media.drivers', [
            ['label' => _l('Local disk'), 'value' => 'local'],
        ]);

        $hints = Hook::applyFilters('settings.media.driver_hints', []);

        $serverMaxStr = ini_get('upload_max_filesize') ?: '2M';
        $serverMaxMb = $this->parseSizeToMb($serverMaxStr);

        return [
            'media_driver' => ['key' => 'media_driver', 'value' => 'local', 'type' => 'select', 'options' => $drivers],
            'media_driver_hints' => ['key' => 'media_driver_hints', 'value' => $hints, 'type' => 'readonly'],
            'media_use_original_name' => ['key' => 'media_use_original_name', 'value' => false, 'type' => 'boolean'],
            'media_convert_uuid' => ['key' => 'media_convert_uuid', 'value' => false, 'type' => 'boolean'],
            'media_keep_original_size_quality' => ['key' => 'media_keep_original_size_quality', 'value' => false, 'type' => 'boolean'],
            'media_image_quality' => ['key' => 'media_image_quality', 'value' => 75, 'type' => 'integer'],
            'media_convert_to_webp' => ['key' => 'media_convert_to_webp', 'value' => false, 'type' => 'boolean'],
            'media_default_placeholder' => ['key' => 'media_default_placeholder', 'value' => '', 'type' => 'string'],
            'media_server_max_upload_size' => ['key' => 'media_server_max_upload_size', 'value' => $serverMaxMb, 'type' => 'readonly'],
            'media_has_gd' => ['key' => 'media_has_gd', 'value' => extension_loaded('gd'), 'type' => 'readonly'],
            'media_has_imagick' => ['key' => 'media_has_imagick', 'value' => extension_loaded('imagick'), 'type' => 'readonly'],
            'media_max_upload_size' => ['key' => 'media_max_upload_size', 'value' => $serverMaxMb, 'type' => 'integer'],
            'media_customize_upload_path' => ['key' => 'media_customize_upload_path', 'value' => false, 'type' => 'boolean'],
            'media_upload_path' => ['key' => 'media_upload_path', 'value' => 'storage', 'type' => 'string'],
            'media_enable_chunk_upload' => ['key' => 'media_enable_chunk_upload', 'value' => false, 'type' => 'boolean'],
            'media_chunk_size' => ['key' => 'media_chunk_size', 'value' => 2, 'type' => 'integer'],
            'media_image_processing_library' => ['key' => 'media_image_processing_library', 'value' => 'gd', 'type' => 'string'],
            'media_enable_thumbnails' => ['key' => 'media_enable_thumbnails', 'value' => true, 'type' => 'boolean'],
        ];
    }

    /**
     * Get default email settings structure
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultEmailSettings(): array
    {
        $settings = [
            'email_from_name' => [
                'key' => 'email_from_name',
                'value' => 'PolyCMS',
                'type' => 'string',
                'label' => 'Sender Name',
                'description' => 'The name that will appear in the "From" field of emails.',
            ],
            'email_from_address' => [
                'key' => 'email_from_address',
                'value' => 'noreply@polycms.org',
                'type' => 'string',
                'label' => 'Sender Email',
                'description' => 'The email address used as the sender.',
            ],
        ];

        // Get registered protocols
        $protocols = $this->mailProtocolRegistry->getProtocols();
        $driverOptions = [];
        $protocolSettings = [];

        foreach ($protocols as $key => $protocol) {
            $driverOptions[] = [
                'label' => $protocol['label'],
                'value' => $key,
            ];

            // Add fields for this protocol
            if (isset($protocol['fields'])) {
                foreach ($protocol['fields'] as $field) {
                    $fieldKey = $field['key'];
                    // Add driver prefix if not present to avoid collisions? 
                    // Protocol registry assumes keys are unique or namespaced.
                    // Ideally, keys should be unique like 'email_smtp_host'
                    
                    $protocolSettings[$fieldKey] = array_merge([
                        'value' => $field['default'] ?? null,
                    ], $field);
                }
            }
        }

        $settings['email_driver'] = [
            'key' => 'email_driver',
            'value' => 'smtp',
            'type' => 'select',
            'label' => 'Mail Driver',
            'options' => $driverOptions,
            'description' => 'Select the mail driver to use for sending emails.',
        ];

        // Merge protocol specific settings
        return array_merge($settings, $protocolSettings);
    }

    /**
     * Get default ecommerce settings structure
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultEcommerceSettings(): array
    {
        return [
            'ecommerce_store_name' => [
                'key' => 'ecommerce_store_name',
                'value' => 'My Digital Store',
                'type' => 'string',
                'label' => 'Store Name',
                'section' => 'store_info',
                'section_label' => 'Store Information',
                'order' => 10,
            ],
            'ecommerce_company_name' => [
                'key' => 'ecommerce_company_name',
                'value' => '',
                'type' => 'string',
                'label' => 'Company Name',
                'section' => 'store_info',
                'section_label' => 'Store Information',
                'order' => 20,
            ],
            'ecommerce_phone_number' => [
                'key' => 'ecommerce_phone_number',
                'value' => '',
                'type' => 'string',
                'label' => 'Phone Number',
                'description' => 'Contact number displayed to customers and on invoices.',
                'section' => 'store_info',
                'order' => 30,
            ],
            'ecommerce_store_email' => [
                'key' => 'ecommerce_store_email',
                'value' => '',
                'type' => 'string',
                'label' => 'Store Email',
                'description' => 'Primary contact email for customer inquiries.',
                'section' => 'store_info',
                'order' => 40,
            ],
            'ecommerce_admin_emails' => [
                'key' => 'ecommerce_admin_emails',
                'value' => [],
                'type' => 'tags',
                'label' => 'Admin Notification Emails',
                'description' => 'Emails to receive new order notifications.',
                'section' => 'store_info',
                'order' => 50,
            ],

            // Location / Address
            'ecommerce_address_country' => [
                'key' => 'ecommerce_address_country',
                'value' => 'US',
                'type' => 'select', // Will need to populate options in frontend or via API
                'label' => 'Country',
                'section' => 'address',
                'section_label' => 'Store Address',
                'order' => 60,
            ],
            'ecommerce_address_state' => [
                'key' => 'ecommerce_address_state',
                'value' => '',
                'type' => 'string',
                'label' => 'State / Province',
                'section' => 'address',
                'order' => 70,
            ],
            'ecommerce_address_city' => [
                'key' => 'ecommerce_address_city',
                'value' => '',
                'type' => 'string',
                'label' => 'City',
                'section' => 'address',
                'order' => 80,
            ],
            'ecommerce_address_line1' => [
                'key' => 'ecommerce_address_line1',
                'value' => '',
                'type' => 'string',
                'label' => 'Address',
                'section' => 'address',
                'order' => 90,
            ],
            'ecommerce_tax_id' => [
                'key' => 'ecommerce_tax_id',
                'value' => '',
                'type' => 'string',
                'label' => 'Tax ID / VAT Number',
                'description' => 'Displayed on invoices.',
                'section' => 'address',
                'order' => 100,
            ],
            'ecommerce_taxes' => [
                'key' => 'ecommerce_taxes',
                'value' => 'exclude',
                'type' => 'select',
                'label' => 'Tax Calculation',
                'section' => 'tax',
            ],

            'ecommerce_currency' => [
                'key' => 'ecommerce_currency',
                'value' => 'USD',
                'type' => 'string',
                'label' => 'Currency Code',
                'description' => 'ISO 4217 currency code (e.g., USD, VND).',
                'section' => 'currency',
                'section_label' => 'Currency & Payment',
                'order' => 110,
            ],
            'ecommerce_currency_symbol' => [
                'key' => 'ecommerce_currency_symbol',
                'value' => '$',
                'type' => 'string',
                'label' => 'Currency Symbol',
                'section' => 'currency',
                'order' => 120,
            ],
            'ecommerce_active_gateways' => [
                'key' => 'ecommerce_active_gateways',
                'value' => [],
                'type' => 'tags',
                'label' => 'Active Payment Gateways',
                'description' => 'List of enabled payment gateway codes.',
                'section' => 'currency',
                'order' => 130,
            ],
            // Invoice
            'ecommerce_invoice_prefix' => [
                'key' => 'ecommerce_invoice_prefix',
                'value' => 'INV',
                'type' => 'string',
                'label' => 'Invoice Prefix',
                'description' => 'Prefix added to randomly generated invoice numbers (e.g. INV84921059).',
                'section' => 'invoice',
                'section_label' => 'Invoice Configuration',
                'order' => 10,
            ],
            'ecommerce_invoice_auto_issue' => [
                'key' => 'ecommerce_invoice_auto_issue',
                'value' => true,
                'type' => 'boolean',
                'label' => 'Auto-Issue Invoice',
                'description' => 'Automatically generate an invoice when an order is paid or completed.',
                'section' => 'invoice',
                'order' => 20,
            ],
            // Formatting options (kept for backward compatibility logic, but hidden in main UI if new UI handles them)
            'currency_thousands_separator' => [
                'key' => 'currency_thousands_separator',
                'value' => ',',
                'type' => 'select',
                'label' => 'Thousands Separator',
                'options' => [
                    ['label' => 'Comma (,)', 'value' => ','],
                    ['label' => 'Period (.)', 'value' => '.'],
                    ['label' => 'Space ( )', 'value' => ' '],
                ],
                'section' => 'formatting',
                'order' => 140,
            ],
            'currency_decimal_separator' => [
                'key' => 'currency_decimal_separator',
                'value' => '.',
                'type' => 'select',
                'label' => 'Decimal Separator',
                'options' => [
                    ['label' => 'Period (.)', 'value' => '.'],
                    ['label' => 'Comma (,)', 'value' => ','],
                ],
                'section' => 'formatting',
                'order' => 150,
            ],
            'currency_decimals' => [
                'key' => 'currency_decimals',
                'value' => 2,
                'type' => 'number',
                'label' => 'Number of Decimals',
                'input_props' => ['min' => 0, 'max' => 4],
                'section' => 'formatting',
                'order' => 160,
            ],
            'currency_symbol_position' => [
                'key' => 'currency_symbol_position',
                'value' => 'before',
                'type' => 'select',
                'label' => 'Currency Symbol Position',
                'options' => [
                    ['label' => 'Before amount ($100)', 'value' => 'before'],
                    ['label' => 'After amount (100$)', 'value' => 'after'],
                ],
                'section' => 'formatting',
                'order' => 170,
            ],
            'currency_space' => [
                'key' => 'currency_space',
                'value' => false,
                'type' => 'boolean',
                'label' => 'Add space between amount and symbol',
                'section' => 'formatting',
                'order' => 180,
            ],
        ];
    }

    /**
     * Get default refund policy settings structure
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultRefundPolicySettings(): array
    {
        return [
            'refund_policy_default_window_days' => [
                'key' => 'refund_policy_default_window_days',
                'value' => 7,
                'type' => 'integer',
                'label' => 'Default refund request window (days)',
                'description' => 'Number of days after a successful order when customers can request refunds by default.',
            ],
            'refund_policy_default_note' => [
                'key' => 'refund_policy_default_note',
                'value' => '',
                'type' => 'text',
                'label' => 'Default refund policy note',
                'description' => 'Fallback note shown when a refundable product has no product-specific refund policy note.',
            ],
        ];
    }

    /**
     * Get default global FAQ settings structure
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultGlobalFaqSettings(): array
    {
        return [
            'global_faqs_enabled' => [
                'key' => 'global_faqs_enabled',
                'value' => true,
                'type' => 'boolean',
                'label' => 'Enable global FAQs',
                'description' => 'Allow products to reuse global FAQ entries.',
            ],
            'media_customize_upload_path' => [
                'key' => 'media_customize_upload_path',
                'value' => false,
                'type' => 'boolean'
            ],
            'media_upload_path' => [
                'key' => 'media_upload_path',
                'value' => 'storage',
                'type' => 'string'
            ],
            'global_faqs_expand_all' => [
                'key' => 'global_faqs_expand_all',
                'value' => false,
                'type' => 'boolean',
                'label' => 'Expand all global FAQ items by default',
                'description' => 'When enabled, global FAQ items are expanded on page load.',
            ],
            'global_faqs_items' => [
                'key' => 'global_faqs_items',
                'value' => [],
                'type' => 'json',
                'label' => 'Global FAQ items',
                'description' => 'Reusable FAQ entries for product detail pages.',
            ],
        ];
    }

    /**
     * Get default global tab settings structure
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultGlobalTabSettings(): array
    {
        return [
            'global_tabs_enabled' => [
                'key' => 'global_tabs_enabled',
                'value' => true,
                'type' => 'boolean',
                'label' => 'Enable global tabs',
                'description' => 'Allow products to reuse global tab entries.',
            ],
            'global_tabs_items' => [
                'key' => 'global_tabs_items',
                'value' => [],
                'type' => 'json',
                'label' => 'Global tab items',
                'description' => 'Reusable tab entries for product detail pages.',
            ],
        ];
    }

    /**
     * Get default template settings structure
     *
     * @return array<string, array{key: string, value: mixed, type: string, label: string, description: string}>
     */
    public function getDefaultTemplateSettings(): array
    {
        return [
            'template_default_posts_show' => [
                'key' => 'template_default_posts_show',
                'value' => null,
                'type' => 'string',
                'label' => 'Post Detail Defaults',
                'description' => 'Default theme template for single posts.',
            ],
            'template_default_posts_index' => [
                'key' => 'template_default_posts_index',
                'value' => null,
                'type' => 'string',
                'label' => 'Post Archive Defaults',
                'description' => 'Default theme template for post index/archive.',
            ],
            'template_default_pages_show' => [
                'key' => 'template_default_pages_show',
                'value' => null,
                'type' => 'string',
                'label' => 'Page Detail Defaults',
                'description' => 'Default theme template for pages.',
            ],
            'template_default_products_show' => [
                'key' => 'template_default_products_show',
                'value' => null,
                'type' => 'string',
                'label' => 'Product Detail Defaults',
                'description' => 'Default theme template for single products.',
            ],
            'template_default_products_index' => [
                'key' => 'template_default_products_index',
                'value' => null,
                'type' => 'string',
                'label' => 'Product Archive Defaults',
                'description' => 'Default theme template for product index/archive.',
            ],
            'template_default_categories_show' => [
                'key' => 'template_default_categories_show',
                'value' => null,
                'type' => 'string',
                'label' => 'Category Detail Defaults',
                'description' => 'Default theme template for post categories.',
            ],
            'template_default_product_categories_show' => [
                'key' => 'template_default_product_categories_show',
                'value' => null,
                'type' => 'string',
                'label' => 'Product Category Defaults',
                'description' => 'Default theme template for product categories.',
            ],
            'template_default_home' => [
                'key' => 'template_default_home',
                'value' => null,
                'type' => 'string',
                'label' => 'Home Defaults',
                'description' => 'Default theme template for the homepage.',
            ]
        ];
    }

    /**
     * Normalize a single setting definition ensuring required keys exist.
     *
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    protected function normalizeDefinition(string $key, array $definition): array
    {
        $defaultValue = $definition['value'] ?? null;

        return array_merge([
            'key' => $definition['key'] ?? $key,
            'value' => $defaultValue,
            'default' => $definition['default'] ?? $defaultValue,
            'type' => $definition['type'] ?? 'string',
            'label' => $definition['label'] ?? $key,
            'description' => $definition['description'] ?? '',
            'options' => $definition['options'] ?? [],
            'section' => $definition['section'] ?? null,
            'section_label' => $definition['section_label'] ?? null,
            'section_description' => $definition['section_description'] ?? null,
            'section_order' => $definition['section_order'] ?? 100,
            'category' => $definition['category'] ?? null,
            'category_label' => $definition['category_label'] ?? null,
            'category_order' => $definition['category_order'] ?? 100,
            'order' => $definition['order'] ?? 100,
            'input_props' => $definition['input_props'] ?? [],
        ], $definition);
    }

    protected function getDefaultThemeSettings(): array
    {
        return [
            'theme_heading_font_family' => [
                'value' => 'Inter, sans-serif',
                'type' => 'text',
                'label' => 'Heading Font Family',
                'description' => 'Font family applied to all heading elements (h1-h6).',
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 10,
            ],
            'theme_heading_font_weight' => [
                'value' => '700',
                'default' => '700',
                'type' => 'select',
                'label' => 'Heading Font Weight',
                'description' => 'Default font weight used for headings.',
                'options' => [
                    ['label' => '400 (Normal)', 'value' => '400'],
                    ['label' => '500 (Medium)', 'value' => '500'],
                    ['label' => '600 (Semi Bold)', 'value' => '600'],
                    ['label' => '700 (Bold)', 'value' => '700'],
                    ['label' => '800 (Extra Bold)', 'value' => '800'],
                ],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 20,
            ],
            'theme_heading_line_height' => [
                'value' => 1.2,
                'type' => 'number',
                'label' => 'Heading Line Height',
                'description' => 'Line height applied to headings.',
                'input_props' => ['step' => 0.05, 'min' => 1, 'max' => 2],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 30,
            ],
            'theme_heading_letter_spacing' => [
                'value' => -0.02,
                'type' => 'number',
                'label' => 'Heading Letter Spacing (em)',
                'description' => 'Adjust spacing between heading characters. Negative values tighten spacing.',
                'input_props' => ['step' => 0.01, 'min' => -0.5, 'max' => 0.5],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 40,
            ],
            'theme_heading_color' => [
                'value' => '#111827',
                'type' => 'color',
                'label' => 'Heading Color',
                'description' => 'Base color for heading elements.',
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 50,
            ],
            'theme_heading_h1_size' => [
                'value' => 36,
                'type' => 'number',
                'label' => 'H1 Font Size (px)',
                'description' => 'Displayed size for h1 headings.',
                'input_props' => ['min' => 24, 'max' => 96, 'step' => 1, 'suffix' => 'px'],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 60,
            ],
            'theme_heading_h2_size' => [
                'value' => 28,
                'type' => 'number',
                'label' => 'H2 Font Size (px)',
                'description' => 'Displayed size for h2 headings.',
                'input_props' => ['min' => 20, 'max' => 64, 'step' => 1, 'suffix' => 'px'],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 70,
            ],
            'theme_heading_h3_size' => [
                'value' => 22,
                'type' => 'number',
                'label' => 'H3 Font Size (px)',
                'description' => 'Displayed size for h3 headings.',
                'input_props' => ['min' => 18, 'max' => 56, 'step' => 1, 'suffix' => 'px'],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 80,
            ],
            'theme_heading_h4_size' => [
                'value' => 18,
                'type' => 'number',
                'label' => 'H4 Font Size (px)',
                'description' => 'Displayed size for h4 headings.',
                'input_props' => ['min' => 16, 'max' => 48, 'step' => 1, 'suffix' => 'px'],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 90,
            ],
            'theme_heading_h5_size' => [
                'value' => 16,
                'type' => 'number',
                'label' => 'H5 Font Size (px)',
                'description' => 'Displayed size for h5 headings.',
                'input_props' => ['min' => 14, 'max' => 36, 'step' => 1, 'suffix' => 'px'],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 100,
            ],
            'theme_heading_h6_size' => [
                'value' => 14,
                'type' => 'number',
                'label' => 'H6 Font Size (px)',
                'description' => 'Displayed size for h6 headings.',
                'input_props' => ['min' => 12, 'max' => 24, 'step' => 1, 'suffix' => 'px'],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'headings',
                'category_label' => 'Headings',
                'category_order' => 10,
                'order' => 110,
            ],
            'theme_body_font_family' => [
                'value' => 'Inter, sans-serif',
                'type' => 'text',
                'label' => 'Body Font Family',
                'description' => 'Primary font applied to body text.',
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'body',
                'category_label' => 'Body Text',
                'category_order' => 20,
                'order' => 10,
            ],
            'theme_body_font_size' => [
                'value' => 16,
                'type' => 'number',
                'label' => 'Body Font Size (px)',
                'description' => 'Base font size for body text.',
                'input_props' => ['min' => 12, 'max' => 22, 'step' => 1, 'suffix' => 'px'],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'body',
                'category_label' => 'Body Text',
                'category_order' => 20,
                'order' => 20,
            ],
            'theme_body_line_height' => [
                'value' => 1.6,
                'type' => 'number',
                'label' => 'Body Line Height',
                'description' => 'Line height used for body copy.',
                'input_props' => ['min' => 1, 'max' => 2.5, 'step' => 0.05],
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'body',
                'category_label' => 'Body Text',
                'category_order' => 20,
                'order' => 30,
            ],
            'theme_body_color' => [
                'value' => '#1f2937',
                'type' => 'color',
                'label' => 'Body Text Color',
                'description' => 'Default text color for body content.',
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'body',
                'category_label' => 'Body Text',
                'category_order' => 20,
                'order' => 40,
            ],
            'theme_body_muted_color' => [
                'value' => '#6b7280',
                'type' => 'color',
                'label' => 'Muted Text Color',
                'description' => 'Color used for descriptions and muted text.',
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'body',
                'category_label' => 'Body Text',
                'category_order' => 20,
                'order' => 50,
            ],
            'theme_body_background_color' => [
                'value' => '#ffffff',
                'type' => 'color',
                'label' => 'Body Background Color',
                'description' => 'Page background color.',
                'section' => 'typography',
                'section_label' => 'Typography',
                'section_order' => 10,
                'category' => 'body',
                'category_label' => 'Body Text',
                'category_order' => 20,
                'order' => 60,
            ],
            'theme_color_primary' => [
                'value' => '#2563eb',
                'type' => 'color',
                'label' => 'Primary Color',
                'description' => 'Primary brand color used across interactive elements.',
                'section' => 'colors',
                'section_label' => 'Colors & Palette',
                'section_order' => 20,
                'category' => 'palette',
                'category_label' => 'Palette',
                'category_order' => 10,
                'order' => 10,
            ],
            'theme_color_secondary' => [
                'value' => '#64748b',
                'type' => 'color',
                'label' => 'Secondary Color',
                'description' => 'Secondary accent color for subtle highlights.',
                'section' => 'colors',
                'section_label' => 'Colors & Palette',
                'section_order' => 20,
                'category' => 'palette',
                'category_label' => 'Palette',
                'category_order' => 10,
                'order' => 20,
            ],
            'theme_color_accent' => [
                'value' => '#10b981',
                'type' => 'color',
                'label' => 'Accent Color',
                'description' => 'Accent color for badges and highlights.',
                'section' => 'colors',
                'section_label' => 'Colors & Palette',
                'section_order' => 20,
                'category' => 'palette',
                'category_label' => 'Palette',
                'category_order' => 10,
                'order' => 30,
            ],
            'theme_color_border' => [
                'value' => '#d1d5db',
                'type' => 'color',
                'label' => 'Border Color',
                'description' => 'Default border color for cards and widgets.',
                'section' => 'colors',
                'section_label' => 'Colors & Palette',
                'section_order' => 20,
                'category' => 'palette',
                'category_label' => 'Palette',
                'category_order' => 10,
                'order' => 40,
            ],
            'theme_surface_color' => [
                'value' => '#ffffff',
                'type' => 'color',
                'label' => 'Surface Color',
                'description' => 'Background color applied to cards, widgets, and panels.',
                'section' => 'colors',
                'section_label' => 'Colors & Palette',
                'section_order' => 20,
                'category' => 'palette',
                'category_label' => 'Palette',
                'category_order' => 10,
                'order' => 50,
            ],
            'theme_anchor_color' => [
                'value' => '#2563eb',
                'type' => 'color',
                'label' => 'Link Color',
                'description' => 'Default color for anchor links.',
                'section' => 'colors',
                'section_label' => 'Colors & Palette',
                'section_order' => 20,
                'category' => 'links',
                'category_label' => 'Links',
                'category_order' => 20,
                'order' => 10,
            ],
            'theme_anchor_hover_color' => [
                'value' => '#1e40af',
                'type' => 'color',
                'label' => 'Link Hover Color',
                'description' => 'Hover color for anchor links.',
                'section' => 'colors',
                'section_label' => 'Colors & Palette',
                'section_order' => 20,
                'category' => 'links',
                'category_label' => 'Links',
                'category_order' => 20,
                'order' => 20,
            ],
            'theme_container_max_width' => [
                'value' => 1200,
                'type' => 'number',
                'label' => 'Container Max Width (px)',
                'description' => 'Maximum width for container layouts.',
                'input_props' => ['min' => 960, 'max' => 1600, 'step' => 10, 'suffix' => 'px'],
                'section' => 'layout',
                'section_label' => 'Layout & Components',
                'section_order' => 30,
                'category' => 'layout',
                'category_label' => 'Layout',
                'category_order' => 10,
                'order' => 10,
            ],
            'theme_button_radius' => [
                'value' => 12,
                'type' => 'number',
                'label' => 'Button Border Radius (px)',
                'description' => 'Corner radius for buttons and interactive elements.',
                'input_props' => ['min' => 0, 'max' => 32, 'step' => 1, 'suffix' => 'px'],
                'section' => 'layout',
                'section_label' => 'Layout & Components',
                'section_order' => 30,
                'category' => 'components',
                'category_label' => 'Components',
                'category_order' => 20,
                'order' => 10,
            ],
            'theme_card_radius' => [
                'value' => 12,
                'type' => 'number',
                'label' => 'Card Border Radius (px)',
                'description' => 'Corner radius for cards and widget panels.',
                'input_props' => ['min' => 0, 'max' => 32, 'step' => 1, 'suffix' => 'px'],
                'section' => 'layout',
                'section_label' => 'Layout & Components',
                'section_order' => 30,
                'category' => 'components',
                'category_label' => 'Components',
                'category_order' => 20,
                'order' => 20,
            ],
            'theme_card_shadow' => [
                'value' => 'md',
                'type' => 'select',
                'label' => 'Card Shadow Style',
                'description' => 'Shadow intensity for cards and widgets.',
                'options' => [
                    ['label' => 'None', 'value' => 'none'],
                    ['label' => 'Soft', 'value' => 'sm'],
                    ['label' => 'Medium', 'value' => 'md'],
                    ['label' => 'Large', 'value' => 'lg'],
                ],
                'section' => 'layout',
                'section_label' => 'Layout & Components',
                'section_order' => 30,
                'category' => 'components',
                'category_label' => 'Components',
                'category_order' => 20,
                'order' => 30,
            ],
        ];
    }

    protected function getDefaultPermalinkSettings(): array
    {
        return [
            'permalink_posts_archive' => [
                'value' => 'posts',
                'type' => 'text',
                'label' => 'Posts Archive Base',
                'description' => 'Prefix for the posts archive page (e.g., "blog" results in /blog).',
                'section' => 'posts',
                'section_label' => 'Posts',
                'section_order' => 10,
                'order' => 10,
            ],
            'permalink_posts_single' => [
                'value' => 'posts',
                'type' => 'text',
                'label' => 'Single Post Base',
                'description' => 'Prefix for individual post URLs. Leave empty to serve posts at the root level.',
                'section' => 'posts',
                'section_label' => 'Posts',
                'section_order' => 10,
                'order' => 20,
                'allow_empty' => true,
            ],
            'permalink_pages_single' => [
                'value' => '',
                'type' => 'text',
                'label' => 'Pages Base',
                'description' => 'Prefix for static pages. Leave empty to serve pages at the root level.',
                'section' => 'pages',
                'section_label' => 'Pages',
                'section_order' => 20,
                'order' => 10,
                'allow_empty' => true,
            ],
            'permalink_products_archive' => [
                'value' => 'products',
                'type' => 'text',
                'label' => 'Products Archive Base',
                'description' => 'Prefix for the products listing page.',
                'section' => 'products',
                'section_label' => 'Products',
                'section_order' => 30,
                'order' => 10,
            ],
            'permalink_products_single' => [
                'value' => 'products',
                'type' => 'text',
                'label' => 'Single Product Base',
                'description' => 'Prefix for individual product URLs.',
                'section' => 'products',
                'section_label' => 'Products',
                'section_order' => 30,
                'order' => 20,
            ],
            'permalink_category_base' => [
                'value' => 'categories',
                'type' => 'text',
                'label' => 'Category Base',
                'description' => 'Prefix for category archive pages (used for posts and products).',
                'section' => 'taxonomies',
                'section_label' => 'Taxonomies',
                'section_order' => 40,
                'order' => 10,
            ],
            'permalink_post_tag_base' => [
                'value' => 'tags',
                'type' => 'text',
                'label' => 'Post Tag Base',
                'description' => 'Prefix for post tag archive pages.',
                'section' => 'taxonomies',
                'section_label' => 'Taxonomies',
                'section_order' => 40,
                'order' => 20,
            ],
            'permalink_product_tag_base' => [
                'value' => 'product-tags',
                'type' => 'text',
                'label' => 'Product Tag Base',
                'description' => 'Prefix for product tag archive pages.',
                'section' => 'taxonomies',
                'section_label' => 'Taxonomies',
                'section_order' => 40,
                'order' => 30,
            ],
            'permalink_product_category_base' => [
                'value' => 'product-categories',
                'type' => 'text',
                'label' => 'Product Category Base',
                'description' => 'Prefix for product category archive pages.',
                'section' => 'taxonomies',
                'section_label' => 'Taxonomies',
                'section_order' => 40,
                'order' => 40,
            ],
            'permalink_product_brand_base' => [
                'value' => 'product-brands',
                'type' => 'text',
                'label' => 'Product Brand Base',
                'description' => 'Prefix for product brand archive pages.',
                'section' => 'taxonomies',
                'section_label' => 'Taxonomies',
                'section_order' => 40,
                'order' => 50,
            ],
            'permalink_user_base' => [
                'value' => 'author',
                'type' => 'text',
                'label' => 'Author Base',
                'description' => 'Prefix for author archive pages.',
                'section' => 'users',
                'section_label' => 'Users',
                'section_order' => 50,
                'order' => 10,
            ],
        ];
    }

    public function prepareSettingsForGroup(string $group, array $settings): array
    {
        if ($group !== 'permalinks') {
            return $settings;
        }

        $definitions = $this->getDefaultPermalinkSettings();

        foreach ($settings as $key => &$value) {
            if (!is_string($value) && $value !== null) {
                $value = (string) $value;
            }

            if (!isset($definitions[$key])) {
                $value = $this->sanitizePermalinkSegment($value ?? '', false);
                continue;
            }

            $allowEmpty = (bool) ($definitions[$key]['allow_empty'] ?? false);
            $value = $this->sanitizePermalinkSegment($value ?? '', $allowEmpty);

            if ($value === '' && !$allowEmpty) {
                $value = $definitions[$key]['value'];
            }
        }

        unset($value);

        return $settings;
    }

    public function getPermalinkStructure(): array
    {
        $definitions = $this->getDefaultPermalinkSettings();
        $settings = $this->getGroupSettings('permalinks');

        $resolve = function (string $key) use ($settings, $definitions) {
            $definition = $definitions[$key] ?? ['value' => ''];
            $allowEmpty = (bool) ($definition['allow_empty'] ?? false);
            $value = $settings[$key]['value'] ?? $definition['value'];
            $value = $this->sanitizePermalinkSegment($value ?? '', $allowEmpty);

            if ($value === '' && !$allowEmpty) {
                $value = $this->sanitizePermalinkSegment($definition['value'] ?? '', $allowEmpty);
            }

            return $value;
        };

        $structure = [
            'posts' => [
                'archive' => $resolve('permalink_posts_archive'),
                'single' => $resolve('permalink_posts_single'),
            ],
            'pages' => [
                'single' => $resolve('permalink_pages_single'),
            ],
            'products' => [
                'archive' => $resolve('permalink_products_archive'),
                'single' => $resolve('permalink_products_single'),
            ],
            'categories' => [
                'single' => $resolve('permalink_category_base'),
            ],
            'product_categories' => [
                'single' => $resolve('permalink_product_category_base'),
            ],
            'product_brands' => [
                'single' => $resolve('permalink_product_brand_base'),
            ],
            'tags' => [
                'post' => $resolve('permalink_post_tag_base'),
                'product' => $resolve('permalink_product_tag_base'),
            ],
            'users' => [
                'single' => $resolve('permalink_user_base'),
            ],
        ];

        return Hook::applyFilters('settings.permalinks.structure', $structure, $this);
    }

    protected function sanitizePermalinkSegment(?string $value, bool $allowEmpty = false): string
    {
        $value = trim((string) ($value ?? ''));
        $value = trim($value, '/');

        if ($value === '') {
            return $allowEmpty ? '' : '';
        }

        $segments = array_filter(explode('/', $value), fn ($segment) => $segment !== '');
        $segments = array_map(fn ($segment) => Str::slug($segment), $segments);
        $sanitized = implode('/', array_filter($segments, fn ($segment) => $segment !== ''));

        if ($sanitized === '') {
            return $allowEmpty ? '' : '';
        }

        return $sanitized;
    }
}
