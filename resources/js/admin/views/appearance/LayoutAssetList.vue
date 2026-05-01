<template>
    <div class="space-y-6">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-indigo-500">Appearance</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ kindLabel }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-gray-600 dark:text-gray-400">
                    {{ description }}
                </p>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
                @click="createAsset"
            >
                Add {{ kindSingleLabel }}
            </button>
        </header>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Filters</div>
                    <div class="space-y-2">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-semibold transition-colors"
                            :class="activeCategory === 'all'
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700/60'"
                            @click="setCategory('all')"
                        >
                            <span>All {{ kindLabel }}</span>
                            <span
                                class="inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-bold"
                                :class="activeCategory === 'all'
                                    ? 'bg-white/15 text-white'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                            >
                                {{ totalCount }}
                            </span>
                        </button>

                        <button
                            v-for="category in categories"
                            :key="category.name"
                            type="button"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-semibold transition-colors"
                            :class="activeCategory === category.name
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700/60'"
                            @click="setCategory(category.name)"
                        >
                            <span>{{ category.label }}</span>
                            <span
                                class="inline-flex min-w-[1.75rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-bold"
                                :class="activeCategory === category.name
                                    ? 'bg-white/15 text-white'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                            >
                                {{ category.count }}
                            </span>
                        </button>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Source</div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="option in sourceOptions"
                            :key="option.value"
                            type="button"
                            class="rounded-lg border px-3 py-2 text-sm font-semibold transition-colors"
                            :class="filters.source === option.value
                                ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:border-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-300'
                                : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                            @click="setSource(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>
            </aside>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-col gap-4 border-b border-gray-200 p-5 dark:border-gray-700 lg:flex-row lg:items-center lg:justify-between">
                    <label class="relative block flex-1">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.2-5.2M16 10.5a5.5 5.5 0 11-11 0 5.5 5.5 0 0111 0z" />
                        </svg>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search by name, slug or description"
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-11 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                            @input="handleSearch"
                        />
                    </label>

                    <div class="inline-flex overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600">
                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center transition-colors"
                            :class="viewMode === 'grid'
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white text-gray-600 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                            @click="viewMode = 'grid'"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h7v7H4V5zm9 0h7v7h-7V5zM4 14h7v5H4v-5zm9 0h7v5h-7v-5z" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center border-l border-gray-300 transition-colors dark:border-gray-600"
                            :class="viewMode === 'list'
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white text-gray-600 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                            @click="viewMode = 'list'"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div v-if="loading" class="flex flex-col items-center justify-center gap-3 p-12 text-center">
                    <div class="h-8 w-8 animate-spin rounded-full border-2 border-indigo-500 border-t-transparent"></div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Loading {{ kindLabel.toLowerCase() }}...</p>
                </div>

                <div v-else-if="assets.length === 0" class="p-12 text-center">
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">No {{ kindLabel.toLowerCase() }} found</div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Create your first {{ kindSingleLabel.toLowerCase() }} or adjust the filters.
                    </p>
                </div>

                <div v-else-if="viewMode === 'grid'" class="grid gap-5 p-5 md:grid-cols-2 2xl:grid-cols-3">
                    <article
                        v-for="asset in assets"
                        :key="asset.id"
                        class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/30"
                    >
                        <div
                            class="relative aspect-[16/10] w-full cursor-pointer overflow-hidden border-b border-gray-200 bg-gray-100 text-left dark:border-gray-700 dark:bg-gray-900/50"
                            role="button"
                            tabindex="0"
                            @click="openAsset(asset)"
                            @keydown.enter.prevent="openAsset(asset)"
                            @keydown.space.prevent="openAsset(asset)"
                        >
                            <LayoutAssetPreviewFrame
                                :src="asset.preview_url || ''"
                                :html="asset.content_html"
                                :fallback-label="asset.category || kindSingleLabel"
                                :content-kind="asset.kind"
                                :fit-mode="kind === 'template' ? 'contain' : 'width'"
                                :viewport-width="1440"
                                :viewport-height="1080"
                            />
                            <span
                                v-if="asset.is_system"
                                class="absolute left-3 top-3 inline-flex rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-700 shadow-sm dark:bg-gray-900/80 dark:text-gray-200"
                            >
                                Default
                            </span>
                        </div>

                        <div class="space-y-4 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ asset.name }}</h3>
                                    <p class="mt-1 break-all text-xs text-gray-500 dark:text-gray-400">/{{ asset.slug }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-gray-200 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    {{ asset.category || 'general' }}
                                </span>
                            </div>

                            <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">
                                {{ asset.description || fallbackDescription(asset) }}
                            </p>

                            <div v-if="asset.kind === 'template' && asset.applies_to?.length" class="flex flex-wrap gap-2">
                                <span
                                    v-for="applyTarget in asset.applies_to"
                                    :key="applyTarget"
                                    class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"
                                >
                                    {{ applyTarget }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-indigo-600 transition-colors hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200"
                                    @click="openAsset(asset)"
                                >
                                    {{ asset.is_system ? 'Preview' : 'Edit' }}
                                </button>
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                                    @click="duplicateAsset(asset)"
                                >
                                    Duplicate
                                </button>
                                <button
                                    v-if="!asset.is_system"
                                    type="button"
                                    class="text-sm font-semibold text-red-600 transition-colors hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    @click="deleteAsset(asset)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <article
                        v-for="asset in assets"
                        :key="asset.id"
                        class="flex flex-col gap-4 p-5 xl:flex-row xl:items-center"
                    >
                        <div
                            class="relative h-24 w-full shrink-0 cursor-pointer overflow-hidden rounded-lg border border-gray-200 bg-gray-100 text-left dark:border-gray-700 dark:bg-gray-900/50 xl:w-48"
                            role="button"
                            tabindex="0"
                            @click="openAsset(asset)"
                            @keydown.enter.prevent="openAsset(asset)"
                            @keydown.space.prevent="openAsset(asset)"
                        >
                            <LayoutAssetPreviewFrame
                                :src="asset.preview_url || ''"
                                :html="asset.content_html"
                                :fallback-label="kindSingleLabel"
                                :content-kind="asset.kind"
                                :fit-mode="kind === 'template' ? 'contain' : 'width'"
                                :viewport-width="1440"
                                :viewport-height="1080"
                            />
                        </div>

                        <div class="min-w-0 flex-1 space-y-3">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ asset.name }}</h3>
                                    <p class="mt-1 break-all text-xs text-gray-500 dark:text-gray-400">/{{ asset.slug }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-gray-200 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        {{ asset.category || 'general' }}
                                    </span>
                                    <span
                                        v-if="asset.is_system"
                                        class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"
                                    >
                                        Default
                                    </span>
                                </div>
                            </div>

                            <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">
                                {{ asset.description || fallbackDescription(asset) }}
                            </p>

                            <div v-if="asset.kind === 'template' && asset.applies_to?.length" class="flex flex-wrap gap-2">
                                <span
                                    v-for="applyTarget in asset.applies_to"
                                    :key="applyTarget"
                                    class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"
                                >
                                    {{ applyTarget }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 xl:justify-end">
                            <button
                                type="button"
                                class="text-sm font-semibold text-indigo-600 transition-colors hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200"
                                @click="openAsset(asset)"
                            >
                                {{ asset.is_system ? 'Preview' : 'Edit' }}
                            </button>
                            <button
                                type="button"
                                class="text-sm font-semibold text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                                @click="duplicateAsset(asset)"
                            >
                                Duplicate
                            </button>
                            <button
                                v-if="!asset.is_system"
                                type="button"
                                class="text-sm font-semibold text-red-600 transition-colors hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                @click="deleteAsset(asset)"
                            >
                                Delete
                            </button>
                        </div>
                    </article>
                </div>

                <div
                    v-if="meta.last_page > 1"
                    class="flex items-center justify-between border-t border-gray-200 px-5 py-4 dark:border-gray-700"
                >
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        :disabled="meta.current_page <= 1"
                        @click="changePage(meta.current_page - 1)"
                    >
                        Previous
                    </button>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        :disabled="meta.current_page >= meta.last_page"
                        @click="changePage(meta.current_page + 1)"
                    >
                        Next
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import LayoutAssetPreviewFrame from '@/admin/components/appearance/LayoutAssetPreviewFrame.vue';
import { useDialog } from '@/admin/composables/useDialog';
import type { LayoutAssetSummary } from '@/admin/editor/layoutAssets';

const props = defineProps<{
    kind: 'part' | 'template';
}>();

const router = useRouter();
const dialog = useDialog();

const assets = ref<LayoutAssetSummary[]>([]);
const loading = ref(false);
const viewMode = ref<'grid' | 'list'>('grid');
const activeCategory = ref('all');
const filters = ref({
    search: '',
    source: 'all',
});
const meta = ref({
    total: 0,
    per_page: 18,
    current_page: 1,
    last_page: 1,
});
let searchTimer: ReturnType<typeof setTimeout> | null = null;

const kindLabel = computed(() => (props.kind === 'template' ? 'Templates' : 'Template Parts'));
const kindSingleLabel = computed(() => (props.kind === 'template' ? 'Template' : 'Template Part'));
const description = computed(() =>
    props.kind === 'template'
        ? 'Build reusable page-level templates and apply them across pages or posts.'
        : 'Manage reusable landing parts that can be inserted into pages from the landing picker.'
);
const totalCount = computed(() => meta.value.total || assets.value.length);
const createRouteName = computed(() => (props.kind === 'template' ? 'admin.appearance.templates.create' : 'admin.appearance.parts.create'));
const editRouteName = computed(() => (props.kind === 'template' ? 'admin.appearance.templates.edit' : 'admin.appearance.parts.edit'));

const sourceOptions = [
    { value: 'all', label: 'All' },
    { value: 'system', label: 'Default' },
    { value: 'custom', label: 'Custom' },
];

const categories = computed(() => {
    const counts = new Map<string, number>();
    assets.value.forEach((asset) => {
        const key = asset.category || 'general';
        counts.set(key, (counts.get(key) || 0) + 1);
    });

    return Array.from(counts.entries())
        .map(([name, count]) => ({
            name,
            label: name.replace(/[-_]/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase()),
            count,
        }))
        .sort((a, b) => a.label.localeCompare(b.label));
});

const extractAssetItems = (responseData: any): LayoutAssetSummary[] => {
    if (Array.isArray(responseData?.data)) {
        return responseData.data;
    }

    if (Array.isArray(responseData?.data?.data)) {
        return responseData.data.data;
    }

    return [];
};

const normalizePositiveInteger = (value: unknown, fallback: number) => {
    const candidate = Array.isArray(value) ? value[0] : value;
    const parsed = Number(candidate);

    if (Number.isFinite(parsed) && parsed > 0) {
        return Math.trunc(parsed);
    }

    return fallback;
};

const resetListState = () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
        searchTimer = null;
    }

    assets.value = [];
    activeCategory.value = 'all';
    filters.value = {
        search: '',
        source: 'all',
    };
    meta.value = {
        total: 0,
        per_page: 18,
        current_page: 1,
        last_page: 1,
    };
};

const fetchAssets = async (page = 1) => {
    loading.value = true;
    try {
        const perPage = normalizePositiveInteger(meta.value.per_page, 18);
        const currentPage = normalizePositiveInteger(page, 1);
        const response = await axios.get('/api/v1/layout-assets', {
            params: {
                kind: props.kind,
                page: currentPage,
                per_page: perPage,
                search: filters.value.search || undefined,
                category: activeCategory.value === 'all' ? undefined : activeCategory.value,
                source: filters.value.source,
                sort_by: 'updated_at',
                sort_order: 'desc',
            },
        });

        assets.value = extractAssetItems(response.data);
        meta.value = {
            total: normalizePositiveInteger(response.data?.meta?.total, assets.value.length || 0),
            per_page: normalizePositiveInteger(response.data?.meta?.per_page, perPage),
            current_page: normalizePositiveInteger(response.data?.meta?.current_page, currentPage),
            last_page: normalizePositiveInteger(response.data?.meta?.last_page, 1),
        };
    } catch (error: any) {
        console.error('Failed to load layout assets', error);
        dialog.error(error?.response?.data?.message || `Failed to load ${kindLabel.value.toLowerCase()}`);
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        meta.value.current_page = 1;
        fetchAssets(1);
    }, 250);
};

const setCategory = (category: string) => {
    activeCategory.value = category;
    meta.value.current_page = 1;
    fetchAssets(1);
};

const setSource = (source: string) => {
    filters.value.source = source;
    meta.value.current_page = 1;
    fetchAssets(1);
};

const changePage = (page: number) => {
    if (page < 1 || page > meta.value.last_page) {
        return;
    }

    fetchAssets(page);
};

const createAsset = () => {
    router.push({ name: createRouteName.value });
};

const openAsset = (asset: LayoutAssetSummary) => {
    router.push({
        name: editRouteName.value,
        params: { id: asset.id },
    });
};

const duplicateAsset = async (asset: LayoutAssetSummary) => {
    try {
        const response = await axios.post(`/api/v1/layout-assets/${asset.id}/duplicate`);
        dialog.success(`${kindSingleLabel.value} duplicated successfully`);
        router.push({
            name: editRouteName.value,
            params: { id: response.data?.data?.id },
        });
    } catch (error: any) {
        console.error('Failed to duplicate layout asset', error);
        dialog.error(error?.response?.data?.message || `Failed to duplicate ${kindSingleLabel.value.toLowerCase()}`);
    }
};

const deleteAsset = async (asset: LayoutAssetSummary) => {
    const confirmed = await dialog.confirm({
        title: `Delete ${kindSingleLabel.value}`,
        message: `Are you sure you want to delete "${asset.name}"?`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });

    if (!confirmed) {
        return;
    }

    try {
        await axios.delete(`/api/v1/layout-assets/${asset.id}`);
        dialog.success(`${kindSingleLabel.value} deleted successfully`);
        fetchAssets(meta.value.current_page);
    } catch (error: any) {
        console.error('Failed to delete layout asset', error);
        dialog.error(error?.response?.data?.message || `Failed to delete ${kindSingleLabel.value.toLowerCase()}`);
    }
};

const fallbackDescription = (asset: LayoutAssetSummary) => {
    if (asset.kind === 'template') {
        const applies = asset.applies_to?.length ? asset.applies_to.join(', ') : 'page';
        return `Reusable landing template for ${applies}.`;
    }

    return 'Reusable landing part built from core landing elements.';
};

watch(
    () => props.kind,
    () => {
        resetListState();
        fetchAssets(1);
    },
    { immediate: true }
);
</script>
