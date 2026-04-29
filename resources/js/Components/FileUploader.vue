<template>
    <Modal :show="show" :center="true" @close="close" maxWidth="2xl">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                    Upload File - <span class="text-indigo-500 font-medium">Upload Limits</span>
                </h2>
                <button @click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <div class="mb-6">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">Upload queue</h3>
                    <p class="text-xs text-gray-400">Queue length: {{ queue.length }}</p>
                </div>

                <!-- Drop Zone -->
                <div 
                    class="relative border-2 border-dashed border-indigo-100 dark:border-gray-800 rounded-2xl bg-indigo-50/30 dark:bg-gray-800/20 p-12 transition-all group overflow-hidden"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                    :class="{ 'border-indigo-400 bg-indigo-50/60 dark:bg-indigo-900/10': isDragging }"
                >
                    <!-- Glassmorphism gradient effect -->
                    <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-white/40 to-transparent dark:from-white/5 pointer-events-none"></div>
                    
                    <div class="relative flex flex-col items-center justify-center text-center">
                        <span class="text-2xl font-black text-indigo-500 mb-2 tracking-tight">Drop</span>
                        <p class="text-xs font-bold text-gray-400">Drop Files here to upload them.</p>
                    </div>
                </div>

                <!-- Manual Selection -->
                <div class="mt-6">
                    <label class="text-xs font-bold text-gray-600 dark:text-gray-400 mb-2 block">Upload</label>
                    <div class="flex items-center space-x-3 p-3 border border-dashed border-indigo-100 dark:border-gray-800 rounded-lg">
                        <button @click="$refs.fileInput.click()" class="px-4 py-1 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-xs font-bold rounded border border-gray-300 dark:border-gray-700 hover:bg-gray-200 transition-colors">
                            Choose Files
                        </button>
                        <span class="text-xs text-gray-400 italic">No file chosen</span>
                        <input type="file" ref="fileInput" multiple @change="handleFileSelect" class="hidden" />
                    </div>
                </div>

                <!-- Queue Table -->
                <div v-if="queue.length > 0" class="mt-8 border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-800/40 text-[10px] uppercase font-black text-gray-400 tracking-widest">
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Size</th>
                                <th class="px-4 py-3">Progress</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            <tr v-for="item in queue" :key="item.id" class="text-xs text-gray-600 dark:text-gray-400">
                                <td class="px-4 py-3 truncate max-w-[150px] font-bold" :title="item.file.name">{{ item.file.name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ formatSize(item.file.size) }}</td>
                                <td class="px-4 py-3 w-32">
                                    <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-indigo-500 transition-all duration-300"
                                            :style="{ width: item.progress + '%' }"
                                            :class="{ 'bg-emerald-500': item.status === 'success', 'bg-rose-500': item.status === 'error' }"
                                        ></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="item.status === 'pending'" class="text-gray-400">Pending</span>
                                    <span v-else-if="item.status === 'uploading'" class="text-blue-500 font-bold">Uploading...</span>
                                    <span v-else-if="item.status === 'success'" class="text-emerald-500 font-bold">Success</span>
                                    <span v-else-if="item.status === 'error'" class="text-rose-500 font-bold" :title="item.error">Failed</span>
                                </td>
                                <td class="px-4 py-3">
                                    <button @click="removeFromQueue(item.id)" class="text-gray-400 hover:text-rose-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Overall Progress -->
                <div v-if="queue.length > 0" class="mt-8">
                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Queue progress:</p>
                    <div class="w-full h-2 bg-indigo-50 dark:bg-gray-800 rounded-full overflow-hidden mb-6">
                        <div 
                            class="h-full bg-indigo-500 transition-all duration-500"
                            :style="{ width: overallProgress + '%' }"
                        ></div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="uploadAll" 
                            :disabled="isUploading || !hasPending"
                            class="flex items-center px-4 py-2 bg-emerald-500/80 hover:bg-emerald-500 text-white text-xs font-bold rounded-md transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            Upload all
                        </button>
                        <button 
                            @click="cancelAll"
                            :disabled="!isUploading"
                            class="flex items-center px-4 py-2 bg-amber-400/80 hover:bg-amber-400 text-white text-xs font-bold rounded-md transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Cancel all
                        </button>
                        <button 
                            @click="removeAll"
                            :disabled="isUploading"
                            class="flex items-center px-4 py-2 bg-rose-500/80 hover:bg-rose-500 text-white text-xs font-bold rounded-md transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Remove all
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: Boolean,
    uploadUrl: {
        type: String,
        required: true
    },
    params: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'success', 'error']);

const isDragging = ref(false);
const queue = ref([]);
const isUploading = ref(false);
const abortControllers = new Map();

const formatSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const hasPending = computed(() => queue.value.some(item => item.status === 'pending' || item.status === 'error'));

const overallProgress = computed(() => {
    if (queue.value.length === 0) return 0;
    const total = queue.value.reduce((acc, item) => acc + item.progress, 0);
    return Math.round(total / queue.value.length);
});

const handleDrop = (e) => {
    isDragging.value = false;
    const files = Array.from(e.dataTransfer.files);
    addFilesToQueue(files);
};

const handleFileSelect = (e) => {
    const files = Array.from(e.target.files);
    addFilesToQueue(files);
};

const addFilesToQueue = (files) => {
    files.forEach(file => {
        queue.value.push({
            id: Math.random().toString(36).substr(2, 9),
            file: file,
            progress: 0,
            status: 'pending',
            error: null
        });
    });
};

const removeFromQueue = (id) => {
    if (abortControllers.has(id)) {
        abortControllers.get(id).abort();
        abortControllers.delete(id);
    }
    const index = queue.value.findIndex(item => item.id === id);
    if (index !== -1) queue.value.splice(index, 1);
};

const uploadItem = async (item) => {
    if (item.status === 'success') return;
    
    item.status = 'uploading';
    item.progress = 0;
    item.error = null;

    const formData = new FormData();
    formData.append('file', item.file);
    Object.entries(props.params).forEach(([key, value]) => {
        formData.append(key, value);
    });

    const controller = new AbortController();
    abortControllers.set(item.id, controller);

    try {
        const response = await axios.post(props.uploadUrl, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            signal: controller.signal,
            onUploadProgress: (progressEvent) => {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                item.progress = percentCompleted;
            }
        });

        if (response.data.success) {
            item.status = 'success';
            item.progress = 100;
        } else {
            throw new Error(response.data.message || 'Upload failed');
        }
    } catch (e) {
        if (e.name === 'CanceledError') {
            item.status = 'pending';
            item.progress = 0;
        } else {
            item.status = 'error';
            item.error = e.message;
        }
    } finally {
        abortControllers.delete(item.id);
    }
};

const uploadAll = async () => {
    if (isUploading.value) return;
    isUploading.value = true;
    
    const pendingItems = queue.value.filter(item => item.status === 'pending' || item.status === 'error');
    
    // Concurrent uploads (limit to 3 at a time)
    const limit = 3;
    for (let i = 0; i < pendingItems.length; i += limit) {
        const chunk = pendingItems.slice(i, i + limit);
        await Promise.all(chunk.map(item => uploadItem(item)));
    }
    
    isUploading.value = false;
    
    const allSuccess = queue.value.every(item => item.status === 'success');
    if (allSuccess) {
        emit('success');
    }
};

const cancelAll = () => {
    abortControllers.forEach(controller => controller.abort());
    abortControllers.clear();
    isUploading.value = false;
};

const removeAll = () => {
    queue.value = [];
};

const close = () => {
    if (isUploading.value) {
        if (!confirm('Uploading is in progress. Are you sure you want to close?')) return;
        cancelAll();
    }
    emit('close');
};
</script>

<style scoped>
.tree-item-container {
    --line-pos: 0;
}
</style>
