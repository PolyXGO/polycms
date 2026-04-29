@php
    $html = $attrs['html'] ?? '';
    $wrapRaw = $attrs['wrap_raw'] ?? false;
@endphp

<div class="landing-html {{ $wrapRaw ? '' : 'max-w-6xl mx-auto px-4 py-8' }}">
    {!! $html !!}
</div>
