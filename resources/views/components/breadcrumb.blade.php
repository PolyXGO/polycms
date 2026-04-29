@props(['items' => [], 'context' => null, 'class' => ''])

@php
    // Application wide hook for breadcrumbs allowing themes or plugins to mutate items
    $items = \App\Facades\Hook::applyFilters('core.breadcrumb.items', $items, $context);
@endphp

@if(!empty($items))
<nav aria-label="Breadcrumb" class="polycms-breadcrumb w-full {{ $class }}">
    <ol class="flex flex-wrap items-center m-0 p-0 list-none text-sm text-gray-500 dark:text-gray-400">
        @foreach($items as $item)
            <li class="inline-flex items-center">
                @if(!$loop->first)
                    <span class="breadcrumb-separator mx-2 text-gray-400 dark:text-gray-500" style="font-size: 0.65rem;">
                        {!! \App\Facades\Hook::applyFilters('core.breadcrumb.separator', '<i class="fas fa-chevron-right"></i>') !!}
                    </span>
                @endif
                
                @if(!empty($item['url']) && !$loop->last)
                    <a href="{{ $item['url'] }}" class="breadcrumb-link hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        @if(!empty($item['icon']))
                            <i class="{{ $item['icon'] }} breadcrumb-icon mr-1"></i>
                        @endif
                        <span class="breadcrumb-label">{{ $item['label'] ?? '' }}</span>
                    </a>
                @else
                    <span class="breadcrumb-label text-gray-800 dark:text-gray-200" aria-current="page">
                        @if(!empty($item['icon']))
                            <i class="{{ $item['icon'] }} breadcrumb-icon mr-1"></i>
                        @endif
                        {{ $item['label'] ?? '' }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
