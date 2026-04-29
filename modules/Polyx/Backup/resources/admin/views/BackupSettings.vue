<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Backup Settings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure backup schedule, storage, and retention</p>
      </div>
      <router-link :to="{ name: 'admin.backup.index' }"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm">
        ← Back to Backups
      </router-link>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
      <nav class="flex gap-6">
        <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
          :class="activeTab === tab.key
            ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
          class="pb-3 border-b-2 text-sm font-medium transition">
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- General Tab -->
    <div v-if="activeTab === 'general'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Files & Directories to Backup</h3>
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-4 mb-4 text-sm text-amber-800 dark:text-amber-300">
          <strong>⚠️ Caution when backing up .env:</strong> If you restore a backup containing the .env file to a NEW server, the old database credentials will overwrite the new server's configuration! Remember to update your .env file manually right after restoration on a new VPS.
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
          <label v-for="dir in availableDirectories" :key="dir.path"
            class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600/50 transition cursor-pointer">
            <input type="checkbox" v-model="dir.enabled"
              class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
            <div class="flex-1">
              <div class="text-sm font-medium text-gray-900 dark:text-white">{{ dir.name }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">{{ dir.path }} — {{ dir.size_formatted }}</div>
            </div>
          </label>
        </div>
      </div>

      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Excluded Tables</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
          Protected tables (backup_records, cloud_accounts, sessions, cache, jobs) are always excluded automatically.
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
          <label v-for="table in userExcludableTables" :key="table"
            class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" v-model="excludedTables" :value="table"
              class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
            {{ table }}
          </label>
        </div>
      </div>

      <button @click="saveSettings" :disabled="saving"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 text-sm font-medium">
        {{ saving ? 'Saving...' : 'Save Settings' }}
      </button>
    </div>

    <!-- Schedule Tab -->
    <div v-if="activeTab === 'schedule'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
      <div class="flex items-center gap-3">
        <input v-model="scheduleEnabled" type="checkbox" id="schedule-enabled"
          class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
        <label for="schedule-enabled" class="text-sm font-medium text-gray-900 dark:text-white">Enable Scheduled Auto-Backup</label>
      </div>

      <div v-if="scheduleEnabled" class="space-y-4 pl-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frequency</label>
          <select v-model="scheduleFrequency"
            class="w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
            <option value="hourly">Hourly</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="custom">Custom (Minutes)</option>
          </select>
        </div>
        <div v-if="scheduleFrequency === 'custom'">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Interval (minutes)</label>
          <input v-model.number="scheduleCustomMinutes" type="number" min="1" max="1440"
            class="w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
          <p class="text-xs text-gray-500 mt-1">Backup will run every {{ scheduleCustomMinutes }} minutes.</p>
        </div>
      </div>

      <button @click="saveSettings" :disabled="saving"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 text-sm font-medium">
        {{ saving ? 'Saving...' : 'Save Settings' }}
      </button>
    </div>

    <!-- Cloud Tab -->
    <div v-if="activeTab === 'cloud'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cloud Storage Accounts</h3>
          <button @click="showAddCloud = true"
            class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
            + Add Account
          </button>
        </div>

        <div v-if="cloudAccounts.length" class="space-y-3">
          <div v-for="account in cloudAccounts" :key="account.id"
            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <div class="flex items-center gap-3">
              <span class="text-2xl">{{ account.provider === 'google_drive' ? '📁' : '☁️' }}</span>
              <div>
                <div class="font-medium text-gray-900 dark:text-white">{{ account.name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  {{ account.provider === 'google_drive' ? 'Google Drive' : 'OneDrive' }}
                  <span v-if="account.base_path_name"> — {{ account.base_path_name }}</span>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span :class="account.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300'"
                class="px-2 py-0.5 rounded-full text-xs font-medium">
                {{ account.is_active ? 'Connected' : 'Not Connected' }}
              </span>
              <button v-if="!account.is_active" @click="connectCloud(account.id)"
                class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700 transition">Connect</button>
              <button @click="deleteCloudAccount(account.id)"
                class="p-1 text-gray-400 hover:text-red-600 transition">🗑️</button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
          <p class="text-2xl mb-2">☁️</p>
          <p>No cloud accounts configured</p>
          <p class="text-sm mt-1">Add Google Drive or OneDrive to sync backups</p>
        </div>
      </div>

      <!-- Add Cloud Account Dialog -->
      <div v-if="showAddCloud" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6 border border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Cloud Storage Account</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Name</label>
              <input v-model="cloudForm.name" type="text" placeholder="My Google Drive"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provider</label>
              <select v-model="cloudForm.provider"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="google_drive">Google Drive</option>
                <option value="onedrive">OneDrive</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client ID</label>
              <input v-model="cloudForm.client_id" type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client Secret</label>
              <input v-model="cloudForm.client_secret" type="password"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
            </div>
          </div>
          <div class="flex justify-end gap-3 mt-6">
            <button @click="showAddCloud = false"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm">
              Cancel
            </button>
            <button @click="saveCloudAccount"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
              Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Retention Tab -->
    <div v-if="activeTab === 'retention'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maximum Backups to Keep</label>
        <input v-model.number="retentionMaxBackups" type="number" min="1" max="100"
          class="w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Oldest backups will be deleted when this limit is exceeded</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maximum Backup Age (days)</label>
        <input v-model.number="retentionMaxDays" type="number" min="1" max="365"
          class="w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Backups older than this will be auto-deleted (at least 1 is always kept)</p>
      </div>

      <button @click="saveSettings" :disabled="saving"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 text-sm font-medium">
        {{ saving ? 'Saving...' : 'Save Settings' }}
      </button>
    </div>

    <!-- Maintenance Tab -->
    <div v-if="activeTab === 'maintenance'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
      <div class="flex items-center gap-3">
        <input v-model="maintenanceForRestore" type="checkbox" id="maintenance-restore"
          class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
        <label for="maintenance-restore" class="text-sm text-gray-900 dark:text-white">Auto-enable maintenance mode during restore (recommended)</label>
      </div>

      <div class="flex items-center gap-3">
        <input v-model="maintenanceForBackup" type="checkbox" id="maintenance-backup"
          class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
        <label for="maintenance-backup" class="text-sm text-gray-900 dark:text-white">Auto-enable maintenance mode during backup</label>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Maintenance Duration (minutes)</label>
        <input v-model.number="maintenanceMaxMinutes" type="number" min="5" max="120"
          class="w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Auto-disable maintenance if it runs longer than this</p>
      </div>

      <button @click="saveSettings" :disabled="saving"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 text-sm font-medium">
        {{ saving ? 'Saving...' : 'Save Settings' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useDialogStore } from '@/admin/stores/dialog';

const dialogStore = useDialogStore();
const activeTab = ref('general');
const saving = ref(false);
const loading = ref(true);
const showAddCloud = ref(false);

const tabs = [
  { key: 'general', label: 'General' },
  { key: 'schedule', label: 'Schedule' },
  { key: 'cloud', label: 'Cloud Storage' },
  { key: 'retention', label: 'Retention' },
  { key: 'maintenance', label: 'Maintenance' },
];

// General
const availableDirectories = ref<any[]>([]);
const excludedTables = ref<string[]>([]);
const userExcludableTables = ref<string[]>([]);

// Schedule
const scheduleEnabled = ref(false);
const scheduleFrequency = ref('daily');
const scheduleCustomMinutes = ref(30);

// Cloud
const cloudAccounts = ref<any[]>([]);
const cloudForm = ref({ name: '', provider: 'google_drive', client_id: '', client_secret: '' });

// Retention
const retentionMaxBackups = ref(10);
const retentionMaxDays = ref(30);

// Maintenance
const maintenanceForRestore = ref(true);
const maintenanceForBackup = ref(false);
const maintenanceMaxMinutes = ref(30);

const fetchSettings = async () => {
  try {
    loading.value = true;
    const [settingsRes, infoRes] = await Promise.all([
      axios.get('/api/v1/backup/settings'),
      axios.get('/api/v1/backup/info'),
    ]);

    const settings = settingsRes.data.data?.settings || {};
    cloudAccounts.value = settingsRes.data.data?.cloud_accounts || [];

    // Apply settings
    scheduleEnabled.value = settings.backup_schedule_enabled === '1';
    scheduleFrequency.value = settings.backup_schedule_frequency || 'daily';
    scheduleCustomMinutes.value = parseInt(settings.backup_schedule_custom_minutes || '30');
    retentionMaxBackups.value = parseInt(settings.backup_retention_max_backups || '10');
    retentionMaxDays.value = parseInt(settings.backup_retention_max_days || '30');
    maintenanceForRestore.value = settings.backup_maintenance_restore !== '0';
    maintenanceForBackup.value = settings.backup_maintenance_backup === '1';
    maintenanceMaxMinutes.value = parseInt(settings.backup_maintenance_max_minutes || '30');

    // Parse directories & tables from info
    const info = infoRes.data.data || {};
    const allTables = info.database?.tables || [];
    const protectedTables = info.database?.protected_tables || [];
    userExcludableTables.value = allTables.filter((t: string) => !protectedTables.includes(t));

    availableDirectories.value = (info.directories || []).map((d: any) => ({
      ...d,
      enabled: true,
      size_formatted: formatBytes(d.size || 0),
    }));
  } catch (err) {
    console.error('Failed to load settings:', err);
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    await axios.put('/api/v1/backup/settings', {
      settings: {
        schedule_enabled: scheduleEnabled.value ? '1' : '0',
        schedule_frequency: scheduleFrequency.value,
        schedule_custom_minutes: String(scheduleCustomMinutes.value),
        retention_max_backups: String(retentionMaxBackups.value),
        retention_max_days: String(retentionMaxDays.value),
        maintenance_restore: maintenanceForRestore.value ? '1' : '0',
        maintenance_backup: maintenanceForBackup.value ? '1' : '0',
        maintenance_max_minutes: String(maintenanceMaxMinutes.value),
        excluded_tables: JSON.stringify(excludedTables.value),
        directories: JSON.stringify(availableDirectories.value.filter((d: any) => d.enabled)),
      },
    });
    dialogStore.success('Settings saved successfully!');
  } catch (err: any) {
    dialogStore.error('Failed to save: ' + (err.response?.data?.message || err.message));
  } finally {
    saving.value = false;
  }
};

// Cloud
const saveCloudAccount = async () => {
  try {
    await axios.post('/api/v1/backup/cloud-accounts', cloudForm.value);
    showAddCloud.value = false;
    cloudForm.value = { name: '', provider: 'google_drive', client_id: '', client_secret: '' };
    await fetchSettings();
    dialogStore.success('Cloud account added successfully.');
  } catch (err: any) {
    dialogStore.error('Failed: ' + (err.response?.data?.message || err.message));
  }
};

const connectCloud = async (id: number) => {
  try {
    const res = await axios.get(`/api/v1/backup/cloud-accounts/${id}/auth-url`);
    window.open(res.data.data.url, '_blank');
  } catch (err: any) {
    dialogStore.error('Failed: ' + (err.response?.data?.message || err.message));
  }
};

const deleteCloudAccount = async (id: number) => {
  const confirmed = await dialogStore.confirm({
    title: 'Delete Cloud Account',
    message: 'Delete this cloud account? Backup files on the cloud will not be deleted.',
    type: 'danger',
    confirmText: 'Delete'
  });
  if (!confirmed) return;

  try {
    await axios.delete(`/api/v1/backup/cloud-accounts/${id}`);
    await fetchSettings();
    dialogStore.success('Cloud account deleted.');
  } catch (err: any) {
    dialogStore.error('Failed: ' + (err.response?.data?.message || err.message));
  }
};

const formatBytes = (bytes: number) => {
  if (!bytes) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
};

onMounted(() => {
  fetchSettings();
});
</script>
