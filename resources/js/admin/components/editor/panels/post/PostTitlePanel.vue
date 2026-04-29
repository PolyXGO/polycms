<template>
    <div class="space-y-4">
        <div class="space-y-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Title *</label>
            <input
                v-model="form.title"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                required
                @input="helpers.generateSlug?.()"
            />
        </div>

        <div class="space-y-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Slug *</label>
            <input
                v-model="form.slug"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                required
                @input="handleSlugInput"
                @blur="handleSlugInput"
            />
            <div v-if="form.slug" class="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span class="font-bold">Permalink:</span>
                <a :href="permalink" target="_blank" rel="noopener" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline break-all">
                    {{ permalink }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Type *</label>
                <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                    <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('PostTitlePanel must be used within editor context');
}

const form = context.form.value;
const helpers = context.helpers ?? {};
const state = context.state ?? {};

const typeOptions = computed(() => state.postTypes ?? [
    { label: 'Post', value: 'post' },
    { label: 'Page', value: 'page' },
    { label: 'News', value: 'news' },
]);

const permalink = computed(() => helpers.getPermalink?.() ?? '');

const handleSlugInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    helpers.onSlugInput?.(event);
    form.slug = target.value;
};
</script>

