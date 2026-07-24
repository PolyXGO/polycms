<?php

declare(strict_types=1);

namespace App\Contracts;

use Symfony\Component\HttpFoundation\Response;

interface SeoRenderContract
{
    /**
     * Render SEO tags for <head> output.
     */
    public function renderHead(): string;

    /**
     * Render robots.txt response.
     */
    public function renderRobotsTxt(): Response;

    /**
     * Render sitemap index response.
     */
    public function renderSitemapIndex(): Response;

    /**
     * Render sitemap response for a provider type and page.
     */
    public function renderSitemap(?string $type = null, int $page = 1): Response;
}
