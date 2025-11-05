# Theme Development Guide

This guide explains how to develop themes for PolyCMS, similar to WordPress theme development.

## Theme Structure

A PolyCMS theme must follow this directory structure:

```
themes/
└── your-theme-slug/
    ├── theme.json          # Theme manifest (required)
    ├── screenshot.png      # Theme screenshot (optional)
    ├── functions.php       # Theme functions (optional)
    ├── resources/
    │   └── views/          # Blade templates
    │       ├── layouts/
    │       │   └── app.blade.php
    │       ├── partials/
    │       └── ...
    ├── public/             # Public assets (CSS, JS, images)
    │   ├── css/
    │   ├── js/
    │   └── images/
    └── config/
        └── theme.php       # Theme configuration (optional)
```

## Theme Manifest (theme.json)

The `theme.json` file is required and contains theme metadata:

```json
{
    "name": "My Awesome Theme",
    "slug": "my-awesome-theme",
    "version": "1.0.0",
    "author": "Your Name",
    "description": "A beautiful theme for PolyCMS",
    "type": "frontend",
    "requires": {
        "polycms": ">=1.0.0"
    }
}
```

### Required Fields

- `name`: Theme display name
- `slug`: Unique theme identifier (must match directory name)

### Optional Fields

- `version`: Theme version (default: "1.0.0")
- `author`: Theme author name
- `description`: Theme description
- `type`: Theme type - "frontend" or "admin" (default: "frontend")
- `requires`: Minimum PolyCMS version required

## View System

### View Resolution Priority

PolyCMS resolves views in this order:

1. **Theme views** (`themes/{slug}/resources/views/`)
2. **Module views** (if applicable)
3. **Default views** (`resources/views/`)

### Creating Views

#### Layout Template

Create a main layout in `resources/views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PolyCMS') }}</title>
    
    <!-- Theme Assets -->
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}">
</head>
<body>
    @include('partials.header')
    
    <main>
        @yield('content')
    </main>
    
    @include('partials.footer')
    
    <script src="{{ theme_asset('js/main.js') }}"></script>
</body>
</html>
```

#### Page Templates

Create page templates in `resources/views/`:

```blade
@extends('layouts.app')

@section('content')
    <article>
        <h1>{{ $post->title }}</h1>
        <div class="content">
            {!! $post->content_html !!}
        </div>
    </article>
@endsection
```

### Available Views

Core views that themes can override:

- `layouts/app.blade.php` - Main layout
- `posts/index.blade.php` - Posts listing
- `posts/show.blade.php` - Single post
- `products/index.blade.php` - Products listing
- `products/show.blade.php` - Single product
- `categories/show.blade.php` - Category archive
- `pages/show.blade.php` - Static page

## Theme Functions

Create a `functions.php` file to add theme-specific functionality:

```php
<?php

use App\Facades\Hook;

// Register theme hooks
Hook::addAction('theme.activated', function ($theme) {
    if ($theme->slug === 'my-theme') {
        // Initialize theme settings
    }
});

// Add theme support
function my_theme_support() {
    // Add theme features
}
add_action('theme.init', 'my_theme_support');

// Helper function
function get_theme_mod($key, $default = null) {
    // Get theme modification
    return $default;
}
```

## Assets

### CSS/JS Files

Place your assets in `public/` directory:

- `public/css/style.css`
- `public/js/main.js`
- `public/images/logo.png`

### Loading Assets

Use the `theme_asset()` helper function:

```blade
<link rel="stylesheet" href="{{ theme_asset('css/style.css') }}">
<script src="{{ theme_asset('js/main.js') }}"></script>
<img src="{{ theme_asset('images/logo.png') }}" alt="Logo">
```

The `theme_asset()` function automatically resolves to:
- Active theme assets: `/themes/{slug}/css/style.css`
- Falls back to default assets if no theme is active

## Theme Configuration

Create a `config/theme.php` file for theme-specific settings:

```php
<?php

return [
    'menus' => [
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu',
    ],
    'widget_areas' => [
        'sidebar' => 'Sidebar',
        'footer' => 'Footer',
    ],
];
```

## Hooks Integration

See [Theme Hooks Documentation](./theme-hooks.md) for available hooks.

### Example: Adding Custom Menu

```php
Hook::addFilter('theme.menu.items', function ($items, $menuName) {
    if ($menuName === 'primary') {
        // Modify menu items
        $items[] = [
            'label' => 'Custom Link',
            'url' => '/custom',
        ];
    }
    return $items;
});
```

## Widget Areas

Register widget areas in your theme:

```php
// In functions.php or service provider
Hook::addAction('theme.init', function () {
    \App\Facades\Widget::registerArea('sidebar', [
        'label' => 'Sidebar',
        'description' => 'Main sidebar widget area',
    ]);
    
    \App\Facades\Widget::registerArea('footer', [
        'label' => 'Footer',
        'description' => 'Footer widget area',
    ]);
});
```

Display widgets in your views:

```blade
<div class="sidebar">
    {!! \App\Facades\Widget::renderArea('sidebar') !!}
</div>
```

## Best Practices

1. **Follow naming conventions**: Use lowercase, hyphens for slugs
2. **Version your theme**: Keep `theme.json` version updated
3. **Document your theme**: Include README.md with setup instructions
4. **Test compatibility**: Test with different PolyCMS versions
5. **Use hooks**: Don't modify core files, use hooks instead
6. **Optimize assets**: Minify CSS/JS for production
7. **Responsive design**: Ensure mobile compatibility
8. **Security**: Escape all output, validate inputs

## Theme Packaging

To create a theme package:

1. Create your theme directory structure
2. Ensure `theme.json` is valid
3. Include a screenshot (1200x900px recommended)
4. Test the theme
5. Create a ZIP file of the theme directory

The ZIP file should have this structure:

```
my-theme.zip
└── my-theme/
    ├── theme.json
    ├── screenshot.png
    ├── resources/
    └── public/
```

## Uploading Themes

Users can upload themes via:

1. **Admin Panel**: Appearance > Themes > Upload Theme
2. **Manual Upload**: Extract ZIP to `themes/` directory

After upload, themes must be synced via "Sync Themes" button.

## Example Theme

See `themes/default/` (if exists) for a complete example theme structure.

## Troubleshooting

### Theme not appearing

1. Check `theme.json` is valid JSON
2. Ensure `slug` matches directory name
3. Run "Sync Themes" in admin panel
4. Check file permissions

### Views not loading

1. Verify view files exist in `resources/views/`
2. Check view names match core expectations
3. Clear view cache: `php artisan view:clear`

### Assets not loading

1. Verify assets are in `public/` directory
2. Check file permissions
3. Ensure `theme_asset()` helper is used
4. Check public storage symlink

## Resources

- [Theme Hooks Documentation](./theme-hooks.md)
- [PolyCMS Architecture](./polyclms-architecture.md)
- [Laravel Blade Documentation](https://laravel.com/docs/blade)

