<?php

declare(strict_types=1);

namespace Modules\Polyx\BannerSlider\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Polyx\BannerSlider\Models\BannerSliderSetting;
use Modules\Polyx\BannerSlider\Services\BannerService;

class SettingsController extends Controller
{
    /**
     * Get banner slider settings
     */
    public function index(): JsonResponse
    {
        $bannerService = app(BannerService::class);
        $settings = $bannerService->getSettings();

        return $this->successResponse($settings);
    }

    /**
     * Update banner slider settings
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'auto_slide' => ['sometimes', 'boolean'],
                'auto_slide_interval' => ['sometimes', 'integer', 'min:100', 'max:30000'],
                'transition_effect' => ['sometimes', 'in:slide,zoom,zoom-in,zoom-out,fade,fade-in,fade-out,slide-top,slide-bottom,slide-left,slide-right'],
                'show_navigation' => ['sometimes', 'boolean'],
                'show_indicators' => ['sometimes', 'boolean'],
            ]);

            // Only process valid setting keys
            $validKeys = ['auto_slide', 'auto_slide_interval', 'transition_effect', 'show_navigation', 'show_indicators'];

            foreach ($request->all() as $key => $value) {
                if (!in_array($key, $validKeys)) {
                    continue;
                }

                try {
                    if (in_array($key, ['auto_slide', 'show_navigation', 'show_indicators'])) {
                        // Convert boolean to string '1' or '0'
                        $stringValue = ($value === true || $value === 'true' || $value === 1 || $value === '1') ? '1' : '0';
                        BannerSliderSetting::set($key, $stringValue);
                    } else {
                        BannerSliderSetting::set($key, (string) $value);
                    }
                } catch (\Exception $e) {
                    \Log::error("Error setting banner slider setting {$key}: " . $e->getMessage());
                    throw new \Exception("Failed to save setting '{$key}': " . $e->getMessage());
                }
            }

            $bannerService = app(BannerService::class);
            $settings = $bannerService->getSettings();

            return $this->successResponse($settings, 'Settings updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error("Error updating banner slider settings: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return $this->errorResponse($e->getMessage(), 'SETTINGS_UPDATE_ERROR', [], 500);
        }
    }
}
