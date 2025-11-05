<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Products</h1>
            <router-link
                :to="{ name: 'admin.products.create' }"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
                + New Product
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search products..."
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                    @input="loadProducts"
                />
                <select v-model="filters.status" @change="loadProducts" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="product in products" :key="product.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ product.slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ product.sku || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ formatPrice(product.price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="[
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                product.status === 'published' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                product.status === 'draft' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' :
                                'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                            ]">
                                {{ product.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ product.stock_quantity || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <router-link
                                :to="{ name: 'admin.products.edit', params: { id: product.id } }"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-4"
                            >
                                Edit
                            </router-link>
                            <button
                                @click="deleteProduct(product.id)"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="products.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No products found. <router-link :to="{ name: 'admin.products.create' }" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">Create one</router-link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
            </div>
            <div class="flex space-x-2">
                <button
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                    Previous
                </button>
                <button
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useDialog } from '../../composables/useDialog';

const router = useRouter();
const dialog = useDialog();

const products = ref<any[]>([]);
const filters = ref({
    search: '',
    status: '',
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
});

const formatPrice = (price: number) => {
    if (!price) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const loadProducts = async () => {
    try {
        const params: any = {
            page: pagination.value.current_page,
            per_page: pagination.value.per_page,
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.status) params.status = filters.value.status;

        const response = await axios.get('/api/v1/products', { params });
        products.value = response.data.data;
        pagination.value = {
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            per_page: response.data.meta.per_page,
            total: response.data.meta.total,
            from: response.data.meta.from,
            to: response.data.meta.to,
        };
    } catch (error) {
        console.error('Error loading products:', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadProducts();
};

const deleteProduct = async (id: number) => {
    const confirmed = await dialog.confirm({
        title: 'Delete Product',
        message: 'Are you sure you want to delete this product?',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });

    if (!confirmed) return;

    try {
        await axios.delete(`/api/v1/products/${id}`);
        loadProducts();
        dialog.success('Product deleted successfully');
    } catch (error: any) {
        console.error('Error deleting product:', error);
        const message = error.response?.data?.message || 'Failed to delete product';
        dialog.error(message);
    }
};

onMounted(() => {
    loadProducts();
});
</script>
