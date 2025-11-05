@php
$hasChildren = !empty($item['children'] ?? []);
$isHighlight = $item['highlight'] ?? false;
$icon = $item['icon'] ?? null;
$label = $item['label'] ?? '';
$url = $item['url'] ?? '#';
$method = $item['method'] ?? 'GET';
@endphp

@if($hasChildren)
    <div class="topbar-dropdown">
        <a href="#" class="{{ $isHighlight ? 'topbar-highlight' : '' }}">
            @if($icon)
                <span class="topbar-icon">@include('components.topbar-icon', ['icon' => $icon])</span>
            @endif
            <span>{{ $label }}</span>
        </a>
        <div class="topbar-dropdown-content">
            @foreach($item['children'] as $child)
                @if($child['method'] === 'POST')
                    <form action="{{ $child['url'] }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" style="background: none; border: none; width: 100%; text-align: left; cursor: pointer; padding: 8px 15px; color: #b4b9be; display: flex; align-items: center; gap: 8px;">
                            @if(!empty($child['icon']))
                                <span class="topbar-icon">@include('components.topbar-icon', ['icon' => $child['icon']])</span>
                            @endif
                            <span>{{ $child['label'] }}</span>
                        </button>
                    </form>
                @else
                    <a href="{{ $child['url'] }}">
                        @if(!empty($child['icon']))
                            <span class="topbar-icon">@include('components.topbar-icon', ['icon' => $child['icon']])</span>
                        @endif
                        <span>{{ $child['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@else
    @if($method === 'POST')
        <form action="{{ $url }}" method="POST" style="margin: 0; display: inline;">
            @csrf
            <button type="submit" class="topbar-button {{ $isHighlight ? 'topbar-highlight' : '' }}" style="background: none; border: none; color: #b4b9be; padding: 0 8px; display: inline-flex; align-items: center; gap: 4px; border-radius: 3px; cursor: pointer; font-size: 13px; line-height: 32px;">
                @if($icon)
                    <span class="topbar-icon">@include('components.topbar-icon', ['icon' => $icon])</span>
                @endif
                <span>{{ $label }}</span>
            </button>
        </form>
    @else
        <a href="{{ $url }}" class="{{ $isHighlight ? 'topbar-highlight' : '' }}">
            @if($icon)
                <span class="topbar-icon">@include('components.topbar-icon', ['icon' => $icon])</span>
            @endif
            <span>{{ $label }}</span>
        </a>
    @endif
@endif

