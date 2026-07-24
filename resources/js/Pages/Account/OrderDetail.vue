<template>
    <AccountLayout>
        <template #header>
            {{ t('My Account') }}
        </template>

        <div class="overflow-hidden bg-white dark:bg-[#111] shadow-sm sm:rounded-xl border border-gray-200 dark:border-zinc-800 transition-colors duration-300">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex items-center justify-between mb-6">
                        <Link :href="route('account.orders')" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors">
                        &larr; {{ t('Back to Orders') }}
                    </Link>
                    <h3 class="text-xl font-bold">{{ t('Order') }} #{{ props.order?.code }}</h3>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('Date Placed') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ props.order ? new Date(props.order.created_at).toLocaleDateString() : '-' }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('Total Amount') }}</dt>
                            <dd class="mt-1 text-sm text-green-600 dark:text-green-400 font-bold">{{ formatCurrency(props.order?.total_amount) }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('Status') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 uppercase tracking-wide font-semibold text-xs transition-colors">{{ t(props.order?.status || '') }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('Payment Method') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 font-medium">{{ t(getPaymentMethodName(props.order?.payment_method)) }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('Billing Address') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200" v-if="props.order?.billing_address">
                                {{ props.order.billing_address.full_name }}<br>
                                {{ props.order.billing_address.address_line }}<br>
                                {{ props.order.billing_address.city }} {{ props.order.billing_address.postal_code }}<br>
                                {{ props.order.billing_address.country }}
                            </dd>
                            <dd class="mt-1 text-sm text-gray-400 dark:text-gray-500 italic" v-else>{{ t('No address provided.') }}</dd>
                        </div>
                    </dl>
                    
                    <!-- Bank Transfer Section -->
                    <div v-if="props.order?.payment_method === 'bank_transfer' && props.bank_transfer_config && props.order?.payment_status !== 'paid' && props.order?.status !== 'completed'" class="mt-8 bg-gray-50 dark:bg-gray-800/50 p-6 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                         <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ t('Payment Instructions') }}</h4>
                         <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ props.bank_transfer_config.instructions }}</p>
                         
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ t('Transfer Content (Memo)') }}</p>
                                <code class="block w-full bg-gray-50 dark:bg-black px-4 py-3 rounded-lg border border-gray-200 dark:border-zinc-800 font-mono font-bold text-lg select-all text-center text-gray-900 dark:text-gray-100">
                                    {{ props.order.code }}
                                </code>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ t('Please include this code in your transfer description.') }}</p>
                            </div>
                            
                            <div v-if="props.bank_transfer_config.banks && props.bank_transfer_config.banks.length > 0">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ t('Bank Accounts') }}</p>
                                <div class="space-y-3">
                                     <div v-for="(bank, idx) in props.bank_transfer_config.banks" :key="idx" class="bg-gray-50 dark:bg-black p-3 rounded-lg border border-gray-200 dark:border-zinc-800 flex items-start gap-4">
                                        <!-- Logo -->
                                        <div v-if="bank.logo" class="w-10 h-10 flex-shrink-0 border border-gray-100 dark:border-gray-800 rounded bg-white dark:bg-gray-100 flex items-center justify-center p-0.5">
                                            <img :src="bank.logo" class="max-w-full max-h-full object-contain">
                                        </div>
                                        <!-- Details -->
                                        <div class="text-sm">
                                            <p class="font-bold text-gray-900 dark:text-gray-100">{{ bank.bank_name }}</p>
                                            <p class="font-mono text-gray-700 dark:text-gray-300">{{ bank.account_number }}</p>
                                            <p class="text-gray-500 dark:text-gray-400 uppercase text-xs">{{ bank.account_holder }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- COD Section -->
                    <div v-if="props.order?.payment_method === 'cod' && props.cod_config" class="mt-8 bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg border border-blue-100 dark:border-blue-900/30 transition-colors">
                         <h4 class="text-lg font-medium text-blue-900 dark:text-blue-300 mb-2">{{ t('Cash On Delivery') }}</h4>
                         <p class="text-sm text-blue-800 dark:text-blue-400">
                            {{ props.cod_config.instructions || t('You have selected Cash on Delivery. Please prepare the exact amount for the courier.') }}
                         </p>
                    </div>

                    <!-- SePay QR Code Payment Section -->
                    <div v-if="props.sepay_payment" class="mt-8 bg-zinc-50 dark:bg-zinc-900/40 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 transition-colors">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ t('Scan QR Code to Pay') }}</h4>
                        
                        <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
                            <!-- QR Code Image -->
                            <div class="flex-shrink-0 bg-white p-3 rounded-lg border border-gray-200 dark:border-zinc-800">
                                <img :src="props.sepay_payment.qr_url" alt="SePay QR Code" class="max-w-[200px] h-auto object-contain">
                            </div>
                            
                            <!-- Payment Instructions -->
                            <div class="flex-grow space-y-4 w-full">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ t('Bank Name') }}</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 mt-1">
                                            {{ props.sepay_payment.bank_name }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ t('Account Number') }}</p>
                                        <p class="text-sm font-mono font-bold text-gray-900 dark:text-gray-100 mt-1 select-all">
                                            {{ props.sepay_payment.account_number }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ t('Account Holder') }}</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 mt-1 uppercase">
                                            {{ props.sepay_payment.account_holder }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ t('Transfer Amount') }}</p>
                                        <p class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                                            {{ formatCurrency(props.sepay_payment.amount, props.sepay_payment.currency) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4">
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ t('Transfer Description (Memo)') }}</p>
                                    <div class="flex items-center gap-2">
                                        <code class="flex-grow font-mono font-bold text-lg select-all bg-white dark:bg-black px-4 py-2 border border-zinc-200 dark:border-zinc-800 rounded-lg text-center text-gray-900 dark:text-gray-100">
                                            {{ props.sepay_payment.description }}
                                        </code>
                                    </div>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-2">
                                        {{ t('Please transfer the exact amount and include the correct memo description. The order will be activated automatically upon receiving the payment.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4 class="text-lg font-medium mt-8 mb-4 text-gray-900 dark:text-gray-100">{{ t('Order Items') }}</h4>
                <div class="overflow-x-auto border border-gray-100 dark:border-gray-800 rounded-lg transition-colors">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ t('Product') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ t('Price') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ t('Quantity') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ t('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#111] divide-y divide-gray-200 dark:divide-zinc-800">
                            <tr v-for="item in props.order?.items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    <div class="flex items-start gap-4">
                                        <div class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-900 shadow-sm" v-if="item.product?.media?.length">
                                            <img :src="getProductImage(item.product)" class="w-full h-full object-cover" alt="">
                                        </div>
                                        <div v-else class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-xl border border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-900/50 flex items-center justify-center text-gray-400 dark:text-zinc-600">
                                            <i class="fas fa-box text-xl"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <a v-if="item.product" :href="item.product.frontend_url" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium block transition-colors">
                                                {{ item.name }}
                                            </a>
                                            <span v-else class="font-medium block text-gray-900 dark:text-gray-100">{{ item.name }}</span>
                                            
                                            <!-- Bidirectional links to Subscription / License -->
                                            <div v-if="item.metadata?.subscription_id || item.metadata?.license_id" class="mt-1 flex flex-wrap gap-2 text-xs">
                                                <Link v-if="item.metadata?.subscription_id" :href="route('account.subscriptions', { search: props.order?.code })" class="inline-flex items-center gap-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded border border-indigo-100 dark:border-indigo-900/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors font-medium">
                                                    <i class="fas fa-redo-alt text-[10px]"></i>
                                                    {{ t('View Subscription') }}
                                                </Link>
                                                <div v-if="item.metadata?.license_key" class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-2.5 py-1 rounded border border-emerald-100 dark:border-emerald-900/50 font-medium">
                                                    <i class="fas fa-key text-[10px]"></i>
                                                    <span>{{ t('License') }}: <strong class="font-mono text-xs">{{ formatMaskedKey(item.metadata.license_key, revealedKeys[item.id]) }}</strong></span>
                                                    <button 
                                                        @click.prevent="toggleRevealKey(item.id)" 
                                                        type="button" 
                                                        class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200 transition-colors ml-1 p-0.5 cursor-pointer"
                                                        :title="revealedKeys[item.id] ? t('Hide Key') : t('Show Key')"
                                                    >
                                                        <i class="fas text-[10px]" :class="revealedKeys[item.id] ? 'fa-eye-slash' : 'fa-eye'"></i>
                                                    </button>
                                                    <button 
                                                        @click.prevent="copyToClipboard(item.metadata.license_key)" 
                                                        type="button" 
                                                        class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200 transition-colors p-0.5 cursor-pointer"
                                                        :title="t('Copy Key')"
                                                    >
                                                        <i class="far text-[10px]" :class="copiedKey === item.metadata.license_key ? 'fa-check-circle text-green-500' : 'fa-copy'"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatCurrency(item.price) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatCurrency(item.total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AccountLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useCurrency } from '@/Composables/useCurrency';
import { useTranslation } from '@/admin/composables/useTranslation';
import axios from 'axios';

const { formatCurrency } = useCurrency();
const { t } = useTranslation();

const revealedKeys = ref<Record<string | number, boolean>>({});
const copiedKey = ref('');

const toggleRevealKey = (id: string | number) => {
    revealedKeys.value[id] = !revealedKeys.value[id];
};

const formatMaskedKey = (key: string | undefined, isRevealed: boolean) => {
    if (!key) return '';
    let normalized = key.replace(/^KEY-/, 'MTX-');
    if (isRevealed) return normalized;
    if (normalized.startsWith('MTX-')) {
        return 'MTX-••••-••••-••••';
    }
    return '••••-••••-••••-••••';
};

const copyToClipboard = (key: string) => {
    if (!key) return;
    let normalized = key.replace(/^KEY-/, 'MTX-');
    navigator.clipboard.writeText(normalized).then(() => {
        copiedKey.value = key;
        setTimeout(() => {
            if (copiedKey.value === key) {
                copiedKey.value = '';
            }
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
};

const props = defineProps({
    order: Object,
    bank_transfer_config: Object,
    cod_config: Object,
    sepay_payment: Object,
});

let pollInterval: any = null;

const getStatusPollUrl = () => {
    const pathParts = window.location.pathname.split('/');
    const activeLocales = ['vi', 'zh'];
    const currentLocale = pathParts[1];
    
    if (activeLocales.includes(currentLocale)) {
        return `/${currentLocale}/account/orders/${props.order?.code}/sepay-status`;
    }
    return `/account/orders/${props.order?.code}/sepay-status`;
};

onMounted(() => {
    // Only poll when status is pending and payment method is sepay
    if (props.order && props.order.status === 'pending' && props.order.payment_method === 'sepay') {
        pollInterval = setInterval(async () => {
            try {
                const response = await axios.get(getStatusPollUrl());
                if (response.data && response.data.is_paid) {
                    clearInterval(pollInterval);
                    // Reload the Inertia page data to update the status and display active license/subscription details
                    router.reload({ preserveScroll: true });
                }
            } catch (err) {
                console.error("Payment status poll failed:", err);
            }
        }, 4000); // 4 seconds interval
    }
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});

const getPaymentMethodName = (code: string | undefined): string => {
    if (!code) return '-';
    const map: Record<string, string> = {
        'cod': 'Cash On Delivery',
        'bank_transfer': 'Bank Transfer',
        'paypal': 'PayPal',
        'stripe': 'Stripe',
        'sepay': 'SePay (QR Code)',
    };
    return map[code] || code;
};

const getProductImage = (product: any) => {
    if (!product || !product.media || !product.media.length) return null;
    // Find primary or take first
    const img = product.media.find((m: any) => m.pivot?.is_primary) || product.media[0];
    return img?.thumbnail_url || img?.url;
};
</script>
