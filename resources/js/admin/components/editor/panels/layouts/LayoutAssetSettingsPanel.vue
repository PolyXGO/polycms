<template>
    <div v-if="form" class="space-y-5">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Category
            </label>
            <input
                v-model="form.category"
                type="text"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition-all duration-200 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                placeholder="general"
                :disabled="isReadOnly"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Use categories like `hero`, `cta`, `showcase`, `footer`, `general`.
            </p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Description
            </label>
            <textarea
                v-model="form.description"
                rows="4"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition-all duration-200 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                placeholder="Explain when this reusable asset should be used."
                :disabled="isReadOnly"
            />
        </div>

        <div
            v-if="form.kind === 'template'"
            class="space-y-3 rounded-2xl border border-gray-200 bg-white/70 p-4 dark:border-gray-700 dark:bg-gray-800/60"
        >
            <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">Applies To</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Choose which post types can use this template.
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2">
                <label
                    v-for="target in applyTargets"
                    :key="target.value"
                    class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 transition-colors hover:border-indigo-400 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-indigo-500 dark:hover:text-indigo-300"
                >
                    <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        :checked="selectedAppliesTo.includes(target.value)"
                        :disabled="isReadOnly"
                        @change="toggleApplyTarget(target.value)"
                    />
                    <span>{{ target.label }}</span>
                </label>
            </div>
        </div>

        <div class="space-y-3 rounded-2xl border border-gray-200 bg-white/70 p-4 dark:border-gray-700 dark:bg-gray-800/60">
            <div class="text-sm font-semibold text-gray-900 dark:text-white">Asset Info</div>

            <dl class="space-y-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Source</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ sourceLabel }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ form.is_system ? 'Default / locked' : 'Custom' }}</dd>
                </div>
                <div v-if="form.kind === 'template'" class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Assigned Content</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ assignedCount }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Layout Engine</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ form.layout || 'landing' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('LayoutAssetSettingsPanel must be used within editor context');
}

const form = context.form;

const isReadOnly = computed<boolean>(() => !!form.value?.is_system);
const assignedCount = computed<number>(() => Number(form.value?.assigned_posts_count ?? 0));
const sourceLabel = computed(() => form.value?.source_name || form.value?.source_type || 'Custom');
const selectedAppliesTo = computed<string[]>(() => Array.isArray(form.value?.applies_to) ? form.value.applies_to : []);

const applyTargets = [
    { value: 'page', label: 'Pages' },
    { value: 'post', label: 'Posts' },
    { value: 'news', label: 'News' },
];

const toggleApplyTarget = (target: string) => {
    const current = new Set(selectedAppliesTo.value);
    if (current.has(target)) {
        current.delete(target);
    } else {
        current.add(target);
    }

    form.value.applies_to = Array.from(current);
};
</script>
