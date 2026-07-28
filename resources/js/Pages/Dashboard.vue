<script setup lang="ts">
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useCurrency } from '@/Composables/useCurrency';
import { useTranslation } from '@/admin/composables/useTranslation';

const { formatCurrency } = useCurrency();
const { t } = useTranslation();

defineProps({
    statistics: Object,
});
</script>

<template>
    <Head :title="t('Dashboard')" />

    <AccountLayout>
        <template #header>
            {{ t('Dashboard') }}
        </template>

        <div class="overflow-hidden bg-white dark:bg-black shadow-sm sm:rounded-xl border border-gray-200 dark:border-zinc-800 transition-colors duration-300">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                {{ t('You\'re logged in!') }}
                
                <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                     <!-- Orders -->
                     <div class="bg-white dark:bg-[#111] p-6 rounded-xl border border-gray-200 dark:border-zinc-800 flex flex-col justify-between transition-colors duration-300 shadow-sm">
                        <div>
                            <h3 class="font-medium text-gray-500 dark:text-gray-400 text-sm mb-2 uppercase tracking-wider">{{ t('Orders') }}</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ statistics?.orders?.count || 0 }}</p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-zinc-800/80 flex justify-between items-end">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('Total Spent') }}</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(statistics?.orders?.total || 0) }}</p>
                        </div>
                     </div>

                     <!-- Subscriptions -->
                     <div class="bg-white dark:bg-[#111] p-6 rounded-xl border border-gray-200 dark:border-zinc-800 flex flex-col justify-between transition-colors duration-300 shadow-sm">
                        <div>
                            <h3 class="font-medium text-gray-500 dark:text-gray-400 text-sm mb-2 uppercase tracking-wider">{{ t('Subscriptions') }}</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ statistics?.subscriptions?.count || 0 }}</p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-zinc-800/80 flex justify-between items-end">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('Total Spent') }}</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(statistics?.subscriptions?.total || 0) }}</p>
                        </div>
                     </div>

                     <!-- Licenses -->
                     <div class="bg-white dark:bg-[#111] p-6 rounded-xl border border-gray-200 dark:border-zinc-800 flex flex-col justify-between transition-colors duration-300 shadow-sm">
                        <div>
                            <h3 class="font-medium text-gray-500 dark:text-gray-400 text-sm mb-2 uppercase tracking-wider">{{ t('Licenses') }}</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ statistics?.licenses?.count || 0 }}</p>
                        </div>
                         <div class="mt-6 pt-4 border-t border-gray-100 dark:border-zinc-800/80 flex justify-between items-end">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('Value') }}</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(statistics?.licenses?.total || 0) }}</p>
                        </div>
                     </div>
                </div>
            </div>
        </div>
    </AccountLayout>
</template>
