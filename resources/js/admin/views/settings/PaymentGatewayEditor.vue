<template>
    <div class="payment-gateway-editor">
        <div class="mb-6 flex items-center gap-4">
            <router-link :to="{ name: 'admin.settings.gateways' }" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ t('Back to Gateways') }}
            </router-link>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('Configure') }} {{ gateway.name }}</h1>
        </div>

        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <form v-else @submit.prevent="saveConfig" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="space-y-6">
                <div v-for="field in configFields" :key="field.key">
                    <label :for="field.key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ field.label }}
                    </label>
                    <input 
                        v-if="field.type === 'text'"
                        :id="field.key"
                        v-model="config[field.key]"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                    <input 
                        v-else-if="field.type === 'password'"
                        :id="field.key"
                        v-model="config[field.key]"
                        type="password"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                    <input
                        v-else-if="field.type === 'number'"
                        :id="field.key"
                        v-model.number="config[field.key]"
                        type="number"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                    <select
                        v-else-if="field.type === 'select'"
                        :id="field.key"
                        v-model="config[field.key]"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option v-for="opt in field.options || []" :key="String(opt.value)" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <textarea
                        v-else-if="field.type === 'textarea'"
                        :id="field.key"
                        v-model="config[field.key]"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                    <textarea
                        v-else-if="field.type === 'json'"
                        :id="field.key"
                        v-model="jsonDraft[field.key]"
                        rows="8"
                        class="w-full px-3 py-2 font-mono text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        @blur="parseJsonField(field.key)"
                    />
                    <div v-else-if="field.type === 'boolean'" class="mt-2">
                        <label class="flex items-center">
                            <input 
                                type="checkbox"
                                v-model="config[field.key]"
                                class="h-4 w-4 text-indigo-600 rounded"
                            />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ t('Enabled') }}</span>
                        </label>
                    </div>
                    <p v-if="field.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ field.description }}
                    </p>
                    <p v-if="jsonErrors[field.key]" class="mt-1 text-xs text-red-500">
                        {{ jsonErrors[field.key] }}
                    </p>
                </div>

                <div v-if="Object.keys(config).length === 0" class="py-4 text-center text-gray-500">
                    {{ t('No additional configuration required for this gateway.') }}
                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                    <button 
                        type="submit"
                        :disabled="saving"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? t('Saving...') : t('Save Configuration') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const route = useRoute();
const { t } = useTranslation();
const dialog = useDialog();

const loading = ref(true);
const saving = ref(false);
const gateway = ref<any>({});
const config = ref<any>({});
const schema = ref<any[]>([]);
const jsonDraft = ref<Record<string, string>>({});
const jsonErrors = ref<Record<string, string>>({});

const configFields = computed(() => {
    const fields = (schema.value || []).map((field: any) => normalizeSchemaField(field));
    if (fields.length > 0) {
        return fields.sort((a: any, b: any) => (a.order ?? 999) - (b.order ?? 999));
    }
    return inferSchemaFromConfig(config.value);
});

const prettyLabel = (key: string): string =>
    key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const normalizeSchemaField = (field: any) => {
    const key = String(field?.key || '');
    const type = String(field?.type || 'text').toLowerCase();
    const lower = key.toLowerCase();
    const sensitive = !!field?.sensitive || lower.includes('secret') || lower.includes('password') || lower.includes('token') || lower.includes('api_key');

    return {
        key,
        label: field?.label || prettyLabel(key),
        description: field?.description || '',
        type: sensitive && type === 'text' ? 'password' : type,
        options: Array.isArray(field?.options) ? field.options : [],
        order: Number(field?.order ?? 999),
    };
};

const inferSchemaFromConfig = (rawConfig: Record<string, any>) => {
    return Object.keys(rawConfig || {}).map((key, index) => {
        const value = rawConfig[key];
        const lower = key.toLowerCase();
        const sensitive = lower.includes('secret') || lower.includes('password') || lower.includes('token') || lower.includes('api_key');

        let type = 'text';
        if (typeof value === 'boolean') type = 'boolean';
        else if (typeof value === 'number') type = 'number';
        else if (Array.isArray(value) || (value && typeof value === 'object')) type = 'json';
        if (sensitive) type = 'password';

        return {
            key,
            label: prettyLabel(key),
            description: '',
            type,
            options: [],
            order: (index + 1) * 10,
        };
    });
};

const refreshJsonDraft = () => {
    const drafts: Record<string, string> = {};
    Object.entries(config.value || {}).forEach(([key, value]) => {
        if (Array.isArray(value) || (value && typeof value === 'object')) {
            drafts[key] = JSON.stringify(value, null, 2);
        }
    });
    jsonDraft.value = drafts;
    jsonErrors.value = {};
};

const parseJsonField = (key: string): boolean => {
    if (!(key in jsonDraft.value)) return true;

    const raw = (jsonDraft.value[key] || '').trim();
    if (raw === '') {
        config.value[key] = null;
        delete jsonErrors.value[key];
        return true;
    }

    try {
        config.value[key] = JSON.parse(raw);
        delete jsonErrors.value[key];
        return true;
    } catch (e: any) {
        jsonErrors.value[key] = t('Invalid JSON format');
        return false;
    }
};

const loadGateway = async () => {
    try {
        const response = await axios.get(`/api/v1/payment-gateways/${route.params.id}`);
        gateway.value = response.data.data;
        config.value = { ...(gateway.value.config || {}) };
        schema.value = Array.isArray(gateway.value.config_schema) ? gateway.value.config_schema : [];
        refreshJsonDraft();
    } catch (error) {
        dialog.error(t('Failed to load gateway'));
    } finally {
        loading.value = false;
    }
};

const saveConfig = async () => {
    for (const field of configFields.value) {
        if (field.type === 'json') {
            const ok = parseJsonField(field.key);
            if (!ok) {
                dialog.error(t('Invalid JSON format'));
                return;
            }
        }
    }

    saving.value = true;
    try {
        await axios.put(`/api/v1/payment-gateways/${route.params.id}`, {
            config: config.value
        });
        dialog.success(t('Configuration saved successfully'));
    } catch (error) {
        dialog.error(t('Failed to save configuration'));
    } finally {
        saving.value = false;
    }
};

onMounted(loadGateway);
</script>
