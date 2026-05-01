<template>
    <div v-if="form" class="space-y-4">
        <div class="space-y-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Page Layout</label>
            <select v-model="form.layout" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                <option value="default">Default (with Sidebar)</option>
                <option value="fullwidth">Full Width (no Sidebar)</option>
                <option value="landing">Landing Page (Blank Canvas)</option>
                <option value="single-column">Single Column (Top Gallery)</option>
            </select>
            <p class="text-xs text-gray-500 dark:text-gray-400">Choose how this content should be displayed on the frontend.</p>
        </div>

        <div v-if="context.type === 'product' && form.layout === 'single-column'" class="space-y-1">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" v-model="form.settings.show_gallery" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 transition-all duration-200" />
                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show Product Gallery</span>
            </label>
            <p class="text-xs text-gray-500 dark:text-gray-400">Toggle whether to display the photo gallery at the top of the single column layout.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { inject, onMounted } from 'vue';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('ThemeLayoutPanel must be used within editor context');
}

const form = context.form;

onMounted(() => {
    if (form.value && !form.value.layout) {
        form.value.layout = 'default';
    }
    if (form.value && (!form.value.settings || Array.isArray(form.value.settings))) {
        form.value.settings = {};
    }
    if (form.value && form.value.settings && form.value.settings.show_gallery === undefined) {
        form.value.settings.show_gallery = true;
    }
});
</script>
