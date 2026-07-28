{{--
    Sub-theme CSS asset injection (placed in <head>).
    When an entity uses a sub-theme template (via template_theme),
    this partial injects that sub-theme's stylesheets.
--}}
@if(isset($__templateTheme) && $__templateTheme)
    @php
        $__mainTheme = app(\App\Services\ThemeManager::class)->getMainTheme();
        $__isSubTheme = $__mainTheme && $__mainTheme->slug !== $__templateTheme;
    @endphp

    @if($__isSubTheme)
        @php
            $__themeAssets = app(\App\Services\TemplateResolver::class)->resolveThemeAssets($__templateTheme);
        @endphp

        @foreach($__themeAssets['css'] ?? [] as $cssPath)
            <link rel="stylesheet" href="{{ $cssPath }}?v={{ time() }}">
        @endforeach
    @endif
@endif
