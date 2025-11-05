<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ isEdit ? $t('Edit Product') || 'Edit Product' : $t('Create Product') || 'Create New Product' }}
            </h1>
            <div class="flex items-center gap-3">
                <a
                    v-if="isEdit && form.slug"
                    :href="getPermalink()"
                    target="_blank"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    {{ $t('View Page') || 'View Page' }}
                </a>
                <button
                    @click="router.back()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                >
                    {{ $t('Cancel') }}
                </button>
            </div>
        </div>

        <form @submit.prevent="saveProduct" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Basic Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @input="generateSlug"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug *</label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @input="onSlugInput"
                            @paste="onSlugInput"
                        />
                        <div v-if="form.slug" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-medium">{{ $t('Permalink:') || 'Permalink:' }}</span>
                            <a 
                                :href="getPermalink()" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="ml-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 hover:underline"
                            >
                                {{ getPermalink() }}
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SKU</label>
                        <input
                            v-model="form.sku"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                        <select v-model="form.status" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <TiptapEditor v-model="form.description" :placeholder="$t('Enter product description...') || 'Enter product description...'" />
                    </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Inventory -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Pricing & Inventory</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price *</label>
                        <input
                            v-model.number="form.price"
                            type="number"
                            step="0.01"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Compare at Price</label>
                        <input
                            v-model.number="form.compare_at_price"
                            type="number"
                            step="0.01"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock Quantity</label>
                        <input
                            v-model.number="form.stock_quantity"
                            type="number"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock Status *</label>
                        <select
                            v-model="form.stock_status"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        >
                            <option value="in_stock">In Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="on_backorder">On Backorder</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Product Images -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Product Images</h2>
                <div class="space-y-4">
                    <!-- Featured Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Featured Image</label>
                        <div class="flex items-center gap-4">
                            <div v-if="featuredImage" class="relative w-32 h-32 border-2 border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                <img :src="featuredImage.url" :alt="featuredImage.name" class="w-full h-full object-cover" />
                                <button
                                    @click="removeFeaturedImage"
                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <button
                                @click="openMediaPicker('featured')"
                                type="button"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                            >
                                {{ featuredImage ? 'Change Image' : 'Select Image' }}
                            </button>
                        </div>
                    </div>

                    <!-- Gallery -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gallery</label>
                        <div class="space-y-2">
                            <div v-if="galleryImages.length > 0" class="grid grid-cols-4 gap-4">
                                <div
                                    v-for="(image, index) in galleryImages"
                                    :key="image.id"
                                    class="relative aspect-square border-2 border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden group"
                                >
                                    <img :src="image.url" :alt="image.name" class="w-full h-full object-cover" />
                                    <button
                                        @click="removeGalleryImage(index)"
                                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="moveGalleryImage(index, 'up')"
                                        v-if="index > 0"
                                        class="absolute top-1 left-1 bg-gray-600 text-white rounded-full p-1 hover:bg-gray-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                        title="Move up"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button
                                @click="openMediaPicker('gallery')"
                                type="button"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                            >
                                Add to Gallery
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">SEO Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                        <input v-model="form.meta_title" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Keywords</label>
                        <input v-model="form.meta_keywords" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                        <textarea
                            v-model="form.meta_description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    @click="router.back()"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                >
                    {{ $t('Cancel') }}
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ loading ? $t('Saving') : $t('Save Product') }}
                </button>
            </div>
        </form>

        <!-- Media Picker -->
        <MediaPicker
            ref="mediaPickerRef"
            :multiple="currentPickerMode === 'gallery'"
            :accepted-types="['image']"
            @select="handleMediaSelect"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import TiptapEditor from '../../components/TiptapEditor.vue';
import MediaPicker from '../../components/MediaPicker.vue';
import { useSlugify } from '../../composables/useSlugify';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import { getCurrentInstance } from 'vue';

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

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const route = useRoute();
const { slugify } = useSlugify();
const dialog = useDialog();

const isEdit = computed(() => !!route.params.id);

const loading = ref(false);
const slugManuallyEdited = ref(false);
const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const currentPickerMode = ref<'featured' | 'gallery'>('featured');
const featuredImage = ref<MediaItem | null>(null);
const galleryImages = ref<MediaItem[]>([]);
const form = ref({
    name: '',
    slug: '',
    sku: '',
    status: 'draft',
    description: '',
    description_html: '',
    price: 0,
    compare_at_price: null as number | null,
    stock_quantity: 0,
    stock_status: 'in_stock',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

const generateSlug = () => {
    // Auto-generate slug from name if slug is empty or hasn't been manually edited
    if (form.value.name) {
        const nameSlug = slugify(form.value.name);
        if (!form.value.slug || (!slugManuallyEdited.value && form.value.slug === nameSlug)) {
            form.value.slug = nameSlug;
            slugManuallyEdited.value = false;
        }
    }
};

const onSlugInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const inputValue = target.value;
    
    // If slug is empty, auto-generate from name
    if (!inputValue || inputValue.trim() === '') {
        if (form.value.name) {
            form.value.slug = slugify(form.value.name);
            slugManuallyEdited.value = false;
        }
        return;
    }
    
    // Check if input contains non-slug characters (spaces, special chars, Vietnamese accents)
    // Convert to lowercase for comparison
    const lowerInput = inputValue.toLowerCase();
    const hasNonSlugChars = /[^a-z0-9\-]/.test(lowerInput) || /[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/.test(lowerInput);
    
    // If input has non-slug characters, convert to slug
    if (hasNonSlugChars) {
        form.value.slug = slugify(inputValue);
        slugManuallyEdited.value = true;
    } else {
        // Input is already in slug format, just mark as manually edited
        slugManuallyEdited.value = true;
    }
};

const getPermalink = (): string => {
    if (!form.value.slug) return '';
    const baseUrl = window.location.origin;
    return `${baseUrl}/products/${form.value.slug}`;
};

const openMediaPicker = (mode: 'featured' | 'gallery') => {
    currentPickerMode.value = mode;
    mediaPickerRef.value?.open();
};

const handleMediaSelect = (media: MediaItem | MediaItem[]) => {
    if (currentPickerMode.value === 'featured') {
        if (Array.isArray(media)) {
            featuredImage.value = media[0] || null;
        } else {
            featuredImage.value = media;
        }
    } else {
        // Gallery mode
        if (Array.isArray(media)) {
            // Add new images to gallery (avoid duplicates)
            media.forEach(img => {
                if (!galleryImages.value.find(existing => existing.id === img.id)) {
                    galleryImages.value.push(img);
                }
            });
        } else {
            if (!galleryImages.value.find(existing => existing.id === media.id)) {
                galleryImages.value.push(media);
            }
        }
    }
};

const removeFeaturedImage = () => {
    featuredImage.value = null;
};

const removeGalleryImage = (index: number) => {
    galleryImages.value.splice(index, 1);
};

const moveGalleryImage = (index: number, direction: 'up' | 'down') => {
    if (direction === 'up' && index > 0) {
        const temp = galleryImages.value[index];
        galleryImages.value[index] = galleryImages.value[index - 1];
        galleryImages.value[index - 1] = temp;
    } else if (direction === 'down' && index < galleryImages.value.length - 1) {
        const temp = galleryImages.value[index];
        galleryImages.value[index] = galleryImages.value[index + 1];
        galleryImages.value[index + 1] = temp;
    }
};

const loadProduct = async () => {
    if (!isEdit.value) return;

    try {
        const response = await axios.get(`/api/v1/products/${route.params.id}`);
        const product = response.data.data;
        form.value = {
            name: product.name,
            slug: product.slug,
            sku: product.sku || '',
            status: product.status,
            description: product.description_html || '',
            description_html: product.description_html || '',
            price: product.price || 0,
            compare_at_price: product.compare_at_price || null,
            stock_quantity: product.stock_quantity || 0,
            stock_status: product.stock_status || 'in_stock',
            meta_title: product.meta_title || '',
            meta_description: product.meta_description || '',
            meta_keywords: product.meta_keywords || '',
        };

        // Load media
        if (product.media && Array.isArray(product.media)) {
            const primaryMedia = product.media.find((m: any) => m.pivot?.is_primary);
            if (primaryMedia) {
                featuredImage.value = {
                    id: primaryMedia.id,
                    name: primaryMedia.name,
                    file_name: primaryMedia.file_name,
                    url: primaryMedia.url,
                    type: primaryMedia.type,
                    size: primaryMedia.size,
                    created_at: primaryMedia.created_at,
                    width: primaryMedia.width,
                    height: primaryMedia.height,
                };
            }

            // Load gallery (non-primary images, sorted by order)
            const galleryMedia = product.media
                .filter((m: any) => !m.pivot?.is_primary)
                .sort((a: any, b: any) => (a.pivot?.order || 0) - (b.pivot?.order || 0))
                .map((m: any) => ({
                    id: m.id,
                    name: m.name,
                    file_name: m.file_name,
                    url: m.url,
                    type: m.type,
                    size: m.size,
                    created_at: m.created_at,
                    width: m.width,
                    height: m.height,
                }));
            galleryImages.value = galleryMedia;
        }
    } catch (error: any) {
        console.error('Error loading product:', error);
        const message = error.response?.data?.message || 'Failed to load product';
        dialog.error(message);
    }
};

const saveProduct = async () => {
    loading.value = true;

    try {
        // Prepare data - sync description_html with description from TiptapEditor
        let descriptionHtml = form.value.description || '';
        
        // Check if description_html is empty or just empty paragraph
        const isEmptyDescription = !descriptionHtml || 
            descriptionHtml.trim() === '' || 
            descriptionHtml.trim() === '<p></p>' || 
            descriptionHtml.trim() === '<p><br></p>';
        
        if (isEmptyDescription) {
            descriptionHtml = null;
        }
        
        // Prepare media IDs
        const mediaIds: number[] = [];
        if (featuredImage.value) {
            mediaIds.push(featuredImage.value.id);
        }
        galleryImages.value.forEach(img => {
            if (!mediaIds.includes(img.id)) {
                mediaIds.push(img.id);
            }
        });

        const data: any = {
            name: form.value.name,
            slug: form.value.slug,
            sku: form.value.sku || null,
            status: form.value.status,
            description_html: descriptionHtml,
            price: form.value.price,
            compare_at_price: form.value.compare_at_price || null,
            stock_quantity: form.value.stock_quantity || 0,
            stock_status: form.value.stock_status,
            meta_title: form.value.meta_title || null,
            meta_description: form.value.meta_description || null,
            meta_keywords: form.value.meta_keywords || null,
            media_ids: mediaIds,
        };

        if (isEdit.value) {
            await axios.put(`/api/v1/products/${route.params.id}`, data);
        } else {
            await axios.post('/api/v1/products', data);
        }

        router.push({ name: 'admin.products.index' });
        dialog.success('Product saved successfully');
    } catch (error: any) {
        console.error('Error saving product:', error);
        const message = error.response?.data?.message || 'Failed to save product';
        dialog.error(message);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadProduct();
});
</script>
