<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar' => $request->user()->avatar ?? 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($request->user()->email))) . '?s=200&d=mp',
                ] : null,
            ],
            'settings' => [
                'version' => config('app.version'),
                'laravel_version' => app()->version(),
                'brand_logo' => app(\App\Services\SettingsService::class)->get('brand_logo'),
                'brand_name' => app(\App\Services\SettingsService::class)->get('brand_name', 'PolyCMS'),
                'currency' => [
                    'code' => app(\App\Services\SettingsService::class)->get('ecommerce_currency', 'USD'),
                    'symbol' => app(\App\Services\SettingsService::class)->get('ecommerce_currency_symbol', '$'),
                    'symbol_position' => app(\App\Services\SettingsService::class)->get('currency_symbol_position', 'before'),
                    'thousands_separator' => app(\App\Services\SettingsService::class)->get('currency_thousands_separator', ','),
                    'decimal_separator' => app(\App\Services\SettingsService::class)->get('currency_decimal_separator', '.'),
                    'decimals' => (int) app(\App\Services\SettingsService::class)->get('currency_decimals', 2),
                    'space_between' => (bool) app(\App\Services\SettingsService::class)->get('currency_space', false),
                ],
                'currencies' => (function() {
                    $currencies = app(\App\Services\SettingsService::class)->get('currencies', []);
                    return is_string($currencies) ? json_decode($currencies, true) : $currencies;
                })(),
                'auth_appearance' => app(\App\Services\SettingsService::class)->getGroupSettings('auth_appearance'),
            ],
            'csrf_token' => csrf_token(),
        ];
    }
}
