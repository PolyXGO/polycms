<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h1 class="text-2xl font-bold text-admin-theme-text">{{ $t('Pages') }}</h1>
      <router-link
        :to="{ name: 'admin.pages.create' }"
        class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors font-medium text-sm"
      >
        + {{ $t('New Page') }}
      </router-link>
    </div>

    <!-- Status Tabs (WordPress Style - Fixed Height & Padding, No Size Shifting) -->
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
          :disabled="!selectedBulkAction || selectedPageIds.length === 0"
          class="px-3.5 py-1.5 bg-admin-theme-surface border border-admin-theme-border text-admin-theme-text hover:bg-admin-theme-base rounded-lg text-sm font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
        >
          {{ $t('Apply') }}
        </button>

        <span class="text-admin-theme-border hidden sm:inline">|</span>

        <!-- Language Filter -->
        <select
          v-model="filters.locale"
          @change="loadPages"
          class="px-3 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm cursor-pointer min-w-[140px]"
        >
          <option value="">{{ $t('All Languages') || 'All Languages' }}</option>
          <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.name }}</option>
        </select>
      </div>

      <!-- Right: Search Input & Search Button -->
      <div class="flex items-center gap-2">
        <input
          v-model="filters.search"
          type="text"
          :placeholder="$t('Search pages...')"
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

    <AdminLoadingState v-if="loading" :message="$t('Loading pages...')" />

    <!-- Pages Table -->
    <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-admin-theme-border">
        <thead class="bg-admin-theme-base">
          <tr>
            <!-- Checkbox Header -->
            <th class="w-10 px-4 py-3 text-left">
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="w-4 h-4 rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary bg-admin-theme-input-bg cursor-pointer align-middle"
              />
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Title') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Author') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Published') }}</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-admin-theme-text-secondary uppercase tracking-wider">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
          <tr 
            v-for="page in pages" 
            :key="page.id"
            :class="{ 'bg-admin-theme-primary/5': selectedPageIds.includes(page.id) }"
          >
            <!-- Checkbox Row -->
            <td class="px-4 py-4 whitespace-nowrap">
              <input
                type="checkbox"
                :value="page.id"
                v-model="selectedPageIds"
                class="w-4 h-4 rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary bg-admin-theme-input-bg cursor-pointer align-middle"
              />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <a 
                :href="page.frontend_url || getFrontendUrl(page.slug)" 
                target="_blank" 
                rel="noopener noreferrer"
                class="text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary hover:underline"
              >
                {{ page.title }}
              </a>
              <div class="text-sm text-admin-theme-text-muted">{{ page.slug }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span v-if="filters.status === 'trash'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/60 text-red-800 dark:text-red-200">
                {{ $t('Trash') }}
              </span>
              <span v-else :class="[
                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                page.status === 'published' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                page.status === 'draft' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' :
                'bg-admin-theme-base text-admin-theme-text'
              ]">
                {{ $t(page.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
              {{ page.user?.name || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
              {{ page.published_at ? new Date(page.published_at).toLocaleDateString() : '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <template v-if="filters.status === 'trash'">
                <button
                  @click="restorePage(page.id)"
                  class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300 font-bold mr-4 cursor-pointer"
                >
                  {{ $t('Restore') }}
                </button>
                <button
                  @click="forceDeletePage(page.id)"
                  class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 font-bold cursor-pointer"
                >
                  {{ $t('Delete Permanently') }}
                </button>
              </template>
              <template v-else>
                <router-link
                  :to="{ name: 'admin.pages.edit', params: { id: page.id } }"
                  class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary mr-4"
                >
                  {{ $t('Edit') }}
                </router-link>
                <button
                  @click="deletePage(page.id)"
                  class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 cursor-pointer"
                >
                  {{ $t('Delete') }}
                </button>
              </template>
            </td>
          </tr>
          <tr v-if="pages.length === 0">
            <td colspan="6" class="px-6 py-4 text-center text-admin-theme-text-muted">
              {{ $t('No pages found.') }} 
              <router-link v-if="filters.status !== 'trash'" :to="{ name: 'admin.pages.create' }" class="text-admin-theme-primary hover:underline">{{ $t('Create one') }}</router-link>
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
          class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer"
        >
          {{ $t('Previous') }}
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="px-4 py-2 border border-admin-theme-border rounded-lg disabled:opacity-50 bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer"
        >
          {{ $t('Next') || 'Next' }}
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

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const dialog = useDialog();

const pages = ref<any[]>([]);
const loading = ref(true);
const languages = ref<any[]>([]);
const selectedPageIds = ref<number[]>([]);
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
  { key: '', label: $t('All') || 'All' },
  { key: 'published', label: $t('Published') || 'Published' },
  { key: 'draft', label: $t('Draft') || 'Draft' },
  { key: 'archived', label: $t('Archived') || 'Archived' },
  { key: 'trash', label: $t('Trash') || 'Trash' },
]);

const isAllSelected = computed(() => {
  return pages.value.length > 0 && selectedPageIds.value.length === pages.value.length;
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedPageIds.value = [];
  } else {
    selectedPageIds.value = pages.value.map((p) => p.id);
  }
};

const setStatus = (statusKey: string) => {
  if (filters.value.status === statusKey) return;
  filters.value.status = statusKey;
  selectedBulkAction.value = '';
  selectedPageIds.value = [];
  pagination.value.current_page = 1;
  loadPages();
};

const onSearchSubmit = () => {
  pagination.value.current_page = 1;
  loadPages();
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

const loadPages = async () => {
  loading.value = true;
  selectedPageIds.value = [];
  try {
    const params: any = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      compact: 1,
      type: 'page', // Pages are Post records with type='page'
    };

    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.status) params.status = filters.value.status;
    if (filters.value.locale) params.locale = filters.value.locale;

    const response = await axios.get('/api/v1/posts', { params });
    pages.value = response.data.data;
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
    console.error('Error loading pages:', error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page: number) => {
  pagination.value.current_page = page;
  loadPages();
};

const getFrontendUrl = (slug: string): string => {
  if (!slug) return '#';
  const baseUrl = window.location.origin;
  return `${baseUrl}/${slug}`;
};

const applyBulkAction = async () => {
  if (!selectedBulkAction.value || selectedPageIds.value.length === 0) return;

  const count = selectedPageIds.value.length;
  let confirmTitle = '';
  let confirmMessage = '';

  if (selectedBulkAction.value === 'trash') {
    confirmTitle = $t('Move to Trash') || 'Move to Trash';
    confirmMessage = $t(`Are you sure you want to move ${count} selected page(s) to trash?`);
  } else if (selectedBulkAction.value === 'restore') {
    confirmTitle = $t('Restore Pages') || 'Restore Pages';
    confirmMessage = $t(`Are you sure you want to restore ${count} selected page(s)?`);
  } else if (selectedBulkAction.value === 'force_delete') {
    confirmTitle = $t('Delete Permanently') || 'Delete Permanently';
    confirmMessage = $t(`Are you sure you want to permanently delete ${count} selected page(s)? This action cannot be undone.`);
  }

  const confirmed = await dialog.confirm({
    title: confirmTitle,
    message: confirmMessage,
    confirmText: $t('Confirm') || 'Confirm',
    cancelText: $t('Cancel') || 'Cancel',
    type: 'danger',
  });

  if (!confirmed) return;

  try {
    if (selectedBulkAction.value === 'trash') {
      await axios.post('/api/v1/posts/bulk-delete', { ids: selectedPageIds.value });
      dialog.success($t('Selected pages moved to trash successfully') || 'Selected pages moved to trash successfully');
    } else if (selectedBulkAction.value === 'restore') {
      await axios.post('/api/v1/posts/bulk-restore', { ids: selectedPageIds.value });
      dialog.success($t('Selected pages restored successfully') || 'Selected pages restored successfully');
    } else if (selectedBulkAction.value === 'force_delete') {
      await axios.post('/api/v1/posts/bulk-force-delete', { ids: selectedPageIds.value });
      dialog.success($t('Selected pages permanently deleted') || 'Selected pages permanently deleted');
    }
    selectedBulkAction.value = '';
    selectedPageIds.value = [];
    loadPages();
  } catch (error: any) {
    console.error('Error performing bulk action:', error);
    const message = error.response?.data?.message || 'Failed to perform bulk action';
    dialog.error(message);
  }
};

const deletePage = async (id: number) => {
  const confirmed = await dialog.confirm({
    title: $t('Move to Trash') || 'Move to Trash',
    message: $t('Are you sure you want to move this page to trash?') || 'Are you sure you want to move this page to trash?',
    confirmText: $t('Move to Trash') || 'Move to Trash',
    cancelText: $t('Cancel') || 'Cancel',
    type: 'danger',
  });

  if (!confirmed) return;

  try {
    await axios.delete(`/api/v1/posts/${id}`);
    loadPages();
    dialog.success($t('Page moved to trash successfully') || 'Page moved to trash successfully');
  } catch (error: any) {
    console.error('Error deleting page:', error);
    const message = error.response?.data?.message || 'Failed to delete page';
    dialog.error(message);
  }
};

const restorePage = async (id: number) => {
  try {
    await axios.post(`/api/v1/posts/${id}/restore`);
    loadPages();
    dialog.success($t('Page restored successfully') || 'Page restored successfully');
  } catch (error: any) {
    console.error('Error restoring page:', error);
    const message = error.response?.data?.message || 'Failed to restore page';
    dialog.error(message);
  }
};

const forceDeletePage = async (id: number) => {
  const confirmed = await dialog.confirm({
    title: $t('Delete Permanently') || 'Delete Permanently',
    message: $t('Are you sure you want to permanently delete this page? This action cannot be undone.') || 'Are you sure you want to permanently delete this page? This action cannot be undone.',
    confirmText: $t('Delete Permanently') || 'Delete Permanently',
    cancelText: $t('Cancel') || 'Cancel',
    type: 'danger',
  });

  if (!confirmed) return;

  try {
    await axios.delete(`/api/v1/posts/${id}/force-delete`);
    loadPages();
    dialog.success($t('Page permanently deleted') || 'Page permanently deleted');
  } catch (error: any) {
    console.error('Error permanently deleting page:', error);
    const message = error.response?.data?.message || 'Failed to permanently delete page';
    dialog.error(message);
  }
};

onMounted(() => {
  loadLanguages();
  loadPages();
});
</script>
