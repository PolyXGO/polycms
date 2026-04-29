<template>
    <div class="space-y-4">
        <div class="space-y-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $t('Name *') }}</label>
            <input
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                required
                @input="helpers.generateSlug?.()"
            />
        </div>

        <div class="space-y-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $t('Slug *') }}</label>
            <input
                v-model="form.slug"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                required
                @input="handleSlugInput"
                @blur="handleSlugInput"
            />
            <div v-if="form.slug" class="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span class="font-bold">{{ $t('Permalink:') }}</span>
                <a :href="permalink" target="_blank" rel="noopener" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline break-all">
                    {{ permalink }}
                </a>
            </div>
        </div>

        <div class="space-y-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $t('SKU') }}</label>
            <input v-model="form.sku" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow" :placeholder="$t('Stock keeping unit')" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, getCurrentInstance } from 'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('ProductTitlePanel must be used within editor context');
}

const form = context.form.value;
const helpers = context.helpers ?? {};

const permalink = computed(() => helpers.getPermalink?.() ?? '');

const handleSlugInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    helpers.onSlugInput?.(event);
    form.slug = target.value;
};
</script>

