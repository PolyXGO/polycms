<template>
    <div class="editor-panels">
        <div
            class="editor-panels__column editor-panels__column--main"
            @dragover.prevent="onAreaDragOver('main', $event)"
            @drop.prevent="onAreaDrop('main', $event)"
        >
            <EditorPanelWrapper
                v-for="(panel, index) in panels.main"
                :key="panel.key"
                :panel="panel"
                :component="resolveComponent(panel)"
                :collapsed="panel.collapsed ?? false"
                :dragging-key="draggingKey"
                :area="'main'"
                :index="index"
                @drag-start="onDragStart"
                @drag-end="onDragEnd"
                @reorder="onReorder('main', $event)"
                @move="onMove"
                @toggle="toggleCollapsed"
            />
        </div>

        <div
            class="editor-panels__column editor-panels__column--sidebar"
            @dragover.prevent="onAreaDragOver('sidebar', $event)"
            @drop.prevent="onAreaDrop('sidebar', $event)"
        >
            <EditorPanelWrapper
                v-for="(panel, index) in panels.sidebar"
                :key="panel.key"
                :panel="panel"
                :component="resolveComponent(panel)"
                :collapsed="panel.collapsed ?? false"
                :dragging-key="draggingKey"
                :area="'sidebar'"
                :index="index"
                @drag-start="onDragStart"
                @drag-end="onDragEnd"
                @reorder="onReorder('sidebar', $event)"
                @move="onMove"
                @toggle="toggleCollapsed"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import type { Component } from 'vue';
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore - Vue SFC typing provided via shim
import EditorPanelWrapper from './EditorPanelWrapper.vue';
import { getEditorPanelComponent, getRegisteredPanelNames } from '../../editor/panelRegistry';
import { useEditorPanels } from '../../editor/useEditorPanels';

const props = defineProps<{
    type: string;
    components: Record<string, Component>;
}>();

const emit = defineEmits<{
    (e: 'loaded'): void;
}>();

const draggingKey = ref<string | null>(null);

const {
    panels,
    fetchPanels,
    reorderPanel,
    movePanelBetweenAreas,
    toggleCollapsed,
    setPanelType,
    panelType,
} = useEditorPanels(props.type);

const isDebug =
    typeof window !== 'undefined' &&
    typeof window.location !== 'undefined' &&
    window.location.hostname === 'localhost';

const panelComponents = computed(() => props.components || {});

const normalizeString = (value: string): string =>
    value
        .toLowerCase()
        .replace(/[^a-z0-9]/g, '');

const expandCandidateNames = (value?: string | null): { originals: string[]; normalized: string[] } => {
    if (!value) {
        return { originals: [], normalized: [] };
    }

    const originals = new Set<string>();
    const queue = [value];

    while (queue.length > 0) {
        const current = queue.pop();
        if (!current) {
            continue;
        }

        const trimmed = current.trim();
        if (!trimmed || originals.has(trimmed)) {
            continue;
        }

        originals.add(trimmed);

        const splitters = ['.', ':', '/', '\\', '-', '_'];
        splitters.forEach((delimiter) => {
            if (trimmed.includes(delimiter)) {
                trimmed.split(delimiter).forEach((segment) => {
                    if (segment && segment !== trimmed) {
                        queue.push(segment);
                    }
                });
            }
        });
    }

    const originalsArray = Array.from(originals);
    const normalizedArray = originalsArray.map(normalizeString).filter(Boolean);

    return { originals: originalsArray, normalized: normalizedArray };
};

const resolveFromRegistry = (candidates: { originals: string[]; normalized: string[] }): Component | undefined => {
    for (const name of candidates.originals) {
        const registered = getEditorPanelComponent(name);
        if (registered) {
            return registered;
        }
    }

    const registryEntries = new Map<string, Component>();
    getRegisteredPanelNames().forEach((name) => {
        const normalized = normalizeString(name);
        if (normalized) {
            const component = getEditorPanelComponent(name);
            if (component) {
                registryEntries.set(normalized, component);
            }
        }
    });

    for (const normalizedName of candidates.normalized) {
        const component = registryEntries.get(normalizedName);
        if (component) {
            return component;
        }
    }

    return undefined;
};

const resolveFromLocalComponents = (candidates: { originals: string[]; normalized: string[] }): Component | undefined => {
    for (const name of candidates.originals) {
        if (panelComponents.value[name]) {
            if (isDebug) {
                // eslint-disable-next-line no-console
                console.debug('[EditorPanelLayout] component resolved from prop map (original)', name);
            }
            return panelComponents.value[name];
        }
    }

    const normalizedMap = new Map<string, Component>();
    Object.entries(panelComponents.value).forEach(([key, component]) => {
        normalizedMap.set(normalizeString(key), component);
    });

    for (const normalizedName of candidates.normalized) {
        if (isDebug) {
            // eslint-disable-next-line no-console
            console.debug('[EditorPanelLayout] checking normalized candidate', normalizedName);
        }
        const component = normalizedMap.get(normalizedName);
        if (component) {
            if (isDebug) {
                // eslint-disable-next-line no-console
                console.debug('[EditorPanelLayout] component resolved from prop map (normalized)', normalizedName);
            }
            return component;
        }
    }

    return undefined;
};

const resolveComponent = (panel: { key: string; component?: string | null }): Component | undefined => {
    const candidates = {
        originals: [] as string[],
        normalized: [] as string[],
    };

    const componentCandidates = expandCandidateNames(panel.component);
    const keyCandidates = expandCandidateNames(panel.key);

    candidates.originals = [...componentCandidates.originals, ...keyCandidates.originals];
    candidates.normalized = [...componentCandidates.normalized, ...keyCandidates.normalized];

    const localComponent = resolveFromLocalComponents(candidates);
    if (localComponent) {
        return localComponent;
    }

    const registryComponent = resolveFromRegistry(candidates);
    if (registryComponent) {
        if (isDebug) {
            // eslint-disable-next-line no-console
            console.debug('[EditorPanelLayout] component resolved from registry', panel.key ?? panel.component);
        }
        return registryComponent;
    }

    if (isDebug) {
        // eslint-disable-next-line no-console
        console.warn(
            '[EditorPanelLayout] Missing component for panel',
            {
                key: panel.key,
                component: panel.component,
                candidates,
                available: Object.keys(panelComponents.value),
                registered: getRegisteredPanelNames(),
            }
        );
    }

    return undefined;
};

const onDragStart = (panelKey: string) => {
    draggingKey.value = panelKey;
};

const onDragEnd = () => {
    draggingKey.value = null;
};

const onReorder = (area: 'main' | 'sidebar', payload: { panelKey: string; to: number }) => {
    reorderPanel(area, payload.panelKey, payload.to);
};

const onMove = (payload: { sourceArea: 'main' | 'sidebar'; targetArea: 'main' | 'sidebar'; panelKey: string; index: number }) => {
    movePanelBetweenAreas(payload.sourceArea, payload.targetArea, payload.panelKey, payload.index);
};

const onAreaDragOver = (area: 'main' | 'sidebar', event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
};

const onAreaDrop = (area: 'main' | 'sidebar', event: DragEvent) => {
    const dataTransfer = event.dataTransfer;
    if (!dataTransfer) {
        draggingKey.value = null;
        return;
    }

    const panelKey = dataTransfer.getData('text/panel-key');
    const sourceArea = dataTransfer.getData('text/source-area') as 'main' | 'sidebar' | undefined;
    if (!panelKey || !sourceArea) {
        draggingKey.value = null;
        return;
    }

    const targetIndex = panels.value[area].length;
    movePanelBetweenAreas(sourceArea, area, panelKey, targetIndex);
    draggingKey.value = null;
};

onMounted(async () => {
    await fetchPanels(props.type);
    emit('loaded');
});

watch(
    () => props.type,
    async (newType) => {
        if (!newType || newType === panelType.value) {
            return;
        }
        setPanelType(newType);
        await fetchPanels();
    }
);
</script>

<style scoped>
.editor-panels {
    display: grid;
    gap: 2rem;
    grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
}

.editor-panels__column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.editor-panels__column--sidebar {
    position: sticky;
    top: 2rem;
    align-self: flex-start;
}

@media (max-width: 1023px) {
    .editor-panels {
        grid-template-columns: minmax(0, 1fr);
    }

    .editor-panels__column--sidebar {
        position: static;
    }
}
</style>

