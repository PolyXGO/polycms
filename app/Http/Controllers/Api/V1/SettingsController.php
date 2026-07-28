<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\Concerns\AuthorizesPermissions;
use App\Services\SettingsService;
use App\Helpers\LanguageHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use AuthorizesPermissions;

    public function __construct(
        protected SettingsService $settingsService
    ) {
        // Ensure LanguageHelper is initialized
        LanguageHelper::init($this->settingsService);
    }

    /**
     * Translate settings labels and descriptions
     */
    protected function translateSettings(array $settings): array
    {
        foreach ($settings as $group => &$groupSettings) {
            foreach ($groupSettings as $key => &$setting) {
                if (isset($setting['label'])) {
                    $setting['label'] = _l($setting['label']);
                }
                if (isset($setting['description'])) {
                    $setting['description'] = _l($setting['description']);
                }
            }
        }
        return $settings;
    }

    /**
     * Get all settings grouped by group
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'view settings');

        $locale = $request->input('locale');
        $settings = $this->settingsService->getAllSettings($locale);
        $settings = $this->translateSettings($settings);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get safe public settings (e.g. for login page)
     */
    public function publicSettings(): JsonResponse
    {
        $general = collect($this->settingsService->getGroupSettings('general'))->mapWithKeys(function ($setting, $key) {
            return [$key => $setting['value'] ?? ($setting['default'] ?? null)];
        })->toArray();

        $authApp = collect($this->settingsService->getGroupSettings('auth_appearance'))->mapWithKeys(function ($setting, $key) {
            return [$key => $setting['value'] ?? ($setting['default'] ?? null)];
        })->toArray();
        
        $moduleManager = app(\App\Services\ModuleManager::class);
        $allActiveModules = $moduleManager->getEnabledModules();
        
        // Only expose explicitly allowed modules to the public API to prevent information disclosure
        $publicModules = array_values(array_filter($allActiveModules, function($module) {
            $lower = strtolower($module);
            return str_contains($lower, 'demo');
        }));
        
        $activeModules = \App\Facades\Hook::applyFilters('modules.public_active', $publicModules, $allActiveModules);
        
        $externalAuth = [
            'google_enabled'   => false,
            'facebook_enabled' => false,
            'github_enabled'   => false,
        ];

        try {
            $externalAuthSettings = collect($this->settingsService->getGroupSettings('external_auth'))->mapWithKeys(function ($setting, $key) {
                return [$key => $setting['value'] ?? ($setting['default'] ?? null)];
            })->toArray();

            $externalAuth['google_enabled'] = filter_var($externalAuthSettings['external_auth_google_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $externalAuth['facebook_enabled'] = filter_var($externalAuthSettings['external_auth_facebook_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $externalAuth['github_enabled'] = filter_var($externalAuthSettings['external_auth_github_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        } catch (\Throwable $e) {
            // Ignore if settings group not found or fails
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'version' => config('app.version'),
                'laravel_version' => app()->version(),
                'active_modules' => $activeModules,
                'general' => [
                    'brand_name' => $general['brand_name'] ?? 'POLYCMS',
                    'brand_logo' => $general['brand_logo'] ?? null,
                ],
                'auth_appearance' => $authApp,
                'external_auth' => $externalAuth
            ],
        ]);
    }

    /**
     * Get settings by group
     */
    public function getGroup(Request $request, string $group): JsonResponse
    {
        $this->authorizePermission($request, 'view settings');

        $locale = $request->input('locale');
        $settings = $this->settingsService->getGroupSettings($group, $locale);
        
        // Translate labels and descriptions
        foreach ($settings as $key => &$setting) {
            if (isset($setting['label'])) {
                $setting['label'] = _l($setting['label']);
            }
            if (isset($setting['description'])) {
                $setting['description'] = _l($setting['description']);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get a single setting
     */
    public function show(Request $request, string $key): JsonResponse
    {
        $this->authorizePermission($request, 'view settings');

        $value = $this->settingsService->get($key);

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $key,
                'value' => $value,
            ],
        ]);
    }

    /**
     * Update settings (can update single or multiple)
     */
    public function update(Request $request, ?string $group = null): JsonResponse
    {
        $this->authorizePermission($request, 'manage settings');

        $validated = $request->validate([
            // Single setting update
            'key' => ['sometimes', 'required_without:settings', 'string'],
            'value' => ['sometimes', 'required_with:key'],
            
            // Multiple settings update
            'settings' => ['sometimes', 'required_without:key', 'array'],
        ]);

        $targetGroup = $group ?? 'general';
        $locale = $request->input('locale');

        if (isset($validated['key'])) {
            // Single setting update
            $value = $validated['value'];
            if ($targetGroup === 'permalinks') {
                $value = $this->settingsService->prepareSettingsForGroup($targetGroup, [
                    $validated['key'] => $value,
                ])[$validated['key']] ?? $value;
            }

            $definition = $this->settingsService->getDefinition($validated['key']);
            $type = $definition['type'] ?? 'string';

            $this->settingsService->set(
                $validated['key'],
                $value,
                $targetGroup,
                $type,
                true,
                $locale
            );
        } else {
            // Multiple settings update
            $settingsPayload = $validated['settings'];
            $settingsPayload = $this->settingsService->prepareSettingsForGroup($targetGroup, $settingsPayload);

            $this->settingsService->setMultiple(
                $settingsPayload,
                $targetGroup,
                $locale
            );
        }

        $settings = $this->settingsService->getGroupSettings($targetGroup, $locale);
        
        // Translate labels and descriptions
        foreach ($settings as $key => &$setting) {
            if (isset($setting['label'])) {
                $setting['label'] = _l($setting['label']);
            }
            if (isset($setting['description'])) {
                $setting['description'] = _l($setting['description']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => _l('Settings updated successfully'),
            'data' => $settings,
        ]);
    }

    /**
     * Get settings categories and items for the Settings Hub dynamically.
     */
    public function getSettingsHub(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'view settings');

        $categories = [
            [
                'name' => 'Common',
                'items' => [
                    [
                        'key' => 'general',
                        'label' => 'General',
                        'description' => 'View and update your general settings and site information',
                        'icon' => 'Cog6ToothIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'general']],
                    ],
                    [
                        'key' => 'auth_appearance',
                        'label' => 'Login Appearance',
                        'description' => 'Customize the login page layout, backgrounds, and brand aesthetics',
                        'icon' => 'PaintBrushIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'auth_appearance']],
                    ],
                    [
                        'key' => 'admin_appearance',
                        'label' => 'Admin Appearance',
                        'description' => 'Customize the admin dashboard theme, colors, and layout',
                        'icon' => 'PaintBrushIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'admin_appearance']],
                    ],
                    [
                        'key' => 'reading',
                        'label' => 'Reading',
                        'description' => 'Configure your homepage and content display preferences',
                        'icon' => 'BookOpenIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'reading']],
                    ],
                    [
                        'key' => 'email',
                        'label' => 'Email',
                        'description' => 'Configure your mail server and sender information',
                        'icon' => 'EnvelopeIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'email']],
                    ],
                    [
                        'key' => 'email_templates',
                        'label' => 'Email templates',
                        'description' => 'Customize notification templates using variables',
                        'icon' => 'DocumentTextIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'email_templates']],
                    ],
                    [
                        'key' => 'media',
                        'label' => 'Media',
                        'description' => 'Manage media upload sizes settings',
                        'icon' => 'PhotoIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'media']],
                    ],
                    [
                        'key' => 'api_settings',
                        'label' => 'API Settings',
                        'description' => 'Configure external API access and keys',
                        'icon' => 'CommandLineIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'api_settings']],
                    ],
                    [
                        'key' => 'template_defaults',
                        'label' => 'Template Defaults',
                        'description' => 'Set default themes and templates for pages, posts, and products.',
                        'icon' => 'PaintBrushIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'template_defaults']],
                    ],
                    [
                        'key' => 'webhooks',
                        'label' => 'Webhooks',
                        'description' => 'Configure incoming and outgoing webhook endpoints.',
                        'icon' => 'LinkIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'webhooks']],
                    ],
                    [
                        'key' => 'socials',
                        'label' => 'Socials',
                        'description' => 'Configure social media profiles and links for widgets and templates.',
                        'icon' => 'LinkIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'socials']],
                    ],
                    [
                        'key' => 'custom_icons',
                        'label' => 'Custom Icons',
                        'description' => 'Manage custom SVG icons for frontend and backend use.',
                        'icon' => 'PaintBrushIcon',
                        'route' => ['name' => 'admin.settings.custom_icons'],
                    ],
                    [
                        'key' => 'contacts',
                        'label' => 'Contacts',
                        'description' => 'Configure contact forms settings, Google reCAPTCHA, and submission preferences.',
                        'icon' => 'EnvelopeIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'contacts']],
                    ],
                    [
                        'key' => 'languages',
                        'label' => 'Languages',
                        'description' => 'Manage store languages, localizations and active locales',
                        'icon' => 'GlobeAltIcon',
                        'route' => ['name' => 'admin.settings.languages'],
                    ],
                    [
                        'key' => 'translations',
                        'label' => 'Translations',
                        'description' => 'Edit theme and module translation strings',
                        'icon' => 'LanguageIcon',
                        'action' => 'translations',
                    ],
                ]
            ],
            [
                'name' => 'Ecommerce',
                'items' => [
                    [
                        'key' => 'ecommerce_general',
                        'label' => 'General',
                        'description' => 'Basic store configuration and contact info',
                        'icon' => 'ShoppingBagIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'ecommerce']],
                    ],
                    [
                        'key' => 'currencies',
                        'label' => 'Currencies',
                        'description' => 'Manage store currencies, formatting and exchange rates',
                        'icon' => 'BanknotesIcon',
                        'route' => ['name' => 'admin.settings.ecommerce.currencies'],
                    ],
                    [
                        'key' => 'payment_gateways',
                        'label' => 'Payment Gateways',
                        'description' => 'Configure PayPal, Stripe, Bank Transfer, etc.',
                        'icon' => 'CreditCardIcon',
                        'route' => ['name' => 'admin.settings.gateways'],
                    ],
                    [
                        'key' => 'coupons',
                        'label' => 'Checkout & Coupons',
                        'description' => 'Global checkout behavior and discount rules',
                        'icon' => 'ReceiptPercentIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'checkout']],
                    ],
                    [
                        'key' => 'invoices',
                        'label' => 'Invoices',
                        'description' => 'Manage invoice numbering and company details',
                        'icon' => 'QueueListIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'invoices']],
                    ],
                    [
                        'key' => 'refund_policy',
                        'label' => 'Refund Policy',
                        'description' => 'Configure default refund request window and fallback policy note',
                        'icon' => 'DocumentTextIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'refund_policy']],
                    ],
                    [
                        'key' => 'global_faqs',
                        'label' => "Global FAQ's",
                        'description' => 'Manage reusable FAQ content for product detail pages',
                        'icon' => 'QuestionMarkCircleIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'global_faqs']],
                    ],
                    [
                        'key' => 'global_tabs',
                        'label' => 'Global Tabs',
                        'description' => 'Manage reusable custom tabs for product detail pages',
                        'icon' => 'QueueListIcon',
                        'route' => ['name' => 'admin.settings.group', 'params' => ['group' => 'global_tabs']],
                    ]
                ]
            ],
            [
                'name' => 'System',
                'items' => [
                    [
                        'key' => 'system_update',
                        'label' => 'System Update',
                        'description' => 'Update PolyCMS core from an official package',
                        'icon' => 'ArrowPathIcon',
                        'route' => ['name' => 'admin.settings.system-update'],
                    ],
                    [
                        'key' => 'system_info',
                        'label' => 'System Info',
                        'description' => 'View system version, PHP info, and server environment',
                        'icon' => 'InformationCircleIcon',
                        'route' => ['name' => 'admin.settings.system-info'],
                    ],
                    [
                        'key' => 'cache',
                        'label' => 'Cache',
                        'description' => 'View cache status and clear application, view, config, and OPcache caches',
                        'icon' => 'CircleStackIcon',
                        'route' => ['name' => 'admin.settings.cache'],
                    ]
                ]
            ]
        ];

        // Apply filter hook
        $categories = \App\Facades\Hook::applyFilters('admin.settings.hub.categories', $categories);

        // Force sequential array for outer list
        $categories = array_values($categories);

        // Translate labels/descriptions recursively and force sequential arrays for items
        foreach ($categories as &$category) {
            if (isset($category['items']) && is_array($category['items'])) {
                $category['items'] = array_values($category['items']);
                foreach ($category['items'] as &$item) {
                    if (isset($item['label'])) {
                        $item['label'] = _l($item['label']);
                    }
                    if (isset($item['description'])) {
                        $item['description'] = _l($item['description']);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Initialize default settings
     */
    public function initialize(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'manage settings');

        $this->settingsService->initializeDefaults();

        return response()->json([
            'success' => true,
            'message' => 'Default settings initialized successfully',
        ]);
    }
}
