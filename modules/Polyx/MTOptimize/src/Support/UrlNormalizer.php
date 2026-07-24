<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Support;

use App\Services\SettingsService;

class UrlNormalizer
{
    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    public function normalize(?string $url, array $context = []): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $resolvedUrl = $this->makeAbsoluteUrl($url);
        $parts = parse_url($resolvedUrl);

        if ($parts === false) {
            return trim($url);
        }

        $queryParams = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $queryParams);
        }

        $queryParams = $this->normalizeQueryParams($queryParams);

        $path = $parts['path'] ?? '/';
        if ($path === '') {
            $path = '/';
        }

        $normalized = ($parts['scheme'] ?? request()->getScheme()) . '://' . ($parts['host'] ?? request()->getHost());

        if (isset($parts['port']) && !in_array((int) $parts['port'], [80, 443], true)) {
            $normalized .= ':' . $parts['port'];
        }

        $normalized .= $path;

        if (!empty($queryParams)) {
            $normalized .= '?' . http_build_query($queryParams);
        }

        return MTOptimizeHooks::applyFilters('mtoptimize/url/normalize', $normalized, $url, $context, $queryParams);
    }

    /**
     * @param array<string, mixed> $queryParams
     * @return array<string, mixed>
     */
    protected function normalizeQueryParams(array $queryParams): array
    {
        $dropList = $this->normalizeList($this->settingsService->get('mtoptimize_canonical_drop_params', []));
        $keepList = $this->normalizeList($this->settingsService->get('mtoptimize_canonical_keep_params', ['page']));

        $result = [];

        foreach ($queryParams as $key => $value) {
            $normalizedKey = trim((string) $key);
            if ($normalizedKey === '') {
                continue;
            }

            if ($this->isTrackingParam($normalizedKey)) {
                continue;
            }

            if (in_array($normalizedKey, $dropList, true)) {
                continue;
            }

            if ($keepList !== [] && in_array($normalizedKey, $keepList, true)) {
                $result[$normalizedKey] = $value;
                continue;
            }

            if ($keepList === []) {
                $result[$normalizedKey] = $value;
                continue;
            }

            // Keep non-empty semantic params when allowlist is configured.
            if (!in_array($normalizedKey, $dropList, true) && !$this->isTrackingParam($normalizedKey)) {
                $result[$normalizedKey] = $value;
            }
        }

        ksort($result);

        return $result;
    }

    protected function isTrackingParam(string $key): bool
    {
        $lower = strtolower($key);

        if (str_starts_with($lower, 'utm_')) {
            return true;
        }

        return in_array($lower, ['gclid', 'fbclid', '_ga', '_gl', 'mc_cid', 'mc_eid'], true);
    }

    /**
     * @param mixed $input
     * @return array<int, string>
     */
    protected function normalizeList(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $input), static fn ($item): bool => $item !== ''));
    }

    protected function makeAbsoluteUrl(string $url): string
    {
        $trimmed = trim($url);

        if ($trimmed === '') {
            return url('/');
        }

        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        if (str_starts_with($trimmed, '/')) {
            return url($trimmed);
        }

        return url('/' . ltrim($trimmed, '/'));
    }
}
