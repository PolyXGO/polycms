<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <title>PolyCMS Admin</title>
    @php
        $siteIconUrl = \App\Facades\Hook::applyFilters('seo.site_favicon', app(\App\Services\SettingsService::class)->get('site_icon'));
    @endphp
    @if($siteIconUrl)
        <link rel="icon" type="image/png" href="{{ $siteIconUrl }}">
        <link rel="apple-touch-icon" href="{{ $siteIconUrl }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Initialize theme before Vue app loads to prevent flash
        (function() {
            const themeMode = localStorage.getItem('theme_mode') || 'system';
            let isDark = false;

            if (themeMode === 'system') {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    isDark = true;
                }
            } else {
                isDark = themeMode === 'dark';
            }

            if (isDark) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script>
        // Inject permalink settings for Vue components
        window.polycmsPermalinkSettings = @json(theme_permalink_structure());
        window.polycmsActiveModules = @json(app(\App\Services\ModuleManager::class)->getEnabledModules());
    </script>
    <script>
        window.polycmsThemePreviewAssets = {
            frontendStyles: [
                @json(\Illuminate\Support\Facades\Vite::asset('resources/css/app.css')),
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                @json(theme_asset('css/landing-blocks.css') . '?v=' . time()),
                @json(theme_asset('css/style.css') . '?v=' . time()),
            ],
        };
    </script>
    @vite(['resources/js/admin/main.ts', 'resources/css/app.css', 'resources/css/landing-blocks.css', 'modules/Polyx/XemTuoiXongDat/resources/admin/index.ts'])
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">
    <div id="polycms-admin-app"></div>
</body>
</html>
