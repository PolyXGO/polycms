@php
    $align = $attrs['alignment'] ?? 'left';
    $style = $attrs['style'] ?? 'primary';
    $size = $attrs['size'] ?? 'px-6 py-3 text-base';
    
    $wrapperClass = match($align) {
        'center' => 'landing-btn-wrap landing-btn-wrap--center',
        'right' => 'landing-btn-wrap landing-btn-wrap--right',
        'full' => 'landing-btn-wrap landing-btn-wrap--full',
        default => 'landing-btn-wrap landing-btn-wrap--left'
    };

    $styleClass = match($style) {
        'secondary' => 'landing-btn-secondary',
        'ghost' => 'landing-btn-ghost',
        'danger' => 'landing-btn-danger',
        'custom' => '',
        default => 'landing-btn-primary'
    };

    $inlineStyles = [];
    if ($style === 'custom') {
        // Border & Radius
        $inlineStyles[] = "--btn-radius: " . ($attrs['border_radius'] ?? '0.75rem');
        $inlineStyles[] = "--btn-border: " . ($attrs['border_color'] ?? 'transparent');
        $inlineStyles[] = "--btn-border-width: " . (($attrs['border_color'] ?? 'transparent') !== 'transparent' ? '2px' : '0px');
        $inlineStyles[] = "--btn-hover-border: " . ($attrs['hover_border_color'] ?? 'transparent');

        // Shadows
        $shadowColor = $attrs['shadow_color'] ?? 'rgba(0,0,0,0.08)';
        $shadowAngle = $attrs['shadow_angle'] ?? 135;
        
        $calcOffset = function($angle, $distance) {
            $rad = ($angle - 90) * (M_PI / 180);
            return [
                'x' => round(cos($rad) * $distance),
                'y' => round(sin($rad) * $distance)
            ];
        };

        if ($shadowColor !== 'transparent') {
            $offset = $calcOffset($shadowAngle, 10);
            $inlineStyles[] = "--btn-shadow: {$offset['x']}px {$offset['y']}px 15px -3px {$shadowColor}";
        } else {
            $inlineStyles[] = "--btn-shadow: none";
        }
        
        $hoverShadowColor = $attrs['hover_shadow_color'] ?? 'rgba(0,0,0,0.15)';
        $hoverShadowAngle = $attrs['hover_shadow_angle'] ?? 135;
        if ($hoverShadowColor !== 'transparent') {
            $hOffset = $calcOffset($hoverShadowAngle, 12);
            $inlineStyles[] = "--btn-hover-shadow: {$hOffset['x']}px {$hOffset['y']}px 25px -5px {$hoverShadowColor}";
        } else {
            $inlineStyles[] = "--btn-hover-shadow: none";
        }

        // Background Normal
        $bgType = $attrs['bg_type'] ?? 'solid';
        if ($bgType === 'solid') {
            $inlineStyles[] = "--btn-bg: " . ($attrs['bg_color'] ?? '#4f46e5');
        } else {
            $angle = $attrs['bg_gradient_angle'] ?? 135;
            $start = $attrs['bg_gradient_start'] ?? '#4f46e5';
            $end = $attrs['bg_gradient_end'] ?? '#7209b7';
            $inlineStyles[] = "--btn-bg: linear-gradient({$angle}deg, {$start} 0%, {$end} 100%)";
        }

        // Background Hover
        $hoverBgType = $attrs['hover_bg_type'] ?? 'solid';
        if ($hoverBgType === 'solid') {
            $inlineStyles[] = "--btn-hover-bg: " . ($attrs['hover_bg_color'] ?? '#4338ca');
        } else {
            $hAngle = $attrs['hover_bg_gradient_angle'] ?? 135;
            $hStart = $attrs['hover_bg_gradient_start'] ?? '#4338ca';
            $hEnd = $attrs['hover_bg_gradient_end'] ?? '#5b21b6';
            $inlineStyles[] = "--btn-hover-bg: linear-gradient({$hAngle}deg, {$hStart} 0%, {$hEnd} 100%)";
        }
    }

    $styleAttr = !empty($inlineStyles) ? 'style="' . implode('; ', $inlineStyles) . '"' : '';
@endphp

<div class="{{ $wrapperClass }}">
    <a 
        href="{{ $attrs['url'] ?? '#' }}" 
        target="{{ $attrs['target'] ?? '_self' }}"
        @if(!empty($attrs['rel'])) rel="{{ $attrs['rel'] }}" @endif
        class="landing-btn {{ $size }} {{ $styleClass }} {{ $align === 'full' ? 'landing-btn--full' : '' }}" 
        {!! $styleAttr !!}
    >
        {{ $attrs['label'] ?? 'Action' }}
    </a>
</div>
