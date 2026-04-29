<template>
    <AccountLayout>
        <Head :title="t('My Orders')" />
        <template #header>
            {{ t('My Orders') }}
        </template>

        <div class="bg-white dark:bg-gray-900 shadow overflow-hidden sm:rounded-lg border border-gray-100 dark:border-gray-800 transition-colors duration-300">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Order Code') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Date') }}
                        </th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Total') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Status') }}
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">{{ t('View') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                    <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                            {{ order.code }}
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ new Date(order.created_at).toLocaleDateString() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ formatCurrency(order.total_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': order.status === 'completed',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': order.status === 'pending',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': order.status === 'cancelled',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': order.status === 'processing'
                                }">
                                {{ t(order.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="route('account.orders.show', order.code)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">{{ t('View') }}</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
            
             <div v-if="orders.data.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                {{ t('No orders found.') }}
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
    orders: Object,
});
</script>
