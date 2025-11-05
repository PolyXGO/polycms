<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Product Tags</h1>
            <router-link
                :to="{ name: 'admin.product-tags.create' }"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
                + New Tag
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <input
                v-model="filters.search"
                type="text"
                placeholder="Search tags..."
                class="w-full md:max-w-md px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                @input="loadTags"
            />
        </div>

        <!-- Tags Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="tag in tags" :key="tag.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ tag.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ tag.slug }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="max-w-xs truncate">{{ tag.description || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <router-link
                                :to="{ name: 'admin.product-tags.edit', params: { id: tag.id } }"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-4"
                            >
                                Edit
                            </router-link>
                            <button
                                @click="deleteTag(tag.id)"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="tags.length === 0">
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No tags found.
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
import { useDialog } from '../../../composables/useDialog';

const router = useRouter();
const dialog = useDialog();

const tags = ref<any[]>([]);
const filters = ref({
    search: '',
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
});

const loadTags = async () => {
    try {
        const params: any = {
            page: pagination.value.current_page,
            per_page: pagination.value.per_page,
        };

        if (filters.value.search) params.search = filters.value.search;

        const response = await axios.get('/api/v1/product-tags', { params });
        tags.value = response.data.data;
        if (response.data.meta?.pagination) {
            const meta = response.data.meta.pagination;
            pagination.value = {
                current_page: meta.current_page || 1,
                last_page: meta.last_page || 1,
                per_page: meta.per_page || 15,
                total: meta.total || 0,
                from: ((meta.current_page || 1) - 1) * (meta.per_page || 15) + 1,
                to: Math.min((meta.current_page || 1) * (meta.per_page || 15), meta.total || 0),
            };
        }
    } catch (error) {
        console.error('Error loading tags:', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadTags();
};

const deleteTag = async (id: number) => {
    const confirmed = await dialog.confirm({
        title: 'Delete Tag',
        message: 'Are you sure you want to delete this tag?',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });

    if (!confirmed) return;

    try {
        await axios.delete(`/api/v1/product-tags/${id}`);
        loadTags();
        dialog.success('Tag deleted successfully');
    } catch (error: any) {
        console.error('Error deleting tag:', error);
        const message = error.response?.data?.message || 'Failed to delete tag';
        dialog.error(message);
    }
};

onMounted(() => {
    loadTags();
});
</script>
