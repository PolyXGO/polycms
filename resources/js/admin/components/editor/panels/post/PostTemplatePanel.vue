<template>
    <div v-if="form" class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">Content Template</label>
            <select
                v-model="form.layout_template_id"
                :disabled="loadingTemplates"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition-shadow focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                @change="handleTemplateChange"
            >
                <option :value="null">None</option>
                <option v-for="template in templates" :key="template.id" :value="template.id">
                    {{ template.name }}
                </option>
            </select>
            <div class="mt-1 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span>Choose a reusable landing template for this {{ contentTypeLabel }}.</span>
                <router-link
                    :to="{ name: 'admin.appearance.templates.index' }"
                    class="shrink-0 text-indigo-600 hover:text-indigo-800 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                    target="_blank"
                >
                    Manage &rarr;
                </router-link>
            </div>
        </div>

        <div
            v-if="selectedTemplate"
            class="space-y-3 rounded-2xl border border-gray-200 bg-white/70 p-4 dark:border-gray-700 dark:bg-gray-800/60"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedTemplate.name }}</div>
                    <p v-if="selectedTemplate.description" class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">
                        {{ selectedTemplate.description }}
                    </p>
                </div>
                <span
                    v-if="selectedTemplate.is_system"
                    class="rounded-full bg-indigo-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200"
                >
                    Default
                </span>
            </div>

            <div class="grid gap-2 text-xs text-gray-500 dark:text-gray-400">
                <div><span class="font-semibold text-gray-700 dark:text-gray-300">Category:</span> {{ selectedTemplate.category || 'general' }}</div>
                <div><span class="font-semibold text-gray-700 dark:text-gray-300">Source:</span> {{ selectedTemplate.source_name || selectedTemplate.source_type || 'Custom' }}</div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="applyingTemplate"
                    @click="applyTemplateToContent"
                >
                    {{ applyingTemplate ? 'Applying...' : 'Apply To Editor' }}
                </button>
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition-colors hover:border-indigo-500 hover:text-indigo-600 dark:border-gray-600 dark:text-gray-200 dark:hover:border-indigo-400 dark:hover:text-indigo-300"
                    @click="clearTemplate"
                >
                    Clear Template
                </button>
            </div>
        </div>

        <div
            v-if="form.layout_template_id && form.layout !== 'landing'"
            class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-xs text-sky-800 dark:border-sky-900/60 dark:bg-sky-950/30 dark:text-sky-200"
        >
            Templates are rendered with the landing layout. The layout will be switched to `landing` automatically when a template is selected.
        </div>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, inject, isRef, onMounted, ref, watch } from 'vue';
import { useDialog } from '@/admin/composables/useDialog';
import { EditorContextKey } from '../../../../editor/context';

interface LayoutTemplateSummary {
    id: number;
    name: string;
    description?: string | null;
    category?: string | null;
    source_type?: string | null;
    source_name?: string | null;
    is_system?: boolean;
}

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('PostTemplatePanel must be used within editor context');
}

const dialog = useDialog();
const form = context.form;

const rawHtml = context.state?.contentHtml;
const rawJson = context.state?.contentRaw;
const contentHtml = isRef(rawHtml) ? rawHtml : ref(rawHtml ?? '');
const contentRaw = isRef(rawJson) ? rawJson : ref(rawJson ?? null);

if (!isRef(rawHtml) && context.state) {
    context.state.contentHtml = contentHtml;
}

if (!isRef(rawJson) && context.state) {
    context.state.contentRaw = contentRaw;
}

const templates = ref<LayoutTemplateSummary[]>([]);
const applyingTemplate = ref(false);
const loadingTemplates = ref(false);

const contentTypeLabel = computed(() => (form.value?.type === 'page' ? 'page' : 'post'));
const selectedTemplate = computed<LayoutTemplateSummary | null>(() => {
    const currentId = Number(form.value?.layout_template_id || 0);
    if (!currentId) {
        return null;
    }

    return templates.value.find((template) => template.id === currentId) || null;
});

const extractTemplateItems = (responseData: any): LayoutTemplateSummary[] => {
    if (Array.isArray(responseData?.data)) {
        return responseData.data;
    }

    if (Array.isArray(responseData?.data?.data)) {
        return responseData.data.data;
    }

    return [];
};

const loadTemplates = async () => {
    loadingTemplates.value = true;
    try {
        const response = await axios.get('/api/v1/layout-assets', {
            params: {
                kind: 'template',
                applies_to: form.value?.type || 'post',
                per_page: 100,
                sort_by: 'name',
                sort_order: 'asc',
            },
        });

        templates.value = extractTemplateItems(response.data);
    } catch (error) {
        console.error('Failed to load templates', error);
    } finally {
        loadingTemplates.value = false;
    }
};

const handleTemplateChange = () => {
    if (form.value?.layout_template_id) {
        form.value.layout = 'landing';
    }
};

const isEditorEmpty = () => {
    const html = `${contentHtml.value || ''}`.trim();
    if (!html || html === '<p></p>' || html === '<p><br></p>') {
        return true;
    }

    const json = contentRaw.value;
    if (!json) {
        return true;
    }

    if (Array.isArray(json?.content) && json.content.length === 0) {
        return true;
    }

    return false;
};

const applyTemplateToContent = async () => {
    if (!form.value?.layout_template_id) {
        return;
    }

    if (!isEditorEmpty()) {
        const confirmed = await dialog.confirm({
            title: 'Replace current content?',
            message: 'Applying this template will replace the current landing content in the editor.',
            confirmText: 'Apply Template',
            cancelText: 'Cancel',
            type: 'warning',
        });

        if (!confirmed) {
            return;
        }
    }

    applyingTemplate.value = true;
    try {
        const response = await axios.get(`/api/v1/layout-assets/${form.value.layout_template_id}`);
        const template = response.data?.data;

        contentRaw.value = template?.content_raw || null;
        contentHtml.value = template?.content_html || '';
        form.value.layout = 'landing';

        dialog.success('Template applied to the editor');
    } catch (error: any) {
        console.error('Failed to apply template', error);
        dialog.error(error?.response?.data?.message || 'Failed to apply template');
    } finally {
        applyingTemplate.value = false;
    }
};

const clearTemplate = () => {
    form.value.layout_template_id = null;
};

onMounted(() => {
    loadTemplates();
});

watch(
    () => form.value?.type,
    () => {
        loadTemplates();
    }
);

watch(
    () => form.value?.layout_template_id,
    (value) => {
        if (value) {
            form.value.layout = 'landing';
        }
    }
);
</script>
