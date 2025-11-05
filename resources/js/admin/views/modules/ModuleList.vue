<template>
    <div>
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

        <!-- Modules Grid -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600">Loading modules...</p>
        </div>

        <div v-else-if="modules.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No modules found</h3>
            <p class="mt-1 text-sm text-gray-500">Modules should be placed in the <code class="bg-gray-100 px-2 py-1 rounded">modules/</code> directory.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="module in modules"
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

                    <!-- Toggle Switch -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
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
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
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
        // Reload to get correct state
        await loadModules();
    } finally {
        toggleLoading.value = null;
    }
};

onMounted(() => {
    loadModules();
});
</script>
