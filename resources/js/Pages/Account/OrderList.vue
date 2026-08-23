<template>
    <AccountLayout>
        <Head :title="t('My Orders')" />
        <template #header>
            {{ t('My Orders') }}
        </template>

        <div class="bg-white dark:bg-[#111] shadow-sm overflow-x-auto sm:rounded-xl border border-gray-200 dark:border-zinc-800 transition-colors duration-300">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ t('Order Code') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[200px] max-w-sm">
                            {{ t('Products') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ t('Date') }}
                        </th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ t('Total') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ t('Status') }}
                        </th>
                        <th scope="col" class="relative px-6 py-3 whitespace-nowrap">
                            <span class="sr-only">{{ t('View') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#111] divide-y divide-gray-200 dark:divide-zinc-800">
                    <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                            {{ order.code }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col gap-1 max-w-[250px]">
                                <template v-for="(item, idx) in order.items" :key="item.id">
                                    <div class="truncate">
                                        <a v-if="item.product && item.product.frontend_url" 
                                           :href="item.product.frontend_url" 
                                           class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                            {{ item.name }}
                                        </a>
                                        <span v-else class="text-gray-900 dark:text-gray-100 font-medium">
                                            {{ item.name }}
                                        </span>
                                        <span v-if="item.quantity > 1" class="text-gray-400 text-xs ml-1">
                                            (x{{ item.quantity }})
                                        </span>
                                        <!-- Package / Plan Snapshot Info -->
                                        <div v-if="getItemPlanInfo(item)" class="text-[11px] text-gray-500 dark:text-gray-400 font-normal">
                                            <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 px-1.5 py-0.5 rounded text-[10px] font-medium">
                                                <i class="fas fa-cube text-[8px] text-indigo-500"></i>
                                                {{ getItemPlanInfo(item) }}
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ new Date(order.created_at).toLocaleString([], { year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false }) }}
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

const getItemPlanInfo = (item) => {
    if (!item) return '';
    let planName = item.metadata?.service_label || item.metadata?.service_name || item.service?.name || item.variant_label || '';
    let price = item.price !== undefined && item.price !== null ? item.price : null;
    if (!planName && (price === null || price === undefined)) return '';
    if (planName && price !== null && price !== undefined) {
        return `${planName} - ${formatCurrency(price)}`;
    }
    if (planName) return planName;
    if (price !== null && price !== undefined) return formatCurrency(price);
    return '';
};

defineProps({
    orders: Object,
});
</script>
