<template>
    <div class="invoices-page">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('Invoices') }}</h1>
            <div class="flex items-center gap-4">
                <input 
                    type="text" 
                    v-model="filters.search" 
                    @keyup.enter="() => loadInvoices()"
                    :placeholder="t('Search Invoice No...')"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                />
                <select
                    v-model="filters.status"
                    @change="() => loadInvoices()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                >
                    <option value="">{{ t('All Status') }}</option>
                    <option value="valid">{{ t('Valid') }}</option>
                    <option value="void">{{ t('Void') }}</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ t('Invoice No') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Order') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Customer') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Total') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Date') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono font-medium text-gray-900 dark:text-white">{{ invoice.invoice_number }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <router-link 
                                v-if="invoice.order"
                                :to="{ name: 'admin.orders.show', params: { id: invoice.order_id } }"
                                class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                            >
                                {{ invoice.order.code }}
                            </router-link>
                            <span v-else class="text-sm text-gray-500 dark:text-gray-400">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div v-if="invoice.order && invoice.order.user" class="flex items-center">
                                <span class="text-sm text-gray-900 dark:text-white">{{ invoice.order.user.name }}</span>
                            </div>
                            <span v-else-if="invoice.order && invoice.order.guest_email" class="text-sm text-gray-500">{{ invoice.order.guest_email }}</span>
                            <span v-else class="text-sm text-gray-500">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatTotal(invoice) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span 
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                :class="invoice.status === 'valid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            >
                                {{ invoice.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(invoice.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a 
                                :href="downloadUrl(invoice.id)"
                                target="_blank"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3"
                            >
                                {{ t('Download PDF') }}
                            </a>
                            <button 
                                v-if="invoice.status === 'valid'"
                                @click="voidInvoice(invoice.id)"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                            >
                                {{ t('Void') }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="invoices.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            {{ t('No invoices found') }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t('Showing') }} {{ pagination.from }} {{ t('to') }} {{ pagination.to }} {{ t('of') }} {{ pagination.total }}
                </p>
                <div class="flex gap-2">
                    <button 
                        @click="loadPage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        {{ t('Previous') }}
                    </button>
                    <button 
                        @click="loadPage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        {{ t('Next') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';

const { t } = useTranslation();

const invoices = ref<any[]>([]);
const loading = ref(true);

const filters = reactive({
    status: '',
    search: '',
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0,
});

onMounted(() => {
    loadInvoices();
});

const loadInvoices = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/invoices', {
            params: {
                page: pagination.current_page,
                status: filters.status,
                search: filters.search,
            }
        });
        invoices.value = response.data.data;
        Object.assign(pagination, {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to,
        });
    } catch (error) {
        console.error('Failed to load invoices:', error);
    } finally {
        loading.value = false;
    }
};

const loadPage = (page: number) => {
    pagination.current_page = page;
    loadInvoices();
};

const downloadUrl = (invoiceId: number) => {
    return `/api/v1/invoices/${invoiceId}/download`;
};

const voidInvoice = async (invoiceId: number) => {
    if (!confirm(t('Are you sure you want to void this invoice? This cannot be undone.'))) {
        return;
    }
    
    try {
        await axios.patch(`/api/v1/invoices/${invoiceId}/void`);
        // Refresh list
        loadInvoices();
    } catch (error) {
        console.error('Failed to void invoice:', error);
        alert(t('Failed to void invoice.'));
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatTotal = (invoice: any) => {
    try {
        const snapshot = typeof invoice.billing_snapshot === 'string' 
            ? JSON.parse(invoice.billing_snapshot) 
            : invoice.billing_snapshot;
        
        if (snapshot && snapshot.totals) {
             const format = snapshot.currency_format || {};
             const amount = parseFloat(snapshot.totals.total_amount).toFixed(format.decimals || 2);
             return `${format.symbol || '$'} ${amount}`;
        }
    } catch (e) {
        // fail silently
    }
    return '-';
};
</script>
