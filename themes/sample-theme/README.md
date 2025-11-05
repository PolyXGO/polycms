# Sample Theme

A sample theme for PolyCMS demonstrating theme structure and best practices.

## Features

- Clean, modern design
- Responsive layout
- Dark mode support
- Widget areas (sidebar, footer)
- Custom post templates
- Product catalog templates
- Category archive templates
- SEO-friendly structure

## Theme Structure

```
sample-theme/
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
│       │   └── show.blade.php
│       ├── products/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── categories/
│       │   └── show.blade.php
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

1. Upload the theme folder to `/themes/sample-theme/`
2. Go to Admin > Appearance > Themes
3. Click "Sync Themes"
4. Click "Activate" on Sample Theme

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
- [Theme Development Guide](../../docs/theme-development.md)
- [Theme Hooks Documentation](../../docs/theme-hooks.md)

