# FlexiWhite — Developer Reference

> **PolyCMS Theme Development Guide**
> Version 2.0.0 — Comprehensive reference for building custom themes.

## Overview

This theme serves as the **definitive developer reference** for PolyCMS theme development. Every file contains detailed comments explaining the hooks, filters, and Blade conventions available.

## Theme Structure

```
themes/flexiwhite/
├── theme.json                           # Theme manifest (required)
├── screenshot.png                       # Theme preview image (required)
├── README.md                            # This file
├── functions.php                        # Hook/filter registration + helpers (required)
├── config/
│   └── theme.php                        # Theme configuration
├── lang/                                # Translations (optional)
├── public/                              # Static assets (CSS, JS, images)
└── resources/
    └── views/
        ├── home.blade.php               # Homepage template
        ├── layouts/
        │   └── app.blade.php            # Main layout wrapper
        ├── partials/
        │   ├── header.blade.php         # Site header + navigation
        │   ├── footer.blade.php         # Site footer
        │   ├── menu-item.blade.php      # Desktop menu item
        │   ├── menu-item-mobile.blade.php # Mobile menu item
        │   └── widget-area.blade.php    # Widget area renderer
        ├── posts/
        │   ├── index.blade.php          # Blog listing
        │   └── show.blade.php           # Single post
        ├── pages/
        │   └── show.blade.php           # Single page
        ├── products/
        │   ├── index.blade.php          # Product listing
        │   └── show.blade.php           # Single product
        ├── categories/
        │   └── show.blade.php           # Category archive
        ├── tags/
        │   └── show.blade.php           # Tag archive
        └── authors/
            └── show.blade.php           # Author archive
```

## Hooks & Filters Reference

### Used in `functions.php`

| # | Hook/Filter | Type | Description |
|---|-------------|------|-------------|
| 1 | `theme.activated` | Action | One-time setup on activation |
| 2 | `theme.view.data` | Filter | Inject data into Blade views |
| 3 | `widgets.register_areas` | Action | Register widget areas (12 areas) |
| 4 | `theme.options.resolved` | Filter | Modify theme options |
| 5 | `theme.template.registry` | Filter | Register custom page templates |
| 6 | `theme.template.resolve` | Filter | Override template resolution |
| 7 | `seo.canonical_url` | Filter | Modify canonical URL |
| 8 | `seo.site_favicon` | Filter | Override favicon |
| 9 | `content.render.html` | Filter | Modify rendered content HTML |
| 10 | `layout.register_assets` | Action | Register CSS/JS assets |
| 11 | `post.frontend_url` | Filter | Customize post URLs |
| 12 | `category.frontend_url` | Filter | Customize category URLs |
| 13 | `topbar.menu.items` | Filter | Add topbar menu items |

### Available in Blade Templates

| Variable | Source | Description |
|----------|--------|-------------|
| `$theme_name` | `theme.view.data` | Theme display name |
| `$theme_version` | `theme.view.data` | Theme version |
| `$site_title` | `theme.view.data` | Site title from settings |
| `$tagline` | `theme.view.data` | Site tagline from settings |
| `$current_year` | `theme.view.data` | Current year |
| `$post` | Core controller | Post model (on posts.show) |
| `$posts` | Core controller | Post collection (on posts.index) |
| `$product` | Core controller | Product model (on products.show) |
| `$products` | Core controller | Product collection (on products.index) |
| `$page` | Core controller | Page model (on pages.show) |
| `$category` | Core controller | Category model (on categories.show) |

## Helper Functions

| Function | Description |
|----------|-------------|
| `get_theme_mod($key, $default)` | Get theme setting from database |
| `theme_widget_area_has_content($key)` | Check if widget area has widgets |
| `theme_widget_area_label($key)` | Get widget area display name |
| `theme_get_options($keys)` | Get all theme options |
| `theme_get_option($key, $default)` | Get single theme option |
| `theme_permalink_structure()` | Get full permalink configuration |
| `theme_permalink_segment($group, $ctx)` | Get permalink segment |
| `theme_permalink_url($group, $slug)` | Generate full permalink URL |
| `the_excerpt($post, $length)` | Get post excerpt |
| `format_post_date($date, $format)` | Format date per site settings |

## Quick Start: Create Your Own Theme

### Step 1: Create Theme Directory
```
themes/your-theme/
├── theme.json
├── screenshot.png
├── functions.php
└── resources/
    └── views/
        ├── home.blade.php
        ├── layouts/
        │   └── app.blade.php
        ├── posts/
        │   ├── index.blade.php
        │   └── show.blade.php
        └── pages/
            └── show.blade.php
```

### Step 2: Minimum `theme.json`
```json
{
    "name": "Your Theme",
    "slug": "your-theme",
    "version": "1.0.0",
    "author": "Your Name",
    "description": "A custom theme for PolyCMS",
    "type": "frontend",
    "requires": { "polycms": ">=1.0.0" }
}
```

### Step 3: Minimum `functions.php`
```php
<?php
use App\Facades\Hook;
use App\Services\SettingsService;

Hook::addFilter('theme.view.data', function ($data, $viewName) {
    $data['site_title'] = app(SettingsService::class)->get('site_title', 'My Site');
    return $data;
}, 10, 2);
```

### Step 4: Blade Layout (`layouts/app.blade.php`)
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $site_title ?? 'PolyCMS' }}</title>
</head>
<body>
    @yield('content')
</body>
</html>
```

### Step 5: Activate Theme
Go to admin → Appearance → Themes → Activate "Your Theme"

## Widget Areas

This theme registers 12 widget areas in 3 groups:

| Group | Areas | Order Range |
|-------|-------|-------------|
| Homepage | hero, intro, highlights, showcase, testimonials, cta | 10-60 |
| Sidebars | primary, blog, shop | 110-130 |
| Footer | col_1, col_2, col_3 | 210-230 |

## License

MIT — Part of PolyCMS Community Edition.
