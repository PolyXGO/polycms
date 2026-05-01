<template>
    <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>
    <div v-else>
        <!-- Email Configuration Form -->
        <form @submit.prevent="saveSettings" class="space-y-8">
            <!-- Common Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">{{ t('Sender Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ settings['email_from_name']?.label || t('Sender Name') }}
                        </label>
                        <input
                            v-model="localSettings['email_from_name']"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ settings['email_from_address']?.label || t('Sender Email') }}
                        </label>
                        <input
                            v-model="localSettings['email_from_address']"
                            type="email"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Protocol Selection -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">{{ t('Mail Engine') }}</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center space-x-6">
                        <label 
                            v-for="(protocol, key) in protocols" 
                            :key="key"
                            class="flex items-center cursor-pointer"
                        >
                            <input
                                type="radio"
                                name="email_driver"
                                :value="key"
                                v-model="localSettings['email_driver']"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ protocol.label }}</span>
                        </label>
                    </div>
                </div>

                <!-- Dynamic Protocol Fields -->
                <div v-if="currentProtocolFields.length > 0" class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                        {{ protocols[localSettings['email_driver']]?.label }} {{ t('Configuration') }}
                    </h4>
                    
                    <div class="space-y-6">
                        <div v-for="field in currentProtocolFields" :key="field.key">
                            <label :for="field.key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ field.label }}
                            </label>

                            <!-- Select -->
                            <select
                                v-if="field.type === 'select'"
                                :id="field.key"
                                v-model="localSettings[field.key]"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option v-for="opt in field.options" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>

                            <!-- Password -->
                            <input
                                v-else-if="field.type === 'password'"
                                :id="field.key"
                                v-model="localSettings[field.key]"
                                type="password"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                            />

                            <!-- Number -->
                            <input
                                v-else-if="field.type === 'number'"
                                :id="field.key"
                                v-model="localSettings[field.key]"
                                type="number"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                            />

                            <!-- Readonly (Computed/Static) -->
                            <div v-else-if="field.type === 'readonly'" class="relative">
                                <input
                                    :id="field.key"
                                    :value="field.value || localSettings[field.key]"
                                    readonly
                                    type="text"
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-500 dark:text-gray-400 focus:ring-indigo-500 focus:border-indigo-500 cursor-not-allowed"
                                />
                                <button 
                                    type="button"
                                    @click="copyToClipboard(field.value || localSettings[field.key])"
                                    class="absolute right-2 top-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                </button>
                            </div>

                            <!-- Info/HTML -->
                            <div v-else-if="field.type === 'info'" class="prose dark:prose-invert text-sm text-gray-600 dark:text-gray-400" v-html="field.content"></div>
                            
                            <!-- Alert -->
                            <div v-else-if="field.type === 'alert'" class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                                <div class="flex">
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700 dark:text-yellow-200" v-html="field.content"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- OAuth Connect Button -->
                            <div v-else-if="field.type === 'oauth_connect'" class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
                                <p class="text-sm text-blue-700 dark:text-blue-200 mb-3" v-html="field.content"></p>
                                <button 
                                    v-if="localSettings['email_google_client_id'] && localSettings['email_google_client_secret']"
                                    type="button"
                                    @click="initiateOAuth(field.url)"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/></svg>
                                    {{ field.label || 'Connect' }}
                                </button>
                                <button
                                    v-else
                                    disabled
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-300 dark:bg-blue-700 cursor-not-allowed"
                                >
                                    {{ t('Save settings to connect') }}
                                </button>
                            </div>

                            <!-- Text (Default) -->
                            <input
                                v-else
                                :id="field.key"
                                v-model="localSettings[field.key]"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            
                            <p v-if="field.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400" v-html="field.description"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4">
                <button
                    type="button"
                    @click="showTestModal = true"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ t('Send Test Email') }}
                </button>

                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
                >
                    <span v-if="saving" class="flex items-center">
                        <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        {{ t('Saving...') }}
                    </span>
                    <span v-else>{{ t('Save Changes') }}</span>
                </button>
            </div>
        </form>

        <!-- Test Email Modal -->
        <div v-if="showTestModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showTestModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    {{ t('Send Test Email') }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        {{ t('Enter an email address to send a test email using the current configuration.') }}
                                    </p>
                                    <div>
                                        <label for="test_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ t('Recipient Email') }}
                                        </label>
                                        <input
                                            id="test_email"
                                            v-model="testEmail"
                                            type="email"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="you@example.com"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            @click="sendTestEmail"
                            :disabled="sendingTest || !testEmail"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="sendingTest" class="flex items-center">
                                <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                                {{ t('Sending...') }}
                            </span>
                            <span v-else>{{ t('Send') }}</span>
                        </button>
                        <button
                            type="button"
                            @click="showTestModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ t('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../../composables/useTranslation';
import { useDialog } from '../../../composables/useDialog';

const props = defineProps<{
    settings: any;
    saving: boolean;
    group: string;
}>();

const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const { t } = useTranslation();
const dialog = useDialog();

const localSettings = ref<Record<string, any>>({});
const protocols = ref<Record<string, any>>({});
const loading = ref(true);
const showTestModal = ref(false);
const testEmail = ref('');
const sendingTest = ref(false);

// Initialize local settings from props
watch(() => props.settings, (newSettings) => {
    if (newSettings) {
        Object.keys(newSettings).forEach(key => {
            localSettings.value[key] = newSettings[key].value;
        });
    }
}, { immediate: true, deep: true });

// Sync changes back to parent
watch(localSettings, (newVal) => {
    Object.keys(newVal).forEach(key => {
        emit('update', props.group, key, newVal[key]);
    });
}, { deep: true });

const fetchProtocols = async () => {
    try {
        const response = await axios.get('/api/v1/email/protocols');
        protocols.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch protocols', error);
        dialog.error(t('Failed to load mail protocols'));
    } finally {
        loading.value = false;
    }
};

const currentProtocolFields = computed(() => {
    const driver = localSettings.value['email_driver'];
    if (!driver || !protocols.value[driver]) return [];
    
    return protocols.value[driver].fields || [];
});

const saveSettings = () => {
    emit('save');
};

const sendTestEmail = async () => {
    if (!testEmail.value) return;
    
    sendingTest.value = true;
    try {
        await axios.post('/api/v1/email/test', {
            email: testEmail.value,
            driver: localSettings.value['email_driver'],
            settings: localSettings.value
        });
        
        dialog.success(t('Test email sent successfully!'));
        showTestModal.value = false;
    } catch (error: any) {
        console.error('Failed to send test email', error);
        const message = error.response?.data?.message || t('Failed to send test email');
        dialog.error(message);
    } finally {
        sendingTest.value = false;
    }
};

const copyToClipboard = (text: string) => {
    if (!text) return;
    navigator.clipboard.writeText(text);
    dialog.success(t('Copied to clipboard'));
};

const initiateOAuth = async (url: string) => {
    try {
        const response = await axios.get(url);
        if (response.data && response.data.url) {
            window.location.href = response.data.url;
        }
    } catch (error: any) {
        console.error('OAuth initiation failed', error);
        const errorMsg = error.response && error.response.data && error.response.data.message
            ? error.response.data.message 
            : t('Failed to initiate connection. Please save settings and try again.');
        dialog.error(errorMsg);
    }
};

onMounted(() => {
    fetchProtocols();
});
</script>
