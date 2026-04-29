<template>
    <div class="space-y-6">
        <!-- Featured Image Section -->
        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">{{ $t('Featured Image') }}</h3>
            </header>
            <div class="space-y-4">
                <div
                    @click="helpers.openMediaPicker?.('featured')"
                    class="relative aspect-video rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500 transition-colors overflow-hidden group"
                >
                    <template v-if="featuredImageValue">
                        <img :src="featuredImageValue.url" :alt="featuredImageValue.name || $t('Featured image')" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-white text-sm font-medium">{{ $t('Change Image') }}</span>
                        </div>
                        <button
                            @click.stop="helpers.removeFeaturedImage?.()"
                            class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors shadow-lg"
                            :title="$t('Remove image')"
                            type="button"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </template>
                    <template v-else>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $t('Choose image') }}</span>
                    </template>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">{{ $t('Gallery') }}</h3>
                <button type="button" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors text-xs" @click="helpers.openMediaPicker?.('gallery')">
                    {{ $t('Add to Gallery') }}
                </button>
            </header>
            
            <div v-if="galleryImagesValue.length" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div
                    v-for="(image, index) in galleryImagesValue"
                    :key="`${image.id}-${index}`"
                    class="group relative aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 cursor-move"
                    :class="{ 'opacity-50': draggingIndex === index }"
                    draggable="true"
                    @dragstart="onDragStart($event, index as number)"
                    @dragover="onDragOver"
                    @drop="onDrop($event, index as number)"
                    @dragend="onDragEnd"
                >
                    <img :src="image.url" :alt="image.name" class="w-full h-full object-cover pointer-events-none" />
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button 
                            type="button" 
                            class="w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/40 text-white rounded-lg backdrop-blur-sm transition-colors disabled:opacity-20 hidden sm:flex"
                            @click="helpers.moveGalleryImage?.(index as number, 'up')" 
                            :disabled="index === 0"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="w-8 h-8 flex items-center justify-center bg-white/20 hover:bg-white/40 text-white rounded-lg backdrop-blur-sm transition-colors disabled:opacity-20 hidden sm:flex"
                            @click="helpers.moveGalleryImage?.(index as number, 'down')"
                            :disabled="index === galleryImagesValue.length - 1"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button 
                            type="button" 
                            class="w-8 h-8 flex items-center justify-center bg-red-500/80 hover:bg-red-600 text-white rounded-lg backdrop-blur-sm transition-colors"
                            @click="helpers.removeGalleryImage?.(index as number)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-500 dark:text-gray-400 italic">{{ $t('No gallery images yet.') }}</p>
        </section>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, isRef, ref, getCurrentInstance } from 'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('ProductMediaPanel must be used within editor context');
}

const rawFeatured = context.state?.featuredImage;
const rawGallery = context.state?.galleryImages;

const featuredImage = isRef(rawFeatured) ? rawFeatured : ref(rawFeatured ?? null);
const galleryImages = isRef(rawGallery) ? rawGallery : ref(rawGallery ?? []);

if (!isRef(rawFeatured) && context.state) {
    (context.state as Record<string, unknown>).featuredImage = featuredImage;
}

if (!isRef(rawGallery) && context.state) {
    (context.state as Record<string, unknown>).galleryImages = galleryImages;
}

const helpers = context.helpers ?? {};

// expose plain refs for template (avoid auto-unwrapping confusion)
const featuredImageValue = computed({
    get: () => featuredImage.value,
    set: (val) => {
        featuredImage.value = val;
    },
});

const galleryImagesValue = computed({
    get: () => galleryImages.value ?? [],
    set: (val) => {
        galleryImages.value = Array.isArray(val) ? val : [];
    },
});

// Drag and Drop Logic
const draggingIndex = ref<number | null>(null);

const onDragStart = (event: DragEvent, index: number) => {
    event.stopPropagation();
    draggingIndex.value = index;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.dropEffect = 'move';
        event.dataTransfer.setData('text/plain', index.toString());
    }
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault(); // Necessary to allow dropping
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
};

const onDrop = (event: DragEvent, targetIndex: number) => {
    event.preventDefault();
    const sourceIndex = draggingIndex.value;
    
    if (sourceIndex === null || sourceIndex === targetIndex) {
        return;
    }

    const newGallery = [...galleryImagesValue.value];
    const [movedItem] = newGallery.splice(sourceIndex, 1);
    newGallery.splice(targetIndex, 0, movedItem);
    
    galleryImagesValue.value = newGallery;
    draggingIndex.value = null;
};

const onDragEnd = () => {
    draggingIndex.value = null;
};
</script>

