<template>
  <div class="space-y-6">
    <!-- Coupons & Discount Rules -->
    <div class="bg-admin-theme-surface shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">
        {{ t('Coupons & Discount Rules') }}
      </h3>

      <div class="space-y-5">
        <div>
          <FormToggle
            name="checkout-coupons-enabled"
            :modelValue="['true', '1', true, 1].includes(settings.checkout_coupons_enabled?.value ?? true)"
            :label="t('Enable Coupon & Discount Codes')"
            @update:modelValue="updateValue('checkout_coupons_enabled', $event)"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Allow customers to apply coupon codes on cart and checkout pages.') }}
          </p>
        </div>

        <div>
          <FormToggle
            name="checkout-multiple-coupons"
            :modelValue="['true', '1', true, 1].includes(settings.checkout_multiple_coupons?.value ?? false)"
            :label="t('Allow Stacking Multiple Coupons')"
            @update:modelValue="updateValue('checkout_multiple_coupons', $event)"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('When enabled, customers can apply multiple discount codes to a single order.') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Checkout Account Behavior -->
    <div class="bg-admin-theme-surface shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">
        {{ t('Checkout Account Behavior') }}
      </h3>

      <div class="space-y-5">
        <div>
          <FormToggle
            name="checkout-guest-enabled"
            :modelValue="['true', '1', true, 1].includes(settings.checkout_guest_enabled?.value ?? true)"
            :label="t('Enable Guest Checkout')"
            @update:modelValue="updateValue('checkout_guest_enabled', $event)"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Allow customers to place orders without creating or logging into an account.') }}
          </p>
        </div>

        <div>
          <FormToggle
            name="checkout-auto-create-account"
            :modelValue="['true', '1', true, 1].includes(settings.checkout_auto_create_account?.value ?? true)"
            :label="t('Auto-create Customer Account on Guest Checkout')"
            @update:modelValue="updateValue('checkout_auto_create_account', $event)"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Automatically create a customer account and email access credentials upon guest checkout completion.') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Checkout Fields & Requirements -->
    <div class="bg-admin-theme-surface shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">
        {{ t('Checkout Fields & Requirements') }}
      </h3>

      <div class="space-y-5">
        <div>
          <FormToggle
            name="checkout-require-billing-address"
            :modelValue="['true', '1', true, 1].includes(settings.checkout_require_billing_address?.value ?? false)"
            :label="t('Require Full Billing Address for Digital Orders')"
            @update:modelValue="updateValue('checkout_require_billing_address', $event)"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Require full street address, city, and zip code during checkout.') }}
          </p>
        </div>

        <div>
          <FormToggle
            name="checkout-require-phone"
            :modelValue="['true', '1', true, 1].includes(settings.checkout_require_phone?.value ?? false)"
            :label="t('Require Phone Number')"
            @update:modelValue="updateValue('checkout_require_phone', $event)"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Make phone number a mandatory field during checkout.') }}
          </p>
        </div>

        <div>
          <FormToggle
            name="checkout-terms-consent"
            :modelValue="['true', '1', true, 1].includes(settings.checkout_terms_consent?.value ?? true)"
            :label="t('Require Terms & Conditions Acceptance Checkbox')"
            @update:modelValue="updateValue('checkout_terms_consent', $event)"
          />
          <p class="mt-1 text-xs text-admin-theme-text-muted">
            {{ t('Customers must check a box agreeing to terms and refund policies before submitting orders.') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Order Success Confirmation -->
    <div class="bg-admin-theme-surface shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">
        {{ t('Order Success Confirmation') }}
      </h3>

      <div>
        <label for="checkout-thankyou-message" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
          {{ t('Order Success Page Message') }}
        </label>
        <textarea
          id="checkout-thankyou-message"
          rows="4"
          :value="settings.checkout_thankyou_message?.value ?? ''"
          @input="updateValue('checkout_thankyou_message', ($event.target as HTMLTextAreaElement).value)"
          class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
          :placeholder="t('Custom message displayed on the order success page after payment.')"
        ></textarea>
        <p class="mt-1 text-xs text-admin-theme-text-muted">
          {{ t('Custom thank you message displayed on order completion page.') }}
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
