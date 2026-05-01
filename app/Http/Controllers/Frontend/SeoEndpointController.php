<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Contracts\SeoRenderContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SeoEndpointController extends Controller
{
    public function robots(): Response
    {
        if (app()->bound(SeoRenderContract::class)) {
            /** @var SeoRenderContract $renderer */
            $renderer = app(SeoRenderContract::class);
            return $renderer->renderRobotsTxt();
        }

        $content = implode(PHP_EOL, [
            'User-agent: *',
            'Allow: /',
            'Sitemap: ' . url('/sitemap-index.xml'),
            '',
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemapIndex(): Response
    {
        if (app()->bound(SeoRenderContract::class)) {
            /** @var SeoRenderContract $renderer */
            $renderer = app(SeoRenderContract::class);
            return $renderer->renderSitemapIndex();
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL
            . '  <url><loc>' . e(url('/')) . '</loc></url>' . PHP_EOL
            . '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function sitemap(?string $type = null, ?int $page = null): Response
    {
        if (app()->bound(SeoRenderContract::class)) {
            /** @var SeoRenderContract $renderer */
            $renderer = app(SeoRenderContract::class);
            return $renderer->renderSitemap($type, max(1, (int) ($page ?? 1)));
        }

        return $this->sitemapIndex();
    }
}
