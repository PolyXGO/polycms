<template>
 <div class="p-6 bg-admin-theme-base min-h-screen border-t border-admin-theme-border">
 <!-- Header -->
 <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
 <div>
 <h1 class="text-2xl font-bold text-admin-theme-text flex items-center gap-2">
 {{ t('Edit Translations') }}
 <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
 {{ language?.name }} ({{ language?.code }})
 </span>
 <button
 @click="showHelpModal = true"
 class="p-1 rounded-full text-admin-theme-text-muted hover:text-admin-theme-primary hover:bg-admin-theme-surface transition-colors focus:outline-none focus:ring-2 focus:ring-admin-theme-primary"
 :title="t('Translation Guidelines')"
 type="button"
 >
 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 </button>
 </h1>
 <p class="text-sm text-admin-theme-text-muted mt-1">Manage translations per specific section of the application.</p>
 </div>
 <div class="flex flex-wrap gap-2">
 <button
 v-if="!language?.is_default"
 @click="syncFromDefault"
 class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2 text-sm font-medium"
 :disabled="loading"
 >
 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
 </svg>
 {{ t('Sync current scope') }}
 </button>
 <button
 @click="compile"
 class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center gap-2 text-sm font-medium"
 :disabled="loading"
 >
 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
 </svg>
 {{ t('Compile scope') }}
 </button>
 <button
 @click="save"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors flex items-center gap-2 text-sm font-medium"
 :disabled="loading || !hasChanges"
 >
 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
 </svg>
 {{ t('Save Changes') }}
 </button>
 <router-link
 :to="{ name:'admin.settings.languages' }"
 class="px-4 py-2 bg-admin-theme-input-bg text-admin-theme-text-secondary rounded-lg border border-admin-theme-border hover:bg-black/5 dark:hover:bg-white/5 transition-colors text-sm font-medium flex items-center justify-center min-w-[80px]"
 >
 {{ t('Back') }}
 </router-link>
 </div>
 </div>

 <!-- Controls Bar -->
 <div class="mb-4 bg-admin-theme-surface p-4 rounded-lg shadow-sm flex flex-col lg:flex-row gap-4">
 
 <!-- Scope Selector -->
 <div class="flex-1">
 <label class="block text-xs font-semibold text-admin-theme-text-muted uppercase tracking-wider mb-1">{{ t('Translation Scope') }}</label>
 <select
 v-model="currentScope"
 @change="fetchTranslations"
 class="w-full px-4 py-2 rounded-lg border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm shadow-sm transition-colors"
 :disabled="loading"
 >
 <optgroup
 v-for="group in groupedScopes"
 :key="group.label"
 :label="group.label"
 class="bg-admin-theme-surface text-admin-theme-text font-semibold"
 >
 <option
 v-for="scope in group.items"
 :key="scope.id"
 :value="scope.id"
 class="bg-admin-theme-surface text-admin-theme-text font-normal"
 >
 {{ scope.displayName }}
 </option>
 </optgroup>
 </select>
 </div>

 <!-- Search Box -->
 <div class="flex-1 lg:max-w-md">
 <label class="block text-xs font-semibold text-admin-theme-text-muted uppercase tracking-wider mb-1">{{ t('Search') }}</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <svg class="h-4 w-4 text-admin-theme-text-muted" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
 </svg>
 </div>
 <input
 v-model="search"
 type="text"
 :placeholder="t('Search original or translated text...')"
 class="w-full pl-9 pr-4 py-2 rounded-lg border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm shadow-sm transition-colors"
 />
 </div>
 </div>

 <!-- Filter -->
 <div class="w-full lg:w-48">
 <label class="block text-xs font-semibold text-admin-theme-text-muted uppercase tracking-wider mb-1">{{ t('Status Filter') }}</label>
 <select
 v-model="filter"
 class="w-full px-4 py-2 rounded-lg border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm shadow-sm transition-colors"
 >
 <option value="all" class="bg-admin-theme-surface text-admin-theme-text">{{ t('All Translations') }}</option>
 <option value="translated" class="bg-admin-theme-surface text-admin-theme-text">{{ t('Translated Only') }}</option>
 <option value="missing" class="bg-admin-theme-surface text-admin-theme-text">{{ t('Missing Only') }}</option>
 </select>
 </div>
 </div>

 <!-- Translations Table -->
 <div class="bg-admin-theme-surface rounded-lg shadow overflow-hidden relative min-h-[400px]">
 
 <!-- Overlay Loader (Clean & Simple without Card Box) -->
 <div v-if="loading" class="absolute inset-0 bg-admin-theme-surface/75 backdrop-blur-sm z-10 flex flex-col items-center justify-center text-center p-4">
 <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
 <p class="mt-3 text-sm font-medium text-admin-theme-text-secondary">{{ t('Loading translations...') }}</p>
 </div>

 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider w-2/5">
 {{ t('Key / Original String') }} ({{ defaultCode.toUpperCase() }})
 </th>
 <th class="px-6 py-3 text-left text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider w-2/5">
 {{ t('Translation') }}
 </th>
 <th class="px-6 py-3 text-center text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider w-24">
 {{ t('Actions') }}
 </th>
 </tr>
 </thead>
 <tbody class="divide-y divide-admin-theme-border">
 <tr v-for="item in filteredTranslations" :key="item.key" class="hover:bg-black/5 dark:hover:bg-white/5/50 transition-colors">
 <td class="px-6 py-4 align-top">
 <div class="text-sm font-mono text-admin-theme-text break-all select-all hover:bg-admin-theme-base p-1 rounded inline-block">{{ item.key }}</div>
 <div class="text-sm text-admin-theme-text-secondary mt-2 bg-admin-theme-base p-2 rounded border border-admin-theme-border">
 {{ item.original || t('(Empty original key)') }}
 </div>
 </td>
 <td class="px-6 py-4 align-top">
 <textarea
 v-model="item.translated"
 @input="markChanged(item)"
 rows="3"
 class="w-full px-3 py-2 rounded-lg border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary resize-y text-sm transition-colors shadow-sm"
 :class="{ 
'border-yellow-400 focus:border-yellow-400 focus:ring-yellow-400': item.changed,
'border-red-300 focus:border-red-400 focus:ring-red-400': !item.translated && filter ==='missing'
 }"
 :placeholder="item.original"
 />
 <div v-if="item.changed" class="text-xs text-yellow-600 dark:text-yellow-400 mt-1 font-medium flex items-center gap-1">
 <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> {{ t('Unsaved change') }}
 </div>
 </td>
 <td class="px-6 py-4 text-center align-top pt-8">
 <button
 @click="confirmDelete(item)"
 class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-2 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
 :title="t('Delete Key')"
 >
 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
 </svg>
 </button>
 </td>
 </tr>
 <tr v-if="filteredTranslations.length === 0 && !loading">
 <td colspan="3" class="px-6 py-16 text-center text-admin-theme-text-muted">
 <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
 </svg>
 <p class="text-lg font-medium text-admin-theme-text">{{ t('No translations found.') }}</p>
 <p class="text-sm mt-1" v-if="search || filter !=='all'">{{ t('Try adjusting your search or filter criteria.') }}</p>
 <p class="text-sm mt-1" v-else-if="currentScope !=='core'">
 {{ t('This component does not have any translation keys yet.') }}
 <br>
 <button @click="syncFromDefault" class="text-admin-theme-primary hover:text-admin-theme-primary-hover dark:text-admin-theme-primary mt-2 font-medium">
 {{ t('Sync from default language to initialize') }}
 </button>
 </p>
 </td>
 </tr>
 </tbody>
 </table>
 </div>

 <!-- Pagination / Summary -->
 <div class="mt-4 flex items-center justify-between text-sm text-admin-theme-text-muted">
 <span>{{ t('Showing') }} <strong>{{ filteredTranslations.length }}</strong> {{ t('of') }} <strong>{{ translations.length }}</strong> {{ t('translations in scope') }}</span>
 <span v-if="hasChanges" class="text-yellow-600 dark:text-yellow-400 font-medium animate-pulse">{{ t('You have unsaved changes') }}</span>
 </div>

 <!-- Guideline Help Modal -->
 <div v-if="showHelpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showHelpModal = false">
 <div class="bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-2xl max-w-2xl w-full overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-200">
 <!-- Modal Header -->
 <div class="px-6 py-4 border-b border-admin-theme-border flex items-center justify-between bg-admin-theme-base/50">
 <div class="flex items-center gap-2 text-admin-theme-text font-bold text-lg">
 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-admin-theme-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>{{ t('Translation Guidelines & Best Practices') }}</span>
 </div>
 <button @click="showHelpModal = false" class="text-admin-theme-text-muted hover:text-admin-theme-text p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
 </svg>
 </button>
 </div>

 <!-- Modal Body -->
 <div class="p-6 overflow-y-auto space-y-5 text-sm text-admin-theme-text">
 
 <!-- Section 1 -->
 <div class="flex gap-3">
 <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center font-bold text-sm">1</div>
 <div>
 <h4 class="font-semibold text-admin-theme-text mb-1">{{ t('Translation Scopes') }}</h4>
 <p class="text-admin-theme-text-muted text-xs leading-relaxed">
 {{ t('Translations are organized into distinct scopes such as Core, Modules, or Themes. Select the desired scope from the dropdown to edit strings specific to that component.') }}
 </p>
 </div>
 </div>

 <!-- Section 2 -->
 <div class="flex gap-3">
 <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold text-sm">2</div>
 <div>
 <h4 class="font-semibold text-admin-theme-text mb-1">{{ t('Editing & Unsaved Status') }}</h4>
 <p class="text-admin-theme-text-muted text-xs leading-relaxed">
 {{ t('Type your localized text in the right column textarea. When modified, strings display a yellow indicator highlighting unsaved edits. Click "Save Changes" to store them to the database.') }}
 </p>
 </div>
 </div>

 <!-- Section 3 -->
 <div class="flex gap-3">
 <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold text-sm">3</div>
 <div>
 <h4 class="font-semibold text-admin-theme-text mb-1">{{ t('Compiling Scope (Crucial Step)') }}</h4>
 <p class="text-admin-theme-text-muted text-xs leading-relaxed">
 {{ t('Saving updates the database records. To apply changes to the live site, click "Compile scope". This compiles translations into high-performance JSON cache files for runtime delivery.') }}
 </p>
 </div>
 </div>

 <!-- Section 4 -->
 <div class="flex gap-3">
 <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center font-bold text-sm">4</div>
 <div>
 <h4 class="font-semibold text-admin-theme-text mb-1">{{ t('Syncing from Default Language') }}</h4>
 <p class="text-admin-theme-text-muted text-xs leading-relaxed">
 {{ t('For secondary languages, click "Sync current scope" to automatically fetch new or missing translation keys from the primary English locale.') }}
 </p>
 </div>
 </div>

 <!-- Section 5 -->
 <div class="flex gap-3">
 <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold text-sm">5</div>
 <div>
 <h4 class="font-semibold text-admin-theme-text mb-1">{{ t('Variables & Dynamic Placeholders') }}</h4>
 <p class="text-admin-theme-text-muted text-xs leading-relaxed">
 {{ t('Preserve variable placeholders like :count, :name, {0}, or %s exactly as they appear in the original string. The system replaces these placeholders with dynamic data at runtime.') }}
 </p>
 </div>
 </div>

 </div>

 <!-- Modal Footer -->
 <div class="px-6 py-3 border-t border-admin-theme-border bg-admin-theme-base/30 flex justify-end">
 <button @click="showHelpModal = false" class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover text-sm font-medium transition-colors">
 {{ t('Got it, thanks!') }}
 </button>
 </div>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useDialog } from '@/admin/composables/useDialog';
import { useTranslation } from '@/admin/composables/useTranslation';
import AdminLoadingState from '@/admin/components/AdminLoadingState.vue';
import axios from 'axios';

interface TranslationItem {
 key: string;
 original: string;
 translated: string;
 is_translated: boolean;
 changed?: boolean;
}

interface ScopeItem {
 id: string;
 name: string;
 type: string;
 path: string;
}

const route = useRoute();
const dialog = useDialog();
const { t } = useTranslation();

const showHelpModal = ref(false);
const languageId = computed(() => route.params.id as string);
const language = ref<any>(null);
const translations = ref<TranslationItem[]>([]);
const scopes = ref<ScopeItem[]>([]);
const currentScope = ref('core');
const loading = ref(false);
const search = ref('');
const filter = ref('all');
const defaultCode = ref('en');

const hasChanges = computed(() => translations.value.some(item => item.changed));

const groupedScopes = computed(() => {
  const groups: { [key: string]: { label: string; items: (ScopeItem & { displayName: string })[] } } = {
    core: { label: t('Core System') || 'Core System', items: [] },
    module: { label: t('Modules') || 'Modules', items: [] },
    theme: { label: t('Themes') || 'Themes', items: [] },
  };

  scopes.value.forEach(scope => {
    const type = scope.type || 'core';
    if (!groups[type]) {
      groups[type] = { label: type.toUpperCase(), items: [] };
    }
    const displayName = scope.name.replace(/^(Module|Theme):\s*/i, '');
    groups[type].items.push({
      ...scope,
      displayName,
    });
  });

  return Object.values(groups).filter(g => g.items.length > 0);
});

const filteredTranslations = computed(() => {
 let result = translations.value;

 // Filter by search term
 if (search.value) {
 const term = search.value.toLowerCase();
 result = result.filter(item =>
 item.key.toLowerCase().includes(term) ||
 item.original.toLowerCase().includes(term) ||
 (item.translated && item.translated.toLowerCase().includes(term))
 );
 }

 // Filter by status
 if (filter.value ==='translated') {
 result = result.filter(item => item.translated !=='');
 } else if (filter.value ==='missing') {
 result = result.filter(item => item.translated ==='');
 }

 return result;
});

const markChanged = (item: TranslationItem) => {
 item.changed = true;
};

const fetchTranslations = async () => {
 if (hasChanges.value) {
 const confirmed = await dialog.confirm(t('You have unsaved changes. Changing scopes will lose these changes. Continue?'));
 if (!confirmed) {
 // Revert select back (naive attempt, ideally we store previous scope)
 return;
 }
 }

 loading.value = true;
 try {
 const response = await axios.get(`/api/v1/languages/${languageId.value}/translations`, {
 params: { scope: currentScope.value }
 });
 language.value = response.data.data.language;
 scopes.value = response.data.data.scopes;
 currentScope.value = response.data.data.current_scope;
 
 translations.value = response.data.data.translations.map((item: TranslationItem) => ({
 ...item,
 changed: false,
 }));
 } catch (e: any) {
 dialog.error(e.response?.data?.message ||'Failed to load translations');
 } finally {
 loading.value = false;
 }
};

const save = async () => {
 loading.value = true;
 try {
 const changedItems = translations.value.filter(item => item.changed);
 await axios.put(`/api/v1/languages/${languageId.value}/translations`, {
 translations: changedItems.map(item => ({
 key: item.key,
 translated: item.translated,
 })),
 }, {
 params: { scope: currentScope.value }
 });
 dialog.success(t('Translations saved successfully for this scope.'));
 // Reset changed flags without fully reloading to preserve state
 translations.value.forEach(item => {
 if(item.changed) {
 item.changed = false;
 item.is_translated = item.translated !=='';
 }
 });
 } catch (e: any) {
 dialog.error(e.response?.data?.message ||'Failed to save translations');
 } finally {
 loading.value = false;
 }
};

const compile = async () => {
 loading.value = true;
 try {
 await axios.post(`/api/v1/languages/${languageId.value}/compile`, null, {
 params: { scope: currentScope.value }
 });
 dialog.success(t('Scope translations compiled successfully.'));
 } catch (e: any) {
 dialog.error(e.response?.data?.message ||'Failed to compile translations');
 } finally {
 loading.value = false;
 }
};

const syncFromDefault = async () => {
 loading.value = true;
 try {
 const response = await axios.post(`/api/v1/languages/${languageId.value}/sync`, null, {
 params: { scope: currentScope.value }
 });
 dialog.success(response.data.message || t('Keys synced from default language.'));
 // Reload translations to reflect newly added keys
 await fetchTranslations();
 } catch (e: any) {
 dialog.error(e.response?.data?.message ||'Failed to sync keys');
 } finally {
 loading.value = false;
 }
};

const confirmDelete = async (item: TranslationItem) => {
 const confirmed = await dialog.confirm(
 t('Are you sure you want to delete this translation key from this scope?') + `\n\nKey: ${item.key}`
 );

 if (confirmed) {
 await deleteKey(item);
 }
};

const deleteKey = async (item: TranslationItem) => {
 loading.value = true;
 try {
 await axios.delete(`/api/v1/languages/${languageId.value}/translations/key`, {
 data: { key: item.key },
 params: { scope: currentScope.value }
 });
 dialog.success(t('Translation key deleted successfully from scope.'));
 // Remove from local list
 translations.value = translations.value.filter(t => t.key !== item.key);
 } catch (e: any) {
 dialog.error(e.response?.data?.message ||'Failed to delete translation key');
 } finally {
 loading.value = false;
 }
};

onMounted(() => {
 fetchTranslations();
});
</script>
