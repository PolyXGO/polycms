<template>
    <!-- Settings Mode -->
    <div v-if="mode === 'settings'" class="button-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Button Label</label>
            <input v-model="state.label" type="text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Click here">
        </div>

        <LinkSettings 
            v-model:url="state.url" 
            v-model:target="state.target" 
            v-model:rel="state.rel" 
        />

        <div class="grid grid-cols-2 gap-4">
            <ButtonStylePicker v-model="state.style" label="Button Style" />
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Size</label>
                <select v-model="state.size" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="px-3 py-1.5 text-xs">Small</option>
                    <option value="px-4 py-2 text-sm">Medium</option>
                    <option value="px-6 py-3 text-base">Large</option>
                    <option value="px-8 py-4 text-lg">Extra Large</option>
                </select>
            </div>
        </div>

        <!-- Custom Style Options -->
        <div v-if="state.style === 'custom'" class="space-y-4 rounded-2xl border border-gray-200 bg-gray-50/80 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/40">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h4 class="text-[10px] font-bold uppercase tracking-[0.22em] text-indigo-500">Advanced Appearance</h4>
                    <p class="mt-1 text-[11px] leading-5 text-gray-500 dark:text-gray-400">Tune normal and hover styles using the same control language as the core admin panel.</p>
                </div>
                <div class="inline-flex shrink-0 gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <button type="button" @click="activeTab = 'normal'" :class="activeTab === 'normal' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200'" class="rounded-lg px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition-all">Normal</button>
                    <button type="button" @click="activeTab = 'hover'" :class="activeTab === 'hover' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200'" class="rounded-lg px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition-all">Hover</button>
                </div>
            </div>

            <!-- Normal State Tabs -->
            <div v-if="activeTab === 'normal'" class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white/80 p-4 dark:border-gray-700 dark:bg-gray-950/30">
                    <div class="form-group">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Background</label>
                        <select v-model="state.bg_type" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="solid">Solid</option>
                            <option value="gradient">Gradient</option>
                        </select>
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <ColorPicker 
                                :modelValue="state.bg_type === 'solid' ? state.bg_color : state.bg_gradient_start" 
                                @update:modelValue="(val: string) => { 
                                    if (state.bg_type === 'solid') state.bg_color = val; 
                                    else state.bg_gradient_start = val;
                                }" 
                            />
                            <template v-if="state.bg_type === 'gradient'">
                                <span class="text-gray-300 dark:text-gray-600">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </span>
                                <ColorPicker v-model="state.bg_gradient_end" />
                                <AnglePicker v-model="state.bg_gradient_angle" />
                            </template>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white/80 p-4 space-y-4 dark:border-gray-700 dark:bg-gray-950/30">
                    <ColorPicker v-model="state.border_color" label="Border Color" />
                    <RangeSlider v-model="state.border_radius_num" label="Corner Radius" :max="50" />
                    <div class="flex flex-wrap items-end gap-3">
                        <ColorPicker v-model="state.shadow_color" label="Shadow Color" />
                        <AnglePicker v-model="state.shadow_angle" label="Shadow Angle" />
                    </div>
                </div>
            </div>

            <!-- Hover State Tabs -->
            <div v-else class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white/80 p-4 dark:border-gray-700 dark:bg-gray-950/30">
                    <div class="form-group">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Hover Background</label>
                        <select v-model="state.hover_bg_type" @change="markHoverTouched('bg_type')" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="solid">Solid</option>
                            <option value="gradient">Gradient</option>
                        </select>

                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <ColorPicker 
                                :modelValue="state.hover_bg_type === 'solid' ? state.hover_bg_color : state.hover_bg_gradient_start" 
                                @update:modelValue="(val: string) => { 
                                    if (state.hover_bg_type === 'solid') { state.hover_bg_color = val; markHoverTouched('bg_color'); } 
                                    else { state.hover_bg_gradient_start = val; markHoverTouched('bg_gradient_start'); }
                                }" 
                            />
                            <template v-if="state.hover_bg_type === 'gradient'">
                                <span class="text-gray-300 dark:text-gray-600">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </span>
                                <ColorPicker v-model="state.hover_bg_gradient_end" @update:modelValue="markHoverTouched('bg_gradient_end')" />
                                <AnglePicker v-model="state.hover_bg_gradient_angle" @update:modelValue="markHoverTouched('bg_gradient_angle')" />
                            </template>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white/80 p-4 space-y-4 dark:border-gray-700 dark:bg-gray-950/30">
                    <ColorPicker v-model="state.hover_border_color" label="Hover Border" @update:modelValue="markHoverTouched('border_color')" />
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-3 py-2 text-[11px] italic text-gray-500 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-400">Corner radius spans both states.</div>
                    <div class="flex flex-wrap items-end gap-3">
                        <ColorPicker v-model="state.hover_shadow_color" label="Hover Shadow" @update:modelValue="markHoverTouched('shadow_color')" />
                        <AnglePicker v-model="state.hover_shadow_angle" label="Shadow Angle" @update:modelValue="markHoverTouched('shadow_angle')" />
                    </div>
                </div>
            </div>
        </div>

        <AlignmentPicker v-model="state.alignment" label="Alignment" />
    </div>

    <!-- Preview Mode -->
    <div
        v-else
        class="button-block-preview"
        :class="[
            state.alignment === 'full'
                ? 'w-full'
                : state.alignment === 'center'
                    ? 'text-center'
                    : state.alignment === 'right'
                        ? 'text-right'
                        : 'text-left'
        ]"
    >
        <a 
            :href="state.url || 'javascript:void(0)'"
            :target="state.target || '_self'"
            :rel="state.rel || ''"
            class="landing-btn"
            :class="[
                state.size,
                state.style === 'primary' ? 'landing-btn-primary' : 
                state.style === 'secondary' ? 'landing-btn-secondary' : 
                state.style === 'ghost' ? 'landing-btn-ghost' : 
                state.style === 'danger' ? 'landing-btn-danger' : '',
                state.alignment === 'full' ? 'w-full' : ''
            ]"
            :style="dynamicStyles"
            @mouseenter="isHovering = true"
            @mouseleave="isHovering = false"
        >
            {{ state.label || 'Action Button' }}
        </a>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch, ref, computed } from 'vue';
import ColorPicker from '../../controls/ColorPicker.vue';
import AnglePicker from '../../controls/AnglePicker.vue';
import RangeSlider from '../../controls/RangeSlider.vue';
import AlignmentPicker from '../../controls/AlignmentPicker.vue';
import ButtonStylePicker from '../../controls/ButtonStylePicker.vue';
import LinkSettings from '../../controls/LinkSettings.vue';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);
const activeTab = ref('normal');
const isHovering = ref(false);

const state = reactive({
    label: props.modelValue?.label || props.data?.label || '',
    url: props.modelValue?.url || props.data?.url || '',
    target: props.modelValue?.target || props.data?.target || '_self',
    rel: props.modelValue?.rel || props.data?.rel || '',
    style: props.modelValue?.style || props.data?.style || 'primary',
    size: props.modelValue?.size || props.data?.size || 'px-6 py-3 text-base',
    alignment: props.modelValue?.alignment || props.data?.alignment || 'left',
    
    // Advanced Styles
    bg_type: props.modelValue?.bg_type || props.data?.bg_type || 'solid',
    bg_color: props.modelValue?.bg_color || props.data?.bg_color || '#4f46e5',
    bg_gradient_start: props.modelValue?.bg_gradient_start || props.data?.bg_gradient_start || '#4f46e5',
    bg_gradient_end: props.modelValue?.bg_gradient_end || props.data?.bg_gradient_end || '#7209b7',
    bg_gradient_angle: props.modelValue?.bg_gradient_angle || props.data?.bg_gradient_angle || 135,
    
    hover_bg_type: props.modelValue?.hover_bg_type || props.data?.hover_bg_type || 'solid',
    hover_bg_color: props.modelValue?.hover_bg_color || props.data?.hover_bg_color || '#4338ca',
    hover_bg_gradient_start: props.modelValue?.hover_bg_gradient_start || props.data?.hover_bg_gradient_start || '#4338ca',
    hover_bg_gradient_end: props.modelValue?.hover_bg_gradient_end || props.data?.hover_bg_gradient_end || '#5b21b6',
    hover_bg_gradient_angle: props.modelValue?.hover_bg_gradient_angle || props.data?.hover_bg_gradient_angle || 135,
    
    border_color: props.modelValue?.border_color || props.data?.border_color || 'transparent',
    border_radius: props.modelValue?.border_radius || props.data?.border_radius || '0.75rem',
    border_radius_num: parseInt(props.modelValue?.border_radius || props.data?.border_radius || '12'),
    shadow_color: props.modelValue?.shadow_color || props.data?.shadow_color || 'rgba(0,0,0,0.08)',
    shadow_angle: props.modelValue?.shadow_angle || props.data?.shadow_angle || 135,
    hover_border_color: props.modelValue?.hover_border_color || props.data?.hover_border_color || 'transparent',
    hover_shadow_color: props.modelValue?.hover_shadow_color || props.data?.hover_shadow_color || 'rgba(0,0,0,0.15)',
    hover_shadow_angle: props.modelValue?.hover_shadow_angle || props.data?.hover_shadow_angle || 135,
});

// To track if the user has manually changed hover properties
const touchedHover = reactive({
    bg_type: !!(props.modelValue?.hover_bg_type || props.data?.hover_bg_type),
    bg_color: !!(props.modelValue?.hover_bg_color || props.data?.hover_bg_color),
    bg_gradient_start: !!(props.modelValue?.hover_bg_gradient_start || props.data?.hover_bg_gradient_start),
    bg_gradient_end: !!(props.modelValue?.hover_bg_gradient_end || props.data?.hover_bg_gradient_end),
    bg_gradient_angle: !!(props.modelValue?.hover_bg_gradient_angle || props.data?.hover_bg_gradient_angle),
    border_color: !!(props.modelValue?.hover_border_color || props.data?.hover_border_color),
    shadow_color: !!(props.modelValue?.hover_shadow_color || props.data?.hover_shadow_color),
    shadow_angle: !!(props.modelValue?.hover_shadow_angle || props.data?.hover_shadow_angle),
});

const markHoverTouched = (property: keyof typeof touchedHover) => {
    touchedHover[property] = true;
};

watch(() => state.border_radius_num, (newVal) => {
    state.border_radius = `${newVal}px`;
});

// Inheritance watchers
watch(() => state.bg_type, (newVal) => { if (!touchedHover.bg_type) state.hover_bg_type = newVal; });
watch(() => state.bg_color, (newVal) => { if (!touchedHover.bg_color) state.hover_bg_color = newVal; });
watch(() => state.bg_gradient_start, (newVal) => { if (!touchedHover.bg_gradient_start) state.hover_bg_gradient_start = newVal; });
watch(() => state.bg_gradient_end, (newVal) => { if (!touchedHover.bg_gradient_end) state.hover_bg_gradient_end = newVal; });
watch(() => state.bg_gradient_angle, (newVal) => { if (!touchedHover.bg_gradient_angle) state.hover_bg_gradient_angle = newVal; });
watch(() => state.border_color, (newVal) => { if (!touchedHover.border_color) state.hover_border_color = newVal; });
watch(() => state.shadow_color, (newVal) => { if (!touchedHover.shadow_color) state.hover_shadow_color = newVal; });
watch(() => state.shadow_angle, (newVal) => { if (!touchedHover.shadow_angle) state.hover_shadow_angle = newVal; });

const dynamicStyles = computed(() => {
    const styles: any = {};
    
    if (state.style === 'custom') {
        const radius = state.border_radius_num + 'px';
        styles['--btn-radius'] = radius;
        styles['--btn-border'] = state.border_color;
        styles['--btn-border-width'] = state.border_color !== 'transparent' ? '2px' : '0px';
        
        // Calculate Shadow Offsets
        const calcOffset = (angle: number, distance: number) => {
            const rad = (angle - 90) * (Math.PI / 180);
            return {
                x: Math.round(Math.cos(rad) * distance),
                y: Math.round(Math.sin(rad) * distance)
            };
        };

        const shadowOffset = calcOffset(state.shadow_angle, 10);
        const shadowStr = `${shadowOffset.x}px ${shadowOffset.y}px 15px -3px ${state.shadow_color}`;
        styles['--btn-shadow'] = state.shadow_color !== 'transparent' ? shadowStr : 'none';
        
        const hShadowOffset = calcOffset(state.hover_shadow_angle, 12);
        const hShadowStr = `${hShadowOffset.x}px ${hShadowOffset.y}px 25px -5px ${state.hover_shadow_color}`;
        styles['--btn-hover-shadow'] = state.hover_shadow_color !== 'transparent' ? hShadowStr : 'none';
        
        // Direct styles for editor preview
        styles.borderRadius = radius;
        styles.borderWidth = state.border_color !== 'transparent' ? '2px' : '0px';
        styles.borderColor = isHovering.value ? (state.hover_border_color !== 'transparent' ? state.hover_border_color : state.border_color) : state.border_color;
        styles.borderStyle = 'solid';
        styles.boxShadow = isHovering.value ? styles['--btn-hover-shadow'] : styles['--btn-shadow'];

        // Background
        if (!isHovering.value) {
            if (state.bg_type === 'solid') {
                styles.background = state.bg_color;
            } else {
                styles.background = `linear-gradient(${state.bg_gradient_angle}deg, ${state.bg_gradient_start} 0%, ${state.bg_gradient_end} 100%)`;
            }
        } else {
            if (state.hover_bg_type === 'solid') {
                styles.background = state.hover_bg_color;
            } else {
                styles.background = `linear-gradient(${state.hover_bg_gradient_angle}deg, ${state.hover_bg_gradient_start} 0%, ${state.hover_bg_gradient_end} 100%)`;
            }
            styles.transform = 'translateY(-2px)';
        }

        // Set variables for CSS transition consistency
        styles['--btn-bg'] = state.bg_type === 'solid' ? state.bg_color : `linear-gradient(${state.bg_gradient_angle}deg, ${state.bg_gradient_start} 0%, ${state.bg_gradient_end} 100%)`;
        styles['--btn-hover-bg'] = state.hover_bg_type === 'solid' ? state.hover_bg_color : `linear-gradient(${state.hover_bg_gradient_angle}deg, ${state.hover_bg_gradient_start} 0%, ${state.hover_bg_gradient_end} 100%)`;
    }

    styles.display = state.alignment === 'full' ? 'flex' : 'inline-flex';
    styles.alignItems = 'center';
    styles.justifyContent = 'center';
    styles.textAlign = 'center';
    styles.width = state.alignment === 'full' ? '100%' : undefined;
    
    return styles;
});

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', { ...props.modelValue, ...newValue });
    }
}, { deep: true });

// Sync internal state when props change (for preview reactivity)
watch(() => props.modelValue, (newVal) => {
    if (props.mode === 'preview' && newVal) {
        state.label = newVal.label || '';
        state.url = newVal.url || '';
        state.target = newVal.target || '_self';
        state.rel = newVal.rel || '';
        state.style = newVal.style || 'primary';
        state.size = newVal.size || 'px-6 py-3 text-base';
        state.alignment = newVal.alignment || 'left';
        
        state.bg_type = newVal.bg_type || 'solid';
        state.bg_color = newVal.bg_color || '#4f46e5';
        state.bg_gradient_start = newVal.bg_gradient_start || '#4f46e5';
        state.bg_gradient_end = newVal.bg_gradient_end || '#7209b7';
        state.bg_gradient_angle = newVal.bg_gradient_angle || 135;
        
        state.hover_bg_type = newVal.hover_bg_type || 'solid';
        state.hover_bg_color = newVal.hover_bg_color || '#4338ca';
        state.hover_bg_gradient_start = newVal.hover_bg_gradient_start || '#4338ca';
        state.hover_bg_gradient_end = newVal.hover_bg_gradient_end || '#5b21b6';
        state.hover_bg_gradient_angle = newVal.hover_bg_gradient_angle || 135;
        
        state.border_color = newVal.border_color || 'transparent';
        state.border_radius = newVal.border_radius || '0.75rem';
        state.border_radius_num = parseInt(newVal.border_radius || '12');
        state.shadow_color = newVal.shadow_color || 'rgba(0,0,0,0.08)';
        state.shadow_angle = newVal.shadow_angle || 135;
        state.hover_border_color = newVal.hover_border_color || 'transparent';
        state.hover_shadow_color = newVal.hover_shadow_color || 'rgba(0,0,0,0.15)';
        state.hover_shadow_angle = newVal.hover_shadow_angle || 135;
    }
}, { deep: true, immediate: true });
</script>
