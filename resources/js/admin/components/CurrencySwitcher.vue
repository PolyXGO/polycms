<template>
    <div class="topbar-settings-dropdown relative" v-click-outside="closeDropdown">
        <button 
            @click="toggleDropdown" 
            class="topbar-button currency-btn"
            :class="{ 'active': isOpen }"
            :title="$t('Switch Currency')"
        >
            <span class="topbar-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span class="ml-1 text-xs font-semibold uppercase">{{ currentCode }} {{ currentSymbol }}</span>
            <span class="ml-1">
                <svg class="w-2.5 h-2.5 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </span>
        </button>

        <transition name="settings-fade">
            <div v-if="isOpen" class="settings-dropdown-content currency-dropdown">
                <div class="settings-body py-1">
                    <button 
                        v-for="currency in currencies" 
                        :key="currency.code"
                        @click="switchCurrency(currency)"
                        class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-blue-400 flex items-center justify-between transition-colors"
                        :class="{'text-blue-400 bg-white/5': currentCode === currency.code}"
                    >
                        <span class="flex items-center gap-2">
                             <span>{{ currency.code }} - {{ currency.symbol }}</span>
                        </span>
                        <svg v-if="currentCode === currency.code" class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                    
                    <div v-if="currencies.length === 0" class="px-4 py-2 text-sm text-gray-500">
                        {{ $t('No currencies available') }}
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '../composables/useTranslation';

const { t } = useTranslation();
const $t = (key: string) => t(key);

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

const isOpen = ref(false);
const currencies = ref<Currency[]>([]);
const currentCode = ref<string>('USD');
const currentSymbol = ref<string>('$');

// Directive for clicking outside
const vClickOutside = {
    mounted(el: any, binding: any) {
        el._clickOutside = (event: Event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.addEventListener('click', el._clickOutside);
    },
    unmounted(el: any) {
        document.removeEventListener('click', el._clickOutside);
    },
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

const loadSettings = async () => {
    try {
        const response = await axios.get('/api/v1/settings/group/ecommerce');
        const settings = response.data.data || {};
        
        // Load Currencies
        if (settings.currencies) {
             try {
                const storedCurrencies = typeof settings.currencies.value === 'string' 
                    ? JSON.parse(settings.currencies.value) 
                    : settings.currencies.value;
                
                if (Array.isArray(storedCurrencies)) {
                    currencies.value = storedCurrencies;
                }
            } catch (e) { console.error(e); }
        }

        // Load current currency
        currentCode.value = settings.ecommerce_currency?.value || 'USD';
        currentSymbol.value = settings.ecommerce_currency_symbol?.value || '$';

    } catch (error) {
        console.error('Failed to load currency settings:', error);
    }
};

const switchCurrency = async (currency: Currency) => {
    if (currency.code === currentCode.value) {
        closeDropdown();
        return;
    }

    try {
        // Update settings in ecommerce group
        await axios.put('/api/v1/settings/group/ecommerce', {
            settings: {
                ecommerce_currency: currency.code,
                ecommerce_currency_symbol: currency.symbol,
                currency_decimals: currency.decimals,
                currency_symbol_position: currency.symbol_position,
                currency_thousands_separator: currency.thousands_separator,
                currency_decimal_separator: currency.decimal_separator,
                currency_space: currency.space_between
            }
        });

        // Update local state and reload
        currentCode.value = currency.code;
        currentSymbol.value = currency.symbol;
        closeDropdown();

        // Reload page to apply changes globally
        window.location.reload();
    } catch (error) {
        console.error('Failed to switch currency:', error);
        alert($t('Failed to switch currency. Please try again.'));
    }
};

onMounted(() => {
    loadSettings();
});
</script>

<style scoped>
.topbar-settings-dropdown {
    position: relative;
    display: inline-flex;
    align-items: center;
    height: 32px;
}

.currency-btn {
    padding: 0 8px !important;
    width: auto !important;
    justify-content: center !important;
    margin-left: 2px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    border-radius: 3px;
    transition: all 0.15s ease;
    color: #d1d5db;
}

.currency-btn:hover, .currency-btn.active {
    color: #60a5fa !important;
    background: rgba(255, 255, 255, 0.1);
}

.topbar-icon svg {
    width: 14px;
    height: 14px;
}

.settings-dropdown-content {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 4px;
    background: #1f2937;
    min-width: 200px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 6px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    z-index: 100001;
    overflow: hidden;
}

.settings-header {
    padding: 8px 12px;
    font-weight: 600;
    color: #9ca3af;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    text-transform: uppercase;
    letter-spacing: 0.025em;
    font-size: 11px;
}

.settings-fade-enter-active,
.settings-fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.settings-fade-enter-from,
.settings-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
