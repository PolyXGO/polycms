<template>
  <div class="submissions-container">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Submissions') }}</h1>
        <p class="text-sm text-admin-theme-text-secondary mt-1">{{ t('Review user contacts, inquiries, and newsletter signups') }}</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-admin-theme-surface rounded-xl border border-admin-theme-border p-4 mb-6 shadow-sm">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Search') }}</label>
          <input
            v-model="filters.search"
            type="text"
            :placeholder="t('Search name, email, data...')"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:border-blue-500"
            @input="debouncedLoad"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Form Type') }}</label>
          <select
            v-model="filters.type"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:border-blue-500"
            @change="loadSubmissions"
          >
            <option value="">{{ t('All Types') }}</option>
            <option value="contact">{{ t('Contact') }}</option>
            <option value="newsletter">{{ t('Newsletter') }}</option>
            <option value="feedback">{{ t('Feedback') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Status') }}</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:border-blue-500"
            @change="loadSubmissions"
          >
            <option value="">{{ t('All Statuses') }}</option>
            <option value="unread">{{ t('Unread') }}</option>
            <option value="read">{{ t('Read') }}</option>
          </select>
        </div>
        <div class="flex items-end justify-end">
          <button
            @click="clearFilters"
            class="w-full md:w-auto px-4 py-2 border border-admin-theme-border text-admin-theme-text rounded-lg hover:bg-admin-theme-base/10 text-sm font-medium transition-colors"
          >
            {{ t('Reset Filters') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <AdminLoadingState v-if="loading" :message="t('Loading submissions...')" />

    <!-- Submissions Table -->
    <div v-else class="bg-admin-theme-surface rounded-xl border border-admin-theme-border shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-admin-theme-border">
          <thead class="bg-admin-theme-base/50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Date') }}</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Contact Details') }}</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Source Form') }}</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Snippet') }}</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Status') }}</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Actions') }}</th>
            </tr>
          </thead>
          <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
            <tr
              v-for="sub in submissions"
              :key="sub.id"
              class="hover:bg-admin-theme-base/10 transition-colors"
              :class="{ 'font-semibold bg-blue-50/10': sub.status === 'unread' }"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-secondary">
                {{ formatDate(sub.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-admin-theme-text">{{ sub.name || t('Anonymous') }}</div>
                <div class="text-xs text-admin-theme-text-muted">{{ sub.email || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
                <div>{{ sub.form?.name || t('Direct API') }}</div>
                <div class="text-xs uppercase px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 inline-block font-mono mt-1 scale-90 origin-left">
                  {{ sub.type }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-admin-theme-text-muted max-w-xs truncate">
                {{ getDataSnippet(sub.data) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  @click="toggleStatus(sub)"
                  class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer transition-colors"
                  :class="sub.status === 'unread' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-400'"
                  :title="sub.status === 'unread' ? t('Mark as read') : t('Mark as unread')"
                >
                  {{ sub.status === 'unread' ? t('Unread') : t('Read') }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                <button
                  @click="openDetails(sub)"
                  class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  {{ t('View') }}
                </button>
                <button
                  @click="deleteSubmission(sub.id)"
                  class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                  :disabled="deleting === sub.id"
                >
                  {{ deleting === sub.id ? t('Deleting...') : t('Delete') }}
                </button>
              </td>
            </tr>
            <tr v-if="submissions.length === 0">
              <td colspan="6" class="px-6 py-8 text-center text-admin-theme-text-secondary">
                <div class="flex flex-col items-center justify-center gap-2">
                  <svg class="w-8 h-8 text-admin-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <span>{{ t('No submissions found.') }}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 bg-admin-theme-base/20 border-t border-admin-theme-border flex items-center justify-between">
        <div class="text-xs text-admin-theme-text-secondary">
          Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of {{ pagination.total }} entries
        </div>
        <div class="flex gap-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1 border border-admin-theme-border rounded bg-admin-theme-surface text-admin-theme-text hover:bg-admin-theme-base/10 text-xs font-medium disabled:opacity-50"
          >
            {{ t('Previous') }}
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 border border-admin-theme-border rounded bg-admin-theme-surface text-admin-theme-text hover:bg-admin-theme-base/10 text-xs font-medium disabled:opacity-50"
          >
            {{ t('Next') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Details View Modal -->
    <div v-if="selectedSub" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
      <div class="bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden animate-slide-up">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-admin-theme-base border-b border-admin-theme-border flex justify-between items-center">
          <div>
            <h3 class="text-lg font-bold text-admin-theme-text">{{ t('Submission Details') }}</h3>
            <p class="text-xs text-admin-theme-text-secondary mt-0.5">{{ formatDate(selectedSub.created_at) }}</p>
          </div>
          <button @click="closeDetails" class="text-admin-theme-text-secondary hover:text-admin-theme-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Content -->
        <div class="px-6 py-5 overflow-y-auto space-y-4 flex-1">
          <!-- Metadata block -->
          <div class="grid grid-cols-2 gap-4 bg-admin-theme-base/20 p-4 rounded-xl border border-admin-theme-border text-sm">
            <div>
              <div class="text-xs font-semibold text-admin-theme-text-muted uppercase">{{ t('Sender Name') }}</div>
              <div class="font-medium text-admin-theme-text mt-0.5">{{ selectedSub.name || t('Anonymous') }}</div>
            </div>
            <div>
              <div class="text-xs font-semibold text-admin-theme-text-muted uppercase">{{ t('Email Address') }}</div>
              <div class="font-medium text-admin-theme-text mt-0.5">{{ selectedSub.email || '-' }}</div>
            </div>
            <div>
              <div class="text-xs font-semibold text-admin-theme-text-muted uppercase">{{ t('Source Form') }}</div>
              <div class="font-medium text-admin-theme-text mt-0.5">{{ selectedSub.form?.name || t('Direct API') }}</div>
            </div>
            <div>
              <div class="text-xs font-semibold text-admin-theme-text-muted uppercase">{{ t('Submission Type') }}</div>
              <div class="font-medium text-admin-theme-text mt-0.5 capitalize">{{ selectedSub.type }}</div>
            </div>
          </div>

          <!-- Submitted Fields data list -->
          <div class="space-y-3">
            <h4 class="text-sm font-bold text-admin-theme-text-secondary border-b border-admin-theme-border pb-1">
              {{ t('Submitted Data') }}
            </h4>
            <div class="space-y-3">
              <div
                v-for="(val, key) in filterMetadata(selectedSub.data)"
                :key="key"
                class="flex flex-col gap-1 border-b border-admin-theme-border/30 pb-2 text-sm"
              >
                <span class="text-xs font-semibold text-admin-theme-text-muted uppercase capitalize">
                  {{ key.replace('_', ' ') }}
                </span>
                <span v-if="val === true || val === '1'" class="text-admin-theme-text font-medium">✓ {{ t('Yes') }}</span>
                <span v-else-if="val === false || val === '0'" class="text-admin-theme-text font-medium">✗ {{ t('No') }}</span>
                <span v-else class="text-admin-theme-text font-medium whitespace-pre-line">{{ val }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-admin-theme-base/30 border-t border-admin-theme-border flex justify-between items-center">
          <button
            @click="toggleStatus(selectedSub)"
            class="px-4 py-2 border border-admin-theme-border text-admin-theme-text rounded-lg hover:bg-admin-theme-base/10 text-sm font-medium transition-colors"
          >
            {{ selectedSub.status === 'unread' ? t('Mark as Read') : t('Mark as Unread') }}
          </button>
          <button
            @click="closeDetails"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all text-sm font-semibold shadow-sm"
          >
            {{ t('Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';
import AdminLoadingState from '../../components/AdminLoadingState.vue';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();

const submissions = ref<any[]>([]);
const loading = ref(true);
const deleting = ref<number | null>(null);
const selectedSub = ref<any | null>(null);

const filters = reactive({
  search: '',
  type: '',
  status: '',
  page: 1
});

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
});

let debounceTimeout: any = null;
const debouncedLoad = () => {
  if (debounceTimeout) clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    filters.page = 1;
    loadSubmissions();
  }, 350);
};

const loadSubmissions = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/contacts/submissions', {
      params: {
        search: filters.search,
        type: filters.type,
        status: filters.status,
        page: filters.page
      }
    });
    submissions.value = response.data.data;
    pagination.current_page = response.data.current_page;
    pagination.last_page = response.data.last_page;
    pagination.per_page = response.data.per_page;
    pagination.total = response.data.total;
  } catch (e) {
    console.error(e);
    dialog.error(t('Failed to load submissions'));
  } finally {
    loading.value = false;
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.type = '';
  filters.status = '';
  filters.page = 1;
  loadSubmissions();
};

const changePage = (page: number) => {
  filters.page = page;
  loadSubmissions();
};

const toggleStatus = async (sub: any) => {
  const oldStatus = sub.status;
  const newStatus = sub.status === 'unread' ? 'read' : 'unread';
  try {
    const response = await axios.put(`/api/v1/contacts/submissions/${sub.id}/status`, {
      status: newStatus
    });
    sub.status = response.data.status;
    
    if (oldStatus !== sub.status) {
      const change = sub.status === 'read' ? -1 : 1;
      window.dispatchEvent(new CustomEvent('admin-menu-badge-update', {
        detail: { key: 'contacts', change }
      }));
    }
  } catch (e) {
    console.error(e);
    dialog.error(t('Failed to update submission status'));
  }
};

const deleteSubmission = async (id: number) => {
  const confirmed = await dialog.confirm({
    title: t('Delete Submission'),
    message: t('Are you sure you want to delete this form submission? This action cannot be undone.')
  });
  
  if (!confirmed) return;

  const targetSub = submissions.value.find(s => s.id === id);
  const wasUnread = targetSub && targetSub.status === 'unread';

  deleting.value = id;
  try {
    await axios.delete(`/api/v1/contacts/submissions/${id}`);
    dialog.success(t('Submission deleted successfully'));
    if (selectedSub.value?.id === id) {
      closeDetails();
    }
    
    if (wasUnread) {
      window.dispatchEvent(new CustomEvent('admin-menu-badge-update', {
        detail: { key: 'contacts', change: -1 }
      }));
    }
    loadSubmissions();
  } catch (e) {
    console.error(e);
    dialog.error(t('Failed to delete submission'));
  } finally {
    deleting.value = null;
  }
};

const openDetails = async (sub: any) => {
  selectedSub.value = sub;
};

const closeDetails = () => {
  selectedSub.value = null;
};

// Formats the DB date string nicely
const formatDate = (dateStr: string) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString();
};

// Extract metadata that shouldn't show in custom fields display
const filterMetadata = (data: any) => {
  if (!data) return {};
  const clean: Record<string, any> = {};
  for (const key in data) {
    if (key !== 'form_id' && key !== 'form_slug') {
      clean[key] = data[key];
    }
  }
  return clean;
};

// Get a short snippet of data values to show in the list row
const getDataSnippet = (data: any) => {
  if (!data) return '-';
  const parts: string[] = [];
  for (const key in data) {
    if (key !== 'form_id' && key !== 'form_slug' && key !== 'name' && key !== 'email') {
      parts.push(data[key]);
    }
  }
  return parts.join(' | ') || '-';
};

onMounted(() => {
  loadSubmissions();
});
</script>

<style scoped>
.submissions-container {
  max-width: 100%;
}
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-slide-up {
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
