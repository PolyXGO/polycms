<?php

/**
 * FlexiWhite Configuration
 *
 * This file defines theme-level configuration that is:
 * - Loaded automatically when the theme is active
 * - Accessible via config('theme.key') in PHP
 * - NOT user-editable (for user settings, use SettingsService)
 *
 * Use this for structural theme metadata (menu locations, widget areas,
 * supported features, color palette definitions, layout options, etc.)
 *
 * For user-configurable settings (like "primary color" or "show sidebar"),
 * use the theme_get_option() / get_theme_mod() helper functions instead.
 */

return [

    // ┌──────────────────────────────────────────────────────┐
    // │ Menu Locations                                       │
    // │ Define named menu slots that users can assign menus  │
    // │ to in admin → Appearance → Menus                     │
    // └──────────────────────────────────────────────────────┘
    'menus' => [
        'primary' => 'Primary Navigation',    // Main horizontal nav
        'footer'  => 'Footer Menu',           // Footer links
        'mobile'  => 'Mobile Menu',           // Mobile-specific nav
    ],

    // ┌──────────────────────────────────────────────────────┐
    // │ Widget Areas                                         │
    // │ Static reference of widget area keys (the actual     │
    // │ registration happens in functions.php via hook)       │
    // └──────────────────────────────────────────────────────┘
    'widget_areas' => [
        'sidebar_primary' => 'Primary Sidebar',
        'sidebar_blog'    => 'Blog Sidebar',
        'sidebar_shop'    => 'Shop Sidebar',
        'footer_col_1'    => 'Footer Column 1',
        'footer_col_2'    => 'Footer Column 2',
        'footer_col_3'    => 'Footer Column 3',
    ],

    // ┌──────────────────────────────────────────────────────┐
    // │ Theme Support Features                               │
    // │ Declare which PolyCMS features this theme supports   │
    // └──────────────────────────────────────────────────────┘
    'supports' => [
        'post-thumbnails' => true,   // Featured image support
        'custom-logo'     => true,   // Custom logo upload
        'menus'           => true,   // Navigation menu system
        'widgets'         => true,   // Widget system
        'dark-mode'       => true,   // Dark mode toggle
        'block-editor'    => true,   // TipTap block editor
        'products'        => true,   // E-commerce product display
        'multi-language'  => true,   // i18n/RTL support
    ],

    // ┌──────────────────────────────────────────────────────┐
    // │ Color Palette                                        │
    // │ Default color definitions for the theme              │
    // │ These can be overridden by user via Theme Options     │
    // └──────────────────────────────────────────────────────┘
    'colors' => [
        'primary'    => '#4f46e5',  // Indigo-600
        'secondary'  => '#64748b',  // Slate-500
        'accent'     => '#f59e0b',  // Amber-500
        'success'    => '#10b981',  // Emerald-500
        'danger'     => '#ef4444',  // Red-500
        'background' => '#ffffff',  // White
        'text'       => '#1e293b',  // Slate-800
    ],

    // ┌──────────────────────────────────────────────────────┐
    // │ Typography                                           │
    // │ Default font families (loaded via Google Fonts or    │
    // │ local assets in the Blade layout)                     │
    // └──────────────────────────────────────────────────────┘
    'typography' => [
        'heading_font' => 'Inter',
        'body_font'    => 'Inter',
        'base_size'    => '16px',
    ],

    // ┌──────────────────────────────────────────────────────┐
    // │ Layout Options                                       │
    // │ Structural layout settings                           │
    // └──────────────────────────────────────────────────────┘
    'layout' => [
        'container_width' => '1280px',   // Max content width
        'sidebar_position' => 'right',   // 'left', 'right', or 'none'
        'footer_columns'   => 3,         // Number of footer columns
    ],

    // ┌──────────────────────────────────────────────────────┐
    // │ Social Links                                         │
    // │ Default social media link slots                      │
    // └──────────────────────────────────────────────────────┘
    'social' => [
        'facebook'  => null,
        'twitter'   => null,
        'instagram' => null,
        'youtube'   => null,
        'linkedin'  => null,
        'github'    => null,
    ],
];
