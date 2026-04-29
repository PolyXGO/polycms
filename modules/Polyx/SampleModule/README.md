# Sample Module — Developer Reference

> **PolyCMS Module Development Guide**
> Version 2.0.0 — Comprehensive reference for building custom modules.

## Overview

This module serves as the **definitive developer reference** for PolyCMS module development. It demonstrates all available hooks, filters, patterns, and conventions with detailed comments in every file.

### What's Included

| Feature | File | Description |
|---------|------|-------------|
| **Service Provider** | `src/SampleModuleServiceProvider.php` | Main entry point with 12 hook/filter categories |
| **Admin Menu** | via `admin.menu.build` hook | Sidebar menu with 3 sub-items |
| **Permissions** | via `roles.register_permissions` hook | 4 custom permissions |
| **CRUD API** | `src/Controllers/Api/V1/NoteController.php` | Full REST API (list, create, read, update, delete) |
| **Settings** | `src/Controllers/Api/V1/SettingsController.php` | Database-backed settings via SettingsService |
| **Model** | `src/Models/SampleNote.php` | Eloquent model with scopes, casts, relationships |
| **Migration** | `src/database/migrations/` | Auto-running database migration |
| **Vue Dashboard** | `resources/admin/views/Dashboard.vue` | Hook/filter reference table |
| **Vue CRUD** | `resources/admin/views/notes/` | List + Create/Edit forms |
| **Vue Settings** | `resources/admin/views/Settings.vue` | Module settings page |
| **Vue Routes** | `resources/admin/routes.ts` | 5 admin routes with lazy loading |

## Module Structure

```
modules/Polyx/SampleModule/
├── module.json                              # Module manifest (required)
├── README.md                                # This file
├── src/
│   ├── SampleModuleServiceProvider.php      # Main provider (required)
│   ├── Controllers/
│   │   └── Api/V1/
│   │       ├── SettingsController.php       # Settings API
│   │       └── NoteController.php           # CRUD API
│   ├── Models/
│   │   └── SampleNote.php                   # Eloquent model
│   └── database/
│       └── migrations/
│           └── 2026_04_26_..._table.php     # Migration
└── resources/
    └── admin/
        ├── routes.ts                        # Vue router config
        └── views/
            ├── Dashboard.vue                # Module dashboard
            ├── Settings.vue                 # Settings page
            └── notes/
                ├── Index.vue                # List view
                └── Form.vue                 # Create/Edit form
```

## Hooks & Filters Reference

### Action Hooks Used

| Hook | Priority | Description |
|------|----------|-------------|
| `admin.menu.build` | 10 | Register sidebar menu items |
| `roles.register_permissions` | 10 | Register module permissions |
| `widgets.register_areas` | 10 | Register widget areas |
| `register_email_templates` | 10 | Register email templates |
| `layout.register_assets` | 10 | Inject CSS/JS into frontend |
| `media.uploaded` | 20 | Log media upload events |
| `cart.item.added` | 20 | Log cart events |
| `product.saved` | 20 | Log product save events |
| `post.saved` | 20 | Log post save events |
| `sample_module.note.created` | — | Custom hook (fired by this module) |
| `sample_module.note.deleted` | — | Custom hook (fired by this module) |

### Filter Hooks Used

| Hook | Priority | Description |
|------|----------|-------------|
| `post.content.render` | 10 | Add reading time badge to posts |
| `content.render.blocks` | 99 | Debug-log block types (debug only) |
| `theme.view.data` | 20 | Inject module data into theme views |
| `settings.defaults` | 10 | Register default setting values |
| `topbar.menu.items` | 10 | Add topbar menu item (debug only) |

### Commented-Out Demos (Ready to Enable)

| Hook | Purpose |
|------|---------|
| `media.url` | CDN URL rewriting |
| `cart.totals` | Add handling fee to cart |
| `seo.canonical_url` | Force HTTPS on canonical URLs |

## Quick Start: Create Your Own Module

### Step 1: Create Module Directory
```
modules/Polyx/YourModule/
├── module.json
├── src/
│   └── YourModuleServiceProvider.php
└── resources/
    └── admin/
        ├── routes.ts
        └── views/
```

### Step 2: module.json
```json
{
  "name": "Your Module",
  "vendor": "Polyx",
  "version": "1.0.0",
  "description": "Description of your module",
  "provider": "Modules\\Polyx\\YourModule\\YourModuleServiceProvider",
  "autoload": {
    "psr-4": {
      "Modules\\Polyx\\YourModule\\": "src/"
    }
  }
}
```

### Step 3: ServiceProvider (Minimum)
```php
<?php
namespace Modules\Polyx\YourModule;

use App\Facades\Hook;
use App\Services\MenuRegistry;
use Illuminate\Support\ServiceProvider;

class YourModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register admin menu
        Hook::addAction('admin.menu.build', function () {
            app(MenuRegistry::class)->register('your-module', [
                'key'   => 'your-module',
                'label' => 'Your Module',
                'icon'  => 'M12 6v6m0 0v6m0-6h6m-6 0H6', // plus icon
                'order' => 100,
                'children' => [],
            ]);
        });
    }
}
```

### Step 4: Enable Module
Add to `config/modules.php`:
```php
'enabled' => [
    'Polyx.YourModule',
],
```

### Step 5: Add Vue Routes (Optional)
Create `resources/admin/routes.ts`:
```typescript
import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
    {
        path: 'your-module',
        name: 'admin.your-module.index',
        component: () => import('./views/Index.vue'),
    },
];

export default routes;
```

## API Endpoints

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/api/v1/sample-module/settings` | Get module settings |
| `PUT` | `/api/v1/sample-module/settings` | Update module settings |
| `GET` | `/api/v1/sample-module/notes` | List notes (paginated) |
| `POST` | `/api/v1/sample-module/notes` | Create a note |
| `GET` | `/api/v1/sample-module/notes/{id}` | Get a note |
| `PUT` | `/api/v1/sample-module/notes/{id}` | Update a note |
| `DELETE` | `/api/v1/sample-module/notes/{id}` | Delete a note |

## License

MIT — Part of PolyCMS Community Edition.
