<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Backups</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage site backups, restore, and cloud sync</p>
      </div>
      <div class="flex gap-3">
        <router-link :to="{ name: 'admin.backup.settings' }"
          class="px-4 py-2 flex items-center border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm">
          <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Settings
        </router-link>
        <button @click="showCreateDialog = true" :disabled="isProcessing"
          class="px-4 py-2 flex items-center bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg> Create Backup
        </button>
      </div>
    </div>

    <!-- Stats cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Backups</div>
        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ stats.total_backups }}</div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Size</div>
        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ stats.total_size_formatted }}</div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Database</div>
        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ systemInfo?.database?.driver?.toUpperCase() || '—' }}</div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Backup</div>
        <div class="text-lg font-semibold text-gray-700 dark:text-gray-300 mt-1">{{ stats.last_backup ? formatDate(stats.last_backup) : 'Never' }}</div>
      </div>
    </div>

    <!-- Progress bar (shown during backup/restore) -->
    <div v-if="activeProgress" class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
      <div class="flex justify-between items-center mb-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ progressLabel }}</span>
        <span class="text-sm text-gray-500">{{ activeProgress.percent }}%</span>
      </div>
      <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" :style="{ width: activeProgress.percent + '%' }"></div>
      </div>
    </div>

    <!-- Filters Header -->
    <div class="flex flex-col sm:flex-row gap-4 mb-4">
      <div class="relative w-full sm:w-64">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <input v-model="filters.search" @input="onSearchInput" type="text" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5" placeholder="Search backups...">
      </div>
      <div class="w-full sm:w-48">
        <select v-model="filters.type" @change="onFilterChange" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
          <option value="">All Types</option>
          <option value="full">Full Backup</option>
          <option value="database">Database</option>
          <option value="files">Files Only</option>
        </select>
      </div>
    </div>

    <!-- Backup list -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs">
            <tr>
              <SortableHeader label="Name" sortKey="name" :currentKey="sortKey" :currentDir="sortDirection" @sort="toggleSort" class="px-5 py-3 font-semibold" />
              <SortableHeader label="Type" sortKey="type" :currentKey="sortKey" :currentDir="sortDirection" @sort="toggleSort" class="px-5 py-3 font-semibold" />
              <SortableHeader label="Status" sortKey="status" :currentKey="sortKey" :currentDir="sortDirection" @sort="toggleSort" class="px-5 py-3 font-semibold" />
              <SortableHeader label="Size" sortKey="size" :currentKey="sortKey" :currentDir="sortDirection" @sort="toggleSort" class="px-5 py-3 font-semibold" />
              <SortableHeader label="Created" sortKey="created_at" :currentKey="sortKey" :currentDir="sortDirection" @sort="toggleSort" class="px-5 py-3 font-semibold" />
              <th class="px-5 py-3 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="backup in backups" :key="backup.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition text-gray-900 dark:text-gray-100">
              <td class="px-5 py-4">
                <div class="font-medium">{{ backup.name }}</div>
                <div v-if="backup.creator" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">by {{ backup.creator.name }}</div>
              </td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="typeClass(backup.type)">
                  {{ backup.type }}
                </span>
              </td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="statusClass(backup.status)">
                  {{ backup.status }}
                </span>
              </td>
              <td class="px-5 py-4">
                <div class="font-medium text-gray-900 dark:text-gray-100">{{ formatBytes(backup.size) }}</div>
                <div v-if="backup.type === 'full'" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                  DB: {{ backup.database_size ? formatBytes(backup.database_size) : '0 B' }} | 
                  Files: {{ formatBytes(backup.size - (backup.database_size || 0)) }}
                </div>
                <div v-else-if="backup.type === 'database'" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                  DB: {{ backup.database_size ? formatBytes(backup.database_size) : formatBytes(backup.size) }}
                </div>
                <div v-else-if="backup.type === 'files'" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                  Files: {{ formatBytes(backup.size) }}
                </div>
              </td>
              <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ formatDate(backup.created_at) }}</td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button v-if="backup.status === 'completed'" @click="downloadBackup(backup.id)" title="Download"
                    class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                  </button>
                  <button v-if="backup.status === 'completed'" @click="confirmRestore(backup)" title="Restore"
                    class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                  </button>
                  <button @click="confirmDelete(backup)" title="Delete"
                    class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!backups.length && !loading">
              <td colspan="6" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                <p class="font-medium">No backups yet</p>
                <p class="text-sm mt-1">Create your first backup to protect your data</p>
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="6" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                <div class="animate-spin inline-block w-6 h-6 border-2 border-indigo-600 rounded-full border-t-transparent"></div>
                <p class="mt-2">Loading backups...</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <DataPagination
          mode="numbered"
          :currentPage="currentPage"
          :lastPage="lastPage"
          :total="total"
          :from="from"
          :to="to"
          :loading="loading"
          @page-change="(p) => { currentPage = p; fetchBackups(); }"
        />
      </div>
    </div>

    <!-- Create Backup Dialog -->
    <div v-if="showCreateDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create Backup</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Backup Name (optional)</label>
            <input v-model="createForm.name" type="text" placeholder="Auto-generated if empty"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Backup Type</label>
            <select v-model="createForm.type"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 text-sm">
              <option value="full">Full (Database + Files)</option>
              <option value="database">Database Only</option>
              <option value="files">Files Only</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="createForm.enable_maintenance" type="checkbox" id="create-maintenance"
              class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
            <label for="create-maintenance" class="text-sm text-gray-700 dark:text-gray-300">Enable maintenance mode during backup</label>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button @click="showCreateDialog = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm">
            Cancel
          </button>
          <button @click="createBackup" :disabled="creating"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 text-sm font-medium">
            {{ creating ? 'Creating...' : 'Create Backup' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Restore Confirm Dialog -->
    <div v-if="restoreTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
          <svg class="h-6 w-6 text-amber-500 mr-2 inline-block flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          Confirm Restore
        </h3>
        
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-4 mb-4 text-sm">
          <p class="font-medium text-amber-800 dark:text-amber-300 mb-1">This will overwrite current data!</p>
          <p class="text-amber-700 dark:text-amber-400 mb-2">A pre-restore snapshot will be created automatically. If restore fails, the system will auto-rollback.</p>
          <p class="text-amber-700 dark:text-amber-400 font-medium"><svg class="w-4 h-4 inline-block -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Important for migrations:</p>
          <p class="text-amber-700 dark:text-amber-400">If you are restoring to a <b>NEW VPS</b> and this backup contains the <code>.env</code> file, it will overwrite the new DB credentials. You must manually fix your <code>.env</code> immediately after restoration.</p>
        </div>

        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mb-4">
          <p><strong>Backup:</strong> {{ restoreTarget.name }}</p>
          <p><strong>Type:</strong> {{ restoreTarget.type }}</p>
          <p><strong>Size:</strong> {{ formatBytes(restoreTarget.size) }}</p>
          <p><strong>Created:</strong> {{ formatDate(restoreTarget.created_at) }}</p>
        </div>

        <div class="flex items-center gap-2 mb-4">
          <input v-model="restoreConfirmed" type="checkbox" id="restore-confirm"
            class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500" />
          <label for="restore-confirm" class="text-sm text-gray-700 dark:text-gray-300">I understand that restore will overwrite current data</label>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="restoreTarget = null; restoreConfirmed = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm">
            Cancel
          </button>
          <button @click="restoreBackup" :disabled="!restoreConfirmed || restoring"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50 text-sm font-medium">
            {{ restoring ? 'Restoring...' : 'Restore Now' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useDialog, useTableSort, usePagination, useDialogStore, SortableHeader, DataPagination } from '@polycms';
interface BackupRecord {
  id: number;
  name: string;
  type: string;
  status: string;
  filename: string;
  size: number;
  database_size: number;
  checksum: string;
  created_at: string;
  creator?: { name: string };
}

const backups = ref<BackupRecord[]>([]);
const dialogStore = useDialogStore();
const loading = ref(true);
const creating = ref(false);
const restoring = ref(false);
const showCreateDialog = ref(false);
const restoreTarget = ref<BackupRecord | null>(null);
const restoreConfirmed = ref(false);
const activeProgress = ref<{ step: string; percent: number } | null>(null);
const progressPollId = ref<number | null>(null);
const systemInfo = ref<any>(null);
const stats = ref({ total_backups: 0, total_size: 0, total_size_formatted: '0 B', last_backup: null as string | null });

const createForm = ref({
  name: '',
  type: 'full',
  enable_maintenance: false,
});

const filters = ref({
  search: '',
  type: ''
});

const {
  currentPage, lastPage, total, from, to, paginationParams, updateFromResponse
} = usePagination({
  perPage: 15,
  onPageChange: () => fetchBackups()
});

const {
  sortKey, sortDirection, toggleSort, sortParams
} = useTableSort({
  defaultKey: 'created_at',
  defaultDir: 'desc',
  onSort: () => {
    currentPage.value = 1;
    fetchBackups();
  }
});

let debounceTimeout: number;
const onSearchInput = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = window.setTimeout(() => {
    currentPage.value = 1;
    fetchBackups();
  }, 400);
};

const onFilterChange = () => {
  currentPage.value = 1;
  fetchBackups();
};

const isProcessing = computed(() => backups.value.some(b => ['processing', 'restoring'].includes(b.status)));

const progressLabel = computed(() => {
  const labels: Record<string, string> = {
    starting: 'Starting backup...',
    backing_up: 'Creating backup...',
    storing: 'Saving backup file...',
    completed: 'Completed!',
    failed: 'Failed',
    preparing: 'Preparing restore...',
    creating_snapshot: 'Creating safety snapshot...',
    downloading: 'Downloading backup...',
    verifying: 'Verifying integrity...',
    extracting: 'Extracting files...',
    restoring_database: 'Restoring database...',
    restoring_files: 'Restoring files...',
    reconciling: 'Reconciling system tables...',
  };
  return labels[activeProgress.value?.step || ''] || 'Working...';
});

// Load data
const fetchBackups = async () => {
  try {
    loading.value = true;
    const params: Record<string, any> = {
      ...paginationParams.value,
      ...sortParams.value
    };
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.type) params.type = filters.value.type;

    const res = await axios.get('/api/v1/backup/records', { params });
    backups.value = res.data.data;
    if (res.data.meta) updateFromResponse(res.data.meta);
  } catch (err) {
    console.error('Failed to load backups:', err);
  } finally {
    loading.value = false;
  }
};

const fetchSystemInfo = async () => {
  try {
    const res = await axios.get('/api/v1/backup/info');
    systemInfo.value = res.data.data;
    stats.value = res.data.data.stats || stats.value;
  } catch (err) {
    console.error('Failed to load system info:', err);
  }
};

// Create backup
const createBackup = async () => {
  creating.value = true;
  showCreateDialog.value = false;
  try {
    const res = await axios.post('/api/v1/backup/create', createForm.value);
    if (res.data.success) {
      await fetchBackups();
      await fetchSystemInfo();
      dialogStore.success('Backup created successfully.');
    } else {
      dialogStore.error('Backup failed: ' + res.data.message);
    }
  } catch (err: any) {
    dialogStore.error('Backup error: ' + (err.response?.data?.message || err.message));
  } finally {
    creating.value = false;
    createForm.value = { name: '', type: 'full', enable_maintenance: false };
  }
};

// Restore
const confirmRestore = (backup: BackupRecord) => {
  restoreTarget.value = backup;
  restoreConfirmed.value = false;
};

const restoreBackup = async () => {
  if (!restoreTarget.value || !restoreConfirmed.value) return;
  restoring.value = true;
  const id = restoreTarget.value.id;
  restoreTarget.value = null;
  restoreConfirmed.value = false;

  try {
    startProgressPolling(id);
    const res = await axios.post(`/api/v1/backup/${id}/restore`, {
      enable_maintenance: true,
      create_snapshot: true,
    });

    if (res.data.success) {
      dialogStore.success('Restore completed successfully!');
    } else {
      dialogStore.error('Restore failed: ' + res.data.message);
    }
  } catch (err: any) {
    dialogStore.error('Restore error: ' + (err.response?.data?.message || err.message));
  } finally {
    restoring.value = false;
    stopProgressPolling();
    await fetchBackups();
    await fetchSystemInfo();
  }
};

// Download
const downloadBackup = async (id: number) => {
  try {
    const res = await axios.get(`/api/v1/backup/${id}/download-url`);
    if (res.data.success && res.data.url) {
      window.location.href = res.data.url;
    }
  } catch (err: any) {
    dialogStore.error('Failed to get download URL: ' + (err.response?.data?.message || err.message));
  }
};

// Delete
const confirmDelete = async (backup: BackupRecord) => {
  const confirmed = await dialogStore.confirm({
    title: 'Delete Backup',
    message: `Are you sure you want to delete backup "<b>${backup.name}</b>"?<br>This action cannot be undone and the file will be permanently lost.`,
    type: 'danger',
    confirmText: 'Delete completely'
  });
  
  if (!confirmed) return;
  
  try {
    await axios.delete(`/api/v1/backup/${backup.id}`);
    await fetchBackups();
    await fetchSystemInfo();
    dialogStore.success('Backup deleted successfully.');
  } catch (err: any) {
    dialogStore.error('Delete failed: ' + (err.response?.data?.message || err.message));
  }
};

// Progress polling
const startProgressPolling = (id: number) => {
  progressPollId.value = window.setInterval(async () => {
    try {
      const res = await axios.get(`/api/v1/backup/${id}/status`);
      const progress = res.data.data?.progress;
      if (progress) {
        activeProgress.value = progress;
        if (progress.percent >= 100 || progress.step === 'completed' || progress.step === 'failed') {
          stopProgressPolling();
        }
      }
    } catch { /* ignore */ }
  }, 2000);
};

const stopProgressPolling = () => {
  if (progressPollId.value) {
    clearInterval(progressPollId.value);
    progressPollId.value = null;
  }
  setTimeout(() => { activeProgress.value = null; }, 3000);
};

// Formatting
const formatBytes = (bytes: number) => {
  if (!bytes) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleString();
};

const typeClass = (type: string) => ({
  'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300': type === 'full',
  'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': type === 'database',
  'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300': type === 'files',
});

const statusClass = (status: string) => ({
  'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': status === 'completed',
  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': status === 'processing' || status === 'restoring',
  'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': status === 'failed',
  'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': status === 'pending',
});

onMounted(() => {
  fetchBackups();
  fetchSystemInfo();
});

onUnmounted(() => {
  stopProgressPolling();
});
</script>
