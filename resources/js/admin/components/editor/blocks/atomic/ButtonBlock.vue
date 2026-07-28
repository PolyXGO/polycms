<template>
    <div
        class="button-block-preview"
        :style="{ textAlign: state.alignment === 'center' ? 'center' : (state.alignment === 'right' ? 'right' : 'left') }"
    >
        <a 
            :key="animationKey"
            :href="state.url || 'javascript:void(0)'"
            :target="state.target || '_self'"
            :rel="state.rel || ''"
            class="landing-btn"
            :class="[
                state.alignment === 'full' ? 'landing-btn--full' : '',
                (state.style === 'primary' && state.bg_type === 'solid' && (!state.bg_color || state.bg_color === '#000000')) ? 'bg-black text-white dark:bg-white dark:text-black border border-black dark:border-white hover:bg-gray-800 dark:hover:bg-gray-200' : '',
                (state.style === 'secondary' && state.bg_type === 'solid' && (!state.bg_color || state.bg_color === '#ffffff')) ? 'bg-white text-black dark:bg-black dark:text-white border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900' : '',
                state.animation_type && state.animation_type !== 'none' ? `poly-animate poly-anim-${state.animation_type}` : ''
            ]"
            :style="dynamicStyles"
            @mouseenter="isHovering = true"
            @mouseleave="isHovering = false"
            @click="isEditor ? $event.preventDefault() : null"
        >
            {{ state.label || 'Action Button' }}
        </a>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch, ref, computed } from 'vue';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);
const isHovering = ref(false);

const state = reactive({
    label: props.modelValue?.label || props.data?.label || '',
    url: props.modelValue?.url || props.data?.url || '',
    target: props.modelValue?.target || props.data?.target || '_self',
    rel: props.modelValue?.rel || props.data?.rel || '',
    style: props.modelValue?.style || props.data?.style || 'primary',
    alignment: props.modelValue?.alignment || props.data?.alignment || 'left',
    
    // Style props
    bg_type: props.modelValue?.bg_type || props.data?.bg_type || 'solid',
    bg_color: props.modelValue?.bg_color || props.data?.bg_color || '#000000',
    bg_gradient_start: props.modelValue?.bg_gradient_start || props.data?.bg_gradient_start || '#4f46e5',
    bg_gradient_end: props.modelValue?.bg_gradient_end || props.data?.bg_gradient_end || '#7209b7',
    bg_gradient_angle: props.modelValue?.bg_gradient_angle || props.data?.bg_gradient_angle || 135,
    
    hover_bg_type: props.modelValue?.hover_bg_type || props.data?.hover_bg_type || 'solid',
    hover_bg_color: props.modelValue?.hover_bg_color || props.data?.hover_bg_color || '#333333',
    hover_bg_gradient_start: props.modelValue?.hover_bg_gradient_start || props.data?.hover_bg_gradient_start || '#4338ca',
    hover_bg_gradient_end: props.modelValue?.hover_bg_gradient_end || props.data?.hover_bg_gradient_end || '#5b21b6',
    hover_bg_gradient_angle: props.modelValue?.hover_bg_gradient_angle || props.data?.hover_bg_gradient_angle || 135,

    text_color: props.modelValue?.text_color || props.data?.text_color || '#ffffff',
    hover_text_color: props.modelValue?.hover_text_color || props.data?.hover_text_color || '#ffffff',
    
    border_color: props.modelValue?.border_color || props.data?.border_color || '#000000',
    hover_border_color: props.modelValue?.hover_border_color || props.data?.hover_border_color || '#000000',
    
    border_radius: props.modelValue?.border_radius || props.data?.border_radius || '6px',
    
    inner_padding_top: props.modelValue?.inner_padding_top ?? props.data?.inner_padding_top ?? 8,
    inner_padding_right: props.modelValue?.inner_padding_right ?? props.data?.inner_padding_right ?? 16,
    inner_padding_bottom: props.modelValue?.inner_padding_bottom ?? props.data?.inner_padding_bottom ?? 8,
    inner_padding_left: props.modelValue?.inner_padding_left ?? props.data?.inner_padding_left ?? 16,
    
    font_size: props.modelValue?.font_size || props.data?.font_size || '0.875rem',
    font_weight: props.modelValue?.font_weight || props.data?.font_weight || '500',

    // Animations
    animation_type: props.modelValue?.animation_type || props.data?.animation_type || 'none',
    animation_duration: props.modelValue?.animation_duration || props.data?.animation_duration || 1000,
    animation_delay: props.modelValue?.animation_delay || props.data?.animation_delay || 0,
    animation_infinite: props.modelValue?.animation_infinite || props.data?.animation_infinite || false,
});

const dynamicStyles = computed(() => {
    const styles: any = {
        borderRadius: state.border_radius,
        padding: `${state.inner_padding_top}px ${state.inner_padding_right}px ${state.inner_padding_bottom}px ${state.inner_padding_left}px`,
        fontSize: state.font_size,
        fontWeight: state.font_weight,
        display: state.alignment === 'full' ? 'flex' : 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        textDecoration: 'none',
        transition: 'all 0.2s ease',
        cursor: 'pointer',
        width: state.alignment === 'full' ? '100%' : undefined
    };
    
    const isDefaultPrimary = state.style === 'primary' && state.bg_type === 'solid' && (!state.bg_color || state.bg_color === '#000000');
    const isDefaultSecondary = state.style === 'secondary' && state.bg_type === 'solid' && (!state.bg_color || state.bg_color === '#ffffff');

    if (isDefaultPrimary) {
        // Tailwind classes applied via template
    } else if (isDefaultSecondary) {
        // Tailwind classes applied via template
    } else {
        // Custom Style Background (handles 'custom' or seeded presets that have 'primary' but with custom colors)
        if (!isHovering.value) {
            if (state.bg_type === 'solid') {
                styles.background = state.bg_color || '#000000';
            } else {
                styles.background = `linear-gradient(${state.bg_gradient_angle}deg, ${state.bg_gradient_start} 0%, ${state.bg_gradient_end} 100%)`;
            }
            styles.color = state.text_color || '#ffffff';
            styles.border = `1px solid ${state.border_color || 'transparent'}`;
        } else {
            if (state.hover_bg_type === 'solid') {
                styles.background = state.hover_bg_color || '#333333';
            } else {
                styles.background = `linear-gradient(${state.hover_bg_gradient_angle}deg, ${state.hover_bg_gradient_start} 0%, ${state.hover_bg_gradient_end} 100%)`;
            }
            styles.color = state.hover_text_color || state.text_color || '#ffffff';
            styles.border = `1px solid ${state.hover_border_color || state.border_color || 'transparent'}`;
        }
    }

    if (state.animation_type && state.animation_type !== 'none') {
        styles['--poly-anim-duration'] = `${state.animation_duration}ms`;
        styles['--poly-anim-delay'] = `${state.animation_delay}ms`;
        if (state.animation_infinite) {
            styles['animationIterationCount'] = 'infinite';
        }
    }

    return styles;
});

const animationKey = computed(() => {
    return `${state.animation_type}-${state.animation_duration}-${state.animation_delay}-${state.animation_infinite}`;
});

// Sync internal state when props change (for preview reactivity)
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        state.label = newVal.label || '';
        state.url = newVal.url || '';
        state.target = newVal.target || '_self';
        state.rel = newVal.rel || '';
        state.style = newVal.style || 'primary';
        state.alignment = newVal.alignment || 'left';
        
        state.bg_type = newVal.bg_type || 'solid';
        state.bg_color = newVal.bg_color || '#000000';
        state.bg_gradient_start = newVal.bg_gradient_start || '#4f46e5';
        state.bg_gradient_end = newVal.bg_gradient_end || '#7209b7';
        state.bg_gradient_angle = newVal.bg_gradient_angle || 135;

        state.hover_bg_type = newVal.hover_bg_type || 'solid';
        state.hover_bg_color = newVal.hover_bg_color || '#333333';
        state.hover_bg_gradient_start = newVal.hover_bg_gradient_start || '#4338ca';
        state.hover_bg_gradient_end = newVal.hover_bg_gradient_end || '#5b21b6';
        state.hover_bg_gradient_angle = newVal.hover_bg_gradient_angle || 135;

        state.text_color = newVal.text_color || '#ffffff';
        state.hover_text_color = newVal.hover_text_color || '#ffffff';

        state.border_color = newVal.border_color || '#000000';
        state.hover_border_color = newVal.hover_border_color || '#000000';

        state.border_radius = newVal.border_radius || '6px';
        
        state.inner_padding_top = newVal.inner_padding_top ?? 8;
        state.inner_padding_right = newVal.inner_padding_right ?? 16;
        state.inner_padding_bottom = newVal.inner_padding_bottom ?? 8;
        state.inner_padding_left = newVal.inner_padding_left ?? 16;
        
        state.font_size = newVal.font_size || '0.875rem';
        state.font_weight = newVal.font_weight || '500';

        state.animation_type = newVal.animation_type || 'none';
        state.animation_duration = newVal.animation_duration || 1000;
        state.animation_delay = newVal.animation_delay || 0;
        state.animation_infinite = newVal.animation_infinite || false;
    }
}, { deep: true, immediate: true });
</script>
