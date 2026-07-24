<template>
 <div class="space-y-6">
 <div class="flex items-center justify-between">
 <div>
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Roles & Permissions') ||'Roles & Permissions' }}</h1>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ t('Manage user roles and detailed permissions.') }}
 </p>
 </div>
 <router-link
 :to="{ name:'admin.roles.create' }"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 >
 + {{ t('New Role') ||'New Role' }}
 </router-link>
 </div>

 <div class="bg-admin-theme-surface rounded-lg shadow p-4">
 <div class="flex flex-col md:flex-row md:items-center gap-3">
 <input
 v-model="filters.search"
 type="text"
 :placeholder="t('Search roles...') ||'Search roles...'"
 class="flex-1 px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
 @input="debouncedLoad()"
 />
 <div class="inline-flex rounded-lg border border-admin-theme-border overflow-hidden">
 <button
 v-for="option in typeOptions"
 :key="option.value"
 type="button"
 class="px-3 py-2 text-sm font-medium transition-colors"
 :class="[
 filters.type === option.value
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-transparent text-admin-theme-text-secondary hover:bg-admin-theme-base',
 ]"
 @click="setType(option.value)"
 >
 {{ option.label }}
 </button>
 </div>
 </div>
 </div>

 <AdminLoadingState v-if="loading" :message="t('Loading roles...')" />

 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Role') ||'Role' }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Type') ||'Type' }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Permissions') ||'Permissions' }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Users') ||'Users' }}
 </th>
 <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Actions') ||'Actions' }}
 </th>
 </tr>
 </thead>
 <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
 <tr v-for="role in roles" :key="role.id">
  <td class="px-6 py-4 whitespace-nowrap">
    <router-link
      :to="{ name: 'admin.roles.edit', params: { id: role.id } }"
      class="text-sm font-semibold text-admin-theme-text hover:text-admin-theme-primary transition-colors cursor-pointer"
    >
      {{ role.label }}
    </router-link>
    <div class="text-xs text-admin-theme-text-muted">{{ role.name }}</div>
  </td>
  <td class="px-6 py-4 whitespace-nowrap">
    <span
      class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full"
      :class="role.is_system
        ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200'
        : 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-200'"
    >
      {{ role.is_system ? (t('System') || 'System') : (t('Custom') || 'Custom') }}
    </span>
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-secondary">
    {{ role.permissions.length }}
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-secondary">
    {{ role.users_count ?? 0 }}
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
    <button
      class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary-hover dark:hover:text-admin-theme-primary-hover transition-colors"
      @click="cloneRole(role)"
    >
      {{ t('Clone') || 'Clone' }}
    </button>
    <router-link
      :to="{ name: 'admin.roles.edit', params: { id: role.id } }"
      class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary-hover dark:hover:text-admin-theme-primary-hover transition-colors"
    >
      {{ role.is_system ? (t('View') || 'View') : (t('Edit') || 'Edit') }}
    </router-link>
    <button
      v-if="!role.is_system"
      class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors"
      @click="deleteRole(role)"
    >
      {{ t('Delete') || 'Delete' }}
    </button>
  </td>
 </tr>
 <tr v-if="roles.length === 0">
 <td colspan="5" class="px-6 py-4 text-center text-admin-theme-text-muted">
 {{ t('No roles found.') ||'No roles found.' }}
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
import { ref, onMounted, computed, getCurrentInstance } from'vue';
import { useRouter } from'vue-router';
import axios from'axios';
import AdminLoadingState from'../../components/AdminLoadingState.vue';
import { useDialog } from'@/admin/composables/useDialog';
import { useTranslation } from'@/admin/composables/useTranslation';
import RoleCloneModal from './RoleCloneModal.vue';

interface RoleItem {
 id: number | string;
 name: string;
 label: string;
 is_system: boolean;
 permissions: string[];
 users_count?: number;
}

const dialog = useDialog();
const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const roles = ref<RoleItem[]>([]);
const loading = ref(true);
const filters = ref({
 search:'',
 type:'all',
});

const pagination = ref({
 current_page: 1,
 last_page: 1,
 per_page: 20,
 total: 0,
 from: 0,
 to: 0,
});

const typeOptions = computed(() => [
 { value:'all', label: $t('All') ||'All' },
 { value:'system', label: $t('System') ||'System' },
 { value:'custom', label: $t('Custom') ||'Custom' },
]);

let debounceTimeout: number | undefined;

const loadRoles = async () => {
 loading.value = true;
 try {
 const params: Record<string, any> = {
 page: pagination.value.current_page,
 per_page: pagination.value.per_page,
 };

 if (filters.value.search) {
 params.search = filters.value.search;
 }

 if (filters.value.type !=='all') {
 params.type = filters.value.type;
 }

 const response = await axios.get('/api/v1/roles', { params });
 const meta = response.data.meta ?? {};

 roles.value = (response.data.data || []).map((item: any) => ({
 ...item,
 label: item.label ?? item.name,
 permissions: item.permissions ?? [],
 }));

 pagination.value = {
 current_page: meta.current_page ?? 1,
 last_page: meta.last_page ?? 1,
 per_page: meta.per_page ?? pagination.value.per_page,
 total: meta.total ?? roles.value.length,
 from: meta.from ?? ((meta.current_page ?? 1) - 1) * (meta.per_page ?? 20) + 1,
 to: meta.to ?? (((meta.current_page ?? 1) - 1) * (meta.per_page ?? 20) + roles.value.length),
 };
 } catch (error) {
 console.error('Failed to load roles:', error);
 } finally {
 loading.value = false;
 }
};

const debouncedLoad = () => {
 clearTimeout(debounceTimeout);
 debounceTimeout = window.setTimeout(() => {
 pagination.value.current_page = 1;
 loadRoles();
 }, 300);
};

const setType = (type: string) => {
 filters.value.type = type;
 pagination.value.current_page = 1;
 loadRoles();
};

const changePage = (page: number) => {
 pagination.value.current_page = Math.max(1, Math.min(page, pagination.value.last_page));
 loadRoles();
};

const deleteRole = async (role: RoleItem) => {
 const confirmed = await dialog.confirm({
 title: $t('Delete Role') ||'Delete Role',
 message: t('Are you sure you want to delete this role?','Are you sure you want to delete this role?'),
 confirmText: $t('Delete') ||'Delete',
 cancelText: $t('Cancel') ||'Cancel',
 type:'danger',
 });

 if (!confirmed) return;

 try {
 await axios.delete(`/api/v1/roles/${role.id}`);
 dialog.success(t('Role deleted successfully'));
 await loadRoles();
 } catch (error: any) {
 const message = error.response?.data?.error?.message ||'Failed to delete role.';
 dialog.error(message);
 }
};

const cloneRole = (role: RoleItem) => {
  dialog.showModal({
    title: t('Clone Role') || 'Clone Role',
    component: RoleCloneModal,
    props: {
      role: role,
      onSuccess: () => {
        dialog.closeModal();
        loadRoles();
      },
      onCancel: () => {
        dialog.closeModal();
      }
    },
    size: 'sm'
  });
};

onMounted(() => {
 loadRoles();
});
</script>
