# Sample Module

This is a sample module for PolyCMS that demonstrates how to create and integrate custom modules.

## Features

1. **Sidebar Menu Registration**: Registers a menu item in the admin sidebar with sub-items
2. **Post Content Enhancement**: Adds custom content after post titles using hooks
3. **Settings Page**: Provides a settings page for module configuration
4. **Hook Integration**: Demonstrates how to use PolyCMS hooks and filters system

## Module Structure

```
modules/Polyx/SampleModule/
├── module.json                    # Module manifest
├── src/
│   ├── SampleModuleServiceProvider.php  # Main service provider
│   ├── Controllers/
│   │   └── SettingsController.php       # Settings controller
│   └── README.md                 # This file
└── README.md
```

## How It Works

### 1. Module Registration

The module is discovered automatically by the ModuleManager when placed in the `modules/` directory. The `module.json` file contains metadata about the module.

### 2. Service Provider

The `SampleModuleServiceProvider` class:
- Registers hooks in the `boot()` method
- Adds menu items to the admin sidebar
- Filters post content to add custom content
- Registers routes for the settings page

### 3. Menu Registration

Uses the `admin.menu.build` action hook to register menu items in the MenuRegistry:

```php
Hook::addAction('admin.menu.build', function () {
    $menuRegistry = app(MenuRegistry::class);
    $menuRegistry->register('sample-module', [...]);
});
```

### 4. Content Filtering

Uses the `post.content.render` filter hook to modify post content:

```php
Hook::addFilter('post.content.render', function ($content, $post) {
    // Modify content here
    return $content;
});
```

### 5. Routes

Registers routes in the service provider:

```php
$this->app['router']->middleware(['web', 'auth:sanctum'])
    ->prefix('admin/sample-module')
    ->name('admin.sample-module.')
    ->group(function () {
        // Routes here
    });
```

## Usage

1. Enable the module in the Modules page (`/admin/modules`)
2. Navigate to Sample Module > Settings to configure
3. View any post to see the additional content after the title

## API Endpoints

- `GET /admin/sample-module/settings` - Get module settings
- `POST /admin/sample-module/settings` - Save module settings

## Hooks Used

- `admin.menu.build` - Action hook for registering admin menu items
- `post.content.render` - Filter hook for modifying post content HTML

## Development

To create your own module based on this sample:

1. Copy the module structure
2. Update `module.json` with your module information
3. Update the ServiceProvider namespace and class name
4. Modify hooks and routes as needed
5. Add your own controllers and views

## Notes

- Settings are currently stored in-memory (for demonstration)
- In a real module, you should store settings in the database
- The menu items will appear after enabling the module
- The content filter only affects the API response, not the stored content
