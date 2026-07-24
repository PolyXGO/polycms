<!DOCTYPE html>
<html lang="{{ $site_language ?? 'en' }}" dir="{{ $site_language_direction ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'PolyCMS') }}</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/css/landing-blocks.css'])

    <!-- Theme Styles -->
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}?v={{ time() }}">

    <!-- Theme Mode Initialization (Prevents FOUC) -->
    <script>
        (function() {
            try {
                const themeMode = localStorage.getItem('theme_mode');
                const colorTheme = localStorage.getItem('color-theme');
                const defaultMode = '{{ theme_get_option('flexiwhite_default_color_mode', 'light') }}';
                let isDark = false;
                
                if (themeMode) {
                    if (themeMode === 'system') {
                        isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    } else {
                        isDark = themeMode === 'dark';
                    }
                } else if (colorTheme) {
                    isDark = colorTheme === 'dark';
                } else {
                    isDark = defaultMode === 'dark';
                }
                
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    @php
        use App\Facades\Hook;

        $themeOptionValues = Hook::applyFilters('theme.options.values', theme_get_options());

        $cssVars = [
            '--color-primary' => $themeOptionValues['theme_color_primary'] ?? '#2563eb',
            '--color-secondary' => $themeOptionValues['theme_color_secondary'] ?? '#64748b',
            '--color-accent' => $themeOptionValues['theme_color_accent'] ?? '#10b981',
            '--color-border' => $themeOptionValues['theme_color_border'] ?? '#d1d5db',
            '--theme-surface-color' => $themeOptionValues['theme_surface_color'] ?? 'var(--geist-background)',
            '--theme-heading-font-family' => $themeOptionValues['theme_heading_font_family'] ?? 'Inter, sans-serif',
            '--theme-heading-font-weight' => $themeOptionValues['theme_heading_font_weight'] ?? '700',
            '--theme-heading-line-height' => $themeOptionValues['theme_heading_line_height'] ?? 1.3,
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
            '--theme-body-line-height' => $themeOptionValues['theme_body_line_height'] ?? 1.3,
            '--theme-body-color' => $themeOptionValues['theme_body_color'] ?? '#1f2937',
            '--theme-body-muted-color' => $themeOptionValues['theme_body_muted_color'] ?? '#6b7280',
            '--theme-body-background-color' => $themeOptionValues['theme_body_background_color'] ?? 'var(--geist-background)',
            '--theme-link-color' => $themeOptionValues['theme_anchor_color'] ?? '#2563eb',
            '--theme-link-hover-color' => $themeOptionValues['theme_anchor_hover_color'] ?? '#1e40af',
            '--theme-container-max-width' => ((float) ($themeOptionValues['theme_container_max_width'] ?? 1200)) . 'px',
            '--theme-reading-max-width' => ((float) ($themeOptionValues['theme_reading_max_width'] ?? 768)) . 'px',
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

    @includeIf('system.partials.theme-styles')
    @stack('theme-styles')
    @stack('styles')

    {{-- Core head hook (favicon, canonical, robots, etc.) --}}
    {!! \App\Facades\Hook::doAction('cms_head') !!}
    
    <!-- Inertia -->
    @routes
    @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
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

    <div id="app-wrapper" style="min-height: 100vh; display: flex; flex-direction: column;">
        {{-- Header --}}
        @include('partials.header')

        {{-- Main Content (Inertia) --}}
        <main style="flex: 1; padding-top: 2rem; padding-bottom: 2rem;">
            @inertia
        </main>

        {{-- Footer --}}
        @include('partials.footer')
    </div>

    {{-- Global Scroll Buttons (visible on all pages) --}}
    <div class="post-scroll-tools">
        <div class="post-scroll-tools-inner">
            <button class="scroll-btn" id="scroll-to-top" aria-label="{{ _l('Scroll to top') }}" title="{{ _l('Scroll to top') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
            </button>
            <button class="scroll-btn" id="scroll-to-bottom" aria-label="{{ _l('Scroll to bottom') }}" title="{{ _l('Scroll to bottom') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Theme Scripts -->
    <script src="{{ theme_asset('js/main.js') }}?v={{ time() }}"></script>

    @stack('theme-scripts')
    @includeIf('system.partials.theme-scripts')
    @stack('scripts')
</body>
</html>
