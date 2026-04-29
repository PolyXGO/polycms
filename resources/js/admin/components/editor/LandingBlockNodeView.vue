<template>
    <node-view-wrapper
        class="landing-block-shell"
        :class="{ 'is-drag-active': isAnyBlockDragging }"
        contenteditable="false"
    >
        <div
            class="landing-block-drop-zone landing-block-drop-zone--before"
            :class="{ 'is-active': isDropTarget('before') }"
            @dragover.prevent.stop="handleDragOver('before', $event)"
            @drop.prevent.stop="handleDrop('before')"
        ></div>

        <div
            class="landing-block-wrapper"
            :class="{
                'is-selected': props.selected || isActiveInStore,
                'is-dragging': isDraggingSelf,
            }"
            @click="selectBlock"
            @dragover.prevent.stop="handleWrapperDragOver"
            @drop.prevent.stop="handleWrapperDrop"
            :style="{ margin: blockData.margin }"
        >
            <div class="landing-block-header">
                <div
                    class="landing-block-drag"
                    data-drag-handle
                    draggable="true"
                    title="Drag to reorder"
                    @mousedown.capture.stop="handleDragHandlePointerDown"
                    @dragstart.stop="handleDragStart"
                    @dragend.stop="handleDragEnd"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" /></svg>
                </div>
                <div class="landing-block-info">
                    <span class="landing-block-icon" v-if="blockDefinition?.icon" v-html="blockDefinition.icon"></span>
                    <span class="landing-block-label">{{ blockDefinition?.label || node.attrs.type }}</span>
                    <span v-if="blockMetaBadge" class="landing-block-badge">{{ blockMetaBadge }}</span>
                </div>
                <div class="landing-block-actions">
                    <button type="button" @click.stop="handleDelete" class="landing-block-delete" title="Delete Block">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="landing-block-content">
                <component 
                    v-if="blockDefinition?.previewComponent" 
                    :is="blockDefinition.previewComponent" 
                    :data="blockData"
                    :node-id="String(nodeId)"
                />
                <component 
                    v-else-if="blockDefinition" 
                    :is="blockDefinition.component" 
                    v-model="blockData"
                    :is-editor="true"
                    mode="preview"
                    :node-id="String(nodeId)"
                />
                <div v-else class="landing-block-error">
                    Unknown block type: {{ node.attrs.type }}
                </div>
            </div>
        </div>

        <div
            class="landing-block-drop-zone landing-block-drop-zone--after"
            :class="{ 'is-active': isDropTarget('after') }"
            @dragover.prevent.stop="handleDragOver('after', $event)"
            @drop.prevent.stop="handleDrop('after')"
        ></div>
    </node-view-wrapper>
</template>

<script setup lang="ts">
import { computed, watch, onBeforeUnmount } from 'vue';
import { nodeViewProps, NodeViewWrapper } from '@tiptap/vue-3';
import { landingBlockRegistry } from '../../editor/landingBlockRegistry';
import { useLandingStore } from '../../stores/landingStore';

const props = defineProps(nodeViewProps);
const landingStore = useLandingStore();

const handleDelete = () => {
    if (typeof props.deleteNode === 'function') {
        props.deleteNode();
    }
};

// stable node ID from attributes
const nodeId = computed(() => props.node.attrs.id);

const blockDefinition = computed(() => {
    return landingBlockRegistry.get(props.node.attrs.type);
});

// blockData is derived from node.attrs.data
const blockData = computed({
    get: () => props.node.attrs.data || {},
    set: (newValue) => {
        props.updateAttributes({
            data: {
                ...(props.node.attrs.data || {}),
                ...newValue,
            },
        });
    },
});

// Check if this node is currently selected in the store
const isActiveInStore = computed(() => {
    return landingStore.activeBlock?.nodeId === nodeId.value;
});

const isAnyBlockDragging = computed(() => Boolean(landingStore.draggingBlock));
const isDraggingSelf = computed(() => landingStore.draggingBlock?.nodeId === nodeId.value);

const resolveRowColumnCount = (data?: Record<string, any>) => {
    if (Array.isArray(data?.column_widths) && data.column_widths.length > 0) {
        return data.column_widths.length;
    }

    const columns = Number(data?.columns);
    return Number.isFinite(columns) && columns > 0 ? columns : null;
};

const blockMetaBadge = computed(() => {
    if (props.node.attrs.type !== 'row') {
        return '';
    }

    const count = resolveRowColumnCount(props.node.attrs.data || {});
    return count ? `${count} ${count === 1 ? 'col' : 'cols'}` : '';
});

const cloneData = <T>(value: T): T => JSON.parse(JSON.stringify(value));

const getNodePos = () => {
    if (typeof props.getPos !== 'function') {
        return null;
    }

    try {
        return Number(props.getPos());
    } catch {
        return null;
    }
};

const selectBlock = (event?: MouseEvent) => {
    const target = event?.target as HTMLElement | undefined;
    if (target?.closest('.nested-landing-block')) {
        return;
    }

    landingStore.setActiveBlock({
        nodeId: nodeId.value,
        type: props.node.attrs.type,
        data: JSON.parse(JSON.stringify(blockData.value)), // Deep clone to avoid reactive linking
        updateAttributes: (attrs) => props.updateAttributes(attrs),
        settingsComponent: blockDefinition.value?.settingsComponent || blockDefinition.value?.component,
    });
};

const handleDragHandlePointerDown = () => {
    selectBlock();
};

const handleDragStart = (event: DragEvent) => {
    const pos = getNodePos();
    if (pos === null) {
        event.preventDefault();
        return;
    }

    const size = props.node.nodeSize;

    landingStore.startBlockDrag({
        nodeId: nodeId.value,
        sourceKind: 'root',
        block: {
            type: props.node.attrs.type,
            data: cloneData(props.node.attrs.data || {}),
        },
        root: {
            pos,
            size,
            json: props.node.toJSON(),
        },
        removeFromSource: () => {
            const tr = props.editor.state.tr.delete(pos, pos + size);
            props.editor.view.dispatch(tr);

            if (landingStore.activeBlock?.nodeId === nodeId.value) {
                landingStore.clearActiveBlock();
            }
        },
    });
    landingStore.setBlockDropTarget({
        nodeId: nodeId.value,
        placement: 'before',
    });

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.dropEffect = 'move';
        event.dataTransfer.setData('application/x-polycms-landing-block', String(nodeId.value));
        event.dataTransfer.setData('text/plain', '');
    }
};

const handleDragOver = (placement: 'before' | 'after', event: DragEvent) => {
    if (!landingStore.draggingBlock || landingStore.draggingBlock.nodeId === nodeId.value) {
        return;
    }

    event.preventDefault();
    landingStore.setBlockDropTarget({
        nodeId: nodeId.value,
        placement,
    });

    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
};

const resolvePlacementFromEvent = (event: DragEvent): 'before' | 'after' => {
    const currentTarget = event.currentTarget as HTMLElement | null;
    if (!currentTarget) {
        return 'after';
    }

    const rect = currentTarget.getBoundingClientRect();
    const offsetY = event.clientY - rect.top;
    return offsetY < rect.height / 2 ? 'before' : 'after';
};

const isNestedDropSurface = (target: EventTarget | null) => {
    return target instanceof HTMLElement && Boolean(target.closest('[data-nested-drop-surface="true"]'));
};

const handleWrapperDragOver = (event: DragEvent) => {
    if (isNestedDropSurface(event.target)) {
        return;
    }

    handleDragOver(resolvePlacementFromEvent(event), event);
};

const handleWrapperDrop = (event: DragEvent) => {
    if (isNestedDropSurface(event.target)) {
        return;
    }

    handleDrop(resolvePlacementFromEvent(event));
};

const handleDrop = (placement: 'before' | 'after') => {
    const dragState = landingStore.draggingBlock;
    const targetPos = getNodePos();
    if (!dragState || targetPos === null || dragState.nodeId === nodeId.value) {
        landingStore.endBlockDrag();
        return;
    }

    if (dragState.sourceKind === 'root' && dragState.root) {
        const targetInsertPos = placement === 'after' ? targetPos + props.node.nodeSize : targetPos;
        let insertPos = targetInsertPos;

        if (dragState.root.pos < targetInsertPos) {
            insertPos -= dragState.root.size;
        }

        if (insertPos === dragState.root.pos) {
            landingStore.endBlockDrag();
            return;
        }

        const node = props.editor.state.schema.nodeFromJSON(dragState.root.json);
        const tr = props.editor.state.tr
            .delete(dragState.root.pos, dragState.root.pos + dragState.root.size)
            .insert(insertPos, node);

        props.editor.view.dispatch(tr);
        landingStore.endBlockDrag();
        return;
    }

    const insertPos = placement === 'after' ? targetPos + props.node.nodeSize : targetPos;
    const newNodeId = crypto.randomUUID();
    const node = props.editor.state.schema.nodes.landingBlock.create({
        id: newNodeId,
        type: dragState.block.type,
        data: cloneData(dragState.block.data || {}),
    });
    const tr = props.editor.state.tr.insert(insertPos, node);

    props.editor.view.dispatch(tr);
    dragState.removeFromSource?.();
    landingStore.endBlockDrag();
};

const handleDragEnd = () => {
    landingStore.endBlockDrag();
};

const isDropTarget = (placement: 'before' | 'after') => {
    return landingStore.dropTarget?.nodeId === nodeId.value && landingStore.dropTarget?.placement === placement;
};

// Watch for store data changes and sync to Tiptap node
watch(
    () => landingStore.activeBlock?.data,
    (newData) => {
        if (isActiveInStore.value && newData) {
            // Only update if data has actually changed
            const currentData = JSON.stringify(props.node.attrs.data || {});
            const incomingData = JSON.stringify(newData);
            if (currentData !== incomingData) {
                props.updateAttributes({ data: JSON.parse(JSON.stringify(newData)) });
            }
        }
    },
    { deep: true }
);

// Clear selection when component unmounts
onBeforeUnmount(() => {
    if (isActiveInStore.value) {
        landingStore.clearActiveBlock();
    }
});
</script>

<style scoped>
.landing-block-wrapper {
    margin: 0.75rem 0;
    border: 1px solid transparent;
    border-radius: 0.875rem;
    background: transparent;
    overflow: visible;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}

.dark .landing-block-wrapper {
    border-color: transparent;
    background: transparent;
}

.landing-block-wrapper:hover {
    border-color: rgba(148, 163, 184, 0.22);
    background: transparent;
}

.dark .landing-block-wrapper:hover {
    border-color: rgba(71, 85, 105, 0.58);
    background: transparent;
}

.landing-block-wrapper.is-selected {
    border-color: var(--color-primary, #6366f1);
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.24);
    background: transparent;
}

.dark .landing-block-wrapper.is-selected {
    background: transparent;
}

.landing-block-header {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.2rem 0.6rem;
    background: var(--color-bg-primary, #ffffff);
    border-bottom: 1px solid var(--color-border, #e5e7eb);
    position: absolute;
    top: -24px;
    left: -1px;
    border-radius: 0.75rem 0.75rem 0 0;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 10;
}

.landing-block-wrapper:hover .landing-block-header,
.landing-block-wrapper.is-selected .landing-block-header {
    opacity: 1;
    visibility: visible;
}

.dark .landing-block-header {
    background: var(--color-bg-primary-dark, #111827);
    border-color: var(--color-border-dark, #374151);
}

.landing-block-drag {
    cursor: grab;
    padding: 0.2rem;
    color: var(--color-text-muted, #9ca3af);
    border-radius: 0.3rem;
    transition: background 0.2s, color 0.2s;
}

.landing-block-drag:hover {
    background: var(--color-bg-tertiary, #f3f4f6);
    color: var(--color-primary, #6366f1);
}

.dark .landing-block-drag:hover {
    background: var(--color-bg-tertiary-dark, #374151);
}

.landing-block-info {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex: 1;
    min-width: 0;
    flex-wrap: wrap;
}

.landing-block-icon {
    display: flex;
    align-items: center;
    color: var(--color-primary, #6366f1);
}

.landing-block-icon :deep(svg) {
    width: 1rem;
    height: 1rem;
}

.landing-block-label {
    font-size: 0.64rem;
    font-weight: 600;
    color: var(--color-text-secondary, #374151);
    text-transform: uppercase;
    letter-spacing: 0.12em;
}

.dark .landing-block-label {
    color: var(--color-text-secondary-dark, #d1d5db);
}

.landing-block-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.12rem 0.36rem;
    border-radius: 999px;
    background: rgba(99, 102, 241, 0.1);
    color: #6366f1;
    font-size: 0.56rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    white-space: nowrap;
}

.dark .landing-block-badge {
    background: rgba(129, 140, 248, 0.14);
    color: #a5b4fc;
}

.landing-block-actions {
    display: flex;
    gap: 0.2rem;
}

.landing-block-delete {
    padding: 0.2rem;
    color: var(--color-text-muted, #9ca3af);
    border-radius: 0.3rem;
    transition: background 0.2s, color 0.2s;
}

.landing-block-delete:hover {
    background: var(--color-danger-light, #fee2e2);
    color: var(--color-danger, #ef4444);
}

.landing-block-content {
    min-height: 20px;
    padding: 0;
}

.landing-block-drop-zone {
    height: 0;
    border-radius: 0;
    opacity: 0;
    transition: height 0.18s ease, opacity 0.18s ease, background 0.18s ease, margin 0.18s ease;
}

.landing-block-shell.is-drag-active .landing-block-drop-zone {
    height: 1.1rem;
    margin: 0.1rem 0;
    opacity: 1;
}

.landing-block-drop-zone.is-active {
    background: rgba(99, 102, 241, 0.22);
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.45);
}

.landing-block-wrapper.is-dragging {
    opacity: 0.55;
}

.landing-block-error {
    padding: 1.5rem;
    color: var(--color-danger, #ef4444);
    font-size: 0.875rem;
    text-align: center;
}
</style>
