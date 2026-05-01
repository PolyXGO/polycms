<!--
  Dashboard.vue — Sample Module Dashboard
  =========================================
  Shows a quick overview of the module + hook/filter reference for developers.
  This page serves as a live documentation hub within the admin panel.
-->
<template>
    <div>
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2"><svg class="w-6 h-6 text-indigo-500 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg> Sample Module</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Developer reference module — v2.0.0
                </p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Notes</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalNotes }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-amber-100 dark:bg-amber-900/30">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pinned</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.pinnedNotes }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Hooks Active</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">12</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hook/Filter Reference -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🪝 Hooks & Filters Reference</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    All hooks/filters demonstrated by this module
                </p>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px px-6">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'px-4 py-3 text-sm font-medium border-b-2 transition-colors',
                            activeTab === tab.key
                                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                        ]"
                    >
                        {{ tab.label }} ({{ tab.count }})
                    </button>
                </nav>
            </div>

            <!-- Hook List -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hook Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fired In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr
                            v-for="hook in filteredHooks"
                            :key="hook.name"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/30"
                        >
                            <td class="px-6 py-3">
                                <code class="text-sm font-mono text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded">{{ hook.name }}</code>
                            </td>
                            <td class="px-6 py-3">
                                <span :class="[
                                    'inline-flex px-2 py-0.5 text-xs font-medium rounded-full',
                                    hook.type === 'Action'
                                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                        : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                ]">{{ hook.type }}</span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ hook.source }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ hook.description }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const activeTab = ref('all');
const stats = ref({ totalNotes: 0, pinnedNotes: 0 });

const tabs = [
    { key: 'all', label: 'All', count: 19 },
    { key: 'action', label: 'Actions', count: 11 },
    { key: 'filter', label: 'Filters', count: 8 },
];

const hooks = ref([
    { name: 'admin.menu.build', type: 'Action', source: 'AdminMenuController', description: 'Register sidebar menu items', category: 'admin' },
    { name: 'roles.register_permissions', type: 'Action', source: 'AppServiceProvider', description: 'Register module permissions', category: 'admin' },
    { name: 'widgets.register_areas', type: 'Action', source: 'AppServiceProvider', description: 'Register widget display areas', category: 'admin' },
    { name: 'register_email_templates', type: 'Action', source: 'AppServiceProvider', description: 'Register email templates', category: 'admin' },
    { name: 'layout.register_assets', type: 'Action', source: 'AppServiceProvider', description: 'Inject CSS/JS into frontend', category: 'frontend' },
    { name: 'media.uploaded', type: 'Action', source: 'MediaService', description: 'Fired after media file upload', category: 'media' },
    { name: 'cart.item.added', type: 'Action', source: 'CartService', description: 'Fired when item added to cart', category: 'ecommerce' },
    { name: 'product.saved', type: 'Action', source: 'CreateProduct/UpdateProduct', description: 'Fired after product create/update', category: 'ecommerce' },
    { name: 'post.saved', type: 'Action', source: 'CreatePost/UpdatePost', description: 'Fired after post create/update', category: 'content' },
    { name: 'sample_module.note.created', type: 'Action', source: 'NoteController', description: 'Custom hook: note created', category: 'module' },
    { name: 'sample_module.note.deleted', type: 'Action', source: 'NoteController', description: 'Custom hook: note deleted', category: 'module' },
    { name: 'post.content.render', type: 'Filter', source: 'PostResource', description: 'Modify post HTML content output', category: 'content' },
    { name: 'content.render.blocks', type: 'Filter', source: 'ContentRenderer', description: 'Modify block array before rendering', category: 'content' },
    { name: 'theme.view.data', type: 'Filter', source: 'Frontend Controllers', description: 'Inject data into theme Blade views', category: 'frontend' },
    { name: 'settings.defaults', type: 'Filter', source: 'SettingsService', description: 'Set default values for module settings', category: 'admin' },
    { name: 'topbar.menu.items', type: 'Filter', source: 'TopbarMenuService', description: 'Add items to admin topbar menu', category: 'admin' },
    { name: 'seo.canonical_url', type: 'Filter', source: 'AppServiceProvider', description: 'Modify page canonical URL', category: 'frontend' },
    { name: 'media.url', type: 'Filter', source: 'MediaService', description: 'Modify media file URL (CDN rewrite)', category: 'media' },
    { name: 'cart.totals', type: 'Filter', source: 'CartService', description: 'Modify cart total calculations', category: 'ecommerce' },
]);

const filteredHooks = computed(() => {
    if (activeTab.value === 'all') return hooks.value;
    return hooks.value.filter(h =>
        h.type.toLowerCase() === activeTab.value
    );
});

onMounted(async () => {
    try {
        const res = await axios.get('/api/v1/sample-module/notes?per_page=1');
        stats.value.totalNotes = res.data.meta?.total ?? 0;
    } catch { /* ignore */ }
    try {
        const res = await axios.get('/api/v1/sample-module/notes?pinned=1&per_page=1');
        stats.value.pinnedNotes = res.data.meta?.total ?? 0;
    } catch { /* ignore */ }
});
</script>
