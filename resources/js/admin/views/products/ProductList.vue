<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h1 class="text-2xl font-bold text-admin-theme-text">{{ $t('Products') }}</h1>
      <router-link
        :to="{ name: 'admin.products.create' }"
        class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors font-medium text-sm"
      >
        + {{ $t('New Product') }}
      </router-link>
    </div>

    <!-- Status Tabs (WordPress Style - Fixed Height & Padding, No Size Shifting on Active) -->
    <div class="flex items-center gap-2 flex-wrap text-sm text-admin-theme-text-muted mb-4 select-none">
      <template v-for="(statusItem, index) in statusTabs" :key="statusItem.key">
        <button
          type="button"
          @click="setStatus(statusItem.key)"
          :class="[
            'px-3 py-1.5 rounded-md text-xs transition-colors flex items-center gap-1.5 cursor-pointer font-medium h-8 border',
            filters.status === statusItem.key
              ? 'bg-admin-theme-primary text-admin-theme-primary-content border-admin-theme-primary shadow-xs'
              : 'bg-admin-theme-surface text-admin-theme-text-secondary hover:text-admin-theme-text hover:bg-admin-theme-base border-admin-theme-border'
          ]"
        >
          <span>{{ statusItem.label }}</span>
          <span
            :class="[
              'px-1.5 py-0.2 rounded-full text-[11px] font-medium leading-normal',
              filters.status === statusItem.key
                ? 'bg-white/20 text-white'
                : 'bg-admin-theme-base text-admin-theme-text-muted border border-admin-theme-border/60'
            ]"
          >
            {{ statusCounts[statusItem.key === '' ? 'all' : statusItem.key] ?? 0 }}
          </span>
        </button>
      </template>
    </div>

    <!-- Action & Filter Toolbar (WordPress Style Layout) -->
    <div class="bg-admin-theme-surface rounded-lg shadow p-3.5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <!-- Left: Bulk Actions & Filters -->
      <div class="flex items-center gap-2.5 flex-wrap">
        <!-- Bulk Action Select -->
        <select
          v-model="selectedBulkAction"
          class="px-3 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm cursor-pointer min-w-[140px]"
        >
          <option value="">{{ $t('Bulk actions') }}</option>
          <template v-if="filters.status === 'trash'">
            <option value="restore">{{ $t('Restore') }}</option>
            <option value="force_delete">{{ $t('Delete Permanently') }}</option>
          </template>
          <template v-else>
            <option value="trash">{{ $t('Move to Trash') }}</option>
          </template>
        </select>

        <!-- Apply Button -->
        <button
          type="button"
          @click="applyBulkAction"
          :disabled="!selectedBulkAction || selectedProductIds.length === 0"
          class="px-3.5 py-1.5 bg-admin-theme-surface border border-admin-theme-border text-admin-theme-text hover:bg-admin-theme-base rounded-lg text-sm font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
        >
          {{ $t('Apply') }}
        </button>

        <span class="text-admin-theme-border hidden sm:inline">|</span>

        <!-- Language Filter -->
        <select
          v-model="filters.locale"
          @change="loadProducts"
          class="px-3 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm cursor-pointer min-w-[140px]"
        >
          <option value="">{{ $t('All Languages') }}</option>
          <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
        </select>
      </div>

      <!-- Right: Search Input & Search Button -->
      <div class="flex items-center gap-2">
        <input
          v-model="filters.search"
          type="text"
          :placeholder="$t('Search products...')"
          class="px-3 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm placeholder-admin-theme-text-muted w-full md:w-60"
          @keyup.enter="onSearchSubmit"
        />
        <button
          type="button"
          @click="onSearchSubmit"
          class="px-3.5 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content hover:bg-admin-theme-primary-hover rounded-lg text-sm font-medium transition-colors whitespace-nowrap cursor-pointer"
        >
          {{ $t('Search') }}
        </button>
      </div>
    </div>

    <AdminLoadingState v-if="loading" :message="$t('Loading products...')" />

    <!-- Products Table -->
    <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-admin-theme-border">
        <thead class="bg-admin-theme-bg">
          <tr>
            <!-- Bulk Select All Checkbox -->
            <th class="w-10 px-4 py-3 text-left">
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="w-4 h-4 rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary bg-admin-theme-input-bg cursor-pointer align-middle"
              />
            </th>
            <th class="w-16 px-4 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Image') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Name') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('SKU') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Price') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Stock') }}</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
          <tr 
            v-for="product in products" 
            :key="product.id"
            :class="{ 'bg-admin-theme-primary/5': selectedProductIds.includes(product.id) }"
          >
            <!-- Checkbox Row -->
            <td class="px-4 py-4 whitespace-nowrap">
              <input
                type="checkbox"
                :value="product.id"
                v-model="selectedProductIds"
                class="w-4 h-4 rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary bg-admin-theme-input-bg cursor-pointer align-middle"
              />
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              <div class="w-12 h-12 rounded-lg overflow-hidden bg-admin-theme-base flex items-center justify-center border border-admin-theme-border flex-shrink-0">
                <img
                  v-if="product.thumbnail_url || product.featured_image_url"
                  :src="product.thumbnail_url || product.featured_image_url"
                  :alt="product.name"
                  loading="lazy"
                  decoding="async"
                  class="w-full h-full object-cover polycms-lazy-img"
                  @load="$event.target.classList.add('polycms-loaded')"
                />
                <svg v-else class="w-6 h-6 text-admin-theme-text-muted opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                </svg>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <a 
                :href="product.frontend_url" 
                target="_blank" 
                rel="noopener noreferrer"
                class="text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary hover:underline"
              >
                {{ product.name }}
              </a>
              <div class="text-sm text-admin-theme-text-muted">{{ product.slug }}</div>

              <!-- Linked ProjectHub Project Info -->
              <div v-if="product.project" class="mt-1.5 flex items-center gap-1.5 text-xs">
                <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-500 dark:text-blue-400 border border-blue-500/20 font-medium inline-flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                  </svg>
                  <a :href="product.project.frontend_url || `/projects/${product.project.slug}`" target="_blank" rel="noopener noreferrer" class="hover:underline">
                    {{ product.project.name || product.project.title || product.project.slug }}
                  </a>
                </span>

                <router-link 
                  v-if="product.project.id && $router.hasRoute('admin.project-hub.edit')" 
                  :to="{ name: 'admin.project-hub.edit', params: { id: product.project.id } }"
                  class="text-admin-theme-text-muted hover:text-admin-theme-primary p-0.5 rounded hover:bg-admin-theme-base transition-colors"
                  :title="$t('Edit ProjectHub Project')"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </router-link>
                <a
                  v-else-if="product.project.id"
                  :href="`/admin/project-hub/${product.project.id}/edit`"
                  class="text-admin-theme-text-muted hover:text-admin-theme-primary p-0.5 rounded hover:bg-admin-theme-base transition-colors"
                  :title="$t('Edit ProjectHub Project')"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </a>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
              {{ product.sku || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text font-bold">
              {{ formatCurrency(product.price) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span v-if="filters.status === 'trash'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/60 text-red-800 dark:text-red-200">
                {{ $t('Trash') }}
              </span>
              <span v-else :class="[
                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                product.status === 'published' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                product.status === 'draft' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' :
                'bg-admin-theme-base text-admin-theme-text'
              ]">
                {{ $t(product.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
              {{ product.stock_quantity || 0 }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <template v-if="filters.status === 'trash'">
                <button
                  @click="restoreProduct(product.id)"
                  class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300 font-bold mr-4 cursor-pointer"
                >
                  {{ $t('Restore') }}
                </button>
                <button
                  @click="forceDeleteProduct(product.id)"
                  class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 font-bold cursor-pointer"
                >
                  {{ $t('Delete Permanently') }}
                </button>
              </template>
              <template v-else>
                <router-link
                  :to="{ name: 'admin.products.edit', params: { id: product.id } }"
                  class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary mr-4"
                >
                  {{ $t('Edit') }}
                </router-link>
                <button
                  @click="deleteProduct(product.id)"
                  class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 cursor-pointer"
                >
                  {{ $t('Delete') }}
                </button>
              </template>
            </td>
          </tr>
          <tr v-if="products.length === 0">
            <td colspan="8" class="px-6 py-4 text-center text-admin-theme-text-muted">
              {{ $t('No products found.') }} <router-link v-if="filters.status !== 'trash'" :to="{ name: 'admin.products.create' }" class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary-hover dark:hover:text-admin-theme-primary">{{ $t('Create one') }}</router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
      <div class="text-sm text-admin-theme-text-secondary">
        {{ $t('Showing') }} {{ pagination.from }} {{ $t('to') }} {{ pagination.to }} {{ $t('of') }} {{ pagination.total }} {{ $t('results') }}
      </div>
      <div class="flex space-x-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-bg cursor-pointer"
        >
          {{ $t('Previous') }}
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-admin-theme-bg cursor-pointer"
        >
          {{ $t('Next') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import axios from 'axios';
import AdminLoadingState from '../../components/AdminLoadingState.vue';
import { useRouter } from 'vue-router';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import { useCurrency } from '@/Composables/useCurrency';

const { t } = useTranslation();
const { formatCurrency } = useCurrency();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const dialog = useDialog();

const products = ref<any[]>([]);
const loading = ref(true);
const languages = ref<any[]>([]);
const selectedProductIds = ref<number[]>([]);
const selectedBulkAction = ref<string>('');

const filters = ref({
  search: '',
  status: '',
  locale: '',
});

const statusCounts = ref<Record<string, number>>({
  all: 0,
  published: 0,
  draft: 0,
  archived: 0,
  trash: 0,
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const statusTabs = computed(() => [
  { key: '', label: $t('All') },
  { key: 'published', label: $t('Published') },
  { key: 'draft', label: $t('Draft') },
  { key: 'archived', label: $t('Archived') },
  { key: 'trash', label: $t('Trash') },
]);

const isAllSelected = computed(() => {
  return products.value.length > 0 && selectedProductIds.value.length === products.value.length;
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedProductIds.value = [];
  } else {
    selectedProductIds.value = products.value.map((p) => p.id);
  }
};

const setStatus = (statusKey: string) => {
  if (filters.value.status === statusKey) return;
  filters.value.status = statusKey;
  selectedBulkAction.value = '';
  selectedProductIds.value = [];
  pagination.value.current_page = 1;
  loadProducts();
};

const onSearchSubmit = () => {
  pagination.value.current_page = 1;
  loadProducts();
};

const loadLanguages = async () => {
  try {
    const response = await axios.get('/api/v1/languages');
    if (response.data && response.data.data) {
      languages.value = response.data.data.filter((l: any) => l.is_active);
    }
  } catch (error) {
    console.error('Failed to load languages:', error);
  }
};

const loadProducts = async () => {
  loading.value = true;
  selectedProductIds.value = [];
  try {
    const params: any = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      compact: 1,
    };

    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.status) params.status = filters.value.status;
    if (filters.value.locale) params.locale = filters.value.locale;

    const response = await axios.get('/api/v1/products', { params });
    products.value = response.data.data;
    const meta = response.data.meta;
    pagination.value = {
      current_page: meta.current_page,
      last_page: meta.last_page,
      per_page: meta.per_page,
      total: Array.isArray(meta.total) ? meta.total[0] : meta.total,
      from: meta.from,
      to: meta.to,
    };

    if (meta && meta.counts) {
      statusCounts.value = {
        all: meta.counts.all || 0,
        published: meta.counts.published || 0,
        draft: meta.counts.draft || 0,
        archived: meta.counts.archived || 0,
        trash: meta.counts.trash || 0,
      };
    }
  } catch (error) {
    console.error('Error loading products:', error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page: number) => {
  pagination.value.current_page = page;
  loadProducts();
};

const applyBulkAction = async () => {
  if (!selectedBulkAction.value || selectedProductIds.value.length === 0) return;

  const count = selectedProductIds.value.length;
  let confirmTitle = '';
  let confirmMessage = '';

  if (selectedBulkAction.value === 'trash') {
    confirmTitle = $t('Move to Trash');
    confirmMessage = $t(`Are you sure you want to move ${count} selected product(s) to trash?`);
  } else if (selectedBulkAction.value === 'restore') {
    confirmTitle = $t('Restore Products');
    confirmMessage = $t(`Are you sure you want to restore ${count} selected product(s)?`);
  } else if (selectedBulkAction.value === 'force_delete') {
    confirmTitle = $t('Delete Permanently');
    confirmMessage = $t(`Are you sure you want to permanently delete ${count} selected product(s)? This action cannot be undone.`);
  }

  const confirmed = await dialog.confirm({
    title: confirmTitle,
    message: confirmMessage,
    confirmText: $t('Confirm'),
    cancelButtonText: $t('Cancel'),
    type: 'danger',
  });

  if (!confirmed) return;

  try {
    if (selectedBulkAction.value === 'trash') {
      await axios.post('/api/v1/products/bulk-delete', { ids: selectedProductIds.value });
      dialog.success($t('Selected products moved to trash successfully'));
    } else if (selectedBulkAction.value === 'restore') {
      await axios.post('/api/v1/products/bulk-restore', { ids: selectedProductIds.value });
      dialog.success($t('Selected products restored successfully'));
    } else if (selectedBulkAction.value === 'force_delete') {
      await axios.post('/api/v1/products/bulk-force-delete', { ids: selectedProductIds.value });
      dialog.success($t('Selected products permanently deleted'));
    }
    selectedBulkAction.value = '';
    selectedProductIds.value = [];
    loadProducts();
  } catch (error: any) {
    console.error('Error performing bulk action:', error);
    const message = error.response?.data?.message || $t('Failed to perform bulk action');
    dialog.error(message);
  }
};

const deleteProduct = async (id: number) => {
  const confirmed = await dialog.confirm({
    title: $t('Move to Trash'),
    message: $t('Are you sure you want to move this product to trash?'),
    confirmText: $t('Move to Trash'),
    cancelButtonText: $t('Cancel'),
    type: 'danger',
  });

  if (!confirmed) return;

  try {
    await axios.delete(`/api/v1/products/${id}`);
    loadProducts();
    dialog.success($t('Product moved to trash successfully'));
  } catch (error: any) {
    console.error('Error deleting product:', error);
    const message = error.response?.data?.message || $t('Failed to delete product');
    dialog.error(message);
  }
};

const restoreProduct = async (id: number) => {
  try {
    await axios.post(`/api/v1/products/${id}/restore`);
    loadProducts();
    dialog.success($t('Product restored successfully'));
  } catch (error: any) {
    console.error('Error restoring product:', error);
    const message = error.response?.data?.message || $t('Failed to restore product');
    dialog.error(message);
  }
};

const forceDeleteProduct = async (id: number) => {
  const confirmed = await dialog.confirm({
    title: $t('Delete Permanently'),
    message: $t('Are you sure you want to permanently delete this product? This action cannot be undone.'),
    confirmText: $t('Delete Permanently'),
    cancelButtonText: $t('Cancel'),
    type: 'danger',
  });

  if (!confirmed) return;

  try {
    await axios.delete(`/api/v1/products/${id}/force-delete`);
    loadProducts();
    dialog.success($t('Product permanently deleted'));
  } catch (error: any) {
    console.error('Error permanently deleting product:', error);
    const message = error.response?.data?.message || $t('Failed to permanently delete product');
    dialog.error(message);
  }
};

onMounted(() => {
  loadLanguages();
  loadProducts();
});
</script>
