<template>
    <!-- Settings Mode -->
    <div v-if="mode === 'settings'" class="spacer-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Spacer Height</label>
            <div class="flex items-center gap-4">
                <input
                    v-model="state.height"
                    type="range"
                    min="0"
                    max="200"
                    step="10"
                    class="spacer-slider flex-1 appearance-none cursor-pointer"
                    :style="sliderStyle"
                >
                <span class="inline-flex min-w-[3rem] items-center justify-center rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">{{ state.height }}px</span>
            </div>
        </div>
    </div>

    <!-- Preview Mode -->
    <div v-else class="spacer-block-preview flex items-center justify-center bg-gray-50/50 dark:bg-gray-800/20 border border-dashed border-gray-200 dark:border-gray-700/50 rounded-lg" :style="{ height: state.height + 'px' }">
        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 opacity-50">Spacer ({{ state.height }}px)</span>
    </div>
</template>

<script setup lang="ts">
import { computed, reactive, watch } from 'vue';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
    height: props.modelValue?.height || props.data?.height || 40,
});

const sliderStyle = computed(() => {
    const percentage = (Number(state.height) / 200) * 100;
    return { '--range-percent': `${percentage}%` } as Record<string, string>;
});

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', { ...props.modelValue, ...newValue });
    }
}, { deep: true });

// Sync internal state when props change (for preview reactivity)
watch(() => [props.modelValue, props.data], () => {
    const source = props.modelValue || props.data;
    if (source) {
        state.height = source.height || 40;
    }
}, { deep: true });
</script>

<style scoped>
.spacer-slider {
    height: 8px;
    border-radius: 9999px;
    background: linear-gradient(to right, #6366f1 0%, #6366f1 var(--range-percent), #cbd5e1 var(--range-percent), #cbd5e1 100%);
    box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.38);
}

.dark .spacer-slider {
    background: linear-gradient(to right, #818cf8 0%, #818cf8 var(--range-percent), #374151 var(--range-percent), #374151 100%);
    box-shadow: inset 0 0 0 1px rgba(75, 85, 99, 0.65);
}

.spacer-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 9999px;
    background: #6366f1;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.28);
}

.dark .spacer-slider::-webkit-slider-thumb {
    border-color: #111827;
}

.spacer-slider::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 9999px;
    background: #6366f1;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.28);
}
</style>
