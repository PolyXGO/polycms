<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <CheckoutSteps :step="3" />
            
            <div class="mt-8 mx-auto max-w-3xl">
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl overflow-hidden text-center">
                    <div class="p-10">
                        
                        <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4">
                            Order Placed Successfully!
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-2">
                            Thank you for your purchase. We have received your order:
                        </p>
                        <p class="text-2xl font-mono font-bold text-gray-900 dark:text-white mb-8">
                            #{{ props.order?.code }}
                        </p>

                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 mb-8 text-left max-w-md mx-auto border border-gray-100 dark:border-gray-700">
                             <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-200 dark:border-gray-600 pb-2">
                                Order Details
                             </h3>
                             <div class="space-y-3">
                                 <div class="flex justify-between">
                                     <span class="text-gray-600 dark:text-gray-300">Order Number:</span>
                                     <span class="font-medium text-gray-900 dark:text-white">{{ props.order?.code || '...' }}</span>
                                 </div>
                                 <div class="flex justify-between items-center">
                                      <span class="text-gray-600 dark:text-gray-300">Payment Status:</span>
                                      <span v-if="props.order?.payment_method === 'free' || props.order?.total_amount <= 0" class="text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800 text-xs uppercase">
                                          Free (Paid)
                                      </span>
                                      <span v-else-if="props.order?.payment_status === 'paid' || props.order?.status === 'completed'" class="text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800 text-xs uppercase">
                                          Paid
                                      </span>
                                      <span v-else :class="{
                                          'text-yellow-600 bg-yellow-50 dark:bg-yellow-950/30 border-yellow-200 dark:border-yellow-800': props.order?.payment_status === 'pending' || props.order?.payment_status === 'awaiting_payment' || props.order?.payment_status === 'unpaid',
                                          'text-red-600 bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800': props.order?.payment_status === 'failed'
                                      }" class="font-medium capitalize px-2 py-0.5 rounded border text-xs">
                                          {{ props.order?.payment_status?.replace('_', ' ') || 'Unpaid' }}
                                      </span>
                                 </div>
                                  <div v-if="props.order?.payment_method === 'sepay' && props.order?.payment_status !== 'paid'" class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded text-sm text-yellow-800 dark:text-yellow-200 flex items-center">
                                      <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                      Please complete your payment via QR Code if you haven't already.
                                  </div>
                              </div>

                              <!-- Free / Paid Completion Notice -->
                              <div v-if="props.order?.payment_method === 'free' || props.order?.total_amount <= 0 || props.order?.payment_status === 'paid' || props.order?.status === 'completed'" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                  <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 p-4 rounded-xl flex items-center gap-3">
                                      <div class="w-9 h-9 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                                          <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                      </div>
                                      <div class="text-left">
                                          <h4 class="text-sm font-bold text-emerald-900 dark:text-emerald-200">Order Complete</h4>
                                          <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">This order is 100% free. All subscriptions, downloads, and licenses are active in your account.</p>
                                      </div>
                                  </div>
                              </div>

                              <!-- Bank Transfer Details (Only if paid amount > 0 and not paid) -->
                              <div v-else-if="props.order?.payment_method === 'bank_transfer' && props.bank_transfer_config && props.order?.payment_status !== 'paid' && props.order?.status !== 'completed' && props.order?.total_amount > 0" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                 <h4 class="font-medium text-gray-900 dark:text-white mb-3">Bank Transfer Instructions</h4>
                                 <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">{{ props.bank_transfer_config.instructions }}</p>
                                 
                                 <div class="bg-gray-100 dark:bg-gray-700/50 p-4 rounded-lg">
                                     <div class="flex flex-col sm:flex-row gap-4 mb-4">
                                         <div class="flex-1 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center text-center">
                                             <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Transfer Content (Memo)</p>
                                             <code class="font-mono font-bold text-lg sm:text-xl text-gray-900 dark:text-white select-all break-all bg-transparent p-0 border-0">
                                                 {{ props.order.code }}
                                             </code>
                                         </div>
                                         <div class="flex-1 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center text-center">
                                             <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Amount to Transfer</p>
                                             <div class="font-bold text-xl sm:text-2xl text-gray-900 dark:text-white">
                                                 {{ formatCurrency(props.order.total_amount || 0) }}
                                             </div>
                                         </div>
                                     </div>

                                     <div v-if="props.bank_transfer_config.banks && props.bank_transfer_config.banks.length > 0" class="space-y-3">
                                         <p class="text-xs font-semibold text-gray-500 uppercase">Bank Accounts</p>
                                         <div v-for="(bank, idx) in props.bank_transfer_config.banks" :key="idx" class="bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600 flex items-start gap-4">
                                             <!-- Logo -->
                                             <div v-if="bank.logo" class="w-12 h-12 flex-shrink-0 border border-gray-100 rounded bg-white flex items-center justify-center p-1">
                                                 <img :src="bank.logo" class="max-w-full max-h-full object-contain">
                                             </div>
                                             <!-- Details -->
                                             <div class="text-sm">
                                                 <p class="font-bold text-gray-900 dark:text-white">{{ bank.bank_name }}</p>
                                                 <p class="font-mono text-gray-700 dark:text-gray-300">{{ bank.account_number }}</p>
                                                 <p class="text-gray-500 uppercase text-xs">{{ bank.account_holder }}</p>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                              </div>

                              <!-- COD Instructions -->
                              <div v-else-if="props.order?.payment_method === 'cod' && props.cod_config && props.order?.payment_status !== 'paid' && props.order?.status !== 'completed' && props.order?.total_amount > 0" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                 <h4 class="font-medium text-gray-900 dark:text-white mb-2">Cash On Delivery</h4>
                                 <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-sm text-blue-800 dark:text-blue-300">
                                     {{ props.cod_config.instructions || 'Please pay the carrier upon delivery.' }}
                                 </div>
                              </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-center items-center space-y-3 sm:space-y-0 sm:space-x-4 max-w-md mx-auto">
                            <a :href="'/account/orders/' + props.order?.code" class="inline-flex justify-center items-center w-full sm:flex-1 px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                                View Order Details
                            </a>
                            <a href="/" class="inline-flex justify-center items-center w-full sm:flex-1 px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-gray-900 hover:bg-black dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 dark:focus:ring-gray-400 shadow-md">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                     <div class="bg-gray-50 dark:bg-gray-700 px-10 py-4 text-sm text-gray-500 dark:text-gray-400">
                        A confirmation email has been sent to your email address.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import CheckoutSteps from '@/Components/CheckoutSteps.vue';

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
