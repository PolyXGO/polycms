<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useTranslation } from '@/admin/composables/useTranslation';
import DialogProvider from '@/admin/components/dialogs/DialogProvider.vue';

const { t } = useTranslation();

const page = usePage();
const user = computed(() => page.props.auth.user as any);
const brandLogo = computed(() => (page.props.settings as any)?.brand_logo);
const brandName = computed(() => (page.props.settings as any)?.brand_name || 'PolyCMS');

const coreMenuItems = [
    { label: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', order: 10 },
    { label: 'Orders', route: 'account.orders', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', order: 20 },
    { label: 'Subscriptions', route: 'account.subscriptions', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', order: 30 },
    { label: 'Licenses', route: 'account.licenses', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z', order: 40 },
    { label: 'Addresses', route: 'account.addresses.index', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', order: 80 },
    { label: 'Profile', route: 'profile.edit', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', order: 90 },
];

// Merge extra menu items from modules (via AccountMenuRegistry + Inertia shared props)
const menuItems = computed(() => {
    const extraItems = (page.props.account_menu_extra as any[]) || [];
    const allItems = [...coreMenuItems, ...extraItems];
    allItems.sort((a, b) => (a.order ?? 999) - (b.order ?? 999));
    return allItems;
});

const currentRoute = computed(() => {
    return route().current();
});

const csrfToken = computed(() => page.props.csrf_token as string);
const demoRestrictionProp = computed(() => page.props.demo_restriction as any);
const eventDemoRestriction = ref<any>(null);
const showDemoRestrictionModal = ref(false);

const activeDemoRestriction = computed(() => {
    return eventDemoRestriction.value || demoRestrictionProp.value;
});

const handleDemoRestrictionEvent = (event: Event) => {
    const customEvent = event as CustomEvent;
    if (customEvent.detail) {
        eventDemoRestriction.value = customEvent.detail;
        showDemoRestrictionModal.value = true;
    }
};

const openDemoRestrictionModal = () => {
    if (demoRestrictionProp.value?.is_demo_restriction) {
        showDemoRestrictionModal.value = true;
    }
};

onMounted(() => {
    openDemoRestrictionModal();
    window.addEventListener('polycms:demo_restriction', handleDemoRestrictionEvent);
});

onUnmounted(() => {
    window.removeEventListener('polycms:demo_restriction', handleDemoRestrictionEvent);
});

watch(demoRestrictionProp, () => {
    openDemoRestrictionModal();
});
</script>

<template>
    <div class="flex min-h-screen bg-[#fafafa] dark:bg-[#0a0a0a] transition-colors duration-300">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white dark:bg-black border-r border-gray-200 dark:border-zinc-800 hidden md:block fixed h-full z-10 transition-colors duration-300">
            <div class="py-4 px-6 border-b border-gray-200 dark:border-zinc-800">

                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" class="h-12 w-12 rounded-full object-cover border border-gray-200 dark:border-zinc-800" />
                        <div v-else class="h-12 w-12 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center border border-gray-200 dark:border-zinc-700">
                            <svg class="h-7 w-7 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md group transition-colors duration-150"
                        :class="[
                            currentRoute === item.route || currentRoute?.startsWith(item.route + '.')
                                ? 'bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white'
                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50 hover:text-gray-900 dark:hover:text-white'
                        ]"
                    >
                        <svg
                            class="mr-3 h-5 w-5 flex-shrink-0 transition-colors"
                            :class="[
                                currentRoute === item.route || currentRoute?.startsWith(item.route + '.')
                                    ? 'text-gray-900 dark:text-white'
                                    : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300'
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
            
            <div class="border-t border-gray-200 dark:border-zinc-800 p-4 absolute bottom-16 w-full">
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
        <div class="md:hidden w-full bg-white dark:bg-black border-b border-gray-200 dark:border-zinc-800 overflow-x-auto whitespace-nowrap sticky top-0 z-20 transition-colors duration-300">
            <nav class="flex items-center px-4 py-2 space-x-2">
                 <Link
                    v-for="item in menuItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition-colors flex-shrink-0"
                    :class="[
                        currentRoute === item.route || currentRoute?.startsWith(item.route + '.')
                            ? 'bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white font-semibold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                    ]"
                >
                    <svg
                        class="mr-1.5 h-4 w-4 flex-shrink-0"
                        :class="[
                            currentRoute === item.route || currentRoute?.startsWith(item.route + '.')
                                ? 'text-gray-900 dark:text-white'
                                : 'text-gray-400 dark:text-gray-500'
                        ]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    {{ t(item.label) }}
                </Link>

                <!-- Mobile Log Out Button -->
                <form :action="route('logout')" method="POST" class="inline-block flex-shrink-0">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <button
                        type="submit"
                        class="flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    >
                        <svg class="mr-1.5 h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        {{ t('Log Out') }}
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-4 md:p-8 pt-4 md:pt-8 transition-colors duration-300">
            <div class="max-w-8xl mx-auto">
                 <header v-if="$slots.header" class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <slot name="header" />
                    </h1>
                </header>
                
                <slot />
            </div>
        </main>

        <Teleport to="body">
            <div v-if="showDemoRestrictionModal" class="fixed inset-0 z-[9999] flex items-center justify-center px-4 py-6">
                <div class="absolute inset-0 bg-slate-950/75 backdrop-blur-sm"></div>
                <div class="relative w-full max-w-md overflow-hidden rounded-xl border border-slate-700 bg-slate-800 text-white shadow-2xl">
                    <div class="flex items-center gap-3 border-b border-slate-700 px-6 py-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/15 text-amber-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold">
                            {{ activeDemoRestriction?.title || 'Demo Actions Restricted' }}
                        </h3>
                    </div>
                    <div class="px-6 py-5 text-sm leading-6 text-slate-300" v-html="activeDemoRestriction?.message || ''"></div>
                    <div class="flex justify-end bg-slate-900/50 px-6 py-4">
                        <button
                            type="button"
                            class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600"
                            @click="showDemoRestrictionModal = false"
                        >
                            {{ activeDemoRestriction?.confirm_text || 'Close' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
        <DialogProvider />
    </div>
</template>
