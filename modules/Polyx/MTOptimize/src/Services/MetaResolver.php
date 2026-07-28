<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Facades\Hook;
use App\Services\SettingsService;
use Modules\Polyx\MTOptimize\Support\MTOptimizeHooks;
use Modules\Polyx\MTOptimize\Support\TemplateVariableEngine;
use Modules\Polyx\MTOptimize\Support\UrlNormalizer;

class MetaResolver
{
    public function __construct(
        protected SettingsService $settingsService,
        protected TemplateVariableEngine $templateVariableEngine,
        protected UrlNormalizer $urlNormalizer,
    ) {}

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    public function resolve(array $context, array $defaults): array
    {
        $entityType = (string) ($context['entityType'] ?? '');
        $overrides = $defaults['entity_overrides']['meta'] ?? [];

        $title = $this->resolveTitle($context, $defaults, $overrides);
        $description = $this->resolveDescription($context, $defaults, $overrides);
        $keywords = $this->resolveKeywords($defaults, $overrides);
        $robots = $this->resolveRobots($context, $defaults, $overrides);
        $canonical = $this->resolveCanonical($context, $overrides);
        $author = $this->resolveAuthor($context);
        $publisher = $this->resolvePublisher($context);
        $language = (string) ($context['locale'] ?? app()->getLocale());

        $title = MTOptimizeHooks::applyFilters('mtoptimize/meta/title', $title, $context, $defaults);
        $description = MTOptimizeHooks::applyFilters('mtoptimize/meta/description', $description, $context, $defaults);
        $keywords = MTOptimizeHooks::applyFilters('mtoptimize/meta/keywords', $keywords, $context, $defaults);
        $robots = MTOptimizeHooks::applyFilters('mtoptimize/meta/robots', $robots, $context, $defaults);
        $canonical = MTOptimizeHooks::applyFilters('mtoptimize/meta/canonical', $canonical, $context, $defaults);
        $author = MTOptimizeHooks::applyFilters('mtoptimize/meta/author', $author, $context, $defaults);
        $publisher = MTOptimizeHooks::applyFilters('mtoptimize/meta/publisher', $publisher, $context, $defaults);

        if ($entityType !== '') {
            $title = MTOptimizeHooks::applyFilters("mtoptimize/{$entityType}/meta/title", $title, $context, $defaults);
            $description = MTOptimizeHooks::applyFilters("mtoptimize/{$entityType}/meta/description", $description, $context, $defaults);
            $canonical = MTOptimizeHooks::applyFilters("mtoptimize/{$entityType}/canonical", $canonical, $context, $defaults);
        }

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'robots' => $robots,
            'canonical' => $canonical,
            'author' => $author,
            'publisher' => $publisher,
            'language' => $language,
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $overrides
     */
    protected function resolveTitle(array $context, array $defaults, array $overrides): string
    {
        $manual = trim((string) ($overrides['title'] ?? ''));
        if ($manual !== '') {
            return $manual;
        }

        $entityTitle = $this->extractEntityTitle($context['entity'] ?? null);
        $template = (string) ($defaults['templates']['default_title'] ?? '{title} | {siteName}');

        if (($context['pageType'] ?? null) === 'home') {
            $template = (string) ($defaults['templates']['home_title'] ?? '{siteName} | {siteTagline}');
        }

        $title = $this->templateVariableEngine->render($template, $context, [
            'title' => $entityTitle,
            'pageTitle' => $entityTitle,
        ]);

        if ($title === '') {
            $title = (string) $this->settingsService->get('site_title', config('app.name', 'PolyCMS'));
        }

        $page = max(1, (int) ($context['pagination']['page'] ?? 1));
        if ($page > 1) {
            $title .= ' - Page ' . $page;
        }

        return $title;
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $overrides
     */
    protected function resolveDescription(array $context, array $defaults, array $overrides): string
    {
        $manual = trim((string) ($overrides['description'] ?? ''));
        if ($manual !== '') {
            return $this->trimToLength($manual, 320);
        }

        $entityDescription = $this->extractEntityDescription($context['entity'] ?? null);
        if ($entityDescription !== '') {
            return $this->trimToLength($entityDescription, 320);
        }

        $template = (string) ($defaults['templates']['default_description'] ?? '{excerpt}');

        $description = $this->templateVariableEngine->render($template, $context, [
            'excerpt' => $entityDescription,
        ]);

        if ($description === '') {
            $description = (string) $this->settingsService->get('tagline', '');
        }

        return $this->trimToLength($description, 320);
    }

    /**
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $overrides
     * @return array<int, string>
     */
    protected function resolveKeywords(array $defaults, array $overrides): array
    {
        $enabled = (bool) ($defaults['keywords']['enabled'] ?? false);

        if (!$enabled) {
            return [];
        }

        $raw = $overrides['keywords'] ?? [];

        if (is_string($raw)) {
            $raw = preg_split('/[,\n]/', $raw) ?: [];
        }

        if (!is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $raw), static fn ($item): bool => $item !== ''));
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $overrides
     */
    protected function resolveRobots(array $context, array $defaults, array $overrides): string
    {
        $directives = array_filter(array_map('trim', explode(',', (string) ($defaults['robots']['default'] ?? 'index,follow'))));

        $allowGlobalIndexing = (bool) $this->settingsService->get('reading_search_engine_noindex', true);
        if (!$allowGlobalIndexing) {
            $directives = ['noindex', 'nofollow'];
        }

        if (($defaults['robots']['noindex_search'] ?? true) && ($context['pageType'] ?? null) === 'search') {
            $directives = ['noindex', 'nofollow'];
        }

        if (($defaults['robots']['noindex_system'] ?? true) && ($context['pageType'] ?? null) === 'system') {
            $directives = ['noindex', 'nofollow'];
        }

        if (($defaults['robots']['noindex_preview'] ?? true) && request()->boolean('preview')) {
            $directives = ['noindex', 'nofollow'];
        }

        $manual = trim((string) ($overrides['robots'] ?? ''));
        if ($manual !== '') {
            $directives = array_filter(array_map('trim', explode(',', $manual)));
        }

        $advanced = $defaults['robots']['advanced'] ?? [];
        if (is_array($advanced)) {
            foreach ($advanced as $directive) {
                $value = trim((string) $directive);
                if ($value !== '') {
                    $directives[] = $value;
                }
            }
        }

        $directives = array_values(array_unique(array_map(static fn ($value): string => strtolower(trim((string) $value)), $directives)));

        return implode(',', array_filter($directives));
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $overrides
     */
    protected function resolveCanonical(array $context, array $overrides): ?string
    {
        $manual = trim((string) ($overrides['canonical'] ?? ''));

        $canonical = $manual;
        if ($canonical === '') {
            $entity = $context['entity'] ?? null;
            if (is_object($entity) && isset($entity->frontend_url)) {
                $canonical = url((string) $entity->frontend_url);
            }
        }

        if ($canonical === '') {
            $canonical = (string) ($context['fullUrl'] ?? request()->fullUrl());
        }

        $canonical = $this->urlNormalizer->normalize($canonical, $context);

        $legacyContext = array_merge($context, ['_mtoptimize_internal' => true]);
        $canonical = Hook::applyFilters('seo.canonical_url', $canonical, $legacyContext);

        return $this->urlNormalizer->normalize(is_string($canonical) ? $canonical : null, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function resolveAuthor(array $context): ?string
    {
        $entity = $context['entity'] ?? null;

        if (is_object($entity) && isset($entity->user) && is_object($entity->user) && isset($entity->user->name)) {
            return trim((string) $entity->user->name) ?: null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function resolvePublisher(array $context): string
    {
        return (string) (
            $this->settingsService->get('mtoptimize_organization_name')
            ?: $this->settingsService->get('site_title', config('app.name', 'PolyCMS'))
        );
    }

    protected function extractEntityTitle(mixed $entity): string
    {
        if (!is_object($entity)) {
            return '';
        }

        return trim((string) (
            $entity->meta_title
            ?? $entity->title
            ?? $entity->name
            ?? ''
        ));
    }

    protected function extractEntityDescription(mixed $entity): string
    {
        if (!is_object($entity)) {
            return '';
        }

        $description = (string) (
            $entity->meta_description
            ?? $entity->excerpt
            ?? $entity->summary
            ?? $entity->short_description
            ?? $entity->description
            ?? $entity->content_html
            ?? $entity->description_html
            ?? ''
        );

        if ($description === '' && method_exists($entity, 'getMeta')) {
            $description = (string) ($entity->getMeta('seo_description') ?? '');
        }

        $description = trim(strip_tags($description));

        return $description;
    }

    protected function trimToLength(string $value, int $maxLength): string
    {
        $value = trim($value);

        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return trim(mb_substr($value, 0, $maxLength - 3)) . '...';
    }
}
