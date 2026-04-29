<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <CheckoutSteps :step="3" />
            
            <div class="mt-8 mx-auto max-w-3xl">
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl overflow-hidden text-center">
                    <div class="p-10">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/30 mb-6">
                            <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        
                        <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4">
                            Order Placed Successfully!
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
                            Thank you for your purchase. We have received your order <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">#{{ props.order?.code }}</span>.
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
                                <div class="flex justify-between">
                                     <span class="text-gray-600 dark:text-gray-300">Payment Status:</span>
                                     <span :class="{
                                         'text-green-600': props.order?.payment_status === 'paid',
                                         'text-yellow-600': props.order?.payment_status === 'pending' || props.order?.payment_status === 'awaiting_payment',
                                         'text-red-600': props.order?.payment_status === 'failed'
                                     }" class="font-medium capitalize">
                                         {{ props.order?.payment_status?.replace('_', ' ') || 'Pending' }}
                                     </span>
                                </div>
                                 <div v-if="props.order?.payment_method === 'sepay' && props.order?.payment_status !== 'paid'" class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded text-sm text-yellow-800 dark:text-yellow-200">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Please complete your payment via QR Code if you haven't already.
                                </div>
                             </div>

                             <!-- Bank Transfer Details -->
                             <div v-if="props.order?.payment_method === 'bank_transfer' && props.bank_transfer_config && props.order?.status !== 'completed'" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-3">Bank Transfer Instructions</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">{{ props.bank_transfer_config.instructions }}</p>
                                
                                <div class="bg-gray-100 dark:bg-gray-700/50 p-4 rounded-lg">
                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Transfer Content (Memo)</p>
                                    <div class="flex items-center gap-2 mb-4">
                                        <code class="bg-white dark:bg-gray-800 px-3 py-1 rounded border border-gray-300 dark:border-gray-600 font-mono font-bold text-lg select-all">
                                            {{ props.order.code }}
                                        </code>
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
                             <div v-if="props.order?.payment_method === 'cod' && props.cod_config" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-2">Cash On Delivery</h4>
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-sm text-blue-800 dark:text-blue-300">
                                    {{ props.cod_config.instructions || 'Please pay the carrier upon delivery.' }}
                                </div>
                             </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                            <a href="/account/orders" class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                                View Order History
                            </a>
                            <a href="/" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md">
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
import CheckoutSteps from '@/Components/CheckoutSteps.vue';

const props = defineProps({
    order: Object,
    bank_transfer_config: Object,
    cod_config: Object,
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
};
</script>
