<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Services\SettingsService;
use Modules\Polyx\MTOptimize\Support\MTOptimizeHooks;

class RuleEngine
{
    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function resolve(array $context): array
    {
        $defaults = [
            'enabled' => (bool) $this->settingsService->get('mtoptimize_enabled', true),
            'templates' => [
                'default_title' => (string) $this->settingsService->get('mtoptimize_default_title_template', '{title} | {siteName}'),
                'home_title' => (string) $this->settingsService->get('mtoptimize_home_title_template', '{siteName} | {siteTagline}'),
                'default_description' => (string) $this->settingsService->get('mtoptimize_default_description_template', '{excerpt}'),
            ],
            'keywords' => [
                'enabled' => (bool) $this->settingsService->get('mtoptimize_enable_keywords', false),
            ],
            'robots' => [
                'default' => (string) $this->settingsService->get('mtoptimize_default_robots', 'index,follow'),
                'noindex_search' => (bool) $this->settingsService->get('mtoptimize_noindex_search', true),
                'noindex_system' => (bool) $this->settingsService->get('mtoptimize_noindex_system', true),
                'noindex_preview' => (bool) $this->settingsService->get('mtoptimize_noindex_preview', true),
                'advanced' => $this->normalizeList($this->settingsService->get('mtoptimize_advanced_robots', [])),
                'suppress_pagination_links_on_noindex' => (bool) $this->settingsService->get('mtoptimize_suppress_pagination_links_on_noindex', true),
            ],
            'social' => [
                'default_image' => $this->settingsService->get('mtoptimize_default_og_image'),
                'twitter_site' => $this->settingsService->get('mtoptimize_twitter_site'),
            ],
            'schema' => [
                'organization_name' => $this->settingsService->get('mtoptimize_organization_name', $this->settingsService->get('site_title', config('app.name', 'PolyCMS'))),
                'organization_logo' => $this->settingsService->get('mtoptimize_organization_logo'),
            ],
            'entity_overrides' => $this->resolveEntityOverrides($context['entity'] ?? null),
        ];

        $defaults = MTOptimizeHooks::applyFilters('mtoptimize/meta/defaults', $defaults, $context);

        $routeName = (string) ($context['routeName'] ?? '');
        if ($routeName !== '') {
            $defaults = MTOptimizeHooks::applyFilters("mtoptimize/rules/route/{$routeName}", $defaults, $context);
        }

        $entityType = (string) ($context['entityType'] ?? '');
        if ($entityType !== '') {
            $defaults = MTOptimizeHooks::applyFilters("mtoptimize/rules/entity/{$entityType}", $defaults, $context);
        }

        return $defaults;
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveEntityOverrides(mixed $entity): array
    {
        if (!is_object($entity)) {
            return [];
        }

        $meta = [];
        $social = [];

        $meta['title'] = $entity->meta_title ?? null;
        $meta['description'] = $entity->meta_description ?? null;
        $meta['keywords'] = $this->normalizeKeywords($entity->meta_keywords ?? null);
        $social['image'] = $entity->og_image ?? $entity->featured_image ?? null;

        if (isset($entity->meta) && is_array($entity->meta)) {
            $metaArray = $entity->meta;

            $meta['title'] = $meta['title'] ?: ($metaArray['seo_title'] ?? $metaArray['meta_title'] ?? null);
            $meta['description'] = $meta['description'] ?: ($metaArray['seo_description'] ?? $metaArray['meta_description'] ?? null);
            $meta['canonical'] = $metaArray['canonical_url'] ?? $metaArray['canonical'] ?? null;
            $meta['robots'] = $metaArray['robots'] ?? null;

            $social['title'] = $metaArray['social_title'] ?? $metaArray['og_title'] ?? null;
            $social['description'] = $metaArray['social_description'] ?? $metaArray['og_description'] ?? null;
            $social['image'] = $social['image'] ?: ($metaArray['social_image'] ?? $metaArray['og_image'] ?? null);
        }

        if (method_exists($entity, 'getMeta')) {
            $meta['canonical'] = $meta['canonical'] ?? $entity->getMeta('canonical_url');
            $meta['robots'] = $meta['robots'] ?? $entity->getMeta('robots');
            $social['title'] = $social['title'] ?? $entity->getMeta('social_title');
            $social['description'] = $social['description'] ?? $entity->getMeta('social_description');
            $social['image'] = $social['image'] ?? $entity->getMeta('social_image');
        }

        if (isset($entity->settings) && is_array($entity->settings)) {
            $settings = $entity->settings;
            $seo = is_array($settings['seo'] ?? null) ? $settings['seo'] : [];

            $meta['canonical'] = $meta['canonical'] ?? ($seo['canonical_url'] ?? $settings['canonical_url'] ?? null);
            $meta['robots'] = $meta['robots'] ?? ($seo['robots'] ?? $settings['robots'] ?? null);

            $social['title'] = $social['title'] ?? ($seo['social_title'] ?? $settings['social_title'] ?? null);
            $social['description'] = $social['description'] ?? ($seo['social_description'] ?? $settings['social_description'] ?? null);
            $social['image'] = $social['image'] ?? ($seo['social_image'] ?? $settings['social_image'] ?? null);
        }

        return [
            'meta' => array_filter($meta, static fn ($value): bool => $value !== null && $value !== ''),
            'social' => array_filter($social, static fn ($value): bool => $value !== null && $value !== ''),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeList(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $input), static fn ($item): bool => $item !== ''));
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeKeywords(mixed $keywords): array
    {
        if ($keywords === null) {
            return [];
        }

        if (is_string($keywords)) {
            $keywords = preg_split('/[,\n]/', $keywords) ?: [];
        }

        if (!is_array($keywords)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($keyword): string => trim((string) $keyword), $keywords), static fn ($item): bool => $item !== ''));
    }
}
