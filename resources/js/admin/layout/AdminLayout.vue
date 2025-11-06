<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
        <!-- Topbar Menu -->
        <TopbarMenu />

        <!-- Sidebar -->
        <aside class="bg-gray-800 dark:bg-gray-800 text-white w-64 space-y-6 py-6 px-2 fixed left-0 bottom-0 transform md:relative md:translate-x-0 transition duration-200 ease-in-out overflow-y-auto" style="top: 32px;">
            <div class="flex items-center justify-between px-4 pt-2">
                <router-link :to="{ name: 'admin.dashboard' }" class="flex items-center">
                    <img
                        v-if="brandLogo"
                        :src="brandLogo"
                        :alt="brandName"
                        class="h-8 w-auto max-w-full object-contain"
                    />
                    <span v-else class="text-white text-xl font-semibold uppercase tracking-wide">
                        {{ brandName }}
                    </span>
                </router-link>
            </div>

            <nav class="space-y-1" v-if="!menuLoading">
                <MenuItem
                    v-for="item in menuItems"
                    :key="item.key"
                    :item="item"
                    :route="route"
                />
            </nav>

            <!-- Loading state -->
            <div v-if="menuLoading" class="space-y-1">
                <div v-for="i in 6" :key="i" class="px-4 py-2.5">
                    <div class="h-5 bg-gray-700 rounded animate-pulse"></div>
                </div>
            </div>

            <!-- Logout (always visible) -->
            <div class="mt-6">
                <a
                    href="#"
                    @click.prevent="authStore.logout()"
                    class="flex items-center px-4 py-2.5 rounded transition duration-200 text-gray-300 hover:bg-gray-700 hover:text-white"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ $t('Logout') }}
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col md:ml-0" style="padding-top: 32px;">
            <!-- Header with Theme Toggle -->
            <header class="w-full bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="px-6 py-3 flex items-center justify-end">
                    <div class="flex items-center space-x-3">
                        <!-- Theme Toggle Button -->
                        <button
                            @click="themeStore.toggle()"
                            class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            :title="$t(themeStore.isDark ? 'Switch to light mode' : 'Switch to dark mode')"
                        >
                            <!-- Sun icon (light mode) -->
                            <svg
                                v-if="themeStore.isDark"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                            <!-- Moon icon (dark mode) -->
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, getCurrentInstance } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import { useTranslation } from '../composables/useTranslation';
import axios from 'axios';
import MenuItem from './MenuItem.vue';
import TopbarMenu from '../components/TopbarMenu.vue';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

interface MenuChild {
    key: string;
    label: string;
    route?: string;
    routeParams?: Record<string, any>;
    icon?: string | null;
    order?: number;
}

interface MenuItemData {
    key: string;
    label: string;
    route?: string;
    routeParams?: Record<string, any>;
    icon?: string;
    order?: number;
    children?: MenuChild[];
}

const authStore = useAuthStore();
const themeStore = useThemeStore();
const route = useRoute();

const menuItems = ref<MenuItemData[]>([]);
const menuLoading = ref(true);
const openMenus = ref<Set<string>>(new Set());

// Brand logo and name
const brandLogo = ref<string | null>(null);
const brandName = ref<string>('POLYCMS');

const loadBrandSettings = async () => {
    try {
        const response = await axios.get('/api/v1/settings');
        const generalSettings = response.data.data?.general || {};
        brandLogo.value = generalSettings.brand_logo?.value || null;
        brandName.value = generalSettings.brand_name?.value || generalSettings.brand_name || 'POLYCMS';
    } catch (error) {
        console.error('Error loading brand settings:', error);
    }
};

// Load menu from API
const loadMenu = async () => {
    menuLoading.value = true;
    try {
        const response = await axios.get('/api/v1/admin/menu');
        menuItems.value = response.data.data || [];

        // Auto-expand menus based on current route
        updateMenuState();
    } catch (error) {
        console.error('Error loading menu:', error);
        // Fallback to empty menu on error
        menuItems.value = [];
    } finally {
        menuLoading.value = false;
    }
};

// Auto-expand menu groups based on current route
const updateMenuState = () => {
    const routeName = route.name;
    const routeType = route.query?.type;

    // Clear previous open menus
    openMenus.value.clear();

    if (typeof routeName === 'string') {
        // Check each menu item and its children
        menuItems.value.forEach(item => {
            // Check if route matches menu item or any child
            if (item.route && routeName === item.route) {
                openMenus.value.add(item.key);
                return;
            }

            if (item.children && item.children.length > 0) {
                const hasActiveChild = item.children.some(child => {
                    if (!child.route) return false;

                    // Check exact route match
                    if (child.route === routeName) {
                        // Also check routeParams for categories
                        if (child.routeParams?.type && routeType === child.routeParams.type) {
                            return true;
                        }
                        if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
                            return true;
                        }
                        return false;
                    }

                    // Check if route name starts with child route (for nested routes)
                    if (routeName.toString().startsWith(child.route)) {
                        if (child.routeParams?.type && routeType === child.routeParams.type) {
                            return true;
                        }
                        if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
                            return true;
                        }
                        return false;
                    }

                    return false;
                });

                if (hasActiveChild) {
                    openMenus.value.add(item.key);
                }
            }
        });
    }
};

// Watch route changes to update menu state
watch(() => [route.name, route.query], () => {
    updateMenuState();
}, { immediate: false });

onMounted(() => {
    loadMenu();
    loadBrandSettings();
});
</script>
