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
 :title="$t(isMobileOpen ?'Close Menu' :'Open Menu')"
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
 :title="$t(themeStore.isDark ?'Switch to light mode' :'Switch to dark mode')"
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

  <!-- Cache Dropdown -->
  <div class="topbar-settings-dropdown polycms-topbar-cache-dropdown">
  <button
  @click="showCacheDropdown = !showCacheDropdown"
  v-click-outside="() => showCacheDropdown = false"
  class="topbar-button cache-btn"
  :title="$t('Cache Control')"
  :class="{'active': showCacheDropdown }"
  >
  <span class="topbar-icon">
  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
  </svg>
  </span>
  </button>

  <transition name="settings-fade">
  <div v-if="showCacheDropdown" class="settings-dropdown-content" style="width: 280px; max-height: 480px; overflow-y: auto;">
  <div class="settings-header flex justify-between items-center">
  <span>{{ $t('Cache Management') }}</span>
  </div>
  <div class="settings-body">
  
  <!-- Toggle Switch for Cache -->
  <div class="setting-item flex items-center justify-between">
  <div class="setting-label">
  <span class="setting-title">{{ $t('System Cache') }}</span>
  <span class="setting-desc">{{ $t('Enable/Disable application caching') }}</span>
  </div>
  <div class="setting-action">
  <input 
  type="checkbox" 
  :checked="systemCacheEnabled"
  @change="toggleSystemCache"
  class="setting-checkbox"
  >
  </div>
  </div>
  
  <div class="settings-divider"></div>

  <!-- Clear All -->
  <button type="button" class="setting-item w-full text-left" @click="clearAllCaches" :disabled="clearing">
  <div class="setting-label">
  <span class="setting-title" style="color: #ef4444;">{{ $t('Clear All Caches') }}</span>
  <span class="setting-desc">{{ $t('Flush all registered cache stores') }}</span>
  </div>
  <div class="setting-action" v-if="clearing">
  <div class="animate-spin h-4 w-4 border-2 border-red-500 border-t-transparent rounded-full"></div>
  </div>
  </button>

  <!-- Fix Permissions -->
  <button type="button" class="setting-item w-full text-left" @click="fixPermissions" :disabled="fixing">
  <div class="setting-label">
  <span class="setting-title">{{ $t('Fix Permissions') }}</span>
  <span class="setting-desc">{{ $t('Repair cache & storage folder permissions') }}</span>
  </div>
  <div class="setting-action" v-if="fixing">
  <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
  </div>
  </button>
  
  <div class="settings-divider"></div>
  
  <!-- Clear individual caches -->
  <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-400">
    {{ $t('Clear Individual Caches') }}
  </div>
  
  <button type="button" class="setting-item w-full text-left cursor-pointer" @click="clearSpecificCache('page_current')" :disabled="clearingSpecific === 'page_current'">
  <div class="setting-label">
  <span class="setting-title" style="color: #3b82f6;">{{ $t('Clear Current Page Cache') }}</span>
  <span class="setting-desc">{{ $t('Purge page cache for current URL & bump generation') }}</span>
  </div>
  <div class="setting-action" v-if="clearingSpecific === 'page_current'">
  <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
  </div>
  </button>

  <button type="button" class="setting-item w-full text-left" @click="clearSpecificCache('application')" :disabled="clearingSpecific === 'application'">
  <div class="setting-label">
  <span class="setting-title">{{ $t('Application Cache') }}</span>
  <span class="setting-desc">{{ $t('Clear main key-value cache') }}</span>
  </div>
  <div class="setting-action" v-if="clearingSpecific === 'application'">
  <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
  </div>
  </button>

  <button type="button" class="setting-item w-full text-left" @click="clearSpecificCache('view')" :disabled="clearingSpecific === 'view'">
  <div class="setting-label">
  <span class="setting-title">{{ $t('View Cache') }}</span>
  <span class="setting-desc">{{ $t('Clear compiled blade templates') }}</span>
  </div>
  <div class="setting-action" v-if="clearingSpecific === 'view'">
  <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
  </div>
  </button>

  <button type="button" class="setting-item w-full text-left" @click="clearSpecificCache('config')" :disabled="clearingSpecific === 'config'">
  <div class="setting-label">
  <span class="setting-title">{{ $t('Config Cache') }}</span>
  <span class="setting-desc">{{ $t('Clear cached app configurations') }}</span>
  </div>
  <div class="setting-action" v-if="clearingSpecific === 'config'">
  <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
  </div>
  </button>

  <button type="button" class="setting-item w-full text-left" @click="clearSpecificCache('route')" :disabled="clearingSpecific === 'route'">
  <div class="setting-label">
  <span class="setting-title">{{ $t('Route Cache') }}</span>
  <span class="setting-desc">{{ $t('Clear cached route registrations') }}</span>
  </div>
  <div class="setting-action" v-if="clearingSpecific === 'route'">
  <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
  </div>
  </button>
  
  <div class="settings-divider"></div>
  
  <!-- Go to Settings -->
  <router-link to="/admin/settings/cache" class="setting-item" @click="showCacheDropdown = false">
  <div class="setting-label">
  <span class="setting-title">{{ $t('Cache Settings') }}</span>
  <span class="setting-desc">{{ $t('Manage settings and Redis config') }}</span>
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

 <!-- Settings Dropdown -->
 <div class="topbar-settings-dropdown">
 <button
 @click="showSettings = !showSettings"
 v-click-outside="() => showSettings = false"
 class="topbar-button settings-btn"
 :title="$t('Editor Settings')"
 :class="{'active': showSettings }"
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
import { ref, computed, onMounted, onUnmounted, watch, inject } from'vue';
import { useRoute, useRouter } from'vue-router';
import { useAuthStore } from'../stores/auth';
import { useThemeStore } from'../stores/theme';
import { useTranslation } from'../composables/useTranslation';
import axios from'axios';
import TopbarMenuItem from'./TopbarMenuItem.vue';
import { useLandingStore } from'../stores/landingStore';
import { useDialog } from'../composables/useDialog';

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

import LanguageSwitcher from'./LanguageSwitcher.vue';
import CurrencySwitcher from'./CurrencySwitcher.vue';
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

const showCacheDropdown = ref(false);
const systemCacheEnabled = ref(true);
const clearing = ref(false);
const fixing = ref(false);
const clearingSpecific = ref<string | null>(null);

const loadCacheStatus = async () => {
  try {
    const res = await axios.get('/api/v1/settings/group/cache_optimization');
    const settings = res.data?.data || {};
    systemCacheEnabled.value = (settings.polycms_cache_enabled?.value !== 'no');
  } catch (e) {
    console.error('Failed to load cache status:', e);
  }
};

const toggleSystemCache = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  const isChecked = target.checked;
  const newValue = isChecked ? 'yes' : 'no';
  
  try {
    await axios.put('/api/v1/settings/group/cache_optimization', {
      settings: {
        polycms_cache_enabled: { value: newValue, type: 'string', group: 'cache_optimization' }
      }
    });
    systemCacheEnabled.value = isChecked;
    dialog.success(t('Cache status updated successfully!'));
  } catch (e) {
    console.error('Failed to update system cache setting:', e);
    dialog.error(t('Failed to update system cache setting.'));
    target.checked = !isChecked;
  }
};

const clearAllCaches = async () => {
  const confirmed = await dialog.confirm(
    $t('Are you sure you want to clear all caches?')
  );
  if (!confirmed) return;

  clearing.value = true;
  try {
    await axios.post('/api/v1/system/cache/clear', { types: ['all'] });
    dialog.success($t('All caches cleared successfully!'));
  } catch (e) {
    dialog.error($t('Failed to clear caches.'));
  } finally {
    clearing.value = false;
    showCacheDropdown.value = false;
  }
};

const fixPermissions = async () => {
  fixing.value = true;
  try {
    await axios.post('/api/v1/cache/fix-permissions');
    dialog.success($t('Permissions fixed successfully!'));
  } catch (e) {
    dialog.error($t('Failed to fix permissions.'));
  } finally {
    fixing.value = false;
    showCacheDropdown.value = false;
  }
};

const clearSpecificCache = async (type: string) => {
  clearingSpecific.value = type;
  try {
    const payload: any = { types: [type] };
    if (type === 'page_current') {
      payload.current_url = window.location.href;
    }
    await axios.post('/api/v1/system/cache/clear', payload);
    dialog.success($t('Cache cleared successfully!'));
    if (type === 'page_current') {
      setTimeout(() => window.location.reload(), 600);
    }
  } catch (e) {
    dialog.error($t('Failed to clear cache.'));
  } finally {
    clearingSpecific.value = null;
    showCacheDropdown.value = false;
  }
};

onMounted(() => {
  loadCacheStatus();
});

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
 group?:'left' |'right';
 highlight?: boolean;
 children?: MenuItem[];
 method?:'GET' |'POST';
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
 .filter(item => (item.group ||'left') ==='left')
 .sort((a, b) => (a.priority || 10) - (b.priority || 10));
});

const rightItems = computed(() => {
 return menuItems.value
 .filter(item => (item.group ||'left') ==='right')
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
 background: rgb(var(--admin-theme-sidebar));
 color: #fff;
 font-size: 13px;
 line-height: 32px;
 height: 32px;
 min-height: 32px;
 max-height: 32px;
 box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
 border-bottom: 1px solid rgba(255, 255, 255, 0.08);
 font-family: -apple-system, BlinkMacSystemFont,"Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell,"Helvetica Neue", sans-serif;
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
 color: rgb(var(--admin-theme-sidebar-text));
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
 color: rgb(var(--admin-theme-sidebar-text)) !important;
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
 color: rgb(var(--admin-theme-sidebar-text-active));
 background: rgb(var(--admin-theme-sidebar-hover));
 }

 :deep(.polycms-topbar .topbar-highlight) {
 background: rgb(var(--admin-theme-primary)) !important;
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
 content:'';
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
 border-top-color: rgb(var(--admin-theme-sidebar-text-active));
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
 color: rgb(var(--admin-theme-sidebar-text)) !important;
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
 background: rgb(var(--admin-theme-sidebar-hover)) !important;
 color: rgb(var(--admin-theme-sidebar-text-active)) !important;
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
 background: rgb(var(--admin-theme-sidebar));
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
 content:'';
 position: absolute;
 bottom: 100%;
 left: 0;
 right: 0;
 height: 4px;
 background: transparent;
 }
  :deep(.polycms-topbar .topbar-dropdown.touch-open > .topbar-dropdown-content) {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

    /* ===== Mobile responsive: topbar items overflow scroll & compact icons ===== */
    @media (max-width: 1023px) {
        .polycms-topbar {
            max-width: 100vw;
            overflow-x: auto;
            overflow-y: visible;
            scrollbar-width: none;
        }

        .polycms-topbar::-webkit-scrollbar {
            display: none;
        }

        .polycms-topbar-container {
            padding: 0 4px;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .polycms-topbar-container::-webkit-scrollbar {
            display: none;
        }

        :deep(.polycms-topbar .topbar-dropdown.touch-open > .topbar-dropdown-content) {
            position: fixed !important;
            top: 38px !important;
            left: 8px !important;
            right: 8px !important;
            width: auto !important;
            max-width: none !important;
            min-width: 0 !important;
            max-height: calc(100vh - 52px) !important;
            overflow-y: auto !important;
            border-radius: 12px !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            z-index: 100005 !important;
            background: rgb(var(--admin-theme-sidebar)) !important;
        }

        .polycms-topbar-left {
            overflow: visible;
            flex-shrink: 1;
            min-width: 0;
        }

        .polycms-topbar-right {
            flex-shrink: 0;
        }

        /* Hide text labels, show only icons on mobile screens */
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

    @media (max-width: 768px) {
        /* On mobile screens, hide label text on topbar trigger buttons if they have icons */
        :deep(.polycms-topbar-left .topbar-dropdown > a > span:not(.topbar-icon)),
        :deep(.polycms-topbar-left .topbar-dropdown > button > span:not(.topbar-icon)),
        :deep(.polycms-topbar-left > a > span:not(.topbar-icon)),
        :deep(.polycms-topbar-left > button > span:not(.topbar-icon)),
        :deep(.polycms-topbar-left > div > a > span:not(.topbar-icon)),
        :deep(.polycms-topbar-left > div > button > span:not(.topbar-icon)),
        :deep(.polycms-topbar-right .topbar-dropdown > a > span:not(.topbar-icon)),
        :deep(.polycms-topbar-right .topbar-dropdown > button > span:not(.topbar-icon)),
        :deep(.polycms-topbar-right > a > span:not(.topbar-icon)),
        :deep(.polycms-topbar-right > button > span:not(.topbar-icon)),
        :deep(.polycms-topbar-right > div > a > span:not(.topbar-icon)),
        :deep(.polycms-topbar-right > div > button > span:not(.topbar-icon)) {
            display: none !important;
        }

        /* Reduce dropdown arrow on mobile */
        :deep(.polycms-topbar-left > .topbar-dropdown > a::after),
        :deep(.polycms-topbar-left .topbar-dropdown > a::after),
        :deep(.polycms-topbar-right > .topbar-dropdown > a::after),
        :deep(.polycms-topbar-right .topbar-dropdown > a::after) {
            margin-left: 2px !important;
        }
    }

 /* ===== Settings Dropdown ===== */
 .topbar-settings-dropdown {
 position: relative;
 height: 32px;
 display: flex;
 align-items: center;
 }

  .settings-btn,
  .cache-btn {
  padding: 0 8px !important;
  width: auto !important;
  justify-content: center !important;
  margin-left: 2px;
  }

  .settings-btn.active,
  .cache-btn.active {
  color: rgb(var(--admin-theme-sidebar-text-active)) !important;
  background: rgb(var(--admin-theme-sidebar-hover)) !important;
  height: 32px;
  display: inline-flex;
  align-items: center;
  }

  .settings-btn .topbar-icon svg,
  .cache-btn .topbar-icon svg {
  width: 14px !important;
  height: 14px !important;
  }

 .settings-dropdown-content {
 position: absolute;
 top: 100%;
 right: 0;
 margin-top: 4px;
 background: rgb(var(--admin-theme-sidebar));
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
 color: rgb(var(--admin-theme-sidebar-text));
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
 background: rgb(var(--admin-theme-sidebar-hover));
 }

 .setting-label {
 display: flex;
 flex-direction: column;
 gap: 2px;
 }

 .setting-title {
 color: rgb(var(--admin-theme-sidebar-text-active));
 font-weight: 500;
 font-size: 13px;
 line-height: 1;
 }

 .setting-desc {
 color: rgb(var(--admin-theme-sidebar-text));
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
