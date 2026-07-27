<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheGenerationStore
{
    protected string $prefix = 'polycms:gen:';

    /**
     * Get current generation token for an entity.
     */
    public function get(string $entityType, string|int $entityId = 'global'): int
    {
        $key = $this->prefix . $entityType . ':' . $entityId;
        return (int) Cache::get($key, 1);
    }

    /**
     * Increment (bump) generation token for an entity to invalidate all its cached pages.
     */
    public function bump(string $entityType, string|int $entityId = 'global'): int
    {
        $key = $this->prefix . $entityType . ':' . $entityId;
        $current = $this->get($entityType, $entityId);
        $newGen = $current + 1;
        Cache::forever($key, $newGen);

        // Also bump global site generation
        if ($entityType !== 'site') {
            $this->bump('site', 'global');
        }

        return $newGen;
    }

    /**
     * Resolve generation token for a given HTTP request.
     */
    public function getForRequest(Request $request): int
    {
        $siteGen = $this->get('site', 'global');

        $route = $request->route();
        if (!$route) {
            return $siteGen;
        }

        $routeName = $route->getName() ?? '';
        $parameters = $route->parameters();
        $entityType = 'page';
        $entityId = 'global';

        // Resolve entity type from route name (reliable) instead of path string (fragile)
        if (str_starts_with($routeName, 'products.') || str_starts_with($routeName, 'product-categories.') || str_starts_with($routeName, 'product-brands.')) {
            $entityType = 'product';
            $entityId = is_object($parameters['product'] ?? null) ? $parameters['product']->id : ($parameters['id'] ?? $parameters['slug'] ?? 'global');
        } elseif (str_starts_with($routeName, 'posts.') || str_starts_with($routeName, 'post.') || $routeName === 'theme.flexidocs.show') {
            $entityType = 'post';
            $entityId = is_object($parameters['post'] ?? null) ? $parameters['post']->id : ($parameters['id'] ?? $parameters['slug'] ?? $parameters['postSlug'] ?? 'global');
        } elseif (str_starts_with($routeName, 'categories.') || str_starts_with($routeName, 'category.') || str_starts_with($routeName, 'product-tags.') || $routeName === 'theme.flexidocs.category') {
            $entityType = 'category';
            $entityId = is_object($parameters['category'] ?? null) ? $parameters['category']->id : ($parameters['slug'] ?? $parameters['id'] ?? 'global');
        } elseif (str_starts_with($routeName, 'projects.') || str_starts_with($routeName, 'project.')) {
            $entityType = 'project';
            $entityId = is_object($parameters['project'] ?? null) ? $parameters['project']->id : ($parameters['id'] ?? $parameters['slug'] ?? 'global');
        } elseif (str_starts_with($routeName, 'pages.') || str_starts_with($routeName, 'page.')) {
            $entityType = 'page';
            $entityId = is_object($parameters['page'] ?? null) ? $parameters['page']->id : ($parameters['slug'] ?? $parameters['id'] ?? 'global');
        }

        $entityGen = $this->get($entityType, $entityId);

        return $siteGen + $entityGen;
    }
}
