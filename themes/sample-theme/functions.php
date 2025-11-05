<?php

/**
 * Sample Theme Functions
 * 
 * This file demonstrates how to add functionality to your theme.
 * You can use hooks, filters, and helper functions here.
 */

use App\Facades\Hook;

// Example: Add custom menu support
Hook::addAction('theme.activated', function ($theme) {
    if ($theme->slug === 'sample-theme') {
        // Initialize theme-specific settings when activated
        // This is where you can set default options, register widget areas, etc.
    }
});

// Example: Add global variables to all views
Hook::addFilter('theme.view.data', function ($data, $viewName) {
    // Add custom variables available in all theme views
    $data['theme_name'] = 'Sample Theme';
    $data['theme_version'] = '1.0.0';
    
    // Example: Get site settings
    $settingsService = app(\App\Services\SettingsService::class);
    $data['site_title'] = $settingsService->get('site_title', 'PolyCMS');
    $data['tagline'] = $settingsService->get('tagline', 'Just another PolyCMS site');
    
    return $data;
});

// Example: Register widget areas when theme is activated
Hook::addAction('theme.activated', function ($theme) {
    if ($theme->slug === 'sample-theme') {
        // Register sidebar widget area
        \App\Models\WidgetArea::firstOrCreate([
            'name' => 'sidebar',
        ], [
            'label' => 'Sidebar',
            'description' => 'Main sidebar widget area',
        ]);
        
        // Register footer widget area
        \App\Models\WidgetArea::firstOrCreate([
            'name' => 'footer',
        ], [
            'label' => 'Footer',
            'description' => 'Footer widget area',
        ]);
    }
});

// Helper function: Get theme modification
function get_theme_mod($key, $default = null)
{
    $settingsService = app(\App\Services\SettingsService::class);
    return $settingsService->get("theme_sample_theme_{$key}", $default);
}

// Helper function: Check if sidebar has widgets
function is_active_sidebar($sidebarName)
{
    $area = \App\Models\WidgetArea::where('name', $sidebarName)->first();
    if (!$area) {
        return false;
    }
    
    return $area->widgetInstances()->count() > 0;
}

// Example: Custom excerpt function
function the_excerpt($post, $length = 55)
{
    if (!empty($post->excerpt)) {
        return $post->excerpt;
    }
    
    $content = strip_tags($post->content_html ?? '');
    if (strlen($content) <= $length) {
        return $content;
    }
    
    return substr($content, 0, $length) . '...';
}

// Example: Format date according to site settings
function format_post_date($date, $format = null)
{
    if (!$date) {
        return '';
    }
    
    $settingsService = app(\App\Services\SettingsService::class);
    $dateFormat = $format ?? $settingsService->get('date_format', 'Y-m-d');
    $timeFormat = $settingsService->get('time_format', 'H:i');
    
    if (is_string($date)) {
        $date = \Carbon\Carbon::parse($date);
    }
    
    return $date->format($dateFormat . ' ' . $timeFormat);
}

