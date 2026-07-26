<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Services\SettingsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheEligibilityPolicy
{
    protected SettingsService $settingsService;

    /**
     * Default allowlist routes when empty in configuration
     *
     * @var array<int, string>
     */
    protected array $defaultAllowlistRoutes = [
        'home',
        'posts.index',
        'posts.archive',
        'posts.show',
        'page.show',
        'pages.show',
        'categories.show',
        'category.show',
        'products.index',
        'products.archive',
        'products.show',
        'product-categories.show',
        'projects.index',
        'projects.show',
    ];

    /**
     * Paths that are always excluded from page cache
     *
     * @var array<int, string>
     */
    protected array $blacklistedPaths = [
        'admin',
        'api',
        'cart',
        'checkout',
        'account',
        'logout',
        'login',
        'register',
        'password',
        'livewire',
    ];

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Determine whether an incoming request is eligible for page caching.
     */
    public function allowsRequest(Request $request): bool
    {
        // 1. System cache master toggles
        $systemCacheEnabled = $this->settingsService->get('polycms_cache_enabled', 'yes') === 'yes';
        $htmlCacheEnabled = $this->settingsService->get('response_html_cache_enabled', 'yes') === 'yes';

        if (!$systemCacheEnabled || !$htmlCacheEnabled) {
            return false;
        }

        // 2. Only GET or HEAD requests
        if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            return false;
        }

        // 3. Bypass Inertia.js protocol requests
        if ($request->headers->get('X-Inertia') === 'true') {
            return false;
        }

        // 4. Bypass AJAX & JSON requests
        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        // 5. Bypass Authenticated or Authorized requests
        if (auth()->check() || $request->headers->has('Authorization')) {
            return false;
        }

        // 6. Bypass Client Reload requests (only if explicitly no-store)
        $cacheControl = (string) $request->headers->get('Cache-Control', '');
        if (str_contains($cacheControl, 'no-store')) {
            return false;
        }

        // 7. Check path exclusions
        $blacklistedPaths = $this->getBlacklistedPaths();
        $path = ltrim($request->path(), '/');
        foreach ($blacklistedPaths as $blacklisted) {
            if ($path === $blacklisted || str_starts_with($path, $blacklisted . '/')) {
                return false;
            }
        }

        // 8. Check for user-specific session/cart cookies
        if ($request->cookies->has('cart_token') || $request->cookies->has('bypass_cache')) {
            return false;
        }

        // 9. Route allowlist check (if route is named or route-based policy)
        $routeName = $request->route() ? $request->route()->getName() : null;
        if ($routeName) {
            $allowlist = $this->getCacheableRoutes();
            if (!empty($allowlist) && !in_array($routeName, $allowlist, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether an outgoing response is eligible for page caching.
     */
    public function allowsResponse(Request $request, Response $response): bool
    {
        // 1. Status code check (Only 200 or 301 allowed)
        $status = $response->getStatusCode();
        if ($status !== 200 && $status !== 301) {
            return false;
        }

        // 2. Check Cache-Control directives (only reject if explicitly no-store)
        $cacheControl = (string) $response->headers->get('Cache-Control', '');
        if (str_contains($cacheControl, 'no-store')) {
            return false;
        }

        // 3. Check Set-Cookie headers: Allow standard framework and guest tracking cookies, reject custom session/user cookies
        if ($response->headers->has('Set-Cookie')) {
            $sessionCookieName = (string) config('session.cookie', 'session');
            $cookieHeaders = $response->headers->all('set-cookie');
            foreach ($cookieHeaders as $cookieStr) {
                $cookieStr = (string) $cookieStr;

                // Reject if setting sensitive user authentication cookies
                if (str_contains($cookieStr, 'remember_web_')
                    || str_contains($cookieStr, 'remember_admin_')
                    || str_contains($cookieStr, 'auth_token')) {
                    return false;
                }

                $isAllowedCookie = str_contains($cookieStr, 'XSRF-TOKEN')
                    || str_contains($cookieStr, 'xsrf-token')
                    || str_contains($cookieStr, $sessionCookieName)
                    || (str_contains($cookieStr, 'session') && !str_contains($cookieStr, 'session_id'))
                    || str_contains($cookieStr, 'viewed_')
                    || str_contains($cookieStr, 'consent')
                    || str_contains($cookieStr, 'adminer')
                    || str_contains($cookieStr, 'staff_lang')
                    || str_contains($cookieStr, 'locale');

                if (!$isAllowedCookie) {
                    return false;
                }
            }
        }

        // 4. Content-Type check (HTML only)
        $contentType = (string) $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return false;
        }

        // 5. Check Body Size Limit
        $maxSetting = $this->settingsService->get('cache_page_max_entry_bytes');
        $maxBytes = ($maxSetting !== null && $maxSetting !== '') ? (int) $maxSetting : (2 * 1024 * 1024);
        $content = $response->getContent();
        if ($content === false || empty($content) || strlen($content) > $maxBytes) {
            return false;
        }

        return true;
    }

    /**
     * Get the allowlist of cacheable route names.
     *
     * @return array<int, string>
     */
    public function getCacheableRoutes(): array
    {
        $customAllowlist = $this->settingsService->get('cache_allowlist_routes', null);
        if (is_string($customAllowlist) && trim($customAllowlist) !== '') {
            return array_filter(array_map('trim', explode(',', $customAllowlist)));
        }
        if (is_array($customAllowlist)) {
            return $customAllowlist;
        }
        return $this->defaultAllowlistRoutes;
    }

    /**
     * Get blacklisted path prefixes.
     *
     * @return array<int, string>
     */
    public function getBlacklistedPaths(): array
    {
        $customBlacklist = $this->settingsService->get('cache_blacklisted_paths', null);
        if (is_string($customBlacklist) && trim($customBlacklist) !== '') {
            return array_filter(array_map('trim', explode(',', $customBlacklist)));
        }
        if (is_array($customBlacklist)) {
            return $customBlacklist;
        }
        return $this->blacklistedPaths;
    }
}
