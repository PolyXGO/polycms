<template>
    <div class="gallery-block-editor p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700" :style="{ padding: state.padding }">
        <div class="flex flex-wrap gap-4 mb-6">
            <div class="form-group flex-1 min-w-[120px]">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Columns</label>
                <select v-model.number="state.columns" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                    <option :value="1">1 Column</option>
                    <option :value="2">2 Columns</option>
                    <option :value="3">3 Columns</option>
                    <option :value="4">4 Columns</option>
                </select>
            </div>
            <div class="form-group flex-1 min-w-[120px]">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Gap Size</label>
                <select v-model="state.gap" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                    <option value="gap-1">Tiny</option>
                    <option value="gap-2">Small</option>
                    <option value="gap-4">Medium</option>
                    <option value="gap-8">Large</option>
                </select>
            </div>
            <div class="form-group flex-1 min-w-[120px]">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Corner Style</label>
                <select v-model="state.rounded" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs">
                    <option value="rounded-none">Square</option>
                    <option value="rounded-lg">Rounded</option>
                    <option value="rounded-2xl">Very Rounded</option>
                    <option value="rounded-full">Circle</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-4">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Gallery Images</label>
                <button @click="openMediaPicker" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition-colors flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                    Add Images
                </button>
            </div>

            <div 
                v-if="state.images.length > 0"
                class="grid grid-cols-4 md:grid-cols-6 gap-2"
            >
                <div v-for="(element, index) in state.images" :key="index" class="relative group aspect-square rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <img :src="element.url" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1">
                         <button @click.stop="moveImage(Number(index), -1)" v-if="Number(index) > 0" class="p-1 bg-white rounded shadow hover:bg-gray-50 text-gray-600" title="Move Up">
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                         </button>
                         <button @click.stop="moveImage(Number(index), 1)" v-if="Number(index) < state.images.length - 1" class="p-1 bg-white rounded shadow hover:bg-gray-50 text-gray-600" title="Move Down">
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                         </button>
                         <button @click.stop="removeImage(Number(index))" class="p-1 bg-white rounded shadow hover:bg-red-50 text-red-600" title="Remove">
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m4-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                         </button>
                    </div>
                </div>
            </div>
            
            <div v-else class="p-10 text-center border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                 <p class="text-xs text-gray-400 italic">No specific images selected. Will display Product/Post media by default.</p>
            </div>
        </div>

        <MediaPicker
            ref="mediaPickerRef"
            :multiple="true"
            :accepted-types="['image']"
            @select="handleMediaSelect"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import MediaPicker from '@/admin/components/MediaPicker.vue';

const props = defineProps<{
    modelValue: any;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);

const state = reactive({
    images: props.modelValue?.images || props.data?.images || [],
    columns: props.modelValue?.columns || props.data?.columns || 3,
    gap: props.modelValue?.gap || props.data?.gap || 'gap-4',
    rounded: props.modelValue?.rounded || props.data?.rounded || 'rounded-xl',
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

const openMediaPicker = () => {
    mediaPickerRef.value?.open();
};

const handleMediaSelect = (media: any) => {
    const selected = Array.isArray(media) ? media : [media];
    const newImages = selected.map(m => ({
        url: m.url,
        alt: m.name || m.file_name || ''
    }));
    
    state.images = [...state.images, ...newImages];
};

const removeImage = (index: number) => {
    state.images.splice(index, 1);
};

const moveImage = (index: number, direction: number) => {
    const newIndex = index + direction;
    if (newIndex < 0 || newIndex >= state.images.length) return;
    
    const temp = state.images[index];
    state.images[index] = state.images[newIndex];
    state.images[newIndex] = temp;
};

const notifyChange = () => {
    // Force watch trigger
};

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', {
            ...props.modelValue,
            ...newValue,
        });
    }
}, { deep: true });

watch(() => props.data, (newData) => {
    if (newData) {
        state.images = newData.images || [];
        state.columns = newData.columns || 3;
        state.gap = newData.gap || 'gap-4';
        state.rounded = newData.rounded || 'rounded-xl';
        state.margin = newData.margin || '';
        state.padding = newData.padding || '';
    }
}, { deep: true });
</script>
