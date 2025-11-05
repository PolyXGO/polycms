<template>
    <div
        v-if="shouldShow"
        class="polycms-topbar"
    >
        <div class="polycms-topbar-container">
            <!-- Left Menu -->
            <div class="polycms-topbar-left">
                <template v-for="item in leftItems" :key="item.id">
                    <TopbarMenuItem :item="item" />
                </template>
            </div>

            <!-- Right Menu -->
            <div class="polycms-topbar-right">
                <template v-for="item in rightItems" :key="item.id">
                    <TopbarMenuItem :item="item" />
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';
import TopbarMenuItem from './TopbarMenuItem.vue';

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
        // Get current route info to pass to backend
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
        z-index: 999999;
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
        padding: 0 16px;
        height: 32px;
        min-height: 32px;
        max-height: 32px;
        gap: 8px;
        box-sizing: border-box;
        overflow: visible;
    }

    .polycms-topbar-left,
    .polycms-topbar-right {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0;
        flex-shrink: 0;
        flex-wrap: nowrap;
        overflow: visible;
        height: 32px;
    }

    .polycms-topbar-right {
        justify-content: flex-end;
    }

    :deep(.polycms-topbar a:not(.topbar-dropdown > a)),
    :deep(.polycms-topbar button:not(.topbar-button)) {
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

    /* Wrap icon and text in a container for proper spacing when using display: contents */
    :deep(.polycms-topbar-left a:not(.topbar-dropdown > a) > *),
    :deep(.polycms-topbar-left button:not(.topbar-button) > *),
    :deep(.polycms-topbar-right a:not(.topbar-dropdown > a) > *),
    :deep(.polycms-topbar-right button:not(.topbar-button) > *) {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        line-height: 32px;
        height: 32px;
        min-height: 32px;
        max-height: 32px;
        vertical-align: middle;
    }

    /* For dropdown links, keep flex layout */
    :deep(.polycms-topbar .topbar-dropdown > a),
    :deep(.polycms-topbar .topbar-button) {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 0 10px !important;
        gap: 6px !important;
        color: #d1d5db !important;
        text-decoration: none !important;
        border-radius: 3px !important;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    :deep(.polycms-topbar .topbar-icon svg) {
        width: 16px;
        height: 16px;
        fill: currentColor;
    }

    :deep(.polycms-topbar .topbar-dropdown > a::after) {
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

    :deep(.polycms-topbar .topbar-dropdown:hover > a::after) {
        border-top-color: #60a5fa;
    }

    /* Keep dropdown visible when hovering over it */
    :deep(.polycms-topbar .topbar-dropdown:hover .topbar-dropdown-content),
    :deep(.polycms-topbar .topbar-dropdown-content:hover) {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Dropdown positioning - left items align to left, right items align to right */
    :deep(.polycms-topbar-left .topbar-dropdown-content) {
        left: 0;
        right: auto;
    }

    :deep(.polycms-topbar-right .topbar-dropdown-content) {
        right: 0;
        left: auto;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .polycms-topbar-container {
            padding: 0 8px;
        }

        :deep(.polycms-topbar a),
        :deep(.polycms-topbar button) {
            padding: 0 8px;
            font-size: 12px;
        }
    }
</style>

