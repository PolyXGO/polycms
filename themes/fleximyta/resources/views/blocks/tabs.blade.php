@php
    $items = $attrs['items'] ?? [];
    $style = $attrs['style'] ?? 'underline'; // underline, pills, blocks
    $alignment = $attrs['alignment'] ?? 'start'; // start, center, end
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';
    $tabId = 'tab-' . uniqid();

    $alignClass = match($alignment) {
        'center' => 'justify-center',
        'end' => 'justify-end',
        default => 'justify-start',
    };

    $baseTabClass = "px-6 py-4 font-bold text-sm transition-all focus:outline-none whitespace-nowrap";
    $activeTabClass = match($style) {
        'pills' => "bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none",
        'blocks' => "bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border-t-2 border-indigo-600",
        default => "text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600",
    };
    $inactiveTabClass = match($style) {
        'pills' => "text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200",
        'blocks' => "bg-gray-50 dark:bg-gray-900 text-gray-400 hover:text-gray-600 border-t-2 border-transparent",
        default => "text-gray-400 hover:text-gray-600 border-b-2 border-transparent",
    };

    $inlineStyles = [];
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

<div id="{{ $tabId }}" class="landing-tabs {{ !$padding ? 'py-8' : '' }} max-w-6xl mx-auto px-4" x-data="{ activeTab: 0 }" style="{{ $styleAttr }}">
    <div class="flex {{ $alignClass }} {{ $style === 'blocks' ? 'bg-gray-50 dark:bg-gray-900 rounded-t-2xl overflow-hidden' : 'border-b border-gray-100 dark:border-gray-700 mb-8' }} gap-2 no-scrollbar overflow-x-auto">
        @foreach($items as $index => $item)
            <button 
                type="button"
                class="tab-trigger {{ $baseTabClass }}"
                role="tab"
                data-index="{{ $index }}"
                :class="activeTab === {{ $index }} ? '{{ $activeTabClass }}' : '{{ $inactiveTabClass }}'"
                @click="activeTab = {{ $index }}"
            >
                {{ $item['title'] ?? 'Tab ' . ($index + 1) }}
            </button>
        @endforeach
    </div>

    <div class="tab-content-wrapper">
        @foreach($items as $index => $item)
            <div 
                class="tab-panel transition-all duration-300"
                role="tabpanel"
                data-index="{{ $index }}"
                x-show="activeTab === {{ $index }}"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                style="{{ $index === 0 ? '' : 'display: none;' }}"
            >
                <div class="bg-white dark:bg-gray-800 p-8 rounded-b-2xl {{ $style === 'blocks' ? 'border border-t-0 border-gray-100 dark:border-gray-700' : '' }} prose dark:prose-invert max-w-none">
                    {!! nl2br(e($item['content'] ?? '')) !!}
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
(function() {
    // Simple vanilla JS fallback if Alpine isn't present
    const container = document.getElementById('{{ $tabId }}');
    if (!container) return;
    
    const triggers = container.querySelectorAll('.tab-trigger');
    const panels = container.querySelectorAll('.tab-panel');
    
    triggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const index = parseInt(trigger.getAttribute('data-index'));
            
            // Update triggers
            triggers.forEach(t => {
                const isItem = parseInt(t.getAttribute('data-index')) === index;
                const activeClasses = '{{ $activeTabClass }}'.split(' ');
                const inactiveClasses = '{{ $inactiveTabClass }}'.split(' ');
                
                if (isItem) {
                    t.classList.remove(...inactiveClasses);
                    t.classList.add(...activeClasses);
                } else {
                    t.classList.remove(...activeClasses);
                    t.classList.add(...inactiveClasses);
                }
            });
            
            // Update panels
            panels.forEach(p => {
                const isItem = parseInt(p.getAttribute('data-index')) === index;
                p.style.display = isItem ? 'block' : 'none';
                if (isItem) {
                    p.classList.remove('opacity-0', 'translate-y-4');
                    p.classList.add('opacity-100', 'translate-y-0');
                }
            });
        });
    });
})();
</script>
