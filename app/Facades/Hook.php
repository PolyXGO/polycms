<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void addAction(string $hookName, callable|string $callback, int $priority = 10)
 * @method static void doAction(string $hookName, mixed ...$args)
 * @method static void addFilter(string $hookName, callable|string $callback, int $priority = 10)
 * @method static mixed applyFilters(string $hookName, mixed $value, mixed ...$args)
 * @method static bool removeAction(string $hookName, callable|string|null $callback = null)
 * @method static bool removeFilter(string $hookName, callable|string|null $callback = null)
 * @method static bool hasAction(string $hookName)
 * @method static bool hasFilter(string $hookName)
 * 
 * @see \App\Services\HookManager
 */
class Hook extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'hook';
    }
}
