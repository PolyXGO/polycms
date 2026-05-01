<template>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ t("Global FAQ's") }}
            </h3>

            <div class="rounded-lg border border-sky-200 bg-sky-50/80 dark:bg-sky-900/20 dark:border-sky-700 px-4 py-3 text-sm text-sky-900 dark:text-sky-200 mb-5">
                {{ t('Global FAQs are reusable across products. Each product can choose to use all, selected, or custom FAQ items.') }}
            </div>

            <div class="space-y-5">
                <label class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        :checked="enabled"
                        @change="enabled = ($event.target as HTMLInputElement).checked"
                    />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('Enable global FAQs') }}
                    </span>
                </label>

                <label class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        :checked="expandAll"
                        @change="expandAll = ($event.target as HTMLInputElement).checked"
                    />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t("Expand all global FAQ's by default") }}
                    </span>
                </label>

                <div class="space-y-4">
                    <div
                        v-for="(item, index) in items"
                        :key="item.id"
                        class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/70 dark:bg-gray-900/30"
                    >
                        <div class="flex items-end gap-3 mb-3">
                            <div class="flex gap-1">
                                <button
                                    type="button"
                                    class="px-2 py-2 text-sm rounded border border-gray-300 dark:border-gray-600 hover:border-indigo-500"
                                    :disabled="index === 0"
                                    @click="moveItem(index, -1)"
                                >
                                    ↑
                                </button>
                                <button
                                    type="button"
                                    class="px-2 py-2 text-sm rounded border border-gray-300 dark:border-gray-600 hover:border-indigo-500"
                                    :disabled="index === items.length - 1"
                                    @click="moveItem(index, 1)"
                                >
                                    ↓
                                </button>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ t('Question Title') }}
                                </label>
                                <input
                                    v-model="item.question"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                                    :placeholder="t('Enter FAQ question')"
                                />
                            </div>
                            <button
                                type="button"
                                class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:bg-red-700 h-[38px]"
                                @click="removeItem(index)"
                            >
                                {{ t('Remove') }}
                            </button>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('Answer Description') }}
                            </label>
                            <TiptapEditor
                                v-model="item.answer"
                                :placeholder="t('Enter FAQ answer...')"
                            />
                        </div>

                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input
                                v-model="item.open"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            {{ t('Open by default') }}
                        </label>
                    </div>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center px-4 py-2 rounded-md bg-sky-600 text-white font-medium hover:bg-sky-700"
                    @click="addItem"
                >
                    + {{ t('Add Global FAQ') }}
                </button>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
                type="button"
                @click="$emit('save')"
                :disabled="saving"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <span v-if="saving" class="flex items-center">
                    <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                    {{ t('Saving...') }}
                </span>
                <span v-else>{{ t('Save Settings') }}</span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useTranslation } from '../../../composables/useTranslation';
import TiptapEditor from '../../../components/TiptapEditor.ts';

interface Setting {
    key: string;
    value: any;
    type: string;
    label: string;
    description: string;
}

interface GlobalFaqItem {
    id: string;
    question: string;
    answer: string;
    open?: boolean;
}

interface Props {
    settings: Record<string, Setting>;
    saving: boolean;
    group: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const { t } = useTranslation();

const ensureItem = (item: any, index: number): GlobalFaqItem => ({
    id: String(item?.id || `global-faq-${Date.now()}-${index}`),
    question: String(item?.question || ''),
    answer: String(item?.answer || ''),
    open: Boolean(item?.open),
});

const items = ref<GlobalFaqItem[]>([]);
const syncingFromProps = ref(false);

const normalizeItems = (value: any): GlobalFaqItem[] =>
    Array.isArray(value) ? value.map((item, index) => ensureItem(item, index)) : [];

const itemsEqual = (a: GlobalFaqItem[], b: GlobalFaqItem[]) => JSON.stringify(a) === JSON.stringify(b);

watch(
    () => props.settings?.global_faqs_items?.value,
    (raw) => {
        const normalized = normalizeItems(raw);
        if (itemsEqual(items.value, normalized)) return;
        syncingFromProps.value = true;
        items.value = normalized;
        queueMicrotask(() => {
            syncingFromProps.value = false;
        });
    },
    { immediate: true }
);

const enabled = computed({
    get: () => Boolean(props.settings?.global_faqs_enabled?.value ?? true),
    set: (value: boolean) => emit('update', props.group, 'global_faqs_enabled', value),
});

const expandAll = computed({
    get: () => Boolean(props.settings?.global_faqs_expand_all?.value ?? false),
    set: (value: boolean) => emit('update', props.group, 'global_faqs_expand_all', value),
});

watch(
    items,
    (value) => {
        if (syncingFromProps.value) return;
        const payload = value.map((item) => ({
            id: item.id,
            question: item.question,
            answer: item.answer,
            open: Boolean(item.open),
        }));
        const current = normalizeItems(props.settings?.global_faqs_items?.value).map((item) => ({
            id: item.id,
            question: item.question,
            answer: item.answer,
            open: Boolean(item.open),
        }));
        if (JSON.stringify(payload) === JSON.stringify(current)) return;
        emit(
            'update',
            props.group,
            'global_faqs_items',
            payload
        );
    },
    { deep: true }
);

const addItem = () => {
    items.value.push({
        id: `global-faq-${Date.now()}-${items.value.length + 1}`,
        question: '',
        answer: '',
        open: false,
    });
};

const removeItem = (index: number) => {
    items.value.splice(index, 1);
};

const moveItem = (index: number, direction: -1 | 1) => {
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= items.value.length) return;
    const current = items.value[index];
    items.value[index] = items.value[targetIndex];
    items.value[targetIndex] = current;
};
</script>
