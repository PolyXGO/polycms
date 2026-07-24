<template>
    <AccountLayout>
        <Head :title="t('My Subscriptions')" />
        <template #header>
            {{ t('My Subscriptions') }}
        </template>

        <!-- Search Bar -->
        <div class="mb-4 flex items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 dark:text-zinc-500 text-xs"></i>
                </div>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    :placeholder="t('Search by product name or order code...')" 
                    class="block w-full pl-9 pr-3 py-2 text-xs bg-white dark:bg-[#111] border border-gray-200 dark:border-zinc-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:focus:ring-indigo-600 focus:border-transparent text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-600 transition-all shadow-sm"
                />
            </div>
            <button 
                v-if="searchQuery" 
                @click="searchQuery = ''" 
                class="text-xs text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors"
            >
                {{ t('Clear') }}
            </button>
        </div>

        <div class="bg-white dark:bg-[#111] shadow-sm overflow-hidden sm:rounded-xl border border-gray-200 dark:border-zinc-800 transition-colors duration-300">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Product') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Price') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Status') }}
                        </th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Starts At') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Expires At') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Auto Renew') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ t('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#111] divide-y divide-gray-200 dark:divide-zinc-800">
                    <tr v-for="sub in filteredSubscriptions" :key="sub.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            <a v-if="sub.product?.slug" :href="getProductUrl(sub.product.slug)" target="_blank" class="block font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ sub.product?.name || sub.service?.name || '-' }}
                            </a>
                            <span v-else class="block font-semibold text-indigo-600 dark:text-indigo-400">
                                {{ sub.product?.name || sub.service?.name || '-' }}
                            </span>
                            <Link v-if="sub.order" :href="route('account.orders.show', sub.order.code)" class="inline-flex items-center gap-1 mt-1 text-xs text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors font-normal">
                                <i class="fas fa-shopping-bag text-[10px]"></i>
                                {{ t('Order') }}: #{{ sub.order.code }}
                            </Link>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ formatCurrency(sub.paid_price !== undefined && sub.paid_price !== null ? sub.paid_price : (sub.recurring_price || sub.service?.price || sub.product?.price)) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': sub.status === 'active',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': sub.status === 'expired' || sub.status === 'cancelled',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': sub.status === 'suspended'
                                }">
                                {{ t(sub.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ sub.starts_at ? new Date(sub.starts_at).toLocaleDateString() : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-medium">
                            <span :class="sub.expires_at ? '' : 'text-emerald-600 dark:text-emerald-400 font-bold'">
                                {{ getExpiresAtText(sub) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                             <span v-if="sub.is_auto_renew" class="text-green-600 dark:text-green-400 font-bold">{{ t('Yes') }}</span>
                             <span v-else class="text-gray-400 dark:text-gray-500">{{ t('No') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <button 
                                v-if="sub.status === 'expired' && sub.product?.slug && !hasActiveSubscription(sub)"
                                @click="renewSubscription(sub.id)"
                                :disabled="renewingId === sub.id"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors cursor-pointer shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <i class="fas" :class="renewingId === sub.id ? 'fa-spinner fa-spin' : 'fa-sync-alt'"></i>
                                {{ renewingId === sub.id ? t('Processing...') : t('Renew') }}
                            </button>
                            <span v-else class="text-gray-400 dark:text-zinc-600">-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            
             <div v-if="filteredSubscriptions.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                {{ t('No subscriptions found.') }}
            </div>
        </div>
    </AccountLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useCurrency } from '@/Composables/useCurrency';
import { useTranslation } from '@/admin/composables/useTranslation';

const { formatCurrency } = useCurrency();
const { t } = useTranslation();

const renewingId = ref(null);

const getProductUrl = (slug) => {
    const pathParts = window.location.pathname.split('/');
    const activeLocales = ['vi', 'zh'];
    const currentLocale = pathParts[1];
    
    if (activeLocales.includes(currentLocale)) {
        return `/${currentLocale}/products/${slug}`;
    }
    return `/products/${slug}`;
};

const renewSubscription = (subscriptionId) => {
    renewingId.value = subscriptionId;
    router.post(route('account.subscriptions.renew', { subscription: subscriptionId }), {}, {
        onFinish: () => {
            renewingId.value = null;
        }
    });
};

const hasActiveSubscription = (sub) => {
    if (!props.subscriptions) return false;
    return props.subscriptions.some(s => 
        s.id !== sub.id && 
        s.product_id === sub.product_id && 
        s.service_id === sub.service_id && 
        s.status === 'active'
    );
};

const props = defineProps({
    subscriptions: Array,
});

const searchQuery = ref('');

const filteredSubscriptions = computed(() => {
    if (!searchQuery.value) {
        return props.subscriptions;
    }
    const q = searchQuery.value.toLowerCase().trim();
    return props.subscriptions.filter(sub => {
        const productName = (sub.product?.name || sub.service?.name || '').toLowerCase();
        const orderCode = (sub.order?.code || '').toLowerCase();
        return productName.includes(q) || orderCode.includes(q);
    });
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const searchParam = params.get('search');
    if (searchParam) {
        searchQuery.value = searchParam;
    }
});

const getExpiresAtText = (sub) => {
    if (!sub.expires_at) {
        return t('Lifetime / Never Expires');
    }
    const expiry = new Date(sub.expires_at);
    const today = new Date();
    today.setHours(0,0,0,0);
    expiry.setHours(0,0,0,0);
    
    const dateStr = expiry.toLocaleDateString();
    
    // If status is not active/suspended, just show the expiration date
    if (sub.status !== 'active' && sub.status !== 'suspended') {
        return dateStr;
    }
    
    const starts = sub.starts_at ? new Date(sub.starts_at) : null;
    if (starts) starts.setHours(0,0,0,0);
    
    if (starts && starts > today) {
        const startDiff = Math.ceil((starts.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
        return `${dateStr} (${t('Starts in')} ${startDiff} ${t('days')})`;
    }
    
    const diffTime = expiry.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 0) {
        return `${dateStr} (${t('Expired')} ${Math.abs(diffDays)} ${t('days ago')})`;
    } else if (diffDays === 0) {
        return `${dateStr} (${t('Expires today')})`;
    } else if (diffDays === 1) {
        return `${dateStr} (${t('1 day remaining')})`;
    } else {
        return `${dateStr} (${diffDays} ${t('days remaining')})`;
    }
};
</script>
