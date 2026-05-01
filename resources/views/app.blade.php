<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        @php
            $siteIconUrl = \App\Facades\Hook::applyFilters('seo.site_favicon', app(\App\Services\SettingsService::class)->get('site_icon'));
        @endphp
        @if($siteIconUrl)
            <link rel="icon" type="image/png" href="{{ $siteIconUrl }}">
            <link rel="apple-touch-icon" href="{{ $siteIconUrl }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @include('components.topbar-menu')
        @inertia
    </body>
</html>
