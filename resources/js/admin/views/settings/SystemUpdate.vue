<template>
    <div class="w-full">
        <div class="mb-8">
            <router-link to="/admin/settings" class="inline-flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 mb-3">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                {{ t('Back to Hub') }}
            </router-link>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('System Update') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ t('Update PolyCMS core from an official package') }} · <a href="https://polycms.org/changelog" target="_blank" rel="noopener" class="text-indigo-500 dark:text-indigo-400 hover:underline">{{ t('Changelog') }}</a></p>
        </div>

        <!-- Current Version Card -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ t('PolyCMS Version') }}</div>
                <div class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400">v{{ systemInfo.polycms_version || '...' }}</div>
            </div>
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ t('PHP Version') }}</div>
                <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ systemInfo.php_version || '...' }}</div>
            </div>
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ t('Laravel Version') }}</div>
                <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ systemInfo.laravel_version || '...' }}</div>
            </div>
        </div>

        <!-- Two-column layout -->
        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
            <!-- Left Column: Actions -->
            <div class="xl:col-span-3 space-y-6">

        <!-- Check for Updates -->
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ t('Check for Updates') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ t('Check polycms.org for a newer version') }}</p>
                </div>
                <button @click="checkForUpdates" :disabled="checking" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors disabled:opacity-50">
                    <span v-if="checking" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ t('Checking...') }}
                    </span>
                    <span v-else>{{ t('Check Now') }}</span>
                </button>
            </div>
            <div v-if="updateCheck" class="mt-4 p-3 rounded-lg text-sm" :class="updateCheck.available ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'">
                <span v-if="updateCheck.available">🆕 {{ t('New version available') }}: <strong>v{{ updateCheck.latest_version }}</strong></span>
                <span v-else>✅ {{ updateCheck.message || t('You are running the latest version.') }}</span>
            </div>
        </div>

        <!-- Upload Package -->
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ t('Upload Update Package') }}</h2>

            <div v-if="!packageInfo" @dragover.prevent="dragOver = true" @dragleave="dragOver = false" @drop.prevent="handleDrop" class="relative border-2 border-dashed rounded-xl p-10 text-center transition-all cursor-pointer" :class="dragOver ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-400'" @click="$refs.fileInput.click()">
                <input ref="fileInput" type="file" accept=".zip" class="hidden" @change="handleFileSelect" />
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('Drag & drop your .zip package here, or click to browse') }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ t('Maximum file size') }}: {{ formatBytes(systemInfo.max_upload_size || 0) }}</p>
            </div>

            <!-- Upload Progress -->
            <div v-if="uploading" class="mt-4">
                <div class="flex items-center gap-3">
                    <svg class="animate-spin h-5 w-5 text-indigo-600" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ t('Uploading and validating package...') }}</span>
                </div>
                <div class="mt-2 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-600 rounded-full transition-all" :style="{ width: uploadProgress + '%' }"></div>
                </div>
            </div>

            <!-- Package Validation Results -->
            <div v-if="packageInfo" class="mt-4 space-y-4">
                <div class="p-4 rounded-xl border" :class="packageInfo.is_upgrade ? 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800' : packageInfo.is_downgrade ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800' : 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800'">
                    <div class="flex items-center gap-2 mb-3">
                        <span v-if="packageInfo.is_upgrade" class="text-green-600 dark:text-green-400 text-lg">⬆️</span>
                        <span v-else-if="packageInfo.is_downgrade" class="text-amber-600 dark:text-amber-400 text-lg">⬇️</span>
                        <span v-else class="text-blue-600 dark:text-blue-400 text-lg">🔄</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            v{{ packageInfo.current_version }} → v{{ packageInfo.package_version }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="packageInfo.is_upgrade ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : packageInfo.is_downgrade ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'">
                            {{ packageInfo.is_upgrade ? t('Upgrade') : packageInfo.is_downgrade ? t('Downgrade') : t('Reinstall') }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <div>{{ t('Files') }}: <strong class="text-gray-900 dark:text-white">{{ packageInfo.file_count?.toLocaleString() }}</strong></div>
                        <div>{{ t('Size') }}: <strong class="text-gray-900 dark:text-white">{{ formatBytes(packageInfo.file_size) }}</strong></div>
                        <div>{{ t('Vendor included') }}: <strong :class="packageInfo.has_vendor ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">{{ packageInfo.has_vendor ? '✅ Yes' : '❌ No' }}</strong></div>
                        <div>{{ t('Build included') }}: <strong :class="packageInfo.has_build ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">{{ packageInfo.has_build ? '✅ Yes' : '❌ No' }}</strong></div>
                    </div>
                </div>

                <div class="p-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-lg text-sm text-amber-700 dark:text-amber-400">
                    ⚠️ {{ t('The site will enter maintenance mode during the update. A backup will be created automatically before any changes are made.') }}
                </div>

                <div class="flex items-center gap-3">
                    <button @click="executeUpdate" :disabled="updating" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ updating ? t('Updating...') : t('Update Now') }}
                    </button>
                    <button @click="cancelUpload" class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                        {{ t('Cancel') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Update Progress -->
        <div v-if="updating && updateSteps.length" class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ t('Update Progress') }}</h2>
            <div class="space-y-3">
                <div v-for="(step, i) in updateSteps" :key="i" class="flex items-start gap-3">
                    <span v-if="step.status === 'success'" class="text-green-500 mt-0.5"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                    <span v-else-if="step.status === 'failed'" class="text-red-500 mt-0.5"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                    <span v-else class="mt-0.5"><svg class="animate-spin h-4 w-4 text-indigo-600" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg></span>
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ step.step }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ step.message }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Result -->
        <div v-if="updateResult" class="p-5 rounded-xl border shadow-sm" :class="updateResult.success ? 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800'">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-lg">
                    <svg v-if="updateResult.success" class="w-6 h-6 inline-block text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    <svg v-else class="w-6 h-6 inline-block text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
                <h3 class="font-semibold" :class="updateResult.success ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'">{{ updateResult.message }}</h3>
            </div>
        </div>

        <!-- Database & Maintenance -->
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm mt-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ t('Database Migrations') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ t('Run pending database migrations manually after a manual update.') }}</p>
                </div>
                <button @click="runMigrations" :disabled="migrating" class="px-4 py-2 text-sm font-medium rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors disabled:opacity-50">
                    <span v-if="migrating" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ t('Migrating...') }}
                    </span>
                    <span v-else>{{ t('Run Migrations') }}</span>
                </button>
            </div>
        </div>

            </div> <!-- /Left Column -->

            <!-- Right Column: Backups & History -->
            <div class="xl:col-span-2 space-y-6">

        <!-- Backups -->
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ t('Core Backups') }}</h2>
                <button @click="loadBackups" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">{{ t('Refresh') }}</button>
            </div>
            <div v-if="backups.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-4 text-center">{{ t('No backups available.') }}</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-2 pr-4">{{ t('Version') }}</th>
                        <th class="pb-2 pr-4">{{ t('Size') }}</th>
                        <th class="pb-2 pr-4">{{ t('Created') }}</th>
                        <th class="pb-2">{{ t('Action') }}</th>
                    </tr></thead>
                    <tbody>
                        <tr v-for="b in backups" :key="b.filename" class="border-b border-gray-100 dark:border-gray-700/50">
                            <td class="py-2.5 pr-4 font-medium text-gray-900 dark:text-white">v{{ b.version }}</td>
                            <td class="py-2.5 pr-4 text-gray-600 dark:text-gray-400">{{ formatBytes(b.size) }}</td>
                            <td class="py-2.5 pr-4 text-gray-600 dark:text-gray-400">{{ b.created_at }}</td>
                            <td class="py-2.5">
                                <button @click="performRollback(b)" :disabled="updating" class="text-xs px-3 py-1 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors disabled:opacity-50">{{ t('Rollback') }}</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Update History -->
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ t('Update History') }}</h2>
            <div v-if="updateLogs.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-4 text-center">{{ t('No update history.') }}</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-2 pr-4">{{ t('From') }}</th>
                        <th class="pb-2 pr-4">{{ t('To') }}</th>
                        <th class="pb-2 pr-4">{{ t('Status') }}</th>
                        <th class="pb-2 pr-4">{{ t('By') }}</th>
                        <th class="pb-2">{{ t('Date') }}</th>
                    </tr></thead>
                    <tbody>
                        <tr v-for="log in updateLogs" :key="log.id" class="border-b border-gray-100 dark:border-gray-700/50">
                            <td class="py-2.5 pr-4 text-gray-900 dark:text-white">v{{ log.from_version }}</td>
                            <td class="py-2.5 pr-4 text-gray-900 dark:text-white">v{{ log.to_version }}</td>
                            <td class="py-2.5 pr-4">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="statusClass(log.status)">{{ log.status }}</span>
                            </td>
                            <td class="py-2.5 pr-4 text-gray-600 dark:text-gray-400">{{ log.performer?.name || 'CLI' }}</td>
                            <td class="py-2.5 text-gray-600 dark:text-gray-400">{{ log.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

            </div> <!-- /Right Column -->
        </div> <!-- /Grid -->
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();

const systemInfo = ref<any>({});
const packageInfo = ref<any>(null);
const uploading = ref(false);
const uploadProgress = ref(0);
const updating = ref(false);
const checking = ref(false);
const updateCheck = ref<any>(null);
const updateSteps = ref<any[]>([]);
const updateResult = ref<any>(null);
const backups = ref<any[]>([]);
const updateLogs = ref<any[]>([]);
const dragOver = ref(false);
const storedPackagePath = ref('');
const migrating = ref(false);

const loadSystemInfo = async () => {
    try {
        const res = await axios.get('/api/v1/system/info');
        systemInfo.value = res.data?.data || {};
    } catch { /* silent */ }
};

const loadBackups = async () => {
    try {
        const res = await axios.get('/api/v1/system/backups');
        backups.value = res.data?.data || [];
    } catch { /* silent */ }
};

const loadUpdateLogs = async () => {
    try {
        const res = await axios.get('/api/v1/system/update/log');
        updateLogs.value = res.data?.data || [];
    } catch { /* silent */ }
};

const checkForUpdates = async () => {
    checking.value = true;
    try {
        const res = await axios.get('/api/v1/system/check-update');
        updateCheck.value = res.data?.data || {};
    } catch {
        updateCheck.value = { available: false, message: 'Unable to connect to update server.' };
    } finally {
        checking.value = false;
    }
};

const handleFileSelect = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) uploadFile(input.files[0]);
};

const handleDrop = (e: DragEvent) => {
    dragOver.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file && file.name.endsWith('.zip')) uploadFile(file);
    else dialog.error(t('Please select a .zip file'));
};

const uploadFile = async (file: File) => {
    uploading.value = true;
    uploadProgress.value = 0;
    updateResult.value = null;

    const formData = new FormData();
    formData.append('package', file);

    try {
        const res = await axios.post('/api/v1/system/update/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (e: any) => {
                uploadProgress.value = Math.round((e.loaded * 100) / (e.total || 1));
            },
        });

        if (res.data?.success) {
            packageInfo.value = res.data.data;
            storedPackagePath.value = res.data.data.stored_path;
        } else {
            dialog.error(res.data?.message || t('Upload failed'));
        }
    } catch (err: any) {
        dialog.error(err.response?.data?.message || t('Upload failed'));
    } finally {
        uploading.value = false;
    }
};

const executeUpdate = async () => {
    const confirmed = await dialog.confirm(
        t('Are you sure you want to update PolyCMS? The site will enter maintenance mode during the update.'),
        t('Confirm Update')
    );
    if (!confirmed) return;

    updating.value = true;
    updateSteps.value = [{ step: 'Starting update', status: 'running', message: 'Preparing...' }];
    updateResult.value = null;

    try {
        const res = await axios.post('/api/v1/system/update/execute', {
            package_path: storedPackagePath.value,
        });

        if (res.data?.success) {
            updateResult.value = { success: true, message: res.data.message };
            updateSteps.value = res.data.data?.steps || [];
            dialog.success(res.data.message);
            loadSystemInfo();
            loadBackups();
            loadUpdateLogs();
        } else {
            updateResult.value = { success: false, message: res.data?.message || 'Update failed' };
            updateSteps.value = res.data?.data?.steps || [];
        }
    } catch (err: any) {
        const msg = err.response?.data?.message || 'Update failed';
        updateResult.value = { success: false, message: msg };
        updateSteps.value = err.response?.data?.data?.steps || updateSteps.value;
        dialog.error(msg);
    } finally {
        updating.value = false;
        packageInfo.value = null;
    }
};

const cancelUpload = () => {
    packageInfo.value = null;
    storedPackagePath.value = '';
};

const performRollback = async (backup: any) => {
    const confirmed = await dialog.confirm(
        t('Are you sure you want to rollback to version') + ` v${backup.version}? ` + t('The site will enter maintenance mode.'),
        t('Confirm Rollback')
    );
    if (!confirmed) return;

    updating.value = true;
    try {
        const res = await axios.post('/api/v1/system/rollback', { backup_path: backup.path });
        if (res.data?.success) {
            dialog.success(res.data.message);
            loadSystemInfo();
            loadUpdateLogs();
        } else {
            dialog.error(res.data?.message || 'Rollback failed');
        }
    } catch (err: any) {
        dialog.error(err.response?.data?.message || 'Rollback failed');
    } finally {
        updating.value = false;
    }
};

const runMigrations = async () => {
    const confirmed = await dialog.confirm(
        t('Are you sure you want to run database migrations?'),
        t('Confirm Migration')
    );
    if (!confirmed) return;

    migrating.value = true;
    try {
        const res = await axios.post('/api/v1/system/update/migrate');
        if (res.data?.success) {
            dialog.success(res.data.message);
        } else {
            dialog.error(res.data?.message || 'Migration failed');
        }
    } catch (err: any) {
        dialog.error(err.response?.data?.message || 'Migration failed');
    } finally {
        migrating.value = false;
    }
};

const formatBytes = (bytes: number): string => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        success: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
        failed: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
        running: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
        rolled_back: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
        pending: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400',
    };
    return map[status] || map.pending;
};

onMounted(() => {
    loadSystemInfo();
    loadBackups();
    loadUpdateLogs();
});
</script>
