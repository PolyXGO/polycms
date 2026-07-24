<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ $t('Tags') ||'Tags' }}</h1>
 <button
 @click="createTag"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 >
 + {{ $t('New Tag') ||'New Tag' }}
 </button>
 </div>

 <!-- Filters -->
 <div class="bg-admin-theme-base rounded-lg shadow p-4 mb-6">
 <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
 <input
 v-model="filters.search"
 type="text"
 :placeholder="$t('Search...') ||'Search tags...'"
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
 @input="loadTags"
 />
 <select v-model="filters.type" @change="loadTags" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('All Types') ||'All Types' }}</option>
 <option value="post">{{ $t('Post') ||'Post' }}</option>
 <option value="product">{{ $t('Product') ||'Product' }}</option>
 </select>
 <select v-model="filters.locale" @change="loadTags" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('All Languages') ||'All Languages' }}</option>
 <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
 </select>
 </div>
 </div>

 <!-- Tags Table -->
 <div class="bg-admin-theme-base rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-bg">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Name') ||'Name' }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Slug') ||'Slug' }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Type') ||'Type' }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Count') ||'Count' }}</th>
 <th class="px-6 py-3 text-right text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Actions') ||'Actions' }}</th>
 </tr>
 </thead>
 <tbody class="bg-admin-theme-base divide-y divide-admin-theme-border">
 <tr v-for="tag in tags" :key="tag.id">
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="text-sm font-medium text-admin-theme-text">{{ tag.name }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-secondary">
 {{ tag.slug }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-admin-theme-primary/20 text-blue-800">
 {{ tag.type }}
 </span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-secondary">
 {{ tag.usage_count || 0 }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <button
 @click="editTag(tag.id)"
 class="text-admin-theme-primary hover:text-admin-theme-primary mr-4"
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
 <td colspan="5" class="px-6 py-4 text-center text-admin-theme-text-secondary">
 No tags found.
 </td>
 </tr>
 </tbody>
 </table>
 </div>

 <!-- Pagination -->
 <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
 <div class="text-sm text-admin-theme-text">
 Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
 </div>
 <div class="flex space-x-2">
 <button
 @click="changePage(pagination.current_page - 1)"
 :disabled="pagination.current_page === 1"
 class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50"
 >
 Previous
 </button>
 <button
 @click="changePage(pagination.current_page + 1)"
 :disabled="pagination.current_page === pagination.last_page"
 class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50"
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
import { useRouter } from'vue-router';
import { useTranslation } from'../../composables/useTranslation';
import { isDemoRestrictionError } from'../../utils/demoRestriction';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();

const tags = ref<any[]>([]);
const languages = ref<any[]>([]);
const filters = ref({
 search:'',
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

const loadTags = async () => {
 try {
 const params: any = {
 page: pagination.value.current_page,
 per_page: pagination.value.per_page,
 };

 if (filters.value.search) params.search = filters.value.search;
 if (filters.value.type) params.type = filters.value.type;
 if (filters.value.locale) params.locale = filters.value.locale;

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

 const type = prompt('Enter tag type (post/product):','post');
 if (!type) return;

 const tag = { name, type, slug: name.toLowerCase().replace(/\s+/g,'-') };
 saveTag(tag);
};

const editTag = (id: number) => {
 const tag = tags.value.find(t => t.id === id);
 if (!tag) return;

 const name = prompt('Enter tag name:', tag.name);
 if (!name) return;

 const updatedTag = { ...tag, name, slug: name.toLowerCase().replace(/\s+/g,'-') };
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
 } catch (error: any) {
 if (isDemoRestrictionError(error)) return;
 console.error('Error saving tag:', error);
 alert('Failed to save tag');
 }
};

const deleteTag = async (id: number) => {
 if (!confirm('Are you sure you want to delete this tag?')) return;

 try {
 await axios.delete(`/api/v1/tags/${id}`);
 loadTags();
 } catch (error: any) {
 if (isDemoRestrictionError(error)) return;
 console.error('Error deleting tag:', error);
 alert('Failed to delete tag');
 }
};

onMounted(() => {
 loadLanguages();
 loadTags();
});
</script>
