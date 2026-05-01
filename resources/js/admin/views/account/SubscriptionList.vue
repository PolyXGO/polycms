<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('My Subscriptions') }}</h1>
        </div>

        <!-- Subscriptions Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Product/Service') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Price') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Starts At') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Expires At') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Auto Renew') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="sub in subscriptions" :key="sub.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                            {{ sub.product?.name || sub.service?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ formatCurrency(sub.recurring_price || sub.product?.price || sub.service?.price, sub.currency) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusClass(sub.status)">
                                {{ sub.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(sub.starts_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(sub.expires_at) }}
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span v-if="sub.is_auto_renew" class="text-green-600 font-bold">Yes</span>
                            <span v-else class="text-gray-400">No</span>
                        </td>
                    </tr>
                    <tr v-if="subscriptions.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            {{ t('No subscriptions found.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="mt-6 flex justify-end gap-2">
             <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 border rounded disabled:opacity-50">{{ t('Prev') }}</button>
             <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 border rounded disabled:opacity-50">{{ t('Next') }}</button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useCurrency } from '@/Composables/useCurrency';
import { useTranslation } from '@/admin/composables/useTranslation';

const { t } = useTranslation();
const { formatCurrency } = useCurrency();
const subscriptions = ref<any[]>([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

const formatDate = (date: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

const getStatusClass = (status: string) => {
    const map: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        expired: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
        suspended: 'bg-yellow-100 text-yellow-800',
    };
    return `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${map[status] || 'bg-gray-100 text-gray-800'}`;
};

const loadSubscriptions = async () => {
    try {
        const params = { page: pagination.value.current_page, user_id: 'me' };
        const response = await axios.get('/api/v1/subscriptions', { params });
        subscriptions.value = response.data.data;
        pagination.value = { ...pagination.value, ...response.data };
    } catch (error) {
        console.error('Error loading subscriptions', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadSubscriptions();
};

onMounted(loadSubscriptions);
</script>
