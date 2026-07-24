<template>
 <div class="currency-settings">
 <!-- Header -->
 <div class="mb-6 flex justify-between items-center">
 <div>
 <div class="mb-2 flex items-center gap-4">
 <router-link :to="{ name:'admin.settings.index' }" class="text-admin-theme-primary hover:text-admin-theme-primary-hover font-medium flex items-center text-sm">
 <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
 </svg>
 {{ t('Back to Hub') }}
 </router-link>
 <span class="text-gray-300">/</span>
 <router-link :to="{ name:'admin.settings.group', params: { group:'ecommerce' } }" class="text-admin-theme-primary hover:text-admin-theme-primary-hover font-medium text-sm">
 {{ t('Ecommerce') }}
 </router-link>
 </div>
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Currencies') }}</h1>
 </div>
 
 </div>

 <div v-if="loading" class="text-center py-12">
 <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
 <p class="mt-2 text-admin-theme-text-secondary">{{ t('Loading settings...') }}</p>
 </div>

 <div v-else class="space-y-6">
 <!-- Global Options -->
 <div class="bg-admin-theme-surface rounded-lg shadow-sm border border-admin-theme-border p-6">
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <!-- Auto-detect -->
 <div class="flex items-start">
 <div class="flex items-center h-5">
 <input
 id="auto_detect"
 v-model="form.auto_detect"
 type="checkbox"
 class="w-4 h-4 text-admin-theme-primary border-admin-theme-border rounded focus:ring-admin-theme-primary"
 />
 </div>
 <div class="ml-3 text-sm">
 <label for="auto_detect" class="font-medium text-admin-theme-text-secondary">{{ t('Enable auto-detect visitor currency') }}</label>
 <p class="text-admin-theme-text-muted">{{ t('Automatically detect and display prices in the visitor\'s local currency based on their location.') }}</p>
 </div>
 </div>

 <!-- API Provider Selection -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-2">{{ t('Exchange Rate API Provider') }}</label>
 <div class="flex items-center gap-4">
 <label class="inline-flex items-center">
 <input type="radio" v-model="form.api_provider" value="manual" class="form-radio text-admin-theme-primary">
 <span class="ml-2 text-admin-theme-text-secondary">{{ t('Manual') }}</span>
 </label>
 <label class="inline-flex items-center">
 <input type="radio" v-model="form.api_provider" value="apilayer" class="form-radio text-admin-theme-primary">
 <span class="ml-2 text-admin-theme-text-secondary">{{ t('API Layer') }}</span>
 </label>
 <label class="inline-flex items-center">
 <input type="radio" v-model="form.api_provider" value="openexchangerates" class="form-radio text-admin-theme-primary">
 <span class="ml-2 text-admin-theme-text-secondary">{{ t('Open Exchange Rates') }}</span>
 </label>
 </div>
 
 <div v-if="form.api_provider !== 'manual'" class="mt-4">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-2">{{ t('API Key') }}</label>
 <div class="flex gap-2">
 <input 
 v-if="form.api_provider === 'apilayer'"
 type="password" 
 v-model="form.apilayer_api_key" 
 class="form-input flex-1" 
 :placeholder="t('Enter your API Layer key')"
 >
 <input 
 v-else-if="form.api_provider === 'openexchangerates'"
 type="password" 
 v-model="form.openexchangerates_api_key" 
 class="form-input flex-1" 
 :placeholder="t('Enter your Open Exchange Rates app ID')"
 >
 <button 
 @click="syncRates" 
 :disabled="syncing"
 class="px-4 py-2 bg-admin-theme-base border border-admin-theme-border text-admin-theme-text rounded-md text-sm font-medium hover:bg-admin-theme-base/80 transition-colors whitespace-nowrap"
 >
 <span v-if="syncing" class="flex items-center gap-2">
 <div class="animate-spin h-4 w-4 border-2 border-admin-theme-primary border-t-transparent rounded-full"></div>
 {{ t('Syncing...') }}
 </span>
 <span v-else>{{ t('Sync Rates Now') }}</span>
 </button>
 </div>
 <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-admin-theme-text-muted">
 <span>{{ t('API key is required to fetch real-time exchange rates.') }}</span>
 <a 
 v-if="form.api_provider === 'apilayer'" 
 href="https://apilayer.com/marketplace/exchangerates_data-api" 
 target="_blank" 
 class="text-admin-theme-primary hover:text-admin-theme-primary-hover hover:underline flex items-center transition-colors"
 >
 {{ t('Get API Key from API Layer') }}
 <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
 </a>
 <a 
 v-else-if="form.api_provider === 'openexchangerates'" 
 href="https://openexchangerates.org/signup/free" 
 target="_blank" 
 class="text-admin-theme-primary hover:text-admin-theme-primary-hover hover:underline flex items-center transition-colors"
 >
 {{ t('Get API Key from Open Exchange Rates') }}
 <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Currency List -->
 <div class="bg-admin-theme-surface rounded-lg shadow-sm border border-admin-theme-border overflow-hidden">
 <div class="p-4 bg-admin-theme-base border-b border-admin-theme-border flex justify-between items-center">
 <h2 class="font-semibold text-admin-theme-text">{{ t('Currencies') }}</h2>
 <button @click="addCurrency" class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-md text-sm font-medium hover:bg-admin-theme-primary-hover transition-colors">
 + {{ t('ADD CURRENCY') }}
 </button>
 </div>
 
 <div class="divide-y divide-admin-theme-border" :key="currenciesCheckKey">
 <!-- Header Row -->
 <div class="grid grid-cols-12 gap-4 bg-admin-theme-base/50 px-6 py-3 text-xs font-medium text-admin-theme-text-muted uppercase tracking-wider">
 <div class="col-span-1"></div>
 <div class="col-span-2">{{ t('Code') }}</div>
 <div class="col-span-2">{{ t('Symbol') }}</div>
 <div class="col-span-2">{{ t('Exchange Rate') }}</div>
 <div class="col-span-2 text-center">{{ t('Default') }}</div>
 <div class="col-span-3 text-right">{{ t('Actions') }}</div>
 </div>

 <!-- Currency Rows -->
 <div 
 v-for="(currency, index) in currencies" 
 :key="index" 
 class="currency-row relative group border-b border-admin-theme-border last:border-0 transition-all duration-200"
 :class="{ 
'bg-admin-theme-primary/10/50 border-indigo-200 dark:border-indigo-800': dragOverIndex === index,
'opacity-40 border-2 border-dashed border-indigo-400 dark:border-admin-theme-primary scale-[0.98] z-50': draggingIndex === index
 }"
 @dragover="handleDragOver(index, $event)"
 @drop="handleDrop(index, $event)"
 >
 <!-- Border highlight for expanded item -->
 <div v-if="expandedIndex === index" class="absolute left-0 top-0 bottom-0 w-1 bg-admin-theme-primary"></div>

 <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-admin-theme-base/50 transition-colors">
 <!-- Drag Handle -->
 <div class="col-span-1 flex items-center">
 <div 
 class="cursor-move text-admin-theme-text-muted hover:text-admin-theme-text p-2 -ml-2 rounded-md hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
 draggable="true"
 @dragstart="onDragStart(index, $event)"
 @dragend="handleDragEnd"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
 </svg>
 </div>
 </div>

 <!-- Code -->
 <div class="col-span-2 relative">
 <input 
 type="text" 
 v-model="currency.code" 
 class="form-input w-full uppercase" 
 placeholder="USD"
 list="standard-currencies"
 @input="onCodeChange(currency)"
 >
 <datalist id="standard-currencies">
 <option v-for="std in availableCurrencies" :key="std.code" :value="std.code">
 {{ std.label }}
 </option>
 </datalist>
 </div>
 
 <!-- Symbol -->
 <div class="col-span-2">
 <input 
 type="text" 
 v-model="currency.symbol" 
 class="form-input w-full" 
 placeholder="$"
 >
 </div>
 
 <!-- Rate -->
 <div class="col-span-2">
 <input 
 type="number" 
 v-model.number="currency.rate" 
 step="0.0001" 
 class="form-input w-full" 
 :disabled="currency.is_default"
 >
 </div>
 
 <!-- Default Toggle -->
 <div class="col-span-2 flex justify-center">
 <label class="cursor-pointer">
 <input 
 type="radio" 
 name="default_currency" 
 :checked="currency.is_default"
 @change="setDefault(index)"
 class="form-radio text-admin-theme-primary w-4 h-4 cursor-pointer focus:ring-admin-theme-primary"
 >
 </label>
 </div>
 
 <!-- Actions -->
 <div class="col-span-3 flex justify-end items-center gap-3">
 <button 
 @click="toggleExpand(index)" 
 class="p-2 rounded hover:bg-admin-theme-base transition-colors"
 :class="expandedIndex === index ?'text-admin-theme-primary bg-admin-theme-primary/10/30' :'text-admin-theme-text-muted hover:text-admin-theme-text'"
 :title="t('Settings')"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
 </svg>
 </button>
 <button 
 @click="removeCurrency(index)" 
 class="p-2 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
 :title="t('Remove')"
 :disabled="currencies.length <= 1"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
 </svg>
 </button>
 </div>
 </div>

 <!-- Expanded Settings -->
 <div v-show="expandedIndex === index" class="px-6 pb-6 pt-0 bg-admin-theme-base/50 border-t border-admin-theme-border">
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
 <!-- Number of Decimals -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Number of decimals') }}</label>
 <input 
 type="number" 
 v-model.number="currency.decimals" 
 min="0" 
 max="8" 
 class="form-input w-full"
 >
 </div>

 <!-- Number Format Preset -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Number format') }}</label>
 <select 
 :value="getFormatPreset(currency)"
 @change="updateFormatFromPreset(currency, ($event.target as HTMLSelectElement).value)"
 class="form-select w-full"
 >
 <option value="western">Western (1,234,567.89)</option>
 <option value="european">European (1.234.567,89)</option>
 <option value="space">Space (1 234 567,89)</option>
 <option value="indian">Indian (12,34,567.89)</option>
 </select>
 </div>

 <!-- Position of Symbol -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Position of symbol') }}</label>
 <select v-model="currency.symbol_position" class="form-select w-full">
 <option value="before">{{ t('Before number') }} ({{ currency.symbol }}100)</option>
 <option value="after">{{ t('After number') }} (100{{ currency.symbol }})</option>
 </select>
 </div>
 </div>

 <div class="mt-4">
 <label class="inline-flex items-center">
 <input 
 type="checkbox" 
 v-model="currency.space_between" 
 class="form-checkbox text-admin-theme-primary rounded border-gray-300 focus:ring-admin-theme-primary"
 >
 <span class="ml-2 text-sm text-admin-theme-text-secondary">{{ t('Add a space between price and currency') }}</span>
 </label>
 </div>
 </div>
 </div>
 </div>

 <div v-if="currencies.length === 0" class="text-center py-8 text-gray-500 bg-admin-theme-base">
 {{ t('No currencies defined.') }}
 </div>
 </div>

 <!-- Footer Warning -->
 <div class="flex items-start p-4 bg-admin-theme-primary/5 border border-admin-theme-primary/20 rounded-lg">
 <svg class="w-5 h-5 text-admin-theme-primary mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <div class="text-sm text-admin-theme-text-secondary">
 <p class="font-medium mb-1 text-admin-theme-text">{{ t('About Default Currency') }}</p>
 <p>{{ t('The default currency must have an exchange rate of 1.') }}</p>
 </div>
 </div>
 
 <!-- Save Button Area -->
 <div class="flex justify-end pt-4 border-t border-admin-theme-border">
 <button @click="saveSettings" :disabled="saving" class="px-6 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover disabled:opacity-50 transition-colors shadow-sm">
 <span v-if="saving" class="flex items-center">
 <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
 </svg>
 {{ t('Saving...') }}
 </span>
 <span v-else>{{ t('Save Changes') }}</span>
 </button>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from'vue';
import axios from'axios';
import { useTranslation } from'../../composables/useTranslation';
import { useDialog } from'../../composables/useDialog';
import { useSortable } from'../../composables/useSortable';

const { t } = useTranslation();
const dialog = useDialog();

const standardCurrencies = [
 {"code":"VND","symbol":"₫","label":"VND ₫" },
 {"code":"USD","symbol":"$","label":"USD $" },
 {"code":"AED","symbol":"AED","label":"AED AED" },
 {"code":"AUD","symbol":"$","label":"AUD $" },
 {"code":"CAD","symbol":"C$","label":"CAD C$" },
 {"code":"CHF","symbol":"CHF","label":"CHF CHF" },
 {"code":"CLP","symbol":"$","label":"CLP $" },
 {"code":"CNY","symbol":"¥","label":"CNY ¥" },
 {"code":"COP","symbol":"$","label":"COP $" },
 {"code":"DKK","symbol":"kr","label":"DKK kr" },
 {"code":"EUR","symbol":"€","label":"EUR €" },
 {"code":"GBP","symbol":"£","label":"GBP £" },
 {"code":"HKD","symbol":"HK$","label":"HKD HK$" },
 {"code":"IDR","symbol":"Rp","label":"IDR Rp" },
 {"code":"ILS","symbol":"₪","label":"ILS ₪" },
 {"code":"INR","symbol":"₹","label":"INR ₹" },
 {"code":"JPY","symbol":"¥","label":"JPY ¥" },
 {"code":"KRW","symbol":"₩","label":"KRW ₩" },
 {"code":"MXN","symbol":"MXN","label":"MXN MXN" },
 {"code":"MYR","symbol":"RM","label":"MYR RM" },
 {"code":"NZD","symbol":"$","label":"NZD $" },
 {"code":"PEN","symbol":"S/","label":"PEN S/" },
 {"code":"PHP","symbol":"₱","label":"PHP ₱" },
 {"code":"PKR","symbol":"₨","label":"PKR ₨" },
 {"code":"PLN","symbol":"zł","label":"PLN zł" },
 {"code":"SAR","symbol":"SAR","label":"SAR SAR" },
 {"code":"SEK","symbol":"kr","label":"SEK kr" },
 {"code":"SGD","symbol":"SG$","label":"SGD SG$" },
 {"code":"THB","symbol":"฿","label":"THB ฿" },
 {"code":"TWD","symbol":"NT$","label":"TWD NT$" },
 {"code":"UAH","symbol":"₴","label":"UAH ₴" },
 {"code":"ZAR","symbol":"R","label":"ZAR R" }
];

const availableCurrencies = computed(() => {
 const selectedCodes = currencies.value.map(c => c.code?.toUpperCase() ||'');
 return standardCurrencies.filter(std => !selectedCodes.includes(std.code.toUpperCase()));
});

const loading = ref(false);
const saving = ref(false);
const expandedIndex = ref<number | null>(null);
const currenciesCheckKey = ref(0);

interface Currency {
 code: string;
 symbol: string;
 rate: number;
 is_default: boolean;
 decimals: number;
 symbol_position:'before' |'after';
 space_between: boolean;
 thousands_separator: string;
 decimal_separator: string;
}

interface FormattingOptions {
 auto_detect: boolean;
 api_provider: string;
 apilayer_api_key?: string;
 openexchangerates_api_key?: string;
 use_api_rate: boolean;
}

const form = ref<FormattingOptions>({
 auto_detect: false,
 api_provider:'manual',
 apilayer_api_key:'',
 openexchangerates_api_key:'',
 use_api_rate: false,
});

const currencies = ref<Currency[]>([]);
const syncing = ref(false);

// Drag and drop for currencies
const {
 draggingIndex,
 dragOverIndex,
 handleDragStart: sortableHandleDragStart,
 handleDragOver,
 handleDragEnd,
 handleDrop
} = useSortable(currencies, {
 onSort: () => saveSettings()
});

const onDragStart = (index: number, event: DragEvent) => {
 const target = event.target as HTMLElement;
 const row = target.closest('.currency-row') as HTMLElement;
 if (row && event.dataTransfer) {
 // Create full row drag image
 const rect = row.getBoundingClientRect();
 event.dataTransfer.setDragImage(row, event.clientX - rect.left, event.clientY - rect.top);
 }
 sortableHandleDragStart(index, event); // Call the aliased function
};

const loadSettings = async () => {
 loading.value = true;
 try {
 const response = await axios.get('/api/v1/settings/group/ecommerce');
 const settings = response.data.data || {};
 
 // Load Global Options
 if (settings.currency_formatting_rules) {
 try {
 const rules = typeof settings.currency_formatting_rules.value ==='string' 
 ? JSON.parse(settings.currency_formatting_rules.value) 
 : settings.currency_formatting_rules.value;
 form.value = { ...form.value, ...rules };
 if (!form.value.api_provider) {
 form.value.api_provider ='manual';
 }
 } catch (e) { console.error(e); }
 }

 // Load Currencies
 if (settings.currencies) {
 try {
 const storedCurrencies = typeof settings.currencies.value ==='string' 
 ? JSON.parse(settings.currencies.value) 
 : settings.currencies.value;
 
 if (Array.isArray(storedCurrencies)) {
 // Ensure all fields exist
 currencies.value = storedCurrencies.map(c => ({
 ...c,
 space_between: c.space_between ?? false,
 thousands_separator: c.thousands_separator ??',',
 decimal_separator: c.decimal_separator ??'.',
 }));
 }
 } catch (e) { console.error(e); }
 }

 if (currencies.value.length === 0) {
 // Defaults
 currencies.value.push({
 code: settings.ecommerce_currency?.value ||'USD',
 symbol: settings.ecommerce_currency_symbol?.value ||'$',
 rate: 1,
 is_default: true,
 decimals: 2,
 symbol_position:'before',
 space_between: false,
 thousands_separator:',',
 decimal_separator:'.'
 });
 }

 } catch (error) {
 console.error('Error loading settings', error);
 dialog.error(t('Failed to load settings'));
 } finally {
 loading.value = false;
 }
};

const saveSettings = async () => {
 saving.value = true;
 try {
 // Prepare payload using the standard type format
 const payload = {
 currency_formatting_rules: {
 value: form.value,
 type: 'json'
 },
 currencies: {
 value: currencies.value,
 type: 'array'
 },
 };
 
 // Removed legacy global fields update to decouple Store Currency from Default Currency
 // The Store Currency (ecommerce_currency) is now managed solely in EcommerceSettings.vue
 // Formatting is derived dynamically from the currencies list based on the selected Store Currency.

 await axios.put('/api/v1/settings/group/ecommerce', {
 settings: payload
 });

 dialog.success(t('Settings saved successfully'));
 } catch (error) {
 console.error('Error saving settings', error);
 dialog.error(t('Failed to save settings'));
 } finally {
 saving.value = false;
 }
};

const syncRates = async () => {
 const currentKey = form.value.api_provider === 'apilayer' ? form.value.apilayer_api_key : form.value.openexchangerates_api_key;

 if (!currentKey) {
 dialog.error(t('Please enter an API Key first.'));
 return;
 }

 if (currencies.value.length === 0) {
 dialog.error(t('Please add at least one currency to sync.'));
 return;
 }

 syncing.value = true;
 try {
 // Find the default currency code
 const defaultCurrency = currencies.value.find(c => c.is_default);
 const defaultCode = defaultCurrency ? defaultCurrency.code :'USD';

 const response = await axios.post('/api/v1/settings/ecommerce/currencies/sync', {
 currencies: currencies.value,
 api_provider: form.value.api_provider,
 api_key: currentKey,
 default_currency_code: defaultCode
 });

 if (response.data.success && response.data.data) {
 currencies.value = response.data.data;
 currenciesCheckKey.value++; // Force re-render
 
 // Auto-save after successful sync to record the API key and new rates
 await saveSettings();
 dialog.success(t('Exchange rates synced and saved successfully.'));
 }
 } catch (error: any) {
 console.error('Error syncing rates:', error);
 dialog.error(error.response?.data?.message || t('Failed to sync exchange rates.'));
 } finally {
 syncing.value = false;
 }
};

const addCurrency = () => {
 currencies.value.push({
 code:'',
 symbol:'',
 rate: 1,
 is_default: false,
 decimals: 2,
 symbol_position:'before',
 space_between: false,
 thousands_separator:',',
 decimal_separator:'.'
 });
 // Automatically expand new item
 expandedIndex.value = currencies.value.length - 1;
};

const removeCurrency = async (index: number) => {
 if (currencies.value[index].is_default) {
 dialog.error(t('Cannot remove the default currency.'));
 return;
 }

 const confirmed = await dialog.confirm({
 title: t('Remove Currency'),
 message: t(`Are you sure you want to remove ${currencies.value[index].code}? This action cannot be undone.`),
 confirmButtonText: t('Remove'),
 cancelButtonText: t('Cancel'),
 type: 'danger'
 });

 if (confirmed) {
 currencies.value.splice(index, 1);
 saveSettings();
 }
 expandedIndex.value = null; 
};


const setDefault = async (index: number) => {
 // Check if clicking on already default currency
 if (currencies.value[index].is_default) return;

 const confirmed = await dialog.confirm({
 title: t('Change Default (Base) Currency?'),
 message: t('Warning: You are changing the Base Currency. This will NOT convert existing product prices. All prices in the database will be treated as the NEW currency values. You must manually update all product prices after this change. Continue?'),
 confirmText: t('Yes, I understand'),
 cancelText: t('Cancel'),
 });

 if (!confirmed) {
 // Force reactivity update to revert radio button selection if cancelled
 // This is a bit tricky with radio buttons bound to array, usually key-re-render helps
 // But here we rely on the fact that data didn't change, so component re-render should fix UI
 currenciesCheckKey.value++;
 return;
 }

 const newDefaultCurrency = currencies.value[index];
 const oldRate = newDefaultCurrency.rate;

 if (oldRate <= 0) {
 return;
 }

 currencies.value.forEach((currency, i) => {
 if (i === index) {
 currency.is_default = true;
 currency.rate = 1;
 } else {
 currency.is_default = false;
 // Calculate new rate relative to the new default
 if (currency.rate > 0) {
 let newRate = currency.rate / oldRate;
 // Use strict precision to avoid floating point artifacts but keep enough detail
 currency.rate = parseFloat(newRate.toFixed(8));
 }
 }
 });
};

const onCodeChange = (currency: Currency) => {
 const code = currency.code.toUpperCase();
 const standard = standardCurrencies.find(s => s.code === code);
 if (standard) {
 currency.symbol = standard.symbol;
 }
};

const toggleExpand = (index: number) => {
 expandedIndex.value = expandedIndex.value === index ? null : index;
};

const getFormatPreset = (currency: Currency): string => {
 const t = currency.thousands_separator;
 const d = currency.decimal_separator;
 
 if (t ===',' && d ==='.') return'western';
 if (t ==='.' && d ===',') return'european';
 if (t ==='' && d ===',') return'space';
 // Add logic for others if needed
 return'custom';
};

const updateFormatFromPreset = (currency: Currency, preset: string) => {
 switch (preset) {
 case'western':
 currency.thousands_separator =',';
 currency.decimal_separator ='.';
 break;
 case'european':
 currency.thousands_separator ='.';
 currency.decimal_separator =',';
 break;
 case'space':
 currency.thousands_separator ='';
 currency.decimal_separator =',';
 break;
 case'indian':
 // Indian numbering system is complex and not just separators, but for now we basically default to standard separators
 currency.thousands_separator =',';
 currency.decimal_separator ='.';
 break;
 }
};

onMounted(() => {
 loadSettings();
});
</script>

<style scoped>
.form-input, .form-select {
 @apply border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/30 focus:ring-opacity-50 text-sm;
}
</style>
