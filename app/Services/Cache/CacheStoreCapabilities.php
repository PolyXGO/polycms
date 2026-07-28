<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Cache;

class CacheStoreCapabilities
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Get active cache store driver name (redis, file, database).
     */
    public function getActiveDriver(): string
    {
        $mode = (string) $this->settingsService->get('cache_deployment_mode', '');
        if (!empty($mode)) {
            return $mode;
        }

        try {
            return Cache::getDefaultDriver();
        } catch (\Throwable $e) {
            return 'file';
        }
    }

    /**
     * Check if current store supports native tags (e.g. Redis, Memcached).
     */
    public function supportsTags(): bool
    {
        return in_array($this->getActiveDriver(), ['redis', 'memcached'], true);
    }

    /**
     * Get Database Cache Capacity Protection configuration options.
     *
     * @return array<string, mixed>
     */
    public function getDatabaseCacheConfig(): array
    {
        return [
            'max_entry_bytes'          => (int) $this->settingsService->get('db_cache_max_entry_bytes', 512 * 1024),
            'max_total_bytes'          => (int) $this->settingsService->get('db_cache_max_total_bytes', 512 * 1024 * 1024),
            'cleanup_batch_size'       => (int) $this->settingsService->get('db_cache_cleanup_batch_size', 500),
            'cleanup_interval_minutes' => (int) $this->settingsService->get('db_cache_cleanup_interval_minutes', 15),
            'query_timeout_ms'         => (int) $this->settingsService->get('db_cache_query_timeout_ms', 100),
            'fail_open'                => (bool) $this->settingsService->get('db_cache_fail_open', true),
        ];
    }

    /**
     * Check if fail-open behavior is active when cache errors occur.
     */
    public function shouldFailOpen(): bool
    {
        return true; // Read failures ALWAYS fail-open to MySQL canonical source of truth
    }
}
