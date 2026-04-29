@php
    $url = $item->effective_url ?? '#';
    $target = $item->target === '_blank' ? ' target="_blank" rel="noopener"' : '';
    $cssClass = $item->css_class ? " {$item->css_class}" : '';
    $isActive = theme_is_menu_active($item);
    $hasChildren = $item->children && $item->children->isNotEmpty();
    $isRoot = $isRoot ?? false;
    $level = $level ?? 0;
@endphp

<div class="nav-item{{ $hasChildren ? ' has-dropdown' : '' }}">
    <a href="{{ $url }}" class="nav-link{{ $isActive ? ' active' : '' }}{{ $cssClass }}"{{ $target }}>
        {{ $item->title }}
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
