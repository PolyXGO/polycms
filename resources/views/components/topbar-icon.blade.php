@php
    $iconName = $icon ?? 'default';
@endphp

{!! \App\Support\IconRegistry::render($iconName) !!}
