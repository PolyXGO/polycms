<template>
  <div class="space-y-6">
    <!-- Invoice General Settings -->
    <div class="bg-admin-theme-surface shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">
        {{ t('Invoice Configuration') }}
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="invoice-prefix" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
            {{ t('Invoice Prefix') }}
          </label>
          <input
            id="invoice-prefix"
            type="text"
            :value="settings.ecommerce_invoice_prefix?.value ?? 'INV'"
            @input="updateValue('ecommerce_invoice_prefix', ($event.target as HTMLInputElement).value)"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
            placeholder="INV"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Prefix added to generated invoice numbers (e.g. INV84921059).') }}
          </p>
        </div>

        <div>
          <FormToggle
            name="invoice-auto-issue"
            :modelValue="['true', '1', true, 1].includes(settings.ecommerce_invoice_auto_issue?.value ?? true)"
            :label="t('Auto-Issue Invoice')"
            @update:modelValue="updateValue('ecommerce_invoice_auto_issue', $event)"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Automatically generate an invoice when an order is paid or completed.') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Company & Seller Information -->
    <div class="bg-admin-theme-surface shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">
        {{ t('Company & Seller Information') }}
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="company-name" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
            {{ t('Company / Seller Name') }}
          </label>
          <input
            id="company-name"
            type="text"
            :value="settings.ecommerce_company_name?.value ?? ''"
            @input="updateValue('ecommerce_company_name', ($event.target as HTMLInputElement).value)"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
            :placeholder="t('Legal business or seller name')"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Legal business name displayed as seller on invoices.') }}
          </p>
        </div>

        <div>
          <label for="tax-id" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
            {{ t('Tax ID / VAT Number') }}
          </label>
          <input
            id="tax-id"
            type="text"
            :value="settings.ecommerce_tax_id?.value ?? ''"
            @input="updateValue('ecommerce_tax_id', ($event.target as HTMLInputElement).value)"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
            :placeholder="t('e.g. US123456789')"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Tax registration or VAT number displayed on invoices.') }}
          </p>
        </div>

        <div>
          <label for="store-email" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
            {{ t('Invoice Contact Email') }}
          </label>
          <input
            id="store-email"
            type="email"
            :value="settings.ecommerce_store_email?.value ?? ''"
            @input="updateValue('ecommerce_store_email', ($event.target as HTMLInputElement).value)"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
            :placeholder="t('billing@yourstore.com')"
          />
        </div>

        <div>
          <label for="store-phone" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
            {{ t('Invoice Phone Number') }}
          </label>
          <input
            id="store-phone"
            type="text"
            :value="settings.ecommerce_phone_number?.value ?? ''"
            @input="updateValue('ecommerce_phone_number', ($event.target as HTMLInputElement).value)"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
            :placeholder="t('+1 (555) 000-0000')"
          />
        </div>
      </div>
    </div>

    <!-- Company Address -->
    <div class="bg-admin-theme-surface shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">
        {{ t('Company Address') }}
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
          <label for="address-line1" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
            {{ t('Street Address') }}
          </label>
          <input
            id="address-line1"
            type="text"
            :value="settings.ecommerce_address_line1?.value ?? ''"
            @input="updateValue('ecommerce_address_line1', ($event.target as HTMLInputElement).value)"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
            :placeholder="t('123 Business Street, Suite 100')"
          />
        </div>

        <div>
          <label for="address-city" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
            {{ t('City') }}
          </label>
          <input
            id="address-city"
            type="text"
            :value="settings.ecommerce_address_city?.value ?? ''"
            @input="updateValue('ecommerce_address_city', ($event.target as HTMLInputElement).value)"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
          />
        </div>

        <div>
          <label for="address-state" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
            {{ t('State / Province') }}
          </label>
          <input
            id="address-state"
            type="text"
            :value="settings.ecommerce_address_state?.value ?? ''"
            @input="updateValue('ecommerce_address_state', ($event.target as HTMLInputElement).value)"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
          />
        </div>

        <div>
          <label for="address-country" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
            {{ t('Country Code (ISO 2)') }}
          </label>
          <input
            id="address-country"
            type="text"
            :value="settings.ecommerce_address_country?.value ?? 'US'"
            @input="updateValue('ecommerce_address_country', ($event.target as HTMLInputElement).value)"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary uppercase"
            placeholder="US"
          />
        </div>
      </div>
    </div>

    <!-- Invoice Footer & Terms -->
    <div class="bg-admin-theme-surface shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">
        {{ t('Invoice Footer & Terms') }}
      </h3>

      <div>
        <label for="invoice-footer-note" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
          {{ t('Invoice Footer Note & Payment Terms') }}
        </label>
        <textarea
          id="invoice-footer-note"
          rows="4"
          :value="settings.ecommerce_invoice_footer_note?.value ?? ''"
          @input="updateValue('ecommerce_invoice_footer_note', ($event.target as HTMLTextAreaElement).value)"
          class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
          :placeholder="t('Default note, payment instructions, bank info or tax disclaimer printed at the bottom of invoices.')"
        ></textarea>
        <p class="mt-1 text-xs text-admin-theme-text-muted">
          {{ t('Displayed at the bottom of printed and PDF invoices.') }}
        </p>
      </div>
    </div>

    <!-- Submit Action Button -->
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
import { useTranslation } from '../../../composables/useTranslation';
import FormToggle from '../../../components/forms/FormToggle.vue';

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
