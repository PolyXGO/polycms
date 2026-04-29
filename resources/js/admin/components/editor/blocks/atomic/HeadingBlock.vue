<template>
    <!-- Settings Mode -->
    <div v-if="mode === 'settings'" class="heading-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading Text</label>
            <textarea v-model="state.text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-20 focus:ring-2 focus:ring-indigo-500" placeholder="Enter heading..."></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Level</label>
                <select v-model="state.level" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                    <option :value="1">H1</option>
                    <option :value="2">H2</option>
                    <option :value="3">H3</option>
                    <option :value="4">H4</option>
                    <option :value="5">H5</option>
                    <option :value="6">H6</option>
                </select>
            </div>
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Alignment</label>
                <div class="flex gap-1">
                    <button v-for="align in ['left', 'center', 'right']" :key="align" @click="state.alignment = align" class="flex-1 p-2 rounded-lg border text-xs capitalize transition-all" :class="state.alignment === align ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700'">
                        {{ align }}
                    </button>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Font Weight</label>
            <select v-model="state.font_weight" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                <option value="font-light">Light</option>
                <option value="font-normal">Normal</option>
                <option value="font-medium">Medium</option>
                <option value="font-semibold">Semibold</option>
                <option value="font-bold">Bold</option>
                <option value="font-black">Black</option>
            </select>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Color</label>
            <div class="flex gap-2">
                <input v-model="state.color" type="color" class="h-8 w-12 border-0 p-0 bg-transparent cursor-pointer">
                <input v-model="state.color" type="text" class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-1 text-xs" placeholder="#000000">
            </div>
        </div>
    </div>

    <!-- Preview Mode -->
    <div v-else class="heading-block-preview" :class="[state.alignment === 'center' ? 'text-center' : state.alignment === 'right' ? 'text-right' : 'text-left']">
        <component 
            :is="'h' + state.level" 
            :class="[state.font_weight]"
            :style="{ color: state.color, fontSize: getFontSize(state.level) }"
        >
            {{ state.text || 'Heading Text' }}
        </component>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
    text: props.modelValue?.text || props.data?.text || '',
    level: props.modelValue?.level || props.data?.level || 2,
    alignment: props.modelValue?.alignment || props.data?.alignment || 'left',
    font_weight: props.modelValue?.font_weight || props.data?.font_weight || 'font-bold',
    color: props.modelValue?.color || props.data?.color || '',
});

const getFontSize = (level: number) => {
    const sizes: Record<number, string> = {
        1: '1.875rem',
        2: '1.5rem',
        3: '1.25rem',
        4: '1.125rem',
        5: '1rem',
        6: '0.875rem',
    };
    return sizes[level] || '1.5rem';
};

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', { ...props.modelValue, ...newValue });
    }
}, { deep: true });

// Sync internal state when props change (for preview reactivity)
watch(() => [props.modelValue, props.data], () => {
    const source = props.modelValue || props.data;
    if (source) {
        state.text = source.text || '';
        state.level = source.level || 2;
        state.alignment = source.alignment || 'left';
        state.font_weight = source.font_weight || 'font-bold';
        state.color = source.color || '';
    }
}, { deep: true });
</script>
