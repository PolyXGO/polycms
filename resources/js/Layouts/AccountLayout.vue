<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { useTranslation } from '@/admin/composables/useTranslation';

const { t } = useTranslation();

const page = usePage();
const user = computed(() => page.props.auth.user as any);
const brandLogo = computed(() => (page.props.settings as any)?.brand_logo);
const brandName = computed(() => (page.props.settings as any)?.brand_name || 'PolyCMS');

const menuItems = [
    { label: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { label: 'Orders', route: 'account.orders', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
    { label: 'Subscriptions', route: 'account.subscriptions', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' },
    { label: 'Licenses', route: 'account.licenses', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
    { label: 'Profile', route: 'profile.edit', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

const currentRoute = computed(() => {
    return route().current();
});

const csrfToken = computed(() => page.props.csrf_token as string);

onMounted(() => {
});
</script>

<template>
    <div class="flex min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white dark:bg-gray-800 shadow-sm border-r border-gray-100 dark:border-gray-700 hidden md:block fixed h-full z-10 transition-colors duration-300">
            <div class="py-4 px-6 border-b border-gray-100 dark:border-gray-800">

                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" class="h-12 w-12 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                        <div v-else class="h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center border border-indigo-200 dark:border-indigo-800">
                            <svg class="h-7 w-7 text-indigo-500 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 overflow-hidden">
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ user?.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user?.email }}</p>
                    </div>
                </div>
            </div>

            <div class="py-4 px-4">
                <nav class="space-y-1">
                    <Link
                        v-for="item in menuItems"
                        :key="item.route"
                        :href="route(item.route)"
                        class="flex items-center px-2 py-2 text-sm font-medium rounded-md group transition-colors duration-150"
                        :class="[
                            currentRoute === item.route || currentRoute?.startsWith(item.route + '.')
                                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white'
                        ]"
                    >
                        <svg
                            class="mr-3 h-5 w-5 flex-shrink-0"
                            :class="[
                                currentRoute === item.route || currentRoute?.startsWith(item.route + '.')
                                    ? 'text-indigo-500 dark:text-indigo-400'
                                    : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300'
                            ]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>
                        {{ t(item.label) }}
                    </Link>
                </nav>
            </div>
            
             <div class="border-t border-gray-200 dark:border-gray-800 p-4 absolute bottom-16 w-full">
                 <form :action="route('logout')" method="POST">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <button type="submit" class="flex items-center w-full px-2 py-2 text-sm font-medium text-red-600 dark:text-red-400 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 group transition-colors">
                        <svg class="mr-3 h-5 w-5 text-red-500 group-hover:text-red-600 dark:group-hover:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        {{ t('Log Out') }}
                    </button>
                 </form>
            </div>
        </aside>

        <!-- Mobile Menu (Top) -->
        <div class="md:hidden w-full bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 overflow-x-auto whitespace-nowrap fixed top-8 z-20 mt-8 transition-colors duration-300">
            <nav class="flex px-4 py-2 space-x-4">
                 <Link
                    v-for="item in menuItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="px-3 py-2 text-sm font-medium rounded-md transition-colors"
                    :class="[
                        currentRoute === item.route || currentRoute?.startsWith(item.route + '.')
                            ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                            : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
                    ]"
                >
                    {{ t(item.label) }}
                </Link>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-4 md:p-8 pt-20 md:pt-8 transition-colors duration-300">
            <div class="max-w-8xl mx-auto">
                 <header v-if="$slots.header" class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <slot name="header" />
                    </h1>
                </header>
                
                <slot />
            </div>
        </main>
    </div>
</template>
