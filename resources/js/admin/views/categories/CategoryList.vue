<template>
    <div>
                        <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Categories</h1>
                    <router-link
                        :to="{ 
                            name: 'admin.categories.create',
                            query: filters.type ? { type: filters.type } : {}
                        }"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                    >
                        + New Category
                    </router-link>
                </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search categories..."
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                    @input="loadCategories"
                />
                <select v-model="filters.type" @change="loadCategories" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">All Types</option>
                    <option value="post">Post</option>
                    <option value="product">Product</option>
                </select>
                <select v-model="filters.parent" @change="loadCategories" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">All Categories</option>
                    <option value="0">Root Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Count</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="category in categories" :key="category.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ category.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ category.slug }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                {{ category.type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ category.parent?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ category.usage_count || 0 }}
                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <router-link
                                        :to="{ name: 'admin.categories.edit', params: { id: category.id } }"
                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-4"
                                    >
                                        Edit
                                    </router-link>
                                    <button
                                        @click="deleteCategory(category.id)"
                                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                    >
                                        Delete
                                    </button>
                                </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No categories found.
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
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();

const categories = ref<any[]>([]);
const filters = ref({
    search: '',
    type: '',
    parent: '',
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
});

// Initialize type filter from query params
if (route.query.type && typeof route.query.type === 'string') {
    filters.value.type = route.query.type;
}

const loadCategories = async () => {
    try {
        const params: any = {
            page: pagination.value.current_page,
            per_page: pagination.value.per_page,
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.type) params.type = filters.value.type;
        if (filters.value.parent !== '') params.parent_id = filters.value.parent === '0' ? null : filters.value.parent;

        const response = await axios.get('/api/v1/categories', { params });
        categories.value = response.data.data;
        pagination.value = {
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            per_page: response.data.meta.per_page,
            total: response.data.meta.total,
            from: response.data.meta.from,
            to: response.data.meta.to,
        };
    } catch (error) {
        console.error('Error loading categories:', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadCategories();
};

// Watch for query param changes
watch(() => route.query.type, (newType) => {
    if (newType && typeof newType === 'string') {
        filters.value.type = newType;
        loadCategories();
    }
}, { immediate: false });



const deleteCategory = async (id: number) => {
    if (!confirm('Are you sure you want to delete this category?')) return;

    try {
        await axios.delete(`/api/v1/categories/${id}`);
        loadCategories();
    } catch (error) {
        console.error('Error deleting category:', error);
        alert('Failed to delete category');
    }
};

onMounted(() => {
    loadCategories();
});
</script>
