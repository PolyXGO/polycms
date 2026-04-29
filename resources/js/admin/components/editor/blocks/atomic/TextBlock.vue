<template>
    <!-- Settings Mode -->
    <div v-if="mode === 'settings'" class="text-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Content</label>
            <textarea v-model="state.content" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-32 focus:ring-2 focus:ring-indigo-500" placeholder="Enter text content..."></textarea>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Font Size</label>
                <select v-model="state.font_size" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm h-11">
                    <option value="text-xs">Extra Small</option>
                    <option value="text-sm">Small</option>
                    <option value="text-base">Base</option>
                    <option value="text-lg">Large</option>
                    <option value="text-xl">Extra Large</option>
                </select>
            </div>
            <ColorPicker v-model="state.color" label="Text Color" />
        </div>

        <AlignmentPicker v-model="state.alignment" label="Alignment" />
    </div>

    <!-- Preview Mode -->
    <div v-else class="text-block-preview landing-text" :class="[state.font_size, state.alignment === 'full' ? 'text-justify' : 'text-' + state.alignment]" :style="{ color: state.color }">
        {{ state.content || 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}
    </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';
import ColorPicker from '../../controls/ColorPicker.vue';
import AlignmentPicker from '../../controls/AlignmentPicker.vue';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
    content: props.modelValue?.content || props.data?.content || '',
    font_size: props.modelValue?.font_size || props.data?.font_size || 'text-base',
    alignment: props.modelValue?.alignment || props.data?.alignment || 'left',
    color: props.modelValue?.color || props.data?.color || '',
});

const buildPayload = () => ({
    content: state.content,
    font_size: state.font_size,
    alignment: state.alignment,
    color: state.color,
});

watch(() => ({ ...state }), () => {
    if (props.mode === 'settings') {
        emit('update:modelValue', buildPayload());
    }
}, { deep: true });

// Sync internal state when props change (for preview reactivity)
watch(() => props.modelValue, (newVal) => {
    if (props.mode === 'preview' && newVal) {
        state.content = newVal.content || '';
        state.font_size = newVal.font_size || 'text-base';
        state.alignment = newVal.alignment || 'left';
        state.color = newVal.color || '';
    }
}, { deep: true, immediate: true });
</script>
