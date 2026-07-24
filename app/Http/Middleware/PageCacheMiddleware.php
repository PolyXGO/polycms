<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageCacheMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Only process GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Exclude admin, api, livewire, cart, checkout, or AJAX requests
        $path = $request->path();
        if ($request->ajax() || 
            $request->wantsJson() || 
            str_starts_with($path, 'admin') || 
            str_starts_with($path, 'api') || 
            str_contains($path, 'cart') || 
            str_contains($path, 'checkout') || 
            str_contains($path, 'logout')) {
            return $next($request);
        }

        $settingsService = app(\App\Services\SettingsService::class);
        $systemCacheEnabled = $settingsService->get('polycms_cache_enabled', 'yes') === 'yes';
        $htmlCacheEnabled = $settingsService->get('response_html_cache_enabled', 'yes') === 'yes';
        $browserCacheEnabled = $settingsService->get('browser_http_cache_enabled', 'yes') === 'yes';
        $ttlSeconds = (int) $settingsService->get('response_cache_ttl', 60);

        if (!$systemCacheEnabled) {
            return $next($request);
        }

        // Handle Server Response HTML Cache (Compatible with File, Database, Redis stores)
        if ($htmlCacheEnabled) {
            $userKey = auth()->check() ? 'user_' . auth()->id() : 'guest';
            $locale = app()->getLocale();
            $cacheKey = 'polycms_page_cache_' . md5($request->fullUrl() . '_' . $locale . '_' . $userKey);

            $cachedContent = Cache::get($cacheKey);
            if ($cachedContent !== null && is_string($cachedContent)) {
                $response = response($cachedContent);
                $response->headers->set('X-PolyCMS-Cache', 'HIT');
                if ($browserCacheEnabled) {
                    $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
                }
                return $response;
            }
        }

        $response = $next($request);

        // Cache successful HTML responses
        if ($htmlCacheEnabled && $response->isSuccessful() && str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $content = $response->getContent();
            if ($content !== false && !empty($content)) {
                $userKey = auth()->check() ? 'user_' . auth()->id() : 'guest';
                $locale = app()->getLocale();
                $cacheKey = 'polycms_page_cache_' . md5($request->fullUrl() . '_' . $locale . '_' . $userKey);

                Cache::put($cacheKey, $content, $ttlSeconds);
                $response->headers->set('X-PolyCMS-Cache', 'MISS');
            }
        }

        if ($browserCacheEnabled && $response->isSuccessful() && str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
        }

        return $response;
    }
}
