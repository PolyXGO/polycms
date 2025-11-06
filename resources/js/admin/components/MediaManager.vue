<template>
    <div class="media-manager bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden">
        <!-- Breadcrumb Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-2 bg-gray-50 dark:bg-gray-800">
            <div class="flex items-center gap-2 flex-wrap">
                <button
                    @click="navigateToFolder('')"
                    class="px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                    :class="currentPath === '' ? 'font-semibold text-gray-900 dark:text-white' : ''"
                >
                    Media Library
                </button>
                <template v-for="(part, index) in pathParts" :key="index">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <button
                        @click="navigateToFolder(getPathUpTo(index))"
                        class="px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                        :class="currentPath === getPathUpTo(index) ? 'font-semibold text-gray-900 dark:text-white' : ''"
                    >
                        {{ part }}
                    </button>
                </template>
            </div>
        </div>

        <!-- Top Toolbar -->
        <div class="border-b border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <!-- Left Actions -->
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <button
                            @click="showUploadDropdown = !showUploadDropdown"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Upload
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-if="showUploadDropdown" class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-10 border border-gray-200 dark:border-gray-700">
                            <button
                                @click="openFileUpload"
                                class="w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-700 dark:text-gray-300"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Upload from local
                            </button>
                            <button
                                @click="showUrlUpload = true"
                                class="w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-700 dark:text-gray-300 border-t border-gray-200 dark:border-gray-700"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                Upload from URL
                            </button>
                        </div>
                    </div>
                    <button
                        @click="refreshMedia"
                        class="p-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                        title="Refresh"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    <button
                        @click="showCreateFolderDialog = true"
                        class="p-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                        title="Create New Folder"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </button>
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-2 flex-1">
                    <div class="relative">
                        <button
                            @click="showTypeFilter = !showTypeFilter"
                            class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors flex items-center gap-2"
                        >
                            <span>{{ typeFilterLabel }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-if="showTypeFilter" class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-10 border border-gray-200 dark:border-gray-700">
                            <button
                                v-for="type in typeFilters"
                                :key="type.value"
                                @click="setTypeFilter(type.value)"
                                :class="[
                                    'w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2',
                                    filters.type === type.value ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300'
                                ]"
                            >
                                <svg v-if="type.value === 'image'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <svg v-else-if="type.value === 'video'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <svg v-else-if="type.value === 'document'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                {{ type.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <input
                                v-model="filters.search"
                                @input="debouncedSearch"
                                type="text"
                                placeholder="Search in current folder"
                                class="w-full px-4 py-2 pl-10 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                            />
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-2">
                    <button
                        @click="viewMode = 'grid'"
                        :class="[
                            'p-2 rounded-lg transition-colors',
                            viewMode === 'grid' ? 'bg-indigo-100 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
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
                            'p-2 rounded-lg transition-colors',
                            viewMode === 'list' ? 'bg-indigo-100 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
                        ]"
                        title="List View"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col h-[600px]">
            <div class="flex flex-1 overflow-hidden">
                <!-- Media Grid/List -->
                <div class="flex-1 overflow-y-auto p-4">
                <div v-if="loading" class="flex items-center justify-center h-full">
                    <div class="text-gray-500 dark:text-gray-400">Loading...</div>
                </div>
                <div v-else-if="mediaItems.length === 0 && folders.length === 0 && !currentPath" class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                    <svg class="w-24 h-24 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p>No media found</p>
                </div>
                <div v-else :class="[
                    'grid gap-4',
                    viewMode === 'grid' ? 'grid-cols-6' : 'grid-cols-1'
                ]">
                    <!-- Back Button (only show if not at root) -->
                    <div
                        v-if="currentPath && viewMode === 'grid'"
                        @click="goBack"
                        class="relative cursor-pointer rounded-lg overflow-hidden border-2 transition-all border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600"
                        title="Back to previous folder"
                    >
                        <div class="aspect-square bg-gray-100 dark:bg-gray-800 flex items-center justify-center relative group">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <div class="absolute bottom-2 left-0 right-0 text-center text-xs text-gray-600 dark:text-gray-400">...</div>
                        </div>
                    </div>
                    <!-- Show "No media found" message in grid if in folder but no items -->
                    <div
                        v-if="currentPath && mediaItems.length === 0 && folders.length === 0 && viewMode === 'grid'"
                        class="col-span-5 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                    >
                        <svg class="w-24 h-24 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p>No media found</p>
                    </div>
                    <!-- Folders -->
                    <div
                        v-for="folder in folders"
                        :key="`folder-${folder}`"
                        @click="navigateToFolder(currentPath ? `${currentPath}/${folder}` : folder)"
                        :class="[
                            'relative cursor-pointer rounded-lg overflow-hidden border-2 transition-all border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
                            viewMode === 'list' ? 'flex items-center gap-4 p-4' : ''
                        ]"
                    >
                        <div v-if="viewMode === 'grid'" class="bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden flex flex-col relative group">
                            <div class="aspect-square flex items-center justify-center relative">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <!-- Action buttons on hover -->
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-opacity flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                                    <button
                                        @click.stop="editFolderName(folder)"
                                        class="p-2 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition-colors z-10"
                                        title="Rename folder"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="deleteFolder(folder)"
                                        class="p-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors z-10"
                                        title="Delete folder"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="px-2 py-1.5 text-center min-h-[2.5rem] flex items-center justify-center">
                                <span class="text-xs text-gray-700 dark:text-gray-300 font-medium truncate w-full" :title="folder">{{ folder }}</span>
                            </div>
                        </div>
                        <div v-else class="flex items-center gap-4 flex-1">
                            <div class="flex-shrink-0 w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded flex items-center justify-center">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">{{ folder }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Folder</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Media Items -->
                    <div
                        v-for="item in visibleMediaItems"
                        :key="item.id"
                        @click="selectMedia(item)"
                        @dblclick="doubleClickMedia(item)"
                        :class="[
                            'relative cursor-pointer rounded-lg overflow-hidden border-2 transition-all group',
                            isSelected(item.id) ? 'border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
                            viewMode === 'list' ? 'flex items-center gap-3 px-3' : ''
                        ]"
                    >
                        <div v-if="viewMode === 'grid'" class="aspect-square bg-gray-100 dark:bg-gray-800 flex items-center justify-center relative group">
                            <img v-if="item.type === 'image'" :src="getMediaUrlSync(item)" :alt="item.name" class="w-full h-full object-cover" @error="handleImageError" @load="loadPrivateMedia(item)" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div v-if="isSelected(item.id)" class="absolute top-2 right-2 bg-indigo-600 text-white rounded-full p-1 z-10">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <button
                                @click.stop="deleteMedia(item)"
                                class="absolute top-2 left-2 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity z-10 hover:bg-red-700"
                                title="Delete"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-opacity flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <span class="text-white text-sm font-medium">{{ item.name }}</span>
                            </div>
                        </div>
                        <div v-else class="flex items-center gap-3 flex-1 py-2">
                            <img v-if="item.type === 'image'" :src="getMediaUrlSync(item)" :alt="item.name" class="w-20 h-20 object-cover rounded flex-shrink-0" @error="handleImageError" @load="loadPrivateMedia(item)" />
                            <div v-else class="w-20 h-20 flex-shrink-0 bg-gray-100 dark:bg-gray-800 rounded flex items-center justify-center">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 dark:text-white truncate mb-1">{{ item.name }}</div>
                                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                        </svg>
                                        {{ formatSize(item.size) }}
                                    </span>
                                    <span v-if="item.width && item.height" class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                        </svg>
                                        {{ item.width }} × {{ item.height }}px
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ formatDate(item.created_at) }}
                                    </span>
                                    <span v-if="item.metadata && item.metadata.format" class="flex items-center gap-1">
                                        <span class="uppercase text-[10px] font-semibold">{{ item.metadata.format.split('/')[1] || item.type }}</span>
                                    </span>
                                </div>
                                <div v-if="item.alt_text || item.caption" class="mt-1 text-xs text-gray-600 dark:text-gray-500 truncate">
                                    <span v-if="item.alt_text">{{ item.alt_text }}</span>
                                    <span v-else-if="item.caption">{{ item.caption }}</span>
                                </div>
                            </div>
                            <button
                                @click.stop="deleteMedia(item)"
                                class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                                title="Delete"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Lazy Loading Trigger -->
                    <div
                        v-if="hasMoreItems && viewMode === 'list'"
                        ref="observerTarget"
                        class="col-span-1 flex items-center justify-center py-4"
                    >
                        <div v-if="isLoadingMore" class="text-gray-500 dark:text-gray-400 text-sm">Loading more...</div>
                    </div>
                </div>
                </div>

                <!-- Right Sidebar (Details) -->
                <div class="w-80 border-l border-gray-200 dark:border-gray-700 p-4 overflow-y-auto flex-shrink-0">
                    <div v-if="selectedMedia.length > 0">
                    <div v-for="item in selectedMedia" :key="item.id" class="mb-6">
                        <img v-if="item.type === 'image'" :src="getMediaUrlSync(item)" :alt="item.name" class="w-full rounded-lg mb-4" @error="handleImageError" @load="loadPrivateMedia(item)" />
                        <div class="space-y-2 text-sm">
                            <div>
                                <label class="font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <div class="text-gray-900 dark:text-white">{{ item.name }}</div>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 dark:text-gray-300">URL</label>
                                <div class="flex items-center gap-2">
                                    <input :value="item.url" readonly class="flex-1 px-2 py-1 text-xs bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white rounded border border-gray-300 dark:border-gray-600" />
                                    <button @click="copyToClipboard(item.url)" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div v-if="item.width && item.height">
                                <label class="font-medium text-gray-700 dark:text-gray-300">Dimensions</label>
                                <div class="text-gray-900 dark:text-white">{{ item.width }} × {{ item.height }} px</div>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 dark:text-gray-300">Size</label>
                                <div class="text-gray-900 dark:text-white">{{ formatSize(item.size) }}</div>
                            </div>
                            <div v-if="item.metadata && Object.keys(item.metadata).length > 0">
                                <label class="font-medium text-gray-700 dark:text-gray-300">Metadata</label>
                                <div class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-2 rounded max-h-32 overflow-y-auto">
                                    <pre class="whitespace-pre-wrap">{{ JSON.stringify(item.metadata, null, 2) }}</pre>
                                </div>
                            </div>
                            <div v-if="item.alt_text">
                                <label class="font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    Alt Text
                                    <button @click="removeMeta(item, 'alt_text')" class="p-1 hover:bg-red-100 dark:hover:bg-red-900/20 rounded text-red-600 dark:text-red-400" title="Remove">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </label>
                                <div class="text-gray-900 dark:text-white">{{ item.alt_text }}</div>
                            </div>
                            <div v-if="item.caption">
                                <label class="font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    Caption
                                    <button @click="removeMeta(item, 'caption')" class="p-1 hover:bg-red-100 dark:hover:bg-red-900/20 rounded text-red-600 dark:text-red-400" title="Remove">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </label>
                                <div class="text-gray-900 dark:text-white">{{ item.caption }}</div>
                            </div>
                            <div v-if="item.description">
                                <label class="font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    Description
                                    <button @click="removeMeta(item, 'description')" class="p-1 hover:bg-red-100 dark:hover:bg-red-900/20 rounded text-red-600 dark:text-red-400" title="Remove">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </label>
                                <div class="text-gray-900 dark:text-white">{{ item.description }}</div>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 dark:text-gray-300">Uploaded at</label>
                                <div class="text-gray-900 dark:text-white">{{ formatDate(item.created_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                        <p class="text-sm">Select an item to view details</p>
                    </div>
                </div>
            </div>

            <!-- Footer Status Bar -->
            <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2 flex-shrink-0">
                <div class="flex items-center justify-between gap-4 text-xs text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            <span>{{ folderStats.totalFolders }} {{ folderStats.totalFolders === 1 ? 'folder' : 'folders' }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ folderStats.totalFiles }} {{ folderStats.totalFiles === 1 ? 'file' : 'files' }}</span>
                        </span>
                        <span v-if="folderStats.totalSize > 0" class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                            </svg>
                            <span>Total: {{ formatSize(folderStats.totalSize) }}</span>
                        </span>
                        <span v-if="folderStats.imageCount > 0" class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ folderStats.imageCount }} {{ folderStats.imageCount === 1 ? 'image' : 'images' }}</span>
                        </span>
                        <span v-if="folderStats.videoCount > 0" class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span>{{ folderStats.videoCount }} {{ folderStats.videoCount === 1 ? 'video' : 'videos' }}</span>
                        </span>
                    </div>
                    <div v-if="currentPath" class="text-gray-500 dark:text-gray-400">
                        Path: <span class="font-mono">{{ currentPath }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload URL Modal -->
        <div v-if="showUrlUpload" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showUrlUpload = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload from URL</h3>
                    <button @click="showUrlUpload = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <input
                    v-model="urlToUpload"
                    type="url"
                    placeholder="Enter image URL"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white mb-4"
                />
                <div class="flex justify-end gap-2">
                    <button @click="showUrlUpload = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
                    <button @click="uploadFromUrl" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Upload</button>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showLightbox"
                    class="fixed inset-0 z-[9999] bg-black/90 flex items-center justify-center"
                    @click.self="closeLightbox"
                >
                    <!-- Close Button -->
                    <button
                        @click="closeLightbox"
                        class="absolute top-16 right-4 text-white hover:text-gray-300 transition-colors z-10"
                        title="Close (Esc)"
                    >
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Previous Button -->
                    <button
                        v-if="lightboxImages.length > 1"
                        @click="previousImage"
                        class="absolute left-4 text-white hover:text-gray-300 transition-colors z-10 bg-black/50 hover:bg-black/70 rounded-full p-3"
                        title="Previous (←)"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Next Button -->
                    <button
                        v-if="lightboxImages.length > 1"
                        @click="nextImage"
                        class="absolute right-4 text-white hover:text-gray-300 transition-colors z-10 bg-black/50 hover:bg-black/70 rounded-full p-3"
                        title="Next (→)"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Image Container -->
                    <div class="max-w-[90vw] max-h-[80vh] flex items-center justify-center">
                        <img
                            v-if="currentLightboxImage"
                            :src="getMediaUrlSync(currentLightboxImage)"
                            :alt="currentLightboxImage.name"
                            class="max-h-[80vh] max-w-full object-contain"
                            @error="handleImageError"
                            @load="loadPrivateMedia(currentLightboxImage)"
                        />
                    </div>

                    <!-- Image Info -->
                    <div v-if="currentLightboxImage && lightboxImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white text-sm bg-black/50 px-4 py-2 rounded">
                        {{ currentLightboxIndex + 1 }} / {{ lightboxImages.length }}
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Hidden File Input -->
        <input
            ref="fileInput"
            type="file"
            multiple
            accept="image/*,video/*,application/pdf"
            @change="handleFileUpload"
            class="hidden"
        />

        <!-- Create Folder Dialog -->
        <div v-if="showCreateFolderDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showCreateFolderDialog = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Create New Folder</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Folder Name</label>
                        <input
                            v-model="newFolderName"
                            type="text"
                            placeholder="Enter folder name"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500"
                            @keyup.enter="createFolder"
                        />
                        <p v-if="currentPath" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Path: {{ currentPath }}/{{ newFolderName || '...' }}
                        </p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            @click="showCreateFolderDialog = false; newFolderName = ''"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                        >
                            Cancel
                        </button>
                        <button
                            @click="createFolder"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                        >
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Folder Dialog -->
        <div v-if="showEditFolderDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showEditFolderDialog = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Rename Folder</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Folder Name</label>
                        <input
                            v-model="editingFolderNewName"
                            type="text"
                            placeholder="Enter folder name"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500"
                            @keyup.enter="saveFolderName"
                        />
                        <p v-if="currentPath" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Path: {{ currentPath }}/{{ editingFolderNewName || '...' }}
                        </p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            @click="showEditFolderDialog = false; editingFolder = ''; editingFolderNewName = ''"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                        >
                            Cancel
                        </button>
                        <button
                            @click="saveFolderName"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import axios from 'axios';
import { useDialog } from '../composables/useDialog';

// Initialize dialog early
const dialog = useDialog();

interface MediaItem {
    id: number;
    name: string;
    file_name: string;
    url: string;
    type: string;
    size: number;
    created_at: string;
    width?: number;
    height?: number;
    disk?: string;
    path?: string;
    metadata?: any;
    alt_text?: string;
    caption?: string;
    description?: string;
}

const props = defineProps<{
    multiple?: boolean;
    acceptedTypes?: string[];
}>();

const emit = defineEmits<{
    (e: 'select', media: MediaItem | MediaItem[]): void;
}>();

const loading = ref(false);
const mediaItems = ref<MediaItem[]>([]);
const folders = ref<string[]>([]);
const selectedMedia = ref<MediaItem[]>([]);
const viewMode = ref<'grid' | 'list'>('grid');
const showUploadDropdown = ref(false);
const showUrlUpload = ref(false);
const urlToUpload = ref('');
const showTypeFilter = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const currentPath = ref<string>('');
const showCreateFolderDialog = ref(false);
const newFolderName = ref('');
const firstMediaItemRef = ref<HTMLElement | null>(null);
const showLightbox = ref(false);
const currentLightboxIndex = ref(0);
const showEditFolderDialog = ref(false);
const editingFolder = ref<string>('');
const editingFolderNewName = ref('');

// Lazy loading
const visibleItemCount = ref(20);
const observerTarget = ref<HTMLElement | null>(null);
const isLoadingMore = ref(false);

const filters = ref({
    type: '',
    search: '',
});

const typeFilters = [
    { value: '', label: 'Everything' },
    { value: 'image', label: 'Image' },
    { value: 'video', label: 'Video' },
    { value: 'document', label: 'Document' },
];

const typeFilterLabel = computed(() => {
    const filter = typeFilters.find(f => f.value === filters.value.type);
    return filter ? filter.label : 'Everything';
});

// Visible items for lazy loading (only for list view)
const visibleMediaItems = computed(() => {
    if (viewMode.value === 'list') {
        return mediaItems.value.slice(0, visibleItemCount.value);
    }
    return mediaItems.value;
});

const hasMoreItems = computed(() => {
    if (viewMode.value !== 'list') return false;
    return visibleItemCount.value < mediaItems.value.length;
});

// Get only images from current folder (for lightbox)
const lightboxImages = computed(() => {
    return mediaItems.value.filter(item => item.type === 'image');
});

const currentLightboxImage = computed(() => {
    if (lightboxImages.value.length === 0) return null;
    return lightboxImages.value[currentLightboxIndex.value] || null;
});

// Folder statistics
const folderStats = computed(() => {
    const totalFiles = mediaItems.value.length;
    const totalFolders = folders.value.length;
    const totalSize = mediaItems.value.reduce((sum, item) => sum + (item.size || 0), 0);
    const imageCount = mediaItems.value.filter(item => item.type === 'image').length;
    const videoCount = mediaItems.value.filter(item => item.type === 'video').length;
    const documentCount = mediaItems.value.filter(item => item.type === 'document').length;
    
    return {
        totalFiles,
        totalFolders,
        totalSize,
        imageCount,
        videoCount,
        documentCount,
    };
});

// Path navigation
const pathParts = computed(() => {
    if (!currentPath.value) return [];
    return currentPath.value.split('/').filter(p => p);
});

const getPathUpTo = (index: number): string => {
    return pathParts.value.slice(0, index + 1).join('/');
};

let searchTimeout: ReturnType<typeof setTimeout>;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadMedia();
    }, 300);
};

const loadMedia = async () => {
    loading.value = true;
    try {
        const params: any = {
            per_page: 50,
        };
        if (filters.value.type) params.type = filters.value.type;
        if (filters.value.search) params.search = filters.value.search;
        if (currentPath.value) params.path = currentPath.value;

        const response = await axios.get('/api/v1/media', { params });
        const items = response.data.data || [];
        // Folders might be in response.data.data.folders or response.data.folders
        const foldersData = response.data.folders || response.data.data?.folders || [];
        
        console.log('Media response:', {
            items: items.length,
            folders: foldersData,
            currentPath: currentPath.value,
            response: response.data
        });
        
        // Fix URLs that use localhost to use current origin
        // Preload private media as blob URLs
        mediaItems.value = await Promise.all(
            items.map(async (item: MediaItem) => {
                if (item.url && item.url.includes('localhost') && !item.url.includes(window.location.origin)) {
                    item.url = item.url.replace(/https?:\/\/localhost[^/]*/, window.location.origin);
                }
                
                // Preload private media as blob URLs
                if (item.disk && (item.disk === 'local' || item.disk === 'private')) {
                    try {
                        await getMediaUrl(item);
                    } catch (error) {
                        console.error('Error preloading private media:', error);
                    }
                }
                
                return item;
            })
        );
        
        folders.value = foldersData || [];
        console.log('Updated folders:', folders.value);
        
        // Reset visible item count when loading new media
        visibleItemCount.value = 20;
        
        // Auto-select first media item if available and none selected
        if (mediaItems.value.length > 0 && selectedMedia.value.length === 0) {
            // Use setTimeout to ensure DOM is updated
            setTimeout(() => {
                selectMedia(mediaItems.value[0]);
            }, 100);
        }
        
        // Setup intersection observer after DOM update
        nextTick(() => {
            setupIntersectionObserver();
        });
    } catch (error) {
        console.error('Error loading media:', error);
    } finally {
        loading.value = false;
    }
};

const navigateToFolder = (path: string) => {
    currentPath.value = path;
    // Save to localStorage
    localStorage.setItem('mediaManager.currentPath', path);
    loadMedia();
};

const goBack = () => {
    if (!currentPath.value) return;
    const pathParts = currentPath.value.split('/').filter(p => p);
    if (pathParts.length > 0) {
        pathParts.pop();
        navigateToFolder(pathParts.join('/'));
    } else {
        navigateToFolder('');
    }
};

const createFolder = async () => {
    if (!newFolderName.value.trim()) {
        dialog.error('Folder name is required');
        return;
    }
    
    try {
        const fullPath = currentPath.value 
            ? `${currentPath.value}/${newFolderName.value.trim()}`
            : newFolderName.value.trim();
        
        await axios.post('/api/v1/media/folders', {
            path: fullPath,
        });
        
        dialog.success('Folder created successfully');
        showCreateFolderDialog.value = false;
        newFolderName.value = '';
        await loadMedia();
    } catch (error: any) {
        console.error('Error creating folder:', error);
        const message = error.response?.data?.error?.message || error.response?.data?.message || 'Failed to create folder';
        dialog.error(message);
    }
};

const editFolderName = (folderName: string) => {
    editingFolder.value = folderName;
    editingFolderNewName.value = folderName;
    showEditFolderDialog.value = true;
};

const saveFolderName = async () => {
    if (!editingFolderNewName.value.trim()) {
        dialog.error('Folder name is required');
        return;
    }
    
    if (editingFolderNewName.value.trim() === editingFolder.value) {
        showEditFolderDialog.value = false;
        return;
    }
    
    try {
        const oldPath = currentPath.value 
            ? `${currentPath.value}/${editingFolder.value}`
            : editingFolder.value;
        
        const newPath = currentPath.value 
            ? `${currentPath.value}/${editingFolderNewName.value.trim()}`
            : editingFolderNewName.value.trim();
        
        await axios.put('/api/v1/media/folders/rename', {
            old_path: oldPath,
            new_path: newPath,
        });
        
        dialog.success('Folder renamed successfully');
        showEditFolderDialog.value = false;
        editingFolder.value = '';
        editingFolderNewName.value = '';
        await loadMedia();
    } catch (error: any) {
        console.error('Error renaming folder:', error);
        const message = error.response?.data?.error?.message || error.response?.data?.message || 'Failed to rename folder';
        dialog.error(message);
    }
};

const deleteFolder = async (folderName: string) => {
    const confirmed = await dialog.confirm({
        title: 'Delete Folder',
        message: `Are you sure you want to delete the folder "${folderName}"? This will also delete all files and subfolders inside it. This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });
    
    if (!confirmed) {
        return;
    }
    
    try {
        const folderPath = currentPath.value 
            ? `${currentPath.value}/${folderName}`
            : folderName;
        
        await axios.delete('/api/v1/media/folders', {
            data: { path: folderPath },
        });
        
        dialog.success('Folder deleted successfully');
        await loadMedia();
    } catch (error: any) {
        console.error('Error deleting folder:', error);
        const message = error.response?.data?.error?.message || error.response?.data?.message || 'Failed to delete folder';
        dialog.error(message);
    }
};

const refreshMedia = () => {
    // Restore currentPath from localStorage before refreshing
    const savedPath = localStorage.getItem('mediaManager.currentPath');
    if (savedPath !== null && currentPath.value !== savedPath) {
        currentPath.value = savedPath;
    }
    loadMedia();
};

const removeMeta = async (item: MediaItem, field: 'alt_text' | 'caption' | 'description') => {
    try {
        await axios.put(`/api/v1/media/${item.id}`, {
            [field]: null
        });
        
        // Update local item
        item[field] = null;
        
        // Update in selectedMedia
        const selectedIndex = selectedMedia.value.findIndex(m => m.id === item.id);
        if (selectedIndex !== -1) {
            selectedMedia.value[selectedIndex][field] = null;
        }
        
        dialog.success('Meta field removed successfully');
    } catch (error: any) {
        console.error('Error removing meta:', error);
        const message = error.response?.data?.error?.message || error.response?.data?.message || 'Failed to remove meta field';
        dialog.error(message);
    }
};

const selectMedia = (item: MediaItem) => {
    if (props.multiple) {
        const index = selectedMedia.value.findIndex(m => m.id === item.id);
        if (index >= 0) {
            selectedMedia.value.splice(index, 1);
        } else {
            selectedMedia.value.push(item);
        }
    } else {
        selectedMedia.value = [item];
    }
    emitSelect();
};

const doubleClickMedia = (item: MediaItem) => {
    // Only open lightbox for images
    if (item.type === 'image') {
        openLightbox(item);
    } else if (!props.multiple) {
        emit('select', item);
    }
};

const openLightbox = (item: MediaItem) => {
    const imageIndex = lightboxImages.value.findIndex(img => img.id === item.id);
    if (imageIndex !== -1) {
        currentLightboxIndex.value = imageIndex;
        showLightbox.value = true;
        // Prevent body scroll when lightbox is open
        document.body.style.overflow = 'hidden';
    }
};

const closeLightbox = () => {
    showLightbox.value = false;
    document.body.style.overflow = '';
};

const nextImage = () => {
    if (lightboxImages.value.length === 0) return;
    currentLightboxIndex.value = (currentLightboxIndex.value + 1) % lightboxImages.value.length;
};

const previousImage = () => {
    if (lightboxImages.value.length === 0) return;
    currentLightboxIndex.value = currentLightboxIndex.value === 0 
        ? lightboxImages.value.length - 1 
        : currentLightboxIndex.value - 1;
};

// Keyboard navigation for lightbox
const handleLightboxKeydown = (event: KeyboardEvent) => {
    if (!showLightbox.value) return;

    switch (event.key) {
        case 'Escape':
            closeLightbox();
            break;
        case 'ArrowLeft':
            event.preventDefault();
            previousImage();
            break;
        case 'ArrowRight':
            event.preventDefault();
            nextImage();
            break;
    }
};

const isSelected = (id: number) => {
    return selectedMedia.value.some(m => m.id === id);
};

const emitSelect = () => {
    if (props.multiple) {
        emit('select', selectedMedia.value);
    } else if (selectedMedia.value.length > 0) {
        emit('select', selectedMedia.value[0]);
    }
};

const openFileUpload = () => {
    showUploadDropdown.value = false;
    fileInput.value?.click();
};

const handleFileUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (!files || files.length === 0) return;

    // Store files array before resetting input (to avoid race condition)
    const filesArray = Array.from(files);
    
    // Reset file input immediately to allow selecting the same file again
    if (fileInput.value) {
        fileInput.value.value = '';
    }

    loading.value = true;
    const uploadErrors: string[] = [];
    const uploadSuccess: string[] = [];
    
    try {
        for (const file of filesArray) {
            // Verify file is still valid
            if (!file || file.size === 0) {
                console.warn('Skipping invalid file:', file?.name);
                continue;
            }

            const formData = new FormData();
            formData.append('file', file);

            // Add path to FormData
            if (currentPath.value) {
                formData.append('path', currentPath.value);
            }
            
            // Debug: Verify FormData
            console.log('Uploading file:', file.name, file.size, file.type);
            console.log('FormData has file:', formData.has('file'));
            console.log('FormData has path:', formData.has('path'), currentPath.value);

            try {
                // Use XMLHttpRequest directly to ensure FormData is sent correctly
                await new Promise<void>((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    const uploadUrl = '/api/v1/media/upload';
                    xhr.open('POST', uploadUrl);
                    
                    // Set Authorization header if token exists
                    const token = localStorage.getItem('auth_token');
                    if (token) {
                        xhr.setRequestHeader('Authorization', `Bearer ${token}`);
                    }
                    xhr.setRequestHeader('Accept', 'application/json');
                    // Don't set Content-Type - browser will set it with boundary automatically
                    
                    // Track upload progress
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            console.log(`Upload progress for ${file.name}: ${percentComplete.toFixed(2)}%`);
                        }
                    };
                    
                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                // Fix URL in response if needed
                                if (response.data?.url && response.data.url.includes('localhost')) {
                                    response.data.url = response.data.url.replace(/https?:\/\/localhost[^/]*/, window.location.origin);
                                }
                                uploadSuccess.push(file.name);
                                resolve();
                            } catch (e) {
                                console.error('Error parsing response:', e);
                                resolve(); // Still resolve if can't parse response
                            }
                        } else {
                            try {
                                const error = JSON.parse(xhr.responseText);
                                const errorMsg = error.error?.message || error.message || `Upload failed with status ${xhr.status}`;
                                uploadErrors.push(`${file.name}: ${errorMsg}`);
                                reject(new Error(errorMsg));
                            } catch {
                                const errorMsg = `Upload failed with status ${xhr.status}`;
                                uploadErrors.push(`${file.name}: ${errorMsg}`);
                                reject(new Error(errorMsg));
                            }
                        }
                    };
                    
                    xhr.onerror = () => {
                        const errorMsg = 'Network error';
                        uploadErrors.push(`${file.name}: ${errorMsg}`);
                        reject(new Error(errorMsg));
                    };
                    
                    xhr.ontimeout = () => {
                        const errorMsg = 'Upload timeout';
                        uploadErrors.push(`${file.name}: ${errorMsg}`);
                        reject(new Error(errorMsg));
                    };
                    
                    // Set timeout to 5 minutes for large files
                    xhr.timeout = 300000;
                    
                    // Send FormData
                    xhr.send(formData);
                });
            } catch (error: any) {
                const errorMessage = error.message || 'Failed to upload file';
                uploadErrors.push(`${file.name}: ${errorMessage}`);
                console.error('Error uploading file:', error);
            }
        }
        
        // Reload media after all uploads complete
        await loadMedia();
        
        // Auto-select newly uploaded item (last in list) after loading
        if (mediaItems.value.length > 0) {
            setTimeout(() => {
                selectMedia(mediaItems.value[mediaItems.value.length - 1]);
            }, 100);
        }
        
        // Show summary messages
        if (uploadSuccess.length > 0) {
            dialog.success(`${uploadSuccess.length} file(s) uploaded successfully`);
        }
        if (uploadErrors.length > 0) {
            dialog.error(`Failed to upload ${uploadErrors.length} file(s): ${uploadErrors.join('; ')}`);
        }
    } catch (error: any) {
        console.error('Error uploading file:', error);
        const errorMessage = error.response?.data?.message || 'Failed to upload file';
        alert(errorMessage);
    } finally {
        loading.value = false;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
};

const uploadFromUrl = async () => {
    if (!urlToUpload.value) return;

    loading.value = true;
    try {
        await axios.post('/api/v1/media/upload-from-url', { url: urlToUpload.value });
        showUrlUpload.value = false;
        urlToUpload.value = '';
        await loadMedia();
        
        // Auto-select newly uploaded item (last in list)
        if (mediaItems.value.length > 0) {
            setTimeout(() => {
                selectMedia(mediaItems.value[mediaItems.value.length - 1]);
            }, 100);
        }
    } catch (error: any) {
        console.error('Error uploading from URL:', error);
        const message = error.response?.data?.message || 'Failed to upload from URL';
        alert(message);
    } finally {
        loading.value = false;
    }
};

const setTypeFilter = (type: string) => {
    filters.value.type = type;
    showTypeFilter.value = false;
    loadMedia();
};

const formatSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleString();
};

const copyToClipboard = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
        // Could show toast notification here
    } catch (error) {
        console.error('Failed to copy:', error);
    }
};

// Cache for blob URLs to avoid re-fetching
const blobUrlCache = new Map<number, string>();

/**
 * Get media URL with proper handling for private files
 * Private files need to use the serve endpoint with authentication
 * For private files, we fetch as blob and create object URL
 */
const getMediaUrl = async (item: MediaItem): Promise<string> => {
    // If URL already includes localhost, fix it
    if (item.url && item.url.includes('localhost') && !item.url.includes(window.location.origin)) {
        return item.url.replace(/https?:\/\/localhost[^/]*/, window.location.origin);
    }
    
    // For private/local disk, fetch as blob with authentication
    if (item.disk && (item.disk === 'local' || item.disk === 'private')) {
        // Check cache first
        if (blobUrlCache.has(item.id)) {
            return blobUrlCache.get(item.id)!;
        }
        
        // Fetch as blob with auth header
        try {
            const token = localStorage.getItem('auth_token');
            const url = item.url || `${window.location.origin}/api/v1/media/${item.id}/serve`;
            
            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'image/*',
                },
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const blobUrl = URL.createObjectURL(blob);
                blobUrlCache.set(item.id, blobUrl);
                return blobUrl;
            }
        } catch (error) {
            console.error('Error fetching private media:', error);
        }
        
        // Fallback to original URL
        return item.url || '';
    }
    
    return item.url || '';
};

// Synchronous version for template use (returns promise, but template will handle it)
const getMediaUrlSync = (item: MediaItem): string => {
    // For private files, return a placeholder initially
    // The actual blob URL will be set when promise resolves
    if (item.disk && (item.disk === 'local' || item.disk === 'private')) {
        if (blobUrlCache.has(item.id)) {
            return blobUrlCache.get(item.id)!;
        }
        // Return empty initially, will be updated by async function
        return '';
    }
    
    // For public files, return URL directly
    if (item.url && item.url.includes('localhost') && !item.url.includes(window.location.origin)) {
        return item.url.replace(/https?:\/\/localhost[^/]*/, window.location.origin);
    }
    
    return item.url || '';
};

/**
 * Load private media as blob URL when image loads (fallback if preload didn't work)
 */
const loadPrivateMedia = async (item: MediaItem) => {
    if (item.disk && (item.disk === 'local' || item.disk === 'private')) {
        if (!blobUrlCache.has(item.id)) {
            try {
                await getMediaUrl(item);
                // Force re-render by updating the item
                const index = mediaItems.value.findIndex(m => m.id === item.id);
                if (index >= 0) {
                    mediaItems.value[index] = { ...mediaItems.value[index] };
                }
            } catch (error) {
                console.error('Error loading private media:', error);
            }
        }
    }
};

const deleteMedia = async (item: MediaItem) => {
    try {
        const confirmed = await dialog.confirm({
            title: 'Delete Media',
            message: `Are you sure you want to delete "${item.name}"? This action cannot be undone.`,
            confirmText: 'Delete',
            cancelText: 'Cancel',
        });
        
        if (!confirmed) {
            return;
        }

        console.log('Deleting media:', item.id);
        const response = await axios.delete(`/api/v1/media/${item.id}`);
        console.log('Delete response:', response);
        
        // Remove from selected media if selected
        selectedMedia.value = selectedMedia.value.filter(m => m.id !== item.id);
        // Reload media list
        await loadMedia();
        dialog.success('Media deleted successfully');
    } catch (error: any) {
        console.error('Error deleting media:', error);
        console.error('Error response:', error.response);
        const message = error.response?.data?.error?.message || error.response?.data?.message || error.message || 'Failed to delete media';
        dialog.error(message);
    }
};

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    const originalSrc = img.src;
    
    // Try to fix URL if it's using localhost instead of current origin
    if (originalSrc.includes('localhost') && !originalSrc.includes(window.location.origin)) {
        const newSrc = originalSrc.replace(/https?:\/\/localhost[^/]*/, window.location.origin);
        console.log('Fixing image URL:', originalSrc, '->', newSrc);
        img.src = newSrc;
        return; // Don't hide yet, try the new URL
    }
    
    // If still fails, show placeholder
    img.style.display = 'none';
    const parent = img.parentElement;
    if (parent && !parent.querySelector('.placeholder-icon')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'w-full h-full flex items-center justify-center placeholder-icon';
        placeholder.innerHTML = `
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        `;
        parent.appendChild(placeholder);
    }
};

// Intersection Observer for lazy loading
let observer: IntersectionObserver | null = null;

const setupIntersectionObserver = () => {
    // Clean up existing observer
    if (observer) {
        observer.disconnect();
    }
    
    // Only setup for list view
    if (viewMode.value !== 'list' || !hasMoreItems.value) {
        return;
    }
    
    // Wait for DOM to update
    nextTick(() => {
        if (observerTarget.value) {
            observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting && hasMoreItems.value && !isLoadingMore.value) {
                            loadMoreItems();
                        }
                    });
                },
                {
                    root: null,
                    rootMargin: '100px',
                    threshold: 0.1,
                }
            );
            
            observer.observe(observerTarget.value);
        }
    });
};

const loadMoreItems = () => {
    if (isLoadingMore.value || !hasMoreItems.value) return;
    
    isLoadingMore.value = true;
    
    // Simulate slight delay for smooth UX
    setTimeout(() => {
        visibleItemCount.value = Math.min(
            visibleItemCount.value + 20,
            mediaItems.value.length
        );
        isLoadingMore.value = false;
        
        // Setup observer again if there are more items
        if (hasMoreItems.value) {
            setupIntersectionObserver();
        }
    }, 100);
};

watch(() => filters.value.type, () => {
    loadMedia();
});

watch(() => viewMode.value, () => {
    // Reset visible items when switching view modes
    visibleItemCount.value = 20;
    nextTick(() => {
        setupIntersectionObserver();
    });
});

onMounted(() => {
    // Restore currentPath from localStorage
    const savedPath = localStorage.getItem('mediaManager.currentPath');
    if (savedPath !== null) {
        currentPath.value = savedPath;
    }
    loadMedia();
    
    // Add keyboard event listener for lightbox
    document.addEventListener('keydown', handleLightboxKeydown);
});

onUnmounted(() => {
    // Clean up observer
    if (observer) {
        observer.disconnect();
        observer = null;
    }
    
    // Remove keyboard event listener
    document.removeEventListener('keydown', handleLightboxKeydown);
    // Ensure body scroll is restored
    document.body.style.overflow = '';
});
</script>

