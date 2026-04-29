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
