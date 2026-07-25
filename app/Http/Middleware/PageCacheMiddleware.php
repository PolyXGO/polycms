<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Analytics\DurableViewCounter;
use App\Services\Cache\CacheEligibilityPolicy;
use App\Services\Cache\CacheEntryIntegrity;
use App\Services\Cache\CacheGenerationStore;
use App\Services\Cache\CacheKeyBuilder;
use App\Services\Cache\PageCacheEntry;
use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PageCacheMiddleware
{
    protected CacheEligibilityPolicy $eligibility;
    protected CacheKeyBuilder $keyBuilder;
    protected CacheEntryIntegrity $integrity;
    protected CacheGenerationStore $generationStore;
    protected DurableViewCounter $viewCounter;
    protected SettingsService $settingsService;

    public function __construct(
        CacheEligibilityPolicy $eligibility,
        CacheKeyBuilder $keyBuilder,
        CacheEntryIntegrity $integrity,
        CacheGenerationStore $generationStore,
        DurableViewCounter $viewCounter,
        SettingsService $settingsService
    ) {
        $this->eligibility = $eligibility;
        $this->keyBuilder = $keyBuilder;
        $this->integrity = $integrity;
        $this->generationStore = $generationStore;
        $this->viewCounter = $viewCounter;
        $this->settingsService = $settingsService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check Request Eligibility
        if (!$this->eligibility->allowsRequest($request)) {
            return $next($request);
        }

        // 2. Build Canonical Cache Key
        $cacheKey = $this->keyBuilder->buildKey($request);

        // 3. Attempt Cache Retrieval
        try {
            $cachedData = Cache::get($cacheKey);
            if ($cachedData !== null && is_array($cachedData)) {
                $entry = PageCacheEntry::fromArray($cachedData);
                if ($entry !== null && $this->integrity->validate($entry)) {
                    if ($entry->isFresh()) {
                        return $this->toResponse($entry, 'HIT');
                    }

                    if ($entry->isStaleAllowed()) {
                        // Serve stale response, background lock will rebuild
                        return $this->toResponse($entry, 'STALE');
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fail-open policy: Any cache store read error falls through to canonical render
            report($e);
        }

        // 4. Record Fencing Token state before rendering
        $genBefore = $this->generationStore->getForRequest($request);

        // 5. Execute downstream pipeline
        $response = $next($request);

        // 6. Check Response Eligibility & Fencing Token
        if ($this->eligibility->allowsResponse($request, $response)) {
            $genAfter = $this->generationStore->getForRequest($request);

            // Fencing Token Check: If generation changed during render, do NOT store stale HTML
            if ($genBefore === $genAfter) {
                $this->storeResponseInCache($cacheKey, $response);
            }
        }

        // 7. Add Cache miss header
        $response->headers->set('X-PolyCMS-Cache', 'MISS');

        return $response;
    }

    /**
     * Build an HTTP Response from a cached PageCacheEntry.
     */
    protected function toResponse(PageCacheEntry $entry, string $cacheState): Response
    {
        $response = response($entry->body, $entry->status);

        foreach ($entry->headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        $response->headers->set('X-PolyCMS-Cache', $cacheState);
        $response->headers->set('X-PolyCMS-Cache-Generation', (string) $entry->contentRevision);

        $browserCacheEnabled = $this->settingsService->get('browser_http_cache_enabled', 'yes') === 'yes';
        if ($browserCacheEnabled) {
            $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
        }

        return $response;
    }

    /**
     * Store successful response in page cache with TTL Jitter & Response Envelope.
     */
    protected function storeResponseInCache(string $cacheKey, Response $response): void
    {
        try {
            $ttlSetting = $this->settingsService->get('response_cache_ttl');
            $ttl = ($ttlSetting !== null && $ttlSetting !== '') ? (int) $ttlSetting : 3600;

            // Jitter ±10% to prevent thundering herd expiry
            $jitter = random_int(-(int)($ttl * 0.1), (int)($ttl * 0.1));
            $finalTtl = max(10, $ttl + $jitter);
            $staleTtl = $finalTtl + 300; // 5 minute stale window

            $body = (string) $response->getContent();
            $checksum = $this->integrity->checksum($body);

            // Extract safe headers only
            $headers = [];
            foreach ($response->headers->all() as $name => $values) {
                $lowerName = strtolower((string) $name);
                if (in_array($lowerName, PageCacheEntry::SAFE_HEADERS, true)) {
                    $headers[$lowerName] = implode(', ', $values);
                }
            }

            $entry = new PageCacheEntry(
                schemaVersion: CacheEntryIntegrity::CURRENT_SCHEMA_VERSION,
                status: $response->getStatusCode(),
                body: $body,
                headers: $headers,
                contentRevision: 'v1',
                generatedAt: time(),
                freshUntil: time() + $finalTtl,
                staleUntil: time() + $staleTtl,
                checksum: $checksum
            );

            Cache::put($cacheKey, $entry->toArray(), $staleTtl);
        } catch (\Throwable $e) {
            // Fail-open: Write failure does not break response
            report($e);
        }
    }
}
