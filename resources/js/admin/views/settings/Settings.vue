<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <!-- Group Breadcrumbs -->
                <div v-if="props.group" class="mb-2 flex items-center gap-4">
                    <router-link :to="{ name: 'admin.settings.index' }" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        {{ t('Back to Hub') }}
                    </router-link>
                </div>
                
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ props.group ? tabs[0]?.label : $t('Settings') }}</h1>
                </div>
                <p v-if="!props.group" class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $t('Manage your site settings') }}</p>
            </div>
        </div>

        <div v-if="loading" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $t('Loading settings...') }}</p>
        </div>

        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === tab.id
                                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
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
import { ref, onMounted, computed, watch, getCurrentInstance } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import GeneralSettings from './tabs/GeneralSettings.vue';
import PermalinkSettings from './tabs/PermalinkSettings.vue';
import EcommerceSettings from './tabs/EcommerceSettings.vue';
import EmailSettings from './tabs/EmailSettings.vue';
import ReadingSettings from './tabs/ReadingSettings.vue';
import RefundPolicySettings from './tabs/RefundPolicySettings.vue';
import GlobalFaqSettings from './tabs/GlobalFaqSettings.vue';
import GlobalTabsSettings from './tabs/GlobalTabsSettings.vue';
import TemplateDefaultsSettings from './tabs/TemplateDefaultsSettings.vue';
import MTOptimizeSettings from './tabs/MTOptimizeSettings.vue';
import MediaSettings from './tabs/MediaSettings.vue';
import DynamicSettings from './tabs/DynamicSettings.vue';
import AuthAppearanceSettings from './tabs/AuthAppearanceSettings.vue';

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
const activeTab = ref<string>(props.group || 'general');
const settings = ref<SettingsData>({});

const availableTabs: Record<string, Tab & { module?: string }> = {
    general: { id: 'general', label: $t('General'), component: GeneralSettings },
    permalinks: { id: 'permalinks', label: $t('Permalinks'), component: PermalinkSettings },
    email: { id: 'email', label: $t('Email'), component: EmailSettings },
    ecommerce: { id: 'ecommerce', label: $t('Ecommerce'), component: EcommerceSettings },
    refund_policy: { id: 'refund_policy', label: $t('Refund Policy'), component: RefundPolicySettings },
    global_faqs: { id: 'global_faqs', label: $t("Global FAQ's"), component: GlobalFaqSettings },
    global_tabs: { id: 'global_tabs', label: $t('Global Tabs'), component: GlobalTabsSettings },
    reading: { id: 'reading', label: $t('Reading'), component: ReadingSettings },
    media: { id: 'media', label: $t('Media'), component: MediaSettings },
    template_defaults: { id: 'template_defaults', label: $t('Template Defaults'), component: TemplateDefaultsSettings },
    auth_appearance: { id: 'auth_appearance', label: $t('Login Appearance'), component: AuthAppearanceSettings },
    mtoptimize: { id: 'mtoptimize', label: $t('MTOptimize'), component: MTOptimizeSettings, module: 'Polyx.MTOptimize' },
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
        const label = props.group.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
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

const loadSettings = async () => {
    const targetTab = availableTabs[props.group || ''];
    if (props.group && targetTab && targetTab.module && !activeModules.includes(targetTab.module)) {
        router.push({ name: 'admin.settings.index' });
        return;
    }

    loading.value = true;
    try {
        const groupParam = props.group || activeTab.value;
        const url = props.group ? `/api/v1/settings/group/${props.group}` : '/api/v1/settings';
        const response = await axios.get(url);
        
        if (props.group) {
            settings.value[props.group] = response.data.data || {};
        } else {
            settings.value = response.data.data || {};
        }
        
        // Initialize default settings if empty
        if (!settings.value.general || Object.keys(settings.value.general).length === 0) {
            if (!props.group || props.group === 'general') {
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

onMounted(() => {
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
    background-color: var(--color-indigo-600, #4f46e5);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4), 0 8px 10px -6px rgba(79, 70, 229, 0.3);
    border: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.editor-floating-actions__primary:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 14px 28px -5px rgba(79, 70, 229, 0.5), 0 10px 10px -6px rgba(79, 70, 229, 0.4);
    background-color: var(--color-indigo-700, #4338ca);
}

.editor-floating-actions__primary:active {
    transform: translateY(0) scale(0.95);
    box-shadow: 0 6px 16px -5px rgba(79, 70, 229, 0.4);
}

.editor-floating-actions__primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
</style>
