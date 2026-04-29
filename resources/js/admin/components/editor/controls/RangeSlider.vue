<template>
    <div class="form-group mb-0">
        <div class="flex items-center justify-between mb-1">
            <label v-if="label" class="mb-0 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ label }}</label>
            <span class="inline-flex min-w-[3rem] items-center justify-center rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">{{ modelValue }}{{ unit }}</span>
        </div>
        <div class="spacing-slider-container">
            <input 
                type="range" 
                :min="min" 
                :max="max" 
                :step="step" 
                :value="modelValue" 
                @input="$emit('update:modelValue', parseInt(($event.target as HTMLInputElement).value))" 
                class="spacing-slider"
                :style="{ '--range-percent': `${percentage}%` }"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    modelValue: number;
    label?: string;
    min?: number;
    max?: number;
    step?: number;
    unit?: string;
}>(), {
    min: 0,
    max: 100,
    step: 1,
    unit: 'px'
});

defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();

const percentage = computed(() => {
    const range = props.max - props.min;
    if (range <= 0) return 0;
    return ((props.modelValue - props.min) / range) * 100;
});
</script>

<style scoped>
.spacing-slider-container {
    padding: 0.4rem 0 0.25rem;
}

.spacing-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 8px;
    border-radius: 9999px;
    background: linear-gradient(to right, #6366f1 0%, #6366f1 var(--range-percent), #cbd5e1 var(--range-percent), #cbd5e1 100%);
    outline: none;
    cursor: pointer;
    transition: background 0.2s;
    padding: 0 !important;
    border: none !important;
    box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.38);
}

.dark .spacing-slider {
    background: linear-gradient(to right, #818cf8 0%, #818cf8 var(--range-percent), #374151 var(--range-percent), #374151 100%);
    box-shadow: inset 0 0 0 1px rgba(75, 85, 99, 0.65);
}

.spacing-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #6366f1;
    cursor: pointer;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.28);
}

.dark .spacing-slider::-webkit-slider-thumb {
    border-color: #111827;
}

.spacing-slider::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #6366f1;
    cursor: pointer;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.28);
}
</style>
