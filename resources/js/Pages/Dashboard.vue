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

        <div class="overflow-hidden bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-800 transition-colors duration-300">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                {{ t('You\'re logged in!') }}
                
                <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                     <!-- Orders -->
                     <div class="bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-xl border border-indigo-100 dark:border-indigo-800/50 flex flex-col justify-between transition-colors duration-300">
                        <div>
                            <h3 class="font-bold text-indigo-700 dark:text-indigo-400 text-lg mb-1">{{ t('Orders') }}</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ statistics?.orders?.count || 0 }}</p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-indigo-200 dark:border-indigo-800/50">
                            <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">{{ t('Total Spent') }}</p>
                            <p class="text-lg font-bold text-indigo-900 dark:text-indigo-300">{{ formatCurrency(statistics?.orders?.total || 0) }}</p>
                        </div>
                     </div>

                     <!-- Subscriptions -->
                     <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-xl border border-green-100 dark:border-green-800/50 flex flex-col justify-between transition-colors duration-300">
                        <div>
                            <h3 class="font-bold text-green-700 dark:text-green-400 text-lg mb-1">{{ t('Subscriptions') }}</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ statistics?.subscriptions?.count || 0 }}</p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-green-200 dark:border-green-800/50">
                            <p class="text-sm text-green-600 dark:text-green-400 font-medium">{{ t('Total Spent') }}</p>
                            <p class="text-lg font-bold text-green-900 dark:text-green-300">{{ formatCurrency(statistics?.subscriptions?.total || 0) }}</p>
                        </div>
                     </div>

                     <!-- Licenses -->
                     <div class="bg-yellow-50 dark:bg-yellow-900/20 p-6 rounded-xl border border-yellow-100 dark:border-yellow-800/50 flex flex-col justify-between transition-colors duration-300">
                        <div>
                            <h3 class="font-bold text-yellow-700 dark:text-yellow-400 text-lg mb-1">{{ t('Licenses') }}</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ statistics?.licenses?.count || 0 }}</p>
                        </div>
                         <div class="mt-4 pt-4 border-t border-yellow-200 dark:border-yellow-800/50">
                            <p class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">{{ t('Value') }}</p>
                            <p class="text-lg font-bold text-yellow-900 dark:text-yellow-300">{{ formatCurrency(statistics?.licenses?.total || 0) }}</p>
                        </div>
                     </div>
                </div>
            </div>
        </div>
    </AccountLayout>
</template>
