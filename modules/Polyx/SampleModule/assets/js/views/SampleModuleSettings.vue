<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Sample Module Settings</h1>
                <p class="text-sm text-gray-600 mt-1">Configure the Sample Module behavior</p>
            </div>
        </div>

        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600">Loading settings...</p>
        </div>

        <form v-else @submit.prevent="saveSettings" class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">General Settings</h2>

                <div class="space-y-4">
                    <div class="flex items-center">
                        <input
                            id="enabled"
                            v-model="form.enabled"
                            type="checkbox"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <label for="enabled" class="ml-2 block text-sm text-gray-900">
                            Enable additional content after post title
                        </label>
                    </div>

                    <div>
                        <label for="additional_content" class="block text-sm font-medium text-gray-700 mb-1">
                            Additional Content
                        </label>
                        <textarea
                            id="additional_content"
                            v-model="form.additional_content"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter content to add after post title..."
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            This content will be added after the post title when viewing posts.
                        </p>
                    </div>

                    <div>
                        <label for="style" class="block text-sm font-medium text-gray-700 mb-1">
                            Custom Style (CSS)
                        </label>
                        <input
                            id="style"
                            v-model="form.style"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="padding: 10px; background: #f0f0f0; ..."
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            CSS styles to apply to the additional content box.
                        </p>
                    </div>

                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">
                            Content Position
                        </label>
                        <select
                            id="position"
                            v-model="form.position"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="before_title">Before Title</option>
                            <option value="after_title">After Title</option>
                            <option value="before_content">Before Content</option>
                            <option value="after_content">After Content</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">
                            Where to insert the additional content in the post.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">How it works</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>
                                This module demonstrates how to:
                            </p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>Register menu items in the admin sidebar using hooks</li>
                                <li>Add filters to modify post content</li>
                                <li>Create settings pages for modules</li>
                                <li>Integrate with the PolyCMS hook system</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    @click="loadSettings"
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ saving ? 'Saving...' : 'Save Settings' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const saving = ref(false);

const form = ref({
    enabled: true,
    additional_content: '',
    style: '',
    position: 'after_title',
});

const loadSettings = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/sample-module/settings');
        if (response.data.success && response.data.data) {
            form.value = {
                enabled: response.data.data.enabled ?? true,
                additional_content: response.data.data.additional_content ?? '',
                style: response.data.data.style ?? '',
                position: response.data.data.position ?? 'after_title',
            };
        }
    } catch (error) {
        console.error('Error loading settings:', error);
        alert('Failed to load settings');
    } finally {
        loading.value = false;
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        const response = await axios.post('/api/v1/sample-module/settings', form.value);
        if (response.data.success) {
            alert('Settings saved successfully!');
        }
    } catch (error: any) {
        console.error('Error saving settings:', error);
        const message = error.response?.data?.message || 'Failed to save settings';
        alert(message);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadSettings();
});
</script>
