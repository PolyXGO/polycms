<template>
    <div class="row-block-preview" :class="{ 'is-dragging': isAnyBlockDragging }" data-nested-drop-surface="true">
        <div
            class="row-block-preview__grid"
            :class="[alignClass]"
            :style="rowGridStyle"
        >
            <div
                v-for="(column, columnIndex) in state.columns_data"
                :key="`column-${columnIndex}`"
                class="row-block-preview__column"
                :class="{
                    'is-empty': column.blocks.length === 0,
                    'is-drop-active': isDropTarget(columnIndex, 0) && column.blocks.length === 0,
                }"
                :style="getColumnStyle(columnIndex)"
                @dragover.prevent="handleColumnDragOver(columnIndex, $event)"
                @drop.prevent="handleColumnDrop(columnIndex)"
            >
                <div v-if="column.blocks.length === 0" class="row-block-preview__empty">
                    <span class="row-block-preview__empty-label">Empty column</span>
                    <button
                        type="button"
                        class="row-block-preview__add row-block-preview__add--center"
                        @click.stop="openBlockPicker(columnIndex)"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                        </svg>
                        <span>Add block</span>
                    </button>
                </div>

                <div v-else class="row-block-preview__stack">
                    <template v-for="(block, blockIndex) in column.blocks" :key="`${columnIndex}-${blockIndex}-${block.type}`">
                        <div
                            class="row-block-preview__drop-zone"
                            :class="{ 'is-active': isDropTarget(columnIndex, blockIndex) }"
                            @dragover.prevent.stop="handleDragOver(columnIndex, blockIndex, $event)"
                            @drop.prevent.stop="handleDrop(columnIndex, blockIndex)"
                        ></div>

                        <div
                            class="row-block-preview__item nested-landing-block"
                            :class="{
                                'is-active': isNestedActive(columnIndex, blockIndex),
                                'is-dragging': isDraggingBlock(columnIndex, blockIndex),
                            }"
                            draggable="true"
                            @mousedown.capture="handleNestedPointerDown(columnIndex, blockIndex, $event)"
                            @click.prevent.stop="selectNestedBlock(columnIndex, blockIndex)"
                            @dragover.prevent.stop="handleItemDragOver(columnIndex, blockIndex, $event)"
                            @drop.prevent.stop="handleItemDrop(columnIndex, blockIndex, $event)"
                            @dragstart.stop="handleDragStart(columnIndex, blockIndex, $event)"
                            @dragend.stop="handleDragEnd"
                        >
                            <div class="row-block-preview__item-bar">
                                <div class="row-block-preview__item-meta">
                                    <span class="row-block-preview__item-handle" title="Drag to move">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                        </svg>
                                    </span>
                                    <span class="row-block-preview__item-label">{{ getBlockLabel(block.type) }}</span>
                                    <span v-if="getBlockMetaBadge(block)" class="row-block-preview__item-badge">{{ getBlockMetaBadge(block) }}</span>
                                </div>

                                <div class="row-block-preview__item-tools">
                                    <button
                                        type="button"
                                        class="row-block-preview__tool row-block-preview__tool--danger"
                                        title="Remove block"
                                        @click.stop="removeBlock(columnIndex, blockIndex)"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <component
                                :is="getBlockComponent(block.type)"
                                v-model="state.columns_data[columnIndex].blocks[blockIndex].data"
                                :is-editor="true"
                                mode="preview"
                                :node-id="buildNestedNodeId(columnIndex, blockIndex)"
                            />
                        </div>
                    </template>

                    <div
                        class="row-block-preview__drop-zone row-block-preview__drop-zone--tail"
                        :class="{ 'is-active': isDropTarget(columnIndex, column.blocks.length) }"
                        @dragover.prevent.stop="handleDragOver(columnIndex, column.blocks.length, $event)"
                        @drop.prevent.stop="handleDrop(columnIndex, column.blocks.length)"
                    ></div>
                </div>

                <button
                    v-if="column.blocks.length > 0"
                    type="button"
                    class="row-block-preview__add"
                    @click.stop="openBlockPicker(columnIndex)"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                    </svg>
                    <span>Add block</span>
                </button>
            </div>
        </div>

        <LandingBlockPicker ref="blockPickerRef" @select="handleBlockSelect" />
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, reactive, ref, watch } from 'vue';
import LandingBlockPicker from '@/admin/components/editor/LandingBlockPicker.vue';
import { landingBlockRegistry } from '@/admin/editor/landingBlockRegistry';
import { useLandingStore, type DraggedLandingBlockState } from '@/admin/stores/landingStore';
import { normalizeRowData } from '@/admin/editor/rowLayoutPresets';

const props = defineProps<{
    modelValue?: Record<string, any>;
    data?: Record<string, any>;
    mode?: 'settings' | 'preview';
    nodeId?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: Record<string, any>): void;
}>();

const landingStore = useLandingStore();
const blockPickerRef = ref<any>(null);
const activeColumnIndex = ref(0);
const dropTarget = ref<{ columnIndex: number; insertIndex: number } | null>(null);
const rowNodeId = computed(() => props.nodeId || 'row');
const isAnyBlockDragging = computed(() => Boolean(landingStore.draggingBlock));

const state = reactive(normalizeRowData(props.modelValue || props.data));

const GAP_SIZE_MAP: Record<string, string> = {
    'gap-0': '0rem',
    'gap-4': '1rem',
    'gap-8': '2rem',
    'gap-16': '4rem',
};

const ALIGN_CLASS_MAP: Record<string, string> = {
    start: 'items-start',
    center: 'items-center',
    end: 'items-end',
};

const alignClass = computed(() => ALIGN_CLASS_MAP[state.vertical_align] || 'items-start');
const rowGridStyle = computed(() => ({
    gap: GAP_SIZE_MAP[state.gap] || '2rem',
    '--row-preview-gap': GAP_SIZE_MAP[state.gap] || '2rem',
}) as Record<string, string>);

function syncState(source?: Record<string, any>) {
    Object.assign(state, normalizeRowData(source));
}

function buildPayload() {
    const base = props.modelValue || props.data || {};
    return {
        ...base,
        ...normalizeRowData(state),
    };
}

function cloneBlock<T>(value: T): T {
    return JSON.parse(JSON.stringify(value));
}

function getBlockLabel(type: string) {
    return landingBlockRegistry.get(type)?.label || type;
}

function resolveRowColumnCount(data?: Record<string, any>) {
    if (Array.isArray(data?.column_widths) && data.column_widths.length > 0) {
        return data.column_widths.length;
    }

    const columns = Number(data?.columns);
    return Number.isFinite(columns) && columns > 0 ? columns : null;
}

function getBlockMetaBadge(block: Record<string, any>) {
    if (block.type !== 'row') {
        return '';
    }

    const count = resolveRowColumnCount(block.data || {});
    return count ? `${count} ${count === 1 ? 'col' : 'cols'}` : '';
}

function getBlockComponent(type: string) {
    const definition = landingBlockRegistry.get(type);
    return definition?.previewComponent || definition?.component;
}

function fractionToPercent(value: string) {
    if (value === '1') {
        return 100;
    }

    const [numerator, denominator] = value.split('/').map((part) => Number.parseFloat(part));
    if (!Number.isFinite(numerator) || !Number.isFinite(denominator) || denominator === 0) {
        return 100;
    }

    return Number(((numerator / denominator) * 100).toFixed(4));
}

function getColumnStyle(columnIndex: number) {
    const width = state.column_widths[columnIndex] || '1';
    return {
        '--row-column-basis': `${fractionToPercent(width)}%`,
    } as Record<string, string>;
}

function buildNestedNodeId(columnIndex: number, blockIndex: number) {
    return `${rowNodeId.value}::column:${columnIndex}:block:${blockIndex}`;
}

function isNestedActive(columnIndex: number, blockIndex: number) {
    return landingStore.activeBlock?.nodeId === buildNestedNodeId(columnIndex, blockIndex);
}

function isDraggingBlock(columnIndex: number, blockIndex: number) {
    const dragState = landingStore.draggingBlock;
    return dragState?.sourceKind === 'row'
        && dragState.containerNodeId === rowNodeId.value
        && dragState.rowColumnIndex === columnIndex
        && dragState.blockIndex === blockIndex;
}

function isInvalidDropTarget(dragState: DraggedLandingBlockState) {
    const containerId = rowNodeId.value;
    return containerId === dragState.nodeId || containerId.startsWith(`${dragState.nodeId}::`);
}

function openBlockPicker(columnIndex: number) {
    activeColumnIndex.value = columnIndex;
    blockPickerRef.value?.open();
}

function handleBlockSelect(definition: { key: string; defaultAttrs?: Record<string, any> }) {
    if (!state.columns_data[activeColumnIndex.value]) {
        state.columns_data[activeColumnIndex.value] = { blocks: [] };
    }

    if ((definition as any).isReusablePart && Array.isArray((definition as any).nestedBlocks) && (definition as any).nestedBlocks.length > 0) {
        const normalizedBlocks = (definition as any).nestedBlocks.map((block: any) => ({
            type: block.type,
            data: cloneBlock(block.data || {}),
        }));

        state.columns_data[activeColumnIndex.value].blocks.push(...normalizedBlocks);

        clearNestedSelection();

        nextTick(() => {
            const blockIndex = state.columns_data[activeColumnIndex.value].blocks.length - normalizedBlocks.length;
            selectNestedBlock(activeColumnIndex.value, blockIndex);
        });

        return;
    }

    state.columns_data[activeColumnIndex.value].blocks.push({
        type: definition.key,
        data: cloneBlock(definition.defaultAttrs || {}),
    });

    clearNestedSelection();

    nextTick(() => {
        const blockIndex = state.columns_data[activeColumnIndex.value].blocks.length - 1;
        selectNestedBlock(activeColumnIndex.value, blockIndex);
    });
}

function selectNestedBlock(columnIndex: number, blockIndex: number) {
    const block = state.columns_data[columnIndex]?.blocks[blockIndex];
    if (!block) {
        return;
    }

    const definition = landingBlockRegistry.get(block.type);
    const nestedNodeId = buildNestedNodeId(columnIndex, blockIndex);

    landingStore.setActiveBlock({
        nodeId: nestedNodeId,
        type: block.type,
        data: cloneBlock(block.data || {}),
        updateAttributes: (attrs) => {
            const target = state.columns_data[columnIndex]?.blocks[blockIndex];
            if (!target) {
                return;
            }

            if (attrs.data) {
                target.data = cloneBlock(attrs.data);
                return;
            }

            target.data = {
                ...(target.data || {}),
                ...attrs,
            };
        },
        settingsComponent: definition?.settingsComponent || definition?.component,
    });
}

function handleNestedPointerDown(columnIndex: number, blockIndex: number, event: MouseEvent) {
    const target = event.target as HTMLElement;
    if (target.closest('.row-block-preview__tool')) {
        return;
    }

    event.stopPropagation();
    selectNestedBlock(columnIndex, blockIndex);
}

function clearNestedSelection() {
    if (!rowNodeId.value) {
        return;
    }

    if (landingStore.activeBlock?.nodeId?.startsWith(`${rowNodeId.value}::`)) {
        landingStore.clearActiveBlock();
    }
}

function removeBlock(columnIndex: number, blockIndex: number) {
    removeBlockAt(columnIndex, blockIndex);
}

function removeBlockAt(columnIndex: number, blockIndex: number) {
    const removedNodeId = buildNestedNodeId(columnIndex, blockIndex);
    const activeNodeId = landingStore.activeBlock?.nodeId;

    state.columns_data[columnIndex]?.blocks.splice(blockIndex, 1);

    if (activeNodeId === removedNodeId || activeNodeId?.startsWith(`${removedNodeId}::`)) {
        landingStore.clearActiveBlock();
    } else if (rowNodeId.value && activeNodeId?.startsWith(`${rowNodeId.value}::`)) {
        landingStore.clearActiveBlock();
    }
}

function handleDragStart(columnIndex: number, blockIndex: number, event: DragEvent) {
    const block = state.columns_data[columnIndex]?.blocks[blockIndex];
    if (!block) {
        event.preventDefault();
        return;
    }

    dropTarget.value = { columnIndex, insertIndex: blockIndex };
    selectNestedBlock(columnIndex, blockIndex);

    landingStore.startBlockDrag({
        nodeId: buildNestedNodeId(columnIndex, blockIndex),
        sourceKind: 'row',
        containerNodeId: rowNodeId.value,
        rowColumnIndex: columnIndex,
        blockIndex,
        block: cloneBlock(block),
        removeFromSource: () => removeBlockAt(columnIndex, blockIndex),
    });

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.dropEffect = 'move';
        event.dataTransfer.setData('application/x-polycms-landing-block', buildNestedNodeId(columnIndex, blockIndex));
        event.dataTransfer.setData('text/plain', '');
    }
}

function handleDragOver(columnIndex: number, insertIndex: number, event: DragEvent) {
    if (!landingStore.draggingBlock || isInvalidDropTarget(landingStore.draggingBlock)) {
        return;
    }

    event.preventDefault();
    dropTarget.value = { columnIndex, insertIndex };

    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
}

function resolveInsertIndexFromEvent(event: DragEvent, blockIndex: number) {
    const currentTarget = event.currentTarget as HTMLElement | null;
    if (!currentTarget) {
        return blockIndex;
    }

    const rect = currentTarget.getBoundingClientRect();
    const offsetY = event.clientY - rect.top;
    return offsetY < rect.height / 2 ? blockIndex : blockIndex + 1;
}

function handleItemDragOver(columnIndex: number, blockIndex: number, event: DragEvent) {
    handleDragOver(columnIndex, resolveInsertIndexFromEvent(event, blockIndex), event);
}

function handleItemDrop(columnIndex: number, blockIndex: number, event: DragEvent) {
    handleDrop(columnIndex, resolveInsertIndexFromEvent(event, blockIndex));
}

function handleColumnDragOver(columnIndex: number, event: DragEvent) {
    const insertIndex = state.columns_data[columnIndex]?.blocks.length || 0;
    handleDragOver(columnIndex, insertIndex, event);
}

function handleColumnDrop(columnIndex: number) {
    const insertIndex = state.columns_data[columnIndex]?.blocks.length || 0;
    handleDrop(columnIndex, insertIndex);
}

function handleDrop(columnIndex: number, insertIndex: number) {
    const dragState = landingStore.draggingBlock;
    if (!dragState || isInvalidDropTarget(dragState)) {
        handleDragEnd();
        return;
    }

    if (
        dragState.sourceKind === 'row'
        && dragState.containerNodeId === rowNodeId.value
        && typeof dragState.rowColumnIndex === 'number'
        && typeof dragState.blockIndex === 'number'
    ) {
        moveDraggedBlock(columnIndex, insertIndex, dragState.rowColumnIndex, dragState.blockIndex);
    } else {
        insertDraggedBlock(columnIndex, insertIndex, dragState);
    }

    handleDragEnd();
}

function handleDragEnd() {
    dropTarget.value = null;
    landingStore.endBlockDrag();
}

function isDropTarget(columnIndex: number, insertIndex: number) {
    return dropTarget.value?.columnIndex === columnIndex && dropTarget.value?.insertIndex === insertIndex;
}

function moveDraggedBlock(targetColumnIndex: number, targetInsertIndex: number, sourceColumnIndex: number, sourceBlockIndex: number) {
    const sourceBlocks = state.columns_data[sourceColumnIndex]?.blocks;
    if (!sourceBlocks) {
        return;
    }

    const sourceNodeId = buildNestedNodeId(sourceColumnIndex, sourceBlockIndex);
    const activeNodeId = landingStore.activeBlock?.nodeId;
    const shouldReselectMovedBlock = activeNodeId === sourceNodeId;

    const [movedBlock] = sourceBlocks.splice(sourceBlockIndex, 1);
    if (!movedBlock) {
        return;
    }

    if (!state.columns_data[targetColumnIndex]) {
        state.columns_data[targetColumnIndex] = { blocks: [] };
    }

    const targetBlocks = state.columns_data[targetColumnIndex].blocks;
    let finalInsertIndex = targetInsertIndex;

    if (sourceColumnIndex === targetColumnIndex && sourceBlockIndex < targetInsertIndex) {
        finalInsertIndex -= 1;
    }

    finalInsertIndex = Math.max(0, Math.min(finalInsertIndex, targetBlocks.length));
    targetBlocks.splice(finalInsertIndex, 0, movedBlock);

    if (rowNodeId.value && activeNodeId?.startsWith(`${rowNodeId.value}::`)) {
        landingStore.clearActiveBlock();
    }

    if (shouldReselectMovedBlock) {
        nextTick(() => selectNestedBlock(targetColumnIndex, finalInsertIndex));
    }
}

function insertDraggedBlock(
    targetColumnIndex: number,
    targetInsertIndex: number,
    dragState: DraggedLandingBlockState
) {
    if (!state.columns_data[targetColumnIndex]) {
        state.columns_data[targetColumnIndex] = { blocks: [] };
    }

    const targetBlocks = state.columns_data[targetColumnIndex].blocks;
    const finalInsertIndex = Math.max(0, Math.min(targetInsertIndex, targetBlocks.length));

    targetBlocks.splice(finalInsertIndex, 0, cloneBlock(dragState.block));
    dragState.removeFromSource?.();

    nextTick(() => selectNestedBlock(targetColumnIndex, finalInsertIndex));
}

watch(
    () => props.modelValue,
    (newValue) => {
        if (props.mode === 'preview' && newValue) {
            syncState(newValue);
        }
    },
    { deep: true, immediate: true },
);

watch(
    () => props.data,
    (newValue) => {
        if (newValue) {
            syncState(newValue);
        }
    },
    { deep: true, immediate: true },
);

watch(
    state,
    () => {
        const payload = buildPayload();
        const current = {
            ...(props.modelValue || props.data || {}),
            ...normalizeRowData(props.modelValue || props.data),
        };

        if (JSON.stringify(payload) !== JSON.stringify(current)) {
            emit('update:modelValue', payload);
        }
    },
    { deep: true },
);
</script>

<style scoped>
.row-block-preview {
    width: 100%;
}

.row-block-preview__grid {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
}

.row-block-preview__column {
    display: flex;
    flex: 1 1 clamp(14rem, calc(var(--row-column-basis, 100%) - var(--row-preview-gap, 0rem)), 100%);
    min-width: min(14rem, 100%);
    max-width: 100%;
    min-inline-size: 0;
    min-height: 6rem;
    flex-direction: column;
    gap: 0.5rem;
    border-radius: 0;
    border: 1px dashed rgba(148, 163, 184, 0.35);
    background: rgba(248, 250, 252, 0.72);
    padding: 0.5rem;
    transition: border-color 0.18s ease, background 0.18s ease;
}

.row-block-preview__column.is-empty {
    justify-content: center;
}

.row-block-preview__column:hover,
.row-block-preview__column.is-drop-active {
    border-color: rgba(99, 102, 241, 0.36);
    background: rgba(99, 102, 241, 0.04);
}

.dark .row-block-preview__column {
    border-color: rgba(71, 85, 105, 0.75);
    background: rgba(15, 23, 42, 0.22);
}

.dark .row-block-preview__column:hover,
.dark .row-block-preview__column.is-drop-active {
    border-color: rgba(129, 140, 248, 0.4);
}

.row-block-preview__empty {
    display: flex;
    min-height: 100%;
    flex: 1;
    min-width: 0;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.row-block-preview__empty-label {
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #94a3b8;
    text-align: center;
    overflow-wrap: anywhere;
}

.row-block-preview__stack {
    display: flex;
    flex: 1;
    flex-direction: column;
}

.row-block-preview__drop-zone {
    height: 0.6rem;
    border-radius: 0;
    border: 1px dashed transparent;
    background: transparent;
    transition: border-color 0.18s ease, background 0.18s ease, height 0.18s ease;
}

.row-block-preview.is-dragging .row-block-preview__drop-zone {
    border-color: rgba(129, 140, 248, 0.18);
}

.row-block-preview__drop-zone.is-active {
    height: 0.95rem;
    border-color: rgba(99, 102, 241, 0.7);
    background: rgba(99, 102, 241, 0.14);
}

.row-block-preview__item {
    position: relative;
    border-radius: 0;
    border: 1px solid rgba(226, 232, 240, 0.9);
    background: rgba(255, 255, 255, 0.92);
    padding: 0.625rem 0.7rem 0.7rem;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
    cursor: pointer;
}

.row-block-preview__item:hover,
.row-block-preview__item.is-active {
    border-color: rgba(99, 102, 241, 0.7);
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.2);
}

.row-block-preview__item.is-dragging {
    opacity: 0.45;
}

.dark .row-block-preview__item {
    border-color: rgba(51, 65, 85, 0.95);
    background: rgba(17, 24, 39, 0.74);
}

.row-block-preview__item-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.6rem;
}

.row-block-preview__item-meta {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.row-block-preview__item-handle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.35rem;
    height: 1.35rem;
    border-radius: 0;
    background: rgba(99, 102, 241, 0.08);
    color: #6366f1;
}

.row-block-preview__item-label {
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #64748b;
}

.dark .row-block-preview__item-label {
    color: #94a3b8;
}

.row-block-preview__item-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.1rem 0.32rem;
    border-radius: 999px;
    background: rgba(99, 102, 241, 0.1);
    color: #6366f1;
    font-size: 0.54rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    white-space: nowrap;
}

.dark .row-block-preview__item-badge {
    background: rgba(129, 140, 248, 0.14);
    color: #a5b4fc;
}

.row-block-preview__item-tools {
    display: flex;
    gap: 0.35rem;
}

.row-block-preview__tool {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.45rem;
    height: 1.45rem;
    border-radius: 0;
    border: 1px solid rgba(226, 232, 240, 0.9);
    background: rgba(255, 255, 255, 0.96);
    color: #64748b;
    transition: border-color 0.18s ease, color 0.18s ease, background 0.18s ease;
}

.row-block-preview__tool:hover {
    border-color: rgba(129, 140, 248, 0.52);
    color: #4f46e5;
}

.row-block-preview__tool--danger:hover {
    border-color: rgba(248, 113, 113, 0.45);
    color: #ef4444;
}

.dark .row-block-preview__tool {
    border-color: rgba(71, 85, 105, 0.8);
    background: rgba(15, 23, 42, 0.94);
    color: #cbd5e1;
}

.row-block-preview__add {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    width: 100%;
    min-width: 0;
    border-radius: 0;
    border: 1px dashed rgba(99, 102, 241, 0.38);
    background: rgba(79, 70, 229, 0.05);
    padding: 0.6rem 0.75rem;
    font-size: 0.66rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #4f46e5;
    text-align: center;
    white-space: normal;
    overflow-wrap: anywhere;
    transition: background 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
}

.row-block-preview__add:hover {
    border-color: rgba(79, 70, 229, 0.72);
    background: rgba(79, 70, 229, 0.11);
}

.row-block-preview__add--center {
    width: auto;
    min-width: 9rem;
}

.dark .row-block-preview__add {
    border-color: rgba(129, 140, 248, 0.4);
    background: rgba(79, 70, 229, 0.12);
    color: #a5b4fc;
}

@media (max-width: 768px) {
    .row-block-preview__grid {
        gap: 0.75rem !important;
    }
}
</style>
