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

        // Check if route has an id parameter (e.g. post, product, category)
        $parameters = $route->parameters();
        $entityType = 'page';
        $entityId = 'global';

        if (isset($parameters['product']) || isset($parameters['id']) && str_contains($request->path(), 'product')) {
            $entityType = 'product';
            $entityId = is_object($parameters['product'] ?? null) ? $parameters['product']->id : ($parameters['id'] ?? 'global');
        } elseif (isset($parameters['post']) || isset($parameters['id']) && str_contains($request->path(), 'post')) {
            $entityType = 'post';
            $entityId = is_object($parameters['post'] ?? null) ? $parameters['post']->id : ($parameters['id'] ?? 'global');
        } elseif (isset($parameters['category']) || isset($parameters['slug']) && str_contains($request->path(), 'category')) {
            $entityType = 'category';
            $entityId = is_object($parameters['category'] ?? null) ? $parameters['category']->id : ($parameters['slug'] ?? 'global');
        }

        $entityGen = $this->get($entityType, $entityId);

        return $siteGen + $entityGen;
    }
}
