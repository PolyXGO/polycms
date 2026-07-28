<template>
 <div>
 <!-- Header -->
 <div class="flex justify-between items-center mb-6">
 <div>
 <h1 class="text-2xl font-bold text-admin-theme-text">Modules</h1>
 <p class="text-sm text-admin-theme-text-secondary mt-1">Manage and enable/disable modules</p>
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
 class="px-4 py-2 rounded-lg bg-admin-theme-primary hover:bg-admin-theme-primary-hover text-admin-theme-primary-content transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
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
 <span>{{ uploadLoading ?'Uploading...' :'Upload Module' }}</span>
 </button>
 <button
 type="button"
 @click="checkModuleUpdates"
 :disabled="checkingUpdates"
 class="px-4 py-2 border border-purple-500/40 rounded-lg hover:bg-purple-500/10 bg-purple-500/5 text-purple-400 font-medium text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer"
 >
 <svg
 v-if="checkingUpdates"
 class="h-4 w-4 animate-spin text-purple-400"
 fill="none"
 viewBox="0 0 24 24"
 >
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a12 12 0 00-12 12h4z"></path>
 </svg>
 <svg
 v-else
 class="h-4 w-4 text-purple-400"
 fill="none"
 stroke="currentColor"
 viewBox="0 0 24 24"
 >
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
 </svg>
 <span>{{ checkingUpdates ?'Checking...' :'Check Updates' }}</span>
 </button>
 <button
 type="button"
 @click="loadModules"
 :disabled="loading"
 class="px-4 py-2 border border-admin-theme-border rounded-lg hover:bg-admin-theme-base bg-admin-theme-input-bg text-admin-theme-text-secondary transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
 >
 <svg
 v-if="loading"
 class="h-4 w-4 animate-spin text-admin-theme-text-muted"
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
 <div class="bg-admin-theme-surface rounded-lg shadow p-4 mb-6">
 <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <!-- Filter Checkboxes -->
 <div class="flex items-center gap-6">
 <FormCheckbox
 name="showActive"
 v-model="filters.showActive"
 :label="`Activate (${stats.active})`"
 @update:model-value="applyFilters"
 />
 <FormCheckbox
 name="showInactive"
 v-model="filters.showInactive"
 :label="`Deactivate (${stats.inactive})`"
 @update:model-value="applyFilters"
 />
 </div>

 <!-- Search Bar and View Toggle -->
 <div class="flex items-center gap-3 flex-1 md:max-w-md">
 <div class="relative flex-1">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <svg class="h-5 w-5 text-admin-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
 </svg>
 </div>
 <input
 v-model="filters.search"
 @input="applyFilters"
 type="text"
 placeholder="Search..."
 class="block w-full pl-10 pr-3 py-2 border border-admin-theme-border rounded-lg leading-5 bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:outline-none focus:ring-1 focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 />
 </div>

 <!-- View Toggle -->
 <div class="flex items-center border border-admin-theme-border rounded-lg overflow-hidden">
 <button
 @click="viewMode ='grid'"
 :class="[
'p-2 transition-colors',
 viewMode ==='grid'
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5'
 ]"
 title="Grid View"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
 </svg>
 </button>
 <button
 @click="viewMode ='list'"
 :class="[
'p-2 transition-colors border-l border-admin-theme-border',
 viewMode ==='list'
 ?'bg-admin-theme-primary text-admin-theme-primary-content'
 :'bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5'
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
 <div v-if="viewMode ==='list' && selectedModules.length > 0" class="mt-4 pt-4 border-t border-admin-theme-border flex items-center gap-4">
 <select v-model="bulkAction" class="px-3 py-2 border border-admin-theme-border rounded-lg text-sm bg-admin-theme-input-bg text-admin-theme-text">
 <option value="">Select action</option>
 <option value="enable">Activate</option>
 <option value="disable">Deactivate</option>
 <option value="delete">Delete</option>
 </select>
 <button
 @click="applyBulkAction"
 :disabled="!bulkAction"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm"
 >
 Apply
 </button>
 <span class="text-sm text-admin-theme-text-secondary">{{ selectedModules.length }} module(s) selected</span>
 <button
 @click="selectedModules = []"
 class="text-sm text-admin-theme-text-secondary hover:text-admin-theme-text"
 >
 Clear selection
 </button>
 </div>
 </div>

 <!-- Loading State -->
 <div v-if="loading" class="text-center py-12 bg-admin-theme-surface rounded-lg shadow">
 <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
 <p class="mt-2 text-admin-theme-text-secondary">Loading modules...</p>
 </div>

 <!-- Empty State -->
 <div v-else-if="filteredModules.length === 0" class="bg-admin-theme-surface rounded-lg shadow p-12 text-center">
 <svg class="mx-auto h-12 w-12 text-admin-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
 </svg>
 <h3 class="mt-2 text-sm font-medium text-admin-theme-text">No modules found</h3>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 <span v-if="modules.length === 0">
 Modules should be placed in the <code class="bg-admin-theme-base px-2 py-1 rounded text-gray-900">modules/</code> directory.
 </span>
 <span v-else>
 No modules match your current filters.
 </span>
 </p>
 </div>

 <!-- Modules Grid View -->
 <div v-else-if="viewMode ==='grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 <div
 v-for="module in paginatedModules"
 :key="module.key"
 class="bg-admin-theme-surface rounded-lg shadow hover:shadow-lg transition-shadow"
 >
 <div class="p-6">
 <!-- Header -->
 <div class="flex items-start justify-between mb-4">
 <div class="flex-1">
 <h3 class="text-lg font-semibold text-admin-theme-text">{{ module.name }}</h3>
 <p class="text-sm text-admin-theme-text-muted mt-1">{{ module.vendor }}.{{ module.module }}</p>
 </div>
 <div class="flex gap-2">
 <span
 v-if="module.sandbox"
 class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200"
 >
 Sandbox App
 </span>
 <span
 :class="[
'px-2 py-1 text-xs font-semibold rounded-full',
 module.enabled
 ?'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
 :'bg-admin-theme-base text-admin-theme-text'
 ]"
 >
 {{ module.enabled ?'Active' :'Inactive' }}
 </span>
 </div>
 </div>

 <!-- Description -->
 <p v-if="module.description" class="text-sm text-admin-theme-text-secondary mb-4 line-clamp-2">
 {{ module.description }}
 </p>

 <!-- Update Notification Banner (Grid) -->
 <div v-if="module.has_update" class="mb-4 p-3 rounded-lg bg-purple-500/10 border border-purple-500/30 text-xs flex items-center justify-between gap-2">
 <div class="flex items-center gap-2">
 <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-purple-500/20 text-purple-400 border border-purple-500/30 uppercase">
 v{{ module.latest_version }} Available
 </span>
 </div>
 <button
    @click="performInAppModuleUpdate(module)"
    :disabled="updatingModule === module.key"
    class="text-purple-400 hover:text-purple-300 font-medium hover:underline disabled:opacity-50 inline-flex items-center gap-1"
  >
    <span v-if="updatingModule === module.key" class="w-3 h-3 border-2 border-purple-400 border-t-transparent rounded-full animate-spin"></span>
    <span>{{ updatingModule === module.key ? 'Updating...' : 'Update Now →' }}</span>
  </button>
 </div>

 <!-- Meta Info -->
 <div class="flex items-center justify-between text-xs text-admin-theme-text-muted mb-4">
 <span>Version: {{ module.version }}</span>
 <span v-if="module.has_provider" class="text-green-600 dark:text-green-400">✓ Provider</span>
 <span v-else class="text-yellow-600 dark:text-yellow-400">⚠ No Provider</span>
 </div>

 <!-- Actions -->
 <div class="pt-4 border-t border-admin-theme-border space-y-3">
 <FormToggle
 :name="`enable-${module.key}`"
 :model-value="module.enabled"
 label="Enable Module"
 :disabled="toggleLoading === module.key"
 @update:model-value="toggleModule(module)"
 class="justify-between w-full"
 />

 <!-- Action Buttons (Actions from Meta) -->
 <div class="flex flex-wrap gap-2 mb-3" v-if="module.meta?.actions">
 <button
 v-for="(action, index) in module.meta.actions"
 :key="index"
 @click="performAction(action)"
 class="inline-flex items-center text-xs font-medium text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary"
 >
 <svg v-if="action.type ==='modal'" class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
 </svg>
 {{ getLocaleText(action.label) }}
 </button>
 </div>

 <!-- Action Buttons (Download & Delete) -->
 <div class="flex gap-3">
 <!-- Download Button -->
 <button
 @click="downloadModule(module)"
 :disabled="downloadLoading === module.key"
 class="inline-flex items-center text-xs font-medium text-admin-theme-text-secondary hover:text-admin-theme-text disabled:opacity-50"
 >
 <svg v-if="downloadLoading !== module.key" class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
 </svg>
 <div v-else class="mr-1 h-3 w-3 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></div>
 Download
 </button>
 <button
 @click="deleteModule(module)"
 :disabled="deleteLoading === module.key"
 class="inline-flex items-center text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-500 disabled:opacity-50"
 >
 <svg v-if="deleteLoading !== module.key" class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
 </svg>
 <div v-else class="mr-1 h-3 w-3 border-2 border-red-400 border-t-transparent rounded-full animate-spin"></div>
 Delete
 </button>
 </div>
 </div>
 </div>
 </div>

 <!-- Pagination for Grid -->
 <div v-if="filteredModules.length > pagination.perPage" class="col-span-full mt-6">
 <div class="bg-admin-theme-surface px-4 py-3 flex items-center justify-between border-t border-admin-theme-border sm:px-6 rounded-lg shadow">
 <div class="flex-1 flex justify-between sm:hidden">
 <button
 @click="pagination.currentPage--"
 :disabled="pagination.currentPage === 1"
 class="relative inline-flex items-center px-4 py-2 border border-admin-theme-border text-sm font-medium rounded-md text-admin-theme-text-secondary bg-admin-theme-input-bg hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
 >
 Previous
 </button>
 <button
 @click="pagination.currentPage++"
 :disabled="pagination.currentPage >= totalPages"
 class="ml-3 relative inline-flex items-center px-4 py-2 border border-admin-theme-border text-sm font-medium rounded-md text-admin-theme-text-secondary bg-admin-theme-input-bg hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
 >
 Next
 </button>
 </div>
 <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
 <div>
 <p class="text-sm text-admin-theme-text-secondary">
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
 <label class="text-sm text-admin-theme-text-secondary mr-2">Show:</label>
 <select
 v-model="pagination.perPage"
 @change="pagination.currentPage = 1"
 class="px-3 py-1 border border-admin-theme-border rounded-md text-sm bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-admin-theme-primary focus:border-admin-theme-primary"
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
 class="relative inline-flex items-center px-2 py-2 border border-admin-theme-border text-sm font-medium rounded-md text-admin-theme-text-secondary bg-admin-theme-input-bg hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
 >
 Previous
 </button>
 <button
 @click="pagination.currentPage++"
 :disabled="pagination.currentPage >= totalPages"
 class="relative inline-flex items-center px-2 py-2 border border-admin-theme-border text-sm font-medium rounded-md text-admin-theme-text-secondary bg-admin-theme-input-bg hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
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
 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base">
 <tr>
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
 <input
 type="checkbox"
 :checked="allSelected"
 @change="toggleSelectAll"
 class="w-4 h-4 text-admin-theme-primary border-admin-theme-border rounded focus:ring-admin-theme-primary bg-admin-theme-input-bg"
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
 <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
 <tr
 v-for="module in paginatedModules"
 :key="module.key"
 :class="[
 selectedModules.includes(module.key) ?'bg-admin-theme-primary/10' :'',
'hover:bg-admin-theme-base transition-colors'
 ]"
 >
 <td class="px-6 py-4 whitespace-nowrap">
 <input
 type="checkbox"
 :value="module.key"
 v-model="selectedModules"
 class="w-4 h-4 text-admin-theme-primary border-admin-theme-border rounded focus:ring-admin-theme-primary bg-admin-theme-input-bg"
 />
 </td>
 <td class="px-6 py-4">
 <div class="flex items-center">
 <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-admin-theme-base rounded-lg">
 <svg class="h-6 w-6 text-admin-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
 </svg>
 </div>
 <div class="ml-4">
 <div class="text-sm font-medium text-admin-theme-text">{{ module.name }}</div>
 <div class="text-sm text-admin-theme-text-muted">{{ module.vendor }}.{{ module.module }}</div>
 <div class="mt-1 flex items-center gap-3">
 <a
 v-if="!module.enabled"
 href="#"
 @click.prevent="toggleModule(module)"
 class="text-sm text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary"
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
 <!-- Dynamic Actions -->
 <template v-if="module.meta?.actions">
 <button
 v-for="(action, index) in module.meta.actions"
 :key="index"
 @click="performAction(action)"
 class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary font-medium"
 >
 {{ getLocaleText(action.label) }}
 </button>
 <span class="text-gray-300 dark:text-gray-600">|</span>
 </template>

 <button
 @click="downloadModule(module)"
 :disabled="downloadLoading === module.key"
 class="text-admin-theme-primary dark:text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary font-medium disabled:opacity-50"
 >
 Download
 </button>
 <span class="text-gray-300 dark:text-gray-600">|</span>
 <button
 @click="deleteModule(module)"
 class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 font-medium"
 >
 Delete
 </button>
 <template v-if="module.has_update">
 <span class="text-gray-300 dark:text-gray-600">|</span>
  <button
    @click="performInAppModuleUpdate(module)"
    :disabled="updatingModule === module.key"
    class="text-purple-400 font-medium hover:underline inline-flex items-center gap-1 disabled:opacity-50"
  >
    <span v-if="updatingModule === module.key" class="w-3 h-3 border-2 border-purple-400 border-t-transparent rounded-full animate-spin"></span>
    <span>{{ updatingModule === module.key ? 'Updating...' : `Update (v${module.latest_version})` }}</span>
  </button>
 </template>
 </div>
 </div>
 </div>
 </td>
 <td class="px-6 py-4">
 <div class="text-sm text-admin-theme-text max-w-md line-clamp-2">
 {{ module.description ||'No description available' }}
 </div>
 <div class="mt-1 flex items-center gap-2">
 <span
 :class="[
'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
 module.enabled
 ?'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
 :'bg-admin-theme-base text-admin-theme-text'
 ]"
 >
 {{ module.enabled ?'Active' :'Inactive' }}
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
 <div class="text-sm font-medium text-admin-theme-text flex items-center gap-1.5">
 <span>{{ module.version }}</span>
 <span v-if="module.has_update" class="px-1.5 py-0.5 text-[10px] font-extrabold rounded-full bg-purple-500/10 text-purple-400 border border-purple-500/30 uppercase">
 v{{ module.latest_version }}
 </span>
 </div>
 <div class="text-sm text-admin-theme-text-muted">By {{ module.vendor }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <div class="flex items-center justify-end gap-2">
 <FormToggle
 :name="`enable-list-${module.key}`"
 :model-value="module.enabled"
 :disabled="toggleLoading === module.key"
 @update:model-value="toggleModule(module)"
 />
 </div>
 </td>
 </tr>
 </tbody>
 </table>
 </div>

 <!-- Pagination -->
 <div v-if="filteredModules.length > pagination.perPage" class="bg-admin-theme-surface px-4 py-3 flex items-center justify-between border-t border-admin-theme-border sm:px-6">
 <div class="flex-1 flex justify-between sm:hidden">
 <button
 @click="pagination.currentPage--"
 :disabled="pagination.currentPage === 1"
 class="relative inline-flex items-center px-4 py-2 border border-admin-theme-border text-sm font-medium rounded-md text-admin-theme-text-secondary bg-admin-theme-input-bg hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
 >
 Previous
 </button>
 <button
 @click="pagination.currentPage++"
 :disabled="pagination.currentPage >= totalPages"
 class="ml-3 relative inline-flex items-center px-4 py-2 border border-admin-theme-border text-sm font-medium rounded-md text-admin-theme-text-secondary bg-admin-theme-input-bg hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
 >
 Next
 </button>
 </div>
 <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
 <div>
 <p class="text-sm text-admin-theme-text-secondary">
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
 <label class="text-sm text-admin-theme-text-secondary mr-2">Show:</label>
 <select
 v-model="pagination.perPage"
 @change="pagination.currentPage = 1"
 class="px-3 py-1 border border-admin-theme-border rounded-md text-sm bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-admin-theme-primary focus:border-admin-theme-primary"
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
 class="relative inline-flex items-center px-2 py-2 border border-admin-theme-border text-sm font-medium rounded-md text-admin-theme-text-secondary bg-admin-theme-input-bg hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
 >
 Previous
 </button>
 <button
 @click="pagination.currentPage++"
 :disabled="pagination.currentPage >= totalPages"
 class="relative inline-flex items-center px-2 py-2 border border-admin-theme-border text-sm font-medium rounded-md text-admin-theme-text-secondary bg-admin-theme-input-bg hover:bg-black/5 dark:hover:bg-white/5 disabled:opacity-50"
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
import { ref, computed, onMounted, watch, markRaw } from'vue';
import axios from'axios';
import { useDialog } from'../../composables/useDialog';
import FormToggle from'../../components/forms/FormToggle.vue';
import FormCheckbox from'../../components/forms/FormCheckbox.vue';
import { Storage } from'../../utils';
import ModuleActionModal from'./ModuleActionModal.vue';

interface ModuleAction {
 type:'modal' |'link';
 label: Record<string, string>;
 modal?: {
 title: Record<string, string>;
 size?:'sm' |'md' |'lg' |'xl' |'full';
 content: Record<string, string>;
 };
 link?: {
 url: string;
 target?:'_blank' |'_self';
 };
}

interface Module {
 key: string;
 name: string;
 vendor: string;
 module: string;
 version: string;
 description: string;
 enabled: boolean;
 has_provider: boolean;
 sandbox?: boolean;
 capabilities?: string[];
 webhooks?: { event: string; url: string }[];
 meta?: {
 actions?: ModuleAction[];
 };
 has_update?: boolean;
 latest_version?: string;
 changelog?: string;
 download_url?: string;
}

const modules = ref<Module[]>([]);
const loading = ref(false);
const checkingUpdates = ref(false);
const updatingModule = ref<string | null>(null);
const toggleLoading = ref<string | null>(null);
const downloadLoading = ref<string | null>(null);
const deleteLoading = ref<string | null>(null);
const uploadLoading = ref(false);
const selectedModules = ref<string[]>([]);
const bulkAction = ref('');
const uploadInput = ref<HTMLInputElement | null>(null);
const currentLanguage = ref('en');

const dialog = useDialog();

const performInAppModuleUpdate = async (module: Module) => {
  const latestVer = module.latest_version || 'new version';
  const confirmed = await dialog.confirm({
    title: 'Confirm Module Update',
    message: `Are you sure you want to update ${module.name} to v${latestVer}?\n\nThis will temporarily switch the site to Maintenance mode, download & extract the update package directly on the server, and clear cache.`,
    confirmText: 'Update Now',
    cancelText: 'Cancel',
    type: 'warning',
  });

  if (!confirmed) return;

  updatingModule.value = module.key;
  try {
    const response = await axios.post('/api/v1/modules/update/execute', { module: module.key });
    if (response.data?.success) {
      dialog.success(response.data.message || `Module ${module.name} updated successfully to v${latestVer}!`);
      await fetchModules();
      await checkModuleUpdates(true);
    } else {
      dialog.error(response.data?.message || 'Module update failed.');
    }
  } catch (error: any) {
    dialog.error(error.response?.data?.message || 'Failed to execute module update.');
  } finally {
    updatingModule.value = null;
  }
};

const checkModuleUpdates = async (silent = false) => {
 checkingUpdates.value = true;
 try {
 const response = await axios.post('/api/v1/modules/check-updates');
 if (response.data?.success) {
 const updateMap = response.data.data || {};
 let updateCount = 0;
 modules.value = modules.value.map(m => {
 const u = updateMap[m.key];
 if (u && u.has_update) {
 updateCount++;
 return { ...m, ...u };
 }
 return m;
 });
 if (!silent) {
 if (updateCount > 0) {
 dialog.success(`Update check complete: ${updateCount} module(s) have new updates available!`);
 } else {
 dialog.info('All modules are up to date.');
 }
 }
 }
 } catch (e) {
 console.error('Failed to check module updates:', e);
 if (!silent) dialog.error('Failed to check module updates.');
 } finally {
 checkingUpdates.value = false;
 }
};

/**
 * Get display text for current language or fallback
 */
const getLocaleText = (textMap: Record<string, string> | undefined): string => {
 if (!textMap) return'';
 return textMap[currentLanguage.value] || textMap['en'] || Object.values(textMap)[0] ||'';
};

/**
 * Perform a dynamic module action
 */
const performAction = (action: ModuleAction) => {
 if (action.type ==='modal' && action.modal) {
 dialog.showModal({
 title: getLocaleText(action.modal.title),
 component: markRaw(ModuleActionModal),
 props: {
 contentMap: action.modal.content,
 language: currentLanguage.value
 },
 size: action.modal.size ||'md'
 });
 } else if (action.type ==='link' && action.link) {
 window.open(action.link.url, action.link.target ||'_blank');
 }
};

// Load view mode from Storage or default to'list'
const viewMode = ref<'grid' |'list'>(Storage.get<'grid' |'list'>('modules_view_mode') ||'list');

// Save view mode to Storage when it changes
watch(viewMode, (newMode) => {
 Storage.set('modules_view_mode', newMode);
});

const filters = ref({
 showActive: Storage.get<boolean>('modules_filter_active', true),
 showInactive: Storage.get<boolean>('modules_filter_inactive', true),
 search:'',
});

// Save filters to Storage when they change
watch(() => filters.value.showActive, (val) => {
 Storage.set('modules_filter_active', val);
});

watch(() => filters.value.showInactive, (val) => {
 Storage.set('modules_filter_inactive', val);
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
 await checkModuleUpdates(true);
 } catch (error) {
 console.error('Error loading modules:', error);
 dialog.error('Failed to load modules');
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

 await performUpload(file, false, input);
};

const performUpload = async (file: File, overwrite: boolean, input: HTMLInputElement) => {
 uploadLoading.value = true;

 try {
 const formData = new FormData();
 formData.append('module', file);
 if (overwrite) {
 formData.append('overwrite','1');
 }

 await axios.post('/api/v1/modules/upload', formData, {
 headers: {
'Content-Type':'multipart/form-data',
 },
 });

 dialog.success('Module uploaded and activated successfully');

 await loadModules();

 setTimeout(() => {
 window.location.reload();
 }, 300);
 } catch (error: any) {
 console.error('Error uploading module:', error);
 
 if (error.response?.data?.error_code ==='MODULE_ALREADY_EXISTS') {
 const confirmed = await dialog.confirm({
 title:'Module Already Exists',
 message: error.response.data.message,
 confirmText:'Overwrite',
 cancelText:'Cancel',
 type:'warning'
 });
 
 if (confirmed) {
 return performUpload(file, true, input);
 }
 } else {
 const message = error.response?.data?.message ||'Failed to upload module';
 dialog.error(message);
 }
 } finally {
 if (!overwrite) { // Only reset loading state if we are not recursing
 uploadLoading.value = false;
 }

 if (input && !overwrite) {
 input.value ='';
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
 { responseType:'blob' }
 );

 const blob = new Blob([response.data], { type:'application/zip' });
 let filename = `${module.module}-${module.version ||'module'}.zip`;

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
 const message = error.response?.data?.message ||'Failed to download module';
 dialog.error(message);
 } finally {
 downloadLoading.value = null;
 }
};

const toggleModule = async (module: Module) => {
 if (toggleLoading.value === module.key) return;

 const moduleIndex = modules.value.findIndex(m => m.key === module.key);
 if (moduleIndex === -1) return;

 const currentEnabledState = modules.value[moduleIndex].enabled;
 const targetState = !currentEnabledState;

 if (targetState === true && module.sandbox) {
 let capsList ='<p class="text-sm text-admin-theme-text-secondary mb-2">This is a Sandboxed Application. It runs externally and requests the following API scopes:</p><ul class="list-disc pl-5 mb-4 text-sm font-semibold text-gray-800 ">';
 if (module.capabilities && module.capabilities.length > 0) {
 module.capabilities.forEach((c: string) => capsList += `<li>${c}</li>`);
 } else {
 capsList += `<li>No elevated capabilities requested</li>`;
 }
 capsList +='</ul>';

 if (module.webhooks && module.webhooks.length > 0) {
 capsList +='<p class="text-sm text-admin-theme-text-secondary mt-4 mb-2">It will also register the following Webhooks:</p><ul class="list-disc pl-5 mb-4 text-sm text-gray-800 ">';
 module.webhooks.forEach((w: {event: string; url: string}) => capsList += `<li><code>${w.event}</code> ➔ <code>${w.url}</code></li>`);
 capsList +='</ul>';
 }

 const confirmed = await dialog.confirm({
 title: `Authorize ${module.name}`,
 message: capsList +'<p class="text-sm font-semibold mt-4 text-red-600">Do you want to generate an App Token and grant these permissions?</p>',
 confirmText:'Authorize & Enable',
 cancelText:'Cancel',
 type:'warning',
 });

 if (!confirmed) return;
 }

 toggleLoading.value = module.key;

 try {
 const endpoint = currentEnabledState
 ? `/api/v1/modules/${encodeURIComponent(module.key)}/disable`
 : `/api/v1/modules/${encodeURIComponent(module.key)}/enable`;

 const response = await axios.post(endpoint);

 modules.value[moduleIndex].enabled = targetState;

 await new Promise(resolve => setTimeout(resolve, 200));
 await loadModules();

 const reloadedModule = modules.value.find(m => m.key === module.key);
 if (reloadedModule && reloadedModule.enabled !== targetState) {
 window.location.reload();
 return;
 }

 setTimeout(() => { window.location.reload(); }, 200);
 } catch (error: any) {
 modules.value[moduleIndex].enabled = currentEnabledState;
 dialog.error(error.response?.data?.message ||'Failed to toggle module');
 await loadModules();
 } finally {
 toggleLoading.value = null;
 }
};

const deleteModule = async (module: Module) => {
 if (deleteLoading.value) return;

 const confirmed = await dialog.confirm({
 title:'Delete Module',
 message: `Are you sure you want to delete"${module.name}"?\n\nThis will permanently delete all module files. This action cannot be undone.`,
 confirmText:'Delete',
 cancelText:'Cancel',
 type:'danger',
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
 const message = error.response?.data?.message ||'Failed to delete module';
 dialog.error(message);
 await loadModules();
 } finally {
 deleteLoading.value = null;
 }
};

const openDocumentation = (module: Module) => {
 // This is now handled by performAction generics
};

const applyBulkAction = async () => {
 if (!bulkAction.value || selectedModules.value.length === 0) return;

 const action = bulkAction.value;
 const count = selectedModules.value.length;

 let confirmed = false;
 if (action ==='delete') {
 confirmed = await dialog.confirm({
 title:'Delete Modules',
 message: `Are you sure you want to delete ${count} module(s)?\n\nThis will permanently delete all module files. This action cannot be undone.`,
 confirmText:'Delete',
 cancelText:'Cancel',
 type:'danger',
 });
 } else {
 confirmed = await dialog.confirm({
 title: `${action.charAt(0).toUpperCase() + action.slice(1)} Modules`,
 message: `Are you sure you want to ${action} ${count} module(s)?`,
 confirmText: action.charAt(0).toUpperCase() + action.slice(1),
 cancelText:'Cancel',
 type:'warning',
 });
 }

 if (!confirmed) {
 return;
 }

 try {
 const promises = selectedModules.value.map(async (key) => {
 if (action ==='enable') {
 await axios.post(`/api/v1/modules/${encodeURIComponent(key)}/enable`);
 } else if (action ==='disable') {
 await axios.post(`/api/v1/modules/${encodeURIComponent(key)}/disable`);
 } else if (action ==='delete') {
 await axios.delete(`/api/v1/modules/${encodeURIComponent(key)}`);
 }
 });

 await Promise.all(promises);

 // Reload page after successful bulk action to refresh sidebar menu
 if (action ==='enable' || action ==='disable') {
 window.location.reload();
 } else {
 // For delete, just reload modules list
 await loadModules();
 selectedModules.value = [];
 bulkAction.value ='';
 dialog.success(`${count} module(s) ${action}d successfully`);
 }
 } catch (error: any) {
 console.error('Error applying bulk action:', error);
 const message = error.response?.data?.message || `Failed to ${action} modules`;
 dialog.error(message);
 await loadModules();
 }
};

onMounted(async () => {
 loadModules();
 try {
 const langResponse = await axios.get('/api/v1/settings/site_language');
 if (langResponse.data?.data?.value) {
 currentLanguage.value = langResponse.data.data.value;
 }
 } catch (e) {
 console.error('Failed to load site language', e);
 }
});
</script>
