<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Support;

use App\Services\SettingsService;

class MetaPayloadNormalizer
{
    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    public function normalize(array $payload, array $context = []): array
    {
        $normalized = $payload;

        foreach (['title', 'description', 'canonical', 'robots', 'author', 'publisher', 'language'] as $field) {
            if (isset($normalized[$field])) {
                $normalized[$field] = $this->cleanString($normalized[$field]);
            }
        }

        $normalized['keywords'] = $this->normalizeKeywords($normalized['keywords'] ?? []);
        $normalized['og'] = $this->normalizeMap($normalized['og'] ?? []);
        $normalized['twitter'] = $this->normalizeMap($normalized['twitter'] ?? []);
        $normalized['links'] = $this->normalizeLinks($normalized['links'] ?? []);
        $normalized['schema'] = $this->normalizeSchema($normalized['schema'] ?? []);

        $suppressPagination = (bool) $this->settingsService->get('mtoptimize_suppress_pagination_links_on_noindex', true);
        if ($suppressPagination && $this->containsNoindex($normalized['robots'] ?? '')) {
            $normalized['links'] = array_values(array_filter(
                $normalized['links'],
                static fn (array $link): bool => !in_array(strtolower((string) ($link['rel'] ?? '')), ['prev', 'next'], true)
            ));
        }

        return MTOptimizeHooks::applyFilters('mtoptimize/meta/final', $normalized, $context);
    }

    protected function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    /**
     * @param mixed $keywords
     * @return array<int, string>
     */
    protected function normalizeKeywords(mixed $keywords): array
    {
        $items = [];

        if (is_string($keywords)) {
            $items = preg_split('/[,\n]/', $keywords) ?: [];
        } elseif (is_array($keywords)) {
            $items = $keywords;
        }

        $cleaned = [];
        foreach ($items as $item) {
            $value = trim((string) $item);
            if ($value !== '') {
                $cleaned[$value] = $value;
            }
        }

        return array_values($cleaned);
    }

    /**
     * @param mixed $map
     * @return array<string, string>
     */
    protected function normalizeMap(mixed $map): array
    {
        if (!is_array($map)) {
            return [];
        }

        $result = [];
        foreach ($map as $key => $value) {
            $normalizedKey = trim((string) $key);
            $normalizedValue = trim((string) $value);

            if ($normalizedKey === '' || $normalizedValue === '') {
                continue;
            }

            $result[$normalizedKey] = $normalizedValue;
        }

        return $result;
    }

    /**
     * @param mixed $links
     * @return array<int, array{rel: string, href: string, hreflang?: string}>
     */
    protected function normalizeLinks(mixed $links): array
    {
        if (!is_array($links)) {
            return [];
        }

        $normalized = [];
        $seen = [];

        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }

            $rel = strtolower(trim((string) ($link['rel'] ?? '')));
            $href = trim((string) ($link['href'] ?? ''));
            $hreflang = trim((string) ($link['hreflang'] ?? ''));

            if ($rel === '' || $href === '') {
                continue;
            }

            $signature = implode('|', [$rel, $href, strtolower($hreflang)]);
            if (isset($seen[$signature])) {
                continue;
            }

            $entry = [
                'rel' => $rel,
                'href' => $href,
            ];

            if ($hreflang !== '') {
                $entry['hreflang'] = $hreflang;
            }

            $seen[$signature] = true;
            $normalized[] = $entry;
        }

        return $normalized;
    }

    /**
     * @param mixed $schema
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeSchema(mixed $schema): array
    {
        if (!is_array($schema)) {
            return [];
        }

        $result = [];
        $seen = [];

        foreach ($schema as $node) {
            if (!is_array($node)) {
                continue;
            }

            $id = trim((string) ($node['@id'] ?? ''));
            $signature = $id !== '' ? $id : sha1(json_encode($node));

            if (isset($seen[$signature])) {
                $result[$seen[$signature]] = array_replace_recursive($result[$seen[$signature]], $node);
                continue;
            }

            $seen[$signature] = count($result);
            $result[] = $node;
        }

        return $result;
    }

    protected function containsNoindex(?string $robots): bool
    {
        if ($robots === null) {
            return false;
        }

        return str_contains(strtolower($robots), 'noindex');
    }
}
