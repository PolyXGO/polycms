<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('Settings') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $t('Manage your site settings') }}</p>
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
                <!-- General Settings Tab -->
                <GeneralSettings
                    v-if="activeTab === 'general'"
                    :settings="settings.general || {}"
                    :saving="saving"
                    @update="updateSettings"
                    @save="saveSettings"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, getCurrentInstance } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import GeneralSettings from './tabs/GeneralSettings.vue';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

interface Setting {
    key: string;
    value: any;
    type: string;
    label: string;
    description: string;
}

interface SettingsData {
    [group: string]: {
        [key: string]: Setting;
    };
}

interface Tab {
    id: string;
    label: string;
    component?: string;
}

const dialog = useDialog();

const loading = ref(false);
const saving = ref(false);
const activeTab = ref<string>('general');
const settings = ref<SettingsData>({});

// Tabs configuration - can be extended
const tabs = computed<Tab[]>(() => [
    {
        id: 'general',
        label: $t('General'),
    },
    // Add more tabs here in the future
    // {
    //     id: 'reading',
    //     label: 'Reading',
    // },
    // {
    //     id: 'writing',
    //     label: 'Writing',
    // },
]);

const loadSettings = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/settings');
        settings.value = response.data.data || {};
        
        // Initialize default settings if empty
        if (!settings.value.general || Object.keys(settings.value.general).length === 0) {
            await initializeDefaults();
        }
    } catch (error: any) {
        console.error('Error loading settings:', error);
        dialog.error($t('Failed to load settings'));
    } finally {
        loading.value = false;
    }
};

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
