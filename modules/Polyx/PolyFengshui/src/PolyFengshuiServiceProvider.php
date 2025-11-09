<?php

namespace Modules\Polyx\PolyFengshui;

use App\Facades\Hook;
use App\Services\MenuRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PolyFengshuiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerMenu();
        $this->registerRoutes();
        $this->registerGraphQL();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth:sanctum'])
            ->prefix('admin-api/polyfengshui')
            ->name('admin-api.polyfengshui.')
            ->group(__DIR__ . '/routes/admin.php');
    }

    protected function registerGraphQL(): void
    {
        $namespaces = (array) config('lighthouse.namespaces.queries');

        if (!in_array('Modules\\Polyx\\PolyFengshui\\GraphQL\\Queries', $namespaces, true)) {
            config([
                'lighthouse.namespaces.queries' => array_merge(
                    $namespaces,
                    ['Modules\\Polyx\\PolyFengshui\\GraphQL\\Queries']
                ),
            ]);
        }

        $mutationNamespaces = (array) config('lighthouse.namespaces.mutations');

        if (!in_array('Modules\\Polyx\\PolyFengshui\\GraphQL\\Mutations', $mutationNamespaces, true)) {
            config([
                'lighthouse.namespaces.mutations' => array_merge(
                    $mutationNamespaces,
                    ['Modules\\Polyx\\PolyFengshui\\GraphQL\\Mutations']
                ),
            ]);
        }
    }

    protected function registerMenu(): void
    {
        Hook::addAction('admin.menu.build', function () {
            $menuRegistry = app(MenuRegistry::class);

            $menuRegistry->register('polyfengshui', [
                'key' => 'polyfengshui',
                'label' => 'PolyFengshui',
                'icon' => 'M4 6h16M4 12h16M4 18h16',
                'order' => 120,
                'children' => [
                    [
                        'key' => 'polyfengshui-tokens',
                        'label' => 'API Tokens',
                        'route' => 'admin.polyfengshui.tokens',
                        'icon' => null,
                        'order' => 1,
                    ],
                    [
                        'key' => 'polyfengshui-docs',
                        'label' => 'Docs',
                        'route' => 'admin.polyfengshui.docs',
                        'icon' => null,
                        'order' => 2,
                    ],
                ],
            ]);
        }, 20);
    }
}

