<template>
    <!-- Settings Mode (for sidebar) -->
    <div v-if="mode === 'settings'" class="text-image-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
            <input 
                v-model="state.heading" 
                type="text" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm"
                placeholder="Section Heading"
            >
        </div>
        
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Content</label>
            <textarea 
                v-model="state.content" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-24"
                placeholder="Enter description text..."
            ></textarea>
        </div>
        
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Image URL</label>
            <div class="flex gap-2">
                <input 
                    v-model="state.image_url" 
                    type="text" 
                    class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs"
                    placeholder="https://example.com/image.jpg"
                >
                <button @click="openMediaPicker" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700">
                    Pick
                </button>
            </div>
        </div>
        
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Image Position</label>
            <div class="flex gap-2">
                <button 
                    @click="state.image_position = 'left'"
                    class="flex-1 px-3 py-2 text-xs rounded-lg border transition-all"
                    :class="state.image_position === 'left' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600'"
                >
                    Left
                </button>
                <button 
                    @click="state.image_position = 'right'"
                    class="flex-1 px-3 py-2 text-xs rounded-lg border transition-all"
                    :class="state.image_position === 'right' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600'"
                >
                    Right
                </button>
            </div>
        </div>

        <MediaPicker ref="mediaPickerRef" :multiple="false" :accepted-types="['image']" @select="handleMediaSelect" />
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="text-image-block-preview" :style="{ padding: state.padding }">
        <div class="ti-preview-image" v-if="state.image_url" :class="{ 'ti-preview-image--right': state.image_position === 'right' }">
            <img :src="state.image_url" alt="Preview">
        </div>
        <div class="ti-preview-content">
            <h3 class="ti-preview-heading">{{ state.heading || 'Section Heading' }}</h3>
            <p class="ti-preview-text">{{ state.content || 'Your description text will appear here...' }}</p>
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

const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);

const state = reactive({
    heading: props.modelValue?.heading || props.data?.heading || '',
    content: props.modelValue?.content || props.data?.content || '',
    image_url: props.modelValue?.image_url || props.data?.image_url || '',
    image_position: props.modelValue?.image_position || props.data?.image_position || 'left',
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', { ...props.modelValue, ...newValue });
    }
}, { deep: true });

watch(() => props.data, (newData) => {
    if (newData) {
        state.heading = newData.heading || '';
        state.content = newData.content || '';
        state.image_url = newData.image_url || '';
        state.image_position = newData.image_position || 'left';
        state.margin = newData.margin || '';
        state.padding = newData.padding || '';
    }
}, { deep: true });

const openMediaPicker = () => mediaPickerRef.value?.open();
const handleMediaSelect = (media: any) => {
    const selected = Array.isArray(media) ? media[0] : media;
    if (selected?.url) state.image_url = selected.url;
};
</script>

<style scoped>
.text-image-block-preview {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 1rem;
    padding: 0.5rem;
    background: var(--color-bg-tertiary, #f3f4f6);
    border-radius: 0.5rem;
}

.ti-preview-image {
    order: 1;
}

.ti-preview-image--right {
    order: 2;
}

.ti-preview-image img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 0.375rem;
}

.ti-preview-content {
    order: 2;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.ti-preview-image--right + .ti-preview-content {
    order: 1;
}

.ti-preview-heading {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--color-text-primary, #1f2937);
    margin: 0 0 0.25rem 0;
}

.ti-preview-text {
    font-size: 0.75rem;
    color: var(--color-text-secondary, #6b7280);
    margin: 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.dark .text-image-block-preview {
    background: var(--color-bg-tertiary-dark, #374151);
}

.dark .ti-preview-heading {
    color: var(--color-text-primary-dark, #f9fafb);
}

.dark .ti-preview-text {
    color: var(--color-text-secondary-dark, #d1d5db);
}
</style>
