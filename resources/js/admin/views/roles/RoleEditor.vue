<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ isEdit ? (t('Edit Role') || 'Edit Role') : (t('New Role') || 'New Role') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ t('Establish user roles and detailed permissions.') }}
                </p>
            </div>
            <router-link
                :to="{ name: 'admin.roles.index' }"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                {{ t('Back to list') || 'Back to list' }}
            </router-link>
        </div>

        <div v-if="role && role.is_system" class="bg-amber-100 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg">
            {{ t('This is a system role, allowing only viewing and cloning, cannot be edited directly.') }}
        </div>

        <form class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6" @submit.prevent="saveRole">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField
                    name="name"
                    :label="t('Role Name') || 'Role Name'"
                    :required="true"
                    :error="validationErrors.name"
                >
                    <FormInput
                        v-model="form.name"
                        name="name"
                        type="text"
                        :disabled="role?.is_system"
                        :rules="['required', { type: 'min' as const, value: 2 }]"
                        validate-on="blur"
                    />
                </FormField>
                <FormField
                    name="label"
                    :label="t('Display Label') || 'Display Label'"
                    :error="validationErrors.label"
                >
                    <FormInput
                        v-model="form.label"
                        name="label"
                        type="text"
                        :placeholder="t('Name displayed in UI')"
                    />
                </FormField>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField
                    name="module_owner"
                    :label="t('Module / Owner') || 'Module / Owner'"
                    :error="validationErrors.module_owner"
                >
                    <FormInput
                        v-model="form.module_owner"
                        name="module_owner"
                        type="text"
                        :placeholder="t('e.g. core, shop-module')"
                    />
                </FormField>
                <FormField
                    name="description"
                    :label="t('Description') || 'Description'"
                    :error="validationErrors.description"
                >
                    <FormTextarea
                        v-model="form.description"
                        name="description"
                        :rows="3"
                        :placeholder="t('Notes about this role')"
                    />
                </FormField>
            </div>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('Permissions') || 'Permissions' }}
                    </label>
                    <div class="space-x-2">
                        <button type="button" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline" @click="toggleAll(true)">
                            {{ t('Select all') || 'Select all' }}
                        </button>
                        <button type="button" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline" @click="toggleAll(false)">
                            {{ t('Clear') || 'Clear' }}
                        </button>
                    </div>
                </div>
                <div class="space-y-4">
                    <div
                        v-for="group in groupedPermissions"
                        :key="group.key"
                        class="border border-gray-200 dark:border-gray-700 rounded-lg"
                    >
                        <button
                            type="button"
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800 text-left"
                            @click="group.collapsed = !group.collapsed"
                        >
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ group.label }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ group.permissions.length }} {{ t('permissions') || 'permissions' }}
                                </div>
                            </div>
                            <svg
                                class="h-4 w-4 text-gray-500 transition-transform duration-200"
                                :class="{ 'rotate-180': !group.collapsed }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div v-show="!group.collapsed" class="px-4 py-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 space-y-2">
                            <label
                                v-for="permission in group.permissions"
                                :key="permission.name"
                                class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-200"
                            >
                                <input
                                    v-model="form.permissions"
                                    :value="permission.name"
                                    type="checkbox"
                                    class="text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    :disabled="role?.is_system"
                                />
                                <span>{{ permission.label }}</span>
                            </label>
                            <div v-if="group.permissions.length === 0" class="text-sm text-gray-400">
                                {{ t('This group has no permissions yet.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                    @click="router.back()"
                >
                    {{ t('Cancel') || 'Cancel' }}
                </button>
                <button
                    type="submit"
                    :disabled="loading || role?.is_system"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50"
                >
                    {{ loading ? (t('Saving...') || 'Saving...') : (t('Save Role') || 'Save Role') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { computed, getCurrentInstance, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import { useValidation } from '../../composables/useValidation';
import FormField from '../../components/forms/FormField.vue';
import FormInput from '../../components/forms/FormInput.vue';
import FormTextarea from '../../components/forms/FormTextarea.vue';

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
    name: '',
    label: '',
    module_owner: '',
    description: '',
    permissions: [] as string[],
});

const groupedPermissions = computed(() => {
    const groups = new Map<string, { key: string; label: string; permissions: PermissionDefinition[]; collapsed: boolean }>();

    permissions.value.forEach((permission) => {
        const groupKey = permission.group || 'core';
        if (!groups.has(groupKey)) {
            groups.set(groupKey, {
                key: groupKey,
                label: formatGroupLabel(groupKey),
                permissions: [],
                collapsed: false,
            });
        }
        groups.get(groupKey)!.permissions.push(permission);
    });

    return Array.from(groups.values());
});

const formatGroupLabel = (group: string) => {
    const label = group.replace(/[._-]+/g, ' ');
    return label.charAt(0).toUpperCase() + label.slice(1);
};

const fetchMeta = async () => {
    try {
        const response = await axios.get('/api/v1/roles/meta');
        const data = response.data.data ?? {};
        permissions.value = (data.permissions || []).map((item: any) => ({
            name: item.name,
            label: item.label || item.name,
            group: item.group || 'core',
            guard_name: item.guard_name || 'web',
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
        form.name = '';
        form.label = '';
        form.module_owner = '';
        form.description = '';
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
        form.module_owner = data.module_owner ?? '';
        form.description = data.metadata?.description ?? '';
        form.permissions = [...(data.permissions || [])];
    } catch (error) {
        console.error('Failed to load role', error);
        dialog.error(t('Role not found or access denied'));
        router.push({ name: 'admin.roles.index' });
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
        router.push({ name: 'admin.roles.index' });
    } catch (error: any) {
        const details = error.response?.data?.error?.details;
        if (details && typeof details === 'object') {
            const message = Object.values(details).flat().join('\n');
            dialog.error(message);
        } else {
            const message = error.response?.data?.error?.message || 'Failed to save role.';
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

onMounted(async () => {
    await fetchMeta();
    await loadRole();
});
</script>

