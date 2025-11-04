<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Tags</h1>
            <button
                @click="createTag"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
                + New Tag
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search tags..."
                    class="px-3 py-2 border border-gray-300 rounded-lg"
                    @input="loadTags"
                />
                <select v-model="filters.type" @change="loadTags" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Types</option>
                    <option value="post">Post</option>
                    <option value="product">Product</option>
                </select>
            </div>
        </div>

        <!-- Tags Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="tag in tags" :key="tag.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ tag.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ tag.slug }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ tag.type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ tag.usage_count || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button
                                @click="editTag(tag.id)"
                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteTag(tag.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="tags.length === 0">
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No tags found.
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

const tags = ref<any[]>([]);
const filters = ref({
    search: '',
    type: '',
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
        if (filters.value.type) params.type = filters.value.type;

        const response = await axios.get('/api/v1/tags', { params });
        tags.value = response.data.data;
        pagination.value = {
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            per_page: response.data.meta.per_page,
            total: response.data.meta.total,
            from: response.data.meta.from,
            to: response.data.meta.to,
        };
    } catch (error) {
        console.error('Error loading tags:', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadTags();
};

const createTag = () => {
    const name = prompt('Enter tag name:');
    if (!name) return;

    const type = prompt('Enter tag type (post/product):', 'post');
    if (!type) return;

    const tag = { name, type, slug: name.toLowerCase().replace(/\s+/g, '-') };
    saveTag(tag);
};

const editTag = (id: number) => {
    const tag = tags.value.find(t => t.id === id);
    if (!tag) return;

    const name = prompt('Enter tag name:', tag.name);
    if (!name) return;

    const updatedTag = { ...tag, name, slug: name.toLowerCase().replace(/\s+/g, '-') };
    saveTag(updatedTag, id);
};

const saveTag = async (tag: any, id?: number) => {
    try {
        if (id) {
            await axios.put(`/api/v1/tags/${id}`, tag);
        } else {
            await axios.post('/api/v1/tags', tag);
        }
        loadTags();
    } catch (error) {
        console.error('Error saving tag:', error);
        alert('Failed to save tag');
    }
};

const deleteTag = async (id: number) => {
    if (!confirm('Are you sure you want to delete this tag?')) return;

    try {
        await axios.delete(`/api/v1/tags/${id}`);
        loadTags();
    } catch (error) {
        console.error('Error deleting tag:', error);
        alert('Failed to delete tag');
    }
};

onMounted(() => {
    loadTags();
});
</script>
