<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold text-admin-theme-text">Product Tags</h1>
 <router-link
 :to="{ name:'admin.product-tags.create' }"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 >
 + New Tag
 </router-link>
 </div>

 <!-- Filters -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-4 mb-6">
 <div class="flex flex-col md:flex-row gap-4">
 <input
 v-model="filters.search"
 type="text"
 placeholder="Search tags..."
 class="w-full md:max-w-md px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
 @input="loadTags"
 />
 <select v-model="filters.locale" @change="loadTags" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">All Languages</option>
 <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
 </select>
 </div>
 </div>

 <AdminLoadingState v-if="loading" message="Loading product tags..." />

 <!-- Tags Table -->
 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-bg">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">Name</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">Slug</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">Description</th>
 <th class="px-6 py-3 text-right text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">Actions</th>
 </tr>
 </thead>
 <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
 <tr v-for="tag in tags" :key="tag.id">
 <td class="px-6 py-4 whitespace-nowrap">
 <a
 :href="tag.frontend_url"
 target="_blank"
 rel="noopener noreferrer"
 class="text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary hover:underline"
 >
 {{ tag.name }}
 </a>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ tag.slug }}
 </td>
 <td class="px-6 py-4 text-sm text-admin-theme-text-muted">
 <div class="max-w-xs truncate">{{ tag.description ||'-' }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <router-link
 :to="{ name:'admin.product-tags.edit', params: { id: tag.id } }"
 class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary mr-4"
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
 <td colspan="4" class="px-6 py-4 text-center text-admin-theme-text-secondary">
 No tags found.
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
import { ref, onMounted } from'vue';
import axios from'axios';
import AdminLoadingState from'../../../components/AdminLoadingState.vue';
import { useRouter } from'vue-router';
import { useDialog } from'../../../composables/useDialog';

const router = useRouter();
const dialog = useDialog();

const tags = ref<any[]>([]);
const languages = ref<any[]>([]);
const loading = ref(true);
const filters = ref({
 search:'',
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

const loadTags = async () => {
 loading.value = true;
 try {
 const params: any = {
 page: pagination.value.current_page,
 per_page: pagination.value.per_page,
 };

 if (filters.value.search) params.search = filters.value.search;
 if (filters.value.locale) params.locale = filters.value.locale;

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
 } finally {
 loading.value = false;
 }
};

const changePage = (page: number) => {
 pagination.value.current_page = page;
 loadTags();
};

const deleteTag = async (id: number) => {
 const confirmed = await dialog.confirm({
 title:'Delete Tag',
 message:'Are you sure you want to delete this tag?',
 confirmText:'Delete',
 cancelText:'Cancel',
 type:'danger',
 });

 if (!confirmed) return;

 try {
 await axios.delete(`/api/v1/product-tags/${id}`);
 loadTags();
 dialog.success('Tag deleted successfully');
 } catch (error: any) {
 console.error('Error deleting tag:', error);
 const message = error.response?.data?.message ||'Failed to delete tag';
 dialog.error(message);
 }
};

onMounted(() => {
 loadLanguages();
 loadTags();
});
</script>
