<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('Product Categories') || 'Product Categories' }}</h1>
            <router-link
                :to="{ name: 'admin.product-categories.create' }"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
                + {{ $t('New Category') || 'New Category' }}
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    :placeholder="$t('Search...') || 'Search categories...'"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                    @input="loadCategories"
                />
                <select v-model="filters.parent" @change="loadCategories" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">{{ $t('All Categories') || 'All Categories' }}</option>
                    <option value="0">{{ $t('Root Categories') || 'Root Categories' }}</option>
                    <option v-for="cat in allCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Name') || 'Name' }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Slug') || 'Slug' }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Parent') || 'Parent' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Actions') || 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="category in categories" :key="category.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a 
                                :href="category.frontend_url" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 hover:underline"
                            >
                                {{ category.name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ category.slug }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ category.parent?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <router-link
                                :to="{ name: 'admin.product-categories.edit', params: { id: category.id } }"
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
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
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
import { ref, onMounted, getCurrentInstance } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../../composables/useTranslation';
import { useDialog } from '../../../composables/useDialog';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;
const dialog = useDialog();

const categories = ref<any[]>([]);
const allCategories = ref<any[]>([]);
const filters = ref({
    search: '',
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

const loadCategories = async () => {
    try {
        const params: any = {
            page: pagination.value.current_page,
            per_page: pagination.value.per_page,
            with_parent: true,
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.parent !== '') params.parent_id = filters.value.parent === '0' ? null : filters.value.parent;

        const response = await axios.get('/api/v1/product-categories', { params });
        categories.value = response.data.data;
        
        if (response.data.meta) {
          pagination.value = {
              current_page: response.data.meta.current_page || 1,
              last_page: response.data.meta.last_page || 1,
              per_page: response.data.meta.per_page || 15,
              total: response.data.meta.total || 0,
              from: response.data.meta.from || 0,
              to: response.data.meta.to || 0,
          };
        } else {
           // Handle cases without standard pagination meta if needed
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
};

const loadAllCategories = async () => {
    try {
        const response = await axios.get('/api/v1/product-categories');
        allCategories.value = response.data.data;
    } catch (error) {
        console.error('Error loading all categories:', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadCategories();
};

const deleteCategory = async (id: number) => {
    const confirmed = await dialog.confirm({
        title: 'Delete Category',
        message: 'Are you sure you want to delete this category?',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });

    if (!confirmed) return;

    try {
        await axios.delete(`/api/v1/product-categories/${id}`);
        loadCategories();
        dialog.success('Category deleted successfully');
    } catch (error: any) {
        console.error('Error deleting category:', error);
        const message = error.response?.data?.message || 'Failed to delete category';
        dialog.error(message);
    }
};

onMounted(() => {
    loadCategories();
    loadAllCategories();
});
</script>
