<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ $t('Product Brands') ||'Product Brands' }}</h1>
 <router-link
 :to="{ name:'admin.product-brands.create' }"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 >
 + {{ $t('New Brand') ||'New Brand' }}
 </router-link>
 </div>

 <!-- Filters -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-4 mb-6">
 <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
 <input
 v-model="filters.search"
 type="text"
 :placeholder="$t('Search...') ||'Search brands...'"
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
 @input="loadBrands"
 />
 <select v-model="filters.parent" @change="loadBrands" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('All Brands') ||'All Brands' }}</option>
 <option value="0">{{ $t('Root Brands') ||'Root Brands' }}</option>
 <option v-for="b in allBrands" :key="b.id" :value="b.id">{{ b.name }}</option>
 </select>
 <select v-model="filters.locale" @change="onLocaleChange" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('All Languages') ||'All Languages' }}</option>
 <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
 </select>
 </div>
 </div>

 <AdminLoadingState v-if="loading" :message="$t('Loading product brands...')" />

 <!-- Brands Table -->
 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-bg">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Image') ||'Image' }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Name') ||'Name' }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Slug') ||'Slug' }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Parent') ||'Parent' }}</th>
 <th class="px-6 py-3 text-right text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Actions') ||'Actions' }}</th>
 </tr>
 </thead>
 <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
 <tr v-for="brand in brands" :key="brand.id">
 <td class="px-6 py-4 whitespace-nowrap">
 <img v-if="brand.image" :src="brand.image" class="h-10 w-10 object-contain rounded border border-admin-theme-border" />
 <div v-else class="h-10 w-10 flex items-center justify-center bg-admin-theme-base rounded text-admin-theme-text-muted">
 <i class="fas fa-image"></i>
 </div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <a 
 :href="brand.frontend_url" 
 target="_blank" 
 rel="noopener noreferrer"
 class="text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary hover:underline"
 >
 {{ brand.name }}
 </a>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ brand.slug }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ brand.parent?.name ||'-' }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <router-link
 :to="{ name:'admin.product-brands.edit', params: { id: brand.id } }"
 class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary mr-4"
 >
 Edit
 </router-link>
 <button
 @click="deleteBrand(brand.id)"
 class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
 >
 Delete
 </button>
 </td>
 </tr>
 <tr v-if="brands.length === 0">
 <td colspan="5" class="px-6 py-4 text-center text-admin-theme-text-muted">
 No brands found.
 </td>
 </tr>
 </tbody>
 </table>
 </div>
 <!-- Pagination -->
 <div v-if="!loading && pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
 <div class="text-sm text-admin-theme-text-secondary">
 Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
 </div>
 <div class="flex space-x-2">
 <button
 @click="changePage(pagination.current_page - 1)"
 :disabled="pagination.current_page === 1"
 class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-bg"
 >
 Previous
 </button>
 <button
 @click="changePage(pagination.current_page + 1)"
 :disabled="pagination.current_page === pagination.last_page"
 class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-bg"
 >
 Next
 </button>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, getCurrentInstance } from'vue';
import axios from'axios';
import AdminLoadingState from'../../../components/AdminLoadingState.vue';
import { useTranslation } from'../../../composables/useTranslation';
import { useDialog } from'../../../composables/useDialog';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;
const dialog = useDialog();

const brands = ref<any[]>([]);
const allBrands = ref<any[]>([]);
const languages = ref<any[]>([]);
const loading = ref(true);
const filters = ref({
 search:'',
 parent:'',
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

const loadBrands = async () => {
 loading.value = true;
 try {
 const params: any = {
 page: pagination.value.current_page,
 per_page: pagination.value.per_page,
 with_parent: true,
 };
 
 if (filters.value.search) params.search = filters.value.search;
 if (filters.value.parent !=='') params.parent_id = filters.value.parent ==='0' ? null : filters.value.parent;
 if (filters.value.locale) params.locale = filters.value.locale;

 const response = await axios.get('/api/v1/product-brands', { params });
 brands.value = response.data.data;
 
 if (response.data.meta) {
 pagination.value = {
 current_page: response.data.meta.current_page || 1,
 last_page: response.data.meta.last_page || 1,
 per_page: response.data.meta.per_page || 15,
 total: response.data.meta.total || 0,
 from: response.data.meta.from || 0,
 to: response.data.meta.to || 0,
 };
 }
 } catch (error) {
 console.error('Error loading brands:', error);
 } finally {
 loading.value = false;
 }
};

const loadAllBrands = async () => {
 try {
 const params: any = {};
 if (filters.value.locale) params.locale = filters.value.locale;
 const response = await axios.get('/api/v1/product-brands', { params });
 allBrands.value = response.data.data;
 } catch (error) {
 console.error('Error loading all brands:', error);
 }
};

const onLocaleChange = () => {
 loadBrands();
 loadAllBrands();
};

const changePage = (page: number) => {
 pagination.value.current_page = page;
 loadBrands();
};

const deleteBrand = async (id: number) => {
 const confirmed = await dialog.confirm({
 title:'Delete Brand',
 message:'Are you sure you want to delete this brand?',
 confirmText:'Delete',
 cancelText:'Cancel',
 type:'danger',
 });

 if (!confirmed) return;

 try {
 await axios.delete(`/api/v1/product-brands/${id}`);
 loadBrands();
 dialog.success('Brand deleted successfully');
 } catch (error: any) {
 console.error('Error deleting brand:', error);
 const message = error.response?.data?.message ||'Failed to delete brand';
 dialog.error(message);
 }
};

onMounted(() => {
 loadLanguages();
 loadBrands();
 loadAllBrands();
});
</script>
