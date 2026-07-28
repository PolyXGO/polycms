<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Licenses') }}</h1>
    </div>

    <!-- Filters -->
    <div class="bg-admin-theme-surface rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input
          v-model="filters.search"
          type="text"
          :placeholder="t('Search by key, product or user...')"
          class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted"
          @input="debouncedLoad"
        />
        <select
          v-model="filters.status"
          @change="loadLicenses"
          class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
        >
          <option value="">{{ t('All Status') }}</option>
          <option value="active">{{ t('Active') }}</option>
          <option value="revoked">{{ t('Revoked') }}</option>
          <option value="suspended">{{ t('Suspended') }}</option>
        </select>
      </div>
    </div>

    <AdminLoadingState v-if="loading" :message="t('Loading licenses...')" />

    <!-- Licenses Table -->
    <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-admin-theme-border">
        <thead class="bg-admin-theme-base">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Product') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('License Key') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Activations') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Status') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('User') }}</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
          <tr v-for="license in licenses" :key="license.id">
            <!-- Product Column -->
            <td class="px-6 py-4 text-sm text-admin-theme-text-muted">
              <a
                v-if="license.subscription?.product?.slug"
                :href="getProductUrl(license.subscription.product.slug)"
                target="_blank"
                class="block font-semibold text-admin-theme-primary hover:underline"
              >
                {{ license.subscription?.product?.name || '-' }}
              </a>
              <span v-else class="block font-semibold text-admin-theme-text">
                {{ license.subscription?.product?.name || '-' }}
              </span>
              
              <router-link
                v-if="license.order"
                :to="{ name: 'admin.orders.show', params: { id: license.order.id } }"
                class="inline-flex items-center gap-1 mt-1 text-xs text-gray-500 hover:text-admin-theme-primary transition-colors font-normal"
              >
                <i class="fas fa-shopping-bag text-[10px]"></i>
                {{ t('Order') }}: #{{ license.order.code }}
              </router-link>
            </td>

            <!-- License Key Column -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-admin-theme-primary">
              <div class="flex items-center gap-2">
                <span>{{ license.license_key }}</span>
                <button
                  @click="copyToClipboard(license.license_key)"
                  type="button"
                  class="text-gray-400 hover:text-admin-theme-primary transition-colors p-1"
                  :title="t('Copy Key')"
                >
                  <i class="far text-xs" :class="copiedKey === license.license_key ? 'fa-check-circle text-green-500' : 'fa-copy'"></i>
                </button>
              </div>
            </td>

            <!-- Activations Column -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
              <span class="font-semibold text-admin-theme-text">{{ license.activation_count }}</span> / {{ license.max_activations }}
            </td>

            <!-- Status Column -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(license.status)" class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full capitalize">
                {{ getStatusLabel(license.status) }}
              </span>
            </td>

            <!-- User Column -->
            <td class="px-6 py-4 text-sm text-admin-theme-text-muted">
              <div v-if="license.subscription?.user">
                <span class="font-medium text-admin-theme-text">{{ license.subscription.user.name }}</span>
                <div class="text-xs text-gray-500">{{ license.subscription.user.email }}</div>
              </div>
              <span v-else>N/A</span>
            </td>

            <!-- Actions Column (Manage & Download) -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
              <div class="flex items-center justify-end gap-2">
                <button 
                  @click="openManageModal(license)" 
                  type="button" 
                  class="inline-flex items-center gap-1.5 text-xs font-semibold bg-admin-theme-primary text-admin-theme-primary-content hover:bg-admin-theme-primary-hover px-3 py-1.5 rounded-lg transition-colors cursor-pointer shadow-sm"
                  title="Manage Max Activations & Domains"
                >
                  <i class="fas fa-cog text-[11px]"></i>
                  Edit / Manage
                </button>
                <button 
                  v-if="license.subscription?.product?.releases && license.subscription.product.releases.length > 0"
                  @click="openDownloadsModal(license)" 
                  type="button" 
                  class="inline-flex items-center gap-1.5 text-xs font-semibold bg-admin-theme-surface hover:bg-black/5 dark:hover:bg-white/5 text-admin-theme-text px-2.5 py-1.5 rounded-lg border border-admin-theme-border transition-colors cursor-pointer"
                >
                  <i class="fas fa-download"></i>
                  {{ t('Downloads') }}
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="licenses.length === 0">
            <td colspan="6" class="px-6 py-4 text-center text-admin-theme-text-muted">
              {{ t('No licenses found.') }}
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

    <!-- Edit & Manage License Modal -->
    <div v-if="showManageModal && editingLicense" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeManageModal"></div>

      <div class="bg-white dark:bg-[#151515] border border-gray-200 dark:border-zinc-850 rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden relative z-10 transition-all">
        <!-- Header -->
        <div class="p-6 border-b border-gray-150 dark:border-zinc-800 flex justify-between items-center bg-gray-50/50 dark:bg-zinc-900/50">
          <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <i class="fas fa-key text-admin-theme-primary"></i>
              Manage License & Activations
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">
              Key: {{ editingLicense.license_key }}
            </p>
          </div>
          <button @click="closeManageModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-850 rounded-lg transition-all">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
          <!-- License Settings Card -->
          <div class="bg-admin-theme-base/40 rounded-xl p-4 border border-admin-theme-border space-y-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-admin-theme-primary">License Settings</h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Max Activations Input -->
              <div>
                <label class="block text-xs font-semibold text-admin-theme-text-secondary mb-1">
                  Max Activations (Seats)
                </label>
                <input
                  v-model.number="editForm.max_activations"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-admin-theme-border rounded-lg text-sm bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
                />
                <p class="text-[11px] text-admin-theme-text-muted mt-1">Number of domains/devices allowed.</p>
              </div>

              <!-- Status Select -->
              <div>
                <label class="block text-xs font-semibold text-admin-theme-text-secondary mb-1">
                  License Status
                </label>
                <select
                  v-model="editForm.status"
                  class="w-full px-3 py-2 border border-admin-theme-border rounded-lg text-sm bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
                >
                  <option value="active">Active</option>
                  <option value="suspended">Suspended</option>
                  <option value="revoked">Revoked</option>
                </select>
                <p class="text-[11px] text-admin-theme-text-muted mt-1">Control license validity.</p>
              </div>
            </div>

            <div class="flex justify-end">
              <button
                type="button"
                @click="saveLicenseChanges"
                :disabled="savingLicense"
                class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content hover:bg-admin-theme-primary-hover rounded-lg text-xs font-bold transition-colors shadow-sm disabled:opacity-50"
              >
                {{ savingLicense ? 'Saving...' : 'Save License Settings' }}
              </button>
            </div>
          </div>

          <!-- Registered Activations / Domains Section -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <h4 class="text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary">
                Active Domains & Devices ({{ (editingLicense.activations || []).length }} / {{ editForm.max_activations }})
              </h4>
            </div>

            <div v-if="loadingDetails" class="text-center py-6 text-xs text-admin-theme-text-muted">
              Loading activation details...
            </div>

            <div v-else-if="!editingLicense.activations || editingLicense.activations.length === 0" class="text-center py-6 border border-dashed border-admin-theme-border rounded-xl text-xs text-admin-theme-text-muted">
              No active domains or devices registered for this license yet.
            </div>

            <div v-else class="border border-admin-theme-border rounded-xl overflow-hidden divide-y divide-admin-theme-border bg-admin-theme-surface">
              <div
                v-for="act in editingLicense.activations"
                :key="act.id"
                class="p-3 flex items-center justify-between hover:bg-admin-theme-input-bg/40 transition-colors"
              >
                <div>
                  <div class="text-sm font-semibold text-admin-theme-text font-mono">
                    {{ act.domain || act.hwid || 'Unknown Domain' }}
                  </div>
                  <div class="text-xs text-admin-theme-text-muted flex gap-3 mt-0.5">
                    <span v-if="act.ip">IP: {{ act.ip }}</span>
                    <span>Activated: {{ new Date(act.created_at).toLocaleDateString() }}</span>
                  </div>
                </div>

                <button
                  type="button"
                  @click="deleteActivation(act)"
                  class="px-2.5 py-1 text-xs font-semibold text-red-600 dark:text-red-400 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                >
                  Deactivate
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-150 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50 flex justify-end">
          <button @click="closeManageModal" type="button" class="px-4 py-2 text-xs font-semibold text-admin-theme-text border border-admin-theme-border rounded-lg bg-admin-theme-input-bg hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Downloads Modal -->
    <div v-if="showModal && activeLicense" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

      <div class="bg-white dark:bg-[#151515] border border-gray-200 dark:border-zinc-850 rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden relative z-10 transition-all transform scale-100">
        <!-- Header -->
        <div class="p-6 border-b border-gray-150 dark:border-zinc-800 flex justify-between items-center bg-gray-50/50 dark:bg-zinc-900/50">
          <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
              {{ activeLicense.subscription?.product?.name }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">
              {{ t('License') }}: {{ activeLicense.license_key }}
            </p>
          </div>
          <button @click="closeModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1.5 hover:bg-gray-100 dark:hover:bg-zinc-850 rounded-lg transition-all cursor-pointer">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 max-h-[60vh] overflow-y-auto space-y-6">
          <div class="text-xs font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2 uppercase tracking-wider mb-2">
            <i class="fas fa-history text-indigo-500"></i>
            {{ t('Available Releases & Source Code (Admin Mode)') }}
          </div>

          <div v-if="activeLicense.subscription?.product?.releases && activeLicense.subscription.product.releases.length > 0" class="space-y-4">
            <div v-for="release in activeLicense.subscription.product.releases" :key="'release-modal-' + release.id" class="p-4 border border-gray-100 dark:border-zinc-800 rounded-xl bg-gray-50/40 dark:bg-zinc-900/20 hover:border-indigo-100 dark:hover:border-indigo-900/40 transition-colors">
              <div class="flex items-start justify-between gap-4 flex-wrap sm:flex-nowrap">
                <div class="space-y-1">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900">
                      {{ release.version }}
                    </span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                      {{ release.title || t('Release') }}
                    </span>
                    <span v-if="release.is_prerelease" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-900">
                      Pre-release
                    </span>
                  </div>
                  <p class="text-[11px] text-gray-500 dark:text-gray-400">
                    {{ t('Released on') }}: {{ new Date(release.released_at).toLocaleDateString() }}
                  </p>
                  <p v-if="release.summary" class="text-xs text-gray-600 dark:text-gray-400 mt-2 whitespace-pre-line leading-relaxed">
                    {{ release.summary }}
                  </p>
                </div>
                <div class="flex sm:flex-col gap-2 shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                  <a 
                    v-if="release.download_url"
                    :href="release.download_url" 
                    target="_blank"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap"
                  >
                    <i class="fas fa-file-archive text-[10px]"></i>
                    {{ t('Download Paid') }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLoadingState from '../../components/AdminLoadingState.vue';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '@polycms';

const licenses = ref<any[]>([]);
const loading = ref(true);
const filters = ref({ search: '', status: '' });
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
const copiedKey = ref('');

const showModal = ref(false);
const activeLicense = ref<any>(null);

const showManageModal = ref(false);
const editingLicense = ref<any>(null);
const loadingDetails = ref(false);
const savingLicense = ref(false);
const editForm = ref({ max_activations: 1, status: 'active' });

const { t } = useTranslation();
const dialog = useDialog();

let debounceTimeout: number | undefined;

const debouncedLoad = () => {
  window.clearTimeout(debounceTimeout);
  debounceTimeout = window.setTimeout(() => {
    pagination.value.current_page = 1;
    loadLicenses();
  }, 300);
};

const loadLicenses = async () => {
  loading.value = true;
  try {
    const params = {
      ...filters.value,
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
    };
    const response = await axios.get('/api/v1/licenses', { params });
    licenses.value = response.data.data ?? [];
    pagination.value = { ...pagination.value, ...response.data };
  } catch (e) {
    console.error('Error loading licenses', e);
  } finally {
    loading.value = false;
  }
};

const openManageModal = async (license: any) => {
  editingLicense.value = license;
  editForm.value = {
    max_activations: license.max_activations || 1,
    status: license.status || 'active',
  };
  showManageModal.value = true;
  loadingDetails.value = true;

  try {
    const res = await axios.get(`/api/v1/licenses/${license.id}`);
    if (res.data) {
      editingLicense.value = res.data;
      editForm.value = {
        max_activations: res.data.max_activations,
        status: res.data.status,
      };
    }
  } catch (e) {
    console.error('Failed to load license details', e);
  } finally {
    loadingDetails.value = false;
  }
};

const closeManageModal = () => {
  showManageModal.value = false;
  editingLicense.value = null;
};

const saveLicenseChanges = async () => {
  if (!editingLicense.value) return;
  savingLicense.value = true;
  try {
    const res = await axios.put(`/api/v1/licenses/${editingLicense.value.id}`, editForm.value);
    if (res.data?.success && res.data?.data) {
      editingLicense.value = res.data.data;
    }
    dialog.success('License settings updated successfully');
    await loadLicenses();
  } catch (e: any) {
    console.error('Failed to update license', e);
    dialog.error(e?.response?.data?.message || 'Failed to update license');
  } finally {
    savingLicense.value = false;
  }
};

const deleteActivation = async (act: any) => {
  if (!editingLicense.value) return;
  const confirmed = await dialog.confirm({
    title: 'Deactivate Domain',
    message: `Are you sure you want to deactivate domain "${act.domain || act.hwid || 'this activation'}"?`,
    confirmText: 'Deactivate',
    cancelText: 'Cancel',
    type: 'danger',
  });

  if (!confirmed) return;

  try {
    const res = await axios.delete(`/api/v1/licenses/${editingLicense.value.id}/activations/${act.id}`);
    if (res.data?.success && res.data?.data) {
      editingLicense.value = res.data.data;
    } else {
      editingLicense.value.activations = (editingLicense.value.activations || []).filter((a: any) => a.id !== act.id);
      editingLicense.value.activation_count = editingLicense.value.activations.length;
    }
    dialog.success('Activation deactivated successfully');
    await loadLicenses();
  } catch (e: any) {
    console.error('Failed to delete activation', e);
    dialog.error('Failed to deactivate domain.');
  }
};

const copyToClipboard = async (text: string) => {
  try {
    await navigator.clipboard.writeText(text);
    copiedKey.value = text;
    setTimeout(() => {
      copiedKey.value = '';
    }, 2000);
  } catch (err) {
    console.error('Failed to copy text: ', err);
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

const openDownloadsModal = (license: any) => {
  activeLicense.value = license;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  activeLicense.value = null;
};

const changePage = (page: number) => {
  pagination.value.current_page = page;
  loadLicenses();
};

const getStatusClass = (status: string) => {
  const map: Record<string, string> = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    revoked: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    suspended: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
  };
  return map[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-850 dark:text-gray-400';
};

const getStatusLabel = (status: string) => {
  const map: Record<string, string> = {
    active: t('Active'),
    revoked: t('Revoked'),
    suspended: t('Suspended'),
  };
  return map[status] || status;
};

onMounted(loadLicenses);
</script>
