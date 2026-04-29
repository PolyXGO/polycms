<script setup lang="ts">
import AccountLayout from '@/Layouts/AccountLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { useTranslation } from '@/admin/composables/useTranslation';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const { t } = useTranslation();
</script>

<template>
    <Head :title="t('Profile')" />

    <AccountLayout>
        <template #header>
            {{ t('Profile') }}
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-900 p-4 shadow-sm sm:rounded-lg sm:p-8 border border-gray-100 dark:border-gray-800 transition-colors">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="w-full"
                />
            </div>

            <div class="bg-white dark:bg-gray-900 p-4 shadow-sm sm:rounded-lg sm:p-8 border border-gray-100 dark:border-gray-800 transition-colors">
                <UpdatePasswordForm class="w-full" />
            </div>

            <div class="bg-white dark:bg-gray-900 p-4 shadow-sm sm:rounded-lg sm:p-8 border border-gray-100 dark:border-gray-800 transition-colors md:col-span-2">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AccountLayout>
</template>
