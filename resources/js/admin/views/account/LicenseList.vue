<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('My Licenses') }}</h1>
        </div>

        <!-- Licenses Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Product') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('License Key') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Activations') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="license in licenses" :key="license.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                            {{ license.subscription?.product?.name || '-' }}
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 rounded p-1">
                            {{ license.license_key }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ license.activation_count }} / {{ license.max_activations }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusClass(license.status)">
                                {{ license.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                {{ t('Manage') }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="licenses.length === 0">
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            {{ t('No licenses found.') }}
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
const licenses = ref<any[]>([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

const getStatusClass = (status: string) => {
    const map: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        revoked: 'bg-red-100 text-red-800',
        suspended: 'bg-yellow-100 text-yellow-800',
    };
    return `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${map[status] || 'bg-gray-100 text-gray-800'}`;
};

const loadLicenses = async () => {
    try {
        const params = { page: pagination.value.current_page, user_id: 'me' };
        const response = await axios.get('/api/v1/licenses', { params });
        licenses.value = response.data.data;
        pagination.value = { ...pagination.value, ...response.data };
    } catch (error) {
        console.error('Error loading licenses', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadLicenses();
};

onMounted(loadLicenses);
</script>
