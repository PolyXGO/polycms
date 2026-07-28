<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ $t('Posts') ||'Posts' }}</h1>
 <router-link
 :to="{ name:'admin.posts.create' }"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 v-if="hasPermission('create post')"
 >
 + {{ $t('New Post') ||'New Post' }}
 </router-link>
 </div>

 <!-- Filters -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-4 mb-6">
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
 <input
 v-model="filters.search"
 type="text"
 :placeholder="$t('Search...') ||'Search posts...'"
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
 @input="loadPosts"
 />
 <select v-model="filters.status" @change="loadPosts" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('All Status') ||'All Status' }}</option>
 <option value="draft">{{ $t('Draft') ||'Draft' }}</option>
 <option value="published">{{ $t('Published') ||'Published' }}</option>
 <option value="archived">{{ $t('Archived') ||'Archived' }}</option>
 </select>
 <select v-model="filters.type" @change="loadPosts" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('All Types') ||'All Types' }}</option>
 <option value="post">{{ $t('Post') ||'Post' }}</option>
 <option value="page">{{ $t('Page') ||'Page' }}</option>
 <option value="news">{{ $t('News') ||'News' }}</option>
 </select>
 <select v-model="filters.locale" @change="loadPosts" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('All Languages') ||'All Languages' }}</option>
 <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
 </select>
 </div>
 </div>

 <AdminLoadingState v-if="loading" :message="$t('Loading posts...')" />

 <!-- Posts Table -->
 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
  <tr>
  <th class="w-16 px-4 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Image') }}</th>
  <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Title') ||'Title' }}</th>
  <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Type') ||'Type' }}</th>
  <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Status') ||'Status' }}</th>
  <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Author') ||'Author' }}</th>
  <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Published') ||'Published' }}</th>
  <th class="px-6 py-3 text-right text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Actions') ||'Actions' }}</th>
  </tr>
  </thead>
  <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
  <tr v-for="post in posts" :key="post.id">
  <td class="px-4 py-3 whitespace-nowrap">
  <div class="w-12 h-12 rounded-lg overflow-hidden bg-admin-theme-base flex items-center justify-center border border-admin-theme-border flex-shrink-0">
  <img
  v-if="post.thumbnail_url || post.featured_image_url || post.featured_image"
  :src="post.thumbnail_url || post.featured_image_url || post.featured_image"
  :alt="post.title"
  loading="lazy"
  decoding="async"
  class="w-full h-full object-cover polycms-lazy-img"
  @load="$event.target.classList.add('polycms-loaded')"
  />
  <svg v-else class="w-6 h-6 text-admin-theme-text-muted opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
  </svg>
  </div>
  </td>
  <td class="px-6 py-4 whitespace-nowrap">
  <a 
  :href="post.frontend_url" 
  target="_blank" 
  rel="noopener noreferrer"
  class="text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary hover:underline"
  >
  {{ post.title }}
  </a>
  <div class="text-sm text-admin-theme-text-muted">{{ post.slug }}</div>
  </td>
  <td class="px-6 py-4 whitespace-nowrap">
  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
  {{ $t(post.type) }}
  </span>
  </td>
  <td class="px-6 py-4 whitespace-nowrap">
  <span :class="[
 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
  post.status ==='published' ?'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
  post.status ==='draft' ?'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' :
 'bg-admin-theme-base text-admin-theme-text'
  ]">
  {{ $t(post.status) }}
  </span>
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
  {{ post.user?.name }}
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
  {{ post.published_at ? new Date(post.published_at).toLocaleDateString() :'-' }}
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
  <router-link
  :to="{ name:'admin.posts.edit', params: { id: post.id }, query: { type: post.type } }"
  class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary mr-4"
  >
  {{ $t('Edit') }}
  </router-link>
  <button
  @click="deletePost(post.id)"
  class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
  >
  {{ $t('Delete') }}
  </button>
  </td>
  </tr>
  <tr v-if="posts.length === 0">
  <td colspan="7" class="px-6 py-4 text-center text-admin-theme-text-muted">
 {{ $t('No posts found.') }} <router-link :to="{ name:'admin.posts.create' }" class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary-hover dark:hover:text-admin-theme-primary">{{ $t('Create one') }}</router-link>
 </td>
 </tr>
 </tbody>
 </table>
 </div>

 <!-- Pagination -->
 <div v-if="!loading && pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
 <div class="text-sm text-admin-theme-text-secondary">
 {{ $t('Showing') }} {{ pagination.from }} {{ $t('to') }} {{ pagination.to }} {{ $t('of') }} {{ pagination.total }} {{ $t('results') }}
 </div>
 <div class="flex space-x-2">
 <button
 @click="changePage(pagination.current_page - 1)"
 :disabled="pagination.current_page === 1"
 class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5"
 >
 {{ $t('Previous') }}
 </button>
 <button
 @click="changePage(pagination.current_page + 1)"
 :disabled="pagination.current_page === pagination.last_page"
 class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5"
 >
 {{ $t('Next') ||'Next' }}
 </button>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, getCurrentInstance, computed } from'vue';
import axios from'axios';
import AdminLoadingState from'../../components/AdminLoadingState.vue';
import { useRouter } from'vue-router';
import { useDialog } from'../../composables/useDialog';
import { useTranslation } from'../../composables/useTranslation';
import { useAuthStore } from'../../stores/auth';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const dialog = useDialog();
const authStore = useAuthStore();

const hasPermission = (permission: string) => {
    return authStore.can(permission);
};

const posts = ref<any[]>([]);
const loading = ref(true);
const languages = ref<any[]>([]);
const filters = ref({
 search:'',
 status:'',
 type:'',
 locale:'',
});
const pagination = ref({
 current_page: 1,
 last_page: 1,
 per_page: 15,
 total: 0,
 from: 0,
 to: 0,
});

const loadLanguages = async () => {
 try {
 const response = await axios.get('/api/v1/languages');
 if (response.data && response.data.data) {
 languages.value = response.data.data.filter((l: any) => l.is_active);
 }
 } catch (error) {
 console.error('Failed to load languages:', error);
 }
};

const loadPosts = async () => {
 loading.value = true;
 try {
 const params: any = {
 page: pagination.value.current_page,
 per_page: pagination.value.per_page,
 compact: 1,
 };

 if (filters.value.search) params.search = filters.value.search;
 if (filters.value.status) params.status = filters.value.status;
 if (filters.value.type) params.type = filters.value.type;
 if (filters.value.locale) params.locale = filters.value.locale;

 const response = await axios.get('/api/v1/posts', { params });
 posts.value = response.data.data;
 const meta = response.data.meta;
 pagination.value = {
 current_page: meta.current_page,
 last_page: meta.last_page,
 per_page: meta.per_page,
 total: Array.isArray(meta.total) ? meta.total[0] : meta.total,
 from: meta.from,
 to: meta.to,
 };
 } catch (error) {
 console.error('Error loading posts:', error);
 } finally {
 loading.value = false;
 }
};

const changePage = (page: number) => {
 pagination.value.current_page = page;
 loadPosts();
};

const deletePost = async (id: number) => {
 const confirmed = await dialog.confirm({
 title:'Delete Post',
 message:'Are you sure you want to delete this post?',
 confirmText:'Delete',
 confirmButtonText:'Delete',
 cancelText:'Cancel',
 type:'danger',
 });

 if (!confirmed) return;

 try {
 await axios.delete(`/api/v1/posts/${id}`);
 loadPosts();
 dialog.success('Post deleted successfully');
 } catch (error: any) {
 console.error('Error deleting post:', error);
 const message = error.response?.data?.message ||'Failed to delete post';
 dialog.error(message);
 }
};

const createRoute = computed(() => ({
 name:'admin.posts.create',
 ...(filters.value.type ? { query: { type: filters.value.type } } : {}),
}));

onMounted(() => {
 loadLanguages();
 loadPosts();
});
</script>
