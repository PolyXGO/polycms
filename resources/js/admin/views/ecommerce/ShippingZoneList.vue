<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ $t('Shipping Zones') }}</h1>
 <router-link
 :to="{ name:'admin.ecommerce.shipping-zones.create' }"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors cursor-pointer"
 >
 + {{ $t('New Shipping Zone') }}
 </router-link>
 </div>

 <AdminLoadingState v-if="loading" :message="$t('Loading shipping zones...')" />

 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Zone Name') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Regions') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Methods') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Priority') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Status') }}</th>
 <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Actions') }}</th>
 </tr>
 </thead>
 <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
 <tr v-for="zone in zones" :key="zone.id">
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-admin-theme-text">
 {{ zone.name }}
 </td>
 <td class="px-6 py-4 text-sm text-admin-theme-text-muted">
 <span v-for="(region, i) in zone.regions" :key="i" class="inline-block bg-admin-theme-base rounded px-2 py-1 text-xs mr-1 mb-1">
 {{ region.country ==='*' ? $t('Rest of World') : region.country }}
 </span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 <span class="font-bold text-admin-theme-primary dark:text-admin-theme-primary">{{ zone.methods_count }}</span> {{ $t('methods') }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
 {{ zone.priority }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span :class="zone.is_active ?'bg-green-100 text-green-800' :'bg-gray-100 text-gray-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
 {{ zone.is_active ? $t('Active') : $t('Inactive') }}
 </span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <router-link :to="{ name:'admin.ecommerce.shipping-zones.edit', params: { id: zone.id } }" class="text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary mr-3">{{ $t('Edit') }}</router-link>
 <button @click="deleteZone(zone.id)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400" :disabled="deleting === zone.id">
 {{ deleting === zone.id ? $t('Deleting...') : $t('Delete') }}
 </button>
 </td>
 </tr>
 <tr v-if="zones.length === 0">
 <td colspan="6" class="px-6 py-4 text-center text-admin-theme-text-muted">{{ $t('No shipping zones found.') }}</td>
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
import { useTranslation } from'../../composables/useTranslation';
import { isDemoRestrictionError } from'../../utils/demoRestriction';

const { t } = useTranslation();

const zones = ref<any[]>([]);
const loading = ref(true);
const deleting = ref<number | null>(null);

const loadZones = async () => {
 loading.value = true;
 try {
 const response = await axios.get('/api/v1/shipping-zones');
 zones.value = response.data.data;
 } catch (e) {
 console.error(e);
 } finally {
 loading.value = false;
 }
};

const deleteZone = async (id: number) => {
 if (!confirm(t('Are you sure you want to delete this shipping zone?'))) return;
 deleting.value = id;
 try {
 await axios.delete(`/api/v1/shipping-zones/${id}`);
 await loadZones();
 } catch (e: any) {
 if (isDemoRestrictionError(e)) return;
 console.error(e);
 alert(t('Failed to delete shipping zone'));
 } finally {
 deleting.value = null;
 }
};

onMounted(loadZones);
</script>
