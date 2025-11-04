<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Posts</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ stats.posts }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <router-link :to="{ name: 'admin.posts.index' }" class="text-sm text-blue-600 hover:text-blue-800 mt-4 inline-block">
                    View all posts →
                </router-link>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Products</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ stats.products }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <router-link :to="{ name: 'admin.products.index' }" class="text-sm text-green-600 hover:text-green-800 mt-4 inline-block">
                    View all products →
                </router-link>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Categories</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ stats.categories }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </div>
                <router-link :to="{ name: 'admin.categories.index' }" class="text-sm text-purple-600 hover:text-purple-800 mt-4 inline-block">
                    View all categories →
                </router-link>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Media Files</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ stats.media }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <router-link :to="{ name: 'admin.media.index' }" class="text-sm text-yellow-600 hover:text-yellow-800 mt-4 inline-block">
                    View media library →
                </router-link>
            </div>
        </div>

        <!-- Recent Posts -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Recent Posts</h2>
                <router-link :to="{ name: 'admin.posts.index' }" class="text-sm text-indigo-600 hover:text-indigo-800">
                    View all →
                </router-link>
            </div>
            <div class="p-6">
                <div v-if="recentPosts.length === 0" class="text-center text-gray-500 py-8">
                    No posts yet. <router-link :to="{ name: 'admin.posts.create' }" class="text-indigo-600 hover:text-indigo-800">Create your first post</router-link>
                </div>
                <div v-else class="space-y-4">
                    <div v-for="post in recentPosts" :key="post.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div>
                            <router-link
                                :to="{ name: 'admin.posts.edit', params: { id: post.id } }"
                                class="text-sm font-medium text-gray-900 hover:text-indigo-600"
                            >
                                {{ post.title }}
                            </router-link>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ post.status }} • {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : 'Not published' }}
                            </p>
                        </div>
                        <span :class="[
                            'px-2 py-1 text-xs font-semibold rounded-full',
                            post.status === 'published' ? 'bg-green-100 text-green-800' :
                            post.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                            'bg-gray-100 text-gray-800'
                        ]">
                            {{ post.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <router-link
                    :to="{ name: 'admin.posts.create' }"
                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <div class="p-2 bg-blue-100 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Create Post</p>
                        <p class="text-sm text-gray-500">Add a new blog post</p>
                    </div>
                </router-link>
                <router-link
                    :to="{ name: 'admin.products.create' }"
                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <div class="p-2 bg-green-100 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Create Product</p>
                        <p class="text-sm text-gray-500">Add a new product</p>
                    </div>
                </router-link>
                <router-link
                    :to="{ name: 'admin.media.index' }"
                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Upload Media</p>
                        <p class="text-sm text-gray-500">Manage media library</p>
                    </div>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

const stats = ref({
    posts: 0,
    products: 0,
    categories: 0,
    media: 0,
});

const recentPosts = ref<any[]>([]);

const loadStats = async () => {
    try {
        // Load posts count
        const postsResponse = await axios.get('/api/v1/posts', { params: { per_page: 1 } });
        stats.value.posts = postsResponse.data.meta?.total || 0;

        // Load recent posts
        const recentPostsResponse = await axios.get('/api/v1/posts', { params: { per_page: 5, sort: 'created_at', order: 'desc' } });
        recentPosts.value = recentPostsResponse.data.data || [];

        // Load products count
        const productsResponse = await axios.get('/api/v1/products', { params: { per_page: 1 } });
        stats.value.products = productsResponse.data.meta?.total || 0;

        // Load categories count
        const categoriesResponse = await axios.get('/api/v1/categories', { params: { per_page: 1 } });
        stats.value.categories = categoriesResponse.data.meta?.total || 0;

        // Load media count
        const mediaResponse = await axios.get('/api/v1/media', { params: { per_page: 1 } });
        stats.value.media = mediaResponse.data.meta?.total || 0;
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
};

onMounted(() => {
    loadStats();
});
</script>
