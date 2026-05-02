<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SEOContextBuilder
{
    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(?Request $request = null): array
    {
        $request ??= request();
        $route = $request->route();
        $routeName = $route?->getName() ?? '';

        $context = [
            'siteId' => (string) config('app.name', 'default'),
            'siteUrl' => rtrim((string) url('/'), '/'),
            'locale' => app()->getLocale(),
            'routeName' => $routeName,
            'requestPath' => '/' . ltrim($request->path(), '/'),
            'fullUrl' => $request->fullUrl(),
            'entityType' => null,
            'entityId' => null,
            'entity' => null,
            'pageType' => $this->resolvePageType($routeName, $request),
            'pagination' => [
                'page' => max(1, (int) $request->query('page', 1)),
                'pageSize' => (int) $request->query('per_page', 0) ?: null,
                'totalItems' => $request->query('total_items') !== null ? (int) $request->query('total_items') : null,
                'totalPages' => $request->query('total_pages') !== null ? (int) $request->query('total_pages') : null,
            ],
            'alternates' => [],
            'primaryTaxonomy' => null,
            'siteConfig' => [
                'siteName' => (string) $this->settingsService->get('site_title', config('app.name', 'PolyCMS')),
                'siteTagline' => (string) $this->settingsService->get('tagline', ''),
                'readingSearchEngineNoindex' => (bool) $this->settingsService->get('reading_search_engine_noindex', true),
            ],
        ];

        [$entityType, $entityId, $entity] = $this->resolveEntityContext($routeName, $route, $request);

        $context['entityType'] = $entityType;
        $context['entityId'] = $entityId;
        $context['entity'] = $entity;
        $context['primaryTaxonomy'] = $this->resolvePrimaryTaxonomy($entityType, $entity);

        return $context;
    }

    protected function resolvePageType(string $routeName, Request $request): string
    {
        $exception = $request->attributes->get('exception');
        if ($exception instanceof NotFoundHttpException || $routeName === 'errors.404') {
            return '404';
        }

        if ($routeName === 'home') {
            return 'home';
        }

        if ($request->query('search') !== null) {
            return 'search';
        }

        if (in_array($routeName, ['posts.show', 'pages.show', 'products.show'], true)) {
            return 'single';
        }

        if (in_array($routeName, ['categories.show', 'product-categories.show', 'tags.show', 'product-tags.show', 'product-brands.show'], true)) {
            return 'taxonomy';
        }

        if (in_array($routeName, ['posts.index', 'products.index', 'authors.show'], true)) {
            return 'archive';
        }

        if ($routeName === '' || str_starts_with($routeName, 'admin.') || str_starts_with($routeName, 'api.')) {
            return 'system';
        }

        return 'system';
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolveEntityContext(string $routeName, ?Route $route, Request $request): array
    {
        $slug = (string) $route?->parameter('slug', '');

        return match ($routeName) {
            'posts.show' => $this->resolvePostBySlug($slug, 'post'),
            'pages.show' => $this->resolvePostBySlug($slug, 'page'),
            'products.show' => $this->resolveProductBySlug($slug),
            'categories.show' => $this->resolveCategoryBySlug($slug),
            'product-categories.show' => $this->resolveProductCategoryBySlug($slug),
            'tags.show' => $this->resolvePostTagBySlug($slug),
            'product-tags.show' => $this->resolveProductTagBySlug($slug),
            'product-brands.show' => $this->resolveProductBrandBySlug($slug),
            'authors.show' => $this->resolveAuthor($route?->parameter('user')),
            default => $this->resolveBoundEntity($route, $request),
        };
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolvePostBySlug(string $slug, string $type): array
    {
        if ($slug === '') {
            return [null, null, null];
        }

        $post = Post::query()
            ->with(['user', 'categories', 'tags', 'meta'])
            ->where('slug', $slug)
            ->where('type', $type)
            ->first();

        return $post ? [$type, $post->id, $post] : [null, null, null];
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolveProductBySlug(string $slug): array
    {
        if ($slug === '') {
            return [null, null, null];
        }

        $product = Product::query()
            ->with(['user', 'categories', 'tags', 'media', 'brands'])
            ->where('slug', $slug)
            ->first();

        return $product ? ['product', $product->id, $product] : [null, null, null];
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolveCategoryBySlug(string $slug): array
    {
        if ($slug === '') {
            return [null, null, null];
        }

        $category = Category::query()->where('slug', $slug)->first();

        return $category ? ['category', $category->id, $category] : [null, null, null];
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolveProductCategoryBySlug(string $slug): array
    {
        if ($slug === '') {
            return [null, null, null];
        }

        $category = ProductCategory::query()->where('slug', $slug)->first();

        return $category ? ['product_category', $category->id, $category] : [null, null, null];
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolvePostTagBySlug(string $slug): array
    {
        if ($slug === '') {
            return [null, null, null];
        }

        $tag = PostTag::query()->where('slug', $slug)->first();

        return $tag ? ['post_tag', $tag->id, $tag] : [null, null, null];
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolveProductTagBySlug(string $slug): array
    {
        if ($slug === '') {
            return [null, null, null];
        }

        $tag = ProductTag::query()->where('slug', $slug)->first();

        return $tag ? ['product_tag', $tag->id, $tag] : [null, null, null];
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolveProductBrandBySlug(string $slug): array
    {
        if ($slug === '') {
            return [null, null, null];
        }

        $brand = ProductBrand::query()->where('slug', $slug)->first();

        return $brand ? ['product_brand', $brand->id, $brand] : [null, null, null];
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolveAuthor(mixed $routeUser): array
    {
        if ($routeUser instanceof User) {
            return ['author', $routeUser->id, $routeUser];
        }

        if (is_scalar($routeUser) && (string) $routeUser !== '') {
            $user = User::query()->where('id', $routeUser)->orWhere('username', (string) $routeUser)->first();
            if ($user) {
                return ['author', $user->id, $user];
            }
        }

        return [null, null, null];
    }

    /**
     * @return array{0: string|null, 1: string|int|null, 2: mixed}
     */
    protected function resolveBoundEntity(?Route $route, Request $request): array
    {
        if ($route === null) {
            return [null, null, null];
        }

        foreach ($route->parameters() as $parameter) {
            if (is_object($parameter) && property_exists($parameter, 'id')) {
                $entity = $this->preloadEntityRelations($parameter);
                $entityType = $this->resolveEntityTypeFromObject($entity);
                return [$entityType, $entity->id, $entity];
            }
        }

        if ($request->query('search') !== null) {
            return ['search', null, null];
        }

        return [null, null, null];
    }

    protected function resolveEntityTypeFromObject(object $entity): string
    {
        return match (true) {
            $entity instanceof Post => (string) ($entity->type ?: 'post'),
            $entity instanceof Product => 'product',
            $entity instanceof Category => 'category',
            $entity instanceof ProductCategory => 'product_category',
            $entity instanceof PostTag => 'post_tag',
            $entity instanceof ProductTag => 'product_tag',
            $entity instanceof ProductBrand => 'product_brand',
            $entity instanceof User => 'author',
            default => strtolower(class_basename($entity)),
        };
    }

    protected function preloadEntityRelations(object $entity): object
    {
        if ($entity instanceof Post) {
            $entity->loadMissing(['user', 'categories', 'tags', 'meta']);
            return $entity;
        }

        if ($entity instanceof Product) {
            $entity->loadMissing(['user', 'categories', 'tags', 'media', 'brands']);
            return $entity;
        }

        if ($entity instanceof Category || $entity instanceof ProductCategory) {
            $entity->loadMissing(['parent']);
            return $entity;
        }

        return $entity;
    }

    /**
     * @param string|null $entityType
     * @param mixed $entity
     * @return array<string, mixed>|null
     */
    protected function resolvePrimaryTaxonomy(?string $entityType, mixed $entity): ?array
    {
        if (!is_object($entity)) {
            return null;
        }

        if (in_array($entityType, ['post', 'page'], true)) {
            $primaryId = $this->extractPrimaryCategoryIdFromPost($entity);
            $category = $this->resolveCategoryCandidate($entity, $primaryId);
            return $category ? $this->buildTaxonomyPayload($category, 'category') : null;
        }

        if ($entityType === 'product') {
            $primaryId = $this->extractPrimaryCategoryIdFromProduct($entity);
            $category = $this->resolveCategoryCandidate($entity, $primaryId);
            return $category ? $this->buildTaxonomyPayload($category, 'product_category') : null;
        }

        return null;
    }

    protected function extractPrimaryCategoryIdFromPost(object $entity): ?int
    {
        if (method_exists($entity, 'getMeta')) {
            $value = $entity->getMeta('primary_category_id');
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        if (isset($entity->meta) && $entity->meta instanceof \Illuminate\Support\Collection) {
            $value = $entity->meta->firstWhere('meta_key', 'primary_category_id')?->meta_value;
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }

    protected function extractPrimaryCategoryIdFromProduct(object $entity): ?int
    {
        $settings = is_array($entity->settings ?? null) ? $entity->settings : [];

        $value = $settings['seo']['primary_category_id'] ?? $settings['primary_category_id'] ?? null;
        if (!is_numeric($value) || (int) $value <= 0) {
            return null;
        }

        return (int) $value;
    }

    protected function resolveCategoryCandidate(object $entity, ?int $primaryId): ?object
    {
        if (!method_exists($entity, 'categories')) {
            return null;
        }

        if (method_exists($entity, 'relationLoaded') && !$entity->relationLoaded('categories')) {
            $entity->loadMissing(['categories']);
        }

        $categories = $entity->categories ?? null;
        if (!$categories instanceof \Illuminate\Support\Collection || $categories->isEmpty()) {
            return null;
        }

        if ($primaryId !== null) {
            $matched = $categories->firstWhere('id', $primaryId);
            if (is_object($matched)) {
                return $matched;
            }
        }

        $first = $categories->first();
        return is_object($first) ? $first : null;
    }

    /**
     * @param object $category
     * @return array<string, mixed>
     */
    protected function buildTaxonomyPayload(object $category, string $type): array
    {
        $frontendUrl = trim((string) ($category->frontend_url ?? ''));
        $url = $frontendUrl === '' ? null : (str_starts_with($frontendUrl, 'http://') || str_starts_with($frontendUrl, 'https://')
            ? $frontendUrl
            : url($frontendUrl));

        return [
            'type' => $type,
            'id' => (int) ($category->id ?? 0),
            'name' => trim((string) ($category->name ?? '')),
            'slug' => trim((string) ($category->slug ?? '')),
            'url' => $url,
            'image' => trim((string) ($category->image ?? '')) ?: null,
        ];
    }
}
