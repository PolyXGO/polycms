<template>
    <div class="payment-logs-page">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('Payment Logs') }}</h1>
            <div class="flex items-center gap-4">
                <select
                    v-model="filters.level"
                    @change="() => loadLogs()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                >
                    <option value="">{{ t('All Levels') }}</option>
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="error">Error</option>
                </select>
                <button 
                    @click="() => loadLogs()"
                    class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                <div 
                    v-for="log in logs" 
                    :key="log.id"
                    class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div 
                                class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                :class="getLevelClass(log.level)"
                            >
                                <svg v-if="log.level === 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <svg v-else-if="log.level === 'warning'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ log.gateway }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded capitalize" :class="getLevelBadgeClass(log.level)">
                                        {{ log.level }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ log.message }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ formatDate(log.created_at) }}</p>
                            </div>
                        </div>
                        <button 
                            v-if="log.context"
                            @click="toggleExpand(log)"
                            class="text-xs text-indigo-600 hover:text-indigo-700"
                        >
                            {{ expandedLogs.includes(log.id) ? t('Hide Details') : t('Show Details') }}
                        </button>
                    </div>
                    <div v-if="expandedLogs.includes(log.id) && log.context" class="mt-3 ml-11">
                        <pre class="bg-gray-100 dark:bg-gray-900 p-3 rounded text-xs overflow-x-auto">{{ JSON.stringify(log.context, null, 2) }}</pre>
                    </div>
                </div>
                
                <div v-if="logs.length === 0" class="p-12 text-center text-gray-500 dark:text-gray-400">
                    {{ t('No payment logs found') }}
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t('Page') }} {{ pagination.current_page }} {{ t('of') }} {{ pagination.last_page }}
                </p>
                <div class="flex gap-2">
                    <button 
                        @click="loadPage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50"
                    >
                        {{ t('Previous') }}
                    </button>
                    <button 
                        @click="loadPage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50"
                    >
                        {{ t('Next') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';

const { t } = useTranslation();
const loading = ref(true);
const logs = ref<any[]>([]);
const expandedLogs = ref<number[]>([]);

const filters = reactive({
    level: '',
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
});

const loadLogs = async (page = 1) => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        params.append('page', String(page));
        if (filters.level) params.append('level', filters.level);

        const response = await axios.get(`/api/v1/payment-logs?${params}`);
        logs.value = response.data.data;
        pagination.current_page = response.data.current_page || 1;
        pagination.last_page = response.data.last_page || 1;
    } catch (error) {
        console.error('Error loading payment logs:', error);
    } finally {
        loading.value = false;
    }
};

const loadPage = (page: number) => {
    loadLogs(page);
};

const toggleExpand = (log: any) => {
    const index = expandedLogs.value.indexOf(log.id);
    if (index > -1) {
        expandedLogs.value.splice(index, 1);
    } else {
        expandedLogs.value.push(log.id);
    }
};

const getLevelClass = (level: string) => {
    const classes: Record<string, string> = {
        error: 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400',
        warning: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400',
        info: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400',
    };
    return classes[level] || 'bg-gray-100 text-gray-600';
};

const getLevelBadgeClass = (level: string) => {
    const classes: Record<string, string> = {
        error: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
        warning: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
        info: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
    };
    return classes[level] || 'bg-gray-100 text-gray-700';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString();
};

onMounted(() => loadLogs());
</script>
