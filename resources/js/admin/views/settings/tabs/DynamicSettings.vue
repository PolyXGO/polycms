<template>
    <div>
        <form @submit.prevent="$emit('save')" class="space-y-6">
            <div v-for="(setting, key) in displaySettings" :key="key">
                <label :for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ t(setting.label) }}
                </label>
                
                <!-- Currency Select -->
                <select
                    v-if="key === 'ecommerce_currency' && availableCurrencies.length > 0"
                    :id="key"
                    :value="setting.value"
                    @change="updateValue(key, ($event.target as HTMLSelectElement).value)"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option v-for="currency in availableCurrencies" :key="currency.code" :value="currency.code">
                        {{ currency.code }} - {{ currency.symbol }}
                    </option>
                </select>

                <!-- String / Text -->
                <input
                    v-else-if="setting.type === 'string' || setting.type === 'text'"
                    :id="key"
                    :value="setting.value"
                    @input="updateValue(key, ($event.target as HTMLInputElement).value)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                />

                <!-- Select -->
                <select
                    v-else-if="setting.type === 'select'"
                    :id="key"
                    :value="setting.value"
                    @change="updateValue(key, ($event.target as HTMLSelectElement).value)"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option v-for="option in setting.options" :key="option.value" :value="option.value">
                        {{ t(option.label) }}
                    </option>
                </select>

                <!-- Number -->
                <input
                    v-else-if="setting.type === 'number'"
                    :id="key"
                    :value="setting.value"
                    @input="updateValue(key, Number(($event.target as HTMLInputElement).value))"
                    type="number"
                    v-bind="setting.input_props"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                />

                <!-- Password -->
                <input
                    v-else-if="setting.type === 'password'"
                    :id="key"
                    :value="setting.value"
                    @input="updateValue(key, ($event.target as HTMLInputElement).value)"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                />

                <!-- Boolean -->
                <FormToggle
                    v-else-if="setting.type === 'boolean' || setting.type === 'toggle'"
                    :name="key"
                    :modelValue="['true', '1', true, 1].includes(setting.value)"
                    :label="t(setting.label)"
                    @update:modelValue="updateValue(key, $event)"
                />

                <!-- Textarea -->
                <textarea
                    v-else-if="setting.type === 'textarea'"
                    :id="key"
                    :value="setting.value"
                    @input="updateValue(key, ($event.target as HTMLTextAreaElement).value)"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                ></textarea>

                <p v-if="setting.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ t(setting.description) }}
                </p>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="saving" class="flex items-center">
                        <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        {{ t('Saving...') }}
                    </span>
                    <span v-else>{{ t('Save Changes') }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTranslation } from '../../../composables/useTranslation';
import FormToggle from '../../../components/forms/FormToggle.vue';

interface Setting {
    key: string;
    value: any;
    type: string;
    label: string;
    description: string;
    options?: { label: string; value: any }[];
    input_props?: Record<string, any>;
}

interface Props {
    settings: {
        [key: string]: Setting;
    };
    saving: boolean;
    group: string;
}

const props = defineProps<Props>();
const { t } = useTranslation();

const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const availableCurrencies = computed(() => {
    if (!props.settings.currencies?.value) return [];
    try {
        const value = props.settings.currencies.value;
        const currencies = typeof value === 'string' ? JSON.parse(value) : value;
        return Array.isArray(currencies) ? currencies : [];
    } catch (e) {
        return [];
    }
});

const displaySettings = computed(() => {
    const hiddenKeys = ['currencies', 'currency_formatting_rules'];
    const filtered: Record<string, Setting> = {};
    
    Object.keys(props.settings).forEach(key => {
        if (!hiddenKeys.includes(key)) {
            filtered[key] = props.settings[key];
        }
    });
    
    return filtered;
});

const updateValue = (key: string, value: any) => {
    emit('update', props.group, key, value);
};
</script>
