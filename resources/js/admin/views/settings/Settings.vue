<template>
 <div>
 <div class="flex justify-between items-center mb-6">
 <div>
 <!-- Group Breadcrumbs -->
 <div v-if="props.group" class="mb-2 flex items-center gap-4">
 <router-link :to="{ name:'admin.settings.index' }" class="text-admin-theme-primary hover:text-admin-theme-primary-hover font-medium flex items-center text-sm">
 <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
 </svg>
 {{ t('Back to Hub') }}
 </router-link>
 </div>
 
 <div class="flex items-center gap-3">
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ props.group ? tabs[0]?.label : $t('Settings') }}</h1>
 </div>
 <p v-if="!props.group" class="text-sm text-admin-theme-text-secondary mt-1">{{ $t('Manage your site settings') }}</p>
 </div>

 <!-- Language Selector Dropdown -->
 <div v-if="languages.length > 1" class="flex items-center gap-2">
 <span class="text-sm text-admin-theme-text-secondary font-medium">{{ t('Language') }}:</span>
 <select 
 v-model="selectedLocale" 
 @change="loadSettings" 
 class="bg-admin-theme-surface border border-admin-theme-border text-admin-theme-text text-sm rounded-lg focus:ring-admin-theme-primary focus:border-admin-theme-primary block p-2.5 transition-colors cursor-pointer"
 >
 <option v-for="lang in languages" :key="lang.code" :value="lang.code">
 {{ lang.name }} ({{ lang.native_name }})
 </option>
 </select>
 </div>
 </div>

 <div v-if="loading" class="text-center py-12 bg-admin-theme-surface rounded-lg shadow">
 <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
 <p class="mt-2 text-admin-theme-text-secondary">{{ $t('Loading settings...') }}</p>
 </div>

 <div v-else class="bg-admin-theme-surface rounded-lg shadow">
 <!-- Tabs Navigation -->
 <div class="border-b border-admin-theme-border">
 <nav class="flex -mb-px" aria-label="Tabs">
 <button
 v-for="tab in tabs"
 :key="tab.id"
 @click="activeTab = tab.id"
 :class="[
'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
 activeTab === tab.id
 ?'border-admin-theme-primary text-admin-theme-primary dark:text-admin-theme-primary'
 :'border-transparent text-admin-theme-text-muted hover:text-gray-700 hover:border-gray-300 dark:text-admin-theme-text-muted dark:hover:text-gray-300',
 ]"
 >
 {{ tab.label }}
 </button>
 </nav>
 </div>

 <!-- Tab Content -->
 <div class="p-6">

 <component 
 :is="activeTabComponent"
 v-if="activeTabComponent && settings[activeTab]"
 :settings="settings[activeTab]"
 :saving="saving"
 :group="activeTab"
 @update="updateSettings"
 @save="saveSettings"
 />
 </div>
 </div>

 <!-- Floating Actions -->
 <div class="editor-floating-actions" style="right: 32px">
 <button 
 type="button" 
 class="editor-floating-actions__primary" 
 :disabled="loading || saving" 
 @click="saveSettings"
 :title="saving ? $t('Saving...') : $t('Save Settings')"
 >
 <svg v-if="!saving" class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M19 21H5C4.44772 21 4 20.5523 4 20V4C4 3.44772 4.44772 3 5 3H16L20 7V20C20 20.5523 19.5523 21 19 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
 <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
 <path d="M7 3V8H15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
 </svg>
 <svg v-else class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
 </svg>
 </button>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch, getCurrentInstance } from'vue';
import { useRouter } from'vue-router';
import axios from'axios';
import { useDialog } from'../../composables/useDialog';
import { useTranslation } from'../../composables/useTranslation';
import GeneralSettings from'./tabs/GeneralSettings.vue';
import PermalinkSettings from'./tabs/PermalinkSettings.vue';
import EcommerceSettings from'./tabs/EcommerceSettings.vue';
import EmailSettings from'./tabs/EmailSettings.vue';
import ReadingSettings from'./tabs/ReadingSettings.vue';
import RefundPolicySettings from'./tabs/RefundPolicySettings.vue';
import GlobalFaqSettings from'./tabs/GlobalFaqSettings.vue';
import GlobalTabsSettings from'./tabs/GlobalTabsSettings.vue';
import TemplateDefaultsSettings from'./tabs/TemplateDefaultsSettings.vue';
import MTOptimizeSettings from'./tabs/MTOptimizeSettings.vue';
import MediaSettings from'./tabs/MediaSettings.vue';
import DynamicSettings from'./tabs/DynamicSettings.vue';
import AuthAppearanceSettings from'./tabs/AuthAppearanceSettings.vue';
import AdminAppearanceSettings from'./tabs/AdminAppearanceSettings.vue';
import SocialsSettings from'./tabs/SocialsSettings.vue';
import ExternalAuthSettings from './tabs/ExternalAuthSettings.vue';


interface Setting {
 key: string;
 value: any;
 type: string;
 label: string;
 description: string;
 options?: { label: string; value: any }[];
}

interface SettingsData {
 [group: string]: {
 [key: string]: Setting;
 };
}

interface Tab {
 id: string;
 label: string;
 component?: any;
}

const props = defineProps<{
 group?: string;
}>();

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const dialog = useDialog();
const router = useRouter();

const loading = ref(false);
const saving = ref(false);
const activeTab = ref<string>(props.group ||'general');
const settings = ref<SettingsData>({});
const languages = ref<any[]>([]);
const selectedLocale = ref<string>('');

const availableTabs: Record<string, Tab & { module?: string }> = {
 general: { id:'general', label: $t('General'), component: GeneralSettings },
 permalinks: { id:'permalinks', label: $t('Permalinks'), component: PermalinkSettings },
 email: { id:'email', label: $t('Email'), component: EmailSettings },
 ecommerce: { id:'ecommerce', label: $t('Ecommerce'), component: EcommerceSettings },
 refund_policy: { id:'refund_policy', label: $t('Refund Policy'), component: RefundPolicySettings },
 global_faqs: { id:'global_faqs', label: $t("Global FAQ's"), component: GlobalFaqSettings },
 global_tabs: { id:'global_tabs', label: $t('Global Tabs'), component: GlobalTabsSettings },
 reading: { id:'reading', label: $t('Reading'), component: ReadingSettings },
 media: { id:'media', label: $t('Media'), component: MediaSettings },
 template_defaults: { id:'template_defaults', label: $t('Template Defaults'), component: TemplateDefaultsSettings },
 auth_appearance: { id:'auth_appearance', label: $t('Login Appearance'), component: AuthAppearanceSettings },
 admin_appearance: { id:'admin_appearance', label: $t('Admin Appearance'), component: AdminAppearanceSettings },
 mtoptimize: { id:'mtoptimize', label: $t('MTOptimize'), component: MTOptimizeSettings, module:'Polyx.MTOptimize' },
 socials: { id: 'socials', label: $t('Socials'), component: SocialsSettings },
 external_auth: { id: 'external_auth', label: $t('External Auth'), component: ExternalAuthSettings, module: 'Polyx.ExternalAuth' },
};

const activeModules = (window as any).polycmsActiveModules || [];

const tabs = computed<Tab[]>(() => {
 if (props.group) {
 const targetTab = availableTabs[props.group];
 if (targetTab) {
 if (targetTab.module && !activeModules.includes(targetTab.module)) {
 // If the module is disabled, do not include its tab
 } else {
 return [targetTab];
 }
 }
 // Fallback for Dynamic Modules Group
 const label = props.group.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join('');
 return [{
 id: props.group,
 label: label,
 component: DynamicSettings
 }];
 }
 return [availableTabs.general, availableTabs.permalinks];
});

const activeTabComponent = computed(() => {
 return tabs.value.find(t => t.id === activeTab.value)?.component || availableTabs.general.component;
});

const loadLanguages = async () => {
  try {
    const response = await axios.get('/api/v1/languages');
    languages.value = response.data?.data || [];
    const defaultLang = languages.value.find((l: any) => l.is_default) || languages.value[0];
    if (defaultLang && !selectedLocale.value) {
      selectedLocale.value = defaultLang.code;
    }
  } catch (error) {
    console.error('Failed to load languages:', error);
  }
};

const loadSettings = async () => {
 const targetTab = availableTabs[props.group ||''];
 if (props.group && targetTab && targetTab.module && !activeModules.includes(targetTab.module)) {
 router.push({ name:'admin.settings.index' });
 return;
 }

 loading.value = true;
 try {
 const groupParam = props.group || activeTab.value;
 const url = props.group ? `/api/v1/settings/group/${props.group}` :'/api/v1/settings';
 const response = await axios.get(url, {
   params: { locale: selectedLocale.value }
 });
 
 if (props.group) {
 settings.value[props.group] = response.data.data || {};
 } else {
 settings.value = response.data.data || {};
 }
 
 // Initialize default settings if empty
 if (!settings.value.general || Object.keys(settings.value.general).length === 0) {
 if (!props.group || props.group ==='general') {
 await initializeDefaults();
 }
 }
 } catch (error: any) {
 console.error('Error loading settings:', error);
 dialog.error($t('Failed to load settings'));
 } finally {
 loading.value = false;
 }
};

watch(() => props.group, (newGroup) => {
 if (newGroup) {
 activeTab.value = newGroup;
 loadSettings();
 }
});

const initializeDefaults = async () => {
 try {
 await axios.post('/api/v1/settings/initialize');
 await loadSettings();
 } catch (error: any) {
 console.error('Error initializing defaults:', error);
 }
};

const updateSettings = (group: string, key: string, value: any) => {
 if (!settings.value[group]) {
 settings.value[group] = {};
 }
 if (!settings.value[group][key]) {
 settings.value[group][key] = {} as Setting;
 }
 settings.value[group][key].value = value;
};

const saveSettings = async () => {
 saving.value = true;
 try {
 const group = activeTab.value;
 const groupSettings = settings.value[group] || {};
 
 // Prepare settings data for API
 const settingsData: Record<string, any> = {};
 Object.keys(groupSettings).forEach(key => {
 settingsData[key] = groupSettings[key].value;
 });

 await axios.put(`/api/v1/settings/group/${group}`, {
 settings: settingsData,
 }, {
   params: { locale: selectedLocale.value }
 });

 dialog.success($t('Settings saved successfully'));
 await loadSettings();
 } catch (error: any) {
 console.error('Error saving settings:', error);
 const message = error.response?.data?.message || $t('Failed to save settings');
 dialog.error(message);
 } finally {
 saving.value = false;
 }
};

onMounted(async () => {
 await loadLanguages();
 loadSettings();
});
</script>

<style scoped>
.editor-floating-actions {
 position: fixed;
 bottom: 32px;
 right: 32px;
 z-index: 50;
 display: flex;
 gap: 12px;
 align-items: center;
 transition: right 0.3s ease;
}

.editor-floating-actions__primary {
 width: 60px;
 height: 60px;
 border-radius: 50%;
 background-color: rgb(var(--admin-theme-primary));
 color: rgb(var(--admin-theme-primary-content));
 display: flex;
 align-items: center;
 justify-content: center;
 box-shadow: 0 10px 25px -5px rgb(var(--admin-theme-primary) / 0.4), 0 8px 10px -6px rgb(var(--admin-theme-primary) / 0.3);
 border: none;
 cursor: pointer;
 transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.editor-floating-actions__primary:hover {
 transform: translateY(-4px) scale(1.05);
 box-shadow: 0 14px 28px -5px rgb(var(--admin-theme-primary) / 0.5), 0 10px 10px -6px rgb(var(--admin-theme-primary) / 0.4);
 background-color: rgb(var(--admin-theme-primary-hover));
}

.editor-floating-actions__primary:active {
 transform: translateY(0) scale(0.95);
 box-shadow: 0 6px 16px -5px rgb(var(--admin-theme-primary) / 0.4);
}

.editor-floating-actions__primary:disabled {
 opacity: 0.6;
 cursor: not-allowed;
 transform: none;
}
</style>
