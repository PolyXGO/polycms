<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
            <button
                @click="createCategory"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
                + New Category
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search categories..."
                    class="px-3 py-2 border border-gray-300 rounded-lg"
                    @input="loadCategories"
                />
                <select v-model="filters.type" @change="loadCategories" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Types</option>
                    <option value="post">Post</option>
                    <option value="product">Product</option>
                </select>
                <select v-model="filters.parent" @change="loadCategories" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Categories</option>
                    <option value="0">Root Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="category in categories" :key="category.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ category.slug }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ category.type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ category.parent?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ category.usage_count || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button
                                @click="editCategory(category.id)"
                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteCategory(category.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No categories found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
            </div>
            <div class="flex space-x-2">
                <button
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50"
                >
                    Previous
                </button>
                <button
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50"
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

const router = useRouter();

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

const createCategory = () => {
    const name = prompt('Enter category name:');
    if (!name) return;

    const type = prompt('Enter category type (post/product):', 'post');
    if (!type) return;

    const parentId = prompt('Enter parent category ID (or leave empty for root):', '');
    
    const category: any = {
        name,
        type,
        slug: name.toLowerCase().replace(/\s+/g, '-'),
    };
    
    if (parentId) {
        category.parent_id = parseInt(parentId);
    }
    
    saveCategory(category);
};

const editCategory = (id: number) => {
    const category = categories.value.find(c => c.id === id);
    if (!category) return;

    const name = prompt('Enter category name:', category.name);
    if (!name) return;

    const updatedCategory = {
        ...category,
        name,
        slug: name.toLowerCase().replace(/\s+/g, '-'),
    };
    saveCategory(updatedCategory, id);
};

const saveCategory = async (category: any, id?: number) => {
    try {
        if (id) {
            await axios.put(`/api/v1/categories/${id}`, category);
        } else {
            await axios.post('/api/v1/categories', category);
        }
        loadCategories();
    } catch (error) {
        console.error('Error saving category:', error);
        alert('Failed to save category');
    }
};

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
