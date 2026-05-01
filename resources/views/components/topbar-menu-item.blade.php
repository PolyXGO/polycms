@php
$hasChildren = !empty($item['children'] ?? []);
$isHighlight = $item['highlight'] ?? false;
$icon = $item['icon'] ?? null;
$label = $item['label'] ?? '';
$url = $item['url'] ?? '#';
$method = $item['method'] ?? 'GET';
@endphp

@if($hasChildren)
    <div id="{{ $item['id'] ?? '' }}" 
        class="topbar-dropdown {{ ($isSubSub ?? false) ? 'submenu' : '' }} momentum-right"
        data-momentum="right"
        onmouseenter="checkTopbarMenuAlignment(this)"
    >
        <a href="{{ $url }}" class="{{ $isHighlight ? 'topbar-highlight' : '' }} {{ $hasChildren ? 'has-arrow' : '' }} {{ ($isSubSub ?? false) ? 'topbar-dropdown-item' : '' }}">
            @if($icon)
                <span class="topbar-icon">@include('components.topbar-icon', ['icon' => $icon])</span>
            @endif
            <span>{{ $label }}</span>
            @if($isSubSub ?? false)
                <svg class="submenu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            @endif
        </a>
        <div class="topbar-dropdown-content">
            @foreach($item['children'] as $child)
                @if(!empty($child['children']))
                    @include('components.topbar-menu-item', ['item' => $child, 'isSubSub' => true])
                @else
                    @php $childMethod = $child['method'] ?? 'GET'; @endphp
                    @if($childMethod === 'POST')
                        <form action="{{ $child['url'] }}" method="POST" class="topbar-dropdown-form">
                            @csrf
                            <button type="submit" 
                                class="topbar-dropdown-item" 
                                @if(($child['id'] ?? '') === 'logout') onclick="handleTopbarLogout(event)" @endif
                            >
                                @if(!empty($child['icon']))
                                    <span class="topbar-icon">@include('components.topbar-icon', ['icon' => $child['icon']])</span>
                                @endif
                                <span>{{ $child['label'] }}</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ $child['url'] }}" class="topbar-dropdown-item">
                            @if(!empty($child['icon']))
                                <span class="topbar-icon">@include('components.topbar-icon', ['icon' => $child['icon']])</span>
                            @endif
                            <span>{{ $child['label'] }}</span>
                        </a>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
@else
    @if($method === 'POST')
        <form action="{{ $url }}" method="POST" class="topbar-form">
            @csrf
            <button type="submit" 
                class="topbar-button {{ $isHighlight ? 'topbar-highlight' : '' }}"
                @if(($item['id'] ?? '') === 'logout') onclick="handleTopbarLogout(event)" @endif
            >
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

