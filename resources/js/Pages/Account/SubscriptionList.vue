<template>
    <AccountLayout>
        <Head :title="t('My Subscriptions')" />
        <template #header>
            {{ t('My Subscriptions') }}
        </template>

        <div class="bg-white dark:bg-gray-900 shadow overflow-hidden sm:rounded-lg border border-gray-100 dark:border-gray-800 transition-colors duration-300">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Product') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Price') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Status') }}
                        </th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Starts At') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Expires At') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Auto Renew') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                    <tr v-for="sub in subscriptions" :key="sub.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                            {{ sub.product?.name || sub.service?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ formatCurrency(sub.recurring_price || sub.product?.price || sub.service?.price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': sub.status === 'active',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': sub.status === 'expired' || sub.status === 'cancelled',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': sub.status === 'suspended'
                                }">
                                {{ t(sub.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ sub.starts_at ? new Date(sub.starts_at).toLocaleDateString() : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ sub.expires_at ? new Date(sub.expires_at).toLocaleDateString() : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                             <span v-if="sub.is_auto_renew" class="text-green-600 dark:text-green-400 font-bold">{{ t('Yes') }}</span>
                             <span v-else class="text-gray-400 dark:text-gray-500">{{ t('No') }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            
             <div v-if="subscriptions.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                {{ t('No subscriptions found.') }}
            </div>
        </div>
    </AccountLayout>
</template>

<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useCurrency } from '@/Composables/useCurrency';
import { useTranslation } from '@/admin/composables/useTranslation';

const { formatCurrency } = useCurrency();
const { t } = useTranslation();

defineProps({
    subscriptions: Array,
});
</script>
