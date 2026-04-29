<template>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Banner Slider Settings</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Configure banner slider display options</p>
        </div>

        <form @submit.prevent="saveSettings" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <!-- Auto Slide -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        v-model="settings.auto_slide"
                        type="checkbox"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                    />
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Auto Slide</span>
                </label>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Automatically transition between banners</p>
            </div>

            <!-- Auto Slide Interval -->
            <div v-if="settings.auto_slide" class="mb-6">
                <label for="auto_slide_interval" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Auto Slide Interval (milliseconds)
                </label>
                <input
                    v-model.number="settings.auto_slide_interval"
                    type="number"
                    id="auto_slide_interval"
                    min="100"
                    max="30000"
                    step="100"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Time between slides (100-30000ms)</p>
            </div>

            <!-- Transition Effect -->
            <div class="mb-6">
                <label for="transition_effect" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Transition Effect
                </label>
                <select
                    v-model="settings.transition_effect"
                    id="transition_effect"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                >
                    <option value="slide">Slide (Left to Right)</option>
                    <option value="slide-right">Slide (Right to Left)</option>
                    <option value="slide-top">Slide (Top to Bottom)</option>
                    <option value="slide-bottom">Slide (Bottom to Top)</option>
                    <option value="fade">Fade</option>
                    <option value="fade-in">Fade In</option>
                    <option value="fade-out">Fade Out</option>
                    <option value="zoom">Zoom</option>
                    <option value="zoom-in">Zoom In</option>
                    <option value="zoom-out">Zoom Out</option>
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Animation effect when transitioning between banners</p>
            </div>

            <!-- Show Navigation -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        v-model="settings.show_navigation"
                        type="checkbox"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                    />
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show Navigation Arrows</span>
                </label>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Display left/right navigation buttons</p>
            </div>

            <!-- Show Indicators -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        v-model="settings.show_indicators"
                        type="checkbox"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                    />
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show Indicators (Dots)</span>
                </label>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Display dot indicators at the bottom</p>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    @click="loadSettings"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    Reset
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ loading ? 'Saving...' : 'Save Settings' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useDialog } from '@admin/composables/useDialog';
import { useTranslation } from '@admin/composables/useTranslation';

const { t } = useTranslation();
const dialog = useDialog();

const loading = ref(false);
const settings = ref({
    auto_slide: true,
    auto_slide_interval: 5000,
    transition_effect: 'slide',
    show_navigation: true,
    show_indicators: true,
});

const loadSettings = async () => {
    try {
        const response = await axios.get('/api/v1/banner-slider/settings');
        const data = response.data.data;

        settings.value = {
            auto_slide: data.auto_slide === '1' || data.auto_slide === true,
            auto_slide_interval: parseInt(data.auto_slide_interval || '5000'),
            transition_effect: data.transition_effect || 'slide',
            show_navigation: data.show_navigation === '1' || data.show_navigation === true,
            show_indicators: data.show_indicators === '1' || data.show_indicators === true,
        };
    } catch (error) {
        console.error('Error loading settings:', error);
    }
};

const saveSettings = async () => {
    loading.value = true;
    try {
        await axios.put('/api/v1/banner-slider/settings', settings.value);
        dialog.success('Settings saved successfully');
    } catch (error: any) {
        console.error('Error saving settings:', error);
        const message = error.response?.data?.message || 'Failed to save settings';
        dialog.error(message);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadSettings();
});
</script>
