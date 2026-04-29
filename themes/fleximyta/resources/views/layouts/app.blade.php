<!DOCTYPE html>
<html lang="{{ $site_language ?? 'en' }}" dir="{{ $site_language_direction ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @yield('title', $site_title ?? config('app.name', 'FlexiMyTa'))
        @hasSection('title')
            - {{ $site_title ?? config('app.name', 'FlexiMyTa') }}
        @endif
    </title>
    <meta name="description" content="@yield('description', $tagline ?? '')">
    
    {!! \App\Facades\Hook::doAction('cms_head') !!}
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- App Config JS injection -->
    <script>
        window.AppConfig = {
            redirectCartAfterAdd: {{ \App\Models\Setting::where('key', 'ecommerce_redirect_cart_after_add')->value('value') == '1' || \App\Models\Setting::where('key', 'ecommerce_redirect_cart_after_add')->value('value') === null ? 'true' : 'false' }} 
        };
    </script>
    
    <!-- Theme Styles -->
    @vite(['resources/css/app.css', 'resources/js/ecommerce-bridge.ts'])
    <link rel="stylesheet" href="{{ theme_asset('css/landing-blocks.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}?v={{ time() }}">
    @stack('styles')
    @stack('theme-styles')
    
    @includeIf('system.partials.theme-assets')
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    {{-- Banner Slider --}}
    @php
        $banners = \App\Facades\Hook::applyFilters('frontend.topbar.banners', []);
    @endphp
    @if(!empty($banners) && count($banners) > 0)
        @include('banner-slider::partials.banner-slider', ['banners' => $banners])
    @endif

    {{-- Topbar Menu --}}
    <x-topbar-menu />
    
        {{-- Header --}}
        @php
            $containerClass = 'container';
            if (isset($page)) {
                $layout = $page->layout ?? 'default';
                if ($layout === 'fullwidth') {
                    $containerClass = 'container-wide';
                } elseif ($layout === 'default' || $layout === 'single_column') {
                    $containerClass = 'container';
                }
            }
        @endphp
        
        @include('partials.header', ['containerClass' => $containerClass])
        
        {{-- Main Content --}}
        <main style="flex: 1; margin-top: 0;">
            @yield('content')
        </main>
        
        {{-- Footer --}}
        @include('partials.footer', ['containerClass' => $containerClass])
    </div>
    
    <!-- Theme Scripts -->
    <script src="{{ theme_asset('js/main.js') }}?v={{ time() }}"></script>
    
    
    @stack('scripts')
    @stack('theme-scripts')
</body>
</html>
