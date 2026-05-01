<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use App\Helpers\LanguageHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
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
    public function index(): JsonResponse
    {
        $settings = $this->settingsService->getAllSettings();
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
        $general = $this->settingsService->getGroupSettings('general');
        $authApp = $this->settingsService->getGroupSettings('auth_appearance');
        
        return response()->json([
            'success' => true,
            'data' => [
                'version' => config('app.version'),
                'laravel_version' => app()->version(),
                'general' => [
                    'brand_name' => $general['brand_name'] ?? 'POLYCMS',
                    'brand_logo' => $general['brand_logo'] ?? null,
                ],
                'auth_appearance' => $authApp
            ],
        ]);
    }

    /**
     * Get settings by group
     */
    public function getGroup(string $group): JsonResponse
    {
        $settings = $this->settingsService->getGroupSettings($group);
        
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
    public function show(string $key): JsonResponse
    {
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
        $validated = $request->validate([
            // Single setting update
            'key' => ['sometimes', 'required_without:settings', 'string'],
            'value' => ['sometimes', 'required_with:key'],
            
            // Multiple settings update
            'settings' => ['sometimes', 'required_without:key', 'array'],
        ]);

        $targetGroup = $group ?? 'general';

        if (isset($validated['key'])) {
            // Single setting update
            $value = $validated['value'];
            if ($targetGroup === 'permalinks') {
                $value = $this->settingsService->prepareSettingsForGroup($targetGroup, [
                    $validated['key'] => $value,
                ])[$validated['key']] ?? $value;
            }

            $this->settingsService->set(
                $validated['key'],
                $value,
                $targetGroup
            );
        } else {
            // Multiple settings update
            $settingsPayload = $validated['settings'];
            $settingsPayload = $this->settingsService->prepareSettingsForGroup($targetGroup, $settingsPayload);

            $this->settingsService->setMultiple(
                $settingsPayload,
                $targetGroup
            );
        }

        $settings = $this->settingsService->getGroupSettings($targetGroup);
        
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
     * Initialize default settings
     */
    public function initialize(): JsonResponse
    {
        $this->settingsService->initializeDefaults();

        return response()->json([
            'success' => true,
            'message' => 'Default settings initialized successfully',
        ]);
    }
}