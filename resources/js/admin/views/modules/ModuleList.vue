<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Modules</h1>
                <p class="text-sm text-gray-600 mt-1">Manage and enable/disable modules</p>
            </div>
            <button
                @click="loadModules"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                Refresh
            </button>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Filter Checkboxes -->
                <div class="flex items-center gap-6">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="filters.showActive"
                            @change="applyFilters"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        />
                        <span class="ml-2 text-sm font-medium text-gray-700">
                            Activate ({{ stats.active }})
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="filters.showInactive"
                            @change="applyFilters"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        />
                        <span class="ml-2 text-sm font-medium text-gray-700">
                            Deactivate ({{ stats.inactive }})
                        </span>
                    </label>
                </div>

                <!-- Search Bar and View Toggle -->
                <div class="flex items-center gap-3 flex-1 md:max-w-md">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="filters.search"
                            @input="applyFilters"
                            type="text"
                            placeholder="Search..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                    
                    <!-- View Toggle -->
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'p-2 transition-colors',
                                viewMode === 'grid'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white text-gray-600 hover:bg-gray-50'
                            ]"
                            title="Grid View"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            :class="[
                                'p-2 transition-colors border-l border-gray-300',
                                viewMode === 'list'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white text-gray-600 hover:bg-gray-50'
                            ]"
                            title="List View"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions (Only for List View) -->
            <div v-if="viewMode === 'list' && selectedModules.length > 0" class="mt-4 pt-4 border-t border-gray-200 flex items-center gap-4">
                <select v-model="bulkAction" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Select action</option>
                    <option value="enable">Activate</option>
                    <option value="disable">Deactivate</option>
                    <option value="delete">Delete</option>
                </select>
                <button
                    @click="applyBulkAction"
                    :disabled="!bulkAction"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm"
                >
                    Apply
                </button>
                <span class="text-sm text-gray-600">{{ selectedModules.length }} module(s) selected</span>
                <button
                    @click="selectedModules = []"
                    class="text-sm text-gray-600 hover:text-gray-900"
                >
                    Clear selection
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12 bg-white rounded-lg shadow">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600">Loading modules...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredModules.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No modules found</h3>
            <p class="mt-1 text-sm text-gray-500">
                <span v-if="modules.length === 0">
                    Modules should be placed in the <code class="bg-gray-100 px-2 py-1 rounded">modules/</code> directory.
                </span>
                <span v-else>
                    No modules match your current filters.
                </span>
            </p>
        </div>

        <!-- Modules Grid View -->
        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="module in paginatedModules"
                :key="module.key"
                class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow"
            >
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ module.name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ module.vendor }}.{{ module.module }}</p>
                        </div>
                        <span
                            :class="[
                                'px-2 py-1 text-xs font-semibold rounded-full',
                                module.enabled
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-gray-100 text-gray-800'
                            ]"
                        >
                            {{ module.enabled ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p v-if="module.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
                        {{ module.description }}
                    </p>

                    <!-- Meta Info -->
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                        <span>Version: {{ module.version }}</span>
                        <span v-if="module.has_provider" class="text-green-600">✓ Provider</span>
                        <span v-else class="text-yellow-600">⚠ No Provider</span>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-gray-200 space-y-3">
                        <!-- Toggle Switch -->
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-700">
                                Enable Module
                            </label>
                            <button
                                @click="toggleModule(module)"
                                :disabled="toggleLoading === module.key"
                                type="button"
                                :class="[
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                                    module.enabled
                                        ? 'bg-indigo-600'
                                        : 'bg-gray-200',
                                    toggleLoading === module.key ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <span
                                    :class="[
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                        module.enabled ? 'translate-x-5' : 'translate-x-0'
                                    ]"
                                >
                                    <span v-if="toggleLoading === module.key" class="absolute inset-0 flex items-center justify-center">
                                        <div class="h-3 w-3 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></div>
                                    </span>
                                </span>
                            </button>
                        </div>

                        <!-- Delete Button -->
                        <button
                            @click="deleteModule(module)"
                            :disabled="deleteLoading === module.key"
                            type="button"
                            class="w-full px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="deleteLoading === module.key" class="flex items-center justify-center">
                                <div class="h-4 w-4 border-2 border-red-400 border-t-transparent rounded-full animate-spin mr-2"></div>
                                Deleting...
                            </span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Module
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Pagination for Grid -->
            <div v-if="filteredModules.length > pagination.perPage" class="col-span-full mt-6">
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button
                            @click="pagination.currentPage--"
                            :disabled="pagination.currentPage === 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            @click="pagination.currentPage++"
                            :disabled="pagination.currentPage >= totalPages"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ (pagination.currentPage - 1) * pagination.perPage + 1 }}</span>
                                to
                                <span class="font-medium">{{ Math.min(pagination.currentPage * pagination.perPage, filteredModules.length) }}</span>
                                of
                                <span class="font-medium">{{ filteredModules.length }}</span>
                                results
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center">
                                <label class="text-sm text-gray-700 mr-2">Show:</label>
                                <select
                                    v-model="pagination.perPage"
                                    @change="pagination.currentPage = 1"
                                    class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option :value="10">10</option>
                                    <option :value="25">25</option>
                                    <option :value="50">50</option>
                                    <option :value="100">100</option>
                                </select>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    @click="pagination.currentPage--"
                                    :disabled="pagination.currentPage === 1"
                                    class="relative inline-flex items-center px-2 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                                >
                                    Previous
                                </button>
                                <button
                                    @click="pagination.currentPage++"
                                    :disabled="pagination.currentPage >= totalPages"
                                    class="relative inline-flex items-center px-2 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modules Table View -->
        <div v-else class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    @change="toggleSelectAll"
                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                />
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Module
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Version
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="module in paginatedModules"
                            :key="module.key"
                            :class="[
                                selectedModules.includes(module.key) ? 'bg-indigo-50' : '',
                                'hover:bg-gray-50 transition-colors'
                            ]"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input
                                    type="checkbox"
                                    :value="module.key"
                                    v-model="selectedModules"
                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-gray-100 rounded-lg">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ module.name }}</div>
                                        <div class="text-sm text-gray-500">{{ module.vendor }}.{{ module.module }}</div>
                                        <div class="mt-1 flex items-center gap-3">
                                            <a
                                                v-if="!module.enabled"
                                                href="#"
                                                @click.prevent="toggleModule(module)"
                                                class="text-sm text-indigo-600 hover:text-indigo-900"
                                            >
                                                Activate
                                            </a>
                                            <a
                                                v-else
                                                href="#"
                                                @click.prevent="toggleModule(module)"
                                                class="text-sm text-orange-600 hover:text-orange-900"
                                            >
                                                Deactivate
                                            </a>
                                            <span class="text-gray-300">|</span>
                                            <a
                                                href="#"
                                                @click.prevent="deleteModule(module)"
                                                class="text-sm text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-md line-clamp-2">
                                    {{ module.description || 'No description available' }}
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                            module.enabled
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ module.enabled ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span
                                        v-if="module.has_provider"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        ✓ Provider
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ module.version }}</div>
                                <div class="text-sm text-gray-500">By {{ module.vendor }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="toggleModule(module)"
                                        :disabled="toggleLoading === module.key"
                                        type="button"
                                        :class="[
                                            'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                                            module.enabled
                                                ? 'bg-indigo-600'
                                                : 'bg-gray-200',
                                            toggleLoading === module.key ? 'opacity-50 cursor-not-allowed' : ''
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                                module.enabled ? 'translate-x-5' : 'translate-x-0'
                                            ]"
                                        >
                                            <span v-if="toggleLoading === module.key" class="absolute inset-0 flex items-center justify-center">
                                                <div class="h-3 w-3 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></div>
                                            </span>
                                        </span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="filteredModules.length > pagination.perPage" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button
                        @click="pagination.currentPage--"
                        :disabled="pagination.currentPage === 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <button
                        @click="pagination.currentPage++"
                        :disabled="pagination.currentPage >= totalPages"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ (pagination.currentPage - 1) * pagination.perPage + 1 }}</span>
                            to
                            <span class="font-medium">{{ Math.min(pagination.currentPage * pagination.perPage, filteredModules.length) }}</span>
                            of
                            <span class="font-medium">{{ filteredModules.length }}</span>
                            results
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center">
                            <label class="text-sm text-gray-700 mr-2">Show:</label>
                            <select
                                v-model="pagination.perPage"
                                @change="pagination.currentPage = 1"
                                class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                        <div class="flex gap-1">
                            <button
                                @click="pagination.currentPage--"
                                :disabled="pagination.currentPage === 1"
                                class="relative inline-flex items-center px-2 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                            >
                                Previous
                            </button>
                            <button
                                @click="pagination.currentPage++"
                                :disabled="pagination.currentPage >= totalPages"
                                class="relative inline-flex items-center px-2 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

interface Module {
    key: string;
    name: string;
    vendor: string;
    module: string;
    version: string;
    description: string;
    enabled: boolean;
    has_provider: boolean;
}

const modules = ref<Module[]>([]);
const loading = ref(false);
const toggleLoading = ref<string | null>(null);
const deleteLoading = ref<string | null>(null);
const selectedModules = ref<string[]>([]);
const bulkAction = ref('');
// Load view mode from localStorage or default to 'list'
const viewMode = ref<'grid' | 'list'>(
    (localStorage.getItem('modules_view_mode') as 'grid' | 'list') || 'list'
);

// Save view mode to localStorage when it changes
watch(viewMode, (newMode) => {
    localStorage.setItem('modules_view_mode', newMode);
});

const filters = ref({
    showActive: true,
    showInactive: true,
    search: '',
});

const pagination = ref({
    currentPage: 1,
    perPage: 25,
});

// Computed stats
const stats = computed(() => {
    const active = modules.value.filter(m => m.enabled).length;
    const inactive = modules.value.filter(m => !m.enabled).length;
    return { active, inactive, total: modules.value.length };
});

// Filtered modules
const filteredModules = computed(() => {
    let result = modules.value;

    // Filter by status
    if (!filters.value.showActive && !filters.value.showInactive) {
        return [];
    }
    if (!filters.value.showActive) {
        result = result.filter(m => !m.enabled);
    }
    if (!filters.value.showInactive) {
        result = result.filter(m => m.enabled);
    }

    // Filter by search
    if (filters.value.search) {
        const search = filters.value.search.toLowerCase();
        result = result.filter(m =>
            m.name.toLowerCase().includes(search) ||
            m.vendor.toLowerCase().includes(search) ||
            m.module.toLowerCase().includes(search) ||
            (m.description && m.description.toLowerCase().includes(search))
        );
    }

    return result;
});

// Paginated modules
const totalPages = computed(() => {
    return Math.ceil(filteredModules.value.length / pagination.value.perPage);
});

const paginatedModules = computed(() => {
    const start = (pagination.value.currentPage - 1) * pagination.value.perPage;
    const end = start + pagination.value.perPage;
    return filteredModules.value.slice(start, end);
});

// Select all
const allSelected = computed(() => {
    return paginatedModules.value.length > 0 && 
           paginatedModules.value.every(m => selectedModules.value.includes(m.key));
});

const toggleSelectAll = () => {
    if (allSelected.value) {
        // Deselect all on current page
        paginatedModules.value.forEach(m => {
            const index = selectedModules.value.indexOf(m.key);
            if (index > -1) {
                selectedModules.value.splice(index, 1);
            }
        });
    } else {
        // Select all on current page
        paginatedModules.value.forEach(m => {
            if (!selectedModules.value.includes(m.key)) {
                selectedModules.value.push(m.key);
            }
        });
    }
};

// Apply filters (reset to page 1)
const applyFilters = () => {
    pagination.value.currentPage = 1;
};

// Watch pagination changes
watch(() => pagination.value.currentPage, () => {
    selectedModules.value = [];
});

const loadModules = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/modules');
        modules.value = response.data.data || [];
    } catch (error) {
        console.error('Error loading modules:', error);
        alert('Failed to load modules');
    } finally {
        loading.value = false;
    }
};

const toggleModule = async (module: Module) => {
    if (toggleLoading.value) return;

    toggleLoading.value = module.key;

    try {
        if (module.enabled) {
            await axios.post(`/api/v1/modules/${encodeURIComponent(module.key)}/disable`);
            module.enabled = false;
        } else {
            await axios.post(`/api/v1/modules/${encodeURIComponent(module.key)}/enable`);
            module.enabled = true;
        }
    } catch (error: any) {
        console.error('Error toggling module:', error);
        const message = error.response?.data?.message || 'Failed to toggle module';
        alert(message);
        await loadModules();
    } finally {
        toggleLoading.value = null;
    }
};

const deleteModule = async (module: Module) => {
    if (deleteLoading.value) return;

    if (!confirm(`Are you sure you want to delete "${module.name}"?\n\nThis will permanently delete all module files. This action cannot be undone.`)) {
        return;
    }

    deleteLoading.value = module.key;

    try {
        await axios.delete(`/api/v1/modules/${encodeURIComponent(module.key)}`);
        await loadModules();
        selectedModules.value = selectedModules.value.filter(key => key !== module.key);
    } catch (error: any) {
        console.error('Error deleting module:', error);
        const message = error.response?.data?.message || 'Failed to delete module';
        alert(message);
        await loadModules();
    } finally {
        deleteLoading.value = null;
    }
};

const applyBulkAction = async () => {
    if (!bulkAction.value || selectedModules.value.length === 0) return;

    const action = bulkAction.value;
    const count = selectedModules.value.length;

    if (action === 'delete') {
        if (!confirm(`Are you sure you want to delete ${count} module(s)?\n\nThis will permanently delete all module files. This action cannot be undone.`)) {
            return;
        }
    } else {
        if (!confirm(`Are you sure you want to ${action} ${count} module(s)?`)) {
            return;
        }
    }

    try {
        const promises = selectedModules.value.map(async (key) => {
            if (action === 'enable') {
                await axios.post(`/api/v1/modules/${encodeURIComponent(key)}/enable`);
            } else if (action === 'disable') {
                await axios.post(`/api/v1/modules/${encodeURIComponent(key)}/disable`);
            } else if (action === 'delete') {
                await axios.delete(`/api/v1/modules/${encodeURIComponent(key)}`);
            }
        });

        await Promise.all(promises);
        
        await loadModules();
        selectedModules.value = [];
        bulkAction.value = '';
        alert(`${count} module(s) ${action}d successfully`);
    } catch (error: any) {
        console.error('Error applying bulk action:', error);
        const message = error.response?.data?.message || `Failed to ${action} modules`;
        alert(message);
        await loadModules();
    }
};

onMounted(() => {
    loadModules();
});
</script>
