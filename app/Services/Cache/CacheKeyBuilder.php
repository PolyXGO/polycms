<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Services\SettingsService;
use Illuminate\Http\Request;

class CacheKeyBuilder
{
    protected UrlCanonicalizer $canonicalizer;
    protected CacheGenerationStore $generationStore;
    protected SettingsService $settingsService;

    public function __construct(
        UrlCanonicalizer $canonicalizer,
        CacheGenerationStore $generationStore,
        SettingsService $settingsService
    ) {
        $this->canonicalizer = $canonicalizer;
        $this->generationStore = $generationStore;
        $this->settingsService = $settingsService;
    }

    /**
     * Build a structured cache key for a request.
     */
    public function buildKey(Request $request): string
    {
        $siteId = (string) $this->settingsService->get('site_id', 'site_1');
        $tenantId = (string) $this->settingsService->get('tenant_id', 'tenant_1');

        // Resolve host safely (trust proxy or standard host)
        $canonicalHost = $request->getHost();

        $route = $request->route();
        $routeName = $route ? ($route->getName() ?? 'unnamed') : 'path_' . md5($request->path());

        $locale = app()->getLocale();
        $currency = 'VND';
        try {
            if ($request->hasSession() && $request->session()->has('currency')) {
                $currency = (string) $request->session()->get('currency');
            } else {
                $currency = (string) $this->settingsService->get('default_currency', 'VND');
            }
        } catch (\Throwable $e) {
            $currency = (string) $this->settingsService->get('default_currency', 'VND');
        }

        $themeRevision = (string) $this->settingsService->get('theme_revision', '1');

        // Resolve generation token for the entity/route
        $generation = $this->generationStore->getForRequest($request);

        $canonicalUri = $this->canonicalizer->canonicalize($request);
        $uriHash = md5($canonicalUri);

        return sprintf(
            'pc:v3:%s:%s:%s:%s:%s:%s:%s:gen_%d:%s',
            $siteId,
            $tenantId,
            $canonicalHost,
            $routeName,
            $locale,
            $currency,
            $themeRevision,
            $generation,
            $uriHash
        );
    }
}
