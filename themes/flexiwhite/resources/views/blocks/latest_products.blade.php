@include('core.blocks.products_slider', [
    'attrs' => array_merge([
        'layout' => $attrs['layout'] ?? 'grid',
        'heading' => $attrs['heading'] ?? __('Featured Products'),
        'count' => $attrs['count'] ?? 6,
        'columns' => $attrs['columns'] ?? 3,
        'show_view_all' => $attrs['show_view_all'] ?? true,
    ], $attrs ?? []),
    'context' => $context ?? [],
    'renderer' => $renderer ?? null
])
