<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ $t('Dashboard') }}</h1>

        <!-- E-commerce Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="ecom-stat-card">
                <div class="ecom-stat-card__body">
                    <p class="ecom-stat-card__label">{{ $t('Revenue') }}</p>
                    <p class="ecom-stat-card__value">{{ formatCurrency(ecomStats.revenue || 0) }}</p>
                    <p class="ecom-stat-card__period">{{ $t('Last') }} {{ ecomStats.period || 30 }} {{ $t('days') }}</p>
                </div>
                <div class="ecom-stat-card__icon ecom-stat-card__icon--revenue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="ecom-stat-card">
                <div class="ecom-stat-card__body">
                    <p class="ecom-stat-card__label">{{ $t('Orders') }}</p>
                    <p class="ecom-stat-card__value">{{ ecomStats.total_orders || 0 }}</p>
                    <p class="ecom-stat-card__period">{{ ecomStats.pending_orders || 0 }} {{ $t('pending') }}</p>
                </div>
                <div class="ecom-stat-card__icon ecom-stat-card__icon--orders">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>

            <div class="ecom-stat-card">
                <div class="ecom-stat-card__body">
                    <p class="ecom-stat-card__label">{{ $t('Customers') }}</p>
                    <p class="ecom-stat-card__value">{{ ecomStats.new_customers || 0 }}</p>
                    <p class="ecom-stat-card__period">{{ $t('New signups') }}</p>
                </div>
                <div class="ecom-stat-card__icon ecom-stat-card__icon--customers">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>

            <div class="ecom-stat-card">
                <div class="ecom-stat-card__body">
                    <p class="ecom-stat-card__label">{{ $t('Pending Reviews') }}</p>
                    <p class="ecom-stat-card__value">{{ ecomStats.pending_reviews || 0 }}</p>
                    <router-link :to="{ name: 'admin.reviews.index' }" class="ecom-stat-card__link">
                        {{ $t('Moderate') }} →
                    </router-link>
                </div>
                <div class="ecom-stat-card__icon ecom-stat-card__icon--reviews">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
            </div>
        </div>

        <!-- CMS Content Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t('Total Posts') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.posts }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                </div>
                <router-link :to="{ name: 'admin.posts.index' }" class="text-sm text-blue-600 hover:text-blue-800 mt-4 inline-block">{{ $t('View all posts') }} →</router-link>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t('Total Products') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.products }}</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                </div>
                <router-link :to="{ name: 'admin.products.index' }" class="text-sm text-green-600 hover:text-green-800 mt-4 inline-block">{{ $t('View all products') }} →</router-link>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t('Categories') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.categories }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                    </div>
                </div>
                <router-link :to="{ name: 'admin.categories.index' }" class="text-sm text-purple-600 hover:text-purple-800 mt-4 inline-block">{{ $t('View all categories') }} →</router-link>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t('Media Files') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.media }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                </div>
                <router-link :to="{ name: 'admin.media.index' }" class="text-sm text-yellow-600 hover:text-yellow-800 mt-4 inline-block">{{ $t('View media library') }} →</router-link>
            </div>
        </div>

        <!-- Top Products + Recent Orders -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top Selling Products -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('Top Selling Products') }}</h2>
                </div>
                <div class="p-4">
                    <div v-if="!ecomStats.top_products?.length" class="text-center text-gray-500 py-6 text-sm">
                        {{ $t('No sales data yet') }}
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="product in ecomStats.top_products" :key="product.id" class="flex items-center justify-between py-2">
                            <div class="min-w-0">
                                <router-link :to="`/admin/products/${product.id}/edit`" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 truncate block">
                                    {{ product.name }}
                                </router-link>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.total_sold }} {{ $t('sold') }}</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap ml-4">
                                {{ formatCurrency(product.total_revenue) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Posts -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('Recent Posts') }}</h2>
                    <router-link :to="{ name: 'admin.posts.index' }" class="text-sm text-indigo-600 hover:text-indigo-800">{{ $t('View all') }} →</router-link>
                </div>
                <div class="p-6">
                    <div v-if="recentPosts.length === 0" class="text-center text-gray-500 py-8">
                        {{ $t('No posts yet.') }} <router-link :to="{ name: 'admin.posts.create' }" class="text-indigo-600 hover:text-indigo-800">{{ $t('Create your first post') }}</router-link>
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="post in recentPosts" :key="post.id" class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div>
                                <router-link :to="{ name: 'admin.posts.edit', params: { id: post.id } }" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ post.title }}
                                </router-link>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $t(post.status) }} • {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : $t('Not published') }}
                                </p>
                            </div>
                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', post.status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : post.status === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300']">
                                {{ $t(post.status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('Quick Actions') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <router-link :to="{ name: 'admin.posts.create' }" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $t('New Post') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('Add a new blog post') }}</p>
                    </div>
                </router-link>
                <router-link :to="{ name: 'admin.products.create' }" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $t('New Product') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('Add a new product') }}</p>
                    </div>
                </router-link>
                <router-link :to="{ name: 'admin.orders.index' }" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $t('View Orders') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('Manage customer orders') }}</p>
                    </div>
                </router-link>
                <router-link :to="{ name: 'admin.media.index' }" class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $t('Upload Media') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('Manage media library') }}</p>
                    </div>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, getCurrentInstance } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useTranslation } from '../composables/useTranslation';
import { useCurrency } from '@/Composables/useCurrency';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;
const router = useRouter();
const { formatCurrency } = useCurrency();

const stats = ref({
    posts: 0,
    products: 0,
    categories: 0,
    media: 0,
});

const ecomStats = ref<any>({});
const recentPosts = ref<any[]>([]);

const loadStats = async () => {
    try {
        // Load posts count
        const postsResponse = await axios.get('/api/v1/posts', { params: { per_page: 1, compact: 1 } });
        const totalPosts = postsResponse.data.meta?.total || 0;
        stats.value.posts = Array.isArray(totalPosts) ? totalPosts[0] : totalPosts;

        // Load recent posts
        const recentPostsResponse = await axios.get('/api/v1/posts', { params: { per_page: 5, compact: 1, sort_by: 'created_at', sort_order: 'desc' } });
        recentPosts.value = recentPostsResponse.data.data || [];

        // Load products count
        const productsResponse = await axios.get('/api/v1/products', { params: { per_page: 1, compact: 1 } });
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

const loadEcomStats = async () => {
    try {
        const { data } = await axios.get('/api/v1/dashboard/stats', { params: { period: 30 } });
        ecomStats.value = data;
    } catch (error) {
        console.error('Error loading e-commerce stats:', error);
    }
};

onMounted(() => {
    loadStats();
    loadEcomStats();
});
</script>

<style scoped>
.ecom-stat-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: transform 0.15s, box-shadow 0.15s;
}
:root.dark .ecom-stat-card {
    background: #1e293b;
    border-color: #334155;
}
.ecom-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.ecom-stat-card__body { flex: 1; min-width: 0; }
.ecom-stat-card__label { font-size: 0.8125rem; font-weight: 500; color: #64748b; }
.ecom-stat-card__value { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin-top: 4px; }
:root.dark .ecom-stat-card__value { color: #f1f5f9; }
.ecom-stat-card__period { font-size: 0.6875rem; color: #94a3b8; margin-top: 4px; }
.ecom-stat-card__link { font-size: 0.75rem; color: #6366f1; text-decoration: none; margin-top: 4px; display: inline-block; }
.ecom-stat-card__link:hover { text-decoration: underline; }

.ecom-stat-card__icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-left: 16px;
}
.ecom-stat-card__icon svg { width: 24px; height: 24px; }
.ecom-stat-card__icon--revenue { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.ecom-stat-card__icon--orders { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.ecom-stat-card__icon--customers { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.ecom-stat-card__icon--reviews { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
</style>
