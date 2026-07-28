<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Orders') }}</h1>
 </div>

 <!-- Filters -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-4 mb-6">
 <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
 <input
 v-model="filters.search"
 type="text"
 :placeholder="t('Search orders...')"
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
 @input="loadOrders"
 />
 <select v-model="filters.status" @change="loadOrders" class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ t('All Status') }}</option>
 <option value="pending">{{ t('Pending') }}</option>
 <option value="processing">{{ t('Processing') }}</option>
 <option value="completed">{{ t('Completed') }}</option>
 <option value="cancelled">{{ t('Cancelled') }}</option>
 <option value="failed">{{ t('Failed') }}</option>
 </select>
 </div>
 </div>

 <AdminLoadingState v-if="loading" :message="t('Loading orders...')" />

 <!-- Orders Table -->
 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Order Code') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Customer') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Total') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Status') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Date') }}</th>
 <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Actions') }}</th>
 </tr>
 </thead>
 <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
 <tr v-for="order in orders" :key="order.id">
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary">
 {{ order.code }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 <div v-if="order.user">
 <span class="font-medium text-admin-theme-text">{{ order.user.name }}</span>
 <div class="text-xs text-gray-500">{{ order.user.email }}</div>
 </div>
 <div v-else>
 <span class="font-medium text-admin-theme-text">{{ order.billing_address?.full_name || t('Guest') }}</span>
 <div v-if="order.guest_email" class="text-xs text-gray-500">{{ order.guest_email }}</div>
 </div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text">
 {{ formatCurrency(order.total_amount) }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span :class="getStatusClass(order.status)">
 {{ getStatusLabel(order.status) }}
 </span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ formatDate(order.created_at) }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <!-- Link to detail page -->
 <button 
 @click="viewOrder(order.id)"
 class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary"
 >
 {{ t('View') }}
 </button>
 </td>
 </tr>
 <tr v-if="orders.length === 0">
 <td colspan="6" class="px-6 py-4 text-center text-admin-theme-text-muted">
 {{ t('No orders found.') }}
 </td>
 </tr>
 </tbody>
 </table>
 </div>
 
 <!-- Pagination (Simplified) -->
 <div v-if="!loading && pagination.total > pagination.per_page" class="mt-6 flex justify-end gap-2">
 <button 
 @click="changePage(pagination.current_page - 1)" 
 :disabled="pagination.current_page === 1" 
 class="px-3 py-1 border border-admin-theme-border rounded bg-admin-theme-surface text-admin-theme-text-secondary hover:bg-admin-theme-base disabled:opacity-50 transition-colors"
 >
 {{ t('Previous') }}
 </button>
 <button 
 @click="changePage(pagination.current_page + 1)" 
 :disabled="pagination.current_page === pagination.last_page" 
 class="px-3 py-1 border border-admin-theme-border rounded bg-admin-theme-surface text-admin-theme-text-secondary hover:bg-admin-theme-base disabled:opacity-50 transition-colors"
 >
 {{ t('Next') }}
 </button>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from'vue';
import { useRouter } from'vue-router';
import axios from'axios';
import AdminLoadingState from'../../components/AdminLoadingState.vue';
import { useCurrency } from'@/Composables/useCurrency';
import { useTranslation } from'../../composables/useTranslation';

const router = useRouter();
const orders = ref<any[]>([]);
const loading = ref(true);
const filters = ref({ search:'', status:'' });
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

const { t } = useTranslation();
const { formatCurrency } = useCurrency();

const formatDate = (date: string) => {
 return new Date(date).toLocaleDateString();
};

const getStatusClass = (status: string) => {
 const map: Record<string, string> = {
 completed:'bg-green-100 text-green-800',
 pending:'bg-yellow-100 text-yellow-800',
 cancelled:'bg-red-100 text-red-800',
 processing:'bg-blue-100 text-blue-800',
 failed:'bg-red-100 text-red-800',
 };
 return `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${map[status] ||'bg-gray-100 text-gray-800'}`;
};

const getStatusLabel = (status: string) => {
 const map: Record<string, string> = {
 completed: t('Completed'),
 pending: t('Pending'),
 cancelled: t('Cancelled'),
 processing: t('Processing'),
 failed: t('Failed'),
 };
 return map[status] || status;
};

const loadOrders = async () => {
 loading.value = true;
 try {
 const params = { ...filters.value, page: pagination.value.current_page };
 const response = await axios.get('/api/v1/orders', { params });
 orders.value = response.data.data;
 pagination.value = { ...pagination.value, ...response.data }; // Simplified mapping
 } catch (error) {
 console.error('Error loading orders', error);
 } finally {
 loading.value = false;
 }
};

const changePage = (page: number) => {
 pagination.value.current_page = page;
 loadOrders();
};

const viewOrder = (id: number) => {
 router.push({ name:'admin.orders.show', params: { id } });
};

onMounted(loadOrders);
</script>
