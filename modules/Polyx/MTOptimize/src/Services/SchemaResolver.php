<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Services\SettingsService;
use Modules\Polyx\MTOptimize\Support\MTOptimizeHooks;
use Modules\Polyx\MTOptimize\Support\SchemaPieceRegistry;

class SchemaResolver
{
    public function __construct(
        protected SettingsService $settingsService,
        protected SchemaPieceRegistry $schemaPieceRegistry,
    ) {}

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     * @param array<string, mixed> $social
     * @param array<string, mixed> $defaults
     * @return array<int, array<string, mixed>>
     */
    public function resolve(array $context, array $meta, array $social, array $defaults): array
    {
        $siteUrl = rtrim((string) ($context['siteUrl'] ?? url('/')), '/');
        $canonical = (string) ($meta['canonical'] ?? $context['fullUrl'] ?? $siteUrl);
        $entity = $context['entity'] ?? null;

        $nodes = [];

        $organizationId = $siteUrl . '#organization';
        $websiteId = $siteUrl . '#website';
        $webpageId = $canonical . '#webpage';

        $nodes[] = [
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => (string) ($defaults['schema']['organization_name'] ?? $this->settingsService->get('site_title', config('app.name', 'PolyCMS'))),
            'url' => $siteUrl,
            'logo' => $this->resolveOrganizationLogo($defaults),
        ];

        $nodes[] = [
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => $siteUrl,
            'name' => (string) $this->settingsService->get('site_title', config('app.name', 'PolyCMS')),
            'publisher' => ['@id' => $organizationId],
        ];

        $nodes[] = [
            '@type' => $this->resolveWebPageType($context),
            '@id' => $webpageId,
            'url' => $canonical,
            'name' => (string) ($meta['title'] ?? ''),
            'description' => (string) ($meta['description'] ?? ''),
            'inLanguage' => (string) ($context['locale'] ?? app()->getLocale()),
            'isPartOf' => ['@id' => $websiteId],
        ];

        $articleNode = $this->resolveArticleNode($context, $meta, $social, $entity, $websiteId, $organizationId, $webpageId);
        if ($articleNode !== null) {
            $nodes[] = $articleNode;
        }

        $productNode = $this->resolveProductNode($context, $meta, $social, $entity, $webpageId);
        if ($productNode !== null) {
            $nodes[] = $productNode;
        }

        $breadcrumbNode = $this->resolveBreadcrumbNode($context, $canonical);
        if ($breadcrumbNode !== null) {
            $nodes[] = $breadcrumbNode;
        }

        $faqNode = $this->resolveFaqNode($context, $entity, $canonical);
        if ($faqNode !== null) {
            $nodes[] = $faqNode;
        }

        $nodes = MTOptimizeHooks::applyFilters('mtoptimize/schema/defaults', $nodes, $context, $meta, $social, $defaults);

        $pieces = $this->schemaPieceRegistry->resolve($context, [
            'meta' => $meta,
            'social' => $social,
            'schema' => $nodes,
        ]);

        foreach ($pieces as $piece) {
            $nodes[] = $piece;
        }

        $entityType = (string) ($context['entityType'] ?? '');
        if ($entityType !== '') {
            $nodes = MTOptimizeHooks::applyFilters("mtoptimize/schema/types/{$entityType}", $nodes, $context, $meta, $social, $defaults);
            $nodes = MTOptimizeHooks::applyFilters("mtoptimize/{$entityType}/schema", $nodes, $context, $meta, $social, $defaults);
        }

        $nodes = MTOptimizeHooks::applyFilters('mtoptimize/schema/resolve', $nodes, $context, $meta, $social, $defaults);

        return MTOptimizeHooks::applyFilters('mtoptimize/schema/final', $this->dedupeNodes($nodes, $canonical), $context, $meta, $social, $defaults);
    }

    protected function resolveOrganizationLogo(array $defaults): ?string
    {
        $logo = (string) ($defaults['schema']['organization_logo'] ?? $this->settingsService->get('site_icon', ''));

        if ($logo === '') {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }

        return url($logo);
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function resolveWebPageType(array $context): string
    {
        return match ((string) ($context['pageType'] ?? 'system')) {
            'search' => 'SearchResultsPage',
            'archive', 'taxonomy' => 'CollectionPage',
            default => 'WebPage',
        };
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     * @param array<string, mixed> $social
     * @param mixed $entity
     * @return array<string, mixed>|null
     */
    protected function resolveArticleNode(array $context, array $meta, array $social, mixed $entity, string $websiteId, string $organizationId, string $webpageId): ?array
    {
        $entityType = (string) ($context['entityType'] ?? '');
        if (!in_array($entityType, ['post', 'page'], true)) {
            return null;
        }

        if (!is_object($entity)) {
            return null;
        }

        $canonical = (string) ($meta['canonical'] ?? $context['fullUrl'] ?? url('/'));

        $node = [
            '@type' => $entityType === 'post' ? 'Article' : 'WebPage',
            '@id' => $canonical . '#article',
            'mainEntityOfPage' => ['@id' => $webpageId],
            'headline' => (string) ($meta['title'] ?? ''),
            'description' => (string) ($meta['description'] ?? ''),
            'url' => $canonical,
            'isPartOf' => ['@id' => $websiteId],
            'publisher' => ['@id' => $organizationId],
        ];

        $primaryTaxonomy = is_array($context['primaryTaxonomy'] ?? null) ? $context['primaryTaxonomy'] : [];
        if (!empty($primaryTaxonomy['name'])) {
            $node['articleSection'] = (string) $primaryTaxonomy['name'];
        }

        if (isset($social['og']['image'])) {
            $node['image'] = [$social['og']['image']];
        }

        if (isset($entity->published_at) && method_exists($entity->published_at, 'toAtomString')) {
            $node['datePublished'] = $entity->published_at->toAtomString();
        }

        if (isset($entity->updated_at) && method_exists($entity->updated_at, 'toAtomString')) {
            $node['dateModified'] = $entity->updated_at->toAtomString();
        }

        if (isset($entity->user) && is_object($entity->user) && isset($entity->user->name)) {
            $node['author'] = [
                '@type' => 'Person',
                'name' => (string) $entity->user->name,
            ];
        }

        return $node;
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     * @param array<string, mixed> $social
     * @param mixed $entity
     * @return array<string, mixed>|null
     */
    protected function resolveProductNode(array $context, array $meta, array $social, mixed $entity, string $webpageId): ?array
    {
        if (($context['entityType'] ?? null) !== 'product' || !is_object($entity)) {
            return null;
        }

        $canonical = (string) ($meta['canonical'] ?? $context['fullUrl'] ?? url('/'));

        $node = [
            '@type' => 'Product',
            '@id' => $canonical . '#product',
            'name' => (string) ($entity->name ?? $meta['title'] ?? ''),
            'description' => (string) ($meta['description'] ?? $entity->short_description ?? ''),
            'url' => $canonical,
            'mainEntityOfPage' => ['@id' => $webpageId],
        ];

        $primaryTaxonomy = is_array($context['primaryTaxonomy'] ?? null) ? $context['primaryTaxonomy'] : [];
        if (!empty($primaryTaxonomy['name'])) {
            $node['category'] = (string) $primaryTaxonomy['name'];
        }

        if (isset($social['og']['image'])) {
            $node['image'] = [$social['og']['image']];
        }

        if (isset($entity->price)) {
            $node['offers'] = [
                '@type' => 'Offer',
                'price' => (string) $entity->price,
                'priceCurrency' => (string) $this->settingsService->get('ecommerce_currency', 'USD'),
                'availability' => (string) (($entity->stock_status ?? 'in_stock') === 'in_stock'
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock'),
                'url' => $canonical,
            ];
        }

        return $node;
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>|null
     */
    protected function resolveBreadcrumbNode(array $context, string $canonical): ?array
    {
        $entity = $context['entity'] ?? null;
        $pageType = (string) ($context['pageType'] ?? 'system');

        if (!in_array($pageType, ['single', 'taxonomy', 'archive', 'search'], true)) {
            return null;
        }

        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => (string) $this->settingsService->get('site_title', 'Home'),
                'item' => url('/'),
            ],
        ];

        $position = 2;

        $primaryTaxonomy = is_array($context['primaryTaxonomy'] ?? null) ? $context['primaryTaxonomy'] : [];
        $primaryTaxonomyName = trim((string) ($primaryTaxonomy['name'] ?? ''));
        $primaryTaxonomyUrl = trim((string) ($primaryTaxonomy['url'] ?? ''));

        if ($primaryTaxonomyName !== '' && $primaryTaxonomyUrl !== '' && $pageType === 'single') {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $primaryTaxonomyName,
                'item' => $primaryTaxonomyUrl,
            ];
            $position++;
        }

        if (is_object($entity) && isset($entity->name)) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => (string) $entity->name,
                'item' => $canonical,
            ];
            $position++;
        }

        if (is_object($entity) && isset($entity->title)) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => (string) $entity->title,
                'item' => $canonical,
            ];
        }

        if (count($items) < 2) {
            return null;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => $items,
        ];
    }

    /**
     * Auto-detect FAQ content and generate FAQPage schema.
     * Supports:
     * - <details><summary>Q</summary>A</details> pattern
     * - <div class="faq-block"><h3 class="faq-question">Q</h3><div class="faq-answer">A</div></div>
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>|null
     */
    protected function resolveFaqNode(array $context, mixed $entity, string $canonical): ?array
    {
        if (!is_object($entity)) {
            return null;
        }

        $html = (string) ($entity->content_html ?? $entity->content ?? $entity->description_html ?? '');
        if ($html === '') {
            return null;
        }

        $faqItems = [];

        // Pattern 1: <details><summary>Question</summary>Answer</details>
        if (preg_match_all(
            '/<details[^>]*>\s*<summary[^>]*>(.*?)<\/summary>(.*?)<\/details>/si',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $question = trim(strip_tags($match[1]));
                $answer = trim(strip_tags($match[2]));
                if ($question !== '' && $answer !== '') {
                    $faqItems[] = [
                        '@type' => 'Question',
                        'name' => $question,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $answer,
                        ],
                    ];
                }
            }
        }

        // Pattern 2: .faq-question / .faq-answer class-based blocks
        if (empty($faqItems) && preg_match_all(
            '/<[^>]+class="[^"]*faq-question[^"]*"[^>]*>(.*?)<\/[^>]+>\s*<[^>]+class="[^"]*faq-answer[^"]*"[^>]*>(.*?)<\/[^>]+>/si',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $question = trim(strip_tags($match[1]));
                $answer = trim(strip_tags($match[2]));
                if ($question !== '' && $answer !== '') {
                    $faqItems[] = [
                        '@type' => 'Question',
                        'name' => $question,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $answer,
                        ],
                    ];
                }
            }
        }

        $faqItems = MTOptimizeHooks::applyFilters('mtoptimize/schema/faq_items', $faqItems, $context, $entity);

        if (empty($faqItems)) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            '@id' => $canonical . '#faq',
            'mainEntity' => $faqItems,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     * @return array<int, array<string, mixed>>
     */
    protected function dedupeNodes(array $nodes, string $canonical): array
    {
        $result = [];
        $index = [];

        foreach ($nodes as $position => $node) {
            if (!is_array($node)) {
                continue;
            }

            if (!isset($node['@id']) || trim((string) $node['@id']) === '') {
                $node['@id'] = $canonical . '#schema-' . ($position + 1);
            }

            $id = (string) $node['@id'];

            if (isset($index[$id])) {
                $result[$index[$id]] = array_replace_recursive($result[$index[$id]], $node);
                continue;
            }

            $index[$id] = count($result);
            $result[] = $node;
        }

        return $result;
    }
}
