<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Services\SettingsService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Polyx\MTOptimize\Support\MTOptimizeHooks;
use Modules\Polyx\MTOptimize\Support\SitemapProviderRegistry;
use Throwable;

class SitemapService
{
    protected string $cachePrefix = 'mtoptimize:sitemap:';
    protected string $cacheKeysKey = 'mtoptimize:sitemap:keys';

    public function __construct(
        protected SettingsService $settingsService,
        protected SitemapProviderRegistry $providerRegistry,
    ) {}

    public function renderIndex(): Response
    {
        $cacheKey = $this->cachePrefix . 'index';
        $cached = Cache::get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return $this->xmlResponse($cached);
        }

        $indexes = [];
        $chunk = max(10, (int) $this->settingsService->get('mtoptimize_sitemap_chunk_size', 500));

        foreach ($this->providerRegistry->all() as $type => $provider) {
            // Query page 1 to discover total_pages for this provider
            $totalPages = 1;
            if (isset($provider['callback']) && is_callable($provider['callback'])) {
                try {
                    $probe = (array) call_user_func($provider['callback'], 1, $chunk, [
                        'type' => $type,
                        'page' => 1,
                        'chunk' => $chunk,
                    ]);
                    $totalPages = max(1, (int) ($probe['total_pages'] ?? 1));
                } catch (Throwable $e) {
                    Log::warning('[MTOptimize] Sitemap index probe failed', [
                        'type' => $type,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            // Generate one <sitemap> entry per page
            for ($page = 1; $page <= $totalPages; $page++) {
                if ($totalPages === 1) {
                    // Single page: use clean URL without page suffix
                    $loc = url('/sitemap-' . $type . '.xml');
                } else {
                    // Multi-page: use paginated URL
                    $loc = url('/sitemap-' . $type . '-page-' . $page . '.xml');
                }

                $indexes[] = [
                    'loc' => $loc,
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }

        $indexes = MTOptimizeHooks::applyFilters('mtoptimize/sitemap/indexes', $indexes);

        $xml = $this->buildSitemapIndexXml($indexes);

        $this->remember($cacheKey, $xml);

        return $this->xmlResponse($xml);
    }

    public function render(string $type, int $page = 1): Response
    {
        if ($type === '' || !$this->providerRegistry->has($type)) {
            return response('<error>Unknown sitemap type</error>', 404, ['Content-Type' => 'application/xml; charset=UTF-8']);
        }

        $page = max(1, $page);
        $cacheKey = $this->cachePrefix . $type . ':' . $page;

        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $this->xmlResponse($cached);
        }

        $provider = $this->providerRegistry->get($type);
        $chunk = max(10, (int) $this->settingsService->get('mtoptimize_sitemap_chunk_size', 500));
        $context = [
            'type' => $type,
            'page' => $page,
            'chunk' => $chunk,
        ];

        $result = [];

        if ($provider !== null && isset($provider['callback']) && is_callable($provider['callback'])) {
            try {
                $result = (array) call_user_func($provider['callback'], $page, $chunk, $context);
            } catch (Throwable $exception) {
                Log::warning('[MTOptimize] Sitemap provider failed', [
                    'type' => $type,
                    'message' => $exception->getMessage(),
                    'exception' => $exception::class,
                ]);
                $result = [];
            }
        }

        $result = MTOptimizeHooks::applyFilters('mtoptimize/sitemap/query', $result, $context);

        $items = $result['items'] ?? $result;
        if (!is_array($items)) {
            $items = [];
        }

        $items = MTOptimizeHooks::applyFilters('mtoptimize/sitemap/items', $items, $context);

        $normalizedItems = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $item = MTOptimizeHooks::applyFilters('mtoptimize/sitemap/item', $item, $context);
            $exclude = (bool) MTOptimizeHooks::applyFilters('mtoptimize/sitemap/exclude', false, $item, $context);
            if ($exclude) {
                continue;
            }

            $loc = trim((string) ($item['loc'] ?? ''));
            if ($loc === '') {
                continue;
            }

            $normalizedItems[] = [
                'loc' => $loc,
                'lastmod' => trim((string) ($item['lastmod'] ?? '')),
                'changefreq' => trim((string) ($item['changefreq'] ?? '')),
                'priority' => trim((string) ($item['priority'] ?? '')),
                'images' => is_array($item['images'] ?? null) ? $item['images'] : [],
            ];
        }

        $xml = $this->buildUrlSetXml($normalizedItems);

        $this->remember($cacheKey, $xml);

        return $this->xmlResponse($xml);
    }

    public function invalidate(): void
    {
        $keys = Cache::get($this->cacheKeysKey, []);

        if (is_array($keys)) {
            foreach ($keys as $key) {
                Cache::forget((string) $key);
            }
        }

        Cache::forget($this->cacheKeysKey);
    }

    /**
     * @param array<int, array<string, string>> $indexes
     */
    protected function buildSitemapIndexXml(array $indexes): string
    {
        $xml = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $xml[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($indexes as $item) {
            $loc = htmlspecialchars((string) ($item['loc'] ?? ''), ENT_QUOTES, 'UTF-8');
            if ($loc === '') {
                continue;
            }

            $xml[] = '  <sitemap>';
            $xml[] = '    <loc>' . $loc . '</loc>';

            $lastmod = trim((string) ($item['lastmod'] ?? ''));
            if ($lastmod !== '') {
                $xml[] = '    <lastmod>' . htmlspecialchars($lastmod, ENT_QUOTES, 'UTF-8') . '</lastmod>';
            }

            $xml[] = '  </sitemap>';
        }

        $xml[] = '</sitemapindex>';

        return implode(PHP_EOL, $xml);
    }

    /**
     * @param array<int, array<string, string>> $items
     */
    protected function buildUrlSetXml(array $items): string
    {
        $hasImages = false;
        foreach ($items as $item) {
            if (!empty($item['images'])) {
                $hasImages = true;
                break;
            }
        }

        $xml = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $ns = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        if ($hasImages) {
            $ns .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        }
        $ns .= '>';
        $xml[] = $ns;

        foreach ($items as $item) {
            $loc = htmlspecialchars((string) ($item['loc'] ?? ''), ENT_QUOTES, 'UTF-8');
            if ($loc === '') {
                continue;
            }

            $xml[] = '  <url>';
            $xml[] = '    <loc>' . $loc . '</loc>';

            if (!empty($item['lastmod'])) {
                $xml[] = '    <lastmod>' . htmlspecialchars((string) $item['lastmod'], ENT_QUOTES, 'UTF-8') . '</lastmod>';
            }

            if (!empty($item['changefreq'])) {
                $xml[] = '    <changefreq>' . htmlspecialchars((string) $item['changefreq'], ENT_QUOTES, 'UTF-8') . '</changefreq>';
            }

            if (!empty($item['priority'])) {
                $xml[] = '    <priority>' . htmlspecialchars((string) $item['priority'], ENT_QUOTES, 'UTF-8') . '</priority>';
            }

            // Image sitemap extension
            if (!empty($item['images']) && is_array($item['images'])) {
                foreach ($item['images'] as $image) {
                    $imgLoc = htmlspecialchars(trim((string) ($image['loc'] ?? '')), ENT_QUOTES, 'UTF-8');
                    if ($imgLoc === '') {
                        continue;
                    }
                    $xml[] = '    <image:image>';
                    $xml[] = '      <image:loc>' . $imgLoc . '</image:loc>';
                    if (!empty($image['title'])) {
                        $xml[] = '      <image:title>' . htmlspecialchars((string) $image['title'], ENT_QUOTES, 'UTF-8') . '</image:title>';
                    }
                    if (!empty($image['caption'])) {
                        $xml[] = '      <image:caption>' . htmlspecialchars((string) $image['caption'], ENT_QUOTES, 'UTF-8') . '</image:caption>';
                    }
                    $xml[] = '    </image:image>';
                }
            }

            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return implode(PHP_EOL, $xml);
    }

    protected function xmlResponse(string $xml): Response
    {
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    protected function remember(string $key, string $value): void
    {
        Cache::put($key, $value, now()->addSeconds($this->cacheTtl()));

        $keys = Cache::get($this->cacheKeysKey, []);
        if (!is_array($keys)) {
            $keys = [];
        }

        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::put($this->cacheKeysKey, $keys, now()->addSeconds($this->cacheTtl()));
        }
    }

    protected function cacheTtl(): int
    {
        return max(300, (int) $this->settingsService->get('mtoptimize_sitemap_cache_ttl_seconds', 3600));
    }
}
