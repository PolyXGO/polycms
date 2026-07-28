<template>
 <div class="space-y-6">
 <div class="flex items-center justify-between">
 <div>
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Users') ||'Users' }}</h1>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ t('Manage all accounts, roles, and permissions for the system.') }}
 </p>
 </div>
 <router-link
 :to="{ name:'admin.users.create' }"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 >
 + {{ t('New User') ||'New User' }}
 </router-link>
 </div>

 <div class="bg-admin-theme-surface rounded-lg shadow p-4">
 <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
 <input
 v-model="filters.search"
 type="text"
 :placeholder="t('Search users...') ||'Search users...'"
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
 @input="debouncedLoad()"
 />
 <select
 v-model="filters.role"
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
 @change="loadUsers"
 >
 <option value="">{{ t('All roles') ||'All roles' }}</option>
 <option v-for="role in availableRoles" :key="role" :value="role">
 {{ role }}
 </option>
 </select>
 </div>
 </div>

 <AdminLoadingState v-if="loading" :message="t('Loading users...')" />

 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Name') ||'Name' }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Email') ||'Email' }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Roles') ||'Roles' }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Created At') ||'Created At' }}
 </th>
 <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Actions') ||'Actions' }}
 </th>
 </tr>
 </thead>
 <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
 <tr v-for="user in users" :key="user.id">
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="text-sm font-semibold text-admin-theme-text">
 {{ user.name }}
 </div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
 {{ user.email }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
 <span
 v-for="role in user.roles"
 :key="role"
 class="inline-flex items-center px-2 py-1 mr-2 mb-1 text-xs font-medium rounded-full bg-admin-theme-primary/15 text-admin-theme-primary-hover dark:text-admin-theme-primary"
 >
 {{ role }}
 </span>
 <span v-if="user.roles.length === 0" class="text-gray-400 text-sm">
 {{ t('No roles') ||'No roles' }}
 </span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
 {{ formatDate(user.created_at) }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
 <router-link
 :to="{ name:'admin.users.edit', params: { id: user.id } }"
 class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary"
 >
 {{ t('Edit') ||'Edit' }}
 </router-link>
 <button
 class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
 @click="deleteUser(user)"
 >
 {{ t('Delete') ||'Delete' }}
 </button>
 </td>
 </tr>
 <tr v-if="users.length === 0">
 <td colspan="5" class="px-6 py-4 text-center text-admin-theme-text-muted">
 {{ t('No users found.') ||'No users found.' }}
 </td>
 </tr>
 </tbody>
 </table>
 </div>

 <div v-if="!loading && pagination.total > pagination.per_page" class="flex items-center justify-between">
 <div class="text-sm text-admin-theme-text-secondary">
 {{ t('Showing') ||'Showing' }} {{ pagination.from || 0 }} {{ t('to') ||'to' }} {{ pagination.to || 0 }}
 {{ t('of') ||'of' }} {{ pagination.total }} {{ t('results') ||'results' }}
 </div>
 <div class="flex space-x-2">
 <button
 class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5"
 :disabled="pagination.current_page === 1"
 @click="changePage(pagination.current_page - 1)"
 >
 {{ t('Previous') ||'Previous' }}
 </button>
 <button
 class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5"
 :disabled="pagination.current_page === pagination.last_page"
 @click="changePage(pagination.current_page + 1)"
 >
 {{ t('Next') ||'Next' }}
 </button>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from'vue';
import { useRouter } from'vue-router';
import axios from'axios';
import AdminLoadingState from'../../components/AdminLoadingState.vue';
import { useDialog } from'@/admin/composables/useDialog';
import { t } from'@/admin/composables/useTranslation';

const router = useRouter();
const dialog = useDialog();

interface UserItem {
 id: number | string;
 name: string;
 email: string;
 roles: string[];
 created_at: string | null;
}

const users = ref<UserItem[]>([]);
const availableRoles = ref<string[]>([]);
const loading = ref(true);
const filters = ref({
 search:'',
 role:'',
});

const pagination = ref({
 current_page: 1,
 last_page: 1,
 per_page: 15,
 total: 0,
 from: 0,
 to: 0,
});

let debounceTimeout: number | undefined;

const debouncedLoad = () => {
 window.clearTimeout(debounceTimeout);
 debounceTimeout = window.setTimeout(() => {
 pagination.value.current_page = 1;
 loadUsers();
 }, 300);
};

const loadUsers = async () => {
 loading.value = true;
 try {
 const params: Record<string, any> = {
 page: pagination.value.current_page,
 per_page: pagination.value.per_page,
 };

 if (filters.value.search) {
 params.search = filters.value.search;
 }

 if (filters.value.role) {
 params.role = filters.value.role;
 }

 const response = await axios.get('/api/v1/users', { params });
 users.value = response.data.data ?? [];

 const meta = response.data.meta ?? {};
 pagination.value = {
 current_page: meta.current_page ?? 1,
 last_page: meta.last_page ?? 1,
 per_page: meta.per_page ?? pagination.value.per_page,
 total: meta.total ?? users.value.length,
 from: meta.from ?? ((pagination.value.current_page - 1) * pagination.value.per_page + 1),
 to: meta.to ?? (meta.from ?? 0) + users.value.length - 1,
 };

 availableRoles.value = meta.available_roles ?? availableRoles.value;
 } catch (error) {
 console.error('Failed to load users:', error);
 } finally {
 loading.value = false;
 }
};

const changePage = (page: number) => {
 pagination.value.current_page = Math.max(1, page);
 loadUsers();
};

const deleteUser = async (user: UserItem) => {
 const confirmed = await dialog.confirm({
 title: t('Delete User'),
 message: `${t('Are you sure you want to delete account?')} ${user.name}?`,
 confirmText: t('Delete'),
 cancelText: t('Cancel'),
 type:'danger',
 });

 if (!confirmed) {
 return;
 }

 try {
 await axios.delete(`/api/v1/users/${user.id}`);
 await loadUsers();
 dialog.success(t('User deleted successfully'));
 } catch (error: any) {
 const message = error.response?.data?.error?.message || t('Failed to delete user');
 dialog.error(message);
 }
};

const formatDate = (value: string | null) => {
 if (!value) return'-';
 const date = new Date(value);
 if (Number.isNaN(date.getTime())) {
 return value;
 }
 return date.toLocaleString();
};

onMounted(() => {
 loadUsers();
});
</script>
