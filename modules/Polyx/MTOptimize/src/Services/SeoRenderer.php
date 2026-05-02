<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Contracts\SeoRenderContract;
use App\Facades\Hook;
use App\Services\SettingsService;
use Illuminate\Http\Response;

class SeoRenderer implements SeoRenderContract
{
    public function __construct(
        protected MTOptimizeEngine $engine,
        protected SitemapService $sitemapService,
        protected RobotsService $robotsService,
        protected SettingsService $settingsService,
    ) {}

    public function renderHead(): string
    {
        $result = $this->engine->resolve(request());
        $payload = $result['payload'];

        $lines = [];

        $description = trim((string) ($payload['description'] ?? ''));
        if ($description !== '') {
            $lines[] = '<meta name="description" content="' . e($description) . '">';
        }

        $keywords = $payload['keywords'] ?? [];
        if (is_array($keywords) && !empty($keywords)) {
            $lines[] = '<meta name="keywords" content="' . e(implode(', ', $keywords)) . '">';
        }

        $robots = trim((string) ($payload['robots'] ?? ''));
        if ($robots !== '') {
            $lines[] = '<meta name="robots" content="' . e($robots) . '">';
        }

        $author = trim((string) ($payload['author'] ?? ''));
        if ($author !== '') {
            $lines[] = '<meta name="author" content="' . e($author) . '">';
        }

        $publisher = trim((string) ($payload['publisher'] ?? ''));
        if ($publisher !== '') {
            $lines[] = '<meta name="publisher" content="' . e($publisher) . '">';
        }

        $language = trim((string) ($payload['language'] ?? ''));
        if ($language !== '') {
            $lines[] = '<meta http-equiv="content-language" content="' . e($language) . '">';
        }

        foreach (($payload['links'] ?? []) as $link) {
            if (!is_array($link)) {
                continue;
            }

            $rel = trim((string) ($link['rel'] ?? ''));
            $href = trim((string) ($link['href'] ?? ''));
            $hreflang = trim((string) ($link['hreflang'] ?? ''));

            if ($rel === '' || $href === '') {
                continue;
            }

            $attributes = [
                'rel="' . e($rel) . '"',
                'href="' . e($href) . '"',
            ];

            if ($hreflang !== '') {
                $attributes[] = 'hreflang="' . e($hreflang) . '"';
            }

            $lines[] = '<link ' . implode(' ', $attributes) . '>';
        }

        foreach (($payload['og'] ?? []) as $key => $value) {
            $metaKey = trim((string) $key);
            $metaValue = trim((string) $value);

            if ($metaKey === '' || $metaValue === '') {
                continue;
            }

            $lines[] = '<meta property="og:' . e($metaKey) . '" content="' . e($metaValue) . '">';
        }

        foreach (($payload['twitter'] ?? []) as $key => $value) {
            $metaKey = trim((string) $key);
            $metaValue = trim((string) $value);

            if ($metaKey === '' || $metaValue === '') {
                continue;
            }

            $lines[] = '<meta name="twitter:' . e($metaKey) . '" content="' . e($metaValue) . '">';
        }

        $schema = $payload['schema'] ?? [];
        if (is_array($schema) && !empty($schema)) {
            $graph = [
                '@context' => 'https://schema.org',
                '@graph' => $schema,
            ];

            $json = json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json !== false) {
                $lines[] = '<script type="application/ld+json">' . $json . '</script>';
            }
        }

        $siteIconUrl = Hook::applyFilters('seo.site_favicon', $this->settingsService->get('site_icon'));
        if (is_string($siteIconUrl) && trim($siteIconUrl) !== '') {
            $iconUrl = trim($siteIconUrl);
            $lines[] = '<link rel="icon" type="image/png" href="' . e($iconUrl) . '">';
            $lines[] = '<link rel="apple-touch-icon" href="' . e($iconUrl) . '">';
        }

        return implode(PHP_EOL, array_unique($lines)) . PHP_EOL;
    }

    public function renderRobotsTxt(): Response
    {
        return response($this->robotsService->render(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function renderSitemapIndex(): Response
    {
        return $this->sitemapService->renderIndex();
    }

    public function renderSitemap(?string $type = null, int $page = 1): Response
    {
        if ($type === null || trim($type) === '') {
            return $this->renderSitemapIndex();
        }

        return $this->sitemapService->render(trim($type), $page);
    }
}
