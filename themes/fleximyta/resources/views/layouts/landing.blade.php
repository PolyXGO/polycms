<!DOCTYPE html>
<html lang="{{ $site_language ?? 'en' }}" dir="{{ $site_language_direction ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @yield('title', $site_title ?? config('app.name', 'FlexiMyTa'))
    </title>
    <meta name="description" content="@yield('description', '')">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Theme Styles -->
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ theme_asset('css/landing-blocks.css') }}?v={{ time() }}">
    
    @stack('styles')
    @stack('theme-styles')
    
    @includeIf('system.partials.theme-assets')
    
    <style>
        body { padding: 0; margin: 0; }
        .landing-page-content { min-height: 100vh; }
    </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    <div id="app">
        {{-- Banner Slider --}}
        @php
            $banners = \App\Facades\Hook::applyFilters('frontend.topbar.banners', []);
        @endphp
        @if(!empty($banners) && count($banners) > 0)
            @include('banner-slider::partials.banner-slider', ['banners' => $banners])
        @endif
        <main>
            @yield('content')
        </main>
    </div>
    
    <!-- Theme Scripts -->
    <script src="{{ theme_asset('js/main.js') }}?v={{ time() }}"></script>
    @vite(['resources/js/ecommerce-bridge.ts'])
    @stack('scripts')
    @stack('theme-scripts')
</body>
</html>
