<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <div class="flex items-center gap-2">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ isEditing ? 'Edit Coupon' : 'New Coupon' }}</h1>
 <HelpGuide 
 :title="t('Coupons Guide')" 
 :description="t('Coupon Guide Text', couponGuideText)"
 />
 </div>
 <button
 @click="save"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 :disabled="saving"
 >
 {{ saving ?'Saving...' :'Save Coupon' }}
 </button>
 </div>

 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
 <div class="lg:col-span-2 space-y-6">
 <!-- Basic Info -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">General Information</h3>
 
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div class="col-span-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Coupon Code</label>
 <input v-model="form.code" type="text" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text uppercase" placeholder="e.g. SUMMER2024" />
 </div>
 
 <div class="col-span-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Title</label>
 <input v-model="form.title" type="text" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" placeholder="Promotion Name" />
 </div>
 
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Discount Type</label>
 <select v-model="form.type" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text">
 <option value="percent">Percentage (%)</option>
 <option value="fixed_amount">Fixed Amount ($)</option>
 </select>
 </div>
 
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Value</label>
 <input v-model="form.value" type="number" step="0.01" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" />
 </div>
 </div>
 </div>
 
 <!-- Usage Limits -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">Usage Limits</h3>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div>
 <div class="flex items-center justify-between mb-1">
   <label class="block text-sm font-medium text-admin-theme-text-secondary">Total Limit</label>
 </div>
 <div class="flex items-center gap-1.5">
   <input v-model="form.usage_limit" type="number" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" placeholder="Leave empty for unlimited" />
   <button
     type="button"
     @click="fillLimitFromRestrictedEmails"
     class="px-2.5 py-2 text-xs font-medium bg-admin-theme-hover text-admin-theme-text border border-admin-theme-border rounded-lg hover:bg-admin-theme-primary hover:text-white transition-colors flex items-center gap-1 whitespace-nowrap"
     :title="t('Set Total Limit equal to count of restricted emails')"
   >
     <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
     </svg>
     <span>{{ form.restricted_emails?.length ? `Sync (${form.restricted_emails.length})` : 'Sync' }}</span>
   </button>
 </div>
 </div>
 
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Limit Per User</label>
 <input v-model="form.usage_limit_per_user" type="number" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" />
 </div>
 
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Min Order Value</label>
 <input v-model="form.min_order_value" type="number" step="0.01" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" />
 </div>

 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Max Discount Value</label>
 <input v-model="form.max_discount_value" type="number" step="0.01" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" placeholder="No limit" />
 </div>


 <div class="col-span-2">
  <div class="col-span-2">
  <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Restricted to Users</label>
  <UserSearch v-model="form.restricted_emails" />
  <p class="text-xs text-gray-500 mt-1">If set, only these users can use the coupon.</p>
  </div>

  <div class="col-span-2 mt-4">
  <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Restricted to Products</label>
  <ProductSearch v-model="productIds" :initial-products="form.selected_products" />
  <p class="text-xs text-gray-500 mt-1">If set, coupon will only apply to these specific products in the cart.</p>
  </div>
  </div>
  </div>
  </div>
  </div>
 
 <div class="space-y-6">
 <!-- Status & Schedule -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">Publishing</h3>
 
 <div class="space-y-3 mb-4">
 <div class="flex items-center">
 <input v-model="form.is_active" type="checkbox" id="is_active" class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-gray-300 rounded" />
 <label for="is_active" class="ml-2 block text-sm text-admin-theme-text">Active</label>
 </div>

 <div class="flex items-center">
 <input v-model="form.is_public" type="checkbox" id="is_public" class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-gray-300 rounded" />
 <label for="is_public" class="ml-2 block text-sm text-admin-theme-text">Publicly Available</label>
 <span class="ml-2 text-xs text-gray-500">(Shown in"Available Coupons" list)</span>
 </div>

 <div class="flex items-center">
 <input v-model="form.is_exclusive" type="checkbox" id="is_exclusive" class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-gray-300 rounded" />
 <label for="is_exclusive" class="ml-2 block text-sm text-admin-theme-text">Exclusive Discount</label>
 <span class="ml-2 text-xs text-gray-500">(Cannot be combined with other coupons)</span>
 </div>
 </div>
 
 <div class="space-y-4">
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Start Date</label>
 <input v-model="form.starts_at" type="datetime-local" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" />
 </div>
 
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Expiration Date</label>
 <input v-model="form.expires_at" type="datetime-local" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" />
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import UserSearch from '../../components/UserSearch.vue';
import ProductSearch from '../../components/ProductSearch.vue';
import { useTranslation } from '../../composables/useTranslation';

const { t } = useTranslation();

const couponGuideText = `
<ul class="list-disc pl-4 space-y-2 mt-2">
  <li><strong>Coupon Code:</strong> The unique code customers input at checkout (e.g., <code>SUMMER2026</code>). It is automatically converted to uppercase.</li>
  <li><strong>Discount Type & Value:</strong> Choose between a <em>Percentage (%)</em> reduction or a <em>Fixed Amount</em> deduction applied to the cart total.</li>
  <li><strong>Usage Limits:</strong> 
    <ul class="list-circle pl-4 mt-1 space-y-1">
      <li><em>Total Limit:</em> Maximum number of times the coupon can be used overall (leave empty for unlimited). <span class="text-xs text-blue-400 block mt-1">💡 <strong>Restricted List Strategy:</strong> For 1-time coupons restricted to specific emails, set <code>Total Limit</code> equal to the total count of restricted emails (or click the <strong>Sync</strong> button next to Total Limit) and set <code>Limit Per User</code> to 1. This guarantees every listed user gets exactly 1 use.</span></li>
      <li><em>Limit Per User:</em> Maximum times a single registered customer can use the coupon.</li>
    </ul>
  </li>
  <li><strong>Order Thresholds:</strong> Set a <em>Min Order Value</em> to restrict usage to larger orders, or define a <em>Max Discount Value</em> to cap the maximum discount savings on percentage-based coupons.</li>
  <li><strong>Restricted Users:</strong> Specify target emails to restrict the coupon to exclusive customers.</li>
  <li><strong>Restricted Products:</strong> Select specific products to restrict discount applicability. The discount will only calculate against matching products in the cart.</li>
  <li><strong>Publishing Flags:</strong>
    <ul class="list-circle pl-4 mt-1 space-y-1">
      <li><em>Active:</em> Instantly enable or disable coupon eligibility.</li>
      <li><em>Publicly Available:</em> Makes the coupon visible on the customer's available coupons list.</li>
      <li><em>Exclusive Discount:</em> If enabled, customers cannot stack this coupon with other coupon codes.</li>
    </ul>
  </li>
  <li><strong>Validity Period:</strong> Define optional start and expiration dates to schedule the promotion campaign automatically.</li>
</ul>
`;

const route = useRoute();
const router = useRouter();
const dialog = useDialog();
const isEditing = computed(() => !!route.params.id);
const saving = ref(false);

const form = ref({
 code:'',
 title:'',
 type:'percent',
 value: 0,
 min_order_value: 0,
 max_discount_value: null,
 usage_limit: null as number | null,
 usage_limit_per_user: 1,
 starts_at:'',
 expires_at:'',
 restricted_emails: [] as string[],
 scope_config: { product_ids: [] as number[] } as any,
 selected_products: [] as any[],
 is_active: true,
 is_public: false,
 is_exclusive: false,
});

const productIds = computed({
  get() {
    return form.value.scope_config?.product_ids || [];
  },
  set(val: number[]) {
    if (!form.value.scope_config) {
      form.value.scope_config = {};
    }
    form.value.scope_config.product_ids = val;
  }
});

const fillLimitFromRestrictedEmails = () => {
 const count = form.value.restricted_emails?.length || 0;
 if (count > 0) {
  form.value.usage_limit = count;
  dialog.success(`Total Limit automatically set to ${count} (equal to restricted emails count).`);
 } else {
  dialog.error('No restricted emails added yet. Please add emails in "Restricted to Users" first.');
 }
};

const loadCoupon = async () => {
 if (!isEditing.value) return;
 try {
 const { data } = await axios.get(`/api/v1/coupons/${route.params.id}`);
 form.value = data;
 } catch (e) {
 console.error(e);
 }
};

const save = async () => {
 saving.value = true;
 try {
 let response;
 if (isEditing.value) {
 response = await axios.put(`/api/v1/coupons/${route.params.id}`, form.value);
 dialog.success('Coupon updated successfully.');
 // Just reload the data to be sure
 await loadCoupon();
 } else {
 response = await axios.post('/api/v1/coupons', form.value);
 dialog.success('Coupon created successfully.');
 
 // Redirect to the edit page of the newly created coupon
 const newId = response.data.id || response.data.data?.id;
 if (newId) {
 router.push({ name:'admin.coupons.edit', params: { id: newId } });
 }
 }
 } catch (e: any) {
 dialog.error(e.response?.data?.message ||'Error saving coupon');
 } finally {
 saving.value = false;
 }
};

onMounted(loadCoupon);
</script>
