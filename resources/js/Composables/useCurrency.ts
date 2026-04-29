import { usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import axios from 'axios';

interface CurrencySettings {
    code: string;
    symbol: string;
    symbol_position: 'before' | 'after';
    thousands_separator: string;
    decimal_separator: string;
    decimals: number;
    space_between: boolean;
}

// Shared state for non-Inertia environments (like Admin SPA)
const adminSettings = reactive<{
    currency: Partial<CurrencySettings> | null;
    currencies: any[];
    loading: boolean;
    loaded: boolean;
}>({
    currency: null,
    currencies: [], // Store list of all currencies
    loading: false,
    loaded: false,
});

async function loadAdminSettings() {
    if (adminSettings.loaded || adminSettings.loading) return;
    
    adminSettings.loading = true;
    try {
        const response = await axios.get('/api/v1/settings/group/ecommerce');
        if (response.data?.success && response.data?.data) {
            const data = response.data.data;
            adminSettings.currency = {
                code: data.ecommerce_currency?.value || 'USD',
                symbol: data.ecommerce_currency_symbol?.value || '$',
                symbol_position: data.currency_symbol_position?.value || 'before',
                thousands_separator: data.currency_thousands_separator?.value || ',',
                decimal_separator: data.currency_decimal_separator?.value || '.',
                decimals: Number(data.currency_decimals?.value ?? 2),
                space_between: Boolean(data.currency_space?.value),
            };

            // Parse currencies list
            if (data.currencies?.value) {
                try {
                    const parsed = typeof data.currencies.value === 'string' 
                        ? JSON.parse(data.currencies.value) 
                        : data.currencies.value;
                    adminSettings.currencies = Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    console.error('Failed to parse currencies', e);
                    adminSettings.currencies = [];
                }
            }

            adminSettings.loaded = true;
        }
    } catch (error) {
        console.error('Failed to load currency settings:', error);
    } finally {
        adminSettings.loading = false;
    }
}

export function useCurrency() {
    const page = usePage();
    
    // Check if we need to load settings for admin
    const isAdmin = typeof window !== 'undefined' && window.location.pathname.includes('/admin');
    if (!page?.props?.settings && isAdmin && !adminSettings.loaded && !adminSettings.loading) {
        loadAdminSettings();
    }
    
    // Safely access settings with fallbacks
    const settings = computed<CurrencySettings>(() => {
        const defaultSettings: CurrencySettings = {
            code: 'USD',
            symbol: '$',
            symbol_position: 'before',
            thousands_separator: ',',
            decimal_separator: '.',
            decimals: 2,
            space_between: false
        };

        const pageSettings = page?.props?.settings as any;
        const inertiaCurrency = pageSettings?.currency;
        
        // Use Inertia props if available, otherwise fallback to admin settings
        const activeCurrency = inertiaCurrency || adminSettings.currency || {};
        
        return { ...defaultSettings, ...activeCurrency };
    });

    const formatCurrency = (amount: number | string | null | undefined, targetCode?: string) => {
        if (amount === null || amount === undefined) {
             return formatValue(0, targetCode);
        }

        let value = Number(amount);
        if (isNaN(value)) {
            return String(amount);
        }
        
        const currenciesList = (page?.props?.settings as any)?.currencies || adminSettings.currencies || [];
        const currentSettings = settings.value;
        const displayCurrencyCode = targetCode || currentSettings.code;

        // Apply conversion ONLY if no targetCode is provided (assuming default display conversion)
        // OR if you explicitly want to convert to that targetCode.
        // For Orders, we usually have the amount in that currency already.
        // For Products, we want to convert from base to display.
        
        if (!targetCode && Array.isArray(currenciesList) && currenciesList.length > 0) {
            const currencyObj = currenciesList.find((c: any) => c.code === displayCurrencyCode);
            if (currencyObj && currencyObj.rate) {
                const rate = Number(currencyObj.rate);
                if (!isNaN(rate) && rate > 0) {
                    value = value * rate;
                }
            }
        }

        return formatValue(value, targetCode);
    };

    const formatValue = (value: number, targetCode?: string) => {
        let currentSettings = settings.value;

        // If targetCode is provided, try to find its specific settings (symbol, position, etc.)
        if (targetCode) {
            const currenciesList = (page?.props?.settings as any)?.currencies || adminSettings.currencies || [];
            const currencyObj = currenciesList.find((c: any) => c.code === targetCode);
            if (currencyObj) {
                currentSettings = {
                    ...currentSettings,
                    code: currencyObj.code,
                    symbol: currencyObj.symbol || '$',
                    symbol_position: currencyObj.symbol_position || 'before',
                    thousands_separator: currencyObj.thousands_separator || ',',
                    decimal_separator: currencyObj.decimal_separator || '.',
                    decimals: Number(currencyObj.decimals ?? 2),
                    space_between: Boolean(currencyObj.space_between),
                };
            }
        }

        const {
            symbol,
            symbol_position,
            thousands_separator,
            decimal_separator,
            decimals,
            space_between,
        } = currentSettings;

        const _decimals = Math.max(0, Number(decimals));

        // Fix logic: toFixed uses dot, we need to replace it
        // and add thousands separators
        const fixed = value.toFixed(_decimals);
        const [integerPart, decimalPart] = fixed.split('.');
        
        const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousands_separator);
        
        const formattedAmount = decimalPart 
            ? `${formattedInteger}${decimal_separator}${decimalPart}`
            : formattedInteger;

        const space = space_between ? ' ' : '';

        if (symbol_position === 'after') {
            return `${formattedAmount}${space}${symbol}`;
        }
        
        return `${symbol}${space}${formattedAmount}`;
    };

    return {
        formatCurrency,
        settings,
        adminSettings: {
            loaded: adminSettings.loaded,
            loading: adminSettings.loading
        }
    };
}
