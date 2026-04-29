<h1 align="center">PolyCMS</h1>

<p align="center">
  <strong>Modern, Modular Content Management System</strong><br>
  Built with Laravel 12 · Vue 3 · Tailwind CSS · GraphQL
</p>

<p align="center">
  <a href="https://github.com/PolyXGO/polycms/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.2+-8892BF.svg" alt="PHP"></a>
  <a href="https://laravel.com/"><img src="https://img.shields.io/badge/Laravel-12-FF2D20.svg" alt="Laravel"></a>
  <a href="https://vuejs.org/"><img src="https://img.shields.io/badge/Vue.js-3-4FC08D.svg" alt="Vue.js"></a>
  <a href="https://www.postgresql.org/"><img src="https://img.shields.io/badge/PostgreSQL-16+-336791.svg" alt="PostgreSQL"></a>
  <a href="https://www.mysql.com/"><img src="https://img.shields.io/badge/MySQL-8+-4479A1.svg" alt="MySQL"></a>
</p>

---

## Overview

**PolyCMS** is a high-performance, open-source content management system designed for modern web applications. It provides a complete ecosystem for content publishing, e-commerce, multi-language support, and extensible module architecture — all powered by a beautiful Vue 3 admin panel.

Whether you're building a blog, a documentation site, an online store, or a multi-tenant SaaS platform, PolyCMS gives you the foundation to ship fast and scale confidently.

---

## ✨ Key Features

### 📝 Content Management
- **Block Editor** — Rich TipTap-based editor with drag-and-drop blocks (Hero, Gallery, CTA, Pricing, Tabs, Accordion, and more)
- **Posts & Pages** — Full CRUD with categories, tags, authors, SEO metadata, and scheduled publishing
- **Media Library** — Centralized asset manager with image optimization, variant generation, and cloud storage support
- **Taxonomy System** — Flexible categories, tags, and custom taxonomies with hierarchical nesting

### 🛒 E-Commerce
- **Product Catalog** — Products with variants, pricing, inventory, and attribute management
- **Payment Gateways** — Built-in PayPal and SePay (QR code for Vietnamese banks) integrations, with extensible gateway API
- **Order Management** — Complete order lifecycle with status tracking and transaction history
- **Multi-Currency** — Dynamic currency switching with live exchange rate support

### 🌐 Internationalization
- **Multi-Language** — Full i18n with per-locale content, URL slugs, and admin interface translation
- **SEO Optimized** — Auto-generated meta tags, Open Graph, JSON-LD structured data, canonical URLs, and sitemap generation
- **Regional Variants** — Support for locale variants (e.g., `en-US`, `vi-VN`) with proper `hreflang` handling

### 🔌 Module System
- **Hot-Pluggable Modules** — Install, activate, and deactivate modules without touching core code
- **Hook & Filter System** — WordPress-inspired action/filter hooks for deep customization
- **GraphQL API** — Auto-discovered schemas per module via Lighthouse
- **REST API** — Sanctum-authenticated `/api/v1` endpoints with rate limiting and token management

### 🎨 Theme System
- **Multi-Theme** — Switch themes instantly with live preview
- **Landing Page Builder** — Visual block-based page construction with pre-built templates
- **Theme Hooks** — `header_scripts`, `footer_scripts`, `after_body_open`, and more for flexible injection
- **Responsive** — All bundled themes are mobile-first and fully responsive

### 🔐 Security & Authentication
- **Laravel Sanctum** — Token-based API authentication with scoped abilities
- **Google 2FA** — TOTP two-factor authentication via Google Authenticator
- **Role & Permission** — Granular RBAC via Spatie Permission with per-module permission registration
- **Cookie Consent** — GDPR/CCPA-compliant consent banner with configurable policies

### ⚙️ Administration
- **Modern Admin Panel** — Vue 3 SPA with dark mode, command palette, and responsive layout
- **Settings Hub** — Centralized settings management with module-contributed setting groups
- **Menu Builder** — Drag-and-drop visual menu editor with nesting support
- **Widget System** — Configurable widget areas with per-theme widget zones
- **Backup & Restore** — Full database and file backup with one-click restore

---

## 🧩 Bundled Modules

| Module | Description |
|--------|-------------|
| **SampleModule** | Developer reference blueprint — demonstrates all available hooks, CRUD, admin pages, and patterns |
| **CookieConsent** | GDPR/CCPA-compliant cookie consent banner |
| **Google2FA** | Google Authenticator TOTP two-factor authentication |
| **PaypalGateway** | PayPal payment gateway for e-commerce |
| **SepayGateway** | SePay QR Code payment gateway (Vietnamese banks) |
| **BannerSlider** | Promotional banner management with scheduling and responsive slider |
| **Backup** | Full backup & restore system with cloud storage integration |

> **Pro Edition** includes additional modules: Accounting, Data Builder (API management & visual reporting), Remote Server Manager, SEO Optimizer, Demo Builder, and more. Visit [polyxgo.com](https://polyxgo.com) for details.

---

## 🛠️ Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel 12 (PHP 8.2+) |
| **Frontend** | Vue.js 3 (Composition API), TypeScript |
| **Styling** | Tailwind CSS 3 |
| **Editor** | TipTap (ProseMirror) |
| **API** | REST (Sanctum) + GraphQL (Lighthouse) |
| **Database** | PostgreSQL 16+ (recommended) / MySQL 8+ |
| **Build** | Vite 7 |
| **State** | Pinia |
| **Auth** | Laravel Sanctum, Spatie Permission |

---

## 📦 Installation

### Requirements

- PHP 8.2+
- Composer 2+
- Node.js 18+ & npm
- PostgreSQL 16+ (recommended) or MySQL 8+

### Quick Start

```bash
# Clone the repository
git clone https://github.com/PolyXGO/polycms.git
cd polycms/polycms

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
# Edit .env with your database credentials
# Tip: Set CACHE_STORE=file for first-time migration
php artisan migrate
php artisan db:seed

# Link storage
php artisan storage:link

# Start development server
npm run dev:all
```

The admin panel is available at `http://localhost:8000/admin`

**Default admin account:**
- Email: `admin@polycms.org`
- Password: `1`

> ⚠️ Change the default password immediately after first login.

### One-Command Setup

```bash
composer setup
```

This runs the full setup pipeline: install dependencies, generate key, run migrations, link storage, and build assets.

### Web UI Installer

For shared hosting or non-CLI deployments, PolyCMS includes a built-in **Web Installer**. Simply upload the files to your server and navigate to:

```
https://your-domain.com/install
```

The installer will guide you through:
1. **System Requirements** — PHP version, extensions, directory permissions
2. **Database Configuration** — Enter your MySQL or PostgreSQL credentials
3. **Run Migrations** — Create all database tables automatically
4. **Admin Account** — Set up your administrator email and password
5. **Finish** — Generates `storage/installed.lock` and redirects to the admin panel

> 💡 The `/install` route is automatically disabled once installation is complete.

---

## ⏱️ Background Tasks

PolyCMS uses Laravel's Task Scheduler for background operations (backups, email campaigns, syncs).

### Production (Cron)

```cron
* * * * * cd /path/to/polycms && php artisan schedule:run >> /dev/null 2>&1
```

### Local Development

```bash
php artisan schedule:work
```

---

## 📁 Project Structure

```
polycms/
├── app/                    # Core application (Models, Controllers, Services)
│   ├── Http/Controllers/   # Web & API controllers
│   ├── Models/             # Eloquent models
│   └── Services/           # Business logic services
├── config/                 # Configuration files
├── database/               # Migrations, seeders, factories
├── graphql/                # Core GraphQL schemas
├── lang/                   # Language files (en, vi, ...)
├── modules/Polyx/          # Installable modules
│   ├── SampleModule/       # Developer reference module
│   ├── CookieConsent/      # GDPR consent
│   ├── Google2FA/          # Two-factor auth
│   └── ...
├── resources/
│   ├── js/admin/           # Vue 3 admin SPA
│   ├── js/                 # Frontend scripts
│   └── css/                # Stylesheets
├── routes/                 # Web, API, auth routes
├── themes/                 # Installable themes
│   ├── flexiwhite/         # Clean multi-purpose theme
│   ├── fleximyta/          # Business & e-commerce theme
│   └── flexidocs/         # Documentation & wiki theme
└── public/                 # Public assets
```

---

## 🔧 Development

### Creating a Module

Modules follow a **Vendor/Module** namespace convention and live in `modules/<Vendor>/<ModuleName>/`. The built-in modules use the `Polyx` vendor namespace, but **you can use any vendor name** for your own modules (e.g., `Acme`, `MyCompany`, your brand name).

Use `SampleModule` as your blueprint:

```
modules/
├── Polyx/                   # Official PolyCMS vendor (built-in modules)
│   ├── SampleModule/        # Developer reference
│   ├── Backup/
│   └── ...
└── Acme/                    # Your custom vendor namespace
    └── MyModule/
        ├── module.json              # Module metadata & dependencies
        ├── src/
        │   ├── MyModuleServiceProvider.php
        │   ├── Http/Controllers/
        │   ├── Models/
        │   └── routes/
        ├── resources/admin/
        │   ├── routes.ts            # Vue admin routes
        │   └── views/               # Vue components
        ├── graphql/                 # Module GraphQL schemas
        └── database/               # Migrations
```

The module identifier format is `Vendor.ModuleName` (e.g., `Acme.MyModule`). PolyCMS auto-discovers all modules by scanning every `modules/*/` vendor directory for `module.json` manifests.

### Creating a Theme

Themes live in `themes/<theme-name>/` and require:

```
my-theme/
├── theme.json               # Theme metadata
├── config/theme.php         # Theme options schema
├── functions.php            # Hook registrations
├── resources/views/         # Blade templates
├── public/css/              # Compiled CSS
└── public/js/               # Compiled JS
```

### Useful Commands

```bash
# Development server (Vite + Laravel concurrent)
npm run dev:all

# Production build
npm run build

# Run tests
composer test

# Code formatting
vendor/bin/pint
```

---

## 📄 License

PolyCMS Community Edition is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

## 🔗 Links

- **Website**: [polyxgo.com](https://polyxgo.com)
- **GitHub**: [github.com/PolyXGO/polycms](https://github.com/PolyXGO/polycms)
- **Issues**: [github.com/PolyXGO/polycms/issues](https://github.com/PolyXGO/polycms/issues)

---

<p align="center">
  Made with ❤️ by <a href="https://polyxgo.com">PolyXGO</a>
</p>