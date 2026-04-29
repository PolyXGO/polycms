<?php

/**
 * FlexiMyTa Theme Configuration
 * 
 * This file contains theme-specific configuration options.
 * You can access these in your views using config('theme.option_name')
 */

return [
    // Menu locations
    'menus' => [
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu',
    ],

    // Widget areas
    'widget_areas' => [
        'sidebar' => 'Sidebar',
        'footer' => 'Footer',
    ],

    // Theme support features
    'supports' => [
        'post-thumbnails' => true,
        'custom-logo' => true,
        'menus' => true,
        'widgets' => true,
    ],

    // Theme colors
    'colors' => [
        'primary' => '#4f46e5',
        'secondary' => '#64748b',
        'accent' => '#f59e0b',
    ],
];
