<template>
    <div class="max-w-4xl">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('System Information') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ t('View system version, PHP info, and server environment') }}</p>
        </div>

        <div v-if="loading" class="flex justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </div>

        <div v-else class="space-y-6">
            <!-- Core Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ t('Core') }}</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <div class="flex justify-between px-5 py-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('PolyCMS Version') }}</span>
                        <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">v{{ info.polycms_version }}</span>
                    </div>
                    <div class="flex justify-between px-5 py-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('PHP Version') }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ info.php_version }}</span>
                    </div>
                    <div class="flex justify-between px-5 py-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('Laravel Version') }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ info.laravel_version }}</span>
                    </div>
                    <div class="flex justify-between px-5 py-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('Database Driver') }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ info.database_driver }}</span>
                    </div>
                    <div class="flex justify-between px-5 py-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('Server OS') }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ info.server_os }}</span>
                    </div>
                </div>
            </div>

            <!-- Disk & Upload -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ t('Server') }}</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <div class="flex justify-between px-5 py-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('Disk Space') }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatBytes(info.disk_free) }} {{ t('free') }} / {{ formatBytes(info.disk_total) }} {{ t('total') }}</span>
                    </div>
                    <div class="px-5 py-3">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('Disk Usage') }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ diskPercent }}%</span>
                        </div>
                        <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all" :class="diskPercent > 90 ? 'bg-red-500' : diskPercent > 70 ? 'bg-amber-500' : 'bg-green-500'" :style="{ width: diskPercent + '%' }"></div>
                        </div>
                    </div>
                    <div class="flex justify-between px-5 py-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('Max Upload Size') }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatBytes(info.max_upload_size) }}</span>
                    </div>
                </div>
            </div>

            <!-- PHP Extensions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ t('PHP Extensions') }}</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <div v-for="(enabled, ext) in (info.extensions || {})" :key="ext" class="flex justify-between px-5 py-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ ext }}</span>
                        <span class="text-sm" :class="enabled ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">{{ enabled ? '✅ Installed' : '❌ Missing' }}</span>
                    </div>
                </div>
            </div>

            <!-- Installed Modules -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ t('Installed Modules') }}</h2>
                </div>
                <div v-if="modules.length === 0" class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ t('No modules installed.') }}</div>
                <div v-else class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <div v-for="m in modules" :key="m.key" class="flex justify-between items-center px-5 py-3">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ m.name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">v{{ m.version }}</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="m.enabled ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">
                            {{ m.enabled ? t('Enabled') : t('Disabled') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';

const { t } = useTranslation();

const loading = ref(true);
const info = ref<any>({});
const modules = ref<any[]>([]);

const diskPercent = computed(() => {
    if (!info.value.disk_total) return 0;
    const used = info.value.disk_total - info.value.disk_free;
    return Math.round((used / info.value.disk_total) * 100);
});

const formatBytes = (bytes: number): string => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

onMounted(async () => {
    try {
        const [sysRes, modRes] = await Promise.all([
            axios.get('/api/v1/system/info'),
            axios.get('/api/v1/modules'),
        ]);
        info.value = sysRes.data?.data || {};
        const rawModules = modRes.data?.data || modRes.data || {};
        modules.value = Object.entries(rawModules).map(([key, m]: [string, any]) => ({ key, ...m }));
    } catch { /* silent */ }
    loading.value = false;
});
</script>
