<template>
    <div class="transactions-page">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('Transactions') }}</h1>
            <div class="flex items-center gap-4">
                <select
                    v-model="filters.gateway"
                    @change="() => loadTransactions()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                >
                    <option value="">{{ t('All Gateways') }}</option>
                    <option v-for="gateway in paymentGateways" :key="gateway.code" :value="gateway.code">
                        {{ gateway.name }}
                    </option>
                </select>
                <select
                    v-model="filters.status"
                    @change="() => loadTransactions()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                >
                    <option value="">{{ t('All Status') }}</option>
                    <option value="success">{{ t('Success') }}</option>
                    <option value="pending">{{ t('Pending') }}</option>
                    <option value="failed">{{ t('Failed') }}</option>
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
                            {{ t('Transaction ID') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Order') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('User') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Gateway') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Amount') }}
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
                    <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono text-gray-900 dark:text-white">{{ transaction.transaction_ref || '#' + transaction.id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <router-link 
                                v-if="transaction.order"
                                :to="{ name: 'admin.orders.show', params: { id: transaction.order_id } }"
                                class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                            >
                                {{ transaction.order.code }}
                            </router-link>
                            <span v-else class="text-sm text-gray-500 dark:text-gray-400">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div v-if="transaction.user" class="flex items-center">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mr-2">
                                    <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ transaction.user.name?.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-900 dark:text-white">{{ transaction.user.name }}</span>
                            </div>
                            <span v-else class="text-sm text-gray-500">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                :class="getGatewayClass(transaction.gateway)">
                                {{ transaction.gateway }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(transaction.amount) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span 
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                :class="getStatusClass(transaction.status)"
                            >
                                {{ getStatusLabel(transaction.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(transaction.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button 
                                @click="viewDetails(transaction)"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                            >
                                {{ t('View') }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="transactions.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            {{ t('No transactions found') }}
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
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50"
                    >
                        {{ t('Previous') }}
                    </button>
                    <button 
                        @click="loadPage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50"
                    >
                        {{ t('Next') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('Transaction Details') }}</h3>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('Transaction Ref') }}</dt>
                            <dd class="text-sm font-mono text-gray-900 dark:text-white">{{ selectedTransaction?.transaction_ref || '#' + selectedTransaction?.id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('Gateway') }}</dt>
                            <dd class="text-sm text-gray-900 dark:text-white capitalize">{{ selectedTransaction?.gateway }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('Amount') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(selectedTransaction?.amount || 0) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('Status') }}</dt>
                            <dd class="text-sm text-gray-900 dark:text-white capitalize">{{ getStatusLabel(selectedTransaction?.status || '') }}</dd>
                        </div>
                    </dl>
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ t('Raw Payload') }}</h4>
                        <pre class="bg-gray-100 dark:bg-gray-700/50 p-4 rounded text-xs text-gray-800 dark:text-gray-200 overflow-x-auto">{{ selectedTransaction?.payload ? JSON.stringify(selectedTransaction.payload, null, 2) : t('No payload data available') }}</pre>
                    </div>
                </div>
                <!-- Manual Actions for Pending Transactions -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-700/30" v-if="selectedTransaction?.status === 'pending'">
                    <button 
                        @click="updateStatus(selectedTransaction, 'failed')"
                        :disabled="updatingStatus"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-red-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-red-400 dark:hover:bg-gray-700 disabled:opacity-50"
                    >
                        <span v-if="updatingStatus && confirmStatus === 'failed'">{{ t('Updating...') }}</span>
                        <span v-else>{{ t('Mark as Failed') }}</span>
                    </button>
                    <button 
                        @click="updateStatus(selectedTransaction, 'success')"
                        :disabled="updatingStatus"
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 dark:hover:bg-indigo-500 disabled:opacity-50"
                    >
                        <span v-if="updatingStatus && confirmStatus === 'success'">{{ t('Updating...') }}</span>
                        <span v-else>{{ t('Mark as Success') }}</span>
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
import { useCurrency } from '@/Composables/useCurrency';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const { formatCurrency } = useCurrency();
const dialog = useDialog();

const loading = ref(true);
const transactions = ref<any[]>([]);
const paymentGateways = ref<any[]>([]);
const showDetailModal = ref(false);
const selectedTransaction = ref<any>(null);
const updatingStatus = ref(false);
const confirmStatus = ref('');

const filters = reactive({
    gateway: '',
    status: '',
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
});

const loadTransactions = async (page = 1) => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        params.append('page', String(page));
        if (filters.gateway) params.append('gateway', filters.gateway);
        if (filters.status) params.append('status', filters.status);

        const response = await axios.get(`/api/v1/transactions?${params}`);
        transactions.value = response.data.data;
        Object.assign(pagination, response.data.meta || {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
        });
    } catch (error) {
        console.error('Error loading transactions:', error);
    } finally {
        loading.value = false;
    }
};

const fetchPaymentGateways = async () => {
    try {
        const response = await axios.get('/api/v1/payment-gateways');
        paymentGateways.value = response.data.data;
    } catch (error) {
        console.error('Error fetching payment gateways:', error);
    }
};

const loadPage = (page: number) => {
    loadTransactions(page);
};

const viewDetails = (transaction: any) => {
    selectedTransaction.value = transaction;
    showDetailModal.value = true;
};

const updateStatus = async (transaction: any, status: 'success' | 'failed') => {
    const isConfirmed = await dialog.confirm({
        title: t('Confirm Status Update'),
        message: t(`Are you sure you want to mark this transaction as {status}?`, { status }),
        confirmText: t('Yes, update it'),
        type: status === 'failed' ? 'danger' : 'info'
    });

    if (!isConfirmed) return;

    updatingStatus.value = true;
    confirmStatus.value = status;
    try {
        await axios.patch(`/api/v1/transactions/${transaction.id}/status`, { status });
        dialog.success(t('Transaction status updated successfully'));
        showDetailModal.value = false;
        loadTransactions(pagination.current_page);
    } catch (error: any) {
        console.error('Error updating transaction:', error);
        dialog.error(error.response?.data?.message || t('Failed to update transaction status'));
    } finally {
        updatingStatus.value = false;
        confirmStatus.value = '';
    }
};

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        success: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        success: t('Success'),
        pending: t('Pending'),
        failed: t('Failed'),
    };
    return labels[status] || status;
};

const getGatewayClass = (gateway: string) => {
    const classes: Record<string, string> = {
        paypal: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        stripe: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        momo: 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
    };
    return classes[gateway] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString();
};

onMounted(() => {
    loadTransactions();
    fetchPaymentGateways();
});
</script>
