<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}">
    
    @stack('styles')
</head>
<body class="theme-sample">
    <div id="app" class="min-h-screen flex flex-col">
        {{-- Header --}}
        @include('partials.header')
        
        {{-- Main Content --}}
        <main class="flex-grow">
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
