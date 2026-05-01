<template>
    <div class="space-y-6">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-indigo-500">{{ kindLabel }}</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ isEdit ? `Edit ${kindSingleLabel}` : `New ${kindSingleLabel}` }}
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    @click="router.push({ name: indexRouteName })"
                >
                    Back
                </button>
                <button
                    v-if="isEdit"
                    type="button"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    :disabled="loading"
                    @click="duplicateAsset"
                >
                    Duplicate
                </button>
                <button
                    v-if="!form.is_system"
                    type="button"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="loading"
                    @click="saveAsset"
                >
                    {{ loading ? 'Saving...' : `Save ${kindSingleLabel}` }}
                </button>
            </div>
        </header>

        <form @submit.prevent="saveAsset">
            <EditorPanelLayout :type="panelType" :components="panelComponents" />
        </form>

        <div class="fixed bottom-8 z-40 hidden flex-col gap-3 lg:flex" :style="floatingActionsStyle">
            <button
                v-if="!form.is_system"
                type="button"
                class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="loading"
                :title="loading ? 'Saving…' : `Save ${kindSingleLabel}`"
                @click="saveAsset"
            >
                <svg v-if="!loading" class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 21H5C4.44772 21 4 20.5523 4 20V4C4 3.44772 4.44772 3 5 3H16L20 7V20C20 20.5523 19.5523 21 19 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M17 21V15H7V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <svg v-else class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>

        <LandingBlockOptionsPanel />
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, provide, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import EditorPanelLayout from '@/admin/components/editor/EditorPanelLayout.vue';
import LandingBlockOptionsPanel from '@/admin/components/editor/panels/LandingBlockOptionsPanel.vue';
import LayoutAssetPrimaryPanel from '@/admin/components/editor/panels/layouts/LayoutAssetPrimaryPanel.vue';
import LayoutAssetSettingsPanel from '@/admin/components/editor/panels/layouts/LayoutAssetSettingsPanel.vue';
import { useDialog } from '@/admin/composables/useDialog';
import { useSlugify } from '@/admin/composables/useSlugify';
import { EditorContextKey, createEditorContext } from '@/admin/editor/context';
import { useLandingStore } from '@/admin/stores/landingStore';

const props = defineProps<{
    kind: 'part' | 'template';
}>();

const route = useRoute();
const router = useRouter();
const dialog = useDialog();
const { slugify } = useSlugify();
const landingStore = useLandingStore();

const loading = ref(false);
const slugManuallyEdited = ref(false);
const contentHtml = ref<string>('');
const contentRaw = ref<any>(null);

const defaultFormState = (kind: 'part' | 'template') => ({
    kind,
    id: null as number | null,
    name: '',
    slug: '',
    category: kind === 'template' ? 'landing' : 'general',
    description: '',
    layout: 'landing',
    meta: {} as Record<string, any>,
    preview_url: null as string | null,
    applies_to: kind === 'template' ? ['page'] : [],
    is_system: false,
    source_type: 'custom',
    source_name: 'Custom',
    assigned_posts_count: 0,
});

const form = ref(defaultFormState(props.kind));

const panelType = computed(() => (props.kind === 'template' ? 'layout-template' : 'layout-part'));
const kindLabel = computed(() => (props.kind === 'template' ? 'Templates' : 'Template Parts'));
const kindSingleLabel = computed(() => (props.kind === 'template' ? 'Template' : 'Template Part'));
const isEdit = computed(() => !!route.params.id);
const indexRouteName = computed(() => (props.kind === 'template' ? 'admin.appearance.templates.index' : 'admin.appearance.parts.index'));
const editRouteName = computed(() => (props.kind === 'template' ? 'admin.appearance.templates.edit' : 'admin.appearance.parts.edit'));

const panelComponents = {
    primary: LayoutAssetPrimaryPanel,
    settings: LayoutAssetSettingsPanel,
};

const generateSlug = () => {
    if (isEdit.value || slugManuallyEdited.value || form.value.is_system) {
        return;
    }

    form.value.slug = form.value.name ? slugify(form.value.name) : '';
};

const onSlugInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.value || target.value.trim() === '') {
        slugManuallyEdited.value = false;
        form.value.slug = form.value.name ? slugify(form.value.name) : '';
        return;
    }

    slugManuallyEdited.value = true;
    form.value.slug = slugify(target.value);
};

const panelContext = createEditorContext({
    type: panelType.value,
    form,
    loading,
    helpers: {
        generateSlug,
        onSlugInput,
        save: () => saveAsset(),
    },
    state: {
        contentHtml,
        contentRaw,
    },
});

provide(EditorContextKey, panelContext);

const floatingActionsStyle = computed(() => {
    const baseOffset = 32;
    if (landingStore.activeBlock) {
        return {
            right: `${(landingStore.optionsWidth || 300) + baseOffset}px`,
        };
    }

    return {
        right: `${baseOffset}px`,
    };
});

const loadAsset = async () => {
    if (!route.params.id) {
        Object.assign(form.value, defaultFormState(props.kind));
        contentHtml.value = '';
        contentRaw.value = null;
        slugManuallyEdited.value = false;
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get(`/api/v1/layout-assets/${route.params.id}`);
        const asset = response.data?.data;

        Object.assign(form.value, defaultFormState(props.kind), asset, {
            kind: asset?.kind || props.kind,
            applies_to: Array.isArray(asset?.applies_to)
                ? asset.applies_to
                : (props.kind === 'template' ? ['page'] : []),
        });

        contentRaw.value = asset?.content_raw || null;
        contentHtml.value = asset?.content_html || '';
        slugManuallyEdited.value = true;
    } catch (error: any) {
        console.error('Failed to load layout asset', error);
        dialog.error(error?.response?.data?.message || `Failed to load ${kindSingleLabel.value.toLowerCase()}`);
        router.push({ name: indexRouteName.value });
    } finally {
        loading.value = false;
    }
};

const saveAsset = async () => {
    if (form.value.is_system) {
        dialog.warning('Duplicate this default asset before customizing it.');
        return;
    }

    loading.value = true;
    try {
        const payload: Record<string, any> = {
            kind: props.kind,
            name: form.value.name,
            slug: form.value.slug || null,
            category: form.value.category || null,
            description: form.value.description || null,
            layout: 'landing',
            content_raw: contentRaw.value,
            meta: form.value.meta || {},
        };

        if (props.kind === 'template') {
            payload.applies_to = Array.isArray(form.value.applies_to) ? form.value.applies_to : ['page'];
        }

        if (isEdit.value) {
            await axios.put(`/api/v1/layout-assets/${route.params.id}`, payload);
            await loadAsset();
            dialog.success(`${kindSingleLabel.value} updated successfully`);
        } else {
            const response = await axios.post('/api/v1/layout-assets', payload);
            const assetId = response.data?.data?.id;
            dialog.success(`${kindSingleLabel.value} created successfully`);
            await router.push({
                name: editRouteName.value,
                params: { id: assetId },
            });
        }
    } catch (error: any) {
        console.error('Failed to save layout asset', error);
        dialog.error(error?.response?.data?.message || `Failed to save ${kindSingleLabel.value.toLowerCase()}`);
    } finally {
        loading.value = false;
    }
};

const duplicateAsset = async () => {
    if (!route.params.id) {
        return;
    }

    loading.value = true;
    try {
        const response = await axios.post(`/api/v1/layout-assets/${route.params.id}/duplicate`);
        const duplicatedId = response.data?.data?.id;
        dialog.success(`${kindSingleLabel.value} duplicated successfully`);
        await router.push({
            name: editRouteName.value,
            params: { id: duplicatedId },
        });
    } catch (error: any) {
        console.error('Failed to duplicate layout asset', error);
        dialog.error(error?.response?.data?.message || `Failed to duplicate ${kindSingleLabel.value.toLowerCase()}`);
    } finally {
        loading.value = false;
    }
};

watch(
    [() => route.params.id, () => props.kind],
    () => {
        loadAsset();
    },
    { immediate: true }
);

watch(
    () => panelType.value,
    (value) => {
        landingStore.setPostType(value);
    },
    { immediate: true }
);
</script>
