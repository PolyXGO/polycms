<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Services\SettingsService;
use Modules\Polyx\MTOptimize\Support\MTOptimizeHooks;

class RobotsService
{
    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    public function render(): string
    {
        $rules = $this->buildRules();
        $rules = MTOptimizeHooks::applyFilters('mtoptimize/robots/rules', $rules);

        $lines = [];

        foreach ($rules as $rule) {
            $userAgent = trim((string) ($rule['user_agent'] ?? '*'));
            if ($userAgent === '') {
                $userAgent = '*';
            }

            $lines[] = 'User-agent: ' . $userAgent;

            foreach ($this->normalizeRuleList($rule['allow'] ?? []) as $path) {
                $lines[] = 'Allow: ' . $path;
            }

            foreach ($this->normalizeRuleList($rule['disallow'] ?? []) as $path) {
                $lines[] = 'Disallow: ' . $path;
            }

            if (!empty($rule['crawl_delay'])) {
                $lines[] = 'Crawl-delay: ' . (int) $rule['crawl_delay'];
            }

            $lines[] = '';
        }

        $sitemapUrls = [
            url('/sitemap-index.xml'),
        ];

        foreach ($sitemapUrls as $url) {
            $lines[] = 'Sitemap: ' . $url;
        }

        $content = implode(PHP_EOL, $lines) . PHP_EOL;

        return MTOptimizeHooks::applyFilters('mtoptimize/robots/content', $content, $rules);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildRules(): array
    {
        $isProduction = app()->environment('production');

        // Treat localhost/loopback as non-production regardless of APP_ENV
        $host = request()->getHost();
        if (in_array($host, ['127.0.0.1', 'localhost', '::1'], true) || str_ends_with($host, '.local')) {
            $isProduction = false;
        }

        $allowNonProduction = (bool) $this->settingsService->get('mtoptimize_robots_allow_non_production', false);

        if (!$isProduction && !$allowNonProduction) {
            return [[
                'user_agent' => '*',
                'allow' => [],
                'disallow' => ['/'],
            ]];
        }

        $allowIndexing = (bool) $this->settingsService->get('reading_search_engine_noindex', true);
        if (!$allowIndexing) {
            return [[
                'user_agent' => '*',
                'allow' => [],
                'disallow' => ['/'],
            ]];
        }

        return [[
            'user_agent' => '*',
            'allow' => $this->normalizeRuleList($this->settingsService->get('mtoptimize_robots_extra_allow', ['/'])),
            'disallow' => $this->normalizeRuleList($this->settingsService->get('mtoptimize_robots_extra_disallow', ['/admin', '/api'])),
        ]];
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    protected function normalizeRuleList(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            } else {
                // Treat single string as one-element list
                $value = [$value];
            }
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $value), static fn ($item): bool => $item !== ''));
    }
}
