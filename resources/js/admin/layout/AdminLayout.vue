<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <aside class="bg-gray-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 transform md:relative md:translate-x-0 transition duration-200 ease-in-out overflow-y-auto">
            <div class="flex items-center justify-between px-4">
                <router-link :to="{ name: 'admin.dashboard' }" class="text-white text-2xl font-semibold uppercase">
                    PolyCMS
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
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col md:ml-0">
            <!-- Header -->
            <header class="w-full bg-white shadow-sm p-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Admin Panel</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">{{ authStore.user?.name }}</span>
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
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';
import MenuItem from './MenuItem.vue';

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
const route = useRoute();

const menuItems = ref<MenuItemData[]>([]);
const menuLoading = ref(true);
const openMenus = ref<Set<string>>(new Set());

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
});
</script>
