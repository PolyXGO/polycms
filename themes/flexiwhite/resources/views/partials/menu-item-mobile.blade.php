@php
    $url = $item->effective_url ?? '#';
    $target = $item->target === '_blank' ? ' target="_blank" rel="noopener"' : '';
    $cssClass = $item->css_class ? " {$item->css_class}" : '';
    $currentUrl = request()->url();
    $isActive = theme_is_menu_active($item);
    $hasChildren = $item->children && $item->children->isNotEmpty();
    $level = $level ?? 0;
@endphp

<div class="mobile-menu-item">
    <a href="{{ $url }}" class="nav-link{{ $isActive ? ' active' : '' }}{{ $cssClass }}"{{ $target }}>
        {{ $item->title }}
    </a>
    @if($hasChildren)
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
