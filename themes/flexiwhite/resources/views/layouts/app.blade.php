{{--
    ┌─────────────────────────────────────────────────────────────┐
    │  POLYCMS — MAIN LAYOUT (app.blade.php)                      │
    ├─────────────────────────────────────────────────────────────┤
    │  This is the root layout for ALL frontend pages.            │
    │                                                              │
    │  DATA AVAILABLE (injected via theme.view.data filter):       │
    │  • $site_title, $tagline, $theme_name, $theme_version        │
    │  • $current_year, $site_language, $site_language_direction    │
    │  • Page-specific: $post, $page, $product, $category, etc.    │
    │                                                              │
    │  HOOK INTEGRATION POINTS:                                    │
    │  • theme.options.values → CSS variables from theme options   │
    │  • theme.options.css_vars → Extend/modify CSS custom props   │
    │  • frontend.topbar.banners → Banner slider module hook       │
    │  • <x-topbar-menu /> → Topbar component (auth users)         │
    │                                                              │
    │  BLADE FEATURES USED:                                        │
    │  • @yield('content') — child views inject content here       │
    │  • @yield('title') — per-page title override                 │
    │  • @stack('styles'/@stack('scripts') — push CSS/JS from views│
    │  • @include() — partial templates (header, footer)           │
    │  • @hasSection() — conditional title separator               │
    └─────────────────────────────────────────────────────────────┘
--}}
<!DOCTYPE html>
<html lang="{{ $site_language ?? 'en' }}" dir="{{ $site_language_direction ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @yield('title', $site_title ?? config('app.name', 'PolyCMS'))
        @hasSection('title')
            - {{ $site_title ?? config('app.name', 'PolyCMS') }}
        @endif
    </title>
    <meta name="description" content="@yield('description', $tagline ?? '')">

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Theme Styles -->
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}?v={{ time() }}">

    @php
        use App\Facades\Hook;

        $themeOptionValues = Hook::applyFilters('theme.options.values', theme_get_options());

        $cssVars = [
            '--color-primary' => $themeOptionValues['theme_color_primary'] ?? '#2563eb',
            '--color-secondary' => $themeOptionValues['theme_color_secondary'] ?? '#64748b',
            '--color-accent' => $themeOptionValues['theme_color_accent'] ?? '#10b981',
            '--color-border' => $themeOptionValues['theme_color_border'] ?? '#d1d5db',
            '--theme-surface-color' => $themeOptionValues['theme_surface_color'] ?? '#ffffff',
            '--theme-heading-font-family' => $themeOptionValues['theme_heading_font_family'] ?? 'Inter, sans-serif',
            '--theme-heading-font-weight' => $themeOptionValues['theme_heading_font_weight'] ?? '700',
            '--theme-heading-line-height' => $themeOptionValues['theme_heading_line_height'] ?? 1.2,
            '--theme-heading-letter-spacing' => ((float) ($themeOptionValues['theme_heading_letter_spacing'] ?? -0.02)) . 'em',
            '--theme-heading-color' => $themeOptionValues['theme_heading_color'] ?? '#111827',
            '--theme-heading-h1-size' => ((float) ($themeOptionValues['theme_heading_h1_size'] ?? 36)) . 'px',
            '--theme-heading-h2-size' => ((float) ($themeOptionValues['theme_heading_h2_size'] ?? 28)) . 'px',
            '--theme-heading-h3-size' => ((float) ($themeOptionValues['theme_heading_h3_size'] ?? 22)) . 'px',
            '--theme-heading-h4-size' => ((float) ($themeOptionValues['theme_heading_h4_size'] ?? 18)) . 'px',
            '--theme-heading-h5-size' => ((float) ($themeOptionValues['theme_heading_h5_size'] ?? 16)) . 'px',
            '--theme-heading-h6-size' => ((float) ($themeOptionValues['theme_heading_h6_size'] ?? 14)) . 'px',
            '--theme-body-font-family' => $themeOptionValues['theme_body_font_family'] ?? 'Inter, sans-serif',
            '--theme-body-font-size' => ((float) ($themeOptionValues['theme_body_font_size'] ?? 16)) . 'px',
            '--theme-body-line-height' => $themeOptionValues['theme_body_line_height'] ?? 1.6,
            '--theme-body-color' => $themeOptionValues['theme_body_color'] ?? '#1f2937',
            '--theme-body-muted-color' => $themeOptionValues['theme_body_muted_color'] ?? '#6b7280',
            '--theme-body-background-color' => $themeOptionValues['theme_body_background_color'] ?? '#ffffff',
            '--theme-link-color' => $themeOptionValues['theme_anchor_color'] ?? '#2563eb',
            '--theme-link-hover-color' => $themeOptionValues['theme_anchor_hover_color'] ?? '#1e40af',
            '--theme-container-max-width' => ((float) ($themeOptionValues['theme_container_max_width'] ?? 1200)) . 'px',
            '--theme-button-radius' => ((float) ($themeOptionValues['theme_button_radius'] ?? 12)) . 'px',
            '--theme-card-radius' => ((float) ($themeOptionValues['theme_card_radius'] ?? 12)) . 'px',
        ];

        $shadowMap = [
            'none' => 'none',
            'sm' => 'var(--shadow-sm)',
            'md' => 'var(--shadow-md)',
            'lg' => 'var(--shadow-xl)',
        ];
        $hoverShadowMap = [
            'none' => 'var(--shadow-sm)',
            'sm' => 'var(--shadow-md)',
            'md' => 'var(--shadow-xl)',
            'lg' => 'var(--shadow-xl)',
        ];
        $selectedShadow = $themeOptionValues['theme_card_shadow'] ?? 'md';
        $cssVars['--theme-card-shadow'] = $shadowMap[$selectedShadow] ?? 'var(--shadow-md)';
        $cssVars['--theme-card-shadow-hover'] = $hoverShadowMap[$selectedShadow] ?? 'var(--shadow-xl)';

        $cssVars = Hook::applyFilters('theme.options.css_vars', $cssVars, $themeOptionValues);
    @endphp

    <!-- Language Direction CSS -->
    <style>
        html[dir="rtl"] {
            direction: rtl;
        }
        html[dir="ltr"] {
            direction: ltr;
        }

        :root {
@foreach ($cssVars as $var => $value)
            {{ $var }}: {{ $value }};
@endforeach
        }
    </style>

    @stack('theme-styles')
    @stack('styles')

    {{-- Core head hook (favicon, canonical, robots, etc.) --}}
    {!! \App\Facades\Hook::doAction('cms_head') !!}
</head>
<body class="theme-flexiwhite" dir="{{ $site_language_direction ?? 'ltr' }}">


    {{-- Banner Slider (visible to all users) --}}
    @php
        $banners = \App\Facades\Hook::applyFilters('frontend.topbar.banners', []);
    @endphp
    @if(!empty($banners) && count($banners) > 0)
        @include('banner-slider::partials.banner-slider', ['banners' => $banners])
    @endif

    {{-- Topbar Menu (only for authenticated users) --}}
    <x-topbar-menu />

    <div id="app" style="min-height: 100vh; display: flex; flex-direction: column;">
        {{-- Header --}}
        @include('partials.header')

        {{-- Breadcrumb (above main content) --}}
        @hasSection('breadcrumb')
        <div class="container breadcrumb-bar">
            @yield('breadcrumb')
        </div>
        @endif

        {{-- Main Content --}}
        <main style="flex: 1;">
            @yield('content')
        </main>

        {{-- Footer --}}
        @include('partials.footer')
    </div>

    <!-- Theme Scripts -->
    <script src="{{ theme_asset('js/main.js') }}?v={{ time() }}"></script>

    @stack('theme-scripts')
    @includeIf('system.partials.theme-assets')
    @stack('scripts')
</body>
</html>
