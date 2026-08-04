<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Inertia\Support\Header;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * Handle the incoming request.
     *
     * Overrides parent to merge Vary header instead of overwriting.
     * The upstream Inertia package hardcodes set('Vary', 'X-Inertia')
     * which destroys Vary values (e.g. Accept-Encoding) set by downstream
     * middlewares like PageCacheMiddleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = parent::handle($request, $next);

        // Re-merge Vary: parent::handle() overwrites with just 'X-Inertia'
        $existingVary = array_filter(array_map('trim', explode(',', (string) $response->headers->get('Vary', ''))));
        if (!in_array('Accept-Encoding', $existingVary, true)) {
            $existingVary[] = 'Accept-Encoding';
        }
        $response->headers->set('Vary', implode(', ', $existingVary));

        return $response;
    }
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
                    'addresses' => $request->user()->addresses()->get(),
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
                'admin_theme' => app(\App\Services\SettingsService::class)->get('admin_theme', 'nebula'),
            ],
            'csrf_token' => csrf_token(),
            'demo_restriction' => fn () => $request->session()->get('demo_restriction'),
            'account_menu_extra' => fn () => $this->getAccountMenuExtra(),
        ];
    }

    /**
     * Collect extra account menu items from modules via hook.
     *
     * @return array
     */
    protected function getAccountMenuExtra(): array
    {
        try {
            $registry = app(\App\Services\AccountMenuRegistry::class);
            \App\Facades\Hook::doAction('account.menu.build');
            $items = array_values($registry->all());
            $registry->clear(); // Reset for next request
            return $items;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
