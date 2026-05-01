# Banner Slider Module

Banner slider management module for displaying promotional banners in topbar area with scheduling, ordering, and responsive slider support.

## Features

- **Banner Management**: Full CRUD operations for banners
- **Image Upload**: Support for banner images via Media Library
- **Scheduling**: Start and end dates for banner display
- **Ordering**: Drag-and-drop or manual order control
- **Visibility**: Active/inactive toggle
- **Frontend Display**: Slider component in topbar area
- **Responsive**: Mobile-friendly slider design
- **Hook Integration**: Uses PolyCMS hooks for frontend rendering

## Module Structure

```
modules/Polyx/BannerSlider/
├── module.json                    # Module manifest
├── README.md                      # This file
├── src/
│   ├── BannerSliderServiceProvider.php  # Main service provider
│   ├── Models/
│   │   └── BannerSlider.php       # Banner model
│   ├── Controllers/
│   │   └── BannerController.php   # Banner CRUD controller
│   ├── Http/
│   │   ├── Requests/
│   │   │   ├── StoreBannerRequest.php
│   │   │   └── UpdateBannerRequest.php
│   │   └── Resources/
│   │       └── BannerResource.php
│   └── Services/
│       └── BannerService.php      # Banner service for frontend
├── resources/
│   ├── admin/
│   │   ├── routes.ts
│   │   └── views/
│   │       ├── Banners.vue
│   │       ├── BannerForm.vue
│   │       └── Settings.vue
│   └── views/
│       └── partials/
│           └── banner-slider.blade.php
└── database/
    └── migrations/
        └── create_banner_sliders_table.php
```

## How It Works

### 1. Module Registration

The module is discovered automatically by the ModuleManager when placed in the `modules/` directory. The `module.json` file contains metadata about the module.

### 2. Service Provider

The `BannerSliderServiceProvider` class:
- Registers hooks in the `boot()` method
- Adds menu items to the admin sidebar (Banner Slider > Banners, Settings)
- Registers frontend hook filter `frontend.topbar.banners`
- Registers routes for admin and API endpoints

### 3. Menu Registration

Uses the `admin.menu.build` action hook to register menu items:

```php
Hook::addAction('admin.menu.build', function () {
    $menuRegistry = app(MenuRegistry::class);
    $menuRegistry->register('banner-slider', [
        'key' => 'banner-slider',
        'label' => 'Banner Slider',
        'children' => [
            ['key' => 'banner-slider-banners', 'label' => 'Banners'],
            ['key' => 'banner-slider-settings', 'label' => 'Settings'],
        ],
    ]);
}, 10);
```

### 4. Frontend Integration

Themes can display banners using the hook filter:

```php
use App\Facades\Hook;

$banners = Hook::applyFilters('frontend.topbar.banners', []);

foreach ($banners as $banner) {
    // Display banner
}
```

## Usage

1. Enable the module in the Modules page (`/admin/modules`)
2. Go to Banner Slider > Banners to manage banners
3. Create banners with images, links, and scheduling
4. Themes can display banners using the `frontend.topbar.banners` hook

## API Endpoints

- `GET /api/v1/banner-slider/banners` - List banners
- `GET /api/v1/banner-slider/banners/{id}` - Get single banner
- `POST /api/v1/banner-slider/banners` - Create banner
- `PUT /api/v1/banner-slider/banners/{id}` - Update banner
- `DELETE /api/v1/banner-slider/banners/{id}` - Delete banner
- `POST /api/v1/banner-slider/banners/reorder` - Reorder banners

## Hooks Used

- `admin.menu.build` - Action hook for registering admin menu items
- `frontend.topbar.banners` - Filter hook for frontend banner data

## Development

To customize this module:

1. Modify the ServiceProvider to add/remove hooks
2. Add controllers in `src/Controllers/`
3. Add Vue components in `resources/admin/views/`
4. Update README.md with your changes

## Notes

- Banners use Media Library for images (relationship with Media model)
- Frontend hook allows themes to customize banner display
- Scheduling logic: Banner is active if current date is between start_date and end_date (or no dates set)
- Order field controls display order (ascending)
- Always use hooks for integration, never modify core files directly
- Follow PolyCMS coding standards
