<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
        <!-- Topbar Menu -->
        <TopbarMenu />

        <!-- Mobile Sidebar Backdrop -->
        <div
            class="admin-sidebar-backdrop lg:hidden"
            :class="{ active: isMobileOpen }"
            @click="closeMobileSidebar"
        ></div>



        <!-- Sidebar -->
        <aside 
            class="bg-gray-800 dark:bg-gray-800 text-white pt-14 pb-6 px-2 fixed left-0 bottom-0 top-0 transition-all duration-300 ease-in-out z-[51] flex flex-col overflow-visible" 
            :class="[
                isCollapsed && !isMobile ? 'w-14' : 'w-64',
                isMobile ? (isMobileOpen ? 'translate-x-0' : '-translate-x-full') : 'translate-x-0'
            ]"
        >
            <div class="flex items-center justify-between relative" :class="[isCollapsed && !isMobile ? 'px-0 justify-center' : 'px-4']">
                <router-link :to="{ name: 'admin.dashboard' }" class="flex items-center min-h-[32px]" :class="{ 'justify-center w-full': isCollapsed && !isMobile }" @click="onNavClick">
                    <img
                        v-if="brandLogo"
                        :src="brandLogo"
                        :alt="brandName"
                        class="h-8 w-auto max-w-full object-contain"
                        @load="logoLoaded = true"
                    />
                    <span v-if="brandLogo && logoLoaded && showBrandLabel && !(isCollapsed && !isMobile)" class="ml-2 text-white text-base font-semibold uppercase tracking-wide truncate">
                        {{ brandName }}
                    </span>
                    <span v-else-if="brandSettingsLoaded && !brandLogo && !(isCollapsed && !isMobile)" class="text-white text-xl font-semibold uppercase tracking-wide">
                        {{ brandName }}
                    </span>
                    <span v-else-if="brandSettingsLoaded && !brandLogo && isCollapsed && !isMobile" class="text-white text-xl font-semibold uppercase tracking-wide">
                        {{ brandName.charAt(0) }}
                    </span>
                </router-link>

                <!-- Collapse Toggle (Top) — hidden on mobile -->
                <button
                    v-if="!isMobile"
                    @click="toggleSidebar"
                    class="absolute -right-2 top-1/2 transform translate-x-1/2 -translate-y-1/2 bg-gray-700 text-gray-300 hover:text-white rounded-full p-1 shadow-md border-2 border-gray-500 focus:outline-none z-[9999] w-6 h-6 flex items-center justify-center transition-transform hover:scale-110"
                    :title="isCollapsed ? $t('Expand Menu') : $t('Collapse Menu')"
                >
                    <svg 
                        class="w-3 h-3 transition-transform duration-300" 
                        :class="[isCollapsed ? 'rotate-180' : '']"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto overflow-x-hidden space-y-6 mt-6" :class="{ 'pr-2': !isCollapsed || isMobile }">
                <nav class="space-y-1" v-if="!menuLoading">
                    <MenuItem
                        v-for="item in menuItems"
                        :key="item.key"
                        :item="item"
                        :route="route"
                        :collapsed="isCollapsed && !isMobile"
                        @nav-click="onNavClick"
                    />
                </nav>

                <!-- Loading state -->
                <div v-if="menuLoading" class="space-y-1">
                    <div v-for="i in 6" :key="i" class="px-4 py-2.5">
                        <div class="h-5 bg-gray-700 rounded animate-pulse"></div>
                    </div>
                </div>

                <!-- Profile -->
                <div class="mt-4 border-t border-gray-700 pt-4">
                    <router-link
                        :to="{ name: 'admin.profile' }"
                        class="flex items-center py-2.5 rounded transition duration-200 text-gray-300 hover:bg-gray-700 hover:text-white"
                        :class="[isCollapsed && !isMobile ? 'justify-center px-2' : 'px-4']"
                        :title="isCollapsed && !isMobile ? $t('Profile') : ''"
                        @click="onNavClick"
                    >
                        <svg class="w-5 h-5" :class="{ 'mr-3': !(isCollapsed && !isMobile) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span v-if="!(isCollapsed && !isMobile)">{{ $t('Profile') }}</span>
                    </router-link>
                </div>

                <!-- Logout (always visible) -->
                <div class="mt-2">
                    <a
                        href="#"
                        @click.prevent="handleLogout"
                        class="flex items-center py-2.5 rounded transition duration-200 text-gray-300 hover:bg-gray-700 hover:text-white"
                        :class="[isCollapsed && !isMobile ? 'justify-center px-2' : 'px-4']"
                        :title="isCollapsed && !isMobile ? $t('Logout') : ''"
                    >
                        <svg class="w-5 h-5" :class="{ 'mr-3': !(isCollapsed && !isMobile) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span v-if="!(isCollapsed && !isMobile)">{{ $t('Logout') }}</span>
                    </a>
                </div>

                <!-- Collapse Toggle (Bottom) — hidden on mobile -->
                <div v-if="!isMobile" class="mt-2 pb-6">
                    <button
                        @click="toggleSidebar"
                        class="w-full flex items-center py-2.5 rounded transition duration-200 text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none"
                        :class="[isCollapsed ? 'justify-center px-2' : 'px-4']"
                        :title="isCollapsed ? $t('Expand Menu') : $t('Collapse Menu')"
                    >
                        <svg 
                            class="w-5 h-5 transition-transform duration-300" 
                            :class="[isCollapsed ? 'rotate-180 mb-0' : 'mr-3']"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                        <span v-if="!isCollapsed">{{ $t('Collapse Menu') }}</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div 
            class="flex-1 flex flex-col transition-all duration-300" 
            :class="[
                isMobile ? 'ml-0' : (isCollapsed ? 'ml-14' : 'ml-64')
            ]"
            style="padding-top: 32px;"
        >
            <!-- Page content -->
            <main class="flex-1 p-3 sm:p-4 lg:p-6">
                <router-view />
            </main>
        </div>

        <!-- Global Command Palette -->
        <CommandPalette />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed, getCurrentInstance, provide } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import { useTranslation } from '../composables/useTranslation';
import { Storage } from '../utils';
import axios from 'axios';
import MenuItem from './MenuItem.vue';
import TopbarMenu from '../components/TopbarMenu.vue';
import CommandPalette from '../components/CommandPalette.vue';

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
const router = useRouter();

const menuItems = ref<MenuItemData[]>([]);
const menuLoading = ref(true);
const openMenus = ref<Set<string>>(new Set());
const isCollapsed = ref(Storage.get<boolean>('sidebar_collapsed') === true);

// ---- Mobile responsive state ----
const isMobileOpen = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1200);

const MOBILE_BREAKPOINT = 1024; // lg breakpoint

const isMobile = computed(() => windowWidth.value < MOBILE_BREAKPOINT);

const handleResize = () => {
    windowWidth.value = window.innerWidth;
    // Auto-close mobile sidebar when resizing to desktop
    if (!isMobile.value && isMobileOpen.value) {
        isMobileOpen.value = false;
    }
};

const toggleMobileSidebar = () => {
    isMobileOpen.value = !isMobileOpen.value;
};

const closeMobileSidebar = () => {
    isMobileOpen.value = false;
};

// Provide sidebar state to child components (TopbarMenu uses this)
provide('isMobileOpen', isMobileOpen);
provide('toggleMobileSidebar', toggleMobileSidebar);
provide('isMobile', isMobile);

const onNavClick = () => {
    if (isMobile.value) {
        closeMobileSidebar();
    }
};

const handleLogout = () => {
    closeMobileSidebar();
    authStore.logout();
};

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    Storage.set('sidebar_collapsed', isCollapsed.value);
};

// Brand logo and name
const brandLogo = ref<string | null>(null);
const brandName = ref<string>('POLYCMS');
const showBrandLabel = ref<boolean>(false);
const logoLoaded = ref<boolean>(false);
const brandSettingsLoaded = ref<boolean>(false);

const loadBrandSettings = async () => {
    try {
        const response = await axios.get('/api/v1/settings');
        const generalSettings = response.data.data?.general || {};
        const newLogo = generalSettings.brand_logo?.value || null;
        if (newLogo !== brandLogo.value) {
            logoLoaded.value = false;
        }
        brandLogo.value = newLogo;
        brandName.value = generalSettings.brand_name?.value || generalSettings.brand_name || 'POLYCMS';
        const labelVal = generalSettings.show_brand_label?.value;
        showBrandLabel.value = labelVal === true || labelVal === '1' || labelVal === 'true';
        brandSettingsLoaded.value = true;
    } catch (error) {
        console.error('Error loading brand settings:', error);
        brandSettingsLoaded.value = true;
    }
};

// Load menu from API
const loadMenu = async (refresh = false, retryCount = 0) => {
    if (retryCount === 0) {
        menuLoading.value = true;
    }
    try {
        const response = await axios.get('/api/v1/admin/menu', refresh ? { params: { refresh: 1 } } : undefined);
        const data = response.data.data || [];
        
        // If menu is empty and we haven't retried too many times, retry after a short delay
        // This handles race conditions where modules haven't fully booted yet
        if (data.length === 0 && retryCount < 2) {
            setTimeout(() => loadMenu(true, retryCount + 1), 500);
            return;
        }

        menuItems.value = data;

        // Auto-expand menus based on current route
        updateMenuState();
    } catch (error) {
        console.error('Error loading menu:', error);
        // Retry on error (network timing, server boot)
        if (retryCount < 2) {
            setTimeout(() => loadMenu(refresh, retryCount + 1), 1000);
            return;
        }
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

                    // For index routes, check if current route belongs to same group
                    if (child.route.endsWith('.index')) {
                        const routePrefix = child.route.replace('.index', '');
                        if (routeName.toString().startsWith(routePrefix)) {
                            if (child.routeParams?.type && routeType === child.routeParams.type) {
                                return true;
                            }
                            if (!child.routeParams || Object.keys(child.routeParams).length === 0) {
                                return true;
                            }
                        }
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

// Watch route changes to update menu state, scroll to top, and close mobile sidebar
watch(() => [route.name, route.query], () => {
    updateMenuState();
    closeMobileSidebar();
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    const mainContent = document.querySelector('main');
    if (mainContent) {
        mainContent.scrollTop = 0;
    }
}, { immediate: false });

const handleGlobalKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Backspace') {
        const activeElement = document.activeElement as HTMLElement;
        const activeTag = activeElement?.tagName?.toLowerCase();
        const isInput = activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select';
        const isContentEditable = activeElement?.isContentEditable;

        if (!isInput && !isContentEditable) {
            e.preventDefault();
            router.back();
        }
    }
};

onMounted(() => {
    loadMenu();
    loadBrandSettings();
    window.addEventListener('admin-menu-changed', handleMenuChanged);
    window.addEventListener('resize', handleResize);
    window.addEventListener('keydown', handleGlobalKeydown);
});

const handleMenuChanged = () => loadMenu(true);
onUnmounted(() => {
    window.removeEventListener('admin-menu-changed', handleMenuChanged);
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('keydown', handleGlobalKeydown);
});
</script>
