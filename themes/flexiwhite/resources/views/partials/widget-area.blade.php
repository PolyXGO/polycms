@php
    /** @var \App\Services\WidgetManager $widgetManager */
    $widgetManager = app('widget');
    $areaKey = $key ?? '';
    $wrapperClass = $class ?? '';
    $areaHtml = trim($widgetManager->renderArea($areaKey));
@endphp

@if($areaHtml !== '')
    <div class="widget-area {{ $wrapperClass }}" data-widget-area="{{ $areaKey }}">
        {!! $areaHtml !!}
    </div>
@endif
