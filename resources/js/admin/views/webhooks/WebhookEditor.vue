<template>
    <div class="space-y-6">
        <header class="flex items-center justify-between">
            <div>
                <nav class="flex text-sm text-gray-500 mb-1" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2">
                        <li>
                            <router-link :to="{ name: 'admin.webhooks.index' }" class="hover:text-gray-900 dark:hover:text-white">Webhooks</router-link>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-700 dark:text-gray-300 md:ml-2 font-medium">
                                    {{ isEditing ? 'Edit Webhook' : 'New Webhook' }}
                                </span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ isEditing ? 'Edit Webhook' : 'Create Webhook' }}
                </h1>
            </div>
            <div class="flex gap-3">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    @click="$router.push({ name: 'admin.webhooks.index' })"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    @click="save"
                    :disabled="saving"
                >
                    <span v-if="saving" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Saving...
                    </span>
                    <span v-else>Save Webhook</span>
                </button>
            </div>
        </header>

        <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Form -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Connection Settings</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Webhook Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="form.name"
                                placeholder="e.g. Zapier Order Sync"
                                :class="{'border-red-500': errors.name}"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name[0] }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Payload Endpoint URL <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="url"
                                v-model="form.url"
                                placeholder="https://hooks.zapier.com/..."
                                :class="{'border-red-500': errors.url}"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="errors.url" class="mt-1 text-sm text-red-500">{{ errors.url[0] }}</p>
                            <p class="mt-1 text-xs text-gray-500">The destination URL where PolyCMS will send POST requests.</p>
                        </div>

                        <div v-if="isEditing">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Endpoint Secret (HMAC-SHA256)
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    :value="webhookSecret"
                                    readonly
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-4 py-2 text-gray-500 font-mono text-sm"
                                />
                            </div>
                            <div class="mt-2 text-sm flex items-center justify-between">
                                <p class="text-xs text-gray-500">Used to sign requests inside the <code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded text-indigo-600 dark:text-indigo-400">X-PolyCMS-Signature</code> header.</p>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.regenerate_secret" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4" />
                                    <span class="ml-2 text-xs font-medium text-red-600 dark:text-red-400">Regenerate on save</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Events Selection -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Event Triggers <span class="text-red-500">*</span></h2>
                    <p class="text-sm text-gray-500 mb-4">Select which system events will trigger a notification to this webhook.</p>
                    
                    <p v-if="errors.events" class="mb-4 text-sm text-red-500">{{ errors.events[0] }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label 
                            v-for="event in availableEvents" 
                            :key="event.id"
                            class="relative flex items-start p-4 cursor-pointer rounded-lg border-2 transition-colors"
                            :class="[
                                isSelected(event.id) 
                                    ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20' 
                                    : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]"
                        >
                            <div class="flex items-center h-5">
                                <input 
                                    type="checkbox" 
                                    :value="event.id"
                                    v-model="form.events"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                />
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-gray-900 dark:text-white block">{{ event.label }}</span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs">{{ event.id }}</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Info Status -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Status & Information</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-500">Status</span>
                            <span :class="isEditing ? (form.is_active ? 'text-green-600' : 'text-red-500') : 'text-gray-400'">
                                {{ isEditing ? (form.is_active ? 'Active' : 'Muted') : 'Draft' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700" v-if="isEditing">
                            <span class="text-gray-500">Created At</span>
                            <span class="text-gray-900 dark:text-white">{{ new Date(createdAt).toLocaleDateString() }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-gray-500">Source</span>
                            <span class="text-gray-900 dark:text-white">
                                <span v-if="moduleSlug" class="px-2 py-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded text-xs">
                                    {{ moduleSlug }}
                                </span>
                                <span v-else class="px-2 py-0.5 bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 rounded text-xs">Custom UI</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- API Access Token (Incoming) -->
                <div v-if="isEditing" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">API Access Token (Incoming)</h3>
                    
                    <p class="text-xs text-gray-500 mb-4">
                        Generate a Bearer Token if the external service needs to make REST API calls back into PolyCMS. 
                        <span class="font-semibold text-red-500">Generating a new token will revoke the old one!</span>
                    </p>
                    
                    <button
                        @click="generateApiToken"
                        :disabled="generatingToken"
                        class="w-full justify-center inline-flex items-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/60 disabled:opacity-50 transition-colors text-sm font-medium"
                    >
                        <svg v-if="generatingToken" class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-700 dark:text-indigo-300" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-else>Generate API Token</span>
                    </button>
                </div>
                
                <!-- Tips -->
                <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4 border border-blue-100 dark:border-blue-800">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Retry Mechanism</h3>
                            <p class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                If the endpoint returns a `5xx` error, the system will automatically retry delivery up to 3 times (after 5 minutes, 10 minutes, and 30 minutes).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';

const route = useRoute();
const router = useRouter();
const dialog = useDialog();

const isEditing = computed(() => route.name === 'admin.webhooks.edit');
const loading = ref(isEditing.value);
const saving = ref(false);
const generatingToken = ref(false);
const errors = ref<Record<string, string[]>>({});

const webhookSecret = ref('');
const createdAt = ref('');
const moduleSlug = ref<string | null>(null);

const form = ref({
    name: '',
    url: '',
    events: [] as string[],
    is_active: true,
    regenerate_secret: false,
});

const availableEvents = [
    { id: 'order.created', label: 'Order Created' },
    { id: 'user.registered', label: 'User Registered' },
    { id: 'post.published', label: 'Post Published' },
    { id: 'product.published', label: 'Product Published' },
    { id: 'admin.login.success', label: 'Admin Login Success' },
    { id: 'module.enabled', label: 'Module Enabled' },
    { id: 'theme.activated', label: 'Theme Activated' },
];

const isSelected = (eventId: string) => {
    return form.value.events.includes(eventId);
};

const generateApiToken = async () => {
    if (!await dialog.confirm('This will revoke any existing tokens for this webhook and generate a new one. External services using the old token will lose access immediately. Continue?')) {
        return;
    }
    
    generatingToken.value = true;
    try {
        const { data } = await axios.post(`/api/v1/webhooks/${route.params.id}/token`);
        await dialog.alert({
            title: 'API Token Generated',
            message: `<p class="mb-3">New access token generated successfully. Please copy the token below. You will not be able to see it again.</p><div class="relative"><div class="p-4 bg-gray-100 dark:bg-gray-900 rounded-lg break-all font-mono text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 select-all">${data.token}</div><p class="text-xs text-indigo-600 dark:text-indigo-400 mt-2 text-center">(Double-click or highlight the text above to copy)</p></div>`,
            type: 'success'
        });
    } catch (e: any) {
        dialog.error(e.response?.data?.message || 'Failed to generate token');
    } finally {
        generatingToken.value = false;
    }
};

const loadWebhook = async () => {
    if (!isEditing.value) return;

    try {
        const { data } = await axios.get(`/api/v1/webhooks/${route.params.id}`);
        const webhook = data.data;
        
        form.value = {
            name: webhook.name,
            url: webhook.url,
            events: webhook.events || [],
            is_active: webhook.is_active,
            regenerate_secret: false,
        };
        
        webhookSecret.value = webhook.secret || 'Not configured';
        createdAt.value = webhook.created_at;
        moduleSlug.value = webhook.module_slug;

    } catch (e: any) {
        dialog.error('Failed to load webhook details');
        router.push({ name: 'admin.webhooks.index' });
    } finally {
        loading.value = false;
    }
};

const save = async () => {
    saving.value = true;
    errors.value = {};

    try {
        if (isEditing.value) {
            await axios.put(`/api/v1/webhooks/${route.params.id}`, form.value);
            dialog.success('Webhook updated successfully');
            await loadWebhook(); // Refresh data
        } else {
            const res = await axios.post('/api/v1/webhooks', form.value);
            dialog.success('Webhook created successfully');
            // Redirect to edit page of the newly created webhook
            if (res.data.data?.id) {
                router.push({ name: 'admin.webhooks.edit', params: { id: res.data.data.id } });
            }
        }
    } catch (e: any) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
            dialog.error('Please fix validation errors');
        } else {
            dialog.error(e.response?.data?.message || 'Failed to save webhook');
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadWebhook();
});
</script>
