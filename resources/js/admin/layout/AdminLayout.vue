<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <aside class="bg-gray-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 transform md:relative md:translate-x-0 transition duration-200 ease-in-out overflow-y-auto">
            <div class="flex items-center justify-between px-4">
                <router-link :to="{ name: 'admin.dashboard' }" class="text-white text-2xl font-semibold uppercase">
                    PolyCMS
                </router-link>
            </div>

            <nav class="space-y-1">
                <router-link
                    :to="{ name: 'admin.dashboard' }"
                    :class="[
                        'flex items-center px-4 py-2.5 rounded transition duration-200',
                        $route.name === 'admin.dashboard' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                    ]"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </router-link>

                <!-- Posts Menu Group -->
                <div>
                    <button
                        @click="toggleMenu('posts')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2.5 rounded transition duration-200',
                            isPostsMenuOpen ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                        ]"
                    >
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="font-medium">Posts</span>
                        </div>
                        <svg
                            class="w-4 h-4 transition-transform duration-200"
                            :class="{ 'transform rotate-180': isPostsMenuOpen }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-show="isPostsMenuOpen" class="ml-4 mt-1 space-y-1">
                        <router-link
                            :to="{ name: 'admin.posts.index' }"
                            :class="[
                                'block px-4 py-2 rounded transition duration-200',
                                $route.name === 'admin.posts.index' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            ]"
                        >
                            All Posts
                        </router-link>
                        <router-link
                            :to="{ name: 'admin.posts.create' }"
                            :class="[
                                'block px-4 py-2 rounded transition duration-200',
                                $route.name === 'admin.posts.create' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            ]"
                        >
                            Add New
                        </router-link>
                        <router-link
                            :to="{ name: 'admin.categories.index', query: { type: 'post' } }"
                            :class="[
                                'block px-4 py-2 rounded transition duration-200',
                                $route.name === 'admin.categories.index' && $route.query.type === 'post' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            ]"
                        >
                            Categories
                        </router-link>
                        <router-link
                            :to="{ name: 'admin.tags.index', query: { type: 'post' } }"
                            :class="[
                                'block px-4 py-2 rounded transition duration-200',
                                $route.name === 'admin.tags.index' && $route.query.type === 'post' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            ]"
                        >
                            Tags
                        </router-link>
                    </div>
                </div>

                <!-- Products Menu Group -->
                <div>
                    <button
                        @click="toggleMenu('products')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2.5 rounded transition duration-200',
                            isProductsMenuOpen ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                        ]"
                    >
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="font-medium">Products</span>
                        </div>
                        <svg
                            class="w-4 h-4 transition-transform duration-200"
                            :class="{ 'transform rotate-180': isProductsMenuOpen }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-show="isProductsMenuOpen" class="ml-4 mt-1 space-y-1">
                        <router-link
                            :to="{ name: 'admin.products.index' }"
                            :class="[
                                'block px-4 py-2 rounded transition duration-200',
                                $route.name === 'admin.products.index' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            ]"
                        >
                            All Products
                        </router-link>
                        <router-link
                            :to="{ name: 'admin.products.create' }"
                            :class="[
                                'block px-4 py-2 rounded transition duration-200',
                                $route.name === 'admin.products.create' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            ]"
                        >
                            Add New
                        </router-link>
                        <router-link
                            :to="{ name: 'admin.categories.index', query: { type: 'product' } }"
                            :class="[
                                'block px-4 py-2 rounded transition duration-200',
                                $route.name === 'admin.categories.index' && $route.query.type === 'product' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            ]"
                        >
                            Categories
                        </router-link>
                        <router-link
                            :to="{ name: 'admin.tags.index', query: { type: 'product' } }"
                            :class="[
                                'block px-4 py-2 rounded transition duration-200',
                                $route.name === 'admin.tags.index' && $route.query.type === 'product' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            ]"
                        >
                            Tags
                        </router-link>
                    </div>
                </div>

                <!-- Media -->
                <router-link
                    :to="{ name: 'admin.media.index' }"
                    :class="[
                        'flex items-center px-4 py-2.5 rounded transition duration-200',
                        $route.name === 'admin.media.index' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                    ]"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Media
                </router-link>

                <!-- Widgets -->
                <router-link
                    :to="{ name: 'admin.widgets.index' }"
                    :class="[
                        'flex items-center px-4 py-2.5 rounded transition duration-200',
                        $route.name === 'admin.widgets.index' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                    ]"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z" />
                    </svg>
                    Widgets
                </router-link>

                <!-- Modules -->
                <router-link
                    :to="{ name: 'admin.modules.index' }"
                    :class="[
                        'flex items-center px-4 py-2.5 rounded transition duration-200',
                        $route.name === 'admin.modules.index' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                    ]"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Modules
                </router-link>

                <!-- Logout -->
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
            </nav>
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

const authStore = useAuthStore();
const route = useRoute();

const isPostsMenuOpen = ref(false);
const isProductsMenuOpen = ref(false);

const toggleMenu = (menu: 'posts' | 'products') => {
    if (menu === 'posts') {
        isPostsMenuOpen.value = !isPostsMenuOpen.value;
    } else if (menu === 'products') {
        isProductsMenuOpen.value = !isProductsMenuOpen.value;
    }
};

// Auto-expand menu groups based on current route
const updateMenuState = () => {
    const routeName = route.name;
    const routeType = route.query?.type;

    if (typeof routeName === 'string') {
        if (routeName.startsWith('admin.posts') ||
            (routeName === 'admin.categories.index' && routeType === 'post') ||
            (routeName === 'admin.tags.index' && routeType === 'post')) {
            isPostsMenuOpen.value = true;
        }
        if (routeName.startsWith('admin.products') ||
            (routeName === 'admin.categories.index' && routeType === 'product') ||
            (routeName === 'admin.tags.index' && routeType === 'product')) {
            isProductsMenuOpen.value = true;
        }
    }
};

watch(() => [route.name, route.query], () => {
    updateMenuState();
}, { immediate: true });

onMounted(() => {
    updateMenuState();
});
</script>
