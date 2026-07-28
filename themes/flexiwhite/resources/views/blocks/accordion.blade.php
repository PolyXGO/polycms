@php
    $items = $attrs['items'] ?? [];
    $style = $attrs['style'] ?? 'standard';
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';

    $inlineStyles = [];
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

<div class="landing-block-accordion {{ !$padding ? 'py-12' : '' }}">
    <x-accordion :items="$items" :style="$style" style="{{ $styleAttr }}" />
</div>

<style>
.landing-block-accordion .faq-container { display: grid; gap: 10px; }
.landing-block-accordion .faq-item { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff; }
.landing-block-accordion .faq-question { display: flex; align-items: center; justify-content: space-between; gap: 12px; cursor: pointer; padding: 12px 14px; font-weight: 600; color: #0f172a; }
.landing-block-accordion .faq-answer { display: none; padding: 0 14px 14px; color: #475569; }
.landing-block-accordion .faq-answer.active { display: block; }
.landing-block-accordion .faq-question i { transition: transform .2s ease; }
.landing-block-accordion .faq-item.is-open .faq-question i { transform: rotate(180deg); }
</style>
<script>
(() => {
  document.querySelectorAll('.landing-block-accordion .faq-item').forEach((item) => {
    const q = item.querySelector('.faq-question');
    const a = item.querySelector('.faq-answer');
    if (!q || !a) return;
    if (a.classList.contains('active')) item.classList.add('is-open');
    q.addEventListener('click', () => {
      const willOpen = !a.classList.contains('active');
      a.classList.toggle('active', willOpen);
      item.classList.toggle('is-open', willOpen);
    });
  });
})();
</script>
