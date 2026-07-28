<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <div class="flex items-center gap-3">
          <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Market Integration') }}</h1>
          
          <HelpGuide :title="t('Market Integration Help Guide')">
            <div class="space-y-4">
              <p>{{ t('This module allows you to integrate third-party marketplaces with your site to synchronize sales figures and verify buyer license claims.') }}</p>
              
              <!-- Envato Accordion -->
              <div class="border border-admin-theme-border rounded-xl overflow-hidden">
                <div class="bg-admin-theme-base/5 p-3 font-bold text-sm text-admin-theme-text border-b border-admin-theme-border flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  {{ t('Envato Market Configuration') }}
                </div>
                <div class="p-4 space-y-3 text-xs leading-relaxed">
                  <p>{{ t('To connect to Envato (sync sales count and verify customer purchase codes), you need to create an Envato Personal Token with the minimum required permissions.') }}</p>
                  <h4 class="font-bold text-admin-theme-text mt-2">{{ t('Steps to generate your Personal Token:') }}</h4>
                  <ol class="list-decimal pl-5 space-y-2">
                    <li>
                      {{ t('Go to the Envato Token creation page:') }}
                      <a href="https://build.envato.com/create-token" target="_blank" class="text-admin-theme-primary hover:underline font-bold inline-flex items-center gap-1">
                        Envato Build Platform
                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                      </a>
                    </li>
                    <li>{{ t('Log in with your Envato Author account.') }}</li>
                    <li>{{ t('Name your token (e.g. PolyCMS Market Sync).') }}</li>
                    <li>
                      {{ t('Under the "Permissions needed" section, check exactly these 3 permissions:') }}
                      <ul class="list-disc pl-5 mt-1 space-y-1 font-semibold text-emerald-600 dark:text-emerald-400">
                        <li>{{ t('View and search Envato sites') }}</li>
                        <li>{{ t("View the user's Envato Account username") }}</li>
                        <li>{{ t("Verify purchases of the user's items") }}</li>
                      </ul>
                    </li>
                    <li>{{ t('Agree to the terms and click "Create Token".') }}</li>
                    <li>{{ t('Copy the generated Token (shown only once) and paste it into the API Key input.') }}</li>
                  </ol>
                </div>
              </div>

              <!-- Other Platforms -->
              <div class="border border-admin-theme-border rounded-xl overflow-hidden">
                <div class="bg-admin-theme-base/5 p-3 font-bold text-sm text-admin-theme-text border-b border-admin-theme-border flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                  {{ t('Other Platforms & Custom Connections') }}
                </div>
                <div class="p-4 space-y-2 text-xs leading-relaxed">
                  <p>{{ t('You can add custom integrations by defining the specific API endpoint, credentials, and API configuration (JSON) parameters.') }}</p>
                  <p>{{ t('For each platform, make sure to specify a unique Platform Code (e.g. appsumo, custom_market) and verify that the sync command scheduler is configured.') }}</p>
                </div>
              </div>
            </div>
          </HelpGuide>
        </div>
        <p class="text-sm text-admin-theme-text-secondary mt-1">
          {{ t('Configure external marketplaces to sync sales data and verify licenses.') }}
        </p>
      </div>

      <!-- Language Selector -->
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

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12 bg-admin-theme-surface rounded-lg shadow border border-admin-theme-border">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
      <p class="mt-2 text-admin-theme-text-secondary">{{ t('Loading settings...') }}</p>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Header Note -->
      <div class="bg-sky-50 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900/40 rounded-xl p-4 flex gap-3 text-sm text-sky-800 dark:text-sky-300">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>{{ t('Note: Internal Store platform is auto-managed and tracks sales directly from your site\'s licenses. Only configure external platforms here.') }}</p>
      </div>

      <!-- Platforms List -->
      <div v-for="(platform, index) in platformsList" :key="platform.id || index" class="bg-admin-theme-surface border border-admin-theme-border shadow rounded-xl p-6 relative transition-all duration-200 hover:shadow-md">
        <!-- Grid Row 1 -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
          <div class="md:col-span-3">
            <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">{{ t('Platform Name') }}</label>
            <input
              type="text"
              v-model="platform.name"
              placeholder="e.g. Envato Market"
              class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
            />
          </div>
          <div class="md:col-span-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">{{ t('Platform Code') }}</label>
            <input
              type="text"
              v-model="platform.code"
              placeholder="e.g. envato"
              class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium font-mono"
            />
          </div>
          <div class="md:col-span-4">
            <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">{{ t('API Endpoint') }}</label>
            <input
              type="text"
              v-model="platform.endpoint"
              placeholder="e.g. https://api.envato.com"
              class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
            />
          </div>
          <div class="md:col-span-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">{{ t('Sync Interval') }}</label>
            <select
              v-model="platform.sync_interval"
              class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium cursor-pointer"
            >
              <option value="30">{{ t('30 minutes') }}</option>
              <option value="60">{{ t('1 hour') }}</option>
              <option value="720">{{ t('12 hours') }}</option>
              <option value="1440">{{ t('Daily') }}</option>
            </select>
          </div>
          <div class="md:col-span-1 flex items-end justify-end">
            <button
              type="button"
              @click="removePlatform(index)"
              class="p-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-500 rounded-lg border border-red-500/20 hover:border-red-500/30 transition-colors cursor-pointer"
              :title="t('Delete Platform')"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Grid Row 2 -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
          <div class="md:col-span-5">
            <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">{{ t('API Key') }}</label>
            <input
              type="password"
              v-model="platform.api_key"
              placeholder="••••••••••••••••••••••••••••••••••••••••"
              class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
            />
            <p class="text-[10px] text-admin-theme-text-muted mt-1">{{ t('API key will be encrypted when saved') }}</p>
          </div>
          <div class="md:col-span-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">{{ t('Status') }}</label>
            <select
              v-model="platform.status"
              class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium cursor-pointer"
            >
              <option value="active">{{ t('Active') }}</option>
              <option value="inactive">{{ t('Inactive') }}</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">{{ t('Sort Order') }}</label>
            <input
              type="number"
              v-model.number="platform.sort_order"
              placeholder="1"
              class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-medium"
            />
          </div>
          <div class="md:col-span-3 flex items-end">
            <button
              type="button"
              @click="testConnection(platform)"
              :disabled="testingStates[platform.code] || !platform.api_key || !platform.endpoint"
              class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer text-sm font-bold shadow-sm"
            >
              <span v-if="testingStates[platform.code]" class="flex items-center justify-center">
                <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-1.5"></div>
                {{ t('Testing...') }}
              </span>
              <span v-else>{{ t('Test Connection') }}</span>
            </button>
          </div>
        </div>

        <!-- Grid Row 3 (API Configuration) -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">{{ t('API Configuration') }}</label>
          <textarea
            v-model="platform.api_config"
            rows="3"
            placeholder='{"headers":{"User-Agent":"PolyCMS Market Integration Module"},"timeout":30,"retry_attempts":3}'
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-2 focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm font-mono"
          ></textarea>
          <p class="text-[10px] text-admin-theme-text-muted mt-1">{{ t('JSON configuration for API calls (timeout, headers, etc.)') }}</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="platformsList.length === 0" class="text-center py-12 bg-admin-theme-surface border border-dashed border-admin-theme-border rounded-xl">
        <svg class="w-12 h-12 mx-auto text-admin-theme-text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <p class="text-sm text-admin-theme-text-secondary font-medium">{{ t('No external sales platforms configured yet.') }}</p>
      </div>

      <!-- Actions Row -->
      <div class="flex flex-col gap-2 pt-2 pb-16">
        <div>
          <button
            type="button"
            @click="addPlatform"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors cursor-pointer text-sm font-bold shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ t('Add Platform') }}
          </button>
        </div>
        <p class="text-[11px] text-admin-theme-text-muted">{{ t('Configure external sales platforms to automatically sync sales data. Each platform can have different sync intervals.') }}</p>
      </div>

      <!-- Floating Actions -->
      <div class="editor-floating-actions" style="right: 32px">
        <button 
          type="button" 
          class="editor-floating-actions__primary" 
          :disabled="saving" 
          @click="saveSettings"
          :title="saving ? t('Saving...') : t('Save Settings')"
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../../composables/useTranslation';
import { useDialog } from '../../../composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();

const loading = ref(false);
const saving = ref(false);
const selectedLocale = ref<string>('');
const languages = ref<any[]>([]);
const platformsList = ref<any[]>([]);
const testingStates = ref<Record<string, boolean>>({});

// Load languages and settings on mount
onMounted(async () => {
  await loadLanguages();
  await loadSettings();
});

const loadLanguages = async () => {
  try {
    const response = await axios.get('/api/v1/languages');
    languages.value = response.data?.data || [];
    const defaultLang = languages.value.find((l: any) => l.is_default) || languages.value[0];
    if (defaultLang) {
      selectedLocale.value = defaultLang.code;
    }
  } catch (error) {
    console.error('Failed to load languages:', error);
  }
};

const loadSettings = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/settings/group/market_integration', {
      params: { locale: selectedLocale.value }
    });
    const settingsData = response.data.data || {};
    
    // Parse platforms list from settingsData
    const platformsSetting = settingsData['market_integration_platforms'];
    let list: any[] = [];
    if (platformsSetting) {
      const val = platformsSetting.value;
      if (typeof val === 'string') {
        try {
          list = JSON.parse(val);
        } catch (e) {
          list = [];
        }
      } else if (Array.isArray(val)) {
        list = val;
      }
    }
    platformsList.value = list;
  } catch (error) {
    console.error('Error loading settings:', error);
    dialog.error(t('Failed to load settings'));
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    // Basic validation of JSON configs
    for (const platform of platformsList.value) {
      if (platform.api_config && typeof platform.api_config === 'string') {
        try {
          JSON.parse(platform.api_config);
        } catch (e) {
          dialog.error(t('API Configuration for platform {name} is not valid JSON.', { name: platform.name }));
          saving.value = false;
          return;
        }
      }
    }

    await axios.put('/api/v1/settings/group/market_integration', {
      settings: {
        market_integration_platforms: JSON.stringify(platformsList.value),
      }
    }, {
      params: { locale: selectedLocale.value }
    });

    dialog.success(t('Settings saved successfully!'));
  } catch (error) {
    console.error('Failed to save settings:', error);
    dialog.error(t('Failed to save settings'));
  } finally {
    saving.value = false;
  }
};

const addPlatform = () => {
  platformsList.value.push({
    id: String(Date.now()),
    name: '',
    code: '',
    endpoint: '',
    sync_interval: '1440',
    api_key: '',
    status: 'inactive',
    sort_order: platformsList.value.length + 1,
    api_config: JSON.stringify({
      headers: {
        'User-Agent': 'PolyCMS Market Integration Module',
      },
      timeout: 30,
      retry_attempts: 3,
    }, null, 2),
  });
};

const removePlatform = (index: number) => {
  platformsList.value.splice(index, 1);
};

const testConnection = async (platform: any) => {
  const code = platform.code || '';
  if (!code) return;

  testingStates.value[code] = true;
  try {
    const response = await axios.post('/api/v1/market/test-connection', {
      code: platform.code,
      api_key: platform.api_key,
      endpoint: platform.endpoint,
    });
    
    if (response.data?.success) {
      dialog.success(response.data.message || t('Connected successfully!'));
    } else {
      dialog.error(response.data?.message || t('Connection failed.'));
    }
  } catch (error: any) {
    console.error('Test connection error:', error);
    const msg = error.response?.data?.message || error.message || t('Connection error.');
    dialog.error(msg);
  } finally {
    testingStates.value[code] = false;
  }
};
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

