<template>
 <div class="max-w-4xl">
 <div class="mb-8">
 <router-link to="/admin/settings" class="inline-flex items-center text-sm font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary mb-3">
 <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
 {{ t('Back to Hub') }}
 </router-link>
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('System Information') }}</h1>
 <p class="text-sm text-admin-theme-text-secondary mt-1">{{ t('View system version, PHP info, and server environment') }}</p>
 </div>

 <div v-if="loading" class="flex justify-center py-12">
 <svg class="animate-spin h-8 w-8 text-admin-theme-primary" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
 </div>

 <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
 <div class="space-y-6">
 <!-- Core Info -->
 <div class="bg-admin-theme-surface rounded-xl border border-admin-theme-border shadow-sm overflow-hidden">
 <div class="px-5 py-3 bg-admin-theme-base/50 border-b border-admin-theme-border">
 <h2 class="text-sm font-semibold text-admin-theme-text-secondary uppercase tracking-wide">{{ t('Core') }}</h2>
 </div>
 <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
 <div class="flex justify-between px-5 py-3">
 <span class="text-sm text-admin-theme-text-secondary">{{ t('PolyCMS Version') }}</span>
 <span class="text-sm font-semibold text-admin-theme-primary dark:text-admin-theme-primary">v{{ info.polycms_version }}</span>
 </div>
 <div class="flex justify-between px-5 py-3">
 <span class="text-sm text-admin-theme-text-secondary">{{ t('PHP Version') }}</span>
 <span class="text-sm font-medium text-admin-theme-text">{{ info.php_version }}</span>
 </div>
 <div class="flex justify-between px-5 py-3">
 <span class="text-sm text-admin-theme-text-secondary">{{ t('Laravel Version') }}</span>
 <span class="text-sm font-medium text-admin-theme-text">{{ info.laravel_version }}</span>
 </div>
 <div class="flex justify-between px-5 py-3">
 <span class="text-sm text-admin-theme-text-secondary">{{ t('Database Driver') }}</span>
 <span class="text-sm font-medium text-admin-theme-text">{{ info.database_driver }}</span>
 </div>
 <div class="flex justify-between px-5 py-3">
 <span class="text-sm text-admin-theme-text-secondary">{{ t('Server OS') }}</span>
 <span class="text-sm font-medium text-admin-theme-text">{{ info.server_os }}</span>
 </div>
 </div>
 </div>

 <!-- Disk & Upload -->
 <div class="bg-admin-theme-surface rounded-xl border border-admin-theme-border shadow-sm overflow-hidden">
 <div class="px-5 py-3 bg-admin-theme-base/50 border-b border-admin-theme-border">
 <h2 class="text-sm font-semibold text-admin-theme-text-secondary uppercase tracking-wide">{{ t('Server') }}</h2>
 </div>
 <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
 <div class="flex justify-between px-5 py-3">
 <span class="text-sm text-admin-theme-text-secondary">{{ t('Disk Space') }}</span>
 <span class="text-sm font-medium text-admin-theme-text">{{ formatBytes(info.disk_free) }} {{ t('free') }} / {{ formatBytes(info.disk_total) }} {{ t('total') }}</span>
 </div>
 <div class="px-5 py-3">
 <div class="flex justify-between mb-1">
 <span class="text-sm text-admin-theme-text-secondary">{{ t('Disk Usage') }}</span>
 <span class="text-xs text-admin-theme-text-muted">{{ diskPercent }}%</span>
 </div>
 <div class="h-2 bg-admin-theme-border rounded-full overflow-hidden">
 <div class="h-full rounded-full transition-all" :class="diskPercent > 90 ?'bg-red-500' : diskPercent > 70 ?'bg-amber-500' :'bg-green-500'" :style="{ width: diskPercent +'%' }"></div>
 </div>
 </div>
 <div class="flex justify-between px-5 py-3">
 <span class="text-sm text-admin-theme-text-secondary">{{ t('Max Upload Size') }}</span>
 <span class="text-sm font-medium text-admin-theme-text">{{ formatBytes(info.max_upload_size) }}</span>
 </div>
 </div>
 </div>

 <!-- PHP Extensions -->
 <div class="bg-admin-theme-surface rounded-xl border border-admin-theme-border shadow-sm overflow-hidden">
 <div class="px-5 py-3 bg-admin-theme-base/50 border-b border-admin-theme-border">
 <h2 class="text-sm font-semibold text-admin-theme-text-secondary uppercase tracking-wide">{{ t('PHP Extensions') }}</h2>
 </div>
 <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
 <div v-for="(enabled, ext) in (info.extensions || {})" :key="ext" class="flex justify-between px-5 py-3">
 <span class="text-sm text-admin-theme-text-secondary">{{ ext }}</span>
 <span class="text-sm" :class="enabled ?'text-green-600 dark:text-green-400' :'text-red-600 dark:text-red-400'">{{ enabled ?'✅ Installed' :'❌ Missing' }}</span>
 </div>
 </div>
 </div>
 </div>
 
 <div class="space-y-6">
 <!-- Installed Modules -->
 <div class="bg-admin-theme-surface rounded-xl border border-admin-theme-border shadow-sm overflow-hidden">
 <div class="px-5 py-3 bg-admin-theme-base/50 border-b border-admin-theme-border">
 <h2 class="text-sm font-semibold text-admin-theme-text-secondary uppercase tracking-wide">{{ t('Installed Modules') }}</h2>
 </div>
 <div v-if="modules.length === 0" class="px-5 py-4 text-sm text-admin-theme-text-muted">{{ t('No modules installed.') }}</div>
 <div v-else class="divide-y divide-gray-100 dark:divide-gray-700/50">
 <div v-for="m in modules" :key="m.key" class="flex justify-between items-center px-5 py-3">
 <div>
 <span class="text-sm font-medium text-admin-theme-text">{{ m.name }}</span>
 <span class="text-xs text-admin-theme-text-muted ml-2">v{{ m.version }}</span>
 </div>
 <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="m.enabled ?'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' :'bg-admin-theme-base text-admin-theme-text-muted'">
 {{ m.enabled ? t('Enabled') : t('Disabled') }}
 </span>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from'vue';
import axios from'axios';
import { useTranslation } from'../../composables/useTranslation';

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
 if (!bytes) return'0 B';
 const k = 1024;
 const sizes = ['B','KB','MB','GB','TB'];
 const i = Math.floor(Math.log(bytes) / Math.log(k));
 return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) +'' + sizes[i];
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
