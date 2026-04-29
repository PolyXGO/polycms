<template>
    <div v-if="form" class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excerpt</label>
        <textarea
            v-model="form.excerpt"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="A short summary for your post..."
        />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Provide a short summary for feeds and meta descriptions.</p>
    </div>
</template>

<script setup lang="ts">
import { inject } from 'vue';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('PostExcerptPanel must be used within editor context');
}

const form = context.form;

if (!form.value) {
    form.value = {
        title: '',
        slug: '',
        type: context.type ?? 'post',
        status: 'draft',
        excerpt: '',
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
    } as any;
}

</script>

