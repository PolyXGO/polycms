<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-admin-theme-text">{{ $t('Dashboard') }}</h1>

    <!-- E-commerce Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Revenue -->
      <div class="flex items-center justify-between p-5 bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm hover:shadow-md transition-shadow">
        <div class="flex-1 min-w-0">
          <p class="text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Revenue') }}</p>
          <p class="text-2xl font-bold text-admin-theme-text mt-1">{{ formatCurrency(ecomStats.revenue || 0) }}</p>
          <p class="text-xs text-admin-theme-text-muted mt-1">{{ $t('Last') }} {{ ecomStats.period || 30 }} {{ $t('days') }}</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center flex-shrink-0 ml-4">
          <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>

      <!-- Orders -->
      <div class="flex items-center justify-between p-5 bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm hover:shadow-md transition-shadow">
        <div class="flex-1 min-w-0">
          <p class="text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Orders') }}</p>
          <p class="text-2xl font-bold text-admin-theme-text mt-1">{{ ecomStats.total_orders || 0 }}</p>
          <p class="text-xs text-admin-theme-text-muted mt-1">{{ ecomStats.pending_orders || 0 }} {{ $t('pending') }}</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center flex-shrink-0 ml-4">
          <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
      </div>

      <!-- Customers -->
      <div class="flex items-center justify-between p-5 bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm hover:shadow-md transition-shadow">
        <div class="flex-1 min-w-0">
          <p class="text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Customers') }}</p>
          <p class="text-2xl font-bold text-admin-theme-text mt-1">{{ ecomStats.new_customers || 0 }}</p>
          <p class="text-xs text-admin-theme-text-muted mt-1">{{ $t('New signups') }}</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center flex-shrink-0 ml-4">
          <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
      </div>

      <!-- Pending Reviews -->
      <div class="flex items-center justify-between p-5 bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm hover:shadow-md transition-shadow">
        <div class="flex-1 min-w-0">
          <p class="text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Reviews') }}</p>
          <p class="text-2xl font-bold text-admin-theme-text mt-1">{{ ecomStats.pending_reviews || 0 }}</p>
          <router-link :to="{ name: 'admin.reviews.index' }" class="text-xs text-admin-theme-text-muted hover:text-admin-theme-text transition-colors mt-1 inline-block">{{ $t('Moderate') }} &rarr;</router-link>
        </div>
        <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center flex-shrink-0 ml-4">
          <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        </div>
      </div>
    </div>

    <!-- CMS Content Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="p-5 bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Posts') }}</p>
            <p class="text-2xl font-bold text-admin-theme-text mt-1">{{ stats.posts }}</p>
          </div>
          <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center">
            <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
          </div>
        </div>
        <router-link :to="{ name: 'admin.posts.index' }" class="text-xs text-admin-theme-text-muted hover:text-admin-theme-text mt-4 inline-block transition-colors">{{ $t('View all') }} &rarr;</router-link>
      </div>

      <div class="p-5 bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Products') }}</p>
            <p class="text-2xl font-bold text-admin-theme-text mt-1">{{ stats.products }}</p>
          </div>
          <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center">
            <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
          </div>
        </div>
        <router-link :to="{ name: 'admin.products.index' }" class="text-xs text-admin-theme-text-muted hover:text-admin-theme-text mt-4 inline-block transition-colors">{{ $t('View all') }} &rarr;</router-link>
      </div>

      <div class="p-5 bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Categories') }}</p>
            <p class="text-2xl font-bold text-admin-theme-text mt-1">{{ stats.categories }}</p>
          </div>
          <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center">
            <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
          </div>
        </div>
        <router-link :to="{ name: 'admin.categories.index' }" class="text-xs text-admin-theme-text-muted hover:text-admin-theme-text mt-4 inline-block transition-colors">{{ $t('View all') }} &rarr;</router-link>
      </div>

      <div class="p-5 bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Media Files') }}</p>
            <p class="text-2xl font-bold text-admin-theme-text mt-1">{{ stats.media }}</p>
          </div>
          <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center">
            <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          </div>
        </div>
        <router-link :to="{ name: 'admin.media.index' }" class="text-xs text-admin-theme-text-muted hover:text-admin-theme-text mt-4 inline-block transition-colors">{{ $t('View media') }} &rarr;</router-link>
      </div>
    </div>

    <!-- Top Products + Recent Posts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Selling Products -->
      <div class="bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm">
        <div class="p-5 border-b border-admin-theme-border flex justify-between items-center">
          <h2 class="text-sm font-semibold text-admin-theme-text uppercase tracking-wider">{{ $t('Top Products') }}</h2>
        </div>
        <div class="p-2">
          <div v-if="!ecomStats.top_products?.length" class="text-center text-admin-theme-text-muted py-8 text-sm">
            {{ $t('No sales data yet') }}
          </div>
          <div v-else class="space-y-1">
            <div v-for="product in ecomStats.top_products" :key="product.id" class="flex items-center justify-between p-3 hover:bg-admin-theme-base rounded-lg transition-colors">
              <div class="min-w-0 pr-4">
                <router-link :to="`/admin/products/${product.id}/edit`" class="text-sm font-medium text-admin-theme-text hover:underline truncate block">
                  {{ product.name }}
                </router-link>
                <p class="text-xs text-admin-theme-text-muted mt-0.5">{{ product.total_sold }} {{ $t('sold') }}</p>
              </div>
              <span class="text-sm font-semibold text-admin-theme-text whitespace-nowrap">
                {{ formatCurrency(product.total_revenue) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Posts -->
      <div class="bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm">
        <div class="p-5 border-b border-admin-theme-border flex justify-between items-center">
          <h2 class="text-sm font-semibold text-admin-theme-text uppercase tracking-wider">{{ $t('Recent Posts') }}</h2>
          <router-link :to="{ name: 'admin.posts.index' }" class="text-xs font-medium text-admin-theme-text-muted hover:text-admin-theme-text transition-colors">{{ $t('View all') }}</router-link>
        </div>
        <div class="p-2">
          <div v-if="recentPosts.length === 0" class="text-center text-admin-theme-text-muted py-8 text-sm">
            {{ $t('No posts yet.') }} <router-link :to="{ name: 'admin.posts.create' }" class="hover:underline text-admin-theme-text">{{ $t('Create one') }}</router-link>
          </div>
          <div v-else class="space-y-1">
            <div v-for="post in recentPosts" :key="post.id" class="flex items-center justify-between p-3 hover:bg-admin-theme-base rounded-lg transition-colors">
              <div class="min-w-0 pr-4">
                <router-link :to="{ name: 'admin.posts.edit', params: { id: post.id } }" class="text-sm font-medium text-admin-theme-text hover:underline truncate block">
                  {{ post.title }}
                </router-link>
                <div class="flex items-center mt-1 text-xs text-admin-theme-text-muted">
                  <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="post.status === 'published' ? 'bg-green-500' : post.status === 'draft' ? 'bg-yellow-500' : 'bg-gray-400'"></span>
                  <span>{{ $t(post.status) }} • {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : $t('Not published') }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-sm p-5">
      <h2 class="text-sm font-semibold text-admin-theme-text mb-4 uppercase tracking-wider">{{ $t('Quick Actions') }}</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <router-link :to="{ name: 'admin.posts.create' }" class="flex items-center p-4 bg-admin-theme-surface border border-admin-theme-border rounded-xl hover:bg-admin-theme-base hover:border-admin-theme-text-muted transition-all shadow-sm">
          <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center mr-4 shrink-0">
            <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          </div>
          <div>
            <p class="font-semibold text-admin-theme-text text-sm">{{ $t('New Post') }}</p>
            <p class="text-xs text-admin-theme-text-muted mt-0.5">{{ $t('Add blog post') }}</p>
          </div>
        </router-link>
        
        <router-link :to="{ name: 'admin.products.create' }" class="flex items-center p-4 bg-admin-theme-surface border border-admin-theme-border rounded-xl hover:bg-admin-theme-base hover:border-admin-theme-text-muted transition-all shadow-sm">
          <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center mr-4 shrink-0">
            <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          </div>
          <div>
            <p class="font-semibold text-admin-theme-text text-sm">{{ $t('New Product') }}</p>
            <p class="text-xs text-admin-theme-text-muted mt-0.5">{{ $t('Add product') }}</p>
          </div>
        </router-link>

        <router-link :to="{ name: 'admin.orders.index' }" class="flex items-center p-4 bg-admin-theme-surface border border-admin-theme-border rounded-xl hover:bg-admin-theme-base hover:border-admin-theme-text-muted transition-all shadow-sm">
          <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center mr-4 shrink-0">
            <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          </div>
          <div>
            <p class="font-semibold text-admin-theme-text text-sm">{{ $t('View Orders') }}</p>
            <p class="text-xs text-admin-theme-text-muted mt-0.5">{{ $t('Manage orders') }}</p>
          </div>
        </router-link>

        <router-link :to="{ name: 'admin.media.index' }" class="flex items-center p-4 bg-admin-theme-surface border border-admin-theme-border rounded-xl hover:bg-admin-theme-base hover:border-admin-theme-text-muted transition-all shadow-sm">
          <div class="w-10 h-10 rounded-lg bg-admin-theme-base border border-admin-theme-border flex items-center justify-center mr-4 shrink-0">
            <svg class="w-5 h-5 text-admin-theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          </div>
          <div>
            <p class="font-semibold text-admin-theme-text text-sm">{{ $t('Upload Media') }}</p>
            <p class="text-xs text-admin-theme-text-muted mt-0.5">{{ $t('Manage library') }}</p>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, getCurrentInstance } from'vue';
import axios from'axios';
import { useRouter } from'vue-router';
import { useTranslation } from'../composables/useTranslation';
import { useCurrency } from'@/Composables/useCurrency';

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
 const recentPostsResponse = await axios.get('/api/v1/posts', { params: { per_page: 5, compact: 1, sort_by:'created_at', sort_order:'desc' } });
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
</style>
