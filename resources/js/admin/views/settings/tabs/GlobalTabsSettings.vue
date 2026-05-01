<template>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ t('Global Tabs') }}
            </h3>

            <div class="rounded-lg border border-sky-200 bg-sky-50/80 dark:bg-sky-900/20 dark:border-sky-700 px-4 py-3 text-sm text-sky-900 dark:text-sky-200 mb-5">
                {{ t('Global tabs are reusable across products. Each product can choose which global tabs to display and add custom tabs.') }}
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
                        {{ t('Enable global tabs') }}
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
                                    {{ t('Tab Title') }}
                                </label>
                                <input
                                    v-model="item.title"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                                    :placeholder="t('Global tab title')"
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
                                {{ t('Tab Content') }}
                            </label>
                            <TiptapEditor
                                v-model="item.content"
                                :placeholder="t('Tab content...')"
                            />
                        </div>

                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input
                                :checked="Boolean(item.active_default)"
                                type="radio"
                                name="global-tabs-default"
                                class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                @change="setDefaultTab(item.id)"
                            />
                            {{ t('Active by default on frontend') }}
                        </label>
                    </div>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center px-4 py-2 rounded-md bg-sky-600 text-white font-medium hover:bg-sky-700"
                    @click="addItem"
                >
                    + {{ t('Add Global Tab') }}
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

interface GlobalTabItem {
    id: string;
    title: string;
    content: string;
    active_default?: boolean;
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

const ensureItem = (item: any, index: number): GlobalTabItem => ({
    id: String(item?.id || `global-tab-${Date.now()}-${index}`),
    title: String(item?.title || ''),
    content: String(item?.content || ''),
    active_default: Boolean(item?.active_default),
});

const normalizeSingleDefault = (list: GlobalTabItem[]): GlobalTabItem[] => {
    if (!list.length) return [];
    const firstActiveIndex = list.findIndex((item) => Boolean(item.active_default));
    return list.map((item, index) => ({
        ...item,
        active_default: firstActiveIndex >= 0 ? index === firstActiveIndex : index === 0,
    }));
};

const items = ref<GlobalTabItem[]>([]);
const syncingFromProps = ref(false);

const normalizeItems = (value: any): GlobalTabItem[] =>
    Array.isArray(value) ? value.map((item, index) => ensureItem(item, index)) : [];

const itemsEqual = (a: GlobalTabItem[], b: GlobalTabItem[]) => JSON.stringify(a) === JSON.stringify(b);

watch(
    () => props.settings?.global_tabs_items?.value,
    (raw) => {
        const normalized = normalizeSingleDefault(normalizeItems(raw));
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
    get: () => Boolean(props.settings?.global_tabs_enabled?.value ?? true),
    set: (value: boolean) => emit('update', props.group, 'global_tabs_enabled', value),
});

watch(
    items,
    (value) => {
        if (syncingFromProps.value) return;
        const payload = value.map((item) => ({
            id: item.id,
            title: item.title,
            content: item.content,
            active_default: Boolean(item.active_default),
        }));
        const current = normalizeItems(props.settings?.global_tabs_items?.value).map((item) => ({
            id: item.id,
            title: item.title,
            content: item.content,
            active_default: Boolean(item.active_default),
        }));
        if (JSON.stringify(payload) === JSON.stringify(current)) return;
        emit(
            'update',
            props.group,
            'global_tabs_items',
            payload
        );
    },
    { deep: true }
);

const addItem = () => {
    items.value.push({
        id: `global-tab-${Date.now()}-${items.value.length + 1}`,
        title: '',
        content: '',
        active_default: items.value.length === 0,
    });
};

const removeItem = (index: number) => {
    items.value.splice(index, 1);
    if (!items.value.length) return;
    if (!items.value.some((item) => Boolean(item.active_default))) {
        items.value = items.value.map((item, idx) => ({
            ...item,
            active_default: idx === 0,
        }));
    }
};

const moveItem = (index: number, direction: -1 | 1) => {
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= items.value.length) return;
    const current = items.value[index];
    items.value[index] = items.value[targetIndex];
    items.value[targetIndex] = current;
};

const setDefaultTab = (id: string) => {
    items.value = items.value.map((item) => ({
        ...item,
        active_default: item.id === id,
    }));
};
</script>
