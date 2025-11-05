<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Themes</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage and activate themes</p>
            </div>
            <div class="flex gap-3">
                <button
                    @click="syncThemes"
                    :disabled="syncing"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors disabled:opacity-50"
                >
                    <span v-if="syncing" class="flex items-center">
                        <div class="h-4 w-4 border-2 border-gray-400 border-t-transparent rounded-full animate-spin mr-2"></div>
                        Syncing...
                    </span>
                    <span v-else class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync Themes
                    </span>
                </button>
                <button
                    @click="showUploadModal = true"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload Theme
                    </span>
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-6">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            :checked="filters.status === 'installed' || !filters.status"
                            @change="filters.status = filters.status === 'installed' ? '' : 'installed'; loadThemes()"
                            class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 bg-white dark:bg-gray-700"
                        />
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Installed</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            :checked="filters.status === 'broken'"
                            @change="filters.status = filters.status === 'broken' ? '' : 'broken'; loadThemes()"
                            class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 bg-white dark:bg-gray-700"
                        />
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Broken</span>
                    </label>
                </div>
                <div class="relative flex-1 md:max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="filters.search"
                        @input="loadThemes"
                        type="text"
                        placeholder="Search themes..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Loading themes...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="themes.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No themes found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <span v-if="filters.search || filters.status">
                    No themes match your current filters.
                </span>
                <span v-else>
                    Themes should be placed in the <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-900 dark:text-gray-200">themes/</code> directory.
                </span>
            </p>
        </div>

        <!-- Themes Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="theme in themes"
                :key="theme.id"
                :class="[
                    'bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden',
                    theme.is_active ? 'ring-2 ring-indigo-500' : ''
                ]"
            >
                <!-- Screenshot -->
                <div class="relative h-48 bg-gray-200 dark:bg-gray-700">
                    <img
                        v-if="theme.screenshot_url"
                        :src="theme.screenshot_url"
                        :alt="theme.name"
                        class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div
                        v-if="theme.is_active"
                        class="absolute top-2 right-2 px-2 py-1 bg-indigo-600 text-white text-xs font-semibold rounded"
                    >
                        Active
                    </div>
                    <div
                        v-if="theme.status === 'broken'"
                        class="absolute top-2 left-2 px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded"
                    >
                        Broken
                    </div>
                </div>

                <!-- Theme Info -->
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ theme.name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">
                        {{ theme.description || 'No description available' }}
                    </p>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-4">
                        <span>v{{ theme.version }}</span>
                        <span v-if="theme.author">{{ theme.author }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button
                            v-if="!theme.is_active && theme.status === 'installed'"
                            @click="activateTheme(theme)"
                            :disabled="activating === theme.slug"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm font-medium"
                        >
                            <span v-if="activating === theme.slug" class="flex items-center justify-center">
                                <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                                Activating...
                            </span>
                            <span v-else>Activate</span>
                        </button>
                        <button
                            v-else-if="theme.is_active"
                            disabled
                            class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg cursor-not-allowed text-sm font-medium"
                        >
                            Active
                        </button>
                        <button
                            v-if="theme.status === 'broken'"
                            disabled
                            class="flex-1 px-4 py-2 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-lg cursor-not-allowed text-sm font-medium"
                        >
                            Broken
                        </button>
                        <button
                            v-if="!theme.is_active"
                            @click="deleteTheme(theme)"
                            :disabled="deleting === theme.slug"
                            class="px-4 py-2 text-red-600 dark:text-red-400 border border-red-300 dark:border-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 disabled:opacity-50 transition-colors text-sm font-medium"
                        >
                            <span v-if="deleting === theme.slug" class="flex items-center">
                                <div class="h-4 w-4 border-2 border-red-400 border-t-transparent rounded-full animate-spin mr-2"></div>
                            </span>
                            <span v-else>Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div
            v-if="showUploadModal"
            @click.self="showUploadModal = false"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full m-4">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Upload Theme</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Theme ZIP File
                            </label>
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".zip"
                                @change="handleFileSelect"
                                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/20 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/30"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Maximum file size: 10MB
                            </p>
                        </div>
                        <div v-if="uploadError" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-sm text-red-700 dark:text-red-400">
                            {{ uploadError }}
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            @click="showUploadModal = false; uploadError = null"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            @click="uploadTheme"
                            :disabled="!selectedFile || uploading"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="uploading" class="flex items-center">
                                <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                                Uploading...
                            </span>
                            <span v-else>Upload</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';

interface Theme {
    id: number;
    name: string;
    slug: string;
    version: string;
    author: string | null;
    description: string | null;
    type: string;
    is_active: boolean;
    status: string;
    path: string;
    screenshot: string | null;
    screenshot_url?: string;
    meta: any;
}

const dialog = useDialog();

const themes = ref<Theme[]>([]);
const loading = ref(false);
const syncing = ref(false);
const activating = ref<string | null>(null);
const deleting = ref<string | null>(null);
const showUploadModal = ref(false);
const selectedFile = ref<File | null>(null);
const uploading = ref(false);
const uploadError = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const filters = ref({
    search: '',
    status: '',
});

const loadThemes = async () => {
    loading.value = true;
    try {
        const params: any = {};
        if (filters.value.search) {
            params.search = filters.value.search;
        }
        if (filters.value.status) {
            params.status = filters.value.status;
        }

        const response = await axios.get('/api/v1/themes', { params });
        themes.value = response.data.data || [];
    } catch (error: any) {
        console.error('Error loading themes:', error);
        dialog.error('Failed to load themes');
    } finally {
        loading.value = false;
    }
};

const syncThemes = async () => {
    syncing.value = true;
    try {
        await axios.post('/api/v1/themes/sync');
        dialog.success('Themes synced successfully');
        await loadThemes();
    } catch (error: any) {
        console.error('Error syncing themes:', error);
        const message = error.response?.data?.message || 'Failed to sync themes';
        dialog.error(message);
    } finally {
        syncing.value = false;
    }
};

const activateTheme = async (theme: Theme) => {
    activating.value = theme.slug;
    try {
        await axios.post(`/api/v1/themes/${theme.slug}/activate`);
        dialog.success(`Theme "${theme.name}" activated successfully`);
        await loadThemes();
    } catch (error: any) {
        console.error('Error activating theme:', error);
        const message = error.response?.data?.message || 'Failed to activate theme';
        dialog.error(message);
    } finally {
        activating.value = null;
    }
};

const deleteTheme = async (theme: Theme) => {
    const confirmed = await dialog.confirm({
        title: 'Delete Theme',
        message: `Are you sure you want to delete "${theme.name}"?`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });

    if (!confirmed) {
        return;
    }

    deleting.value = theme.slug;
    try {
        await axios.delete(`/api/v1/themes/${theme.slug}`);
        dialog.success('Theme disabled successfully');
        await loadThemes();
    } catch (error: any) {
        console.error('Error deleting theme:', error);
        const message = error.response?.data?.message || 'Failed to delete theme';
        dialog.error(message);
    } finally {
        deleting.value = null;
    }
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
        uploadError.value = null;
    }
};

const uploadTheme = async () => {
    if (!selectedFile.value) {
        return;
    }

    uploading.value = true;
    uploadError.value = null;

    try {
        const formData = new FormData();
        formData.append('theme', selectedFile.value);

        await axios.post('/api/v1/themes/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        dialog.success('Theme uploaded successfully');
        showUploadModal.value = false;
        selectedFile.value = null;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
        await loadThemes();
    } catch (error: any) {
        console.error('Error uploading theme:', error);
        const message = error.response?.data?.message || 'Failed to upload theme';
        uploadError.value = message;
    } finally {
        uploading.value = false;
    }
};

onMounted(() => {
    loadThemes();
});
</script>
