<?php

declare(strict_types=1);

use Modules\Polyx\MTOptimize\Support\MTOptimizeHooks;
use Modules\Polyx\MTOptimize\Support\SchemaPieceRegistry;
use Modules\Polyx\MTOptimize\Support\SitemapProviderRegistry;

if (!function_exists('applyFilters')) {
    function applyFilters(string $hookName, mixed $value, mixed ...$args): mixed
    {
        return MTOptimizeHooks::applyFilters($hookName, $value, ...$args);
    }
}

if (!function_exists('doAction')) {
    function doAction(string $hookName, mixed ...$args): void
    {
        MTOptimizeHooks::doAction($hookName, ...$args);
    }
}

if (!function_exists('registerSchemaPiece')) {
    function registerSchemaPiece(string $key, callable $factory, int $priority = 10): void
    {
        app(SchemaPieceRegistry::class)->register($key, $factory, $priority);
    }
}

if (!function_exists('removeSchemaPiece')) {
    function removeSchemaPiece(string $key): void
    {
        app(SchemaPieceRegistry::class)->remove($key);
    }
}

if (!function_exists('registerSitemapProvider')) {
    function registerSitemapProvider(string $type, callable $provider, int $priority = 10): void
    {
        app(SitemapProviderRegistry::class)->register($type, $provider, $priority);
    }
}

if (!function_exists('removeSitemapProvider')) {
    function removeSitemapProvider(string $type): void
    {
        app(SitemapProviderRegistry::class)->remove($type);
    }
}
