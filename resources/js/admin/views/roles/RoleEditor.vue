<template>
 <div class="space-y-6">
 <div class="flex items-center justify-between">
 <div>
 <h1 class="text-2xl font-bold text-admin-theme-text">
 {{ isEdit ? (t('Edit Role') ||'Edit Role') : (t('New Role') ||'New Role') }}
 </h1>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ t('Establish user roles and detailed permissions.') }}
 </p>
 </div>
 <router-link
 :to="{ name:'admin.roles.index' }"
 class="px-4 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-surface text-admin-theme-text-secondary hover:bg-admin-theme-base transition-colors"
 >
 {{ t('Back to list') ||'Back to list' }}
 </router-link>
 </div>

 <div v-if="role && role.is_system" class="bg-amber-100 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg">
 {{ t('This is a system role, allowing only viewing and cloning, cannot be edited directly.') }}
 </div>

 <form class="bg-admin-theme-surface rounded-lg shadow p-6 space-y-6" @submit.prevent="saveRole">
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <FormField
 name="name"
 :label="t('Role Name') ||'Role Name'"
 :required="true"
 :error="validationErrors.name"
 >
 <FormInput
 v-model="form.name"
 name="name"
 type="text"
 :disabled="role?.is_system"
 :rules="['required', { type:'min' as const, value: 2 }]"
 validate-on="blur"
 />
 </FormField>
 <FormField
 name="label"
 :label="t('Display Label') ||'Display Label'"
 :error="validationErrors.label"
 >
 <FormInput
 v-model="form.label"
 name="label"
 type="text"
 :disabled="role?.is_system"
 :placeholder="t('Name displayed in UI')"
 />
 </FormField>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <FormField
 name="module_owner"
 :label="t('Module / Owner') ||'Module / Owner'"
 :error="validationErrors.module_owner"
 >
 <FormInput
 v-model="form.module_owner"
 name="module_owner"
 type="text"
 :disabled="role?.is_system"
 :placeholder="t('e.g. core, shop-module')"
 />
 </FormField>
 <FormField
 name="description"
 :label="t('Description') ||'Description'"
 :error="validationErrors.description"
 >
 <FormTextarea
 v-model="form.description"
 name="description"
 :rows="3"
 :disabled="role?.is_system"
 :placeholder="t('Notes about this role')"
 />
 </FormField>
 </div>

 <div>
 <div class="flex items-center justify-between mb-3">
 <label class="text-sm font-medium text-admin-theme-text-secondary">
 {{ t('Permissions') ||'Permissions' }}
 </label>
 <div v-if="!role?.is_system" class="space-x-2">
 <button type="button" class="text-xs text-admin-theme-primary dark:text-admin-theme-primary hover:underline" @click="toggleAll(true)">
 {{ t('Select all') ||'Select all' }}
 </button>
 <button type="button" class="text-xs text-admin-theme-primary dark:text-admin-theme-primary hover:underline" @click="toggleAll(false)">
 {{ t('Clear') ||'Clear' }}
 </button>
 </div>
 </div>
 <div class="space-y-4">
 <div
 v-for="group in groupedPermissions"
 :key="group.key"
 class="border border-admin-theme-border rounded-lg"
 >
 <button
 type="button"
 class="w-full flex items-center justify-between px-4 py-3 bg-admin-theme-base text-left"
 @click="group.collapsed = !group.collapsed"
 >
 <div>
 <div class="text-sm font-semibold text-admin-theme-text">
 {{ group.label }}
 </div>
 <div class="text-xs text-admin-theme-text-muted">
 {{ group.permissions.length }} {{ t('permissions') ||'permissions' }}
 </div>
 </div>
 <svg
 class="h-4 w-4 text-gray-500 transition-transform duration-200"
 :class="{'rotate-180': !group.collapsed }"
 fill="none"
 stroke="currentColor"
 viewBox="0 0 24 24"
 xmlns="http://www.w3.org/2000/svg"
 >
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
 </svg>
 </button>
  <div v-show="!group.collapsed" class="px-6 py-5 bg-admin-theme-surface border-t border-admin-theme-border space-y-5">
    <div v-for="resource in group.resources" :key="resource.name" class="border border-admin-theme-border/50 rounded-xl p-4 bg-admin-theme-base/10 dark:bg-admin-theme-base/5">
      <h4 class="text-sm font-bold text-admin-theme-text border-b border-admin-theme-border/50 pb-2 mb-3 flex items-center justify-between">
        <span>{{ resource.name }}</span>
        <span class="text-xs text-admin-theme-text-muted font-normal">{{ resource.permissions.length }} {{ t('permissions') }}</span>
      </h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <label
          v-for="permission in resource.permissions"
          :key="permission.name"
          class="flex items-center gap-3 text-sm text-admin-theme-text-secondary hover:text-admin-theme-text cursor-pointer select-none"
        >
          <input
            v-model="form.permissions"
            :value="permission.name"
            type="checkbox"
            class="text-admin-theme-primary focus:ring-admin-theme-primary border-gray-300 rounded"
            :disabled="role?.is_system"
          />
          <span>{{ permission.label }}</span>
        </label>
      </div>
    </div>
    <div v-if="group.resources.length === 0" class="text-sm text-admin-theme-text-muted italic">
      {{ t('This group has no permissions yet.') }}
    </div>
  </div>
 </div>
 </div>
 </div>

  <div class="flex justify-end space-x-3">
    <button
      type="button"
      class="px-4 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-surface text-admin-theme-text-secondary hover:bg-admin-theme-base transition-colors"
      @click="router.back()"
    >
      {{ t('Cancel') || 'Cancel' }}
    </button>
    <button
      v-if="role?.is_system"
      type="button"
      class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center"
      @click="cloneRole"
    >
      {{ t('Clone to edit') || 'Clone to edit' }}
    </button>
    <button
      v-else
      type="submit"
      :disabled="loading"
      class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors disabled:opacity-50"
    >
      {{ loading ? (t('Saving...') || 'Saving...') : (t('Save Role') || 'Save Role') }}
    </button>
  </div>
 </form>
 </div>
</template>

<script setup lang="ts">
import { computed, getCurrentInstance, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import RoleCloneModal from './RoleCloneModal.vue';
import { useValidation } from'../../composables/useValidation';
import FormField from'../../components/forms/FormField.vue';
import FormInput from'../../components/forms/FormInput.vue';
import FormTextarea from'../../components/forms/FormTextarea.vue';

interface PermissionDefinition {
 name: string;
 label: string;
 group: string;
 guard_name: string;
 module_owner?: string | null;
}

interface RolePayload {
 id?: number | string;
 name: string;
 label: string;
 is_system: boolean;
 module_owner?: string | null;
 metadata?: Record<string, unknown>;
 permissions: string[];
}

const router = useRouter();
const route = useRoute();
const dialog = useDialog();
const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

// Validation
const validation = useValidation({
 showToast: false,
});

const validationErrors = computed(() => validation.errors.value);

const isEdit = computed(() => Boolean(route.params.id));
const loading = ref(false);
const role = ref<RolePayload | null>(null);
const permissions = ref<PermissionDefinition[]>([]);

const form = reactive({
 name:'',
 label:'',
 module_owner:'',
 description:'',
 permissions: [] as string[],
});

const getResourceName = (name: string) => {
  const parts = name.trim().split(/\s+/);
  if (parts.length <= 1) return 'General';
  
  const action = parts[0];
  const actions = ['create', 'edit', 'delete', 'view', 'manage', 'publish', 'activate', 'access', 'adjust', 'apply', 'update', 'read', 'write'];
  if (actions.includes(action.toLowerCase())) {
    const rawResource = parts.slice(1).join(' ');
    return rawResource.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
  }
  
  return 'General';
};

const groupedPermissions = computed(() => {
  const groups = new Map<string, { 
    key: string; 
    label: string; 
    permissions: PermissionDefinition[]; 
    resources: { name: string; permissions: PermissionDefinition[] }[];
    collapsed: boolean 
  }>();

  permissions.value.forEach((permission) => {
    const groupKey = permission.group ||'core';
    if (!groups.has(groupKey)) {
      groups.set(groupKey, {
        key: groupKey,
        label: formatGroupLabel(groupKey),
        permissions: [],
        resources: [],
        collapsed: false,
      });
    }
    
    const group = groups.get(groupKey)!;
    group.permissions.push(permission);

    const resourceName = getResourceName(permission.name);
    let resource = group.resources.find(r => r.name === resourceName);
    if (!resource) {
      resource = { name: resourceName, permissions: [] };
      group.resources.push(resource);
    }
    resource.permissions.push(permission);
  });

  // Sort resources by name, but put "General" last
  groups.forEach((group) => {
    group.resources.sort((a, b) => {
      if (a.name === 'General') return 1;
      if (b.name === 'General') return -1;
      return a.name.localeCompare(b.name);
    });
  });

  return Array.from(groups.values());
});

const formatGroupLabel = (group: string) => {
 const label = group.replace(/[._-]+/g,'');
 return label.charAt(0).toUpperCase() + label.slice(1);
};

const fetchMeta = async () => {
 try {
 const response = await axios.get('/api/v1/roles/meta');
 const data = response.data.data ?? {};
 permissions.value = (data.permissions || []).map((item: any) => ({
 name: item.name,
 label: item.label || item.name,
 group: item.group ||'core',
 guard_name: item.guard_name ||'web',
 module_owner: item.module_owner ?? null,
 }));
 } catch (error) {
 console.error('Failed to load role metadata:', error);
 permissions.value = [];
 }
};

const loadRole = async () => {
 if (!isEdit.value) {
 role.value = null;
 form.name ='';
 form.label ='';
 form.module_owner ='';
 form.description ='';
 form.permissions = [];
 return;
 }

 try {
 const response = await axios.get(`/api/v1/roles/${route.params.id}`);
 const data = response.data.data;
 role.value = {
 id: data.id,
 name: data.name,
 label: data.label ?? data.name,
 is_system: data.is_system,
 module_owner: data.module_owner,
 metadata: data.metadata ?? {},
 permissions: data.permissions || [],
 };

 form.name = data.name;
 form.label = data.metadata?.label ?? data.name;
 form.module_owner = data.module_owner ??'';
 form.description = data.metadata?.description ??'';
 form.permissions = [...(data.permissions || [])];
 } catch (error) {
 console.error('Failed to load role', error);
 dialog.error(t('Role not found or access denied'));
 router.push({ name:'admin.roles.index' });
 }
};

const saveRole = async () => {
 if (role.value?.is_system) {
 dialog.warning(t('System roles cannot be edited'));
 return;
 }

 loading.value = true;

 const payload = {
 name: form.name,
 module_owner: form.module_owner || null,
 metadata: {
 label: form.label || form.name,
 description: form.description || null,
 },
 permissions: form.permissions,
 };

 try {
 if (isEdit.value) {
 await axios.put(`/api/v1/roles/${route.params.id}`, payload);
 dialog.success(t('Role updated successfully'));
 } else {
 await axios.post('/api/v1/roles', payload);
 dialog.success(t('Role created successfully'));
 }
 router.push({ name:'admin.roles.index' });
 } catch (error: any) {
 const details = error.response?.data?.error?.details;
 if (details && typeof details ==='object') {
 const message = Object.values(details).flat().join('\n');
 dialog.error(message);
 } else {
 const message = error.response?.data?.error?.message ||'Failed to save role.';
 dialog.error(message);
 }
 } finally {
 loading.value = false;
 }
};

const toggleAll = (checked: boolean) => {
  if (checked) {
    form.permissions = permissions.value.map((p) => p.name);
  } else {
    form.permissions = [];
  }
};

const cloneRole = () => {
  if (!role.value) return;
  dialog.showModal({
    title: t('Clone Role') || 'Clone Role',
    component: RoleCloneModal,
    props: {
      role: {
        id: Number(role.value.id),
        name: role.value.name,
        label: role.value.label
      },
      onSuccess: (clonedRole: any) => {
        dialog.closeModal();
        if (clonedRole && clonedRole.id) {
          router.push({ name: 'admin.roles.edit', params: { id: clonedRole.id } });
        } else {
          router.push({ name: 'admin.roles.index' });
        }
      },
      onCancel: () => {
        dialog.closeModal();
      }
    },
    size: 'sm'
  });
};

watch(() => route.params.id, () => {
  loadRole();
});

onMounted(async () => {
  await fetchMeta();
  await loadRole();
});
</script>
