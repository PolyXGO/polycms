<?php

if (!function_exists('poly_widgets')) {
    function poly_widgets(string $areaKey): string
    {
        return app('widget')->renderArea($areaKey);
    }
}

if (!function_exists('poly_widget_area_exists')) {
    function poly_widget_area_exists(string $areaKey): bool
    {
        return \App\Models\WidgetArea::where('key', $areaKey)->exists();
    }
}
