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

    {!! \App\Facades\Hook::doAction('cms_head') !!}

    <!-- Theme Styles -->
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}?v={{ time() }}">
    @stack('theme-styles')
    
    <!-- Tailwind CSS (Development/CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
        // Theme Mode Initialization (Prevents FOUC)
        (function() {
            try {
                const themeMode = localStorage.getItem('theme_mode');
                const colorTheme = localStorage.getItem('color-theme');
                const defaultMode = '{{ theme_get_option('flexidocs_default_color_mode', 'light') }}';
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
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">
    {{-- Banner Slider --}}
    @php
        $banners = \App\Facades\Hook::applyFilters('frontend.topbar.banners', []);
    @endphp
    @if(!empty($banners) && count($banners) > 0)
        @include('banner-slider::partials.banner-slider', ['banners' => $banners])
    @endif

    <x-topbar-menu />
    
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <!-- Theme Scripts -->
    <script src="{{ theme_asset('js/main.js') }}?v={{ time() }}"></script>
    @stack('theme-scripts')
    @includeIf('system.partials.theme-assets')
</body>
</html>
