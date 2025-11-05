<template>
    <div class="media-manager bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden">
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
        <div class="flex h-[600px]">
            <!-- Media Grid/List -->
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="loading" class="flex items-center justify-center h-full">
                    <div class="text-gray-500 dark:text-gray-400">Loading...</div>
                </div>
                <div v-else-if="mediaItems.length === 0" class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                    <svg class="w-24 h-24 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p>No media found</p>
                </div>
                <div v-else :class="[
                    'grid gap-4',
                    viewMode === 'grid' ? 'grid-cols-2 md:grid-cols-4 lg:grid-cols-6' : 'grid-cols-1'
                ]">
                    <div
                        v-for="item in mediaItems"
                        :key="item.id"
                        @click="selectMedia(item)"
                        @dblclick="doubleClickMedia(item)"
                        :class="[
                            'relative cursor-pointer rounded-lg overflow-hidden border-2 transition-all',
                            isSelected(item.id) ? 'border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
                            viewMode === 'list' ? 'flex items-center gap-4 p-4' : ''
                        ]"
                    >
                        <div v-if="viewMode === 'grid'" class="aspect-square bg-gray-100 dark:bg-gray-800 flex items-center justify-center relative group">
                            <img v-if="item.type === 'image'" :src="item.url" :alt="item.name" class="w-full h-full object-cover" @error="handleImageError" />
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
                        <div v-else class="flex items-center gap-4 flex-1">
                            <img v-if="item.type === 'image'" :src="item.url" :alt="item.name" class="w-16 h-16 object-cover rounded" @error="handleImageError" />
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">{{ item.name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatSize(item.size) }}</div>
                            </div>
                            <button
                                @click.stop="deleteMedia(item)"
                                class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                title="Delete"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar (Details) -->
            <div v-if="selectedMedia.length > 0" class="w-80 border-l border-gray-200 dark:border-gray-700 p-4 overflow-y-auto">
                <div v-for="item in selectedMedia" :key="item.id" class="mb-6">
                    <img v-if="item.type === 'image'" :src="item.url" :alt="item.name" class="w-full rounded-lg mb-4" />
                    <div class="space-y-2 text-sm">
                        <div>
                            <label class="font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <div class="text-gray-900 dark:text-white">{{ item.name }}</div>
                        </div>
                        <div>
                            <label class="font-medium text-gray-700 dark:text-gray-300">URL</label>
                            <div class="flex items-center gap-2">
                                <input :value="item.url" readonly class="flex-1 px-2 py-1 text-xs bg-gray-100 dark:bg-gray-800 rounded border border-gray-300 dark:border-gray-600" />
                                <button @click="copyToClipboard(item.url)" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="font-medium text-gray-700 dark:text-gray-300">Size</label>
                            <div class="text-gray-900 dark:text-white">{{ formatSize(item.size) }}</div>
                        </div>
                        <div>
                            <label class="font-medium text-gray-700 dark:text-gray-300">Uploaded at</label>
                            <div class="text-gray-900 dark:text-white">{{ formatDate(item.created_at) }}</div>
                        </div>
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

        <!-- Hidden File Input -->
        <input
            ref="fileInput"
            type="file"
            multiple
            accept="image/*,video/*,application/pdf"
            @change="handleFileUpload"
            class="hidden"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
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
const selectedMedia = ref<MediaItem[]>([]);
const viewMode = ref<'grid' | 'list'>('grid');
const showUploadDropdown = ref(false);
const showUrlUpload = ref(false);
const urlToUpload = ref('');
const showTypeFilter = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

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

        const response = await axios.get('/api/v1/media', { params });
        const items = response.data.data || [];
        
        // Fix URLs that use localhost to use current origin
        mediaItems.value = items.map((item: MediaItem) => {
            if (item.url && item.url.includes('localhost') && !item.url.includes(window.location.origin)) {
                item.url = item.url.replace(/https?:\/\/localhost[^/]*/, window.location.origin);
            }
            return item;
        });
    } catch (error) {
        console.error('Error loading media:', error);
    } finally {
        loading.value = false;
    }
};

const refreshMedia = () => {
    loadMedia();
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
    if (!props.multiple) {
        emit('select', item);
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

    loading.value = true;
    try {
        for (const file of Array.from(files)) {
            const formData = new FormData();
            formData.append('file', file);

            // Debug: Check FormData
            console.log('Uploading file:', file.name, file.size, file.type);
            console.log('FormData has file:', formData.has('file'));

            try {
                // Use XMLHttpRequest directly to ensure FormData is sent correctly
                await new Promise<void>((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '/api/v1/media/upload');
                    
                    // Set Authorization header if token exists
                    const token = localStorage.getItem('auth_token');
                    if (token) {
                        xhr.setRequestHeader('Authorization', `Bearer ${token}`);
                    }
                    xhr.setRequestHeader('Accept', 'application/json');
                    // Don't set Content-Type - browser will set it with boundary automatically
                    
                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                // Fix URL in response if needed
                                if (response.data?.url && response.data.url.includes('localhost')) {
                                    response.data.url = response.data.url.replace(/https?:\/\/localhost[^/]*/, window.location.origin);
                                }
                                resolve();
                            } catch {
                                resolve(); // Still resolve if can't parse response
                            }
                        } else {
                            try {
                                const error = JSON.parse(xhr.responseText);
                                reject(new Error(error.error?.message || error.message || 'Upload failed'));
                            } catch {
                                reject(new Error(`Upload failed with status ${xhr.status}`));
                            }
                        }
                    };
                    
                    xhr.onerror = () => {
                        reject(new Error('Network error'));
                    };
                    
                    xhr.send(formData);
                });
            } catch (error: any) {
                const errorMessage = error.message || 'Failed to upload file';
                alert(`Error uploading ${file.name}: ${errorMessage}`);
                console.error('Error uploading file:', error);
            }
        }
        await loadMedia();
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

const deleteMedia = async (item: MediaItem) => {
    const confirmed = await dialog.confirm({
        title: 'Delete Media',
        message: `Are you sure you want to delete "${item.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
    });
    
    if (!confirmed) {
        return;
    }

    try {
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

watch(() => filters.value.type, () => {
    loadMedia();
});

onMounted(() => {
    loadMedia();
});
</script>

