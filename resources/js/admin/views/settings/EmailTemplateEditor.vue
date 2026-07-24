<template>
 <div class="email-template-editor">
 <div class="mb-6 flex items-center gap-4">
 <router-link :to="{ name:'admin.settings.email-templates' }" class="text-admin-theme-primary hover:text-admin-theme-primary-hover font-medium flex items-center">
 <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
 </svg>
 {{ t('Back to Templates') }}
 </router-link>
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Edit Email Template') }}</h1>
 </div>

 <div v-if="loading" class="text-center py-12">
 <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
 </div>

 <form v-else @submit.prevent="saveTemplate" class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
 <div class="xl:col-span-8 space-y-6">
 <!-- Editor Card -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-6">
 <div class="mb-4">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Subject') }}</label>
 <input 
 v-model="form.subject"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
 required
 />
 </div>

 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Email Body') }}</label>
 <EmailTiptapEditor 
 ref="editorRef"
 v-model="form.body"
 />
 </div>
 </div>

 <div class="bg-admin-theme-surface rounded-lg shadow p-6">
 <div class="flex items-center justify-between gap-3 mb-4">
 <div>
 <h3 class="font-bold text-admin-theme-text">{{ t('Preview') }}</h3>
 <p class="text-xs text-admin-theme-text-muted">
 {{ t('Preview only: variables are rendered using random real data from your database.') }}
 </p>
 </div>
 <button
 type="button"
 @click="loadPreview"
 :disabled="previewLoading"
 class="px-3 py-1.5 rounded-lg border border-admin-theme-border text-sm text-admin-theme-text-secondary hover:bg-admin-theme-base disabled:opacity-50"
 >
 {{ previewLoading ? t('Loading...') : t('Randomize Preview') }}
 </button>
 </div>

 <div class="rounded-lg border border-admin-theme-border overflow-hidden">
 <div class="px-4 py-3 bg-admin-theme-base/40 border-b border-admin-theme-border">
 <p class="text-[11px] uppercase tracking-wide text-admin-theme-text-muted font-semibold">{{ t('Subject') }}</p>
 <p class="mt-1 text-sm font-semibold text-admin-theme-text break-words">
 {{ preview.subject || t('No preview yet') }}
 </p>
 </div>
 <div class="px-4 py-4 bg-admin-theme-surface">
 <p class="text-[11px] uppercase tracking-wide text-admin-theme-text-muted font-semibold mb-2">{{ t('Email Body') }}</p>
 <div
 class="email-preview-content text-sm text-gray-800 leading-7"
 v-html="preview.body ||'<p>' + t('No preview yet') +'</p>'"
 ></div>
 </div>
 </div>

 <div class="mt-4 rounded-lg border border-indigo-200 dark:border-indigo-900/40 bg-admin-theme-primary/10 p-3">
 <div class="flex items-center justify-between mb-2">
 <p class="text-[11px] uppercase tracking-wide text-admin-theme-primary font-semibold">{{ t('Preview Data Source') }}</p>
 <span class="text-xs font-semibold text-admin-theme-primary dark:text-admin-theme-primary">{{ sampleVariableCount }} {{ t('variables') }}</span>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-44 overflow-y-auto pr-1">
 <div v-for="(val, key) in preview.sample_data" :key="key" class="rounded bg-admin-theme-surface/80 border border-indigo-100 dark:border-indigo-900/30 px-2 py-1.5">
 <p class="text-[10px] font-mono text-admin-theme-primary break-all">{ {{ key }} }</p>
 <p class="text-xs text-admin-theme-text-secondary mt-0.5 break-all">{{ String(val) }}</p>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Sidebar Info -->
 <div class="xl:col-span-4 space-y-4 xl:sticky xl:top-6">
 <div class="bg-admin-theme-surface rounded-lg shadow p-5">
 <h3 class="font-bold text-admin-theme-text">{{ t('Settings') }}</h3>
 <p class="mt-1 text-xs text-admin-theme-text-muted">
 {{ t('Manage status and save changes for this template.') }}
 </p>

 <div class="mt-4 p-3 rounded-lg border border-admin-theme-border bg-admin-theme-base/40 space-y-3">
 <div class="flex items-center justify-between gap-3">
 <span class="text-xs font-semibold uppercase tracking-wide text-admin-theme-text-muted">{{ t('Status') }}</span>
 <span
 class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
 :class="form.is_active ?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' :'bg-gray-200 text-admin-theme-text-secondary '"
 >
 {{ form.is_active ? t('Enabled') : t('Disabled') }}
 </span>
 </div>
 <FormToggle
 v-model="form.is_active"
 name="is_active"
 :label="t('Enabled')"
 />
 </div>

 <div class="mt-4 grid grid-cols-2 gap-3">
 <div class="rounded-lg border border-indigo-200 dark:border-indigo-900/40 bg-admin-theme-primary/10 p-3">
 <p class="text-[10px] uppercase tracking-wide text-admin-theme-primary font-semibold">{{ t('Template') }}</p>
 <p class="mt-1 text-xs font-mono text-admin-theme-primary dark:text-admin-theme-primary break-all">{{ templateIdentifier }}</p>
 </div>
 <div class="rounded-lg border border-indigo-200 dark:border-indigo-900/40 bg-admin-theme-primary/10 p-3">
 <p class="text-[10px] uppercase tracking-wide text-admin-theme-primary font-semibold">{{ t('Variables') }}</p>
 <p class="mt-1 text-sm font-semibold text-admin-theme-primary dark:text-admin-theme-primary">{{ variableCount }}</p>
 </div>
 </div>

 <button 
 type="submit"
 :disabled="saving"
 class="mt-4 w-full px-6 py-2.5 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover disabled:opacity-50"
 >
 {{ saving ? t('Saving...') : t('Save Template') }}
 </button>
 <p class="mt-2 text-[11px] text-admin-theme-text-muted">
 {{ t('Changes will apply to future outgoing emails.') }}
 </p>
 </div>

 <div v-if="template.variables" class="bg-admin-theme-primary/10/30 rounded-lg p-6">
 <h3 class="font-bold text-admin-theme-primary mb-3 text-sm">{{ t('Available Variables') }}</h3>
 <ul class="space-y-2">
 <li v-for="(desc, variableKey) in template.variables" :key="variableKey" class="flex flex-col gap-1">
 <button 
 type="button"
 @click="insertVariable(isNaN(Number(variableKey)) ? String(variableKey) : String(desc))"
 class="inline-flex items-center w-fit px-2 py-1 rounded bg-admin-theme-surface text-admin-theme-primary dark:text-admin-theme-primary hover:bg-admin-theme-primary hover:text-white dark:hover:bg-admin-theme-primary/100 transition-colors font-mono text-xs border border-indigo-100 dark:border-indigo-900/50"
 :title="t('Click to insert variable')"
 >
 {{ isNaN(Number(variableKey)) ? variableKey : desc }}
 </button>
 <span v-if="isNaN(Number(variableKey))" class="text-[10px] text-admin-theme-text-muted ml-1">{{ desc }}</span>
 </li>
 </ul>
 <p class="mt-4 text-[10px] text-admin-theme-primary dark:text-admin-theme-primary italic">
 {{ t('Note: Variables must be wrapped in single curly braces (e.g. {user_name}).') }}
 </p>
 </div>
 </div>
 </form>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from'vue';
import { useRoute } from'vue-router';
import axios from'axios';
import { useTranslation } from'@/admin/composables/useTranslation';
import { useDialog } from'@/admin/composables/useDialog';
import EmailTiptapEditor from'@/admin/components/EmailTiptapEditor.vue';
import FormToggle from'@/admin/components/forms/FormToggle.vue';

const route = useRoute();
const { t } = useTranslation();
const dialog = useDialog();

const editorRef = ref<any>(null);
const loading = ref(true);
const saving = ref(false);
const previewLoading = ref(false);
const template = ref<any>({});
const preview = ref({
 subject:'',
 body:'',
 sample_data: {} as Record<string, any>,
});
let previewDebounceTimer: ReturnType<typeof setTimeout> | null = null;
const form = ref({
 subject:'',
 body:'',
 is_active: true
});

const loadTemplate = async () => {
 try {
 const response = await axios.get(`/api/v1/email-templates/${route.params.id}`);
 template.value = response.data.data;
 form.value.subject = template.value.subject;
 form.value.body = template.value.body;
 form.value.is_active = template.value.is_active;
 await loadPreview();
 } catch (error) {
 console.error('Error loading template:', error);
 dialog.error(t('Failed to load template'));
 } finally {
 loading.value = false;
 }
};

const saveTemplate = async () => {
 saving.value = true;
 try {
 await axios.put(`/api/v1/email-templates/${route.params.id}`, form.value);
 dialog.success(t('Template saved successfully'));
 } catch (error) {
 console.error('Error saving template:', error);
 dialog.error(t('Failed to save template'));
 } finally {
 saving.value = false;
 }
};

const loadPreview = async () => {
 previewLoading.value = true;
 try {
 const response = await axios.post(`/api/v1/email-templates/${route.params.id}/preview`, {
 subject: form.value.subject,
 body: form.value.body,
 });
 const data = response.data?.data || {};
 preview.value.subject = data.subject ||'';
 preview.value.body = data.body ||'';
 preview.value.sample_data = data.sample_data || {};
 } catch (error) {
 console.error('Error loading preview:', error);
 } finally {
 previewLoading.value = false;
 }
};

const queuePreviewUpdate = () => {
 if (previewDebounceTimer) {
 clearTimeout(previewDebounceTimer);
 }
 previewDebounceTimer = setTimeout(() => {
 loadPreview();
 }, 450);
};

const insertVariable = (variable: string) => {
 if (editorRef.value) {
 editorRef.value.insertContent(`{${variable}}`);
 }
};

const templateIdentifier = computed(() => {
 return template.value?.name || template.value?.code || `#${route.params.id}`;
});

const variableCount = computed(() => {
 if (!template.value?.variables) return 0;
 if (Array.isArray(template.value.variables)) return template.value.variables.length;
 return Object.keys(template.value.variables).length;
});

const sampleVariableCount = computed(() => Object.keys(preview.value.sample_data || {}).length);

watch(
 () => [form.value.subject, form.value.body],
 () => {
 if (loading.value) return;
 queuePreviewUpdate();
 }
);

onMounted(loadTemplate);
</script>

<style scoped>
.email-preview-content :deep(a) {
 color: rgb(var(--admin-theme-primary));
 text-decoration: underline;
 text-underline-offset: 2px;
}

.dark .email-preview-content :deep(a) {
 color: #818cf8;
}
</style>
