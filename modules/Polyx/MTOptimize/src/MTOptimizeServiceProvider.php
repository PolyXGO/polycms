<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize;

use App\Contracts\SeoRenderContract;
use App\Facades\Hook;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use Illuminate\Support\ServiceProvider;
use Modules\Polyx\MTOptimize\Services\LinkResolver;
use Modules\Polyx\MTOptimize\Services\MetaResolver;
use Modules\Polyx\MTOptimize\Services\MTOptimizeEngine;
use Modules\Polyx\MTOptimize\Services\RobotsService;
use Modules\Polyx\MTOptimize\Services\RuleEngine;
use Modules\Polyx\MTOptimize\Services\SchemaResolver;
use Modules\Polyx\MTOptimize\Services\SEODocumentStore;
use Modules\Polyx\MTOptimize\Services\SeoRenderer;
use Modules\Polyx\MTOptimize\Services\SEOContextBuilder;
use Modules\Polyx\MTOptimize\Services\SitemapService;
use Modules\Polyx\MTOptimize\Services\SocialResolver;
use Modules\Polyx\MTOptimize\Support\MetaPayloadNormalizer;
use Modules\Polyx\MTOptimize\Support\SchemaPieceRegistry;
use Modules\Polyx\MTOptimize\Support\SitemapProviderRegistry;
use Modules\Polyx\MTOptimize\Support\TemplateVariableEngine;
use Modules\Polyx\MTOptimize\Support\UrlNormalizer;

class MTOptimizeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        require_once __DIR__ . '/Support/helpers.php';

        $this->app->singleton(TemplateVariableEngine::class);
        $this->app->singleton(UrlNormalizer::class);
        $this->app->singleton(MetaPayloadNormalizer::class);
        $this->app->singleton(SchemaPieceRegistry::class);
        $this->app->singleton(SitemapProviderRegistry::class);

        $this->app->singleton(SEOContextBuilder::class);
        $this->app->singleton(RuleEngine::class);
        $this->app->singleton(MetaResolver::class);
        $this->app->singleton(SocialResolver::class);
        $this->app->singleton(SchemaResolver::class);
        $this->app->singleton(LinkResolver::class);
        $this->app->singleton(SEODocumentStore::class);
        $this->app->singleton(RobotsService::class);
        $this->app->singleton(SitemapService::class);
        $this->app->singleton(MTOptimizeEngine::class);

        $this->app->singleton(SeoRenderer::class);
        $this->app->singleton(SeoRenderContract::class, SeoRenderer::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register redirect middleware on web group
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make(\Illuminate\Routing\Router::class);
        $router->pushMiddlewareToGroup('web', \Modules\Polyx\MTOptimize\Http\Middleware\SeoRedirectMiddleware::class);

        $this->registerAdminRoutes();
        $this->registerHookContracts();
        $this->registerSettingsDefaults();
        $this->registerLegacyBridge();
        $this->registerDefaultSchemaPieces();
        $this->registerDefaultSitemapProviders();
        $this->registerInvalidationWatchers();
    }

    protected function registerAdminRoutes(): void
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make(\Illuminate\Routing\Router::class);

        $router->group([
            'prefix' => 'api/v1/admin/seo-tools',
            'middleware' => ['web', 'auth', 'admin'],
        ], function (\Illuminate\Routing\Router $r) {
            $controller = \Modules\Polyx\MTOptimize\Http\Controllers\SeoToolsController::class;

            // Redirects
            $r->get('redirects', [$controller, 'redirectIndex']);
            $r->post('redirects', [$controller, 'redirectStore']);
            $r->put('redirects/{id}', [$controller, 'redirectUpdate']);
            $r->delete('redirects/{id}', [$controller, 'redirectDestroy']);

            // 404 Monitor
            $r->get('404-logs', [$controller, 'notFoundIndex']);
            $r->delete('404-logs/{id}', [$controller, 'notFoundDestroy']);
            $r->post('404-logs/clear', [$controller, 'notFoundClear']);
            $r->post('404-logs/{id}/to-redirect', [$controller, 'notFoundToRedirect']);

            // Physical robots.txt
            $r->get('physical-robots', [$controller, 'checkPhysicalRobots']);
            $r->delete('physical-robots', [$controller, 'deletePhysicalRobots']);
        });
    }

    protected function registerHookContracts(): void
    {
        $identity = static fn (mixed $value): mixed => $value;

        $filterHooks = [
            'mtoptimize/context/build',
            'mtoptimize/context/before_resolve',
            'mtoptimize/context/after_resolve',
            'mtoptimize/meta/defaults',
            'mtoptimize/meta/resolve',
            'mtoptimize/meta/final',
            'mtoptimize/meta/title',
            'mtoptimize/meta/description',
            'mtoptimize/meta/keywords',
            'mtoptimize/meta/robots',
            'mtoptimize/meta/canonical',
            'mtoptimize/meta/author',
            'mtoptimize/meta/publisher',
            'mtoptimize/opengraph/resolve',
            'mtoptimize/opengraph/final',
            'mtoptimize/twitter/resolve',
            'mtoptimize/twitter/final',
            'mtoptimize/schema/defaults',
            'mtoptimize/schema/resolve',
            'mtoptimize/schema/final',
            'mtoptimize/sitemap/indexes',
            'mtoptimize/sitemap/query',
            'mtoptimize/sitemap/items',
            'mtoptimize/sitemap/item',
            'mtoptimize/sitemap/exclude',
            'mtoptimize/robots/rules',
            'mtoptimize/robots/content',
            'mtoptimize/robots/meta',
            'mtoptimize/link/canonical',
            'mtoptimize/link/alternate',
            'mtoptimize/link/hreflang',
            'mtoptimize/link/prev',
            'mtoptimize/link/next',
            'mtoptimize/content/analyze',
            'mtoptimize/content/checklist',
            'mtoptimize/content/suggestions',
            'mtoptimize/url/normalize',
            'mtoptimize/template/variables',
            'mtoptimize/template/render',
            'mtoptimize/schema/pieces',
            'mtoptimize/schema/piece',
            'mtoptimize/sitemap/providers',
        ];

        foreach ($filterHooks as $hookName) {
            Hook::addFilter($hookName, $identity, 1, 1);
        }

        Hook::doAction('mtoptimize/context/hooks_registered', $filterHooks, '1.0.0');
    }

    protected function registerSettingsDefaults(): void
    {
        Hook::addFilter('settings.defaults', function (array $defaults): array {
            $defaults['mtoptimize'] = [
                'mtoptimize_enabled' => [
                    'value' => true,
                    'type' => 'boolean',
                    'label' => 'Enable MTOptimize',
                    'description' => 'Enable SEO rendering through MTOptimize.',
                ],
                'mtoptimize_default_title_template' => [
                    'value' => '{title} | {siteName}',
                    'type' => 'string',
                    'label' => 'Default title template',
                    'description' => 'Fallback title template for most pages.',
                ],
                'mtoptimize_home_title_template' => [
                    'value' => '{siteName} | {siteTagline}',
                    'type' => 'string',
                    'label' => 'Home title template',
                    'description' => 'Title template for homepage.',
                ],
                'mtoptimize_default_description_template' => [
                    'value' => '{excerpt}',
                    'type' => 'string',
                    'label' => 'Default description template',
                    'description' => 'Fallback template used when description is missing.',
                ],
                'mtoptimize_enable_keywords' => [
                    'value' => false,
                    'type' => 'boolean',
                    'label' => 'Enable meta keywords',
                    'description' => 'Optional compatibility metadata for legacy systems.',
                ],
                'mtoptimize_default_robots' => [
                    'value' => 'index,follow',
                    'type' => 'string',
                    'label' => 'Default robots policy',
                    'description' => 'Default robots directives for indexable pages.',
                ],
                'mtoptimize_noindex_search' => [
                    'value' => true,
                    'type' => 'boolean',
                    'label' => 'Noindex search pages',
                    'description' => 'Apply noindex,nofollow for search result pages.',
                ],
                'mtoptimize_noindex_system' => [
                    'value' => true,
                    'type' => 'boolean',
                    'label' => 'Noindex system pages',
                    'description' => 'Apply noindex,nofollow for system routes.',
                ],
                'mtoptimize_noindex_preview' => [
                    'value' => true,
                    'type' => 'boolean',
                    'label' => 'Noindex preview pages',
                    'description' => 'Apply noindex,nofollow for preview requests.',
                ],
                'mtoptimize_advanced_robots' => [
                    'value' => [],
                    'type' => 'json',
                    'label' => 'Advanced robots directives',
                    'description' => 'Additional directives like noarchive, nosnippet, max-snippet:50.',
                ],
                'mtoptimize_default_og_image' => [
                    'value' => null,
                    'type' => 'string',
                    'label' => 'Default social image',
                    'description' => 'Fallback image for OpenGraph and Twitter cards.',
                ],
                'mtoptimize_twitter_site' => [
                    'value' => null,
                    'type' => 'string',
                    'label' => 'Twitter account',
                    'description' => 'Twitter @account for card metadata.',
                ],
                'mtoptimize_suppress_pagination_links_on_noindex' => [
                    'value' => true,
                    'type' => 'boolean',
                    'label' => 'Suppress prev/next when noindex',
                    'description' => 'Avoid conflicting signals on noindex pages.',
                ],
                'mtoptimize_canonical_drop_params' => [
                    'value' => ['gclid', 'fbclid', '_ga', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'],
                    'type' => 'json',
                    'label' => 'Canonical drop params',
                    'description' => 'Query parameters to strip from canonical URLs.',
                ],
                'mtoptimize_canonical_keep_params' => [
                    'value' => ['page'],
                    'type' => 'json',
                    'label' => 'Canonical keep params',
                    'description' => 'Query parameters that should remain in canonical URLs.',
                ],
                'mtoptimize_organization_name' => [
                    'value' => null,
                    'type' => 'string',
                    'label' => 'Organization name',
                    'description' => 'Organization schema name.',
                ],
                'mtoptimize_organization_logo' => [
                    'value' => null,
                    'type' => 'string',
                    'label' => 'Organization logo URL',
                    'description' => 'Organization schema logo URL.',
                ],
                'mtoptimize_robots_extra_disallow' => [
                    'value' => ['/admin', '/api'],
                    'type' => 'json',
                    'label' => 'Robots disallow paths',
                    'description' => 'Additional disallow directives for robots.txt.',
                ],
                'mtoptimize_robots_extra_allow' => [
                    'value' => ['/'],
                    'type' => 'json',
                    'label' => 'Robots allow paths',
                    'description' => 'Additional allow directives for robots.txt.',
                ],
                'mtoptimize_robots_allow_non_production' => [
                    'value' => false,
                    'type' => 'boolean',
                    'label' => 'Allow crawl on non-production',
                    'description' => 'If disabled, staging/dev robots will disallow all crawling.',
                ],
                'mtoptimize_cache_ttl_seconds' => [
                    'value' => 900,
                    'type' => 'integer',
                    'label' => 'Metadata cache TTL (seconds)',
                    'description' => 'In-memory cache lifetime for resolved metadata payload.',
                ],
                'mtoptimize_sitemap_chunk_size' => [
                    'value' => 500,
                    'type' => 'integer',
                    'label' => 'Sitemap chunk size',
                    'description' => 'Number of URLs returned per sitemap page.',
                ],
                'mtoptimize_sitemap_cache_ttl_seconds' => [
                    'value' => 3600,
                    'type' => 'integer',
                    'label' => 'Sitemap cache TTL (seconds)',
                    'description' => 'Cache lifetime for sitemap index and chunk files.',
                ],
            ];

            return $defaults;
        }, 30, 1);
    }

    protected function registerLegacyBridge(): void
    {
        Hook::addFilter('seo.canonical_url', function (mixed $canonical, ?array $context = null): mixed {
            if (($context['_mtoptimize_internal'] ?? false) === true) {
                return $canonical;
            }

            if (!app()->bound(MTOptimizeEngine::class)) {
                return $canonical;
            }

            return app(MTOptimizeEngine::class)->resolveCanonicalFromCurrentRequest((string) $canonical);
        }, 50, 2);
    }

    protected function registerDefaultSchemaPieces(): void
    {
        $registry = app(SchemaPieceRegistry::class);

        $registry->register('webpage.search_results', function (array $context, array $payload): ?array {
            if (($context['pageType'] ?? null) !== 'search') {
                return null;
            }

            $canonical = $payload['meta']['canonical'] ?? $context['fullUrl'] ?? url('/');

            return [
                '@type' => 'SearchResultsPage',
                '@id' => $canonical . '#search-results',
                'url' => $canonical,
                'name' => $payload['meta']['title'] ?? 'Search results',
            ];
        }, 20);
    }

    protected function registerDefaultSitemapProviders(): void
    {
        $registry = app(SitemapProviderRegistry::class);

        $registry->register('posts', function (int $page, int $chunk): array {
            $query = Post::query()
                ->where('status', 'published')
                ->where('type', 'post')
                ->whereDoesntHave('meta', function ($q) {
                    $q->where('meta_key', 'sitemap_exclude')->whereIn('meta_value', ['1', 'true']);
                })
                ->orderByDesc('updated_at');

            $total = (int) $query->count();
            $items = $query->forPage($page, $chunk)->get(['id', 'slug', 'updated_at', 'featured_image']);

            return [
                'items' => $items->map(function (Post $post) {
                    $item = [
                        'loc' => url($post->frontend_url),
                        'lastmod' => optional($post->updated_at)->toAtomString(),
                    ];
                    // Image sitemap extension
                    $image = trim((string) ($post->featured_image ?? ''));
                    if ($image !== '') {
                        $item['images'] = [['loc' => str_starts_with($image, 'http') ? $image : url($image)]];
                    }
                    return $item;
                })->all(),
                'total_pages' => max(1, (int) ceil($total / max(1, $chunk))),
            ];
        }, 10);

        $registry->register('pages', function (int $page, int $chunk): array {
            $query = Post::query()
                ->where('status', 'published')
                ->where('type', 'page')
                ->whereDoesntHave('meta', function ($q) {
                    $q->where('meta_key', 'sitemap_exclude')->whereIn('meta_value', ['1', 'true']);
                })
                ->orderByDesc('updated_at');

            $total = (int) $query->count();
            $items = $query->forPage($page, $chunk)->get(['id', 'slug', 'updated_at', 'featured_image']);

            return [
                'items' => $items->map(function (Post $item) {
                    $entry = [
                        'loc' => url($item->frontend_url),
                        'lastmod' => optional($item->updated_at)->toAtomString(),
                    ];
                    $image = trim((string) ($item->featured_image ?? ''));
                    if ($image !== '') {
                        $entry['images'] = [['loc' => str_starts_with($image, 'http') ? $image : url($image)]];
                    }
                    return $entry;
                })->all(),
                'total_pages' => max(1, (int) ceil($total / max(1, $chunk))),
            ];
        }, 20);

        $registry->register('products', function (int $page, int $chunk): array {
            $query = Product::query()
                ->where('status', 'published')
                ->orderByDesc('updated_at');

            $total = (int) $query->count();
            $items = $query->forPage($page, $chunk)->get(['id', 'slug', 'updated_at']);

            return [
                'items' => $items->map(fn (Product $item) => [
                    'loc' => url($item->frontend_url),
                    'lastmod' => optional($item->updated_at)->toAtomString(),
                ])->all(),
                'total_pages' => max(1, (int) ceil($total / max(1, $chunk))),
            ];
        }, 30);

        $registry->register('categories', function (int $page, int $chunk): array {
            $query = Category::query()
                ->where('type', 'post')
                ->orderByDesc('updated_at');

            $total = (int) $query->count();
            $items = $query->forPage($page, $chunk)->get(['id', 'slug', 'updated_at']);

            return [
                'items' => $items->map(fn (Category $item) => [
                    'loc' => url($item->frontend_url),
                    'lastmod' => optional($item->updated_at)->toAtomString(),
                ])->all(),
                'total_pages' => max(1, (int) ceil($total / max(1, $chunk))),
            ];
        }, 40);

        $registry->register('product-categories', function (int $page, int $chunk): array {
            $query = ProductCategory::query()->orderByDesc('updated_at');
            $total = (int) $query->count();
            $items = $query->forPage($page, $chunk)->get(['id', 'slug', 'updated_at']);

            return [
                'items' => $items->map(fn (ProductCategory $item) => [
                    'loc' => url($item->frontend_url),
                    'lastmod' => optional($item->updated_at)->toAtomString(),
                ])->all(),
                'total_pages' => max(1, (int) ceil($total / max(1, $chunk))),
            ];
        }, 50);

        $registry->register('post-tags', function (int $page, int $chunk): array {
            $query = PostTag::query()->orderByDesc('updated_at');
            $total = (int) $query->count();
            $items = $query->forPage($page, $chunk)->get(['id', 'slug', 'updated_at']);

            return [
                'items' => $items->map(fn (PostTag $item) => [
                    'loc' => url('/' . trim(app(\App\Services\SettingsService::class)->getPermalinkStructure()['tags']['post'] ?? 'tags', '/') . '/' . $item->slug),
                    'lastmod' => optional($item->updated_at)->toAtomString(),
                ])->all(),
                'total_pages' => max(1, (int) ceil($total / max(1, $chunk))),
            ];
        }, 60);

        $registry->register('product-tags', function (int $page, int $chunk): array {
            $query = ProductTag::query()->orderByDesc('updated_at');
            $total = (int) $query->count();
            $items = $query->forPage($page, $chunk)->get(['id', 'slug', 'updated_at']);

            return [
                'items' => $items->map(fn (ProductTag $item) => [
                    'loc' => url($item->frontend_url),
                    'lastmod' => optional($item->updated_at)->toAtomString(),
                ])->all(),
                'total_pages' => max(1, (int) ceil($total / max(1, $chunk))),
            ];
        }, 70);
    }

    protected function registerInvalidationWatchers(): void
    {
        $invalidateEntity = function (string $objectType, mixed $objectId = null): void {
            $store = app(SEODocumentStore::class);
            $sitemap = app(SitemapService::class);

            $store->invalidateByObject($objectType, $objectId === null ? null : (string) $objectId);
            $sitemap->invalidate();
        };

        Hook::addAction('post.saved', fn (Post $post) => $invalidateEntity('post', $post->id), 20, 1);
        Hook::addAction('post.deleted', fn (Post $post) => $invalidateEntity('post', $post->id), 20, 1);

        Hook::addAction('product.saved', fn (Product $product) => $invalidateEntity('product', $product->id), 20, 1);
        Hook::addAction('product.deleted', fn (Product $product) => $invalidateEntity('product', $product->id), 20, 1);

        Hook::addAction('category.saved', fn ($category) => $invalidateEntity('category', $category?->id), 20, 1);
        Hook::addAction('category.deleted', fn ($category) => $invalidateEntity('category', $category?->id), 20, 1);

        Hook::addAction('tag.saved', fn ($tag) => $invalidateEntity('tag', $tag?->id), 20, 1);
        Hook::addAction('tag.deleted', fn ($tag) => $invalidateEntity('tag', $tag?->id), 20, 1);

        Hook::addAction('settings.saved', function (mixed $payload = null): void {
            app(SEODocumentStore::class)->invalidateAll();
            app(SitemapService::class)->invalidate();
        }, 20, 1);
    }
}
