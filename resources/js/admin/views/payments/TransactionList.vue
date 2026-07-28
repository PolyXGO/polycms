<template>
 <div class="transactions-page">
 <div class="mb-6 flex items-center justify-between">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Transactions') }}</h1>
 <div class="flex items-center gap-4">
 <select
 v-model="filters.gateway"
 @change="() => loadTransactions()"
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 >
 <option value="">{{ t('All Gateways') }}</option>
 <option v-for="gateway in paymentGateways" :key="gateway.code" :value="gateway.code">
 {{ gateway.name }}
 </option>
 </select>
 <select
 v-model="filters.status"
 @change="() => loadTransactions()"
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
 >
 <option value="">{{ t('All Status') }}</option>
 <option value="success">{{ t('Success') }}</option>
 <option value="pending">{{ t('Pending') }}</option>
 <option value="failed">{{ t('Failed') }}</option>
 </select>
 </div>
 </div>

 <div class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <div v-if="loading" class="text-center py-12">
 <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
 </div>

 <table v-else class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
 {{ t('Transaction ID') }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider">
 {{ t('Order') }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider">
 {{ t('User') }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider">
 {{ t('Gateway') }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider">
 {{ t('Amount') }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider">
 {{ t('Status') }}
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider">
 {{ t('Date') }}
 </th>
 <th class="px-6 py-3 text-right text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider">
 {{ t('Actions') }}
 </th>
 </tr>
 </thead>
 <tbody class="divide-y divide-admin-theme-border">
 <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-admin-theme-base/50">
 <td class="px-6 py-4 whitespace-nowrap">
 <span class="text-sm font-mono text-admin-theme-text">{{ transaction.transaction_ref ||'#' + transaction.id }}</span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <router-link 
 v-if="transaction.order"
 :to="{ name:'admin.orders.show', params: { id: transaction.order_id } }"
 class="text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary"
 >
 {{ transaction.order.code }}
 </router-link>
 <span v-else class="text-sm text-admin-theme-text-muted">-</span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <div v-if="transaction.user" class="flex items-center">
 <div class="w-8 h-8 bg-admin-theme-primary/15 rounded-full flex items-center justify-center mr-2">
 <span class="text-xs font-medium text-admin-theme-primary dark:text-admin-theme-primary">
 {{ transaction.user.name?.charAt(0).toUpperCase() }}
 </span>
 </div>
 <span class="text-sm text-admin-theme-text">{{ transaction.user.name }}</span>
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
 <span class="text-sm font-medium text-admin-theme-text">
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
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ formatDate(transaction.created_at) }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <button 
 @click="viewDetails(transaction)"
 class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary"
 >
 {{ t('View') }}
 </button>
 </td>
 </tr>
 <tr v-if="transactions.length === 0">
 <td colspan="8" class="px-6 py-12 text-center text-admin-theme-text-muted">
 {{ t('No transactions found') }}
 </td>
 </tr>
 </tbody>
 </table>

 <!-- Pagination -->
 <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-admin-theme-border flex justify-between items-center">
 <p class="text-sm text-admin-theme-text-muted">
 {{ t('Showing') }} {{ pagination.from }} {{ t('to') }} {{ pagination.to }} {{ t('of') }} {{ pagination.total }}
 </p>
 <div class="flex gap-2">
 <button 
 @click="loadPage(pagination.current_page - 1)"
 :disabled="pagination.current_page === 1"
 class="px-3 py-1 border border-admin-theme-border rounded text-sm disabled:opacity-50"
 >
 {{ t('Previous') }}
 </button>
 <button 
 @click="loadPage(pagination.current_page + 1)"
 :disabled="pagination.current_page === pagination.last_page"
 class="px-3 py-1 border border-admin-theme-border rounded text-sm disabled:opacity-50"
 >
 {{ t('Next') }}
 </button>
 </div>
 </div>
 </div>

 <!-- Detail Modal -->
 <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
 <div class="bg-admin-theme-surface rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
 <div class="px-6 py-4 border-b border-admin-theme-border flex justify-between items-center">
 <h3 class="text-lg font-medium text-admin-theme-text">{{ t('Transaction Details') }}</h3>
 <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-500">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
 </svg>
 </button>
 </div>
 <div class="p-6">
 <dl class="grid grid-cols-2 gap-4">
 <div>
 <dt class="text-sm font-medium text-admin-theme-text-muted">{{ t('Transaction Ref') }}</dt>
 <dd class="text-sm font-mono text-admin-theme-text">{{ selectedTransaction?.transaction_ref ||'#' + selectedTransaction?.id }}</dd>
 </div>
 <div>
 <dt class="text-sm font-medium text-admin-theme-text-muted">{{ t('Gateway') }}</dt>
 <dd class="text-sm text-admin-theme-text capitalize">{{ selectedTransaction?.gateway }}</dd>
 </div>
 <div>
 <dt class="text-sm font-medium text-admin-theme-text-muted">{{ t('Amount') }}</dt>
 <dd class="text-sm font-medium text-admin-theme-text">{{ formatCurrency(selectedTransaction?.amount || 0) }}</dd>
 </div>
 <div>
 <dt class="text-sm font-medium text-admin-theme-text-muted">{{ t('Status') }}</dt>
 <dd class="text-sm text-admin-theme-text capitalize">{{ getStatusLabel(selectedTransaction?.status ||'') }}</dd>
 </div>
 </dl>
 <div class="mt-6 border-t border-admin-theme-border pt-6" v-if="selectedTransaction?.payload">
 <h4 class="text-sm font-medium text-admin-theme-text-muted mb-4 uppercase tracking-wider">{{ t('Verification Details') }}</h4>
 <div class="bg-admin-theme-base/50 p-5 rounded-lg border border-admin-theme-border text-sm text-admin-theme-text space-y-5">
 <div v-if="selectedTransaction.payload.admin_note">
 <span class="font-medium text-admin-theme-text-muted block mb-1">{{ t('Admin Note') }}:</span>
 <p class="text-admin-theme-text whitespace-pre-wrap bg-admin-theme-surface p-3 rounded border border-admin-theme-border shadow-sm">{{ selectedTransaction.payload.admin_note }}</p>
 </div>
 <div v-if="selectedTransaction.payload.proof_of_payment">
 <span class="font-medium text-admin-theme-text-muted block mb-2">{{ t('Proof of Payment') }}:</span>
 <div v-if="!proofImageUrl" class="w-48 h-32 flex items-center justify-center bg-admin-theme-surface rounded border border-admin-theme-border shadow-sm">
 <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-admin-theme-primary"></div>
 </div>
 <div v-else class="w-48 h-32 overflow-hidden rounded-lg border border-admin-theme-border cursor-pointer relative group shadow-sm hover:shadow-md transition-all duration-300" @click="showProofModal = true">
 <img :src="proofImageUrl" class="w-full h-full object-cover filter blur-sm group-hover:blur-none transition duration-300" />
 <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-0 group-hover:opacity-100 transition duration-300 bg-black bg-opacity-20">
 <span class="bg-black bg-opacity-70 text-white px-3 py-1.5 rounded-full text-xs font-medium flex items-center gap-1 shadow-lg backdrop-blur-sm">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
 {{ t('View Details') }}
 </span>
 </div>
 </div>
 </div>
 <div v-if="!selectedTransaction.payload.admin_note && !selectedTransaction.payload.proof_of_payment">
 <pre class="text-xs text-left text-admin-theme-text overflow-x-auto p-3 bg-admin-theme-surface rounded border border-admin-theme-border shadow-sm">{{ JSON.stringify(selectedTransaction.payload, null, 2) }}</pre>
 </div>
 </div>
 </div>

 <!-- Manual Verification Form -->
 <div v-if="selectedTransaction?.status ==='pending'" class="mt-6 border-t border-admin-theme-border pt-4">
 <h4 class="text-sm font-medium text-admin-theme-text mb-4">{{ t('Manual Verification Details') }}</h4>
 <div class="space-y-4">
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-muted mb-1">{{ t('Transaction Ref') }}</label>
 <input type="text" v-model="verifyForm.transaction_ref" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm" placeholder="Bank ref, receipt number...">
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-muted mb-1">{{ t('Proof of Payment') }}</label>
 <input type="file" @change="onFileChange" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm" accept="image/*,.pdf">
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-muted mb-1">{{ t('Admin Note') }}</label>
 <textarea v-model="verifyForm.admin_note" rows="2" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"></textarea>
 </div>
 </div>
 </div>
 </div>
 <!-- Manual Actions for Pending Transactions -->
 <div class="px-6 py-4 border-t border-admin-theme-border flex justify-end gap-3 bg-admin-theme-base" v-if="selectedTransaction?.status ==='pending'">
 <button 
 @click="updateStatus(selectedTransaction,'failed')"
 :disabled="updatingStatus"
 class="px-4 py-2 bg-white border border-admin-theme-border rounded-md shadow-sm text-sm font-medium text-red-700 hover:bg-admin-theme-base dark:text-red-400 disabled:opacity-50"
 >
 <span v-if="updatingStatus && confirmStatus ==='failed'">{{ t('Updating...') }}</span>
 <span v-else>{{ t('Mark as Failed') }}</span>
 </button>
 <button 
 @click="updateStatus(selectedTransaction,'success')"
 :disabled="updatingStatus"
 class="px-4 py-2 bg-admin-theme-primary border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-admin-theme-primary-hover dark:hover:bg-admin-theme-primary/100 disabled:opacity-50"
 >
 <span v-if="updatingStatus && confirmStatus ==='success'">{{ t('Updating...') }}</span>
 <span v-else>{{ t('Mark as Success') }}</span>
 </button>
 </div>
 </div>
 </div>

 <!-- Proof of Payment Modal -->
 <div v-if="showProofModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-80 p-4" @click.self="showProofModal = false">
 <div class="relative max-w-4xl max-h-screen">
 <button @click="showProofModal = false" class="absolute -top-10 right-0 text-white hover:text-gray-300 focus:outline-none">
 <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
 </svg>
 </button>
 <img :src="proofImageUrl" class="max-w-full max-h-[85vh] object-contain rounded shadow-2xl" />
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from'vue';
import axios from'axios';
import { useTranslation } from'../../composables/useTranslation';
import { useCurrency } from'@/Composables/useCurrency';
import { useDialog } from'../../composables/useDialog';

const { t } = useTranslation();
const { formatCurrency } = useCurrency();
const dialog = useDialog();

const loading = ref(true);
const transactions = ref<any[]>([]);
const paymentGateways = ref<any[]>([]);
const showDetailModal = ref(false);
const showProofModal = ref(false);
const proofImageUrl = ref('');
const selectedTransaction = ref<any>(null);
const updatingStatus = ref(false);
const confirmStatus = ref('');

const verifyForm = reactive({
 transaction_ref:'',
 admin_note:'',
 proof_of_payment: null as File | null
});

const onFileChange = (e: Event) => {
 const target = e.target as HTMLInputElement;
 if (target.files && target.files.length > 0) {
 verifyForm.proof_of_payment = target.files[0];
 }
};

const resetVerifyForm = () => {
 verifyForm.transaction_ref ='';
 verifyForm.admin_note ='';
 verifyForm.proof_of_payment = null;
};

const filters = reactive({
 gateway:'',
 status:'',
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
 resetVerifyForm();
 proofImageUrl.value ='';
 
 // Load proof image via axios if available
 if (transaction.payload?.proof_of_payment) {
 loadProofImage(transaction.id);
 }
 
 showDetailModal.value = true;
};

const loadProofImage = async (id: number) => {
 try {
 const response = await axios.get(`/api/v1/transactions/${id}/proof`, {
 responseType:'blob'
 });
 proofImageUrl.value = URL.createObjectURL(response.data);
 } catch (error) {
 console.error('Failed to load proof image', error);
 }
};

const updateStatus = async (transaction: any, status:'success' |'failed') => {
 const messageText = status ==='success' 
 ? t('Are you sure you want to mark this transaction as success? This will mark the order as Paid, change its status to Processing, and log this action. This action cannot be undone.')
 : t('Are you sure you want to mark this transaction as failed?');

 const isConfirmed = await dialog.confirm({
 title: t('Confirm Status Update'),
 message: messageText,
 confirmText: t('Yes, update it'),
 type: status ==='failed' ?'danger' :'info'
 });

 if (!isConfirmed) return;

 updatingStatus.value = true;
 confirmStatus.value = status;
 try {
 const formData = new FormData();
 formData.append('status', status);
 formData.append('_method','PATCH');
 
 if (status ==='success') {
 if (verifyForm.transaction_ref) formData.append('transaction_ref', verifyForm.transaction_ref);
 if (verifyForm.admin_note) formData.append('admin_note', verifyForm.admin_note);
 if (verifyForm.proof_of_payment) formData.append('proof_of_payment', verifyForm.proof_of_payment);
 }

 await axios.post(`/api/v1/transactions/${transaction.id}/status`, formData, {
 headers: {
'Content-Type':'multipart/form-data'
 }
 });
 dialog.success(t('Transaction status updated successfully'));
 showDetailModal.value = false;
 loadTransactions(pagination.current_page);
 } catch (error: any) {
 console.error('Error updating transaction:', error);
 dialog.error(error.response?.data?.message || t('Failed to update transaction status'));
 } finally {
 updatingStatus.value = false;
 confirmStatus.value ='';
 }
};

const getStatusClass = (status: string) => {
 const classes: Record<string, string> = {
 success:'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
 pending:'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
 failed:'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
 };
 return classes[status] ||'bg-gray-100 text-gray-800';
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
 paypal:'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
 stripe:'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
 momo:'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
 };
 return classes[gateway] ||'bg-gray-100 text-gray-800';
};

const formatDate = (date: string) => {
 return new Date(date).toLocaleString();
};

onMounted(() => {
 loadTransactions();
 fetchPaymentGateways();
});
</script>
