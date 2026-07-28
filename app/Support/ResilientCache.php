<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ResilientCache — A fault-tolerant wrapper around Laravel's Cache facade.
 *
 * When the file cache store encounters permission errors (e.g. directories
 * owned by root after running artisan commands as root), this wrapper
 * catches the exception and falls back to computing the value without
 * caching, keeping the application functional.
 *
 * Once permissions are restored (e.g. via the admin "Fix Permissions"
 * button or chown commands), caching will resume automatically.
 */
class ResilientCache
{
    /**
     * Tracks whether we've already logged a cache failure in this request
     * to avoid spamming the log file.
     */
    private static bool $hasLoggedFailure = false;

    /**
     * Resilient version of Cache::remember().
     *
     * If the cache read succeeds but the write fails, the closure result
     * is still returned — the site stays up, just without caching.
     *
     * @param  string    $key
     * @param  int|\DateTimeInterface|\DateInterval  $ttl
     * @param  \Closure  $callback
     * @return mixed
     */
    public static function remember(string $key, $ttl, \Closure $callback): mixed
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Throwable $e) {
            self::logOnce($e, $key);

            // Try to auto-repair directory permissions
            self::attemptAutoRepair($e);

            // Fall back: just compute the value without caching
            return $callback();
        }
    }

    /**
     * Resilient version of Cache::put().
     */
    public static function put(string $key, mixed $value, $ttl = null): bool
    {
        try {
            return Cache::put($key, $value, $ttl);
        } catch (\Throwable $e) {
            self::logOnce($e, $key);
            self::attemptAutoRepair($e);
            return false;
        }
    }

    /**
     * Resilient version of Cache::forever().
     */
    public static function forever(string $key, mixed $value): bool
    {
        try {
            return Cache::forever($key, $value);
        } catch (\Throwable $e) {
            self::logOnce($e, $key);
            self::attemptAutoRepair($e);
            return false;
        }
    }

    /**
     * Passthrough for Cache::forget() — failures here are non-critical.
     */
    public static function forget(string $key): bool
    {
        try {
            return Cache::forget($key);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Passthrough for Cache::get() — read-only, less likely to fail.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Passthrough for Cache::has().
     */
    public static function has(string $key): bool
    {
        try {
            return Cache::has($key);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Attempt to auto-repair the cache directory when a permission error
     * is detected. This only works when the web server user owns the
     * parent directory or when running as root.
     */
    private static function attemptAutoRepair(\Throwable $e): void
    {
        $message = $e->getMessage();

        // Only attempt repair for permission-related errors on the cache data directory
        if (!str_contains($message, 'Permission denied') && !str_contains($message, 'No such file or directory')) {
            return;
        }

        // Extract the failing path from the error message
        if (preg_match('/file_put_contents\(([^)]+)\)/', $message, $matches)) {
            $failedPath = $matches[1];
            $dir = dirname($failedPath);

            // Try to create missing directories
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            // Try to fix permissions on the directory
            if (is_dir($dir) && !is_writable($dir)) {
                @chmod($dir, 0775);
            }
        }

        // Also try the top-level cache data directory
        $cacheDataDir = storage_path('framework/cache/data');
        if (is_dir($cacheDataDir) && !is_writable($cacheDataDir)) {
            @chmod($cacheDataDir, 0775);
        }
    }

    /**
     * Log the cache failure once per request to avoid log spam.
     */
    private static function logOnce(\Throwable $e, string $key): void
    {
        if (self::$hasLoggedFailure) {
            return;
        }

        self::$hasLoggedFailure = true;

        Log::warning(
            "[ResilientCache] Cache write failed — operating without cache. "
            . "Fix: chown -R <webuser>:<webuser> storage bootstrap/cache. "
            . "Key: {$key}, Error: {$e->getMessage()}"
        );
    }
}
