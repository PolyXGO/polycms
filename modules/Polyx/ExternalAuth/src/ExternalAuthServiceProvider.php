<?php

declare(strict_types=1);

namespace Modules\Polyx\ExternalAuth;

use App\Facades\Hook;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ExternalAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No services to bind
    }

    public function boot(): void
    {
        $this->registerSettingsDefaults();
        $this->registerAdminMenu();
        $this->registerInertiaShare();
        $this->loadRoutes();
    }

    /**
     * Register default configuration values
     */
    protected function registerSettingsDefaults(): void
    {
        Hook::addFilter('settings.defaults', function (array $defaults): array {
            $defaults['external_auth'] = [
                'external_auth_google_enabled' => [
                    'key'         => 'external_auth_google_enabled',
                    'value'       => '0',
                    'type'        => 'boolean',
                    'label'       => 'Enable Google Login',
                    'description' => 'Allow users to log in / register using their Google account.',
                ],
                'external_auth_google_client_id' => [
                    'key'         => 'external_auth_google_client_id',
                    'value'       => '',
                    'type'        => 'string',
                    'label'       => 'Google Client ID',
                    'description' => 'OAuth 2.0 Client ID generated in Google Developer Console.',
                ],
                'external_auth_google_client_secret' => [
                    'key'         => 'external_auth_google_client_secret',
                    'value'       => '',
                    'type'        => 'password',
                    'label'       => 'Google Client Secret',
                    'description' => 'OAuth 2.0 Client Secret generated in Google Developer Console.',
                ],
                'external_auth_facebook_enabled' => [
                    'key'         => 'external_auth_facebook_enabled',
                    'value'       => '0',
                    'type'        => 'boolean',
                    'label'       => 'Enable Facebook Login',
                    'description' => 'Allow users to log in / register using their Facebook account.',
                ],
                'external_auth_facebook_client_id' => [
                    'key'         => 'external_auth_facebook_client_id',
                    'value'       => '',
                    'type'        => 'string',
                    'label'       => 'Facebook Client ID',
                    'description' => 'App ID generated in Facebook Developers Portal.',
                ],
                'external_auth_facebook_client_secret' => [
                    'key'         => 'external_auth_facebook_client_secret',
                    'value'       => '',
                    'type'        => 'password',
                    'label'       => 'Facebook Client Secret',
                    'description' => 'App Secret generated in Facebook Developers Portal.',
                ],
                'external_auth_github_enabled' => [
                    'key'         => 'external_auth_github_enabled',
                    'value'       => '0',
                    'type'        => 'boolean',
                    'label'       => 'Enable GitHub Login',
                    'description' => 'Allow users to log in / register using their GitHub account.',
                ],
                'external_auth_github_client_id' => [
                    'key'         => 'external_auth_github_client_id',
                    'value'       => '',
                    'type'        => 'string',
                    'label'       => 'GitHub Client ID',
                    'description' => 'Client ID generated in GitHub Settings -> Developer settings -> OAuth Apps.',
                ],
                'external_auth_github_client_secret' => [
                    'key'         => 'external_auth_github_client_secret',
                    'value'       => '',
                    'type'        => 'password',
                    'label'       => 'GitHub Client Secret',
                    'description' => 'Client Secret generated in GitHub Settings -> Developer settings -> OAuth Apps.',
                ],
            ];
            return $defaults;
        }, 10, 2);

        Hook::addFilter('admin.settings.hub.categories', function (array $categories): array {
            foreach ($categories as &$category) {
                if ($category['name'] === 'Common') {
                    $category['items'][] = [
                        'key'         => 'external_auth',
                        'label'       => 'External Auth',
                        'description' => 'Configure third-party login integration (Google, Facebook, GitHub) for authentication.',
                        'icon'        => 'ShieldCheckIcon',
                        'route'       => ['name' => 'admin.settings.group', 'params' => ['group' => 'external_auth']],
                    ];
                }
            }
            return $categories;
        });
    }

    /**
     * Add settings sub-menu in the admin dashboard
     */
    protected function registerAdminMenu(): void
    {
        Hook::addAction('admin.menu.build', function () {
            $menuRegistry = app(\App\Services\MenuRegistry::class);
            $menuRegistry->addChild('settings', [
                'key'        => 'settings-external-auth',
                'label'      => 'External Auth',
                'route'      => 'admin.settings.group',
                'urlParams'  => ['group' => 'external_auth'],
                'permission' => 'manage settings',
                'order'      => 75,
            ]);
        });
    }

    /**
     * Share configuration settings globally with Inertia views
     */
    protected function registerInertiaShare(): void
    {
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::share('external_auth', function() {
                return [
                    'google_enabled'   => (bool) app(\App\Services\SettingsService::class)->get('external_auth_google_enabled', false),
                    'facebook_enabled' => (bool) app(\App\Services\SettingsService::class)->get('external_auth_facebook_enabled', false),
                    'github_enabled'   => (bool) app(\App\Services\SettingsService::class)->get('external_auth_github_enabled', false),
                ];
            });
        }
    }

    /**
     * Load OAuth2 routes
     */
    protected function loadRoutes(): void
    {
        Route::middleware(['web'])
            ->group(function () {
                Route::get('external-auth/redirect/{provider}', [Http\Controllers\ExternalAuthController::class, 'redirectToProvider'])
                    ->name('external-auth.redirect');
                Route::get('external-auth/callback/{provider}', [Http\Controllers\ExternalAuthController::class, 'handleProviderCallback'])
                    ->name('external-auth.callback');
            });
    }
}
