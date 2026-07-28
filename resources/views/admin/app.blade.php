<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

            const adminTheme = @json(app(\App\Services\SettingsService::class)->get('admin_theme', 'nebula'));
            document.documentElement.setAttribute('data-admin-theme', adminTheme);
            window.polycmsAdminTheme = adminTheme;
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
                @json(cdn_asset('assets/vendor/font-awesome-6.4.0/css/all.min.css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css')),
                @json(\Illuminate\Support\Facades\Vite::asset('resources/css/landing-blocks.css')),
                @json(\Illuminate\Support\Facades\Vite::asset('resources/css/poly-animations.css')),
                @php
                    $previewResolver = app(\App\Services\TemplateResolver::class);
                    $previewActiveThemes = \App\Models\Theme::where('is_active', true)->get();
                @endphp
                @foreach($previewActiveThemes as $previewTheme)
                    @php $previewAssets = $previewResolver->resolveThemeAssets($previewTheme->slug); @endphp
                    @foreach($previewAssets['css'] as $previewCssPath)
                        @json(asset($previewCssPath) . '?v=' . time()),
                    @endforeach
                @endforeach
            ],
        };
    </script>
    @vite(['resources/js/admin/main.ts', 'resources/css/app.css', 'resources/css/landing-blocks.css', 'resources/css/poly-animations.css'])
</head>
<body class="antialiased bg-admin-theme-base text-admin-theme-text transition-colors duration-200">
    <div id="polycms-admin-app"></div>
</body>
</html>
