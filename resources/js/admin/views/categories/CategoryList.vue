<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ $t('Categories') }}</h1>
 <router-link
 :to="createRoute"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 >
 + {{ $t('New Category') }}
 </router-link>
 </div>

 <!-- Filters -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-4 mb-6">
 <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
 <input
 v-model="filters.search"
 type="text"
 :placeholder="$t('Search categories...')"
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
 @input="loadCategories"
 />
 <select v-model="filters.parent" @change="loadCategories" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('All Categories') }}</option>
 <option value="0">{{ $t('Root Categories') }}</option>
 <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
 </select>
 <select v-model="filters.locale" @change="loadCategories" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('All Languages') }}</option>
 <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
 </select>
 </div>
 </div>

 <AdminLoadingState v-if="loading" :message="$t('Loading categories...')" />

 <!-- Categories Table -->
 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Name') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Slug') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Parent') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Count') }}</th>
 <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Actions') }}</th>
 </tr>
 </thead>
 <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
 <tr v-for="category in categories" :key="category.id">
 <td class="px-6 py-4 whitespace-nowrap">
 <a 
 :href="category.frontend_url" 
 target="_blank" 
 rel="noopener noreferrer"
 class="text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary hover:underline"
 >
 {{ category.name }}
 </a>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ category.slug }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ category.parent?.name ||'-' }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ category.posts_count || 0 }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <router-link
 :to="editRoute(category.id)"
 class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary mr-4"
 >
 {{ $t('Edit') }}
 </router-link>
 <button
 @click="deleteCategory(category.id)"
 class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
 >
 {{ $t('Delete') }}
 </button>
 </td>
 </tr>
 <tr v-if="categories.length === 0">
 <td colspan="5" class="px-6 py-4 text-center text-admin-theme-text-muted">
 {{ $t('No categories found.') }}
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
 {{ $t('Next') }}
 </button>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, getCurrentInstance } from'vue';
import axios from'axios';
import AdminLoadingState from'../../components/AdminLoadingState.vue';
import { useRouter, useRoute } from'vue-router';
import { useTranslation } from'../../composables/useTranslation';
import { useDialog } from'../../composables/useDialog';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const route = useRoute();
const dialog = useDialog();

const categories = ref<any[]>([]);
const loading = ref(true);
const languages = ref<any[]>([]);
const filters = ref({
 search:'',
 parent:'',
 locale:'',
});
const routeType = computed(() => {
    if (typeof route.meta.type === 'string') return route.meta.type;
    return typeof route.query.type === 'string' ? route.query.type : '';
});
const routeNamePrefix = computed(() => {
 if (typeof route.name === 'string' && route.name.startsWith('admin.project-hub.categories.')) return 'admin.project-hub.categories';
 return 'admin.categories';
});
const createRoute = computed(() => ({
 name:`${routeNamePrefix.value}.create`,
 ...(routeType.value ? { query: { type: routeType.value } } : {}),
}));
const editRoute = (id: number) => ({
 name:`${routeNamePrefix.value}.edit`,
 params: { id },
 ...(routeType.value ? { query: { type: routeType.value } } : {}),
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

const loadCategories = async () => {
 loading.value = true;
 try {
 const params: any = {
 page: pagination.value.current_page,
 per_page: pagination.value.per_page,
 };

 if (filters.value.search) params.search = filters.value.search;
 if (filters.value.parent !=='') params.parent_id = filters.value.parent ==='0' ? null : filters.value.parent;
 if (routeType.value) params.type = routeType.value;
 if (filters.value.locale) params.locale = filters.value.locale;

 const response = await axios.get('/api/v1/categories', { params });
 categories.value = response.data.data;
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
 console.error('Error loading categories:', error);
 } finally {
 loading.value = false;
 }
};

const changePage = (page: number) => {
 pagination.value.current_page = page;
 loadCategories();
};

const deleteCategory = async (id: number) => {
 const confirmed = await dialog.confirm({
 title: $t('Delete Category'),
 message: $t('Are you sure you want to delete this category?'),
 confirmText: $t('Delete'),
 cancelText: $t('Cancel'),
 type:'danger',
 });

 if (!confirmed) return;

 try {
 await axios.delete(`/api/v1/categories/${id}`);
 loadCategories();
 dialog.success($t('Category deleted successfully'));
 } catch (error: any) {
 console.error('Error deleting category:', error);
 const message = error.response?.data?.message || $t('Failed to delete category');
 dialog.error(message);
 }
};

onMounted(() => {
 loadLanguages();
 loadCategories();
});
</script>
