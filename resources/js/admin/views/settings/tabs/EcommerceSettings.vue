<template>
 <div class="space-y-6">
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
 <!-- Left Column: Store Information -->
 <div class="space-y-6">
 <div class="bg-admin-theme-surface shadow rounded-lg p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">{{ t('Store Information') }}</h3>
 <div class="space-y-4">
 <!-- Store Name -->
 <div>
 <label for="ecommerce_store_name" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Store Name') }}</label>
 <input
 id="ecommerce_store_name"
 type="text"
 :value="settings.ecommerce_store_name?.value"
 @input="updateValue('ecommerce_store_name', ($event.target as HTMLInputElement).value)"
 :class="controlClass"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">{{ t('Your store name will appear in emails, invoices, and throughout the site.') }}</p>
 </div>

 <!-- Company Name -->
 <div>
 <label for="ecommerce_company_name" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Company Name') }}</label>
 <input
 id="ecommerce_company_name"
 type="text"
 :value="settings.ecommerce_company_name?.value"
 @input="updateValue('ecommerce_company_name', ($event.target as HTMLInputElement).value)"
 :class="controlClass"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">{{ t('Legal business name for invoices and official documents.') }}</p>
 </div>

 <!-- Phone Number -->
 <div>
 <label for="ecommerce_phone_number" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Phone Number') }}</label>
 <input
 id="ecommerce_phone_number"
 type="text"
 :value="settings.ecommerce_phone_number?.value"
 @input="updateValue('ecommerce_phone_number', ($event.target as HTMLInputElement).value)"
 :class="controlClass"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">{{ settings.ecommerce_phone_number?.description }}</p>
 </div>

 <!-- Store Email -->
 <div>
 <label for="ecommerce_store_email" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Store Email') }}</label>
 <input
 id="ecommerce_store_email"
 type="email"
 :value="settings.ecommerce_store_email?.value"
 @input="updateValue('ecommerce_store_email', ($event.target as HTMLInputElement).value)"
 :class="controlClass"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">{{ settings.ecommerce_store_email?.description }}</p>
 </div>
 </div>
 </div>

 <!-- Admin Notification Emails -->
 <div class="bg-admin-theme-surface shadow rounded-lg p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">{{ t('Admin Notification Emails') }}</h3>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Emails') }}</label>
 <FormTags
 name="ecommerce_admin_emails"
 :model-value="settings.ecommerce_admin_emails?.value || []"
 @update:modelValue="updateValue('ecommerce_admin_emails', $event)"
 :placeholder="t('Add email...')"
 tag-type="email"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">{{ settings.ecommerce_admin_emails?.description }}</p>
 </div>
 </div>

 <!-- Invoice Configuration -->
 <div class="bg-admin-theme-surface shadow rounded-lg p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">{{ t('Invoice Configuration') }}</h3>
 <div class="space-y-4">
 <!-- Invoice Prefix -->
 <div>
 <label for="ecommerce_invoice_prefix" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Invoice Prefix') }}</label>
 <input
 id="ecommerce_invoice_prefix"
 type="text"
 :value="settings.ecommerce_invoice_prefix?.value"
 @input="updateValue('ecommerce_invoice_prefix', ($event.target as HTMLInputElement).value)"
 :class="[controlClass, 'uppercase']"
 placeholder="INV"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">{{ t('Prefix added to randomly generated 10-digit invoice numbers (e.g. INV8492105942).') }}</p>
 </div>
 
 <!-- Auto-issue Toggle -->
 <div>
 <label class="flex items-start">
 <div class="flex items-center h-5">
 <input
 id="ecommerce_invoice_auto_issue"
 type="checkbox"
 :checked="settings.ecommerce_invoice_auto_issue?.value ==='1' || settings.ecommerce_invoice_auto_issue?.value === true || settings.ecommerce_invoice_auto_issue?.value ==='true'"
 @change="updateValue('ecommerce_invoice_auto_issue', ($event.target as HTMLInputElement).checked ?'1' :'0')"
 :class="checkboxClass"
 />
 </div>
 <div class="ml-3 text-sm">
 <span class="font-medium text-admin-theme-text-secondary">{{ t('Auto-Issue Invoice') }}</span>
 <p class="mt-1 text-admin-theme-text-muted">
 {{ t('Automatically generate an invoice when an order is paid or marked as processing/completed.') }}
 </p>
 </div>
 </label>
 </div>
 </div>
 </div>
 </div>

 <!-- Right Column: Location & Address -->
 <div class="space-y-6">
 <!-- Store Address -->
 <div class="bg-admin-theme-surface shadow rounded-lg p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">{{ t('Store Address') }}</h3>
 <div class="space-y-4">
 <!-- Country -->
 <div>
 <label for="ecommerce_address_country" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Country') }}</label>
 <select
 id="ecommerce_address_country"
 :value="settings.ecommerce_address_country?.value"
 @change="updateValue('ecommerce_address_country', ($event.target as HTMLSelectElement).value)"
 :class="controlClass"
 >
 <option value="">{{ t('Select a country') }}</option>
 <option v-for="country in countries" :key="country.code" :value="country.code">
 {{ country.name }}
 </option>
 </select>
 </div>
 
 <!-- State / Province -->
 <div>
 <label for="ecommerce_address_state" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('State / Province') }}</label>
 <input
 id="ecommerce_address_state"
 type="text"
 :value="settings.ecommerce_address_state?.value"
 @input="updateValue('ecommerce_address_state', ($event.target as HTMLInputElement).value)"
 :class="controlClass"
 />
 </div>

 <!-- City -->
 <div>
 <label for="ecommerce_address_city" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('City') }}</label>
 <input
 id="ecommerce_address_city"
 type="text"
 :value="settings.ecommerce_address_city?.value"
 @input="updateValue('ecommerce_address_city', ($event.target as HTMLInputElement).value)"
 :class="controlClass"
 />
 </div>

 <!-- Address -->
 <div>
 <label for="ecommerce_address_line1" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Address') }}</label>
 <textarea
 id="ecommerce_address_line1"
 :value="settings.ecommerce_address_line1?.value"
 @input="updateValue('ecommerce_address_line1', ($event.target as HTMLTextAreaElement).value)"
 rows="3"
 :class="controlClass"
 ></textarea>
 </div>

 <!-- Tax ID -->
 <div>
 <label for="ecommerce_tax_id" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Tax ID / VAT Number') }}</label>
 <input
 id="ecommerce_tax_id"
 type="text"
 :value="settings.ecommerce_tax_id?.value"
 @input="updateValue('ecommerce_tax_id', ($event.target as HTMLInputElement).value)"
 :class="controlClass"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">{{ settings.ecommerce_tax_id?.description }}</p>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Bottom: Currency & Payment -->
 <div class="bg-admin-theme-surface shadow rounded-lg p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">{{ t('Currency & Payment') }}</h3>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <!-- Currency Selection -->
 <div>
 <label for="ecommerce_currency" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Store Display Currency') }}</label>
 <select
 id="ecommerce_currency"
 :value="settings.ecommerce_currency?.value"
 @change="onCurrencyChange(($event.target as HTMLSelectElement).value)"
 :class="controlClass"
 >
 <option v-if="availableCurrencies.length === 0" value="USD">USD - $</option>
 <option v-for="currency in availableCurrencies" :key="currency.code" :value="currency.code">
 {{ currency.code }} - {{ currency.symbol }}
 </option>
 </select>
 <p class="mt-2 text-sm text-admin-theme-text-muted">
 {{ t('Example display:') }} <span class="font-medium text-admin-theme-text">{{ currencyPreview }}</span>
 </p>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ t('This currency is the default currency displayed to customers on the storefront. Prices will be converted automatically from the Base Currency.') }}
 <router-link :to="{ name:'admin.settings.ecommerce.currencies' }" class="text-admin-theme-primary hover:text-admin-theme-primary font-medium ml-1 inline-flex items-center">
 {{ t('Manage currencies & formatting') }} &rarr;
 </router-link>
 </p>
 </div>
 </div>
 </div>

 <!-- Add to Cart & Shopping Experience -->
 <div class="bg-admin-theme-surface shadow rounded-lg p-6">
 <h3 class="text-lg font-medium text-admin-theme-text mb-4">{{ t('Shopping Experience') }}</h3>
 <div class="grid grid-cols-1 gap-6">
 <!-- Add to Cart Behavior -->
 <div>
 <label class="flex items-start">
 <div class="flex items-center h-5">
 <input
 id="ecommerce_redirect_cart_after_add"
 type="checkbox"
 :checked="settings.ecommerce_redirect_cart_after_add?.value ==='1' || settings.ecommerce_redirect_cart_after_add?.value === true || settings.ecommerce_redirect_cart_after_add?.value ==='true'"
 @change="updateValue('ecommerce_redirect_cart_after_add', ($event.target as HTMLInputElement).checked ?'1' :'0')"
 :class="checkboxClass"
 />
 </div>
 <div class="ml-3 text-sm">
 <span class="font-medium text-admin-theme-text-secondary">{{ t('Redirect to the cart page after successful addition') }}</span>
 <p class="mt-1 text-admin-theme-text-muted">
 {{ t('Enable this to automatically send users to the cart page after they click"Add to Cart". If disabled, they will remain on the product page and may continue shopping.') }}
 </p>
 </div>
 </label>
 </div>

 <!-- Product Views Tracking Mode -->
 <div class="mt-4 border-t border-admin-theme-border pt-4">
 <label for="product_views_tracking_mode" class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('Product Views Tracking Mode') }}</label>
 <select
 id="product_views_tracking_mode"
 :value="settings.product_views_tracking_mode?.value || 'every_visit'"
 @change="updateValue('product_views_tracking_mode', ($event.target as HTMLSelectElement).value)"
 :class="controlClass"
 >
 <option value="every_visit">{{ t('Every Visit (Page Refresh)') }}</option>
 <option value="unique_24h">{{ t('Unique Session (24-hour Cookie per Product)') }}</option>
 </select>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ t(settings.product_views_tracking_mode?.description || 'Select how product page views are tracked and incremented.') }}
 </p>
 </div>

 <!-- Default Product Image -->
 <div class="mt-4 border-t border-admin-theme-border pt-4">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ t('Default Product Image') }}
 </label>
 <input
 id="ecommerce_default_product_image"
 :value="getSettingValue('ecommerce_default_product_image')"
 type="hidden"
 />
 <div class="flex items-center gap-4 mt-2">
 <div v-if="getSettingValue('ecommerce_default_product_image')" class="flex-shrink-0">
 <img
 :src="getSettingValue('ecommerce_default_product_image')"
 alt="Default Product Image"
 class="h-20 w-32 object-cover border border-admin-theme-border rounded p-1 bg-admin-theme-input-bg"
 />
 </div>
 <div v-else class="flex-shrink-0 w-32 h-20 border-2 border-dashed border-admin-theme-border rounded flex items-center justify-center bg-admin-theme-base">
 <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 <div class="flex-1">
 <button type="button" class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors text-sm font-medium" @click="openMediaPicker('ecommerce_default_product_image')">
 {{ getSettingValue('ecommerce_default_product_image') ? t('Change Image') : t('Select Image') }}
 </button>
 <button v-if="getSettingValue('ecommerce_default_product_image')" type="button" class="ml-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-sm font-medium" @click="updateValue('ecommerce_default_product_image', '')">
 {{ t('Remove') }}
 </button>
 </div>
 </div>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ t('Fallback image used when a product does not have a featured image.') }}
 </p>
 </div>
 </div>
 </div>

 <!-- Savings Call-to-Action (CTA) -->
 <div class="bg-admin-theme-surface shadow rounded-lg p-6">
 <div class="flex justify-between items-center mb-4">
 <h3 class="text-lg font-medium text-admin-theme-text">{{ t('Savings Call-to-Action (CTA)') }}</h3>
 
 <div class="relative group">
 <button type="button" class="text-sm px-3 py-1.5 bg-admin-theme-base border border-admin-theme-border rounded-lg hover:bg-admin-theme-input-bg transition-colors flex items-center">
 <i class="fas fa-magic mr-1.5 text-indigo-500"></i> {{ t('Apply Preset') }}
 </button>
 <div class="absolute right-0 top-full mt-1 w-56 bg-admin-theme-surface border border-admin-theme-border rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10">
 <button type="button" @click="applyCtaPreset('⚡ Tiết kiệm $:amount+ trong :years năm cho :keys keys so với gia hạn hàng năm', '⚡ Tiết kiệm $:amount+ mỗi năm cho :keys keys so với gia hạn hàng tháng')" class="block w-full text-left px-4 py-2 text-sm text-admin-theme-text hover:bg-admin-theme-base border-b border-admin-theme-border rounded-t-lg">
 Vietnamese Default
 </button>
 <button type="button" @click="applyCtaPreset('⚡ Save $:amount+ over :years yrs across :keys keys vs yearly renewal', '⚡ Save $:amount+ per year across :keys keys vs monthly renewal')" class="block w-full text-left px-4 py-2 text-sm text-admin-theme-text hover:bg-admin-theme-base rounded-b-lg">
 English Default
 </button>
 </div>
 </div>
 </div>
 
 <div class="grid grid-cols-1 gap-6">
 <!-- Lifetime CTA -->
 <div>
 <label for="ecommerce_savings_cta_lifetime" class="block text-sm font-medium text-admin-theme-text-secondary">
 {{ t('Savings CTA (Lifetime vs Yearly)') }}
 </label>
 <textarea
 id="ecommerce_savings_cta_lifetime"
 :value="settings.ecommerce_savings_cta_lifetime?.value"
 @input="updateValue('ecommerce_savings_cta_lifetime', ($event.target as HTMLTextAreaElement).value)"
 rows="2"
 :class="controlClass"
 placeholder="⚡ Save $:amount+ over :years yrs across :keys keys vs yearly renewal"
 ></textarea>
 <p class="mt-2 text-sm text-admin-theme-text-muted flex gap-1.5 flex-wrap items-center">
 <span>{{ t('Macros:') }}</span>
 <button type="button" @click="insertMacro('ecommerce_savings_cta_lifetime', ':amount')" class="text-indigo-600 dark:text-indigo-400 font-mono text-[11px] bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors">:amount</button>
 <button type="button" @click="insertMacro('ecommerce_savings_cta_lifetime', ':years')" class="text-indigo-600 dark:text-indigo-400 font-mono text-[11px] bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors">:years</button>
 <button type="button" @click="insertMacro('ecommerce_savings_cta_lifetime', ':keys')" class="text-indigo-600 dark:text-indigo-400 font-mono text-[11px] bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors">:keys</button>
 </p>
 </div>

 <!-- Yearly CTA -->
 <div>
 <label for="ecommerce_savings_cta_yearly" class="block text-sm font-medium text-admin-theme-text-secondary">
 {{ t('Savings CTA (Yearly vs Monthly)') }}
 </label>
 <textarea
 id="ecommerce_savings_cta_yearly"
 :value="settings.ecommerce_savings_cta_yearly?.value"
 @input="updateValue('ecommerce_savings_cta_yearly', ($event.target as HTMLTextAreaElement).value)"
 rows="2"
 :class="controlClass"
 placeholder="⚡ Save $:amount+ per year across :keys keys vs monthly renewal"
 ></textarea>
 <p class="mt-2 text-sm text-admin-theme-text-muted flex gap-1.5 flex-wrap items-center">
 <span>{{ t('Macros:') }}</span>
 <button type="button" @click="insertMacro('ecommerce_savings_cta_yearly', ':amount')" class="text-indigo-600 dark:text-indigo-400 font-mono text-[11px] bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors">:amount</button>
 <button type="button" @click="insertMacro('ecommerce_savings_cta_yearly', ':keys')" class="text-indigo-600 dark:text-indigo-400 font-mono text-[11px] bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors">:keys</button>
 </p>
 </div>
 </div>
 </div>

 <!-- Save Button -->
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

 <!-- Media Picker -->
 <MediaPicker
 ref="mediaPickerRef"
 :multiple="false"
 :accepted-types="['image']"
 @select="handleMediaSelect"
 />
 </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from'vue';
import { useTranslation } from'../../../composables/useTranslation';
import FormTags from'../../../components/forms/FormTags.vue';
import MediaPicker from'../../../components/MediaPicker';

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
const emit = defineEmits<{
 (e:'update', group: string, key: string, value: any): void;
 (e:'save'): void;
}>();

const { t } = useTranslation();

const controlClass = 'mt-1 block w-full rounded-md border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary sm:text-sm';
const checkboxClass = 'mt-0.5 rounded border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-primary shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/30 focus:ring-opacity-50';

const getSettingValue = (key: string): any => {
    const value = props.settings[key]?.value;
    if (value === null || value === undefined) return '';
    return String(value);
};

const updateValue = (key: string, value: any) => {
    emit('update', props.group, key, value);
};

const insertMacro = (key: string, macro: string) => {
    const el = document.getElementById(key) as HTMLTextAreaElement;
    if (!el) {
        // Fallback if element not found: just append
        const currentVal = String(props.settings[key]?.value || '');
        updateValue(key, currentVal + ' ' + macro);
        return;
    }
    
    const currentVal = String(props.settings[key]?.value || '');
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const newVal = currentVal.substring(0, start) + macro + currentVal.substring(end);
    
    updateValue(key, newVal);
    
    // Focus and restore cursor position after DOM updates
    setTimeout(() => {
        el.focus();
        el.setSelectionRange(start + macro.length, start + macro.length);
    }, 50);
};

const applyCtaPreset = (lifetimeText: string, yearlyText: string) => {
    updateValue('ecommerce_savings_cta_lifetime', lifetimeText);
    updateValue('ecommerce_savings_cta_yearly', yearlyText);
};

const mediaPickerRef = ref<any>(null);
const currentPickerField = ref<string>('');

const openMediaPicker = (field: string) => {
    currentPickerField.value = field;
    if (mediaPickerRef.value) {
        mediaPickerRef.value.open();
    }
};

const handleMediaSelect = (media: any) => {
    if (currentPickerField.value && media) {
        const selectedMedia = Array.isArray(media) ? media[0] : media;
        if (selectedMedia && selectedMedia.url) {
            updateValue(currentPickerField.value, selectedMedia.url);
        }
    }
    currentPickerField.value = '';
};

const onCurrencyChange = (code: string) => {
 updateValue('ecommerce_currency', code);
 
 // Find the currency and update related fields
 const currency = availableCurrencies.value.find((c: any) => c.code === code);
 if (currency) {
 updateValue('ecommerce_currency_symbol', currency.symbol);
 updateValue('currency_decimals', currency.decimals);
 updateValue('currency_symbol_position', currency.symbol_position);
 updateValue('currency_thousands_separator', currency.thousands_separator);
 updateValue('currency_decimal_separator', currency.decimal_separator);
 updateValue('currency_space', currency.space_between);
 }
};

// Available currencies for dropdown
const availableCurrencies = computed(() => {
 if (!props.settings.currencies?.value) return [];
 try {
 const value = props.settings.currencies.value;
 const currencies = typeof value ==='string' ? JSON.parse(value) : value;
 return Array.isArray(currencies) ? currencies : [];
 } catch (e) {
 return [];
 }
});

import { countries } from'../../../data/countries';

const currencyPreview = computed(() => {
 const code = props.settings.ecommerce_currency?.value ||'USD';
 const currency = availableCurrencies.value.find((c: any) => c.code === code);

 // Default values if no currency found or partial data
 let symbol ='$';
 let decimals = 2;
 let thousandsSep =',';
 let decimalSep ='.';
 let position ='before';
 let space = false;

 if (currency) {
 symbol = currency.symbol ||'$';
 decimals = typeof currency.decimals !=='undefined' ? parseInt(currency.decimals) : 2;
 thousandsSep = currency.thousands_separator ||',';
 decimalSep = currency.decimal_separator ||'.';
 position = currency.symbol_position ||'before';
 space = currency.space_between === true || currency.space_between ==='true';
 }

 const testValue = 12345.6789;
 
 // Format number
 const fixed = testValue.toFixed(decimals);
 const parts = fixed.split('.');
 parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
 const formattedNumber = parts.join(decimalSep);

 const spaceStr = space ?'' :'';
 
 if (position ==='after') {
 return `${formattedNumber}${spaceStr}${symbol}`;
 }
 return `${symbol}${spaceStr}${formattedNumber}`;
});


</script>
