@php
    $url = $item->effective_url ?? '#';
    $target = $item->target === '_blank' ? ' target="_blank" rel="noopener"' : '';
    $cssClass = $item->css_class ? " {$item->css_class}" : '';
    $isActive = theme_is_menu_active($item);
    $hasChildren = $item->children && $item->children->isNotEmpty();
    $isRoot = $isRoot ?? false;
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
        $widgetInstance->id = 'menu-item-' . $item->id;
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
    @php
        static $navSearchCssOut = false;
        $shouldOutputNavSearchCss = !$navSearchCssOut;
        $navSearchCssOut = true;
    @endphp
    @if($shouldOutputNavSearchCss)
    <style>
    .nav-item-search { display: flex !important; align-items: center !important; }
    .nav-item-search .widget { margin: 0 !important; padding: 0 !important; background: transparent !important; border: none !important; box-shadow: none !important; }
    .nav-item-search .widget-blog-search { display: flex !important; align-items: center !important; }
    .nav-item-search .widget-title { display: none !important; }
    </style>
    @endif
    <div class="nav-item nav-item-search{{ $cssClass }}">
        {!! $searchHtml !!}
    </div>
@else
<div class="nav-item{{ $hasChildren ? ' has-dropdown' : '' }}">
    <a href="{{ $url }}" class="nav-link{{ $isActive ? ' active' : '' }}{{ $cssClass }}{{ $hasChildren ? ' has-children-link' : '' }}"{{ $target }}
       @if($item->type === 'language') style="display: {{ ($isRoot ?? false) ? 'inline-flex' : 'flex' }}; align-items: center; gap: {{ ($isRoot ?? false) ? '4px' : '8px' }};{{ ($isRoot ?? false) ? ' vertical-align: middle;' : '' }}" @endif>
        @if($flag)
            <span class="flag-icon-wrapper" style="display: inline-flex; align-items: center; {{ $showLabel ? 'margin-right: 8px;' : '' }} vertical-align: middle;">{!! $flag !!}</span>
        @endif
        @if($showLabel)
            <span class="nav-link-label">
                @if($item->type === 'language')
                    @php
                        $activeLangs = \App\Helpers\LanguageHelper::getActiveLanguages();
                        $currentLangModel = $activeLangs->firstWhere('code', $langCode);
                        echo e($currentLangModel ? ($currentLangModel->native_name ?: $currentLangModel->name) : $item->title);
                    @endphp
                @else
                    {{ $item->title }}
                @endif
            </span>
        @endif
    </a>
    @if($hasChildren)
        <ul class="nav-dropdown">
            @foreach($item->children as $child)
                @include('partials.menu-item', [
                    'item' => $child,
                    'level' => $level + 1,
                    'isRoot' => false
                ])
            @endforeach
        </ul>
    @endif
</div>
@endif
