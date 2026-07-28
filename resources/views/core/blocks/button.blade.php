@php
    $align = $attrs['alignment'] ?? 'left';
    $style = $attrs['style'] ?? 'primary';
    
    // Core structure classes
    $wrapperClass = match($align) {
        'center' => 'landing-btn-wrap landing-btn-wrap--center',
        'right' => 'landing-btn-wrap landing-btn-wrap--right',
        'full' => 'landing-btn-wrap landing-btn-wrap--full',
        default => 'landing-btn-wrap landing-btn-wrap--left'
    };

    $btnClasses = ['landing-btn'];
    
    if ($align === 'full') {
        $btnClasses[] = 'landing-btn--full';
    }

    // Process Animations
    $inlineStyles = [];
    
    // Add Vercel aesthetic core inline styles by default if not set
    // Vercel Primary: bg-black, text-white, rounded-md (6px)
    // Vercel Secondary: bg-white, text-black, border-gray-200
    
    $radius = $attrs['border_radius'] ?? '6px';
    
    $pt = $attrs['inner_padding_top'] ?? 8;
    $pr = $attrs['inner_padding_right'] ?? 16;
    $pb = $attrs['inner_padding_bottom'] ?? 8;
    $pl = $attrs['inner_padding_left'] ?? 16;
    $padding = "{$pt}px {$pr}px {$pb}px {$pl}px";

    $fontSize = $attrs['font_size'] ?? '0.875rem';
    $fontWeight = $attrs['font_weight'] ?? '500';

    $inlineStyles[] = "border-radius: {$radius}";
    $inlineStyles[] = "padding: {$padding}";
    $inlineStyles[] = "font-size: {$fontSize}";
    $inlineStyles[] = "font-weight: {$fontWeight}";
    $inlineStyles[] = "display: inline-block";
    $inlineStyles[] = "text-decoration: none";
    $inlineStyles[] = "transition: all 0.2s ease";
    
    $isDefaultPrimary = $style === 'primary' && ($attrs['bg_type'] ?? 'solid') === 'solid' && empty($attrs['bg_color']) || ($attrs['bg_color'] ?? '') === '#000000';
    $isDefaultSecondary = $style === 'secondary' && ($attrs['bg_type'] ?? 'solid') === 'solid' && empty($attrs['bg_color']) || ($attrs['bg_color'] ?? '') === '#ffffff';

    if ($isDefaultPrimary) {
        $btnClasses[] = 'bg-black text-white dark:bg-white dark:text-black border border-black dark:border-white hover:bg-gray-800 dark:hover:bg-gray-200';
    } elseif ($isDefaultSecondary) {
        $btnClasses[] = 'bg-white text-black dark:bg-black dark:text-white border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900';
    } else {
        // Custom style with gradients
        $bgType = $attrs['bg_type'] ?? 'solid';
        if ($bgType === 'solid') {
            $bgColor = $attrs['bg_color'] ?? 'transparent';
            $inlineStyles[] = "background: {$bgColor}";
            $inlineStyles[] = "--poly-btn-bg: {$bgColor}";
        } else {
            $angle = $attrs['bg_gradient_angle'] ?? 135;
            $start = $attrs['bg_gradient_start'] ?? '#4f46e5';
            $end = $attrs['bg_gradient_end'] ?? '#7209b7';
            $bg = "linear-gradient({$angle}deg, {$start} 0%, {$end} 100%)";
            $inlineStyles[] = "background: {$bg}";
            $inlineStyles[] = "--poly-btn-bg: {$bg}";
        }

        $hoverBgType = $attrs['hover_bg_type'] ?? 'solid';
        if ($hoverBgType === 'solid') {
            $hoverBgColor = $attrs['hover_bg_color'] ?? 'transparent';
            $inlineStyles[] = "--poly-btn-hover-bg: {$hoverBgColor}";
        } else {
            $hAngle = $attrs['hover_bg_gradient_angle'] ?? 135;
            $hStart = $attrs['hover_bg_gradient_start'] ?? '#4338ca';
            $hEnd = $attrs['hover_bg_gradient_end'] ?? '#5b21b6';
            $hBg = "linear-gradient({$hAngle}deg, {$hStart} 0%, {$hEnd} 100%)";
            $inlineStyles[] = "--poly-btn-hover-bg: {$hBg}";
        }

        $textColor = $attrs['text_color'] ?? 'inherit';
        $borderColor = $attrs['border_color'] ?? 'transparent';
        $hoverTextColor = $attrs['hover_text_color'] ?? $textColor;
        $hoverBorderColor = $attrs['hover_border_color'] ?? $borderColor;

        $inlineStyles[] = "color: {$textColor}";
        $inlineStyles[] = "border: 1px solid {$borderColor}";
        $inlineStyles[] = "--poly-btn-text: {$textColor}";
        $inlineStyles[] = "--poly-btn-hover-text: {$hoverTextColor}";
        $inlineStyles[] = "--poly-btn-border: {$borderColor}";
        $inlineStyles[] = "--poly-btn-hover-border: {$hoverBorderColor}";
        $btnClasses[] = 'poly-btn-dynamic-hover';
    }

    $styleAttr = !empty($inlineStyles) ? 'style="' . implode('; ', $inlineStyles) . '"' : '';

    // Handle animations specifically for the button element if configured on the button
    // Note: The global post-renderer handles block-level animations, but if the button itself
    // is configured with animation, we apply it here too.
    if (!empty($attrs['animation_type']) && $attrs['animation_type'] !== 'none') {
        $btnClasses[] = 'poly-animate';
        $btnClasses[] = 'poly-anim-' . $attrs['animation_type'];
        
        $animStyle = [];
        if (!empty($attrs['animation_duration'])) {
            $animStyle[] = "--poly-anim-duration: {$attrs['animation_duration']}ms";
        }
        if (!empty($attrs['animation_delay'])) {
            $animStyle[] = "--poly-anim-delay: {$attrs['animation_delay']}ms";
        }
        if (!empty($attrs['animation_infinite'])) {
            $animStyle[] = "animation-iteration-count: infinite";
        }
        if (!empty($animStyle)) {
            $styleAttr = 'style="' . implode('; ', array_merge($inlineStyles, $animStyle)) . '"';
        }
    }
@endphp

<div class="{{ $wrapperClass }}" style="{{ $align === 'center' ? 'text-align: center;' : ($align === 'right' ? 'text-align: right;' : 'text-align: left;') }}">
    <a 
        href="{{ $attrs['url'] ?? '#' }}" 
        target="{{ get_link_target($attrs['url'] ?? '#', $attrs['target'] ?? '_self') }}"
        @if(!empty($attrs['rel'])) rel="{{ $attrs['rel'] }}" @endif
        class="{{ implode(' ', $btnClasses) }}" 
        {!! $styleAttr !!}
    >
        {{ $attrs['label'] ?? 'Action' }}
    </a>
</div>
