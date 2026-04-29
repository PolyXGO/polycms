<template>
    <div class="currency-settings">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <div class="mb-2 flex items-center gap-4">
                    <router-link :to="{ name: 'admin.settings.index' }" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        {{ t('Back to Hub') }}
                    </router-link>
                    <span class="text-gray-300">/</span>
                    <router-link :to="{ name: 'admin.settings.group', params: { group: 'ecommerce' } }" class="text-indigo-600 hover:text-indigo-700 font-medium text-sm">
                        {{ t('Ecommerce') }}
                    </router-link>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('Currencies') }}</h1>
            </div>
            
        </div>

        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ t('Loading settings...') }}</p>
        </div>

        <div v-else class="space-y-6">
             <!-- Global Options -->
             <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Auto-detect -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="auto_detect"
                                v-model="form.auto_detect"
                                type="checkbox"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                            />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="auto_detect" class="font-medium text-gray-700 dark:text-gray-300">{{ t('Enable auto-detect visitor currency') }}</label>
                            <p class="text-gray-500 dark:text-gray-400">{{ t('Automatically detect and display prices in the visitor\'s local currency based on their location.') }}</p>
                        </div>
                    </div>

                    <!-- API Provider Selection -->
                     <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ t('Exchange Rate API Provider') }}</label>
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="radio" v-model="form.api_provider" value="" class="form-radio text-indigo-600">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ t('Manual') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" v-model="form.api_provider" value="apilayer" class="form-radio text-indigo-600">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ t('API Layer') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" v-model="form.api_provider" value="openexchangerates" class="form-radio text-indigo-600">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ t('Open Exchange Rates') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Currency List -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="font-semibold text-gray-900 dark:text-white">{{ t('Currencies') }}</h2>
                    <button @click="addCurrency" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors">
                        + {{ t('ADD CURRENCY') }}
                    </button>
                </div>
                
                <div class="divide-y divide-gray-200 dark:divide-gray-700" :key="currenciesCheckKey">
                    <!-- Header Row -->
                     <div class="grid grid-cols-12 gap-4 bg-gray-50/50 dark:bg-gray-800/50 px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
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
                        class="currency-row relative group border-b border-gray-100 dark:border-gray-700 last:border-0 transition-all duration-200"
                        :class="{ 
                            'bg-indigo-50/50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800': dragOverIndex === index,
                            'opacity-40 border-2 border-dashed border-indigo-400 dark:border-indigo-500 scale-[0.98] z-50': draggingIndex === index
                        }"
                        @dragover="handleDragOver(index, $event)"
                        @drop="handleDrop(index, $event)"
                    >
                        <!-- Border highlight for expanded item -->
                        <div v-if="expandedIndex === index" class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-600"></div>

                        <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <!-- Drag Handle -->
                            <div class="col-span-1 flex items-center">
                                <div 
                                    class="cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 -ml-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
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
                                        class="form-radio text-indigo-600 w-4 h-4 cursor-pointer focus:ring-indigo-500"
                                    >
                                </label>
                            </div>
                            
                            <!-- Actions -->
                            <div class="col-span-3 flex justify-end items-center gap-3">
                                <button 
                                    @click="toggleExpand(index)" 
                                    class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                                    :class="expandedIndex === index ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
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
                        <div v-show="expandedIndex === index" class="px-6 pb-6 pt-0 bg-gray-50/50 dark:bg-gray-800/20 border-t border-gray-100 dark:border-gray-700">
                           <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                                <!-- Number of Decimals -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('Number of decimals') }}</label>
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
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('Number format') }}</label>
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
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('Position of symbol') }}</label>
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
                                        class="form-checkbox text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ t('Add a space between price and currency') }}</span>
                                </label>
                           </div>
                        </div>
                    </div>
                </div>

                <div v-if="currencies.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 dark:bg-gray-800">
                    {{ t('No currencies defined.') }}
                </div>
            </div>

            <!-- Footer Warning -->
            <div class="flex items-start p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-yellow-700 dark:text-yellow-400">
                    <p class="font-medium mb-1">{{ t('About Default Currency') }}</p>
                    <p>{{ t('The default currency must have an exchange rate of 1.') }}</p>
                </div>
            </div>
            
            <!-- Save Button Area -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                 <button @click="saveSettings" :disabled="saving" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors shadow-sm">
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
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';
import { useSortable } from '../../composables/useSortable';

const { t } = useTranslation();
const dialog = useDialog();

const standardCurrencies = [
  { "code": "VND", "symbol": "₫",  "label": "VND ₫" },
  { "code": "USD", "symbol": "$",  "label": "USD $" },
  { "code": "AED", "symbol": "AED", "label": "AED AED" },
  { "code": "AUD", "symbol": "$",   "label": "AUD $" },
  { "code": "CAD", "symbol": "C$",  "label": "CAD C$" },
  { "code": "CHF", "symbol": "CHF", "label": "CHF CHF" },
  { "code": "CLP", "symbol": "$",   "label": "CLP $" },
  { "code": "CNY", "symbol": "¥",   "label": "CNY ¥" },
  { "code": "COP", "symbol": "$",   "label": "COP $" },
  { "code": "DKK", "symbol": "kr",  "label": "DKK kr" },
  { "code": "EUR", "symbol": "€",   "label": "EUR €" },
  { "code": "GBP", "symbol": "£",   "label": "GBP £" },
  { "code": "HKD", "symbol": "HK$", "label": "HKD HK$" },
  { "code": "IDR", "symbol": "Rp",  "label": "IDR Rp" },
  { "code": "ILS", "symbol": "₪",   "label": "ILS ₪" },
  { "code": "INR", "symbol": "₹",   "label": "INR ₹" },
  { "code": "JPY", "symbol": "¥",   "label": "JPY ¥" },
  { "code": "KRW", "symbol": "₩",   "label": "KRW ₩" },
  { "code": "MXN", "symbol": "MXN", "label": "MXN MXN" },
  { "code": "MYR", "symbol": "RM",  "label": "MYR RM" },
  { "code": "NZD", "symbol": "$",   "label": "NZD $" },
  { "code": "PEN", "symbol": "S/",  "label": "PEN S/" },
  { "code": "PHP", "symbol": "₱",   "label": "PHP ₱" },
  { "code": "PKR", "symbol": "₨",   "label": "PKR ₨" },
  { "code": "PLN", "symbol": "zł",  "label": "PLN zł" },
  { "code": "SAR", "symbol": "SAR", "label": "SAR SAR" },
  { "code": "SEK", "symbol": "kr",  "label": "SEK kr" },
  { "code": "SGD", "symbol": "SG$", "label": "SGD SG$" },
  { "code": "THB", "symbol": "฿",   "label": "THB ฿" },
  { "code": "TWD", "symbol": "NT$", "label": "TWD NT$" },
  { "code": "UAH", "symbol": "₴",   "label": "UAH ₴" },
  { "code": "ZAR", "symbol": "R",   "label": "ZAR R" }
];

const availableCurrencies = computed(() => {
    const selectedCodes = currencies.value.map(c => c.code?.toUpperCase() || '');
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
    symbol_position: 'before' | 'after';
    space_between: boolean;
    thousands_separator: string;
    decimal_separator: string;
}

interface FormattingOptions {
    auto_detect: boolean;
    api_provider: string;
    use_api_rate: boolean;
}

const form = ref<FormattingOptions>({
    auto_detect: false,
    api_provider: '',
    use_api_rate: false,
});

const currencies = ref<Currency[]>([]);

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
                const rules = typeof settings.currency_formatting_rules.value === 'string' 
                    ? JSON.parse(settings.currency_formatting_rules.value) 
                    : settings.currency_formatting_rules.value;
                form.value = { ...form.value, ...rules };
            } catch (e) { console.error(e); }
        }

        // Load Currencies
        if (settings.currencies) {
             try {
                const storedCurrencies = typeof settings.currencies.value === 'string' 
                    ? JSON.parse(settings.currencies.value) 
                    : settings.currencies.value;
                
                if (Array.isArray(storedCurrencies)) {
                    // Ensure all fields exist
                    currencies.value = storedCurrencies.map(c => ({
                        ...c,
                        space_between: c.space_between ?? false,
                        thousands_separator: c.thousands_separator ?? ',',
                        decimal_separator: c.decimal_separator ?? '.',
                    }));
                }
            } catch (e) { console.error(e); }
        }

        if (currencies.value.length === 0) {
            // Defaults
            currencies.value.push({
                code: settings.ecommerce_currency?.value || 'USD',
                symbol: settings.ecommerce_currency_symbol?.value || '$',
                rate: 1,
                is_default: true,
                decimals: 2,
                symbol_position: 'before',
                space_between: false,
                thousands_separator: ',',
                decimal_separator: '.'
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
        // Prepare payload
        const payload = {
            currency_formatting_rules: JSON.stringify(form.value),
            currencies: JSON.stringify(currencies.value),
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

const addCurrency = () => {
    currencies.value.push({
        code: '',
        symbol: '',
        rate: 1,
        is_default: false,
        decimals: 2,
        symbol_position: 'before',
        space_between: false,
        thousands_separator: ',',
        decimal_separator: '.'
    });
    // Automatically expand new item
    expandedIndex.value = currencies.value.length - 1;
};

const removeCurrency = (index: number) => {
    if (currencies.value[index].is_default) {
        dialog.error(t('Cannot remove the default currency.'));
        return;
    }
    currencies.value.splice(index, 1);
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
    
    if (t === ',' && d === '.') return 'western';
    if (t === '.' && d === ',') return 'european';
    if (t === ' ' && d === ',') return 'space';
    // Add logic for others if needed
    return 'custom';
};

const updateFormatFromPreset = (currency: Currency, preset: string) => {
    switch (preset) {
        case 'western':
            currency.thousands_separator = ',';
            currency.decimal_separator = '.';
            break;
        case 'european':
            currency.thousands_separator = '.';
            currency.decimal_separator = ',';
            break;
        case 'space':
            currency.thousands_separator = ' ';
            currency.decimal_separator = ',';
            break;
        case 'indian':
             // Indian numbering system is complex and not just separators, but for now we basically default to standard separators
             currency.thousands_separator = ',';
             currency.decimal_separator = '.';
             break;
    }
};

onMounted(() => {
    loadSettings();
});
</script>

<style scoped>
.form-input, .form-select {
    @apply border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm;
}
</style>
