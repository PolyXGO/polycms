@php
    $legacyColumns = (int) ($attrs['columns'] ?? 2);
    $columnWidths = $attrs['column_widths'] ?? null;
    if (!is_array($columnWidths) || empty($columnWidths)) {
        $columnWidths = match($legacyColumns) {
            1 => ['1'],
            3 => ['1/3', '1/3', '1/3'],
            4 => ['1/4', '1/4', '1/4', '1/4'],
            default => ['1/2', '1/2'],
        };
    }

    $columns = count($columnWidths);
    $gap = $attrs['gap'] ?? 'gap-8';
    $verticalAlign = $attrs['vertical_align'] ?? 'start';
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';

    $gapValue = match($gap) {
        'gap-0' => '0px',
        'gap-4' => '1rem',
        'gap-16' => '4rem',
        default => '2rem',
    };

    $alignValue = match($verticalAlign) {
        'center' => 'center',
        'end' => 'end',
        default => 'start',
    };

    $templateColumns = implode(' ', array_map(function ($width) {
        $value = (string) $width;

        if ($value === '1') {
            return 'minmax(0, 1fr)';
        }

        if (str_contains($value, '/')) {
            [$numerator, $denominator] = array_map('floatval', explode('/', $value, 2));
            $ratio = $denominator > 0 ? $numerator / $denominator : 1;
            $ratio = rtrim(rtrim(number_format($ratio, 4, '.', ''), '0'), '.');

            return "minmax(0, {$ratio}fr)";
        }

        return 'minmax(0, 1fr)';
    }, $columnWidths));

    $inlineStyles = [];
    $inlineStyles[] = "--landing-row-template: {$templateColumns}";
    $inlineStyles[] = "--landing-row-gap: {$gapValue}";
    $inlineStyles[] = "align-items: {$alignValue}";
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

<div class="landing-row {{ !$padding ? 'landing-row--default-padding' : '' }}" style="{{ $styleAttr }}">
    @for($i = 0; $i < $columns; $i++)
        <div class="landing-column">
            @php
                $columnBlocks = $attrs['columns_data'][$i]['blocks'] ?? [];
                $columnHtml = !empty($columnBlocks) ? $renderer->render($columnBlocks) : '';
            @endphp
            {!! $columnHtml !!}
        </div>
    @endfor
</div>
