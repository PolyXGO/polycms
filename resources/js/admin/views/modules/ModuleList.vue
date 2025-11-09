<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Modules</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage and enable/disable modules</p>
            </div>
            <div class="flex items-center gap-3">
                <input
                    ref="uploadInput"
                    type="file"
                    class="hidden"
                    accept=".zip"
                    @change="handleUpload"
                />
                <button
                    type="button"
                    @click="triggerUpload"
                    :disabled="uploadLoading"
                    class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <svg
                        v-if="uploadLoading"
                        class="h-4 w-4 animate-spin"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a12 12 0 00-12 12h4z"></path>
                    </svg>
                    <span>{{ uploadLoading ? 'Uploading...' : 'Upload Module' }}</span>
                </button>
                <button
                    type="button"
                    @click="loadModules"
                    :disabled="loading"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <svg
                        v-if="loading"
                        class="h-4 w-4 animate-spin text-gray-500 dark:text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a12 12 0 00-12 12h4z"></path>
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Filter Checkboxes -->
                <div class="flex items-center gap-6">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="filters.showActive"
                            @change="applyFilters"
                            class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 bg-white dark:bg-gray-700"
                        />
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Activate ({{ stats.active }})
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="filters.showInactive"
                            @change="applyFilters"
                            class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 bg-white dark:bg-gray-700"
                        />
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Deactivate ({{ stats.inactive }})
                        </span>
                    </label>
                </div>

                <!-- Search Bar and View Toggle -->
                <div class="flex items-center gap-3 flex-1 md:max-w-md">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="filters.search"
                            @input="applyFilters"
                            type="text"
                            placeholder="Search..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                    
                    <!-- View Toggle -->
                    <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'p-2 transition-colors',
                                viewMode === 'grid'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
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
                                'p-2 transition-colors border-l border-gray-300 dark:border-gray-600',
                                viewMode === 'list'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
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
            <div v-if="viewMode === 'list' && selectedModules.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-4">
                <select v-model="bulkAction" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
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
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ selectedModules.length }} module(s) selected</span>
                <button
                    @click="selectedModules = []"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                >
                    Clear selection
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Loading modules...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredModules.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No modules found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <span v-if="modules.length === 0">
                    Modules should be placed in the <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-900 dark:text-gray-200">modules/</code> directory.
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
                class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow"
            >
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ module.vendor }}.{{ module.module }}</p>
                        </div>
                        <span
                            :class="[
                                'px-2 py-1 text-xs font-semibold rounded-full',
                                module.enabled
                                    ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                            ]"
                        >
                            {{ module.enabled ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p v-if="module.description" class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                        {{ module.description }}
                    </p>

                    <!-- Meta Info -->
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-4">
                        <span>Version: {{ module.version }}</span>
                        <span v-if="module.has_provider" class="text-green-600 dark:text-green-400">✓ Provider</span>
                        <span v-else class="text-yellow-600 dark:text-yellow-400">⚠ No Provider</span>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
                        <!-- Toggle Switch -->
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Enable Module
                            </label>
                            <button
                                @click="toggleModule(module)"
                                :disabled="toggleLoading === module.key"
                                type="button"
                                :class="[
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800',
                                    module.enabled
                                        ? 'bg-indigo-600'
                                        : 'bg-gray-200 dark:bg-gray-600',
                                    toggleLoading === module.key ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <span
                                    :class="[
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white dark:bg-gray-300 shadow ring-0 transition duration-200 ease-in-out',
                                        module.enabled ? 'translate-x-5' : 'translate-x-0'
                                    ]"
                                >
                                    <span v-if="toggleLoading === module.key" class="absolute inset-0 flex items-center justify-center">
                                        <div class="h-3 w-3 border-2 border-gray-400 dark:border-gray-500 border-t-transparent rounded-full animate-spin"></div>
                                    </span>
                                </span>
                            </button>
                        </div>

                        <!-- Download Button -->
                        <button
                            @click="downloadModule(module)"
                            :disabled="downloadLoading === module.key"
                            type="button"
                            class="w-full px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="downloadLoading === module.key" class="flex items-center justify-center">
                                <div class="h-4 w-4 border-2 border-blue-400 border-t-transparent rounded-full animate-spin mr-2"></div>
                                Preparing...
                            </span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm4 6h8m-4 4l-3-3m3 3l3-3" />
                                </svg>
                                Download ZIP
                            </span>
                        </button>

                        <!-- Delete Button -->
                        <button
                            @click="deleteModule(module)"
                            :disabled="deleteLoading === module.key"
                            type="button"
                            class="w-full px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
                <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6 rounded-lg shadow">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button
                            @click="pagination.currentPage--"
                            :disabled="pagination.currentPage === 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            @click="pagination.currentPage++"
                            :disabled="pagination.currentPage >= totalPages"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
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
                                <label class="text-sm text-gray-700 dark:text-gray-300 mr-2">Show:</label>
                                <select
                                    v-model="pagination.perPage"
                                    @change="pagination.currentPage = 1"
                                    class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
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
                                    class="relative inline-flex items-center px-2 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
                                >
                                    Previous
                                </button>
                                <button
                                    @click="pagination.currentPage++"
                                    :disabled="pagination.currentPage >= totalPages"
                                    class="relative inline-flex items-center px-2 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
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
        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    @change="toggleSelectAll"
                                    class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 bg-white dark:bg-gray-700"
                                />
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Module
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Description
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Version
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr
                            v-for="module in paginatedModules"
                            :key="module.key"
                            :class="[
                                selectedModules.includes(module.key) ? 'bg-indigo-50 dark:bg-indigo-900/20' : '',
                                'hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors'
                            ]"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input
                                    type="checkbox"
                                    :value="module.key"
                                    v-model="selectedModules"
                                    class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 bg-white dark:bg-gray-700"
                                />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ module.name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ module.vendor }}.{{ module.module }}</div>
                                        <div class="mt-1 flex items-center gap-3">
                                            <a
                                                v-if="!module.enabled"
                                                href="#"
                                                @click.prevent="toggleModule(module)"
                                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                                            >
                                                Activate
                                            </a>
                                            <a
                                                v-else
                                                href="#"
                                                @click.prevent="toggleModule(module)"
                                                class="text-sm text-orange-600 dark:text-orange-400 hover:text-orange-900 dark:hover:text-orange-300"
                                            >
                                                Deactivate
                                            </a>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <a
                                                href="#"
                                                @click.prevent="downloadModule(module)"
                                                :class="[
                                                    'text-sm text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300',
                                                    downloadLoading === module.key ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
                                                ]"
                                            >
                                                Download
                                            </a>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <a
                                                href="#"
                                                @click.prevent="deleteModule(module)"
                                                class="text-sm text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                            >
                                                Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white max-w-md line-clamp-2">
                                    {{ module.description || 'No description available' }}
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                            module.enabled
                                                ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                                        ]"
                                    >
                                        {{ module.enabled ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span
                                        v-if="module.has_provider"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                                    >
                                        ✓ Provider
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ module.version }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">By {{ module.vendor }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="toggleModule(module)"
                                        :disabled="toggleLoading === module.key"
                                        type="button"
                                        :class="[
                                            'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800',
                                            module.enabled
                                                ? 'bg-indigo-600'
                                                : 'bg-gray-200 dark:bg-gray-600',
                                            toggleLoading === module.key ? 'opacity-50 cursor-not-allowed' : ''
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white dark:bg-gray-300 shadow ring-0 transition duration-200 ease-in-out',
                                                module.enabled ? 'translate-x-5' : 'translate-x-0'
                                            ]"
                                        >
                                            <span v-if="toggleLoading === module.key" class="absolute inset-0 flex items-center justify-center">
                                                <div class="h-3 w-3 border-2 border-gray-400 dark:border-gray-500 border-t-transparent rounded-full animate-spin"></div>
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
            <div v-if="filteredModules.length > pagination.perPage" class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button
                        @click="pagination.currentPage--"
                        :disabled="pagination.currentPage === 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <button
                        @click="pagination.currentPage++"
                        :disabled="pagination.currentPage >= totalPages"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
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
                            <label class="text-sm text-gray-700 dark:text-gray-300 mr-2">Show:</label>
                            <select
                                v-model="pagination.perPage"
                                @change="pagination.currentPage = 1"
                                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
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
                                class="relative inline-flex items-center px-2 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
                            >
                                Previous
                            </button>
                            <button
                                @click="pagination.currentPage++"
                                :disabled="pagination.currentPage >= totalPages"
                                class="relative inline-flex items-center px-2 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
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
import { useDialog } from '../../composables/useDialog';

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
const downloadLoading = ref<string | null>(null);
const uploadLoading = ref(false);
const selectedModules = ref<string[]>([]);
const bulkAction = ref('');
const dialog = useDialog();
const uploadInput = ref<HTMLInputElement | null>(null);
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

const triggerUpload = () => {
    if (uploadLoading.value) return;
    uploadInput.value?.click();
};

const handleUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    uploadLoading.value = true;

    try {
        const formData = new FormData();
        formData.append('module', file);

        await axios.post('/api/v1/modules/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        dialog.success('Module uploaded and activated successfully');

        await loadModules();

        setTimeout(() => {
            window.location.reload();
        }, 300);
    } catch (error: any) {
        console.error('Error uploading module:', error);
        const message = error.response?.data?.message || 'Failed to upload module';
        dialog.error(message);
    } finally {
        uploadLoading.value = false;

        if (input) {
            input.value = '';
        }

        if (uploadInput.value) {
            uploadInput.value.value = '';
        }
    }
};

const downloadModule = async (module: Module) => {
    if (downloadLoading.value === module.key) {
        return;
    }

    downloadLoading.value = module.key;

    try {
        const response = await axios.get(
            `/api/v1/modules/${encodeURIComponent(module.key)}/download`,
            { responseType: 'blob' }
        );

        const blob = new Blob([response.data], { type: 'application/zip' });
        let filename = `${module.module}-${module.version || 'module'}.zip`;

        const headers = response.headers as Record<string, string | undefined>;
        const contentDisposition = headers['content-disposition'] || headers['Content-Disposition'];

        if (contentDisposition) {
            const match = /filename="?([^";]+)"?/i.exec(contentDisposition);
            if (match?.[1]) {
                filename = decodeURIComponent(match[1]);
            }
        }

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error: any) {
        console.error('Error downloading module:', error);
        const message = error.response?.data?.message || 'Failed to download module';
        dialog.error(message);
    } finally {
        downloadLoading.value = null;
    }
};

const toggleModule = async (module: Module) => {
    // Prevent multiple clicks on the same module
    if (toggleLoading.value === module.key) return;

    // Find the module in the array to ensure we're working with the reactive reference
    const moduleIndex = modules.value.findIndex(m => m.key === module.key);
    if (moduleIndex === -1) {
        console.error('Module not found in array');
        return;
    }

    // Get current state before making API call - use the value from the array
    const currentEnabledState = modules.value[moduleIndex].enabled;
    const targetState = !currentEnabledState;

    // Set loading state before making the request
    toggleLoading.value = module.key;

    try {
        // Make API call to toggle module state based on current state
        const endpoint = currentEnabledState 
            ? `/api/v1/modules/${encodeURIComponent(module.key)}/disable`
            : `/api/v1/modules/${encodeURIComponent(module.key)}/enable`;
        
        await axios.post(endpoint);
        
        // Update local state optimistically for immediate UI feedback
        modules.value[moduleIndex].enabled = targetState;
        
        // Wait a bit to ensure backend has processed the change
        await new Promise(resolve => setTimeout(resolve, 200));
        
        // Reload modules from API to verify and get the latest state from server
        await loadModules();
        
        // Find the module again after reload to verify state
        const reloadedModule = modules.value.find(m => m.key === module.key);
        if (reloadedModule && reloadedModule.enabled !== targetState) {
            console.warn('State mismatch detected, forcing reload...');
            // If state doesn't match, reload page to force refresh
            window.location.reload();
            return;
        }
        
        // Reload page after successful toggle to refresh sidebar menu
        // Small delay to ensure UI update is visible
        setTimeout(() => {
            window.location.reload();
        }, 200);
    } catch (error: any) {
        console.error('Error toggling module:', error);
        
        // Revert optimistic update on error
        modules.value[moduleIndex].enabled = currentEnabledState;
        
        const message = error.response?.data?.message || 'Failed to toggle module';
        dialog.error(message);
        
        // Reload modules to get correct state from server
        await loadModules();
        toggleLoading.value = null;
    }
};

const deleteModule = async (module: Module) => {
    if (deleteLoading.value) return;

    const confirmed = await dialog.confirm({
        title: 'Delete Module',
        message: `Are you sure you want to delete "${module.name}"?\n\nThis will permanently delete all module files. This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });

    if (!confirmed) {
        return;
    }

    deleteLoading.value = module.key;

    try {
        await axios.delete(`/api/v1/modules/${encodeURIComponent(module.key)}`);
        await loadModules();
        selectedModules.value = selectedModules.value.filter(key => key !== module.key);
        dialog.success('Module deleted successfully');
    } catch (error: any) {
        console.error('Error deleting module:', error);
        const message = error.response?.data?.message || 'Failed to delete module';
        dialog.error(message);
        await loadModules();
    } finally {
        deleteLoading.value = null;
    }
};

const applyBulkAction = async () => {
    if (!bulkAction.value || selectedModules.value.length === 0) return;

    const action = bulkAction.value;
    const count = selectedModules.value.length;

    let confirmed = false;
    if (action === 'delete') {
        confirmed = await dialog.confirm({
            title: 'Delete Modules',
            message: `Are you sure you want to delete ${count} module(s)?\n\nThis will permanently delete all module files. This action cannot be undone.`,
            confirmText: 'Delete',
            cancelText: 'Cancel',
            type: 'danger',
        });
    } else {
        confirmed = await dialog.confirm({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} Modules`,
            message: `Are you sure you want to ${action} ${count} module(s)?`,
            confirmText: action.charAt(0).toUpperCase() + action.slice(1),
            cancelText: 'Cancel',
            type: 'warning',
        });
    }

    if (!confirmed) {
        return;
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
        
        // Reload page after successful bulk action to refresh sidebar menu
        if (action === 'enable' || action === 'disable') {
            window.location.reload();
        } else {
            // For delete, just reload modules list
            await loadModules();
            selectedModules.value = [];
            bulkAction.value = '';
            dialog.success(`${count} module(s) ${action}d successfully`);
        }
    } catch (error: any) {
        console.error('Error applying bulk action:', error);
        const message = error.response?.data?.message || `Failed to ${action} modules`;
        dialog.error(message);
        await loadModules();
    }
};

onMounted(() => {
    loadModules();
});
</script>
