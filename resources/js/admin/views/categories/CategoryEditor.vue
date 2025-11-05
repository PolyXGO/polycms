<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ isEdit ? $t('Edit Category') || 'Edit Category' : $t('Create Category') || 'Create New Category' }}
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

        <form @submit.prevent="saveCategory" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Basic Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name (Title) *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="Category name"
                            @input="generateSlug"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug *</label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="category-slug"
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
                    <div v-if="!isTypeFixed">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                        <select v-model="form.type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Select type</option>
                            <option value="post">Post</option>
                            <option value="product">Product</option>
                        </select>
                    </div>
                    <div v-else>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            {{ form.type === 'post' ? 'Post' : 'Product' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parent Category</label>
                        <select v-model="form.parent_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option :value="null">None (Root Category)</option>
                            <option
                                v-for="category in parentCategories"
                                :key="category.id"
                                :value="category.id"
                                :disabled="category.id === parseInt(String(route.params.id))"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Summary</label>
                        <textarea
                            v-model="form.summary"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="Short summary of the category..."
                        />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <TiptapEditor v-model="form.description" :placeholder="$t('Enter category description...') || 'Enter category description...'" />
                    </div>
                    </div>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Category Image</h2>
                <div class="space-y-4">
                    <div v-if="form.image || imagePreview" class="relative">
                        <img
                            :src="imagePreview || form.image"
                            alt="Category image preview"
                            class="max-w-md h-64 object-cover rounded-lg border border-gray-300 dark:border-gray-600"
                        />
                        <button
                            v-if="form.image || imagePreview"
                            type="button"
                            @click="removeImage"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload Image</label>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            @change="handleImageUpload"
                            class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/20 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/30"
                        />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">PNG, JPG, GIF up to 10MB</p>
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
                    {{ loading ? $t('Saving') : (isEdit ? $t('Update Category') : $t('Create Category')) }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch, getCurrentInstance } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import TiptapEditor from '../../components/TiptapEditor.vue';
import { useSlugify } from '../../composables/useSlugify';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const route = useRoute();
const { slugify } = useSlugify();
const dialog = useDialog();

const isEdit = computed(() => !!route.params.id);

const loading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const imagePreview = ref<string | null>(null);
const parentCategories = ref<any[]>([]);
const slugManuallyEdited = ref(false);

// Get type from query params or default to 'post'
const defaultType = computed(() => {
    if (route.query.type && typeof route.query.type === 'string') {
        return route.query.type;
    }
    return 'post';
});

// Check if type is fixed (from query params)
const isTypeFixed = computed(() => {
    return !isEdit.value && route.query.type && typeof route.query.type === 'string';
});

const form = ref({
    name: '',
    slug: '',
    type: defaultType.value,
    parent_id: null as number | null,
    description: '',
    summary: '',
    image: '',
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
    return `${baseUrl}/categories/${form.value.slug}`;
};

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);

    // Upload file first
    uploadImage(file);
};

const uploadImage = async (file: File) => {
    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await axios.post('/api/v1/media', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        form.value.image = response.data.data.url;
    } catch (error) {
        console.error('Error uploading image:', error);
        dialog.error($t('Failed to upload image') || 'Failed to upload image');
    }
};

const removeImage = () => {
    form.value.image = '';
    imagePreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const loadParentCategories = async () => {
    try {
        const response = await axios.get('/api/v1/categories', {
            params: { per_page: 100, type: form.value.type },
        });
        parentCategories.value = response.data.data || [];
    } catch (error) {
        console.error('Error loading parent categories:', error);
    }
};

const loadCategory = async () => {
    if (!isEdit.value) return;

    try {
        const response = await axios.get(`/api/v1/categories/${route.params.id}`);
        const category = response.data.data;
        form.value = {
            name: category.name,
            slug: category.slug,
            type: category.type,
            parent_id: category.parent_id || null,
            description: category.description || '',
            summary: category.summary || '', // If summary field exists
            image: category.image || '',
        };
        // Load parent categories for the same type
        await loadParentCategories();
    } catch (error) {
        console.error('Error loading category:', error);
        dialog.error($t('Failed to load category') || 'Failed to load category');
    }
};

const saveCategory = async () => {
    loading.value = true;

    try {
        const data: any = {
            name: form.value.name,
            slug: form.value.slug,
            type: form.value.type,
            parent_id: form.value.parent_id,
            summary: form.value.summary || null,
            description: form.value.description,
            image: form.value.image || null,
        };

        if (isEdit.value) {
            await axios.put(`/api/v1/categories/${route.params.id}`, data);
            // Reload category data to reflect changes, but stay on edit page
            await loadCategory();
            dialog.success($t('Category updated successfully') || 'Category updated successfully');
        } else {
            const response = await axios.post('/api/v1/categories', data);
            // Redirect to edit page after creating
            dialog.success($t('Category created successfully') || 'Category created successfully');
            router.push({
                name: 'admin.categories.edit',
                params: { id: response.data.data.id },
                query: route.query
            });
        }
    } catch (error: any) {
        console.error('Error saving category:', error);
        const message = error.response?.data?.message || ($t('Failed to save category') || 'Failed to save category');
        dialog.error(message);
    } finally {
        loading.value = false;
    }
};

// Watch for type changes to reload parent categories
watch(() => form.value.type, async () => {
    if (form.value.type) {
        await loadParentCategories();
        // Reset parent_id when type changes
        if (form.value.parent_id) {
            const parentExists = parentCategories.value.find(c => c.id === form.value.parent_id);
            if (!parentExists) {
                form.value.parent_id = null;
            }
        }
    }
});

onMounted(async () => {
    if (isEdit.value) {
        await loadCategory();
    } else {
        await loadParentCategories();
    }
});
</script>
