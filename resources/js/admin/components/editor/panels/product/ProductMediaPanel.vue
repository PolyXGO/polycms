<template>
    <div class="media-panel">
        <section class="media-panel__section">
            <header class="media-panel__section-header">
                <h3>Featured Image</h3>
            </header>
            <div class="media-panel__featured">
                <div v-if="featuredImageValue" class="media-panel__featured-preview">
                    <img :src="featuredImageValue.url" :alt="featuredImageValue.name || 'Featured image'" />
                    <button type="button" class="media-panel__remove" @click="helpers.removeFeaturedImage?.()">
                        Remove
                    </button>
                </div>
                <button type="button" class="media-panel__button" @click="helpers.openMediaPicker?.('featured')">
                    {{ featuredImageValue ? 'Change Image' : 'Select Image' }}
                </button>
            </div>
        </section>

        <section class="media-panel__section">
            <header class="media-panel__section-header">
                <h3>Gallery</h3>
                <button type="button" class="media-panel__button" @click="helpers.openMediaPicker?.('gallery')">
                    Add to Gallery
                </button>
            </header>
            <div v-if="galleryImagesValue.length" class="media-panel__gallery">
                <div
                    v-for="(image, index) in galleryImagesValue"
                    :key="`${image.id}-${index}`"
                    class="media-panel__gallery-item"
                >
                    <img :src="image.url" :alt="image.name" />
                    <div class="media-panel__gallery-actions">
                        <button type="button" @click="helpers.moveGalleryImage?.(index, 'up')" :disabled="index === 0">
                            ↑
                        </button>
                        <button
                            type="button"
                            @click="helpers.moveGalleryImage?.(index, 'down')"
                            :disabled="index === galleryImagesValue.length - 1"
                        >
                            ↓
                        </button>
                        <button type="button" @click="helpers.removeGalleryImage?.(index)">
                            ×
                        </button>
                    </div>
                </div>
            </div>
            <p v-else class="media-panel__empty">No gallery images yet.</p>
        </section>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, isRef, ref } from 'vue';
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
</script>

<style scoped>
.media-panel {
    display: grid;
    gap: 1.5rem;
}

.media-panel__section {
    display: grid;
    gap: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.85rem;
    padding: 1rem;
    background: #ffffff;
}

.media-panel__section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.media-panel__section-header h3 {
    font-weight: 600;
    color: #0f172a;
}

.media-panel__featured {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.media-panel__featured-preview {
    position: relative;
    border-radius: 0.85rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    max-width: 220px;
}

.media-panel__featured-preview img {
    width: 100%;
    display: block;
    object-fit: cover;
}

.media-panel__remove {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    border: none;
    background: rgba(239, 68, 68, 0.9);
    color: #ffffff;
    padding: 0.25rem 0.5rem;
    border-radius: 0.45rem;
    cursor: pointer;
}

.media-panel__button {
    border: none;
    background: #4f46e5;
    color: white;
    font-weight: 600;
    border-radius: 0.65rem;
    padding: 0.45rem 0.85rem;
    cursor: pointer;
}

.media-panel__gallery {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
}

.media-panel__gallery-item {
    position: relative;
    border-radius: 0.75rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.media-panel__gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.media-panel__gallery-actions {
    position: absolute;
    bottom: 0.35rem;
    right: 0.35rem;
    display: flex;
    gap: 0.25rem;
}

.media-panel__gallery-actions button {
    border: none;
    background: rgba(15, 23, 42, 0.6);
    color: white;
    border-radius: 0.35rem;
    padding: 0.2rem 0.35rem;
    cursor: pointer;
}

.media-panel__gallery-actions button:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.media-panel__empty {
    font-size: 0.9rem;
    color: #64748b;
}
</style>

