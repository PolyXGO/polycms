<!--
  Settings.vue — Module Settings Page
  =====================================
  Demonstrates the standard pattern for module settings:
  - Load settings from API on mount
  - Save settings via PUT request
  - Use SettingsService backend (database-backed)
  - Group settings by category
  - Show educational info about each setting
-->
<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2"><svg class="w-6 h-6 text-gray-500 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Settings</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Configure Sample Module behavior
                </p>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Loading settings...</p>
        </div>

        <form v-else @submit.prevent="saveSettings" class="space-y-6">
            <!-- Content Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Content Settings
                </h2>

                <div class="space-y-5">
                    <!-- Reading Time Badge -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5 mt-1">
                            <input
                                id="content_badge"
                                type="checkbox"
                                :checked="form.sample_module_content_badge === 'yes'"
                                @change="form.sample_module_content_badge = ($event.target as HTMLInputElement).checked ? 'yes' : 'no'"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                            />
                        </div>
                        <div class="ml-3">
                            <label for="content_badge" class="text-sm font-medium text-gray-900 dark:text-white">
                                Show Reading Time Badge
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Adds a <svg class="w-4 h-4 inline-block -mt-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg> "X min read" badge after the first heading in post content.
                                Uses the <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded text-xs">post.content.render</code> filter hook.
                            </p>
                        </div>
                    </div>

                    <!-- Badge Style -->
                    <div>
                        <label for="badge_style" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Badge Style
                        </label>
                        <select
                            id="badge_style"
                            v-model="form.sample_module_badge_style"
                            class="w-full sm:w-64 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="default">Default (Indigo pill)</option>
                            <option value="minimal">Minimal (Text only)</option>
                            <option value="colorful">Colorful (Gradient)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Notes Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Notes (CRUD Demo)
                </h2>

                <div class="space-y-5">
                    <!-- Notes per page -->
                    <div>
                        <label for="notes_per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notes Per Page
                        </label>
                        <input
                            id="notes_per_page"
                            v-model="form.sample_module_notes_per_page"
                            type="number"
                            min="5"
                            max="100"
                            class="w-full sm:w-32 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Pagination size for the Notes list. Default: 10.
                            Stored via <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded text-xs">SettingsService::set()</code>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Developer Info -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400 dark:text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">How Settings Work</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400 space-y-1">
                            <p><strong>Backend:</strong> <code>SettingsService::get($key)</code> / <code>::set($key, $value)</code> — stored in <code>settings</code> table.</p>
                            <p><strong>Key convention:</strong> <code>module_slug_setting_name</code> (e.g., <code>sample_module_content_badge</code>).</p>
                            <p><strong>Defaults:</strong> Register via <code>settings.defaults</code> filter hook in your ServiceProvider.</p>
                            <p><strong>API pattern:</strong> <code>GET /api/v1/{module}/settings</code> + <code>PUT /api/v1/{module}/settings</code>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    @click="loadSettings"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
                >
                    Reset
                </button>
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                >
                    {{ saving ? 'Saving...' : 'Save Settings' }}
                </button>
            </div>

            <!-- Success toast -->
            <transition name="fade">
                <div v-if="showSuccess" class="fixed bottom-6 right-6 bg-green-500 text-white px-5 py-3 rounded-lg shadow-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Settings saved!
                </div>
            </transition>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const saving = ref(false);
const showSuccess = ref(false);

const form = ref({
    sample_module_content_badge: 'no',
    sample_module_notes_per_page: '10',
    sample_module_badge_style: 'default',
});

const loadSettings = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/v1/sample-module/settings');
        if (res.data.success && res.data.data) {
            form.value = { ...form.value, ...res.data.data };
        }
    } catch (error) {
        console.error('Error loading settings:', error);
    } finally {
        loading.value = false;
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        const res = await axios.put('/api/v1/sample-module/settings', form.value);
        if (res.data.success) {
            showSuccess.value = true;
            setTimeout(() => { showSuccess.value = false; }, 3000);
        }
    } catch (error: any) {
        const msg = error.response?.data?.message || 'Failed to save settings';
        alert(msg);
    } finally {
        saving.value = false;
    }
};

onMounted(() => loadSettings());
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
