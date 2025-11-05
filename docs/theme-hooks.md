# Theme Hooks & Filters

PolyCMS provides a comprehensive hook system that allows themes to integrate with the core system and extend functionality.

## Available Hooks

### Action Hooks

Actions are hooks that trigger at specific points in the application lifecycle. They don't return values but allow themes to perform actions.

#### `theme.activated`
Triggered when a theme is activated.

```php
Hook::addAction('theme.activated', function ($theme) {
    // $theme is an instance of App\Models\Theme
    // Perform actions when theme is activated
});
```

#### `theme.deactivated`
Triggered when a theme is deactivated.

```php
Hook::addAction('theme.deactivated', function ($theme) {
    // Perform cleanup or actions when theme is deactivated
});
```

#### `theme.view.render`
Triggered before rendering a theme view.

```php
Hook::addAction('theme.view.render', function ($viewName, $data) {
    // Modify data or perform actions before view rendering
});
```

### Filter Hooks

Filters allow themes to modify values before they are used.

#### `theme.view.data`
Filter the data passed to theme views.

```php
Hook::addFilter('theme.view.data', function ($data, $viewName) {
    // Modify $data before it's passed to the view
    $data['custom_variable'] = 'custom_value';
    return $data;
});
```

#### `theme.asset.url`
Filter theme asset URLs.

```php
Hook::addFilter('theme.asset.url', function ($url, $path) {
    // Modify asset URL if needed (e.g., CDN)
    return 'https://cdn.example.com/' . $path;
});
```

#### `theme.menu.items`
Filter menu items for theme rendering.

```php
Hook::addFilter('theme.menu.items', function ($items, $menuName) {
    // Modify menu items before rendering
    return $items;
});
```

## Usage in Themes

### In Theme Service Provider

Themes can register hooks in their service provider:

```php
<?php

namespace Themes\MyTheme;

use App\Facades\Hook;
use Illuminate\Support\ServiceProvider;

class MyThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register theme-specific hooks
        Hook::addAction('theme.activated', function ($theme) {
            if ($theme->slug === 'my-theme') {
                // Initialize theme-specific settings
            }
        });

        Hook::addFilter('theme.view.data', function ($data, $viewName) {
            // Add global variables to all theme views
            $data['theme_settings'] = get_theme_mod('my_setting');
            return $data;
        });
    }
}
```

### In Theme Functions File

If your theme has a `functions.php` file, you can include it in the service provider:

```php
public function boot(): void
{
    $functionsPath = base_path('themes/my-theme/functions.php');
    if (file_exists($functionsPath)) {
        require_once $functionsPath;
    }
}
```

## Best Practices

1. **Always check theme slug** before applying theme-specific hooks:
   ```php
   Hook::addAction('theme.activated', function ($theme) {
       if ($theme->slug !== 'my-theme') {
           return;
       }
       // Theme-specific code
   });
   ```

2. **Use filters for data modification** rather than actions when you need to return values.

3. **Document your hooks** if creating custom hooks in your theme.

4. **Clean up on deactivation** using `theme.deactivated` hook.

## Core Hooks Reference

### Content Rendering
- `content.render.blocks` - Filter blocks before rendering
- `content.render.html` - Filter final HTML output
- `content.render.block.{type}` - Filter specific block type rendering

### Post/Product Hooks
- `post.query.builder` - Filter post query builder
- `post.saved` - Action after post is saved
- `post.deleted` - Action after post is deleted
- `product.query.builder` - Filter product query builder
- `product.saved` - Action after product is saved

### Menu Hooks
- `admin.menu.build` - Action to register admin menu items
- `theme.menu.items` - Filter menu items for frontend rendering

### Widget Hooks
- `widget.register` - Action to register widget types
- `widget.render` - Filter widget output

## Example: Custom Theme Hook

```php
// In your theme's service provider or functions.php

// Register a custom hook for theme-specific functionality
Hook::addAction('theme.init', function () {
    // Initialize theme
});

// Call it from your theme
Hook::doAction('theme.init');
```

