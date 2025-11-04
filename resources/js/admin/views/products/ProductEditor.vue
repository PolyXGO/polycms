<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                {{ isEdit ? 'Edit Product' : 'Create New Product' }}
            </h1>
            <button
                @click="router.back()"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
                Cancel
            </button>
        </div>

        <form @submit.prevent="saveProduct" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                        <input
                            v-model="form.sku"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select v-model="form.status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>
                </div>
            </div>

            <!-- Pricing & Inventory -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Pricing & Inventory</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                        <input
                            v-model.number="form.price"
                            type="number"
                            step="0.01"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Compare at Price</label>
                        <input
                            v-model.number="form.compare_at_price"
                            type="number"
                            step="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                        <input
                            v-model.number="form.stock_quantity"
                            type="number"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">SEO Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                        <input v-model="form.meta_title" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Keywords</label>
                        <input v-model="form.meta_keywords" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                        <textarea
                            v-model="form.meta_description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        />
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
                    {{ loading ? 'Saving...' : 'Save Product' }}
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
const form = ref({
    name: '',
    slug: '',
    sku: '',
    status: 'draft',
    description: '',
    price: 0,
    compare_at_price: null as number | null,
    stock_quantity: 0,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

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
            description: product.description || '',
            price: product.price || 0,
            compare_at_price: product.compare_at_price || null,
            stock_quantity: product.stock_quantity || 0,
            meta_title: product.meta_title || '',
            meta_description: product.meta_description || '',
            meta_keywords: product.meta_keywords || '',
        };
    } catch (error) {
        console.error('Error loading product:', error);
        alert('Failed to load product');
    }
};

const saveProduct = async () => {
    loading.value = true;

    try {
        if (isEdit.value) {
            await axios.put(`/api/v1/products/${route.params.id}`, form.value);
        } else {
            await axios.post('/api/v1/products', form.value);
        }

        router.push({ name: 'admin.products.index' });
    } catch (error: any) {
        console.error('Error saving product:', error);
        if (error.response?.data?.message) {
            alert(error.response.data.message);
        } else {
            alert('Failed to save product');
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadProduct();
});
</script>
