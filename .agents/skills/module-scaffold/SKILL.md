---
name: module-scaffold
description: Scaffold a new PolyCMS module with standard directory structure, ServiceProvider, module.json, admin routes, and commercial license server verification integration.
---

# Module Scaffold Skill

## Purpose
This skill guides the creation of a new PolyCMS module with standard architecture, following PolyCMS conventions, Vue 3 path aliases, and optional commercial license server verification.

## Standard Directory Structure

```
modules/{Vendor}/{ModuleName}/
├── module.json                    # Module manifest (required)
├── README.md                      # Module documentation
├── src/
│   ├── {ModuleName}ServiceProvider.php  # Main service provider (required)
│   └── Controllers/               # Controllers
│       └── Api/V1/SettingsController.php
├── resources/
│   └── admin/                     # Admin Vue components
│       └── views/
│           └── Settings.vue
└── database/
    └── migrations/
```

## Commercial License Server Verification Pattern

For commercial modules requiring license key activation & remote site domain verification:

1. **Default Settings in ServiceProvider**:
   ```php
   $service->registerDefaults([
       '{module_snake}_license_key'              => '',
       '{module_snake}_license_status'           => 'unlicensed',
       '{module_snake}_license_activated_domain' => '',
       '{module_snake}_license_activated_at'     => '',
       '{module_snake}_license_server_url'       => env(strtoupper('{module_snake}') . '_LICENSE_SERVER', 'https://headrandom.com'),
   ]);
   ```

2. **Dual-Mode License Verification in SettingsController**:
   - **Local Monolith Mode**: When installed on the seller site (`class_exists(\App\Services\Ecommerce\LicenseManager::class)`), verify directly against local database models.
   - **Remote Client Mode**: When installed on a standalone client site (`yourdomain.com`), call home to the seller server via HTTP API (`Http::post`):
     - `POST {server_url}/api/v1/licenses/activate`
     - `POST {server_url}/api/v1/licenses/verify`
     - `POST {server_url}/api/v1/licenses/deactivate`
   - **API Payload**: Pass `license_key` and `domain` (`$request->getHost()`).
