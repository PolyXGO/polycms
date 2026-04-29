<template>
    <div v-if="form" class="space-y-4">
        <!-- Status Selection -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Status:</span>
                    <strong class="text-sm text-gray-900 dark:text-white">{{ statusLabel }}</strong>
                </div>
                <button type="button" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline" @click="toggleStatus">
                    Edit
                </button>
            </div>
            <div v-if="showStatusEditor" class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                <select v-model="draftStatus" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <div class="flex gap-2">
                    <button type="button" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold" @click="confirmStatus">
                        OK
                    </button>
                    <button type="button" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-semibold" @click="cancelStatus">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Content Type -->
        <div class="space-y-2" v-if="typeOptions && typeOptions.length">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Content Type:</span>
                    <strong class="text-sm text-gray-900 dark:text-white">{{ typeLabel }}</strong>
                </div>
            </div>
        </div>

        <!-- Publish Date -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Publish Date:</span>
                    <strong class="text-sm text-gray-900 dark:text-white">{{ scheduledLabel }}</strong>
                </div>
                <button type="button" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline" @click="toggleDate">
                    Edit
                </button>
            </div>
            <div v-if="showDateEditor" class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                <input v-model="draftDate" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" />
                <div class="flex gap-2">
                    <button type="button" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold" @click="confirmDate">
                        OK
                    </button>
                    <button type="button" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-semibold" @click="cancelDate">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="space-y-1 py-3 border-y border-gray-100 dark:border-gray-700">
            <div v-if="form.created_at" class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                <span class="font-medium">Created on:</span>
                <span>{{ formatDate(form.created_at) }}</span>
            </div>
            <div v-if="form.updated_at" class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                <span class="font-medium">Last updated:</span>
                <span>{{ formatDate(form.updated_at) }}</span>
            </div>
        </div>

        <!-- Main Actions -->
        <div class="flex flex-col gap-2 pt-2">
            <button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors text-sm" @click="helpers.preview?.()">
                Preview Changes
            </button>
            <button
                type="button"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold transition-colors text-sm disabled:opacity-50"
                :disabled="context.loading.value"
                @click="helpers.save?.()"
            >
                {{ context.loading.value ? 'Saving…' : 'Update' }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('PostPublishPanel must be used within editor context');
}

const form = context.form;

if (!form.value) {
    form.value = {
        title: '',
        slug: '',
        type: context.type ?? 'post',
        status: context.state?.postStatuses?.[0]?.value ?? 'draft',
        excerpt: '',
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        created_at: null,
        updated_at: null,
        published_at: null,
        scheduled_at: null,
    } as any;
}

const helpers = context.helpers ?? {};
const state = context.state ?? {};

const toLocalDateInput = (value: string) => {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }
    const offset = date.getTimezoneOffset();
    const local = new Date(date.getTime() - offset * 60 * 1000);
    return local.toISOString().slice(0, 16);
};

const showStatusEditor = ref(false);
const showDateEditor = ref(false);

const draftStatus = ref(form.value.status);
const initialDate =
    form.value.scheduled_at ??
    form.value.published_at ??
    form.value.created_at ??
    null;
const draftDate = ref<string | null>(initialDate ? toLocalDateInput(initialDate) : null);

const statusOptions = computed(() => state.postStatuses ?? [
    { label: 'Draft', value: 'draft' },
    { label: 'Published', value: 'published' },
    { label: 'Archived', value: 'archived' },
]);

const statusLabel = computed(() => {
    const current = statusOptions.value.find((option: any) => option.value === form.value.status);
    return current ? current.label : form.value.status;
});

const typeOptions = computed(() => state.postTypes ?? [
    { label: 'Standard Post', value: 'post' },
    { label: 'Page', value: 'page' },
]);

const typeLabel = computed(() => {
    const current = typeOptions.value.find((option: any) => option.value === form.value.type);
    return current ? current.label : form.value.type;
});

const scheduledLabel = computed(() => {
    if (form.value.scheduled_at) {
        return formatDate(form.value.scheduled_at);
    }
    if (form.value.published_at) {
        return formatDate(form.value.published_at);
    }
    if (form.value.created_at) {
        return formatDate(form.value.created_at);
    }
    return 'Not set';
});

const toggleStatus = () => {
    showStatusEditor.value = !showStatusEditor.value;
    draftStatus.value = form.value.status;
};

const confirmStatus = () => {
    form.value.status = draftStatus.value;
    if (form.value.status === 'published') {
        const nowIso = new Date().toISOString();
        if (!form.value.published_at || new Date(form.value.published_at).getTime() > Date.now()) {
            form.value.published_at = nowIso;
        }
        form.value.scheduled_at = null;
    } else {
        form.value.published_at = null;
    }
    showStatusEditor.value = false;
};

const cancelStatus = () => {
    showStatusEditor.value = false;
    draftStatus.value = form.value.status;
};

const toggleDate = () => {
    showDateEditor.value = !showDateEditor.value;
    const source =
        form.value.scheduled_at ??
        form.value.published_at ??
        form.value.created_at ??
        null;
    draftDate.value = source ? toLocalDateInput(source) : null;
};

const confirmDate = () => {
    if (!draftDate.value) {
        form.value.scheduled_at = null;
        form.value.published_at = null;
        showDateEditor.value = false;
        return;
    }

    const selected = new Date(draftDate.value);
    const now = new Date();
    if (Number.isNaN(selected.getTime())) {
        showDateEditor.value = false;
        return;
    }

    const iso = selected.toISOString();
    if (selected <= now) {
        form.value.published_at = iso;
        form.value.scheduled_at = null;
        if (form.value.status !== 'published') {
            form.value.status = 'published';
        }
    } else {
        form.value.scheduled_at = iso;
        form.value.published_at = null;
        if (form.value.status === 'published') {
            form.value.status = 'draft';
        }
    }

    showDateEditor.value = false;
};

const cancelDate = () => {
    showDateEditor.value = false;
    draftDate.value = form.value.scheduled_at || null;
};

const formatDate = (value: string | null | undefined) => {
    if (!value) {
        return '';
    }
    try {
        return new Date(value).toLocaleString();
    } catch (error) {
        return value;
    }
};

watch(
    () => form.value.status,
    (value) => {
        if (!showStatusEditor.value) {
            draftStatus.value = value;
        }
    }
);

watch(
    () => form.value.scheduled_at,
    (value) => {
        if (!showDateEditor.value) {
            draftDate.value = value
                ? toLocalDateInput(value)
                : form.value.published_at
                    ? toLocalDateInput(form.value.published_at)
                    : null;
        }
    }
);

watch(
    () => form.value.published_at,
    (value) => {
        if (!showDateEditor.value && !form.value.scheduled_at) {
            draftDate.value = value ? toLocalDateInput(value) : null;
        }
    }
);
</script>

