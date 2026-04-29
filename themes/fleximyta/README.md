# FlexiMyTa Theme

A modern, flexible blog theme designed for content creators with clean design, responsive layout, and dark mode support.

## Features

- Clean, modern design optimized for reading
- Responsive layout for all devices
- Dark mode support
- Widget areas (sidebar, footer)
- Custom post templates
- Product catalog templates (optional)
- Category archive templates
- SEO-friendly structure

## Theme Structure

```
fleximyta/
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

1. Upload the theme folder to `/themes/fleximyta/`
2. Go to Admin > Appearance > Themes
3. Click "Sync Themes"
4. Click "Activate" on FlexiMyTa theme

## Customization

### Changing Colors

Edit `public/css/style.css` to customize colors and styling.

### Adding Custom Templates

Create new Blade templates in `resources/views/` following the naming convention used by PolyCMS.

### Using Widgets

The theme supports widget areas:
- **Sidebar**: Widget area for sidebar
- **Footer**: Widget area for footer

Add widgets via Admin > Widgets.

## Development

This theme serves as a starting point for your own themes. Key files to modify:

- `functions.php` - Add custom functionality
- `resources/views/` - Customize templates
- `public/css/style.css` - Modify styles
- `public/js/main.js` - Add JavaScript functionality

## Widget Areas

- **Sidebar**: Main sidebar widget area
- **Footer**: Footer widget area

## Dark Mode

The theme fully supports dark mode using TailwindCSS dark mode classes. Dark mode is automatically handled by PolyCMS.

## Asset Loading

The theme uses the `theme_asset()` helper to load assets:

```blade
<link rel="stylesheet" href="{{ theme_asset('css/style.css') }}">
<script src="{{ theme_asset('js/main.js') }}"></script>
```

## Notes

- All templates support dark mode
- Widget areas must be registered in `functions.php`
- Use `Widget::renderArea('area-key')` to render widgets
- Follow PolyCMS coding standards
- Use semantic HTML for SEO
