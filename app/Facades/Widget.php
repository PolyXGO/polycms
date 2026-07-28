<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $type, string $class, array $config = [])
 * @method static array|null getWidget(string $type)
 * @method static array getWidgets()
 * @method static array getWidgetsByCategory(string $category)
 * @method static string render(\App\Models\WidgetInstance $instance)
 * @method static string renderArea(string $areaKey)
 * @method static bool has(string $type)
 *
 * @see \App\Services\WidgetManager
 */
class Widget extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'widget';
    }
}
