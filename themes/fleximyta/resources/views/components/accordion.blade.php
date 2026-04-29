@props([
    'items' => [],
    'style' => 'standard', // separate, bordered, standard
])

<div {{ $attributes->merge(['class' => 'faq-container style-' . $style]) }}>
    @foreach($items as $index => $item)
        <div class="faq-item {{ $style === 'bordered' ? 'border border-gray-200' : '' }}">
            <div class="faq-question">
                <span>{{ $item['title'] ?? '' }}</span>
                <i class="fas fa-chevron-down mr-3 text-gray-400"></i>
            </div>
            <div class="faq-answer {{ (isset($item['open']) && $item['open']) ? 'active' : '' }}">
                <div class="prose dark:prose-invert max-w-none">
                    @if(!empty($item['is_html']))
                        {!! $item['description'] ?? $item['content'] ?? '' !!}
                    @else
                        {!! nl2br(e($item['description'] ?? $item['content'] ?? '')) !!}
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
