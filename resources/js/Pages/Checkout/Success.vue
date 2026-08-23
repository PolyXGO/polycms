<template>
    <Head :title="props.order?.code ? `${t('Order Confirmation')} #${props.order.code}` : t('Order Confirmation')" />
    <div class="py-4 sm:py-6 transition-colors duration-200">
        <div class="container max-w-4xl mx-auto px-4 sm:px-6">
            <CheckoutSteps :step="3" />
            
            <div class="mt-4 sm:mt-6 mx-auto max-w-2xl">
                <div class="bg-white dark:bg-[#111622] shadow-md dark:shadow-2xl dark:shadow-black/60 border border-slate-200/90 dark:border-slate-800/80 rounded-2xl overflow-hidden text-center transition-colors">
                    <div class="p-6 sm:p-8">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20 mb-4 shadow-sm">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mb-2 tracking-tight">
                            Order Placed Successfully!
                        </h2>
                        <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 mb-1">
                            Thank you for your purchase. We have received your order:
                        </p>
                        <p class="text-xl sm:text-2xl font-mono font-bold text-slate-900 dark:text-slate-200 mb-6 tracking-wide">
                            #{{ props.order?.code }}
                        </p>

                        <div class="bg-slate-50/90 dark:bg-[#0c101a]/90 rounded-xl p-5 sm:p-6 mb-6 text-left max-w-md mx-auto border border-slate-200/80 dark:border-slate-800/80">
                             <h3 class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 border-b border-slate-200 dark:border-slate-800 pb-2">
                                Order Details
                             </h3>
                             <div class="space-y-2.5 text-sm">
                                  <div class="flex justify-between items-center">
                                      <span class="text-slate-600 dark:text-slate-400">Order Number:</span>
                                      <span class="font-mono font-bold text-slate-900 dark:text-slate-200">{{ props.order?.code || '...' }}</span>
                                  </div>
                                  <div class="flex justify-between items-center">
                                       <span class="text-slate-600 dark:text-slate-400">Payment Status:</span>
                                       <span v-if="props.order?.payment_method === 'free' || props.order?.total_amount <= 0" class="text-emerald-700 dark:text-emerald-300 font-bold bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/70 text-[11px] uppercase">
                                           Free (Paid)
                                       </span>
                                       <span v-else-if="props.order?.payment_status === 'paid' || props.order?.status === 'completed'" class="text-emerald-700 dark:text-emerald-300 font-bold bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/70 text-[11px] uppercase">
                                           Paid
                                       </span>
                                       <span v-else :class="{
                                           'text-yellow-700 bg-yellow-50 dark:bg-yellow-950/40 border-yellow-200 dark:border-yellow-800/70 dark:text-yellow-300': props.order?.payment_status === 'pending' || props.order?.payment_status === 'awaiting_payment' || props.order?.payment_status === 'unpaid',
                                           'text-red-700 bg-red-50 dark:bg-red-950/40 border-red-200 dark:border-red-800/70 dark:text-red-300': props.order?.payment_status === 'failed'
                                       }" class="font-medium capitalize px-2.5 py-0.5 rounded-full border text-[11px]">
                                           {{ props.order?.payment_status?.replace('_', ' ') || 'Unpaid' }}
                                       </span>
                                  </div>
                                  <div v-if="props.order?.payment_method === 'sepay' && props.order?.payment_status !== 'paid'" class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/70 rounded-xl text-xs text-yellow-800 dark:text-yellow-200 flex items-center">
                                       <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                       Please complete your payment via QR Code if you haven't already.
                                  </div>
                             </div>

                             <!-- Free / Paid Completion Notice -->
                             <div v-if="props.order?.payment_method === 'free' || props.order?.total_amount <= 0 || props.order?.payment_status === 'paid' || props.order?.status === 'completed'" class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-3">
                                 <div class="bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60 p-3.5 rounded-xl flex items-center gap-3">
                                     <div class="w-8 h-8 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                                         <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                     </div>
                                     <div class="text-left">
                                         <h4 class="text-xs font-bold text-emerald-900 dark:text-emerald-200 m-0">Order Complete</h4>
                                         <p class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-0.5 m-0 leading-relaxed">This order is 100% free. All subscriptions, downloads, and licenses are active in your account.</p>
                                     </div>
                                 </div>
                             </div>

                             <!-- Bank Transfer Details (Only if paid amount > 0 and not paid) -->
                             <div v-else-if="props.order?.payment_method === 'bank_transfer' && props.bank_transfer_config && props.order?.payment_status !== 'paid' && props.order?.status !== 'completed' && props.order?.total_amount > 0" class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-3">
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-900 dark:text-slate-200 mb-2">Bank Transfer Instructions</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mb-3">{{ props.bank_transfer_config.instructions }}</p>
                                
                                <div class="bg-slate-100 dark:bg-slate-900 p-3 rounded-xl">
                                    <div class="flex flex-col sm:flex-row gap-3 mb-3">
                                        <div class="flex-1 bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-center">
                                            <p class="text-[11px] font-bold text-slate-500 uppercase mb-1">Transfer Content (Memo)</p>
                                            <code class="font-mono font-bold text-base text-slate-900 dark:text-slate-100 select-all break-all bg-transparent p-0 border-0">
                                                {{ props.order.code }}
                                            </code>
                                        </div>
                                        <div class="flex-1 bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-center">
                                            <p class="text-[11px] font-bold text-slate-500 uppercase mb-1">Amount to Transfer</p>
                                            <div class="font-bold text-lg text-slate-900 dark:text-slate-100">
                                                {{ formatCurrency(props.order.total_amount || 0) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="props.bank_transfer_config.banks && props.bank_transfer_config.banks.length > 0" class="space-y-2">
                                        <p class="text-[11px] font-bold text-slate-500 uppercase">Bank Accounts</p>
                                        <div v-for="(bank, idx) in props.bank_transfer_config.banks" :key="idx" class="bg-white dark:bg-slate-800 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 flex items-start gap-3">
                                            <div v-if="bank.logo" class="w-10 h-10 flex-shrink-0 border border-slate-100 dark:border-slate-700 rounded-lg bg-white flex items-center justify-center p-1">
                                                <img :src="bank.logo" class="max-w-full max-h-full object-contain">
                                            </div>
                                            <div class="text-xs">
                                                <p class="font-bold text-slate-900 dark:text-slate-100">{{ bank.bank_name }}</p>
                                                <p class="font-mono text-slate-700 dark:text-slate-300 font-semibold">{{ bank.account_number }}</p>
                                                <p class="text-slate-500 uppercase text-[10px]">{{ bank.account_holder }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>

                             <!-- COD Instructions -->
                             <div v-else-if="props.order?.payment_method === 'cod' && props.cod_config && props.order?.payment_status !== 'paid' && props.order?.status !== 'completed' && props.order?.total_amount > 0" class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-3">
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-900 dark:text-slate-200 mb-2">Cash On Delivery</h4>
                                <div class="bg-blue-50 dark:bg-blue-950/40 p-3 rounded-xl text-xs text-blue-800 dark:text-blue-300">
                                    {{ props.cod_config.instructions || 'Please pay the carrier upon delivery.' }}
                                </div>
                             </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-center items-center gap-3 max-w-md mx-auto">
                            <a :href="'/account/orders/' + props.order?.code" class="inline-flex justify-center items-center w-full sm:flex-1 px-5 py-2.5 border border-slate-300 dark:border-slate-700/80 text-sm font-semibold rounded-xl text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-all shadow-xs">
                                View Order Details
                            </a>
                            <a href="/" class="inline-flex justify-center items-center w-full sm:flex-1 px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-slate-900 hover:bg-slate-800 dark:bg-slate-200 dark:hover:bg-white dark:text-slate-950 transition-all shadow-sm">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                     <div class="bg-slate-50/80 dark:bg-[#0c101a]/80 px-6 py-3 text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80">
                        A confirmation email has been sent to your email address.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useTranslation } from '@/admin/composables/useTranslation';
import axios from 'axios';
import CheckoutSteps from '@/Components/CheckoutSteps.vue';

const { t } = useTranslation();

const props = defineProps({
    order: Object,
    bank_transfer_config: Object,
    cod_config: Object,
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
    if (props.order && props.order.payment_method === 'sepay' && props.order.payment_status !== 'paid') {
        pollInterval = setInterval(async () => {
            try {
                const response = await axios.get(getStatusPollUrl());
                if (response.data && response.data.is_paid) {
                    clearInterval(pollInterval);
                    router.reload({ preserveScroll: true });
                }
            } catch (err) {
                console.error("Payment status poll failed:", err);
            }
        }, 4000);
    }
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
};
</script>
