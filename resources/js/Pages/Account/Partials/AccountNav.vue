<template>
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <Link
                :href="route('account.orders')"
                :class="[
                    route().current('account.orders') || route().current('account.orders.show')
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                ]"
            >
                My Orders
            </Link>

            <Link
                :href="route('account.subscriptions')"
                :class="[
                    route().current('account.subscriptions')
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                ]"
            >
                Subscriptions
            </Link>

            <Link
                :href="route('account.licenses')"
                :class="[
                    route().current('account.licenses')
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                ]"
            >
                Licenses
            </Link>
             <Link
                :href="route('profile.edit')"
                :class="[
                    route().current('profile.edit')
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                ]"
            >
                Profile
            </Link>
        </nav>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>
