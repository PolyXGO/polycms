<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Services\SettingsService;
use Modules\Polyx\MTOptimize\Support\MTOptimizeHooks;

class SocialResolver
{
    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $meta
     * @return array{og: array<string, string>, twitter: array<string, string>}
     */
    public function resolve(array $context, array $defaults, array $meta): array
    {
        $entity = $context['entity'] ?? null;
        $overrides = $defaults['entity_overrides']['social'] ?? [];

        $title = trim((string) ($overrides['title'] ?? '')) ?: (string) ($meta['title'] ?? '');
        $description = trim((string) ($overrides['description'] ?? '')) ?: (string) ($meta['description'] ?? '');
        $image = $this->resolveSocialImage($entity, $overrides, $defaults, $context);

        $og = [
            'title' => $title,
            'description' => $description,
            'type' => $this->resolveOgType((string) ($context['entityType'] ?? '')),
            'url' => (string) ($meta['canonical'] ?? $context['fullUrl'] ?? ''),
            'site_name' => (string) $this->settingsService->get('site_title', config('app.name', 'PolyCMS')),
            'locale' => (string) ($context['locale'] ?? app()->getLocale()),
        ];

        if ($image !== null) {
            $og['image'] = $image;
        }

        if (is_object($entity)) {
            $publishedAt = $entity->published_at ?? null;
            $updatedAt = $entity->updated_at ?? null;

            if ($publishedAt !== null && method_exists($publishedAt, 'toAtomString')) {
                $og['article:published_time'] = $publishedAt->toAtomString();
            }

            if ($updatedAt !== null && method_exists($updatedAt, 'toAtomString')) {
                $og['article:modified_time'] = $updatedAt->toAtomString();
            }
        }

        $og = MTOptimizeHooks::applyFilters('mtoptimize/opengraph/resolve', $og, $context, $defaults, $meta);
        $og = MTOptimizeHooks::applyFilters('mtoptimize/opengraph/final', $og, $context, $defaults, $meta);

        $twitter = [
            'card' => $image !== null ? 'summary_large_image' : 'summary',
            'title' => $title,
            'description' => $description,
        ];

        $twitterSite = trim((string) ($defaults['social']['twitter_site'] ?? ''));
        if ($twitterSite !== '') {
            $twitter['site'] = $twitterSite;
        }

        if ($image !== null) {
            $twitter['image'] = $image;
        }

        $twitter = MTOptimizeHooks::applyFilters('mtoptimize/twitter/resolve', $twitter, $context, $defaults, $meta);
        $twitter = MTOptimizeHooks::applyFilters('mtoptimize/twitter/final', $twitter, $context, $defaults, $meta);

        return [
            'og' => $this->sanitizeMap($og),
            'twitter' => $this->sanitizeMap($twitter),
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     * @param array<string, mixed> $defaults
     */
    protected function resolveSocialImage(mixed $entity, array $overrides, array $defaults, array $context = []): ?string
    {
        $candidates = [
            $overrides['image'] ?? null,
            is_object($entity) ? ($entity->og_image ?? null) : null,
            is_object($entity) ? ($entity->featured_image ?? null) : null,
            is_object($entity) ? ($entity->image ?? null) : null,
            $this->resolvePrimaryTaxonomyImage($context),
            $this->resolveEntityCategoryImage($entity),
            $defaults['social']['default_image'] ?? null,
            $this->settingsService->get('site_icon'),
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) ($candidate ?? ''));
            if ($value === '') {
                continue;
            }

            return $this->toAbsoluteUrl($value);
        }

        return null;
    }

    protected function resolveEntityCategoryImage(mixed $entity): ?string
    {
        if (!is_object($entity)) {
            return null;
        }

        if (method_exists($entity, 'categories') && method_exists($entity, 'relationLoaded') && $entity->relationLoaded('categories')) {
            $category = $entity->categories->first();
            if (is_object($category)) {
                return $category->image ?? null;
            }
        }

        if (method_exists($entity, 'relationLoaded') && $entity->relationLoaded('media') && isset($entity->media)) {
            $mediaCollection = $entity->media;
            if ($mediaCollection instanceof \Illuminate\Support\Collection) {
                $primary = $mediaCollection->first(function ($item): bool {
                    return (bool) ($item->pivot->is_primary ?? false);
                }) ?: $mediaCollection->first();

                if (is_object($primary)) {
                    return $primary->url ?? $primary->path ?? null;
                }
            }
        }

        if (method_exists($entity, 'primaryImage')) {
            $media = $entity->primaryImage();
            if (is_object($media)) {
                return $media->url ?? $media->path ?? null;
            }
        }

        return null;
    }

    protected function resolvePrimaryTaxonomyImage(array $context): ?string
    {
        $primaryTaxonomy = is_array($context['primaryTaxonomy'] ?? null) ? $context['primaryTaxonomy'] : [];
        $image = trim((string) ($primaryTaxonomy['image'] ?? ''));

        return $image === '' ? null : $image;
    }

    protected function resolveOgType(string $entityType): string
    {
        return match ($entityType) {
            'post' => 'article',
            'product' => 'product',
            default => 'website',
        };
    }

    /**
     * @param array<string, mixed> $map
     * @return array<string, string>
     */
    protected function sanitizeMap(array $map): array
    {
        $normalized = [];

        foreach ($map as $key => $value) {
            $normalizedKey = trim((string) $key);
            $normalizedValue = trim((string) $value);

            if ($normalizedKey === '' || $normalizedValue === '') {
                continue;
            }

            $normalized[$normalizedKey] = $normalizedValue;
        }

        return $normalized;
    }

    protected function toAbsoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return url('/' . ltrim($url, '/'));
    }
}
