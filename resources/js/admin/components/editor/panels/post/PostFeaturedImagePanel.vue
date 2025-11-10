<template>
    <div class="featured-image">
        <div v-if="featuredImageValue" class="featured-image__preview">
            <img :src="featuredImageValue.url" :alt="featuredImageValue.name || 'Featured image'" />
            <button type="button" class="featured-image__remove" @click="helpers.removeFeaturedImage?.()">
                Remove featured image
            </button>
        </div>
        <button type="button" class="featured-image__button" @click="helpers.openMediaPicker?.()">
            {{ featuredImageValue ? 'Change Image' : 'Select Image' }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, isRef, ref } from 'vue';
import { EditorContextKey } from '../../../../editor/context';

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
</script>

<style scoped>
.featured-image {
    display: grid;
    gap: 0.75rem;
}

.featured-image__preview {
    position: relative;
    border-radius: 0.75rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.featured-image__preview img {
    width: 100%;
    display: block;
    object-fit: cover;
}

.featured-image__remove {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border: none;
    padding: 0.35rem 0.6rem;
    border-radius: 0.5rem;
    cursor: pointer;
}

.featured-image__button {
    justify-self: flex-start;
    padding: 0.55rem 1rem;
    border-radius: 0.75rem;
    border: none;
    background: #4f46e5;
    color: white;
    font-weight: 600;
    cursor: pointer;
}
</style>

