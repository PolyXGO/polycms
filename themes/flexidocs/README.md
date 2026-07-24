# {{THEME_NAME}}

{{DESCRIPTION}}

## Features

- Clean, modern design
- Responsive layout
- Dark mode support
- Widget areas (sidebar, footer)
- Custom post templates (including iFrame & Full-Width iFrame)
- Product catalog templates
- Category archive templates (including iFrame & Full-Width iFrame)
- SEO-friendly structure (context-aware meta tags for nested posts)

## Theme Structure

```
{{THEME_SLUG}}/
├── theme.json          # Theme manifest
├── functions.php       # Theme functions and hooks
├── screenshot.png      # Theme screenshot (1200x900px recommended)
├── README.md           # This file
├── resources/
│   └── views/          # Blade templates
│       ├── layouts/
│       │   └── app.blade.php
│       ├── partials/
│       │   ├── header.blade.php
│       │   └── footer.blade.php
│       ├── posts/
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   ├── iframe.blade.php
│       │   └── iframe-full.blade.php
│       ├── products/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── categories/
│       │   ├── show.blade.php
│       │   ├── iframe.blade.php
│       │   └── iframe-full.blade.php
│       └── pages/
│           └── show.blade.php
├── public/             # Public assets
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
└── config/
    └── theme.php       # Theme configuration
```

## Installation

1. Upload the theme folder to `/themes/{{THEME_SLUG}}/`
2. Go to Admin > Appearance > Themes
3. Click "Sync Themes" button (required to register theme in database)
4. Theme will appear in the list with "installed" status
5. Click "Activate" on {{THEME_NAME}}

**Important**: Themes must be synced to the database before they can be activated. The "Sync Themes" button discovers themes from the filesystem and registers them in the database.

## Dynamic Routing & Hooks

FlexiDocs uniquely intercepts category and post URLs automatically. Any Category or Post explicitly mapped to the `flexidocs` Layout/Theme is considered part of the documentation wiki.
The `functions.php` file hooks into `category.frontend_url` and `post.frontend_url` to override standard PolyCMS blog permalinks (`/danh-muc/...`).

### Theme Options Integration
The URL prefix used to serve the Wiki (default: `docs`) is customizable and stored independently from the main PolyCMS layout theme.
You can configure this by going to **Admin > Settings > Theme Options > FlexiDocs Settings**.

When you configure this prefix (e.g., `apis`):
1. The routing interceptor updates automatically (affecting `getFrontendUrlAttribute()`).
2. FlexiDocs registers custom web routes at the exact prefix configured by accessing the Mega Cache `theme_options`.

## Customization

### Changing Colors

Edit `public/css/style.css` to customize colors and styling.

### Adding Custom Templates

Create new Blade templates in `resources/views/` following the naming convention used by PolyCMS.

### Using Widgets

The theme supports widget areas:
- Sidebar
- Footer

Add widgets via Admin > Widgets.

## Development

This theme serves as a starting point for your own themes. Key files to modify:

- `functions.php` - Add custom functionality
- `resources/views/` - Customize templates
- `public/css/style.css` - Customize styles
- `public/js/main.js` - Add custom JavaScript

## Support

For more information about theme development, see:
- Theme Development Guide in `heraspec/changes/theme-system/proposal.md`
- Theme Hooks Documentation in `heraspec/changes/theme-system/proposal.md`
