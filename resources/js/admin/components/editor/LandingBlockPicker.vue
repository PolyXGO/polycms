<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-6xl h-[85vh] max-h-[900px] overflow-hidden flex flex-col">
            <header class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button
                        v-if="presetTargetBlock"
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-indigo-500 dark:hover:text-indigo-300"
                        @click="presetTargetBlock = null"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ presetTargetBlock ? 'Choose Row Layout' : 'Add Landing Element' }}
                        </h3>
                        <p v-if="presetTargetBlock" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Pick a row preset now. You can still change it later in the options panel.</p>
                    </div>
                </div>
                <button @click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>
            
            <div class="p-6 overflow-y-auto custom-scrollbar">
                <div v-if="presetTargetBlock" class="space-y-4">
                    <RowLayoutPicker
                        size="md"
                        :modelValue="selectedRowPreset"
                        @update:modelValue="selectRowPreset"
                    />
                </div>

                <div v-else>
                    <div v-for="(blocks, category) in categorizedBlocks" :key="category" class="mb-10 last:mb-0">
                        <div class="flex items-center gap-3 mb-4 px-1">
                            <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em]">{{ getCategoryLabel(category) }}</h4>
                            <div class="h-px flex-1 bg-gradient-to-r from-indigo-100 dark:from-indigo-900/40 to-transparent"></div>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <button 
                                v-for="block in blocks" 
                                :key="block.key"
                                @click="selectBlock(block)"
                                class="landing-picker-card group"
                                :class="{ 'landing-picker-card--pattern': block.isPattern }"
                            >
                                <div v-if="block.isPattern" class="landing-picker-pattern" :class="`landing-picker-pattern--${block.key}`">
                                    <template v-if="block.key === 'hero_section'">
                                        <span class="landing-picker-pattern__hero-title"></span>
                                        <span class="landing-picker-pattern__hero-copy"></span>
                                        <span class="landing-picker-pattern__hero-copy landing-picker-pattern__hero-copy--short"></span>
                                        <span class="landing-picker-pattern__hero-btn"></span>
                                    </template>

                                    <template v-else-if="block.key === 'pricing_matrix'">
                                        <span class="landing-picker-pattern__pricing-card"></span>
                                        <span class="landing-picker-pattern__pricing-card landing-picker-pattern__pricing-card--featured"></span>
                                        <span class="landing-picker-pattern__pricing-card"></span>
                                    </template>

                                    <template v-else-if="block.key === 'what_you_get'">
                                        <span class="landing-picker-pattern__title"></span>
                                        <span class="landing-picker-pattern__copy"></span>
                                        <div class="landing-picker-pattern__list landing-picker-pattern__list--two">
                                            <span v-for="i in 4" :key="`what-left-${i}`" class="landing-picker-pattern__list-item"></span>
                                            <span v-for="i in 4" :key="`what-right-${i}`" class="landing-picker-pattern__list-item"></span>
                                        </div>
                                        <span class="landing-picker-pattern__highlight"></span>
                                    </template>

                                    <template v-else-if="block.key === 'features_grid'">
                                        <span class="landing-picker-pattern__title"></span>
                                        <div class="landing-picker-pattern__grid">
                                            <span v-for="i in 6" :key="`feature-${i}`" class="landing-picker-pattern__grid-card"></span>
                                        </div>
                                    </template>

                                    <template v-else-if="block.key === 'showcase'">
                                        <span class="landing-picker-pattern__title"></span>
                                        <div class="landing-picker-pattern__showcase">
                                            <div class="landing-picker-pattern__showcase-copy">
                                                <span class="landing-picker-pattern__showcase-head"></span>
                                                <span class="landing-picker-pattern__showcase-btn"></span>
                                                <span class="landing-picker-pattern__showcase-list"></span>
                                                <span class="landing-picker-pattern__showcase-list"></span>
                                            </div>
                                            <span class="landing-picker-pattern__showcase-media"></span>
                                        </div>
                                    </template>

                                    <template v-else-if="block.key === 'cta_section'">
                                        <span class="landing-picker-pattern__cta-box">
                                            <span class="landing-picker-pattern__cta-title"></span>
                                            <span class="landing-picker-pattern__cta-copy"></span>
                                            <span class="landing-picker-pattern__cta-stats"></span>
                                        </span>
                                    </template>
                                </div>

                                <div
                                    v-else
                                    class="landing-picker-card__icon"
                                    v-html="block.icon || defaultIcon"
                                >
                                </div>
                                
                                <div class="landing-picker-card__body">
                                    <div class="landing-picker-card__title">{{ block.label }}</div>
                                    <div v-if="block.isPattern" class="landing-picker-card__meta">Template Part</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div v-if="reusablePartItems.length" class="mb-10 last:mb-0">
                        <div class="flex items-center gap-3 mb-4 px-1">
                            <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em]">Reusable Parts</h4>
                            <div class="h-px flex-1 bg-gradient-to-r from-indigo-100 dark:from-indigo-900/40 to-transparent"></div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <div
                                v-for="part in reusablePartItems"
                                :key="`part-${part.asset.id}`"
                                class="landing-picker-card group"
                                role="button"
                                tabindex="0"
                                @click="selectReusablePart(part)"
                                @keydown.enter.prevent="selectReusablePart(part)"
                                @keydown.space.prevent="selectReusablePart(part)"
                            >
                                <div class="landing-picker-card__preview">
                                    <LayoutAssetPreviewFrame
                                        :src="part.asset.preview_url || ''"
                                        :html="part.asset.content_html"
                                        fallback-label="Part"
                                        :content-kind="part.asset.kind"
                                        fit-mode="width"
                                        :viewport-width="1440"
                                        :viewport-height="1080"
                                    />
                                    <span v-if="part.asset.is_system" class="landing-picker-card__badge">Default</span>
                                </div>

                                <div class="landing-picker-card__body">
                                    <div class="landing-picker-card__title">{{ part.asset.name }}</div>
                                    <div class="landing-picker-card__meta">{{ part.asset.category || 'Reusable part' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <footer class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700">
                <p class="text-xs text-center text-gray-500">
                    {{ presetTargetBlock ? 'Choose a row structure to insert it into your landing page.' : 'Select an element to insert it into your landing page.' }}
                </p>
            </footer>
        </div>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, computed } from 'vue';
import LayoutAssetPreviewFrame from '@/admin/components/appearance/LayoutAssetPreviewFrame.vue';
import { landingBlockRegistry, type LandingBlockDefinition } from '../../editor/landingBlockRegistry';
import RowLayoutPicker from './controls/RowLayoutPicker.vue';
import { DEFAULT_ROW_LAYOUT_PRESET, applyRowLayout } from '@/admin/editor/rowLayoutPresets';
import { createDemoShowcasePatternBlock } from '@/admin/editor/landingPatternTemplates';
import { buildReusablePartSelection, type LayoutAssetSummary, type ReusablePartSelection } from '@/admin/editor/layoutAssets';

const show = ref(false);
const presetTargetBlock = ref<LandingBlockDefinition | null>(null);
const selectedRowPreset = ref(DEFAULT_ROW_LAYOUT_PRESET);
const reusableParts = ref<LayoutAssetSummary[]>([]);
const reusablePartsLoaded = ref(false);

const emit = defineEmits<{
    (e: 'select', block: LandingBlockDefinition): void;
}>();

const categorizedBlocks = computed(() => {
    const grouped = landingBlockRegistry.getCategorized();
    const managedReusablePatternKeys = new Set<string>();

    reusableParts.value.forEach((asset) => {
        if (!asset.is_system) {
            return;
        }

        if (asset.slug === 'demo-showcase') {
            managedReusablePatternKeys.add('showcase');
        }
        if (asset.slug === 'what-you-get') {
            managedReusablePatternKeys.add('what_you_get');
        }
        if (asset.slug === 'cta-section') {
            managedReusablePatternKeys.add('cta_section');
        }
    });

    return Object.fromEntries(
        Object.entries(grouped)
            .map(([category, blocks]) => [
                category,
                blocks.filter((block) => !managedReusablePatternKeys.has(block.key)),
            ])
            .filter(([, blocks]) => blocks.length > 0)
    );
});
const reusablePartItems = computed<ReusablePartSelection[]>(() => reusableParts.value.map(buildReusablePartSelection));

const extractAssetItems = (responseData: any): LayoutAssetSummary[] => {
    if (Array.isArray(responseData?.data)) {
        return responseData.data;
    }

    if (Array.isArray(responseData?.data?.data)) {
        return responseData.data.data;
    }

    return [];
};

const getCategoryLabel = (cat: string) => {
    const labels: Record<string, string> = {
        atomic: 'Atomic Elements',
        layout: 'Layout & Grid',
        patterns: 'Template Parts',
        marketing: 'Marketing sections',
        general: 'Common Elements',
        ecommerce: 'E-commerce',
        custom: 'Custom Blocks'
    };
    return labels[cat] || cat;
}

const loadReusableParts = async () => {
    try {
        const response = await axios.get('/api/v1/layout-assets', {
            params: {
                kind: 'part',
                per_page: 100,
                sort_by: 'name',
                sort_order: 'asc',
            },
        });

        reusableParts.value = extractAssetItems(response.data);
        reusablePartsLoaded.value = true;
    } catch (error) {
        console.error('Failed to load reusable parts', error);
    }
};

const open = async () => {
    show.value = true;
    if (!reusablePartsLoaded.value) {
        await loadReusableParts();
    }
};
const close = () => {
    show.value = false;
    presetTargetBlock.value = null;
    selectedRowPreset.value = DEFAULT_ROW_LAYOUT_PRESET;
};

const selectBlock = (block: LandingBlockDefinition) => {
    if (block.key === 'row') {
        presetTargetBlock.value = block;
        selectedRowPreset.value = block.defaultAttrs?.layout_preset || DEFAULT_ROW_LAYOUT_PRESET;
        return;
    }

    if (block.key === 'showcase') {
        emit('select', {
            ...block,
            ...createDemoShowcasePatternBlock(),
        });
        close();
        return;
    }

    emit('select', block);
    close();
};

const selectReusablePart = (part: ReusablePartSelection) => {
    emit('select', part as any);
    close();
};

const selectRowPreset = (presetId: string) => {
    if (!presetTargetBlock.value) {
        return;
    }

    selectedRowPreset.value = presetId;

    emit('select', {
        ...presetTargetBlock.value,
        defaultAttrs: {
            ...(presetTargetBlock.value.defaultAttrs || {}),
            ...applyRowLayout(presetTargetBlock.value.defaultAttrs || {}, presetId),
        },
    });

    close();
};

const defaultIcon = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>`;

defineExpose({ open, close });
</script>

<style scoped>
.landing-picker-card {
    position: relative;
    display: flex;
    min-height: 15.25rem;
    flex-direction: column;
    gap: 1rem;
    border: 1px solid rgba(71, 85, 105, 0.65);
    background: rgba(31, 41, 55, 0.86);
    padding: 1rem;
    overflow: hidden;
    transition: border-color 0.22s ease, background 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
}

.landing-picker-card:hover {
    border-color: rgba(99, 102, 241, 0.9);
    background: rgba(30, 41, 59, 0.96);
    transform: translateY(-2px);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.28);
}

.landing-picker-card__icon {
    display: inline-flex;
    width: 4.25rem;
    height: 4.25rem;
    align-items: center;
    justify-content: center;
    align-self: center;
    background: rgba(51, 65, 85, 0.9);
    color: #9ca3af;
}

.landing-picker-card__preview {
    position: relative;
    min-height: 8.8rem;
    border: 1px solid rgba(71, 85, 105, 0.55);
    background-color: rgba(15, 23, 42, 0.56);
    overflow: hidden;
}

.landing-picker-card__preview-fallback {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(226, 232, 240, 0.72);
}

.landing-picker-card__badge {
    position: absolute;
    top: 0.7rem;
    right: 0.7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.28rem 0.5rem;
    background: rgba(79, 70, 229, 0.92);
    color: #fff;
    font-size: 0.58rem;
    font-weight: 900;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.landing-picker-card__body {
    margin-top: auto;
    text-align: center;
}

.landing-picker-card__title {
    font-size: 0.92rem;
    color: #f8fafc;
    transition: color 0.22s ease;
}

.landing-picker-card__meta {
    margin-top: 0.5rem;
    font-size: 0.58rem;
    font-weight: 900;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: #818cf8;
}

.landing-picker-card:hover .landing-picker-card__title {
    color: #a5b4fc;
}

.landing-picker-pattern {
    display: flex;
    min-height: 8.7rem;
    flex-direction: column;
    gap: 0.55rem;
    border: 1px solid rgba(71, 85, 105, 0.55);
    background: rgba(17, 24, 39, 0.44);
    padding: 0.8rem;
}

.landing-picker-pattern__title,
.landing-picker-pattern__copy,
.landing-picker-pattern__hero-title,
.landing-picker-pattern__hero-copy,
.landing-picker-pattern__hero-btn,
.landing-picker-pattern__highlight,
.landing-picker-pattern__cta-title,
.landing-picker-pattern__cta-copy,
.landing-picker-pattern__cta-stats,
.landing-picker-pattern__showcase-head,
.landing-picker-pattern__showcase-btn,
.landing-picker-pattern__showcase-list {
    display: block;
}

.landing-picker-pattern__title,
.landing-picker-pattern__hero-title,
.landing-picker-pattern__cta-title,
.landing-picker-pattern__showcase-head {
    height: 0.9rem;
    background: rgba(248, 250, 252, 0.92);
}

.landing-picker-pattern__copy,
.landing-picker-pattern__hero-copy,
.landing-picker-pattern__cta-copy {
    height: 0.45rem;
    background: rgba(148, 163, 184, 0.65);
}

.landing-picker-pattern__hero-copy--short {
    width: 74%;
}

.landing-picker-pattern__hero-btn,
.landing-picker-pattern__showcase-btn {
    width: 4.4rem;
    height: 1.05rem;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
}

.landing-picker-pattern__pricing-card {
    flex: 1;
    border: 1px solid rgba(148, 163, 184, 0.22);
    background: rgba(255, 255, 255, 0.92);
}

.landing-picker-pattern__pricing-card--featured {
    border-color: rgba(99, 102, 241, 0.6);
    background: linear-gradient(180deg, rgba(99, 102, 241, 0.12) 0%, rgba(255, 255, 255, 0.95) 100%);
}

.landing-picker-pattern--pricing_matrix {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.5rem;
    align-items: stretch;
}

.landing-picker-pattern__list {
    display: grid;
    gap: 0.38rem 0.6rem;
}

.landing-picker-pattern__list--two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.landing-picker-pattern__list-item {
    display: block;
    height: 0.55rem;
    background:
        radial-gradient(circle at 0.32rem center, #22c55e 0 0.18rem, transparent 0.22rem),
        linear-gradient(to right, rgba(248, 250, 252, 0.92), rgba(248, 250, 252, 0.92));
    background-size: 0.65rem 100%, calc(100% - 0.95rem) 100%;
    background-position: 0 0, 0.95rem 0;
    background-repeat: no-repeat;
}

.landing-picker-pattern__highlight {
    height: 1.4rem;
    border-left: 3px solid #22c55e;
    background: linear-gradient(135deg, rgba(248, 249, 255, 0.8) 0%, rgba(232, 244, 255, 0.78) 100%);
}

.landing-picker-pattern__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.4rem;
    flex: 1;
}

.landing-picker-pattern__grid-card {
    display: block;
    min-height: 2rem;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(148, 163, 184, 0.22);
}

.landing-picker-pattern__showcase {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.55rem;
    flex: 1;
}

.landing-picker-pattern__showcase-copy {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.landing-picker-pattern__showcase-list {
    height: 0.42rem;
    background: rgba(148, 163, 184, 0.62);
}

.landing-picker-pattern__showcase-media {
    display: block;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background:
        linear-gradient(180deg, rgba(15, 23, 42, 0.1), rgba(15, 23, 42, 0.12)),
        linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
}

.landing-picker-pattern__cta-box {
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: center;
    gap: 0.55rem;
    padding: 0.85rem;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
}

.landing-picker-pattern__cta-title,
.landing-picker-pattern__cta-copy,
.landing-picker-pattern__cta-stats {
    background: rgba(255, 255, 255, 0.92);
}

.landing-picker-pattern__cta-stats {
    height: 0.65rem;
    opacity: 0.72;
}

.landing-picker-pattern--hero_section {
    justify-content: center;
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.25) 0%, rgba(17, 24, 39, 0.16) 100%);
}

.landing-picker-pattern--showcase,
.landing-picker-pattern--what_you_get,
.landing-picker-pattern--features_grid {
    background: rgba(17, 24, 39, 0.24);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}

@media (max-width: 640px) {
    .landing-picker-card {
        min-height: 12.5rem;
        padding: 0.8rem;
    }

    .landing-picker-pattern {
        min-height: 7.25rem;
        padding: 0.65rem;
    }
}
</style>
