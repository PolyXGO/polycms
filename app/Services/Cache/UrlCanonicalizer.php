<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Services\SettingsService;
use Illuminate\Http\Request;

class UrlCanonicalizer
{
    protected SettingsService $settingsService;

    /**
     * Known tracking query parameters to ignore by default
     *
     * @var array<int, string>
     */
    protected array $defaultKnownTrackingParams = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'fbclid',
        'gclid',
        'msclkid',
        'ttclid',
        'ref',
        '_ga',
        '_gl',
        'mc_cid',
        'mc_eid',
    ];

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Build canonical URI string for cache key generation.
     */
    public function canonicalize(Request $request): string
    {
        $url = $request->fullUrl();
        $parsed = parse_url($url);

        if ($parsed === false) {
            return $url;
        }

        // 1. RFC 3986: Lowercase scheme and host ONLY
        $scheme = strtolower($parsed['scheme'] ?? 'https');
        $host   = strtolower($parsed['host'] ?? '');

        // 2. Path: DO NOT lowercase path (case-sensitive)
        $path = $parsed['path'] ?? '/';
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        // 3. Normalize Query Parameters
        $queryString = '';
        if (isset($parsed['query']) && $parsed['query'] !== '') {
            $normalizedQuery = $this->normalizeQuery($parsed['query'], $request);
            if ($normalizedQuery !== '') {
                $queryString = '?' . $normalizedQuery;
            }
        }

        $port = isset($parsed['port']) && $parsed['port'] !== 80 && $parsed['port'] !== 443 ? ':' . $parsed['port'] : '';

        return $scheme . '://' . $host . $port . $path . $queryString;
    }

    /**
     * Normalize query parameters string according to route-scoped policies.
     */
    public function normalizeQuery(string $queryString, Request $request): string
    {
        parse_str($queryString, $queryParams);

        if (empty($queryParams)) {
            return '';
        }

        $strategy = $this->settingsService->get('cache_query_param_strategy', 'ignore_known_tracking');
        $ignoredList = $this->getIgnoredTrackingParams();

        $filteredParams = [];

        foreach ($queryParams as $key => $value) {
            if ($this->shouldIgnoreParam((string) $key, $strategy, $ignoredList)) {
                continue;
            }
            $filteredParams[$key] = $value;
        }

        if (empty($filteredParams)) {
            return '';
        }

        // Alphabetically sort keys to guarantee ?a=1&b=2 and ?b=2&a=1 generate identical keys
        ksort($filteredParams);

        // Build query string using RFC 3986 encoding
        return http_build_query($filteredParams, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Check if a parameter key should be ignored based on tracking strategy and patterns.
     */
    protected function shouldIgnoreParam(string $paramKey, string $strategy, array $ignoredList): bool
    {
        foreach ($ignoredList as $ignoredPattern) {
            if ($ignoredPattern === $paramKey) {
                return true;
            }
            // Support wildcard matching e.g. utm_*, fb_*
            if (str_contains($ignoredPattern, '*')) {
                $pattern = '/^' . str_replace('\*', '.*', preg_quote($ignoredPattern, '/')) . '$/i';
                if (preg_match($pattern, $paramKey) === 1) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get array of tracking parameters to ignore.
     *
     * @return array<int, string>
     */
    public function getIgnoredTrackingParams(): array
    {
        $customIgnored = $this->settingsService->get('cache_ignored_query_params', null);
        if (is_string($customIgnored) && trim($customIgnored) !== '') {
            return array_filter(array_map('trim', explode(',', $customIgnored)));
        }
        if (is_array($customIgnored)) {
            return $customIgnored;
        }
        return $this->defaultKnownTrackingParams;
    }
}
