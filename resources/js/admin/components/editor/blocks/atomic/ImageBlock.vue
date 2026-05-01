<template>
    <!-- Settings Mode -->
    <div v-if="mode === 'settings'" class="image-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Image Source</label>
            <div class="flex gap-2">
                <input v-model="state.src" type="text" class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs focus:ring-2 focus:ring-indigo-500" placeholder="https://...">
                <button @click="openMediaPicker" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs hover:bg-gray-200 dark:hover:bg-gray-600">Pick</button>
            </div>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Alt Text</label>
            <input v-model="state.alt" type="text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs" placeholder="Image description">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Width</label>
                <select v-model="state.width" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                    <option value="w-full">Full Width</option>
                    <option value="w-1/2">Half Width</option>
                    <option value="w-1/3">1/3 Width</option>
                    <option value="w-auto">Auto</option>
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
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Border Radius</label>
            <select v-model="state.border_radius" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                <option value="rounded-none">None</option>
                <option value="rounded">Small</option>
                <option value="rounded-lg">Medium</option>
                <option value="rounded-xl">Large</option>
                <option value="rounded-2xl">Extra Large</option>
                <option value="rounded-full">Circle/Pill</option>
            </select>
        </div>
        
        <MediaPicker ref="mediaPickerRef" @select="handleMediaSelect" />
    </div>

    <!-- Preview Mode -->
    <div v-else class="image-block-preview flex" :class="[state.alignment === 'center' ? 'justify-center' : state.alignment === 'right' ? 'justify-end' : 'justify-start']">
        <div :class="[state.width, 'overflow-hidden', state.border_radius]">
            <img v-if="state.src" :src="state.src" :alt="state.alt" class="w-full h-auto object-cover border border-gray-100 dark:border-gray-700">
            <div v-else class="w-full aspect-video bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import MediaPicker from '@/admin/components/MediaPicker.vue';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const mediaPickerRef = ref<any>(null);

const state = reactive({
    src: props.modelValue?.src || props.data?.src || '',
    alt: props.modelValue?.alt || props.data?.alt || '',
    width: props.modelValue?.width || props.data?.width || 'w-full',
    alignment: props.modelValue?.alignment || props.data?.alignment || 'left',
    border_radius: props.modelValue?.border_radius || props.data?.border_radius || 'rounded-xl',
});

const openMediaPicker = () => mediaPickerRef.value?.open();
const handleMediaSelect = (media: any) => {
    const selected = Array.isArray(media) ? media[0] : media;
    if (selected?.url) state.src = selected.url;
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
        state.src = source.src || '';
        state.alt = source.alt || '';
        state.width = source.width || 'w-full';
        state.alignment = source.alignment || 'left';
        state.border_radius = source.border_radius || 'rounded-xl';
    }
}, { deep: true });
</script>
