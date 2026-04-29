<template>
    <AccountLayout>
        <Head :title="t('My Licenses')" />
        <template #header>
            {{ t('My Licenses') }}
        </template>

        <div class="bg-white dark:bg-gray-900 shadow overflow-hidden sm:rounded-lg border border-gray-100 dark:border-gray-800 transition-colors duration-300">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Product') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('License Key') }}
                        </th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Activations') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Status') }}
                        </th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                    <tr v-for="license in licenses" :key="license.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                            {{ license.subscription?.product?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-50 dark:bg-gray-800 rounded p-1">
                            {{ license.license_key }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ license.activation_count }} / {{ license.max_activations }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': license.status === 'active',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': license.status === 'revoked',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': license.status === 'suspended'
                                }">
                                {{ t(license.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                           <!-- Deactivate logic/buttons could go here -->
                        </td>
                    </tr>
                </tbody>
            </table>
            
             <div v-if="licenses.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                {{ t('No licenses found.') }}
            </div>
        </div>
    </AccountLayout>
</template>

<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useTranslation } from '@/admin/composables/useTranslation';

const { t } = useTranslation();

defineProps({
    licenses: Array,
});
</script>
