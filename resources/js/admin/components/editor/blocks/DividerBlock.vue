<template>
    <!-- Settings Mode (for sidebar) -->
    <div v-if="mode === 'settings'" class="divider-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Spacing</label>
            <select v-model="state.spacing" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                <option value="py-2">Small (8px)</option>
                <option value="py-4">Medium (16px)</option>
                <option value="py-8">Large (32px)</option>
                <option value="py-12">Extra Large (48px)</option>
                <option value="py-16">Huge (64px)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Style</label>
            <select v-model="state.style" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                <option value="solid">Solid</option>
                <option value="dashed">Dashed</option>
                <option value="dotted">Dotted</option>
            </select>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Width</label>
            <select v-model="state.width" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                <option value="full">Full Width</option>
                <option value="1/2">Half Width</option>
                <option value="1/3">One Third</option>
            </select>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Color</label>
            <div class="flex gap-2">
                <input v-model="state.color" type="color" class="h-8 w-12 border-0 p-0 bg-transparent cursor-pointer">
                <input v-model="state.color" type="text" class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-1 text-xs" placeholder="gray-200 or #HEX">
            </div>
        </div>
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="divider-block-preview" :class="state.spacing" :style="{ padding: state.padding }">
        <hr :style="previewStyle" :class="previewClass">
    </div>
</template>

<script setup lang="ts">
import { reactive, watch, computed } from 'vue';

const props = defineProps<{
    modelValue: any;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
    spacing: props.modelValue?.spacing || props.data?.spacing || 'py-8',
    style: props.modelValue?.style || props.data?.style || 'solid',
    width: props.modelValue?.width || props.data?.width || 'full',
    color: props.modelValue?.color || props.data?.color || '#e5e7eb',
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

const previewClass = computed(() => {
    const classes = ['border-t'];
    
    if (state.style === 'dashed') classes.push('border-dashed');
    if (state.style === 'dotted') classes.push('border-dotted');
    if (state.style === 'solid') classes.push('border-solid');

    if (state.width === '1/2') classes.push('w-1/2 mx-auto');
    else if (state.width === '1/3') classes.push('w-1/3 mx-auto');
    else classes.push('w-full');

    return classes.join(' ');
});

const previewStyle = computed(() => {
    if (state.color.startsWith('#')) {
        return { borderColor: state.color };
    }
    return { borderColor: '#e5e7eb' };
});

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', { ...props.modelValue, ...newValue });
    }
}, { deep: true });

watch(() => props.data, (newData) => {
    if (newData) {
        state.spacing = newData.spacing || 'py-8';
        state.style = newData.style || 'solid';
        state.width = newData.width || 'full';
        state.color = newData.color || '#e5e7eb';
        state.margin = newData.margin || '';
        state.padding = newData.padding || '';
    }
}, { deep: true });
</script>

<style scoped>
.divider-block-preview {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

.divider-block-preview hr {
    border-width: 1px 0 0 0;
}
</style>
