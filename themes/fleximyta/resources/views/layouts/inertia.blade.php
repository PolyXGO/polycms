<!DOCTYPE html>
<html lang="{{ $site_language ?? 'en' }}" dir="{{ $site_language_direction ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'FlexiMyTa') }}</title>
    
    <!-- Theme Styles -->
    <link rel="stylesheet" href="{{ theme_asset('css/landing-blocks.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}?v={{ time() }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
    @stack('theme-styles')
    
    @includeIf('system.partials.theme-assets')

    <!-- Inertia -->
    @routes
    @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    {{-- Topbar Menu --}}
    <x-topbar-menu />
    
    {{-- Header --}}
    @include('partials.header', ['containerClass' => 'container'])
    
    {{-- Main Content --}}
    <main style="flex: 1; margin-top: 0; padding-top: 32px;">
        @inertia
    </main>
    
    {{-- Footer --}}
    @include('partials.footer', ['containerClass' => 'container'])
    
    <!-- Theme Scripts -->
    <script src="{{ theme_asset('js/main.js') }}?v={{ time() }}"></script>
    
    @stack('scripts')
    @stack('theme-scripts')
</body>
</html>
