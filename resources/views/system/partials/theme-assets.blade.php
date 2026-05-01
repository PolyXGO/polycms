@if(isset($__templateTheme) && $__templateTheme && app(\App\Services\TemplateResolver::class)->themeHasTemplate($__templateTheme, 'theme-assets'))
    {{-- This checks if the active theme provides a specific theme-assets partial, which is optional --}}
@else
    @if(isset($__templateTheme) && $__templateTheme)
        @php
            $themeAssets = app(\App\Services\TemplateResolver::class)->resolveThemeAssets($__templateTheme);
        @endphp
        
        @if(!empty($themeAssets['css']))
            @push('theme-styles')
                @foreach($themeAssets['css'] as $cssPath)
                    <link rel="stylesheet" href="{{ $cssPath }}">
                @endforeach
            @endpush
        @endif

        @if(!empty($themeAssets['js']))
            @push('theme-scripts')
                @foreach($themeAssets['js'] as $jsPath)
                    <script src="{{ $jsPath }}"></script>
                @endforeach
            @endpush
        @endif
    @endif
@endif
