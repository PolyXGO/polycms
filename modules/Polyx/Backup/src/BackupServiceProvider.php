<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup;

use App\Facades\Hook;
use Illuminate\Support\ServiceProvider;
use Modules\Polyx\Backup\Contracts\DatabaseDumperInterface;
use Modules\Polyx\Backup\Drivers\Database\PostgresDumper;
use Modules\Polyx\Backup\Drivers\Database\MySqlDumper;
use Modules\Polyx\Backup\Services\BackupManager;
use Modules\Polyx\Backup\Services\IntegrityService;
use Modules\Polyx\Backup\Services\MaintenanceModeService;
use Illuminate\Console\Scheduling\Schedule;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Artisan commands
        $this->commands([
            Console\Commands\BackupRunCommand::class,
            Console\Commands\BackupRestoreCommand::class,
            Console\Commands\BackupCleanCommand::class,
            Console\Commands\BackupListCommand::class,
        ]);

        // Register DatabaseDumper based on configured DB driver
        $this->app->singleton(DatabaseDumperInterface::class, function ($app) {
            $driver = config('database.default');
            $connection = config("database.connections.{$driver}.driver", $driver);

            return match ($connection) {
                'pgsql' => new PostgresDumper(),
                'mysql', 'mariadb' => new MySqlDumper(),
                default => new PostgresDumper(), // Default to PostgreSQL
            };
        });

        // Register IntegrityService
        $this->app->singleton(IntegrityService::class, fn () => new IntegrityService());

        // Register MaintenanceModeService
        $this->app->singleton(MaintenanceModeService::class, fn () => new MaintenanceModeService());

        // Register BackupManager
        $this->app->singleton(BackupManager::class, function ($app) {
            return new BackupManager(
                $app->make(DatabaseDumperInterface::class),
                $app->make(IntegrityService::class),
                $app->make(MaintenanceModeService::class),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register admin menu
        Hook::addAction('admin.menu.build', function () {
            $menuRegistry = app(\App\Services\MenuRegistry::class);

            $menuRegistry->register('backup', [
                'key' => 'backup',
                'label' => 'Backup',
                'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
                'order' => 85,
                'children' => [
                    [
                        'key' => 'backup.index',
                        'label' => 'Backups',
                        'route' => 'admin.backup.index',
                    ],
                    [
                        'key' => 'backup.settings',
                        'label' => 'Settings',
                        'route' => 'admin.backup.settings',
                    ],
                ],
            ]);
        }, 10);

        // Load routes
        $this->loadRoutes();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register browser title mapping
        Hook::addFilter('admin.browser_title_map', function (array $map) {
            $map['admin.backup.index'] = 'Backups';
            $map['admin.backup.settings'] = 'Backup Settings';
            return $map;
        });

        // Register scheduler
        $this->app->booted(function () {
            if (!$this->app->runningInConsole()) {
                return;
            }

            $schedule = $this->app->make(Schedule::class);
            $settings = \App\Models\Setting::whereIn('key', [
                'backup_schedule_enabled', 
                'backup_schedule_frequency', 
                'backup_schedule_custom_minutes'
            ])->pluck('value', 'key');

            $isEnabled = ($settings['backup_schedule_enabled'] ?? '0') === '1';
            $frequency = $settings['backup_schedule_frequency'] ?? 'daily';
            $customMinutes = (int) ($settings['backup_schedule_custom_minutes'] ?? 30);

            // Scheduled backup
            $event = $schedule->command('backup:run --scheduled')
                ->withoutOverlapping()
                ->runInBackground()
                ->when(fn () => $isEnabled);

            // Apply frequency
            if ($frequency === 'weekly') {
                $event->weekly();
            } elseif ($frequency === 'monthly') {
                $event->monthly();
            } elseif ($frequency === 'hourly') {
                $event->hourly();
            } elseif ($frequency === 'custom') {
                $event->cron("*/" . max(1, $customMinutes) . " * * * *");
            } else {
                $event->daily();
            }

            // Cleanup old backups
            $schedule->command('backup:clean')
                ->daily()
                ->withoutOverlapping();

            // Failsafe: auto-disable stuck maintenance mode
            $schedule->call(function () {
                app(MaintenanceModeService::class)->ensureNotStuck();
            })->everyFiveMinutes();
        });
    }

    /**
     * Load module routes.
     */
    protected function loadRoutes(): void
    {
        $this->app['router']->middleware(['api', 'auth:sanctum'])
            ->prefix('api/v1')
            ->group(__DIR__ . '/routes/api.php');

        // Public OAuth callback route (no auth)
        $this->app['router']->middleware(['web'])
            ->prefix('api/v1')
            ->group(function () {
                $this->app['router']->get(
                    'backup/cloud-accounts/{id}/callback',
                    [Http\Controllers\SettingsController::class, 'callback']
                )->name('backup.cloud.callback');
            });
    }
}
