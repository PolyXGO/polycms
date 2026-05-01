<template>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ t('Refund Policy') }}
            </h3>

            <div class="space-y-5">
                <div>
                    <label
                        for="refund-policy-window-days"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                        {{ t('Default refund request window (days)') }}
                    </label>
                    <input
                        id="refund-policy-window-days"
                        type="number"
                        min="0"
                        max="3650"
                        :value="settings.refund_policy_default_window_days?.value ?? 7"
                        @input="updateValue('refund_policy_default_window_days', Number(($event.target as HTMLInputElement).value || 0))"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ t('Number of days after a successful order when customers can request refunds by default.') }}
                    </p>
                </div>

                <div>
                    <label
                        for="refund-policy-default-note"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                        {{ t('Default refund policy note') }}
                    </label>
                    <textarea
                        id="refund-policy-default-note"
                        rows="5"
                        :value="settings.refund_policy_default_note?.value ?? ''"
                        @input="updateValue('refund_policy_default_note', ($event.target as HTMLTextAreaElement).value)"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                        :placeholder="t('This note is used when product-specific refund policy note is empty.')"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ t('Fallback note shown when a refundable product has no product-specific refund policy note.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
                type="button"
                @click="$emit('save')"
                :disabled="saving"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <span v-if="saving" class="flex items-center">
                    <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                    {{ t('Saving...') }}
                </span>
                <span v-else>{{ t('Save Settings') }}</span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useTranslation } from '../../../composables/useTranslation';

interface Setting {
    key: string;
    value: any;
    type: string;
    label: string;
    description: string;
}

interface Props {
    settings: Record<string, Setting>;
    saving: boolean;
    group: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const { t } = useTranslation();

const updateValue = (key: string, value: any) => {
    emit('update', props.group, key, value);
};
</script>

