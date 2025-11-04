<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Posts</h1>
            <router-link
                :to="{ name: 'admin.posts.create' }"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
                + New Post
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search posts..."
                    class="px-3 py-2 border border-gray-300 rounded-lg"
                    @input="loadPosts"
                />
                <select v-model="filters.status" @change="loadPosts" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
                <select v-model="filters.type" @change="loadPosts" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Types</option>
                    <option value="post">Post</option>
                    <option value="page">Page</option>
                    <option value="news">News</option>
                </select>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Published</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="post in posts" :key="post.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ post.title }}</div>
                            <div class="text-sm text-gray-500">{{ post.slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ post.type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="[
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                post.status === 'published' ? 'bg-green-100 text-green-800' :
                                post.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-gray-100 text-gray-800'
                            ]">
                                {{ post.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ post.user?.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <router-link
                                :to="{ name: 'admin.posts.edit', params: { id: post.id } }"
                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                            >
                                Edit
                            </router-link>
                            <button
                                @click="deletePost(post.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="posts.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No posts found. <router-link :to="{ name: 'admin.posts.create' }" class="text-indigo-600">Create one</router-link>
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

const posts = ref<any[]>([]);
const filters = ref({
    search: '',
    status: '',
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

const loadPosts = async () => {
    try {
        const params: any = {
            page: pagination.value.current_page,
            per_page: pagination.value.per_page,
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.status) params.status = filters.value.status;
        if (filters.value.type) params.type = filters.value.type;

        const response = await axios.get('/api/v1/posts', { params });
        posts.value = response.data.data;
        pagination.value = {
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            per_page: response.data.meta.per_page,
            total: response.data.meta.total,
            from: response.data.meta.from,
            to: response.data.meta.to,
        };
    } catch (error) {
        console.error('Error loading posts:', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadPosts();
};

const deletePost = async (id: number) => {
    if (!confirm('Are you sure you want to delete this post?')) return;

    try {
        await axios.delete(`/api/v1/posts/${id}`);
        loadPosts();
    } catch (error) {
        console.error('Error deleting post:', error);
        alert('Failed to delete post');
    }
};

onMounted(() => {
    loadPosts();
});
</script>
