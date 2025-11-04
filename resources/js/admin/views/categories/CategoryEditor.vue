<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                {{ isEdit ? 'Edit Category' : 'Create New Category' }}
            </h1>
            <button
                @click="router.back()"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
                Cancel
            </button>
        </div>

        <form @submit.prevent="saveCategory" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name (Title) *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            placeholder="Category name"
                            @input="generateSlug"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            placeholder="category-slug"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select v-model="form.type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Select type</option>
                            <option value="post">Post</option>
                            <option value="product">Product</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                        <select v-model="form.parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option :value="null">None (Root Category)</option>
                            <option
                                v-for="category in parentCategories"
                                :key="category.id"
                                :value="category.id"
                                :disabled="category.id === parseInt(route.params.id)"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Summary</label>
                        <textarea
                            v-model="form.summary"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            placeholder="Short summary of the category..."
                        />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="6"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            placeholder="Full description of the category..."
                        />
                    </div>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Category Image</h2>
                <div class="space-y-4">
                    <div v-if="form.image || imagePreview" class="relative">
                        <img
                            :src="imagePreview || form.image"
                            alt="Category image preview"
                            class="max-w-md h-64 object-cover rounded-lg border border-gray-300"
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Image</label>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            @change="handleImageUpload"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <p class="mt-1 text-sm text-gray-500">PNG, JPG, GIF up to 10MB</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    @click="router.back()"
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ loading ? 'Saving...' : (isEdit ? 'Update Category' : 'Create Category') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

const isEdit = computed(() => !!route.params.id);

const loading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const imagePreview = ref<string | null>(null);
const parentCategories = ref<any[]>([]);

const form = ref({
    name: '',
    slug: '',
    type: 'post',
    parent_id: null as number | null,
    description: '',
    summary: '',
    image: '',
});

const generateSlug = () => {
    if (!isEdit.value) {
        form.value.slug = form.value.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
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
        alert('Failed to upload image');
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
        alert('Failed to load category');
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
        } else {
            await axios.post('/api/v1/categories', data);
        }

        router.push({ name: 'admin.categories.index' });
    } catch (error: any) {
        console.error('Error saving category:', error);
        if (error.response?.data?.message) {
            alert(error.response.data.message);
        } else {
            alert('Failed to save category');
        }
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    if (isEdit.value) {
        await loadCategory();
    } else {
        await loadParentCategories();
    }
});
</script>
