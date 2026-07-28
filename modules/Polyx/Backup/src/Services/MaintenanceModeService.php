<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MaintenanceModeService
{
    private const CACHE_KEY = 'backup_maintenance_started';

    /**
     * Enable maintenance mode.
     */
    public function enable(array $options = []): void
    {
        $secret = $options['secret'] ?? Str::random(32);
        $retry = $options['retry'] ?? 60;

        Artisan::call('down', [
            '--secret' => $secret,
            '--retry' => $retry,
        ]);

        Cache::put(self::CACHE_KEY, now()->toIso8601String(), 3600);
        Cache::put('backup_maintenance_secret', $secret, 3600);

        Log::info('Backup: Maintenance mode enabled', ['secret' => $secret]);
    }

    /**
     * Disable maintenance mode.
     */
    public function disable(): void
    {
        try {
            Artisan::call('up');
        } catch (\Exception $e) {
            Log::warning('Backup: Failed to disable maintenance mode via artisan', [
                'error' => $e->getMessage(),
            ]);
            // Fallback: remove the maintenance file directly
            $maintenanceFile = storage_path('framework/down');
            if (file_exists($maintenanceFile)) {
                @unlink($maintenanceFile);
            }
        }

        Cache::forget(self::CACHE_KEY);
        Cache::forget('backup_maintenance_secret');

        Log::info('Backup: Maintenance mode disabled');
    }

    /**
     * Check if maintenance mode is currently active (set by this module).
     */
    public function isActive(): bool
    {
        return Cache::has(self::CACHE_KEY);
    }

    /**
     * Get the secret bypass URL segment.
     */
    public function getSecret(): ?string
    {
        return Cache::get('backup_maintenance_secret');
    }

    /**
     * Failsafe: auto-disable if maintenance has been active too long.
     */
    public function ensureNotStuck(): void
    {
        $started = Cache::get(self::CACHE_KEY);
        if (!$started) {
            return;
        }

        $maxMinutes = config('backup.maintenance_max_minutes', 30);
        $startedAt = \Carbon\Carbon::parse($started);

        if ($startedAt->diffInMinutes(now()) > $maxMinutes) {
            Log::warning("Backup: Auto-disabling stuck maintenance mode (active > {$maxMinutes} min)");
            $this->disable();
        }
    }
}
