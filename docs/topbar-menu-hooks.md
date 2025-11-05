# Topbar Menu Hooks

PolyCMS provides a comprehensive hook system for extending the topbar menu (similar to WordPress admin bar) that appears on both frontend and admin pages.

## Available Hooks

### Filter Hooks

#### `topbar.menu.items`
Filter the menu items before they are displayed. This allows modules and themes to add, modify, or remove menu items.

**Parameters:**
- `array $items` - Current menu items
- `Request $request` - Current HTTP request
- `User $user` - Current authenticated user

**Return:** Array of menu items

**Example:**
```php
use App\Facades\Hook;

Hook::addFilter('topbar.menu.items', function ($items, $request, $user) {
    // Add a custom menu item
    $items[] = [
        'id' => 'my-custom-item',
        'label' => 'My Custom Link',
        'url' => url('/my-custom-page'),
        'icon' => 'custom',
        'priority' => 50,
        'group' => 'left',
    ];
    
    return $items;
}, 10);
```

#### `topbar.menu.should_show`
Filter whether the topbar should be displayed.

**Parameters:**
- `bool $shouldShow` - Whether to show the topbar (default: true if user is authenticated)
- `User $user` - Current authenticated user

**Return:** Boolean

**Example:**
```php
Hook::addFilter('topbar.menu.should_show', function ($shouldShow, $user) {
    // Hide topbar for specific user role
    if ($user->hasRole('guest')) {
        return false;
    }
    
    return $shouldShow;
}, 10);
```

### Action Hooks

#### `topbar.menu.context`
Action hook fired when building context-aware menu items (like edit links). This allows modules to add context-specific menu items.

**Parameters:**
- `array &$items` - Menu items array (passed by reference)
- `Request $request` - Current HTTP request
- `User $user` - Current authenticated user

**Example:**
```php
Hook::addAction('topbar.menu.context', function ($items, $request, $user) {
    $routeName = $request->route()->getName();
    
    // Add edit link for custom post type
    if ($routeName === 'my-custom-post.show') {
        $slug = $request->route('slug');
        $post = MyCustomPost::where('slug', $slug)->first();
        
        if ($post && $user->can('update', $post)) {
            $items[] = [
                'id' => 'edit-my-custom-post',
                'label' => 'Edit Custom Post',
                'url' => url('/admin/my-custom-posts/' . $post->id . '/edit'),
                'icon' => 'pencil',
                'priority' => 5,
                'group' => 'left',
                'highlight' => true,
            ];
        }
    }
}, 10);
```

## Menu Item Structure

Each menu item should have the following structure:

```php
[
    'id' => 'unique-item-id',           // Required: Unique identifier
    'label' => 'Menu Label',             // Required: Display text
    'url' => 'https://example.com',      // Required: Link URL (or '#' for dropdowns)
    'icon' => 'dashboard',               // Optional: Icon name (see icon list)
    'priority' => 10,                     // Optional: Sort order (lower = earlier)
    'group' => 'left',                   // Optional: 'left' or 'right' (default: 'left')
    'highlight' => false,                // Optional: Highlight this item (for edit links)
    'children' => [],                    // Optional: Array of child items for dropdowns
    'method' => 'GET',                   // Optional: 'GET' or 'POST' (for forms)
]
```

## Available Icons

- `dashboard` - Dashboard icon
- `plus` - Plus/add icon
- `pencil` - Edit/pencil icon
- `paint-brush` - Customize/paint brush icon
- `user-circle` - User circle icon
- `user` - User icon
- `logout` - Logout icon
- `document` - Document icon
- `page` - Page icon
- `product` - Product icon

## Usage in Modules

Modules can register topbar menu items in their service provider:

```php
<?php

namespace Modules\MyModule;

use App\Facades\Hook;
use Illuminate\Support\ServiceProvider;

class MyModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Add menu item via filter
        Hook::addFilter('topbar.menu.items', function ($items, $request, $user) {
            if ($user->can('access-my-module')) {
                $items[] = [
                    'id' => 'my-module-settings',
                    'label' => 'My Module',
                    'url' => url('/admin/my-module/settings'),
                    'icon' => 'settings',
                    'priority' => 25,
                    'group' => 'left',
                ];
            }
            
            return $items;
        }, 10);
        
        // Add context-aware edit link
        Hook::addAction('topbar.menu.context', function ($items, $request, $user) {
            $routeName = $request->route()->getName();
            
            if ($routeName === 'my-module.item.show') {
                $id = $request->route('id');
                $item = MyModuleItem::find($id);
                
                if ($item && $user->can('update', $item)) {
                    $items[] = [
                        'id' => 'edit-my-module-item',
                        'label' => 'Edit Item',
                        'url' => url('/admin/my-module/items/' . $item->id . '/edit'),
                        'icon' => 'pencil',
                        'priority' => 5,
                        'group' => 'left',
                        'highlight' => true,
                    ];
                }
            }
        }, 10);
    }
}
```

## Usage in Themes

Themes can also add menu items in their service provider or functions.php:

```php
// In theme service provider or functions.php
Hook::addFilter('topbar.menu.items', function ($items, $request, $user) {
    // Add theme-specific menu items
    $items[] = [
        'id' => 'theme-customizer',
        'label' => 'Customize Theme',
        'url' => url('/admin/themes/customize'),
        'icon' => 'paint-brush',
        'priority' => 30,
        'group' => 'left',
    ];
    
    return $items;
}, 10);
```

## Best Practices

1. **Check permissions**: Always check user permissions before adding menu items
2. **Use appropriate priorities**: Lower numbers appear first (left to right)
3. **Group logically**: Use 'left' for main actions, 'right' for user-related items
4. **Highlight important items**: Use `highlight => true` for context-aware edit links
5. **Use unique IDs**: Ensure menu item IDs are unique to avoid conflicts
6. **Document your hooks**: If creating custom hooks, document them in your module/theme documentation

## Examples

### Adding a Dropdown Menu
```php
Hook::addFilter('topbar.menu.items', function ($items, $request, $user) {
    $items[] = [
        'id' => 'my-dropdown',
        'label' => 'My Tools',
        'url' => '#',
        'icon' => 'tools',
        'priority' => 20,
        'group' => 'left',
        'children' => [
            [
                'id' => 'tool-1',
                'label' => 'Tool 1',
                'url' => url('/admin/tools/tool-1'),
                'icon' => 'tool',
            ],
            [
                'id' => 'tool-2',
                'label' => 'Tool 2',
                'url' => url('/admin/tools/tool-2'),
                'icon' => 'tool',
            ],
        ],
    ];
    
    return $items;
}, 10);
```

### Adding a POST Form Item
```php
Hook::addFilter('topbar.menu.items', function ($items, $request, $user) {
    $items[] = [
        'id' => 'clear-cache',
        'label' => 'Clear Cache',
        'url' => url('/admin/tools/clear-cache'),
        'icon' => 'refresh',
        'priority' => 30,
        'group' => 'left',
        'method' => 'POST',
    ];
    
    return $items;
}, 10);
```

