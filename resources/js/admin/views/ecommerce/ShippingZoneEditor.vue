<template>
 <div class="w-full">
 <div class="flex items-center mb-6">
 <router-link :to="{ name:'admin.ecommerce.shipping-zones.index' }" class="text-admin-theme-text-muted hover:text-gray-700 dark:text-admin-theme-text-muted mr-4">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
 </router-link>
 <h1 class="text-2xl font-bold text-admin-theme-text">
 {{ isEditing ? $t('Edit Shipping Zone') : $t('New Shipping Zone') }}
 </h1>
 </div>

 <form @submit.prevent="saveZone" novalidate class="space-y-6">
 <!-- Zone Basics -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-6">
 <h2 class="text-lg font-bold mb-4 text-admin-theme-text">{{ $t('Zone Details') }}</h2>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Zone Name') }} *</label>
 <input v-model="form.name" type="text" required :class="{'border-red-500': errors.name}" class="w-full px-3 py-2 border border-admin-theme-border rounded-md bg-admin-theme-input-bg text-admin-theme-text" placeholder="e.g. North America, Domestic" />
 <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name[0] }}</p>
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Priority') }}</label>
 <input v-model.number="form.priority" type="number" min="0" class="w-full px-3 py-2 border border-admin-theme-border rounded-md bg-admin-theme-input-bg text-admin-theme-text" />
 <p class="text-xs text-gray-500 mt-1">{{ $t('Lower number gets calculated first') }}</p>
 </div>
 </div>

 <div class="mt-4 flex items-center">
 <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-gray-300 rounded" />
 <label for="is_active" class="ml-2 block text-sm text-admin-theme-text">
 {{ $t('Active') }}
 </label>
 </div>

 <!-- Regions -->
 <div class="mt-6">
 <h3 class="text-sm font-medium text-admin-theme-text-secondary mb-2">{{ $t('Regions') }} *</h3>
 <CountryRegionSelect v-model="form.regions" :multiple="true" :error="!!errors.regions" />
 <p v-if="errors.regions" class="mt-1 text-sm text-red-500">{{ errors.regions[0] }}</p>
 <p v-else class="text-xs text-gray-500 mt-2">{{ $t('Customers will be matched against these regions.') }}</p>
 </div>
 </div>

 <!-- Shipping Methods -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-6">
 <div class="flex justify-between items-center mb-4">
 <h2 class="text-lg font-bold text-admin-theme-text">{{ $t('Shipping Methods') }}</h2>
 <button type="button" @click="addMethod" class="px-3 py-1 bg-admin-theme-base text-admin-theme-text-secondary rounded hover:bg-admin-theme-base text-sm font-medium">
 + {{ $t('Add Method') }}
 </button>
 </div>

 <div v-if="form.methods.length === 0" class="text-center py-8 text-admin-theme-text-muted bg-admin-theme-base/50 rounded-lg border border-dashed border-admin-theme-border">
 {{ $t('No shipping methods defined for this zone.') }}
 </div>

 <div class="space-y-4">
 <div v-for="(method, index) in form.methods" :key="index" class="border border-admin-theme-border rounded-lg p-4 bg-admin-theme-base/30">
 <div class="flex justify-between items-start mb-4">
 <h3 class="font-bold text-admin-theme-text">{{ $t('Method') }} #{{ index + 1 }}</h3>
 <button type="button" @click="removeMethod(index)" class="text-red-500 hover:text-red-700 text-sm">{{ $t('Remove') }}</button>
 </div>
 
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
 <div class="col-span-2">
 <label class="block text-xs font-medium text-gray-700 dark:text-admin-theme-text-muted mb-1">{{ $t('Method Name') }} *</label>
 <input v-model="method.name" type="text" required :class="{'border-red-500': errors[`methods.${index}.name`]}" class="w-full px-3 py-1.5 border border-admin-theme-border rounded md:text-sm bg-admin-theme-input-bg text-admin-theme-text" placeholder="e.g. Standard Shipping" />
 <p v-if="errors[`methods.${index}.name`]" class="mt-1 text-xs text-red-500">{{ errors[`methods.${index}.name`][0] }}</p>
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-700 dark:text-admin-theme-text-muted mb-1">{{ $t('Type') }}</label>
 <select v-model="method.type" class="w-full px-3 py-1.5 border border-admin-theme-border rounded md:text-sm bg-admin-theme-input-bg text-admin-theme-text">
 <option value="flat_rate">{{ $t('Flat Rate') }}</option>
 <option value="free_shipping">{{ $t('Free Shipping') }}</option>
 <option value="pickup">{{ $t('Local Pickup') }}</option>
 </select>
 </div>
 <div v-if="method.type !=='free_shipping'">
 <label class="block text-xs font-medium text-gray-700 dark:text-admin-theme-text-muted mb-1">{{ $t('Tax Status') }}</label>
 <select v-model="method.tax_status" class="w-full px-3 py-1.5 border border-admin-theme-border rounded md:text-sm bg-admin-theme-input-bg text-admin-theme-text">
 <option value="taxable">{{ $t('Taxable') }}</option>
 <option value="none">{{ $t('None') }}</option>
 </select>
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
 <div v-if="method.type !=='free_shipping'">
 <label class="block text-xs font-medium text-gray-700 dark:text-admin-theme-text-muted mb-1">{{ $t('Cost') }} *</label>
 <input v-model="method.cost" type="number" step="0.01" min="0" required class="w-full px-3 py-1.5 border border-admin-theme-border rounded md:text-sm bg-admin-theme-input-bg text-admin-theme-text" />
 </div>

 <div v-if="method.type ==='free_shipping'">
 <label class="block text-xs font-medium text-gray-700 dark:text-admin-theme-text-muted mb-1">{{ $t('Free Shipping Requires') }}</label>
 <select v-model="method.requires" class="w-full px-3 py-1.5 border border-admin-theme-border rounded md:text-sm bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">{{ $t('No requirement') }}</option>
 <option value="coupon">{{ $t('A valid free shipping coupon') }}</option>
 <option value="min_amount">{{ $t('A minimum order amount') }}</option>
 <option value="either">{{ $t('A minimum order amount OR coupon') }}</option>
 <option value="both">{{ $t('A minimum order amount AND coupon') }}</option>
 </select>
 </div>

 <div v-if="method.type !=='free_shipping' || ['min_amount','either','both'].includes(method.requires)">
 <label class="block text-xs font-medium text-gray-700 dark:text-admin-theme-text-muted mb-1">
 <template v-if="method.type ==='free_shipping'">{{ $t('Minimum Order Amount') }} *</template>
 <template v-else>{{ $t('Free Shipping Threshold') }} ({{ $t('Optional') }})</template>
 </label>
 <input v-model="method.free_threshold" type="number" step="0.01" min="0" 
 :required="method.type ==='free_shipping'" 
 :placeholder="method.type ==='free_shipping' ? $t('e.g. 50.00') : $t('e.g. 100 to override cost to 0')"
 class="w-full px-3 py-1.5 border border-admin-theme-border rounded md:text-sm bg-admin-theme-input-bg text-admin-theme-text" />
 </div>

 <div>
 <label class="block text-xs font-medium text-gray-700 dark:text-admin-theme-text-muted mb-1">{{ $t('Estimated Days') }} ({{ $t('Optional') }})</label>
 <input v-model="method.estimated_days" type="text" placeholder="e.g. 3-5 business days" class="w-full px-3 py-1.5 border border-admin-theme-border rounded md:text-sm bg-admin-theme-input-bg text-admin-theme-text" />
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="flex justify-end pt-4 mb-20">
 <button type="submit" :disabled="saving" class="px-6 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-md hover:bg-admin-theme-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-admin-theme-primary disabled:opacity-50">
 {{ saving ? $t('Saving...') : $t('Save Shipping Zone') }}
 </button>
 </div>
 </form>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from'vue';
import { useRoute, useRouter } from'vue-router';
import axios from'axios';
import { useTranslation } from'../../composables/useTranslation';
import CountryRegionSelect from'../../components/Form/CountryRegionSelect.vue';
import { isDemoRestrictionError } from'../../utils/demoRestriction';

const { t } = useTranslation();
const route = useRoute();
const router = useRouter();

const isEditing = computed(() => !!route.params.id);
const saving = ref(false);
const errors = ref<Record<string, string[]>>({});

const form = ref({
 name:'',
 is_active: true,
 priority: 0,
 regions: [{ country:'*' }] as any[],
 methods: [] as any[]
});

const addMethod = () => {
 form.value.methods.push({
 name:'',
 type:'flat_rate',
 cost: 0,
 tax_status:'none',
 requires:'',
 free_threshold: null,
 estimated_days:'',
 is_active: true,
 });
};

const removeMethod = (index: number) => {
 form.value.methods.splice(index, 1);
};

const loadZone = async () => {
 try {
 const response = await axios.get(`/api/v1/shipping-zones/${route.params.id}`);
 const data = response.data.data;
 form.value = {
 name: data.name,
 is_active: data.is_active,
 priority: data.priority,
 regions: data.regions || [],
 methods: data.methods || [],
 };
 } catch (e) {
 console.error(e);
 alert(t('Failed to load shipping zone'));
 router.push({ name:'admin.ecommerce.shipping-zones.index' });
 }
};

const saveZone = async () => {
 errors.value = {};
 if (!form.value.regions || form.value.regions.length === 0) {
 errors.value.regions = [t('Please select a region, or select"Everywhere".')];
 return;
 }

 if (!form.value.name.trim()) {
 errors.value.name = [t('Zone Name is required.')];
 return;
 }

 let hasMethodError = false;
 form.value.methods.forEach((method, idx) => {
 if (!method.name || !method.name.trim()) {
 errors.value[`methods.${idx}.name`] = [t('Method Name is required.')];
 hasMethodError = true;
 }
 });

 if (hasMethodError) {
 return;
 }

 const payload = {
 ...form.value,
 regions: form.value.regions
 };

 saving.value = true;
 try {
 if (isEditing.value) {
 await axios.put(`/api/v1/shipping-zones/${route.params.id}`, payload);
 alert(t('Shipping zone updated successfully'));
 } else {
 const res = await axios.post('/api/v1/shipping-zones', payload);
 alert(t('Shipping zone created successfully'));
 router.push({ name:'admin.ecommerce.shipping-zones.edit', params: { id: res.data.data.id } });
 }
 } catch (e: any) {
 if (isDemoRestrictionError(e)) return;
 if (e.response?.status === 422) {
 errors.value = e.response.data.errors;
 alert(t('Please fix validation errors'));
 } else {
 console.error(e);
 alert(e.response?.data?.message || t('Failed to save shipping zone'));
 }
 } finally {
 saving.value = false;
 }
};

onMounted(() => {
 if (isEditing.value) {
 loadZone();
 }
});
</script>
