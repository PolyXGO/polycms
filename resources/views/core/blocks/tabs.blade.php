@php
    $items = $attrs['items'] ?? [];
    $style = $attrs['style'] ?? 'underline';
    $alignment = $attrs['alignment'] ?? 'start';
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';
    $tabId = 'tab-' . uniqid();

    $alignClass = match($alignment) {
        'center' => 'justify-center',
        'end' => 'justify-end',
        default => 'justify-start',
    };

    $baseTabClass = "px-5 py-3 font-bold text-sm transition-all focus:outline-none whitespace-nowrap cursor-pointer";
    $activeTabClass = match($style) {
        'pills' => "bg-emerald-600 text-white rounded-xl shadow-lg",
        'blocks' => "bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 border-t-2 border-emerald-600 shadow-sm",
        default => "text-emerald-600 dark:text-emerald-400 border-b-2 border-emerald-600",
    };
    $inactiveTabClass = match($style) {
        'pills' => "text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200",
        'blocks' => "bg-slate-50 dark:bg-slate-900 text-slate-400 hover:text-slate-600 border-t-2 border-transparent",
        default => "text-slate-400 hover:text-slate-600 border-b-2 border-transparent",
    };

    $inlineStyles = [];
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

<div id="{{ $tabId }}" class="landing-tabs w-full my-6" style="{{ $styleAttr }}">
    <div class="flex {{ $alignClass }} {{ $style === 'blocks' ? 'bg-slate-50 dark:bg-slate-900 rounded-t-2xl overflow-hidden' : 'border-b border-slate-200 dark:border-slate-800 mb-6' }} gap-2 no-scrollbar overflow-x-auto">
        @foreach($items as $index => $item)
            <button 
                type="button"
                class="tab-trigger {{ $baseTabClass }} {{ $index === 0 ? $activeTabClass : $inactiveTabClass }}"
                role="tab"
                data-index="{{ $index }}"
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
                style="{{ $index === 0 ? 'display: block;' : 'display: none;' }}"
            >
                <div class="bg-white dark:bg-slate-900/60 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 prose dark:prose-invert max-w-none text-sm text-slate-700 dark:text-slate-200">
                    {!! nl2br(e($item['content'] ?? '')) !!}
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    (function() {
        const container = document.getElementById('{{ $tabId }}');
        if (!container) return;
        
        const triggers = container.querySelectorAll('.tab-trigger');
        const panels = container.querySelectorAll('.tab-panel');
        
        triggers.forEach(trigger => {
            trigger.addEventListener('click', () => {
                const index = parseInt(trigger.getAttribute('data-index'));
                
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
                
                panels.forEach(p => {
                    const isItem = parseInt(p.getAttribute('data-index')) === index;
                    p.style.display = isItem ? 'block' : 'none';
                });
            });
        });
    })();
});
</script>
