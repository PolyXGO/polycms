<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Services\SettingsService;
use Modules\Polyx\MTOptimize\Support\MTOptimizeHooks;
use Modules\Polyx\MTOptimize\Support\UrlNormalizer;

class LinkResolver
{
    public function __construct(
        protected SettingsService $settingsService,
        protected UrlNormalizer $urlNormalizer,
    ) {}

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     * @param array<string, mixed> $defaults
     * @return array<int, array{rel: string, href: string, hreflang?: string}>
     */
    public function resolve(array $context, array $meta, array $defaults): array
    {
        $links = [];

        $canonical = trim((string) ($meta['canonical'] ?? ''));
        if ($canonical !== '') {
            $canonical = (string) MTOptimizeHooks::applyFilters('mtoptimize/link/canonical', $canonical, $context, $meta, $defaults);
            $links[] = [
                'rel' => 'canonical',
                'href' => $canonical,
            ];
        }

        $alternates = $context['alternates'] ?? [];
        $alternates = MTOptimizeHooks::applyFilters('mtoptimize/link/alternate', $alternates, $context, $meta, $defaults);
        $alternates = MTOptimizeHooks::applyFilters('mtoptimize/link/hreflang', $alternates, $context, $meta, $defaults);

        if (is_array($alternates)) {
            foreach ($alternates as $alternate) {
                if (!is_array($alternate)) {
                    continue;
                }

                $locale = trim((string) ($alternate['locale'] ?? ''));
                $url = trim((string) ($alternate['url'] ?? ''));

                if ($locale === '' || $url === '') {
                    continue;
                }

                $links[] = [
                    'rel' => 'alternate',
                    'href' => $this->urlNormalizer->normalize($url, $context) ?? $url,
                    'hreflang' => $locale,
                ];
            }
        }

        $paginationLinks = $this->resolvePaginationLinks($context, $meta, $defaults);
        foreach ($paginationLinks as $item) {
            $links[] = $item;
        }

        return $links;
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     * @param array<string, mixed> $defaults
     * @return array<int, array{rel: string, href: string}>
     */
    protected function resolvePaginationLinks(array $context, array $meta, array $defaults): array
    {
        $pagination = $context['pagination'] ?? [];
        $page = max(1, (int) ($pagination['page'] ?? 1));
        $totalPages = $pagination['totalPages'] ?? null;

        $suppressOnNoindex = (bool) ($defaults['robots']['suppress_pagination_links_on_noindex'] ?? true);
        if ($suppressOnNoindex && str_contains(strtolower((string) ($meta['robots'] ?? '')), 'noindex')) {
            return [];
        }

        $links = [];

        if ($page > 1) {
            $prev = $this->buildPageUrl((string) ($meta['canonical'] ?? $context['fullUrl'] ?? url('/')), $page - 1);
            $prev = (string) MTOptimizeHooks::applyFilters('mtoptimize/link/prev', $prev, $context, $meta, $defaults);
            if ($prev !== '') {
                $links[] = ['rel' => 'prev', 'href' => $prev];
            }
        }

        if ($totalPages !== null && $page < (int) $totalPages) {
            $next = $this->buildPageUrl((string) ($meta['canonical'] ?? $context['fullUrl'] ?? url('/')), $page + 1);
            $next = (string) MTOptimizeHooks::applyFilters('mtoptimize/link/next', $next, $context, $meta, $defaults);
            if ($next !== '') {
                $links[] = ['rel' => 'next', 'href' => $next];
            }
        }

        return $links;
    }

    protected function buildPageUrl(string $baseUrl, int $page): string
    {
        $parts = parse_url($baseUrl);

        if ($parts === false) {
            return $baseUrl;
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        if ($page <= 1) {
            unset($query['page']);
        } else {
            $query['page'] = $page;
        }

        $url = ($parts['scheme'] ?? request()->getScheme()) . '://' . ($parts['host'] ?? request()->getHost());

        if (isset($parts['port']) && !in_array((int) $parts['port'], [80, 443], true)) {
            $url .= ':' . $parts['port'];
        }

        $url .= $parts['path'] ?? '/';

        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        return $this->urlNormalizer->normalize($url) ?? $url;
    }
}
