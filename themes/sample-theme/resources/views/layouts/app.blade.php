<!DOCTYPE html>
<html lang="{{ $site_language ?? 'en' }}" dir="{{ $site_language_direction ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @yield('title', $site_title ?? config('app.name', 'PolyCMS'))
        @hasSection('title')
            - {{ $site_title ?? config('app.name', 'PolyCMS') }}
        @endif
    </title>
    <meta name="description" content="@yield('description', $tagline ?? '')">
    
    <!-- Theme Styles -->
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}?v={{ time() }}">
    
    <!-- Language Direction CSS -->
    <style>
        html[dir="rtl"] {
            direction: rtl;
        }
        html[dir="ltr"] {
            direction: ltr;
        }
    </style>
    
    @stack('styles')
</head>
<body class="theme-sample" dir="{{ $site_language_direction ?? 'ltr' }}">
    {{-- Topbar Menu --}}
    <x-topbar-menu />
    
    <div id="app" style="min-height: 100vh; display: flex; flex-direction: column;">
        {{-- Header --}}
        @include('partials.header')
        
        {{-- Main Content --}}
        <main style="flex: 1;">
            @yield('content')
        </main>
        
        {{-- Footer --}}
        @include('partials.footer')
    </div>
    
    <!-- Theme Scripts -->
    <script src="{{ theme_asset('js/main.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
