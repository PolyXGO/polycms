<?php

declare(strict_types=1);

namespace Tests\Feature\MTOptimize;

use App\Facades\Hook;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Modules\Polyx\MTOptimize\Services\MTOptimizeEngine;
use Modules\Polyx\MTOptimize\Services\SEOContextBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class MTOptimizeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var SettingsService $settings */
        $settings = app(SettingsService::class);
        $settings->set('mtoptimize_enabled', true, 'mtoptimize', 'boolean');
        $settings->set('mtoptimize_robots_allow_non_production', true, 'mtoptimize', 'boolean');
        $settings->set('reading_search_engine_noindex', true, 'reading', 'boolean');
    }

    public function test_context_builder_classifies_critical_page_types(): void
    {
        /** @var SEOContextBuilder $builder */
        $builder = app(SEOContextBuilder::class);

        $cases = [
            ['uri' => '/', 'route' => 'home', 'query' => [], 'params' => [], 'exception' => null, 'expected' => 'home'],
            ['uri' => '/posts', 'route' => 'posts.index', 'query' => [], 'params' => [], 'exception' => null, 'expected' => 'archive'],
            ['uri' => '/posts/demo', 'route' => 'posts.show', 'query' => [], 'params' => ['slug' => 'demo'], 'exception' => null, 'expected' => 'single'],
            ['uri' => '/categories/news', 'route' => 'categories.show', 'query' => [], 'params' => ['slug' => 'news'], 'exception' => null, 'expected' => 'taxonomy'],
            ['uri' => '/posts', 'route' => 'posts.index', 'query' => ['search' => 'seo'], 'params' => [], 'exception' => null, 'expected' => 'search'],
            ['uri' => '/admin', 'route' => 'admin.dashboard', 'query' => [], 'params' => [], 'exception' => null, 'expected' => 'system'],
            ['uri' => '/missing-page', 'route' => '', 'query' => [], 'params' => [], 'exception' => new NotFoundHttpException(), 'expected' => '404'],
        ];

        foreach ($cases as $case) {
            $request = $this->makeRequestForContext(
                $case['uri'],
                $case['route'],
                $case['query'],
                $case['params'],
                $case['exception']
            );

            $context = $builder->build($request);

            $this->assertSame(
                $case['expected'],
                $context['pageType'],
                sprintf('Expected pageType %s for route %s', $case['expected'], $case['route'])
            );
        }
    }

    public function test_engine_resolves_canonical_hreflang_and_schema_payload(): void
    {
        /** @var MTOptimizeEngine $engine */
        $engine = app(MTOptimizeEngine::class);

        $alternateFilter = static function (mixed $alternates): array {
            $current = is_array($alternates) ? $alternates : [];
            $current[] = [
                'locale' => 'vi',
                'url' => url('/vi/posts?page=2'),
            ];

            return $current;
        };

        Hook::addFilter('mtoptimize/link/alternate', $alternateFilter, 30, 1);

        try {
            $request = $this->makeRequestForContext(
                '/posts',
                'posts.index',
                ['utm_source' => 'newsletter', 'page' => 2]
            );

            $result = $engine->resolve($request);
            $payload = $result['payload'];

            $canonical = (string) ($payload['canonical'] ?? '');
            $this->assertNotSame('', $canonical);
            $this->assertStringContainsString('page=2', $canonical);
            $this->assertStringNotContainsString('utm_source', $canonical);

            $hasAlternate = false;
            foreach (($payload['links'] ?? []) as $link) {
                if (($link['rel'] ?? null) === 'alternate' && ($link['hreflang'] ?? null) === 'vi') {
                    $hasAlternate = true;
                    break;
                }
            }
            $this->assertTrue($hasAlternate, 'Expected alternate hreflang=vi link in payload.');

            $schemaTypes = array_map(
                static fn (array $node): string => (string) ($node['@type'] ?? ''),
                array_values(array_filter($payload['schema'] ?? [], 'is_array'))
            );

            $this->assertContains('Organization', $schemaTypes);
            $this->assertContains('WebSite', $schemaTypes);
            $this->assertContains('CollectionPage', $schemaTypes);
        } finally {
            Hook::removeFilter('mtoptimize/link/alternate', $alternateFilter, 30);
        }
    }

    public function test_sitemap_and_robots_endpoints_return_expected_response_formats(): void
    {
        $this->get('/sitemap-index.xml')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<sitemapindex', false);

        $this->get('/sitemap-posts.xml')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false);

        $this->get('/robots.txt')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *', false)
            ->assertSee('Sitemap: ', false);
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $routeParams
     */
    protected function makeRequestForContext(
        string $uri,
        string $routeName,
        array $query = [],
        array $routeParams = [],
        ?\Throwable $exception = null
    ): Request {
        $request = Request::create($uri, 'GET', $query);

        if ($routeName !== '') {
            $route = new Route(['GET'], parse_url($uri, PHP_URL_PATH) ?: '/', static fn () => null);
            $route->name($routeName);

            foreach ($routeParams as $key => $value) {
                $route->setParameter($key, $value);
            }

            $request->setRouteResolver(static fn () => $route);
        }

        if ($exception !== null) {
            $request->attributes->set('exception', $exception);
        }

        return $request;
    }
}
