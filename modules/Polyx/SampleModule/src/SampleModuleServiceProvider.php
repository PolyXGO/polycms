<?php

declare(strict_types=1);

namespace Modules\Polyx\SampleModule;

use App\Facades\Hook;
use App\Services\MenuRegistry;
use Illuminate\Support\ServiceProvider;

class SampleModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register admin menu items
        Hook::addAction('admin.menu.build', function () {
            $menuRegistry = app(MenuRegistry::class);
            
            $menuRegistry->register('sample-module', [
                'key' => 'sample-module',
                'label' => 'Sample Module',
                'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                'order' => 100,
                'children' => [
                    [
                        'key' => 'sample-module-settings',
                        'label' => 'Settings',
                        'route' => 'admin.sample-module.settings',
                        'icon' => null,
                        'order' => 1,
                    ],
                ],
            ]);
        }, 10);

        // Add content after post title using filter
        Hook::addFilter('post.content.render', function ($content, $post) {
            // Get settings (in real module, this would be from database)
            $settings = $this->getSettings();
            
            if (!$settings['enabled'] || empty($settings['additional_content'])) {
                return $content;
            }

            // Add content after title
            $additionalContent = '<div class="sample-module-content" style="' . ($settings['style'] ?? '') . '">' . 
                                htmlspecialchars($settings['additional_content']) . 
                                '</div>';

            // If content has title, insert after it
            if (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $content, $matches)) {
                $titleWithTag = $matches[0];
                $content = str_replace($titleWithTag, $titleWithTag . $additionalContent, $content);
            } else {
                // Otherwise, prepend to content
                $content = $additionalContent . $content;
            }

            return $content;
        }, 10);

        // Register routes
        $this->loadRoutes();
    }

    /**
     * Get module settings
     * In a real module, this would fetch from database
     */
    protected function getSettings(): array
    {
        return [
            'enabled' => true,
            'additional_content' => 'This content was added by Sample Module after the post title.',
            'style' => 'padding: 10px; background: #f0f0f0; border-left: 3px solid #3b82f6; margin: 20px 0;',
        ];
    }

    /**
     * Load module routes
     */
    protected function loadRoutes(): void
    {
        $this->app['router']->middleware(['web', 'auth:sanctum'])->prefix('admin/sample-module')->name('admin.sample-module.')->group(function () {
            $this->app['router']->get('settings', [Controllers\SettingsController::class, 'index'])->name('settings');
            $this->app['router']->post('settings', [Controllers\SettingsController::class, 'store'])->name('settings.store');
        });
    }
}
