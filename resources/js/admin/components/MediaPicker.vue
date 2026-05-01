<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="close">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ multiple ? 'Select Media' : 'Select Media' }}
                </h3>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Media Manager -->
            <div class="flex-1 overflow-hidden">
                <MediaManager
                    :multiple="multiple"
                    :accepted-types="acceptedTypes"
                    @select="handleSelect"
                />
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span v-if="selectedCount > 0">{{ selectedCount }} selected</span>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="close"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmSelection"
                        :disabled="selectedCount === 0"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ multiple ? 'Select' : 'Select' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import MediaManager from './MediaManager.vue';

interface MediaItem {
    id: number;
    name: string;
    file_name: string;
    url: string;
    type: string;
    size: number;
    created_at: string;
    width?: number;
    height?: number;
}

const props = defineProps<{
    multiple?: boolean;
    acceptedTypes?: string[];
}>();

const emit = defineEmits<{
    (e: 'select', media: MediaItem | MediaItem[]): void;
    (e: 'close'): void;
}>();

const isOpen = ref(false);
const selectedMedia = ref<MediaItem[]>([]);

const selectedCount = computed(() => selectedMedia.value.length);

const open = () => {
    isOpen.value = true;
    selectedMedia.value = [];
};

const close = () => {
    isOpen.value = false;
    selectedMedia.value = [];
    emit('close');
};

const handleSelect = (media: MediaItem | MediaItem[]) => {
    if (Array.isArray(media)) {
        selectedMedia.value = media;
    } else {
        selectedMedia.value = [media];
    }
};

const confirmSelection = () => {
    if (props.multiple) {
        emit('select', selectedMedia.value);
    } else if (selectedMedia.value.length > 0) {
        emit('select', selectedMedia.value[0]);
    }
    close();
};

defineExpose({
    open,
    close,
});
</script>

