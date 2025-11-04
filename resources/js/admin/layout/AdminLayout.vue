<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <aside class="bg-gray-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 transform md:relative md:translate-x-0 transition duration-200 ease-in-out">
            <div class="flex items-center justify-between px-4">
                <router-link :to="{ name: 'admin.dashboard' }" class="text-white text-2xl font-semibold uppercase">
                    PolyCMS
                </router-link>
            </div>

            <nav>
                <router-link
                    :to="{ name: 'admin.dashboard' }"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white"
                >
                    Dashboard
                </router-link>
                <router-link
                    :to="{ name: 'admin.posts.index' }"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white"
                >
                    Posts
                </router-link>
                <router-link
                    :to="{ name: 'admin.products.index' }"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white"
                >
                    Products
                </router-link>
                <router-link
                    :to="{ name: 'admin.categories.index' }"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white"
                >
                    Categories
                </router-link>
                <router-link
                    :to="{ name: 'admin.tags.index' }"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white"
                >
                    Tags
                </router-link>
                <router-link
                    :to="{ name: 'admin.media.index' }"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white"
                >
                    Media
                </router-link>
                <router-link
                    :to="{ name: 'admin.widgets.index' }"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white"
                >
                    Widgets
                </router-link>
                <a
                    href="#"
                    @click.prevent="authStore.logout()"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white"
                >
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
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
</script>
