<template>
    <section
        class="editor-panel"
        :class="{
            'editor-panel--collapsed': collapsed,
            'editor-panel--dragging': draggingKey === panel.key,
            'editor-panel--locked': panel.movable === false,
        }"
        :data-panel-key="panel.key"
        :draggable="isDragReady && panel.movable !== false"
        @dragstart.stop="handleDragStart"
        @dragend.stop="handleDragEnd"
        @dragover.prevent.stop="handleDragOver"
        @drop.prevent.stop="handleDrop"
    >
        <header 
            class="editor-panel__header"
            @mousedown="isDragReady = true"
        >
            <div v-if="panel.movable !== false" class="editor-panel__handle" title="Drag to reorder">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M4 6a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm0 4a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm3-4a1 1 0 011-1h1a1 1 0 110 2H8a1 1 0 01-1-1zm0 4a1 1 0 011-1h1a1 1 0 110 2H8a1 1 0 01-1-1zm3-4a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1zm0 4a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
            <div class="editor-panel__title">
                <span class="editor-panel__label">{{ panel.label }}</span>
                <p v-if="panel.description" class="editor-panel__description">
                    {{ panel.description }}
                </p>
            </div>
            <div class="editor-panel__actions">
                <button
                    v-if="panel.collapsible !== false"
                    type="button"
                    class="editor-panel__collapse"
                    @click="emitToggle"
                    @mousedown.stop="isDragReady = false"
                >
                    <svg
                        class="editor-panel__collapse-icon"
                        :class="{ 'editor-panel__collapse-icon--collapsed': collapsed }"
                        width="18"
                        height="18"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M6 9l6 6 6-6"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
            </div>
        </header>

        <transition name="panel-collapse">
            <div 
                v-show="!collapsed" 
                class="editor-panel__body"
                @mousedown.stop="isDragReady = false"
            >
                <component v-if="component" :is="component" :panel="panel" />
                <slot v-else />
            </div>
        </transition>
    </section>
</template>

<script setup lang="ts">
import type { Component } from 'vue';
import type { EditorPanelDefinition } from '../../editor/useEditorPanels';

const props = defineProps<{
    panel: EditorPanelDefinition;
    component?: Component;
    collapsed: boolean;
    draggingKey: string | null;
    area: 'main' | 'sidebar';
    index: number;
}>();

import { ref, onMounted, onUnmounted } from 'vue';

const isDragReady = ref(false);

const handleGlobalMouseUp = () => {
    isDragReady.value = false;
};

onMounted(() => {
    window.addEventListener('mouseup', handleGlobalMouseUp);
});

onUnmounted(() => {
    window.removeEventListener('mouseup', handleGlobalMouseUp);
});

const emit = defineEmits<{
    (e: 'drag-start', key: string): void;
    (e: 'drag-end'): void;
    (e: 'reorder', payload: { panelKey: string; to: number }): void;
    (e: 'move', payload: { sourceArea: 'main' | 'sidebar'; targetArea: 'main' | 'sidebar'; panelKey: string; index: number }): void;
    (e: 'toggle', key: string): void;
}>();

const handleDragStart = (event: DragEvent) => {
    if (props.panel.movable === false) {
        event.preventDefault();
        return;
    }
    if (!event.dataTransfer) {
        return;
    }
    event.dataTransfer.setData('text/panel-key', props.panel.key);
    event.dataTransfer.setData('text/source-area', props.area);
    event.dataTransfer.setData('text/source-index', props.index.toString());
    event.dataTransfer.effectAllowed = 'move';
    emit('drag-start', props.panel.key);
};

const handleDragEnd = () => {
    if (props.panel.movable === false) {
        return;
    }
    emit('drag-end');
};

const handleDragOver = (event: DragEvent) => {
    if (props.panel.movable === false) {
        return;
    }
    const dataTransfer = event.dataTransfer;
    if (!dataTransfer) {
        return;
    }

    const bounding = (event.currentTarget as HTMLElement).getBoundingClientRect();
    const offset = event.clientY - bounding.top;
    const shouldMoveBefore = offset < bounding.height / 2;
    const targetIndex = shouldMoveBefore ? props.index : props.index + 1;

    if (props.draggingKey && props.draggingKey !== props.panel.key) {
        emit('reorder', { panelKey: props.draggingKey, to: targetIndex });
    }
};

const handleDrop = (event: DragEvent) => {
    if (props.panel.movable === false) {
        return;
    }
    const dataTransfer = event.dataTransfer;
    if (!dataTransfer) {
        return;
    }

    const panelKey = dataTransfer.getData('text/panel-key');
    const sourceArea = dataTransfer.getData('text/source-area') as 'main' | 'sidebar' | undefined;
    if (!panelKey || !sourceArea) {
        return;
    }

    const bounding = (event.currentTarget as HTMLElement).getBoundingClientRect();
    const offset = event.clientY - bounding.top;
    const shouldMoveBefore = offset < bounding.height / 2;
    const targetIndex = shouldMoveBefore ? props.index : props.index + 1;

    if (sourceArea !== props.area) {
        emit('move', {
            sourceArea,
            targetArea: props.area,
            panelKey,
            index: targetIndex,
        });
    } else {
        emit('reorder', { panelKey, to: targetIndex });
    }
};

const emitToggle = () => {
    emit('toggle', props.panel.key);
};
</script>

<style scoped>
.editor-panel {
    border: 1px solid var(--panel-border, #e5e7eb);
    border-radius: 0.75rem;
    background: var(--panel-bg, #ffffff);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.editor-panel--dragging {
    opacity: 0.6;
    border-style: dashed;
}

.editor-panel--locked .editor-panel__header {
    padding-left: 1.25rem;
    cursor: default;
}

.editor-panel--locked .editor-panel__handle {
    display: none;
}

.editor-panel__header {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--panel-border, #e5e7eb);
}

.editor-panel--collapsed .editor-panel__header {
    border-bottom: none;
}

.editor-panel__handle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 0.5rem;
    background: var(--panel-handle-bg, #f1f5f9);
    color: var(--panel-handle-color, #64748b);
    cursor: grab;
}

.editor-panel__handle:active {
    cursor: grabbing;
}

.editor-panel__title {
    flex: 1;
    min-width: 0;
}

.editor-panel__label {
    display: block;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--panel-title, #0f172a);
}

.editor-panel__description {
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--panel-description, #64748b);
}

.editor-panel__actions {
    display: flex;
    gap: 0.25rem;
}

.editor-panel__collapse {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 0.5rem;
    border: none;
    background: transparent;
    color: var(--panel-handle-color, #64748b);
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}

.editor-panel__collapse:hover {
    background: rgba(99, 102, 241, 0.1);
    color: #4f46e5;
}

.editor-panel__collapse-icon {
    transition: transform 0.2s ease;
}

.editor-panel__collapse-icon--collapsed {
    transform: rotate(-90deg);
}

.editor-panel__body {
    padding: 1.25rem;
}

.panel-collapse-enter-active,
.panel-collapse-leave-active {
    transition: height 0.25s ease, opacity 0.25s ease;
}

.panel-collapse-enter-from,
.panel-collapse-leave-to {
    height: 0;
    opacity: 0;
    overflow: hidden;
}

/* Dark mode support */
:global(.dark) .editor-panel {
    --panel-bg: #1f2937;
    --panel-border: #374151;
    --panel-title: #f9fafb;
    --panel-description: #9ca3af;
    --panel-handle-bg: #374151;
    --panel-handle-color: #9ca3af;
}
</style>

