<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold text-admin-theme-text">Coupons</h1>
 <router-link
 :to="{ name:'admin.coupons.create' }"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 >
 + New Coupon
 </router-link>
 </div>

 <div class="bg-admin-theme-surface rounded-lg shadow p-4 mb-6">
 <input
 v-model="search"
 type="text"
 placeholder="Search coupons..."
 class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text w-full md:w-1/3"
 @input="loadCoupons"
 />
 </div>

 <AdminLoadingState v-if="loading" message="Loading coupons..." />

 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
 <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
 </tr>
 </thead>
 <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
 <tr v-for="coupon in coupons" :key="coupon.id">
 <td class="px-6 py-4 whitespace-nowrap font-mono text-admin-theme-primary font-bold">
 {{ coupon.code }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ coupon.title }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text">
 {{ coupon.type ==='percent' ? coupon.value +'%' : formatCurrency(coupon.value) }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ coupon.usage_count }} / {{ coupon.usage_limit ||'∞' }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span :class="coupon.is_active ?'bg-green-100 text-green-800' :'bg-gray-100 text-gray-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
 {{ coupon.is_active ?'Active' :'Inactive' }}
 </span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <router-link :to="{ name:'admin.coupons.edit', params: { id: coupon.id } }" class="text-admin-theme-primary hover:text-admin-theme-primary mr-3">Edit</router-link>
 <button @click="deleteCoupon(coupon.id)" class="text-red-600 hover:text-red-900" :disabled="deleting === coupon.id">
 {{ deleting === coupon.id ?'Deleting...' :'Delete' }}
 </button>
 </td>
 </tr>
 <tr v-if="coupons.length === 0">
 <td colspan="6" class="px-6 py-4 text-center text-gray-500">No coupons found.</td>
 </tr>
 </tbody>
 </table>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from'vue';
import axios from'axios';
import AdminLoadingState from'../../components/AdminLoadingState.vue';
import { useCurrency } from'@/Composables/useCurrency';
import { isDemoRestrictionError } from'../../utils/demoRestriction';

const { formatCurrency } = useCurrency();

const coupons = ref<any[]>([]);
const loading = ref(true);
const search = ref('');
const deleting = ref<number | null>(null);

const loadCoupons = async () => {
 loading.value = true;
 try {
 const response = await axios.get('/api/v1/coupons', { params: { search: search.value } });
 coupons.value = response.data.data;
 } catch (e) {
 console.error(e);
 } finally {
 loading.value = false;
 }
};

const deleteCoupon = async (id: number) => {
 if (!confirm('Are you sure you want to delete this coupon?')) return;
 deleting.value = id;
 try {
 await axios.delete(`/api/v1/coupons/${id}`);
 await loadCoupons();
 } catch (e: any) {
 if (isDemoRestrictionError(e)) return;
 console.error(e);
 alert('Failed to delete coupon');
 } finally {
 deleting.value = null;
 }
};

onMounted(loadCoupons);
</script>
