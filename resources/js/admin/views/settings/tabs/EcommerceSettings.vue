<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column: Store Information -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t('Store Information') }}</h3>
                    <div class="space-y-4">
                        <!-- Store Name -->
                        <div>
                            <label for="ecommerce_store_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Store Name') }}</label>
                            <input
                                id="ecommerce_store_name"
                                type="text"
                                :value="settings.ecommerce_store_name?.value"
                                @input="updateValue('ecommerce_store_name', ($event.target as HTMLInputElement).value)"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            />
                             <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('Your store name will appear in emails, invoices, and throughout the site.') }}</p>
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label for="ecommerce_company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Company Name') }}</label>
                            <input
                                id="ecommerce_company_name"
                                type="text"
                                :value="settings.ecommerce_company_name?.value"
                                @input="updateValue('ecommerce_company_name', ($event.target as HTMLInputElement).value)"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('Legal business name for invoices and official documents.') }}</p>
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="ecommerce_phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Phone Number') }}</label>
                            <input
                                id="ecommerce_phone_number"
                                type="text"
                                :value="settings.ecommerce_phone_number?.value"
                                @input="updateValue('ecommerce_phone_number', ($event.target as HTMLInputElement).value)"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ settings.ecommerce_phone_number?.description }}</p>
                        </div>

                        <!-- Store Email -->
                        <div>
                            <label for="ecommerce_store_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Store Email') }}</label>
                            <input
                                id="ecommerce_store_email"
                                type="email"
                                :value="settings.ecommerce_store_email?.value"
                                @input="updateValue('ecommerce_store_email', ($event.target as HTMLInputElement).value)"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ settings.ecommerce_store_email?.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Admin Notification Emails -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t('Admin Notification Emails') }}</h3>
                    <div>
                         <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Emails') }}</label>
                         <FormTags
                             name="ecommerce_admin_emails"
                             :model-value="settings.ecommerce_admin_emails?.value || []"
                             @update:modelValue="updateValue('ecommerce_admin_emails', $event)"
                             :placeholder="t('Add email...')"
                             tag-type="email"
                         />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ settings.ecommerce_admin_emails?.description }}</p>
                    </div>
                </div>

                <!-- Invoice Configuration -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t('Invoice Configuration') }}</h3>
                    <div class="space-y-4">
                        <!-- Invoice Prefix -->
                        <div>
                            <label for="ecommerce_invoice_prefix" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Invoice Prefix') }}</label>
                            <input
                                id="ecommerce_invoice_prefix"
                                type="text"
                                :value="settings.ecommerce_invoice_prefix?.value"
                                @input="updateValue('ecommerce_invoice_prefix', ($event.target as HTMLInputElement).value)"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm uppercase"
                                placeholder="INV"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('Prefix added to randomly generated 10-digit invoice numbers (e.g. INV8492105942).') }}</p>
                        </div>
                        
                        <!-- Auto-issue Toggle -->
                        <div>
                             <label class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        id="ecommerce_invoice_auto_issue"
                                        type="checkbox"
                                        :checked="settings.ecommerce_invoice_auto_issue?.value === '1' || settings.ecommerce_invoice_auto_issue?.value === true || settings.ecommerce_invoice_auto_issue?.value === 'true'"
                                        @change="updateValue('ecommerce_invoice_auto_issue', ($event.target as HTMLInputElement).checked ? '1' : '0')"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 mt-0.5"
                                    />
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ t('Auto-Issue Invoice') }}</span>
                                    <p class="mt-1 text-gray-500 dark:text-gray-400">
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
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t('Store Address') }}</h3>
                    <div class="space-y-4">
                        <!-- Country -->
                         <div>
                            <label for="ecommerce_address_country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Country') }}</label>
                            <select
                                id="ecommerce_address_country"
                                :value="settings.ecommerce_address_country?.value"
                                @change="updateValue('ecommerce_address_country', ($event.target as HTMLSelectElement).value)"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            >
                                <option value="">{{ t('Select a country') }}</option>
                                <option v-for="country in countries" :key="country.code" :value="country.code">
                                    {{ country.name }}
                                </option>
                            </select>
                        </div>
                        
                         <!-- State / Province -->
                        <div>
                            <label for="ecommerce_address_state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('State / Province') }}</label>
                            <input
                                id="ecommerce_address_state"
                                type="text"
                                :value="settings.ecommerce_address_state?.value"
                                @input="updateValue('ecommerce_address_state', ($event.target as HTMLInputElement).value)"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            />
                        </div>

                         <!-- City -->
                        <div>
                            <label for="ecommerce_address_city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('City') }}</label>
                            <input
                                id="ecommerce_address_city"
                                type="text"
                                :value="settings.ecommerce_address_city?.value"
                                @input="updateValue('ecommerce_address_city', ($event.target as HTMLInputElement).value)"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            />
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="ecommerce_address_line1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Address') }}</label>
                            <textarea
                                id="ecommerce_address_line1"
                                :value="settings.ecommerce_address_line1?.value"
                                @input="updateValue('ecommerce_address_line1', ($event.target as HTMLTextAreaElement).value)"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            ></textarea>
                        </div>

                        <!-- Tax ID -->
                         <div>
                            <label for="ecommerce_tax_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Tax ID / VAT Number') }}</label>
                            <input
                                id="ecommerce_tax_id"
                                type="text"
                                :value="settings.ecommerce_tax_id?.value"
                                @input="updateValue('ecommerce_tax_id', ($event.target as HTMLInputElement).value)"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ settings.ecommerce_tax_id?.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom: Currency & Payment -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t('Currency & Payment') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Currency Selection -->
                <div>
                     <label for="ecommerce_currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Store Display Currency') }}</label>
                    <select
                        id="ecommerce_currency"
                        :value="settings.ecommerce_currency?.value"
                         @change="onCurrencyChange(($event.target as HTMLSelectElement).value)"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                    >
                         <option v-if="availableCurrencies.length === 0" value="USD">USD - $</option>
                         <option v-for="currency in availableCurrencies" :key="currency.code" :value="currency.code">
                            {{ currency.code }} - {{ currency.symbol }}
                        </option>
                    </select>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ t('Example display:') }} <span class="font-medium text-gray-900 dark:text-gray-200">{{ currencyPreview }}</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ t('This currency is the default currency displayed to customers on the storefront. Prices will be converted automatically from the Base Currency.') }}
                        <router-link :to="{ name: 'admin.settings.ecommerce.currencies' }" class="text-indigo-600 hover:text-indigo-500 font-medium ml-1 inline-flex items-center">
                            {{ t('Manage currencies & formatting') }} &rarr;
                        </router-link>
                    </p>
                </div>
            </div>
        </div>

        <!-- Add to Cart & Shopping Experience -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t('Shopping Experience') }}</h3>
            <div class="grid grid-cols-1 gap-6">
                <!-- Add to Cart Behavior -->
                <div>
                     <label class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="ecommerce_redirect_cart_after_add"
                                type="checkbox"
                                :checked="settings.ecommerce_redirect_cart_after_add?.value === '1' || settings.ecommerce_redirect_cart_after_add?.value === true || settings.ecommerce_redirect_cart_after_add?.value === 'true'"
                                @change="updateValue('ecommerce_redirect_cart_after_add', ($event.target as HTMLInputElement).checked ? '1' : '0')"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 mt-0.5"
                            />
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ t('Redirect to the cart page after successful addition') }}</span>
                            <p class="mt-1 text-gray-500 dark:text-gray-400">
                                {{ t('Enable this to automatically send users to the cart page after they click "Add to Cart". If disabled, they will remain on the product page and may continue shopping.') }}
                            </p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Save Button -->
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
import { ref, computed, onMounted } from 'vue';
import { useTranslation } from '../../../composables/useTranslation';
import FormTags from '../../../components/forms/FormTags.vue';

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
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const { t } = useTranslation();

const updateValue = (key: string, value: any) => {
    emit('update', props.group, key, value);
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
        const currencies = typeof value === 'string' ? JSON.parse(value) : value;
        return Array.isArray(currencies) ? currencies : [];
    } catch (e) {
        return [];
    }
});

import { countries } from '../../../data/countries';

const currencyPreview = computed(() => {
    const code = props.settings.ecommerce_currency?.value || 'USD';
    const currency = availableCurrencies.value.find((c: any) => c.code === code);

    // Default values if no currency found or partial data
    let symbol = '$';
    let decimals = 2;
    let thousandsSep = ',';
    let decimalSep = '.';
    let position = 'before';
    let space = false;

    if (currency) {
        symbol = currency.symbol || '$';
        decimals = typeof currency.decimals !== 'undefined' ? parseInt(currency.decimals) : 2;
        thousandsSep = currency.thousands_separator || ',';
        decimalSep = currency.decimal_separator || '.';
        position = currency.symbol_position || 'before';
        space = currency.space_between === true || currency.space_between === 'true';
    }

    const testValue = 12345.6789;
    
    // Format number
    const fixed = testValue.toFixed(decimals);
    const parts = fixed.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
    const formattedNumber = parts.join(decimalSep);

    const spaceStr = space ? ' ' : '';
    
    if (position === 'after') {
        return `${formattedNumber}${spaceStr}${symbol}`;
    }
    return `${symbol}${spaceStr}${formattedNumber}`;
});


</script>
