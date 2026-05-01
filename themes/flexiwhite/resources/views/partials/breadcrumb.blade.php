{{-- Breadcrumb Navigation Partial --}}
{{-- Usage: @include('partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => url('/')], ...]]) --}}
@if(!empty($items) && count($items) > 1)
<nav class="breadcrumb" aria-label="{{ _l('Breadcrumb') }}">
    <ol>
        @foreach($items as $crumb)
            <li>
                @if(!$loop->last && !empty($crumb['url']))
                    <a href="{{ $crumb['url'] }}">{{ $crumb['label'] ?? '' }}</a>
                @else
                    <span aria-current="page">{{ $crumb['label'] ?? '' }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
