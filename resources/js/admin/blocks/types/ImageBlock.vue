<template>
    <div class="block-image">
        <div v-if="!attrs.url && !attrs.media_id" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
            <button
                type="button"
                @click="openMediaPicker = true"
                class="text-gray-500 hover:text-indigo-600"
            >
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p>Click to select image</p>
            </button>
        </div>
        <div v-else class="relative group">
            <img
                :src="imageUrl"
                :alt="attrs.alt || ''"
                class="w-full h-auto rounded-lg"
            />
            <input
                v-if="editingAlt"
                v-model="localAlt"
                @blur="handleAltBlur"
                @keyup.enter="handleAltBlur"
                class="mt-2 w-full px-2 py-1 border border-gray-300 rounded"
                placeholder="Alt text"
            />
            <div v-else class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Alt: {{ attrs.alt || 'No alt text' }}
                <button type="button" @click="editingAlt = true" class="ml-2 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
            </div>
        </div>
        <!-- Media Picker Modal would go here -->
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    block: any;
    selected: boolean;
}>();

const emit = defineEmits<{
    'update:attrs': [attrs: Record<string, any>];
}>();

const attrs = props.block.attrs || {};
const openMediaPicker = ref(false);
const editingAlt = ref(false);
const localAlt = ref(attrs.alt || '');

const imageUrl = computed(() => {
    if (attrs.url) return attrs.url;
    if (attrs.media_id) return `/api/v1/media/${attrs.media_id}`;
    return '';
});

const handleAltBlur = () => {
    editingAlt.value = false;
    emit('update:attrs', { alt: localAlt.value });
};

watch(() => props.selected, (selected) => {
    if (!selected) {
        editingAlt.value = false;
    }
});
</script>
