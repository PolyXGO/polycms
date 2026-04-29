<template>
    <div
        v-if="shouldShow"
        class="polycms-topbar"
    >
        <div class="polycms-topbar-container">
            <!-- Left Menu -->
            <div class="polycms-topbar-left">
                <!-- Hamburger Toggle (mobile only, inside topbar) -->
                <button
                    v-if="isMobile"
                    @click="handleHamburgerClick"
                    class="topbar-button topbar-hamburger-btn"
                    :title="$t(isMobileOpen ? 'Close Menu' : 'Open Menu')"
                >
                    <span class="topbar-icon">
                        <svg v-if="!isMobileOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </span>
                </button>

                <!-- Left menu items (always visible) -->
                <template v-for="item in leftItems" :key="item.id">
                    <TopbarMenuItem :item="item" />
                </template>
            </div>

            <!-- Right Menu -->
            <div class="polycms-topbar-right">
                <!-- Right menu items (always visible) -->
                <template v-for="item in rightItems" :key="item.id">
                    <TopbarMenuItem :item="item" />
                </template>

                <!-- Language Switcher -->
                <LanguageSwitcher />

                <!-- Currency Switcher -->
                <CurrencySwitcher />

                <!-- Theme Toggle Button -->
                <button
                    @click="themeStore.toggle()"
                    class="topbar-button theme-toggle-btn"
                    :title="$t(themeStore.isDark ? 'Switch to light mode' : 'Switch to dark mode')"
                >
                    <span v-if="themeStore.isDark" class="topbar-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </span>
                    <span v-else class="topbar-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </span>
                </button>

                <!-- Settings Dropdown -->
                <div class="topbar-settings-dropdown">
                    <button
                        @click="showSettings = !showSettings"
                        v-click-outside="() => showSettings = false"
                        class="topbar-button settings-btn"
                        :title="$t('Editor Settings')"
                        :class="{ 'active': showSettings }"
                    >
                        <span class="topbar-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                    </button>

                    <transition name="settings-fade">
                        <div v-if="showSettings" class="settings-dropdown-content">
                            <div class="settings-header">
                                {{ $t('Editor Settings') }}
                            </div>
                            <div class="settings-body">
                                <label class="setting-item">
                                    <div class="setting-label">
                                        <span class="setting-title">{{ $t('Auto-hide block settings') }}</span>
                                        <span class="setting-desc">{{ $t('Hide panel when clicking outside a block') }}</span>
                                    </div>
                                    <div class="setting-action">
                                        <input 
                                            type="checkbox" 
                                            :checked="landingStore.autoHideSidebar"
                                            @change="landingStore.toggleAutoHideSidebar()"
                                            class="setting-checkbox"
                                        >
                                    </div>
                                </label>
                                <div class="settings-divider"></div>
                                <button class="setting-item w-full text-left" @click="compileAllLanguages">
                                    <div class="setting-label">
                                        <span class="setting-title">{{ $t('Compile Language') }}</span>
                                        <span class="setting-desc">{{ $t('Refresh and compile all language files') }}</span>
                                    </div>
                                    <div class="setting-action" v-if="compiling">
                                        <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                                    </div>
                                </button>
                                <div class="settings-divider"></div>
                                <router-link to="/admin/settings" class="setting-item" @click="showSettings = false">
                                    <div class="setting-label">
                                        <span class="setting-title">{{ $t('Settings') }}</span>
                                        <span class="setting-desc">{{ $t('Go to system settings hub') }}</span>
                                    </div>
                                    <div class="setting-action text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </div>
                                </router-link>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, inject } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import { useTranslation } from '../composables/useTranslation';
import axios from 'axios';
import TopbarMenuItem from './TopbarMenuItem.vue';
import { useLandingStore } from '../stores/landingStore';
import { useDialog } from '../composables/useDialog';

const vClickOutside = {
    mounted(el: any, binding: any) {
        el._clickOutside = (event: Event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.addEventListener('click', el._clickOutside);
    },
    unmounted(el: any) {
        document.removeEventListener('click', el._clickOutside);
    },
};

import LanguageSwitcher from './LanguageSwitcher.vue';
import CurrencySwitcher from './CurrencySwitcher.vue';
const { t } = useTranslation();
const themeStore = useThemeStore();
const landingStore = useLandingStore();
const router = useRouter();
const $t = (key: string) => t(key);

// Inject sidebar state from AdminLayout
const isMobileOpen = inject<any>('isMobileOpen', ref(false));
const toggleMobileSidebar = inject<any>('toggleMobileSidebar', () => {});
const isMobile = inject<any>('isMobile', computed(() => false));

const handleHamburgerClick = () => {
    if (toggleMobileSidebar) {
        toggleMobileSidebar();
    }
};

const showSettings = ref(false);
const compiling = ref(false);
const dialog = useDialog();

const compileAllLanguages = async () => {
    const confirmed = await dialog.confirm(
        $t('Are you sure you want to compile all languages? This will refresh all translation files.')
    );

    if (!confirmed) return;

    compiling.value = true;
    try {
        const response = await axios.post('/api/v1/languages/compile-all');
        if (response.data.success) {
            dialog.success($t('All translations compiled successfully.'));
        } else {
            dialog.error(response.data.message || $t('Failed to compile translations.'));
        }
    } catch (error: any) {
        console.error('Failed to compile all languages:', error);
        dialog.error(error.response?.data?.message || $t('Failed to compile translations.'));
    } finally {
        compiling.value = false;
        showSettings.value = false;
    }
};

interface MenuItem {
    id: string;
    label: string;
    url?: string;
    icon?: string;
    priority?: number;
    group?: 'left' | 'right';
    highlight?: boolean;
    children?: MenuItem[];
    method?: 'GET' | 'POST';
}

const authStore = useAuthStore();
const route = useRoute();

const menuItems = ref<MenuItem[]>([]);
const loading = ref(true);

const shouldShow = computed(() => {
    return authStore.isAuthenticated;
});

const leftItems = computed(() => {
    return menuItems.value
        .filter(item => (item.group || 'left') === 'left')
        .sort((a, b) => (a.priority || 10) - (b.priority || 10));
});

const rightItems = computed(() => {
    return menuItems.value
        .filter(item => (item.group || 'left') === 'right')
        .sort((a, b) => (a.priority || 10) - (b.priority || 10));
});

const loadMenuItems = async () => {
    if (!authStore.isAuthenticated) {
        return;
    }

    try {
        const response = await axios.get('/api/v1/topbar/menu', {
            params: {
                route: route.name,
                route_params: JSON.stringify(route.params),
            },
        });

        menuItems.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to load topbar menu:', error);
        menuItems.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadMenuItems();
});

// Reload menu when route changes
watch(() => route.name, () => {
    loadMenuItems();
});
</script>

<style scoped>
    .polycms-topbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 99999;
        background: #1f2937;
        color: #fff;
        font-size: 13px;
        line-height: 32px;
        height: 32px;
        min-height: 32px;
        max-height: 32px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        overflow: visible;
    }

    .polycms-topbar-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 12px;
        height: 32px;
        min-height: 32px;
        max-height: 32px;
        gap: 4px;
        box-sizing: border-box;
        overflow: visible;
    }

    .polycms-topbar-left,
    .polycms-topbar-right {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 2px;
        flex-shrink: 1;
        flex-wrap: nowrap;
        overflow: visible;
        height: 32px;
        min-width: 0;
    }

    .polycms-topbar-left {
        flex-shrink: 1;
        overflow: visible;
    }

    .polycms-topbar-right {
        justify-content: flex-end;
        flex-shrink: 0;
    }

    /* Hamburger button inside topbar */
    .topbar-hamburger-btn {
        padding: 0 6px !important;
        width: auto !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        margin-right: 4px;
    }

    .topbar-hamburger-btn .topbar-icon svg {
        width: 16px !important;
        height: 16px !important;
    }

    /* Hide hamburger on desktop */
    @media (min-width: 1024px) {
        .topbar-hamburger-btn {
            display: none !important;
        }
    }

    .theme-toggle-btn {
        padding: 0 8px !important;
        width: auto !important;
        justify-content: center !important;
        margin-left: 2px;
    }

    .theme-toggle-btn .topbar-icon svg {
        width: 14px !important;
        height: 14px !important;
    }

    :deep(.polycms-topbar-left a:not(.topbar-dropdown > a)),
    :deep(.polycms-topbar-left button:not(.topbar-button)),
    :deep(.polycms-topbar-right > a:not(.topbar-dropdown > a)),
    :deep(.polycms-topbar-right > button:not(.topbar-button)) {
        color: #d1d5db;
        text-decoration: none;
        padding: 0 10px;
        display: contents;
        border-radius: 3px;
        transition: all 0.15s ease;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 400;
        white-space: nowrap;
        flex-shrink: 0;
        overflow: visible;
        box-sizing: border-box;
        vertical-align: middle;
    }

    :deep(.polycms-topbar-left a:not(.topbar-dropdown > a) > *),
    :deep(.polycms-topbar-left button:not(.topbar-button) > *),
    :deep(.polycms-topbar-right > a:not(.topbar-dropdown > a) > *),
    :deep(.polycms-topbar-right > button:not(.topbar-button) > *) {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        line-height: 32px;
        height: 32px;
        min-height: 32px;
        max-height: 32px;
        vertical-align: middle;
    }

    :deep(.polycms-topbar .topbar-dropdown > a),
    :deep(.polycms-topbar .topbar-button) {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 0 10px !important;
        gap: 6px !important;
        color: #d1d5db !important;
        text-decoration: none !important;
        border-radius: 0 !important;
        transition: all 0.15s ease !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        overflow: visible !important;
        box-sizing: border-box !important;
        vertical-align: middle !important;
        line-height: 32px !important;
        height: 32px !important;
        min-height: 32px !important;
        max-height: 32px !important;
    }

    :deep(.polycms-topbar a:hover),
    :deep(.polycms-topbar button:hover) {
        color: #60a5fa;
        background: rgba(255, 255, 255, 0.1);
    }

    :deep(.polycms-topbar .topbar-highlight) {
        background: #3b82f6 !important;
        color: #fff !important;
        font-weight: 500;
    }

    :deep(.polycms-topbar .topbar-highlight:hover) {
        background: #2563eb !important;
        color: #fff !important;
    }

    :deep(.polycms-topbar .topbar-icon) {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0 !important;
        line-height: 1 !important;
    }

    :deep(.polycms-topbar .topbar-icon svg) {
        width: 16px !important;
        height: 16px !important;
        display: block !important;
    }

    :deep(.polycms-topbar-left > .topbar-dropdown > a::after),
    :deep(.polycms-topbar-right > .topbar-dropdown > a::after) {
        content: '';
        display: inline-block;
        width: 0;
        height: 0;
        margin-left: 4px;
        margin-top: 2px;
        vertical-align: middle;
        border-top: 4px solid #d1d5db;
        border-right: 4px solid transparent;
        border-bottom: 0;
        border-left: 4px solid transparent;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }

    :deep(.polycms-topbar-left > .topbar-dropdown:hover > a::after),
    :deep(.polycms-topbar-right > .topbar-dropdown:hover > a::after) {
        border-top-color: #60a5fa;
    }

    /* Keep dropdown visible when hovering — desktop only (≥1024px) */
    /* Below 1024px, only click toggle (.touch-open) controls dropdown */
    @media (min-width: 1024px) {
        :deep(.polycms-topbar .topbar-dropdown:hover > .topbar-dropdown-content),
        :deep(.polycms-topbar .topbar-dropdown-content:hover) {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
    }

    :deep(.polycms-topbar-left .topbar-dropdown-content) {
        left: 0;
        right: auto;
    }

    :deep(.polycms-topbar-right .topbar-dropdown-content) {
        right: 0;
        left: auto;
    }

    :deep(.polycms-topbar .topbar-dropdown-form) {
        margin: 0;
        padding: 0;
    }

    :deep(.polycms-topbar .topbar-dropdown-content a),
    :deep(.polycms-topbar .topbar-dropdown-content button),
    :deep(.polycms-topbar .topbar-dropdown-item) {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 10px 16px !important;
        color: #d1d5db !important;
        white-space: nowrap !important;
        width: 100% !important;
        text-align: left !important;
        gap: 10px !important;
        height: auto !important;
        border-radius: 0 !important;
        margin: 0 !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        font-family: inherit !important;
        line-height: 1.5 !important;
        transition: all 0.15s ease !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        text-decoration: none !important;
        box-sizing: border-box !important;
    }

    :deep(.polycms-topbar .topbar-dropdown-content a:hover),
    :deep(.polycms-topbar .topbar-dropdown-content button:hover),
    :deep(.polycms-topbar .topbar-dropdown-item:hover) {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #60a5fa !important;
    }

    :deep(.polycms-topbar .topbar-dropdown-content a:first-child),
    :deep(.polycms-topbar .topbar-dropdown-content button:first-child),
    :deep(.polycms-topbar .topbar-dropdown-item:first-child) {
        border-radius: 6px 6px 0 0 !important;
    }

    :deep(.polycms-topbar .topbar-dropdown-content a:last-child),
    :deep(.polycms-topbar .topbar-dropdown-content button:last-child),
    :deep(.polycms-topbar .topbar-dropdown-item:last-child) {
        border-radius: 0 0 6px 6px !important;
    }

    :deep(.polycms-topbar .topbar-dropdown-content a:only-child),
    :deep(.polycms-topbar .topbar-dropdown-content button:only-child),
    :deep(.polycms-topbar .topbar-dropdown-item:only-child) {
        border-radius: 6px !important;
    }

    :deep(.polycms-topbar .topbar-dropdown:not(.submenu) > .topbar-dropdown-content) {
        display: none;
        opacity: 0;
        visibility: hidden;
        position: absolute;
        top: 100%;
        margin-top: 4px;
        background: #1f2937;
        min-width: 200px;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        border-radius: 6px;
        padding: 6px 0;
        z-index: 100000;
        transition: opacity 0.15s ease, visibility 0.15s ease;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    :deep(.polycms-topbar .topbar-dropdown.submenu > .topbar-dropdown-content) {
        top: 0 !important;
        left: 100% !important;
        margin-top: -6px !important;
    }

    :deep(.polycms-topbar .topbar-dropdown-content::before) {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 0;
        right: 0;
        height: 4px;
        background: transparent;
    }

    /* ===== Mobile responsive: topbar items overflow scroll ===== */
    @media (max-width: 1023px) {
        .polycms-topbar-container {
            padding: 0 4px;
        }

        .polycms-topbar-left {
            overflow: visible;
            flex-shrink: 1;
            min-width: 0;
        }

        /* Hide text labels, show only icons on very small screens */
        :deep(.polycms-topbar .topbar-dropdown > a),
        :deep(.polycms-topbar .topbar-button) {
            padding: 0 6px !important;
            gap: 4px !important;
        }

        /* Dropdown sub-items: toggle inline on mobile instead of flyout */
        :deep(.polycms-topbar .topbar-dropdown.submenu > .topbar-dropdown-content) {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            padding-left: 12px !important;
            background: rgba(255, 255, 255, 0.03) !important;
            min-width: auto !important;
        }

        :deep(.polycms-topbar .topbar-dropdown-item) {
            min-height: 40px !important;
        }
    }

    @media (max-width: 639px) {
        /* On very small screens, hide label text in topbar items, show only icons */
        :deep(.polycms-topbar-left .topbar-dropdown > a > span:not(.topbar-icon)),
        :deep(.polycms-topbar-left > a > span:not(.topbar-icon)) {
            display: none !important;
        }

        /* Reduce dropdown arrow on mobile */
        :deep(.polycms-topbar-left > .topbar-dropdown > a::after) {
            margin-left: 2px;
        }
    }

    /* ===== Settings Dropdown ===== */
    .topbar-settings-dropdown {
        position: relative;
        height: 32px;
        display: flex;
        align-items: center;
    }

    .settings-btn {
        padding: 0 8px !important;
        width: auto !important;
        justify-content: center !important;
        margin-left: 2px;
    }

    .settings-btn.active {
        color: #60a5fa !important;
        background: rgba(255, 255, 255, 0.1) !important;
        height: 32px;
        display: inline-flex;
        align-items: center;
    }

    .settings-btn .topbar-icon svg {
        width: 14px !important;
        height: 14px !important;
    }

    .settings-dropdown-content {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 4px;
        background: #1f2937;
        min-width: 280px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        z-index: 100001;
        overflow: hidden;
    }

    @media (max-width: 639px) {
        .settings-dropdown-content {
            position: fixed;
            left: 8px;
            right: 8px;
            top: 36px;
            min-width: auto;
            width: auto;
        }
    }

    .settings-header {
        padding: 12px 16px;
        font-weight: 600;
        color: #9ca3af;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        text-transform: uppercase;
        letter-spacing: 0.025em;
        font-size: 11px;
    }

    .settings-body {
        padding: 4px 0;
    }

    .settings-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.05);
        margin: 4px 16px;
    }

    .setting-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .setting-item:hover {
        background: rgba(255, 255, 255, 0.03);
    }

    .setting-label {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .setting-title {
        color: #f3f4f6;
        font-weight: 500;
        font-size: 13px;
        line-height: 1;
    }

    .setting-desc {
        color: #9ca3af;
        font-size: 11px;
    }

    .setting-checkbox {
        width: 16px;
        height: 16px;
        accent-color: #3b82f6;
        cursor: pointer;
    }

    .settings-fade-enter-active,
    .settings-fade-leave-active {
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .settings-fade-enter-from,
    .settings-fade-leave-to {
        opacity: 0;
        transform: translateY(-8px);
    }
</style>
