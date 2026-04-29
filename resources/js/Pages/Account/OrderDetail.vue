<template>
    <AccountLayout>
        <template #header>
            {{ t('My Account') }}
        </template>

        <div class="overflow-hidden bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-800 transition-colors duration-300">
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
                                <code class="block w-full bg-white dark:bg-gray-900 px-4 py-3 rounded border border-gray-300 dark:border-gray-700 font-mono font-bold text-lg select-all text-center text-gray-900 dark:text-indigo-400">
                                    {{ props.order.code }}
                                </code>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ t('Please include this code in your transfer description.') }}</p>
                            </div>
                            
                            <div v-if="props.bank_transfer_config.banks && props.bank_transfer_config.banks.length > 0">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ t('Bank Accounts') }}</p>
                                <div class="space-y-3">
                                     <div v-for="(bank, idx) in props.bank_transfer_config.banks" :key="idx" class="bg-white dark:bg-gray-900 p-3 rounded border border-gray-200 dark:border-gray-700 flex items-start gap-4">
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
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                            <tr v-for="item in props.order?.items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 mr-4" v-if="item.product?.media?.length">
                                            <img :src="getProductImage(item.product)" class="h-10 w-10 rounded-full object-cover border border-gray-100 dark:border-gray-800" alt="">
                                        </div>
                                        <div>
                                            <a v-if="item.product" :href="route('products.show', item.product.slug)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium block transition-colors">
                                                {{ item.name }}
                                            </a>
                                            <span v-else class="font-medium block text-gray-900 dark:text-gray-100">{{ item.name }}</span>
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
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useCurrency } from '@/Composables/useCurrency';
import { useTranslation } from '@/admin/composables/useTranslation';

const { formatCurrency } = useCurrency();
const { t } = useTranslation();

const props = defineProps({
    order: Object,
    bank_transfer_config: Object,
    cod_config: Object,
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
    return img?.url;
};
</script>
