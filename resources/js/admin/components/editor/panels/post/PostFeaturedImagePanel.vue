<template>
    <div class="grid gap-3">
        <div
            @click="helpers.openMediaPicker?.()"
            class="relative aspect-video rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500 transition-colors overflow-hidden group"
        >
            <template v-if="featuredImageValue">
                <img :src="featuredImageValue.url" :alt="featuredImageValue.name || 'Featured image'" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="text-white text-sm font-medium">Change Image</span>
                </div>
                <button
                    @click.stop="helpers.removeFeaturedImage?.()"
                    class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors shadow-lg"
                    title="Remove image"
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
                <span class="text-sm text-gray-500 dark:text-gray-400">Choose image</span>
            </template>
        </div>

        <div class="mt-2 px-1">
            <FormToggle
                name="show_featured_image"
                v-model="showFeaturedImage"
                size="lg"
                label="Show Featured Image on detail view"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, isRef, ref } from 'vue';
import { EditorContextKey } from '../../../../editor/context';
import FormToggle from '../../../forms/FormToggle.vue';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('PostFeaturedImagePanel must be used within editor context');
}

const rawFeaturedImage = context.state?.featuredImage;

const featuredImage = isRef(rawFeaturedImage) ? rawFeaturedImage : ref(rawFeaturedImage ?? null);

if (!isRef(rawFeaturedImage)) {
    if (context.state) {
        (context.state as Record<string, unknown>).featuredImage = featuredImage;
    }
}

const helpers = context.helpers ?? {};

const featuredImageValue = computed({
    get: () => featuredImage.value,
    set: (val) => {
        featuredImage.value = val;
    },
});

const showFeaturedImage = computed({
    get: () => context.form?.value?.show_featured_image ?? true,
    set: (val) => {
        if (context.form?.value) {
            context.form.value.show_featured_image = val;
        }
    },
});
</script>

<style scoped>
.featured-image {
    display: grid;
    gap: 0.75rem;
}
</style>
