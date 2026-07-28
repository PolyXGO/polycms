<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Subscriptions') }}</h1>
    </div>

    <!-- Filters -->
    <div class="bg-admin-theme-surface rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input
          v-model="filters.search"
          type="text"
          :placeholder="t('Search by product or user...')"
          class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
          @input="debouncedLoad"
        />
        <select
          v-model="filters.status"
          @change="loadSubscriptions"
          class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
        >
          <option value="">{{ t('All Status') }}</option>
          <option value="active">{{ t('Active') }}</option>
          <option value="expired">{{ t('Expired') }}</option>
          <option value="cancelled">{{ t('Cancelled') }}</option>
          <option value="suspended">{{ t('Suspended') }}</option>
        </select>
      </div>
    </div>

    <AdminLoadingState v-if="loading" :message="t('Loading subscriptions...')" />

    <!-- Subscriptions Table -->
    <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-admin-theme-border">
        <thead class="bg-admin-theme-base">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Product') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('User') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Price') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Status') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Starts At') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Expires At') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Auto Renew') }}</th>
          </tr>
        </thead>
        <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
          <tr v-for="sub in subscriptions" :key="sub.id">
            <!-- Product Column -->
            <td class="px-6 py-4 text-sm font-medium text-admin-theme-text">
              <a
                v-if="sub.product?.slug"
                :href="getProductUrl(sub.product.slug)"
                target="_blank"
                class="block font-semibold text-admin-theme-primary hover:underline"
              >
                {{ sub.product?.name || sub.service?.name || '-' }}
              </a>
              <span v-else class="block font-semibold text-admin-theme-text">
                {{ sub.product?.name || sub.service?.name || '-' }}
              </span>
              
              <router-link
                v-if="sub.order"
                :to="{ name: 'admin.orders.show', params: { id: sub.order.id } }"
                class="inline-flex items-center gap-1 mt-1 text-xs text-gray-500 hover:text-admin-theme-primary transition-colors font-normal"
              >
                <i class="fas fa-shopping-bag text-[10px]"></i>
                {{ t('Order') }}: #{{ sub.order.code }}
              </router-link>
            </td>

            <!-- User Column -->
            <td class="px-6 py-4 text-sm text-admin-theme-text-muted">
              <div v-if="sub.user">
                <span class="font-medium text-admin-theme-text">{{ sub.user.name }}</span>
                <div class="text-xs text-gray-500">{{ sub.user.email }}</div>
              </div>
              <span v-else>N/A</span>
            </td>

            <!-- Price Column -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-admin-theme-text">
              {{ formatCurrency(sub.paid_price !== undefined && sub.paid_price !== null ? sub.paid_price : (sub.recurring_price || sub.service?.price || sub.product?.price)) }}
            </td>

            <!-- Status Column -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="{
                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': sub.status === 'active',
                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': sub.status === 'expired' || sub.status === 'cancelled',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': sub.status === 'suspended'
                }">
                {{ t(sub.status) || sub.status }}
              </span>
            </td>

            <!-- Starts At Column -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
              {{ sub.starts_at ? new Date(sub.starts_at).toLocaleDateString() : '-' }}
            </td>

            <!-- Expires At Column -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted font-medium">
              <span :class="sub.expires_at ? '' : 'text-emerald-600 dark:text-emerald-400 font-bold'">
                {{ getExpiresAtText(sub) }}
              </span>
            </td>

            <!-- Auto Renew Column -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
              <span v-if="sub.is_auto_renew" class="text-green-600 dark:text-green-400 font-bold">{{ t('Yes') }}</span>
              <span v-else class="text-gray-400 dark:text-zinc-600">{{ t('No') }}</span>
            </td>
          </tr>
          <tr v-if="subscriptions.length === 0">
            <td colspan="7" class="px-6 py-4 text-center text-admin-theme-text-muted">
              {{ t('No subscriptions found.') }}
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
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLoadingState from '../../components/AdminLoadingState.vue';
import { useTranslation } from '../../composables/useTranslation';
import { useCurrency } from '@/Composables/useCurrency';

const subscriptions = ref<any[]>([]);
const loading = ref(true);
const filters = ref({ search: '', status: '' });
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

const { t } = useTranslation();
const { formatCurrency } = useCurrency();

let debounceTimeout: number | undefined;

const debouncedLoad = () => {
  window.clearTimeout(debounceTimeout);
  debounceTimeout = window.setTimeout(() => {
    pagination.value.current_page = 1;
    loadSubscriptions();
  }, 300);
};

const loadSubscriptions = async () => {
  loading.value = true;
  try {
    const params = {
      ...filters.value,
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
    };
    const response = await axios.get('/api/v1/subscriptions', { params });
    subscriptions.value = response.data.data ?? [];
    pagination.value = { ...pagination.value, ...response.data };
  } catch (e) {
    console.error('Error loading subscriptions', e);
  } finally {
    loading.value = false;
  }
};

const changePage = (page: number) => {
  pagination.value.current_page = page;
  loadSubscriptions();
};

const getExpiresAtText = (sub: any) => {
  if (!sub.expires_at) {
    return t('Lifetime / Never Expires') || 'Lifetime / Never Expires';
  }
  const expiry = new Date(sub.expires_at);
  const today = new Date();
  today.setHours(0,0,0,0);
  expiry.setHours(0,0,0,0);
  
  const dateStr = expiry.toLocaleDateString();
  
  if (sub.status !== 'active' && sub.status !== 'suspended') {
    return dateStr;
  }
  
  const starts = sub.starts_at ? new Date(sub.starts_at) : null;
  if (starts) starts.setHours(0,0,0,0);
  
  if (starts && starts > today) {
    const startDiff = Math.ceil((starts.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    return `${dateStr} (${t('Starts in') || 'Starts in'} ${startDiff} ${t('days') || 'days'})`;
  }
  
  const diffTime = expiry.getTime() - today.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays < 0) {
    return `${dateStr} (${t('Expired') || 'Expired'} ${Math.abs(diffDays)} ${t('days ago') || 'days ago'})`;
  } else if (diffDays === 0) {
    return `${dateStr} (${t('Expires today') || 'Expires today'})`;
  } else if (diffDays === 1) {
    return `${dateStr} (${t('1 day remaining') || '1 day remaining'})`;
  } else {
    return `${dateStr} (${diffDays} ${t('days remaining') || 'days remaining'})`;
  }
};

const getProductUrl = (slug: string) => {
  const pathParts = window.location.pathname.split('/');
  const activeLocales = ['vi', 'zh'];
  const currentLocale = pathParts[1];
  
  if (activeLocales.includes(currentLocale)) {
    return `/${currentLocale}/products/${slug}`;
  }
  return `/products/${slug}`;
};

onMounted(loadSubscriptions);
</script>
