@php
    $url = $item->effective_url ?? '#';
    $target = $item->target === '_blank' ? ' target="_blank" rel="noopener"' : '';
    $cssClass = $item->css_class ? " {$item->css_class}" : '';
    $isActive = theme_is_menu_active($item);
    $hasChildren = $item->children && $item->children->isNotEmpty();
    $level = $level ?? 0;

    $flag = '';
    $showLabel = true;
    if ($item->type === 'language') {
        $langCode = \App\Helpers\LanguageHelper::getCurrentLanguage();
        $flag = \App\Helpers\LanguageHelper::getFlagSvg($langCode);
        $cssClass .= ' language-switcher';
        $showLabel = $item->show_label;
    } elseif ($item->getAttribute('lang_code')) {
        $langCode = $item->getAttribute('lang_code');
        $flag = \App\Helpers\LanguageHelper::getFlagSvg($langCode);
        $cssClass .= ' language-item';
    }

    $searchHtml = '';
    if ($item->type === 'search') {
        $style = $item->search_style;
        $placeholder = $item->search_placeholder;

        $widgetInstance = new \App\Models\WidgetInstance();
        $widgetInstance->id = 'menu-item-mobile-' . $item->id;
        $widgetInstance->title = $item->title ?: _l('Search');
        $widgetInstance->config = [
            'display_style' => $style,
            'placeholder' => $placeholder,
            'show_title' => false,
            'suggestions_enabled' => true,
            'suggestion_scope' => 'all',
            'suggestion_limit' => 5,
        ];

        $widget = new \App\Widgets\BlogSearchWidget();
        $searchHtml = $widget->render($widgetInstance);
    }
@endphp

@if($item->type === 'search')
    <div class="mobile-menu-item mobile-menu-item-search{{ $cssClass }}">
        {!! $searchHtml !!}
    </div>
@else
<div class="mobile-menu-item">
    <a href="{{ $url }}" class="nav-link{{ $isActive ? ' active' : '' }}{{ $cssClass }}"{{ $target }}
       @if($item->type === 'language') style="display: {{ ($isRoot ?? false) ? 'inline-flex' : 'flex' }}; align-items: center; gap: {{ ($isRoot ?? false) ? '4px' : '8px' }};{{ ($isRoot ?? false) ? ' vertical-align: middle;' : '' }}" @endif>
        @if($flag)
            <span class="flag-icon-wrapper" style="display: inline-flex; align-items: center; {{ $showLabel ? 'margin-right: 8px;' : '' }} vertical-align: middle;">{!! $flag !!}</span>
        @endif
        @if($showLabel)
            @if($item->type === 'language')
                @php
                    $activeLangs = \App\Helpers\LanguageHelper::getActiveLanguages();
                    $currentLangModel = $activeLangs->firstWhere('code', $langCode);
                    echo e($currentLangModel ? ($currentLangModel->native_name ?: $currentLangModel->name) : $item->title);
                @endphp
            @else
                {{ $item->title }}
            @endif
        @endif
    </a>
    @if($hasChildren)
        <span class="submenu-toggle"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></span>
        <ul class="mobile-submenu">
            @foreach($item->children as $child)
                @include('partials.menu-item-mobile', [
                    'item' => $child,
                    'level' => $level + 1
                ])
            @endforeach
        </ul>
    @endif
</div>
@endif
