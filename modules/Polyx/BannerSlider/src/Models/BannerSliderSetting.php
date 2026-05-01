<?php

declare(strict_types=1);

namespace Modules\Polyx\BannerSlider\Models;

use Illuminate\Database\Eloquent\Model;

class BannerSliderSetting extends Model
{
    protected $table = 'banner_slider_settings';

    public $timestamps = true;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings as array
     */
    public static function allAsArray(): array
    {
        try {
            $settings = static::all();
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->value;
            }
            return $result;
        } catch (\Exception $e) {
            // Return default settings if table doesn't exist or has issues
            return [
                'auto_slide' => '1',
                'auto_slide_interval' => '5000',
                'transition_effect' => 'slide',
                'show_navigation' => '1',
                'show_indicators' => '1',
            ];
        }
    }
}
