<template>
 <div class="space-y-6">
 <div class="bg-admin-theme-surface shadow rounded-lg p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">
 {{ t('Refund Policy') }}
 </h3>

 <div class="space-y-5">
 <div>
 <label
 for="refund-policy-window-days"
 class="block text-sm font-medium text-admin-theme-text-secondary mb-1"
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
 class="w-full rounded-md border-admin-theme-border shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary sm:text-sm"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ t('Number of days after a successful order when customers can request refunds by default.') }}
 </p>
 </div>

 <div>
 <label
 for="refund-policy-default-note"
 class="block text-sm font-medium text-admin-theme-text-secondary mb-1"
 >
 {{ t('Default refund policy note') }}
 </label>
 <textarea
 id="refund-policy-default-note"
 rows="5"
 :value="settings.refund_policy_default_note?.value ??''"
 @input="updateValue('refund_policy_default_note', ($event.target as HTMLTextAreaElement).value)"
 class="w-full rounded-md border-admin-theme-border shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary sm:text-sm"
 :placeholder="t('This note is used when product-specific refund policy note is empty.')"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ t('Fallback note shown when a refundable product has no product-specific refund policy note.') }}
 </p>
 </div>
 </div>
 </div>

 <div class="flex justify-end pt-4 border-t border-admin-theme-border">
 <button
 type="button"
 @click="$emit('save')"
 :disabled="saving"
 class="px-6 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
import { useTranslation } from'../../../composables/useTranslation';

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
 (e:'update', group: string, key: string, value: any): void;
 (e:'save'): void;
}>();

const { t } = useTranslation();

const updateValue = (key: string, value: any) => {
 emit('update', props.group, key, value);
};
</script>

