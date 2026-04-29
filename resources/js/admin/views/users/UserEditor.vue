<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ isEdit ? (t('Edit User') || 'Edit User') : (t('New User') || 'New User') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ t('Manage login information, roles, and permissions for this account.') }}
                </p>
            </div>
            <router-link
                :to="{ name: 'admin.users.index' }"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                {{ t('Back to list') || 'Back to list' }}
            </router-link>
        </div>

        <form class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6" @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField
                    name="name"
                    :label="t('Name') || 'Name'"
                    :required="true"
                    :error="validationErrors.name"
                >
                    <FormInput
                        v-model="form.name"
                        name="name"
                        type="text"
                        :rules="['required', { type: 'min' as const, value: 2 }]"
                        validate-on="blur"
                    />
                </FormField>
                <FormField
                    name="email"
                    :label="t('Email') || 'Email'"
                    :required="true"
                    :error="validationErrors.email"
                    :hint="isEdit ? t('Email cannot be changed from this page.') : undefined"
                >
                    <FormInput
                        v-model="form.email"
                        name="email"
                        type="email"
                        :disabled="isEdit"
                        :rules="['required', 'email']"
                        validate-on="blur"
                    />
                </FormField>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField
                    name="password"
                    :label="t('Password') || 'Password'"
                    :required="!isEdit"
                    :error="validationErrors.password"
                    :hint="isEdit ? t('Leave blank if you do not want to change the password.') : undefined"
                >
                    <FormInput
                        v-model="form.password"
                        name="password"
                        type="password"
                        :rules="isEdit ? [] : ['required', { type: 'min' as const, value: 8 }]"
                        validate-on="blur"
                        placeholder="********"
                    />
                </FormField>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ t('Roles') || 'Roles' }}
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label
                        v-for="role in availableRoles"
                        :key="role"
                        class="flex items-center space-x-3 px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900"
                    >
                        <input
                            v-model="form.roles"
                            type="checkbox"
                            :value="role"
                            class="text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ role }}</span>
                    </label>
                </div>
                <p v-if="form.roles.length === 0" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ t('If not selected, the system will automatically assign the default customer role.') }}
                </p>
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
                    :disabled="loading"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50"
                >
                    {{ loading ? (t('Saving...') || 'Saving...') : (t('Save User') || 'Save User') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDialog } from '@/admin/composables/useDialog';
import { useValidation } from '@/admin/composables/useValidation';
import FormField from '@/admin/components/forms/FormField.vue';
import FormInput from '@/admin/components/forms/FormInput.vue';
import { t } from '@/admin/composables/useTranslation';

const props = defineProps({
    id: {
        type: String,
        default: null
    }
});

const route = useRoute();
const router = useRouter();
const dialog = useDialog();

const isEdit = computed(() => Boolean(route.params.id));
const loading = ref(false);

// Validation
const validation = useValidation({
    showToast: false,
});

const validationErrors = computed(() => validation.errors.value);

const form = ref({
    name: '',
    email: '',
    password: '',
    roles: [] as string[],
});

const availableRoles = ref<string[]>([]);

const fetchMeta = async () => {
    try {
        const response = await axios.get('/api/v1/users/meta');
        availableRoles.value = response.data.data?.roles ?? [];
    } catch (error) {
        console.error('Failed to load role list:', error);
    }
};

const loadUser = async () => {
    if (!isEdit.value) {
        return;
    }

    try {
        const response = await axios.get(`/api/v1/users/${route.params.id}`);
        const user = response.data.data;
        form.value.name = user.name || '';
        form.value.email = user.email || '';
        form.value.roles = user.roles || [];
    } catch (error) {
        console.error('Failed to load user information:', error);
        dialog.error(t('Failed to load user information'));
    }
};

const handleSubmit = async () => {
    // Validate form
    const validationRules: Record<string, any[]> = {
        name: ['required', { type: 'min' as const, value: 2 }],
        email: ['required', 'email'],
    };

    if (!isEdit.value) {
        validationRules.password = ['required', { type: 'min' as const, value: 8 }];
    }

    const results = await validation.validateForm(form.value, validationRules);
    const hasValidationErrors = results.some(r => !r.valid);

    if (hasValidationErrors) {
        return;
    }

    loading.value = true;
    validation.clearAllErrors();

    try {
        if (isEdit.value) {
            const payload: Record<string, unknown> = {
                name: form.value.name,
                roles: form.value.roles,
            };

            if (form.value.password) {
                payload.password = form.value.password;
            }

            await axios.put(`/api/v1/users/${route.params.id}`, payload);
            dialog.success(t('User updated successfully'));
        } else {
            await axios.post('/api/v1/users', form.value);
            dialog.success(t('User created successfully'));
        }

        router.push({ name: 'admin.users.index' });
    } catch (error: any) {
        const errors = error.response?.data?.error?.details || error.response?.data?.errors;
        if (errors && typeof errors === 'object') {
            // Set errors from API response
            validation.setErrors(errors);
            const messages = Object.values(errors).flat().join('\n');
            dialog.error(messages);
        } else {
            const message = error.response?.data?.error?.message || error.response?.data?.message || t('Failed to save user');
            dialog.error(message);
        }
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await fetchMeta();
    await loadUser();
});
</script>

