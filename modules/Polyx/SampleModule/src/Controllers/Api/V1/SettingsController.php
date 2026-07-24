<?php

declare(strict_types=1);

namespace Modules\Polyx\SampleModule\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Settings Controller — Module Settings via SettingsService
 *
 * This controller demonstrates the recommended pattern for module settings:
 * - Use SettingsService for persistent storage (database-backed)
 * - Prefix all setting keys with your module name (e.g., sample_module_)
 * - Validate input before saving
 * - Return consistent JSON responses
 *
 * API Endpoints:
 * - GET  /api/v1/sample-module/settings  → index()
 * - PUT  /api/v1/sample-module/settings  → update()
 */
class SettingsController extends Controller
{
    /**
     * Module setting keys with their defaults.
     *
     * Convention: prefix all keys with your module slug + underscore.
     * This prevents collisions with other modules' settings.
     */
    private const SETTING_KEYS = [
        'sample_module_content_badge'  => 'no',      // yes/no — show reading time badge
        'sample_module_notes_per_page' => '10',       // pagination size
        'sample_module_badge_style'    => 'default',  // badge style variant
    ];

    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * Get all module settings.
     *
     * Returns current values from database, falling back to defaults.
     */
    public function index(): JsonResponse
    {
        $data = [];
        foreach (self::SETTING_KEYS as $key => $default) {
            $data[$key] = $this->settings->get($key, $default);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Update module settings.
     *
     * Uses SettingsService::set() for persistent database storage.
     * Settings are stored in the `settings` table with key-value pairs.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sample_module_content_badge'  => ['sometimes', 'in:yes,no'],
            'sample_module_notes_per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
            'sample_module_badge_style'    => ['sometimes', 'in:default,minimal,colorful'],
        ]);

        // Save each validated setting to database
        foreach ($validated as $key => $value) {
            if (array_key_exists($key, self::SETTING_KEYS)) {
                $this->settings->set($key, (string) $value);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully',
        ]);
    }
}
