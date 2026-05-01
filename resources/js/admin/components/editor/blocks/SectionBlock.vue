<template>
    <div class="section-block-editor" :style="{ padding: state.padding }">
        <div class="mb-4 border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 space-y-3">
            <h4 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Section Settings
            </h4>
            
            <div class="space-y-4">
                <div class="form-group">
                    <label class="block text-[9px] font-bold uppercase text-gray-400 mb-2">Background</label>
                    <div class="flex items-center gap-3">
                        <select v-model="state.bg_type" class="w-24 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 py-1.5 px-2 text-[10px] appearance-none focus:ring-1 focus:ring-indigo-500">
                            <option value="solid">Solid</option>
                            <option value="gradient">Gradient</option>
                        </select>
                        
                        <div class="flex items-center gap-2">
                            <ColorPicker 
                                :modelValue="state.bg_type === 'solid' ? state.bg_color : state.bg_gradient_start" 
                                @update:modelValue="(val: string) => { 
                                    if (state.bg_type === 'solid') state.bg_color = val; 
                                    else state.bg_gradient_start = val;
                                }" 
                            />
                            <template v-if="state.bg_type === 'gradient'">
                                <span class="text-gray-300">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </span>
                                <ColorPicker v-model="state.bg_gradient_end" />
                                <AnglePicker v-model="state.bg_gradient_angle" />
                            </template>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-end">
                    <div class="form-group">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Internal Spacing</label>
                        <select v-model="state.padding_class" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 p-2 text-xs">
                            <option value="py-4">Small</option>
                            <option value="py-8">Medium</option>
                            <option value="py-16">Large</option>
                            <option value="py-24">Extra Large</option>
                            <option value="py-32">Huge</option>
                        </select>
                    </div>
                    <ColorPicker v-model="state.text_color" label="Text Color" />
                </div>

                <div class="grid grid-cols-2 gap-3 items-end">
                    <AlignmentPicker v-model="state.alignment" label="Content Alignment" />
                    <label class="flex items-center gap-2 cursor-pointer h-10">
                        <input type="checkbox" v-model="state.overlay" class="form-checkbox h-3.5 w-3.5 text-indigo-600 rounded">
                        <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Dark Overlay</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Background Image</label>
                    <div class="flex gap-2">
                        <input v-model="state.bg_image" type="text" class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 p-2 text-xs" placeholder="URL to image...">
                        <button @click="openMediaPicker" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-xs hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Pick</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-indigo-50/30 dark:bg-indigo-500/5 border border-dashed border-indigo-100 dark:border-indigo-500/20 p-4">
            <h4 class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                Grouped Blocks
            </h4>

            <div class="section-block-children" :class="{ 'is-dragging': isAnyBlockDragging }" data-nested-drop-surface="true">
                <div
                    v-if="state.blocks.length === 0"
                    class="section-block-empty"
                    :class="{ 'is-drop-active': isDropTarget(0) }"
                    @dragover.prevent="handleDragOver(0, $event)"
                    @drop.prevent="handleDrop(0)"
                >
                    <span class="section-block-empty__label">No blocks in this section</span>
                    <button @click="openBlockPicker" class="section-block-add section-block-add--center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" /></svg>
                        <span>Add Block to Section</span>
                    </button>
                </div>

                <div
                    v-else
                    class="section-block-list"
                    @dragover.prevent="handleListDragOver($event)"
                    @drop.prevent="handleListDrop"
                >
                    <template v-for="(block, index) in state.blocks" :key="`${index}-${block.type}`">
                        <div
                            class="section-block-drop-zone"
                            :class="{ 'is-active': isDropTarget(index) }"
                            @dragover.prevent.stop="handleDragOver(index, $event)"
                            @drop.prevent.stop="handleDrop(index)"
                        ></div>

                        <div
                            class="section-block-item nested-landing-block"
                            :class="{
                                'is-active': isNestedActive(index),
                                'is-dragging': isDraggingBlock(index),
                            }"
                            draggable="true"
                            @mousedown.capture="handleNestedPointerDown(index, $event)"
                            @click.prevent.stop="selectNestedBlock(index)"
                            @dragover.prevent.stop="handleItemDragOver(index, $event)"
                            @drop.prevent.stop="handleItemDrop(index, $event)"
                            @dragstart.stop="handleDragStart(index, $event)"
                            @dragend.stop="handleDragEnd"
                        >
                            <div class="section-block-item__bar">
                                <div class="section-block-item__meta">
                                    <span class="section-block-item__handle" title="Drag to move">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" /></svg>
                                    </span>
                                    <span class="section-block-item__count">#{{ Number(index) + 1 }}</span>
                                    <span class="section-block-item__label">{{ getBlockLabel(block.type) }}</span>
                                    <span v-if="getBlockMetaBadge(block)" class="section-block-item__badge">{{ getBlockMetaBadge(block) }}</span>
                                </div>

                                <div class="section-block-item__tools">
                                    <button @click.stop="removeBlock(Number(index))" class="section-block-tool section-block-tool--danger" title="Remove block">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <component 
                                :is="getBlockComponent(block.type)"
                                v-model="state.blocks[index].data"
                                :is-editor="true"
                                mode="preview"
                                :node-id="buildNestedNodeId(index)"
                            />
                        </div>
                    </template>

                    <div
                        class="section-block-drop-zone section-block-drop-zone--tail"
                        :class="{ 'is-active': isDropTarget(state.blocks.length) }"
                        @dragover.prevent.stop="handleDragOver(state.blocks.length, $event)"
                        @drop.prevent.stop="handleDrop(state.blocks.length)"
                    ></div>
                </div>

                <button @click="openBlockPicker" class="section-block-add">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" /></svg>
                    <span>Add Block to Section</span>
                </button>
            </div>
        </div>

        <MediaPicker ref="mediaPickerRef" @select="handleMediaSelect" />
        <LandingBlockPicker ref="blockPickerRef" @select="handleBlockSelect" />
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, reactive, ref, watch } from 'vue';
import { landingBlockRegistry } from '../../../editor/landingBlockRegistry';
import { useLandingStore, type DraggedLandingBlockState } from '@/admin/stores/landingStore';
import MediaPicker from '@/admin/components/MediaPicker.vue';
import LandingBlockPicker from '@/admin/components/editor/LandingBlockPicker.vue';
import ColorPicker from '../controls/ColorPicker.vue';
import AnglePicker from '../controls/AnglePicker.vue';
import AlignmentPicker from '../controls/AlignmentPicker.vue';

const props = defineProps<{
    modelValue: any;
    mode?: 'settings' | 'preview';
    data?: any;
    nodeId?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const landingStore = useLandingStore();
const mediaPickerRef = ref<any>(null);
const blockPickerRef = ref<any>(null);
const dropTarget = ref<number | null>(null);
const sectionNodeId = computed(() => props.nodeId || 'section');
const isAnyBlockDragging = computed(() => Boolean(landingStore.draggingBlock));

const state = reactive({
    bg_type: props.modelValue?.bg_type || props.data?.bg_type || 'solid',
    bg_color: props.modelValue?.bg_color || props.data?.bg_color || '',
    bg_gradient_start: props.modelValue?.bg_gradient_start || props.data?.bg_gradient_start || '#4f46e5',
    bg_gradient_end: props.modelValue?.bg_gradient_end || props.data?.bg_gradient_end || '#7209b7',
    bg_gradient_angle: props.modelValue?.bg_gradient_angle || props.data?.bg_gradient_angle || 135,
    bg_image: props.modelValue?.bg_image || props.data?.bg_image || '',
    padding_class: props.modelValue?.padding_class || props.data?.padding_class || props.modelValue?.padding || 'py-16',
    text_color: props.modelValue?.text_color || props.data?.text_color || '',
    alignment: props.modelValue?.alignment || props.data?.alignment || 'left',
    full_width: props.modelValue?.full_width || props.data?.full_width || false,
    overlay: props.modelValue?.overlay || props.data?.overlay || false,
    blocks: JSON.parse(JSON.stringify(props.modelValue?.blocks || props.data?.blocks || [])),
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

const syncState = (source: any) => {
    state.bg_type = source?.bg_type || 'solid';
    state.bg_color = source?.bg_color || '';
    state.bg_gradient_start = source?.bg_gradient_start || '#4f46e5';
    state.bg_gradient_end = source?.bg_gradient_end || '#7209b7';
    state.bg_gradient_angle = source?.bg_gradient_angle || 135;
    state.bg_image = source?.bg_image || '';
    state.padding_class = source?.padding_class || source?.padding || 'py-16';
    state.text_color = source?.text_color || '';
    state.alignment = source?.alignment || 'left';
    state.full_width = source?.full_width || false;
    state.overlay = source?.overlay || false;
    state.blocks = JSON.parse(JSON.stringify(source?.blocks || []));
    state.margin = source?.margin || '';
    state.padding = source?.padding || '';
};

const buildPayload = () => ({
    ...(props.modelValue || props.data || {}),
    bg_type: state.bg_type,
    bg_color: state.bg_color,
    bg_gradient_start: state.bg_gradient_start,
    bg_gradient_end: state.bg_gradient_end,
    bg_gradient_angle: state.bg_gradient_angle,
    bg_image: state.bg_image,
    padding_class: state.padding_class,
    text_color: state.text_color,
    alignment: state.alignment,
    full_width: state.full_width,
    overlay: state.overlay,
    blocks: state.blocks,
    margin: state.margin,
    padding: state.padding,
});

const cloneBlock = <T>(value: T): T => JSON.parse(JSON.stringify(value));

const getBlockLabel = (type: string) => landingBlockRegistry.get(type)?.label || type;
const resolveRowColumnCount = (data?: Record<string, any>) => {
    if (Array.isArray(data?.column_widths) && data.column_widths.length > 0) {
        return data.column_widths.length;
    }

    const columns = Number(data?.columns);
    return Number.isFinite(columns) && columns > 0 ? columns : null;
};
const getBlockMetaBadge = (block: Record<string, any>) => {
    if (block.type !== 'row') {
        return '';
    }

    const count = resolveRowColumnCount(block.data || {});
    return count ? `${count} ${count === 1 ? 'col' : 'cols'}` : '';
};
const getBlockComponent = (type: string) => {
    const definition = landingBlockRegistry.get(type);
    return definition?.previewComponent || definition?.component;
};

const buildNestedNodeId = (index: number) => `${sectionNodeId.value}::block:${index}`;
const isNestedActive = (index: number) => landingStore.activeBlock?.nodeId === buildNestedNodeId(index);
const isDropTarget = (index: number) => dropTarget.value === index;
const isDraggingBlock = (index: number) => {
    const dragState = landingStore.draggingBlock;
    return dragState?.sourceKind === 'section'
        && dragState.containerNodeId === sectionNodeId.value
        && dragState.blockIndex === index;
};

const isInvalidDropTarget = (dragState: DraggedLandingBlockState) => {
    const containerId = sectionNodeId.value;
    return containerId === dragState.nodeId || containerId.startsWith(`${dragState.nodeId}::`);
};

const openMediaPicker = () => mediaPickerRef.value?.open();
const handleMediaSelect = (media: any) => {
    const selected = Array.isArray(media) ? media[0] : media;
    if (selected?.url) state.bg_image = selected.url;
};

const openBlockPicker = () => blockPickerRef.value?.open();
const handleBlockSelect = (definition: any) => {
    if (definition?.isReusablePart && Array.isArray(definition.nestedBlocks) && definition.nestedBlocks.length > 0) {
        state.blocks.push(...definition.nestedBlocks.map((block: any) => ({
            type: block.type,
            data: cloneBlock(block.data || {}),
        })));

        clearNestedSelection();

        nextTick(() => {
            selectNestedBlock(state.blocks.length - definition.nestedBlocks.length);
        });

        return;
    }

    state.blocks.push({
        type: definition.key,
        data: cloneBlock(definition.defaultAttrs || {})
    });

    clearNestedSelection();

    nextTick(() => {
        selectNestedBlock(state.blocks.length - 1);
    });
};

const selectNestedBlock = (index: number) => {
    const block = state.blocks[index];
    if (!block) {
        return;
    }

    const definition = landingBlockRegistry.get(block.type);
    const nestedNodeId = buildNestedNodeId(index);

    landingStore.setActiveBlock({
        nodeId: nestedNodeId,
        type: block.type,
        data: cloneBlock(block.data || {}),
        updateAttributes: (attrs) => {
            const target = state.blocks[index];
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
};

const handleNestedPointerDown = (index: number, event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (target.closest('.section-block-tool')) {
        return;
    }

    event.stopPropagation();
    selectNestedBlock(index);
};

const clearNestedSelection = () => {
    if (!sectionNodeId.value) {
        return;
    }

    if (landingStore.activeBlock?.nodeId?.startsWith(`${sectionNodeId.value}::`)) {
        landingStore.clearActiveBlock();
    }
};

const removeBlock = (index: number) => {
    removeBlockAt(index);
};

const removeBlockAt = (index: number) => {
    const removedNodeId = buildNestedNodeId(index);
    const activeNodeId = landingStore.activeBlock?.nodeId;

    state.blocks.splice(index, 1);

    if (activeNodeId === removedNodeId || activeNodeId?.startsWith(`${removedNodeId}::`)) {
        landingStore.clearActiveBlock();
    } else if (sectionNodeId.value && activeNodeId?.startsWith(`${sectionNodeId.value}::`)) {
        landingStore.clearActiveBlock();
    }
};

const handleDragStart = (index: number, event: DragEvent) => {
    const block = state.blocks[index];
    if (!block) {
        event.preventDefault();
        return;
    }

    dropTarget.value = index;
    selectNestedBlock(index);

    landingStore.startBlockDrag({
        nodeId: buildNestedNodeId(index),
        sourceKind: 'section',
        containerNodeId: sectionNodeId.value,
        blockIndex: index,
        block: cloneBlock(block),
        removeFromSource: () => removeBlockAt(index),
    });

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.dropEffect = 'move';
        event.dataTransfer.setData('application/x-polycms-landing-block', buildNestedNodeId(index));
        event.dataTransfer.setData('text/plain', '');
    }
};

const handleDragOver = (index: number, event: DragEvent) => {
    if (!landingStore.draggingBlock || isInvalidDropTarget(landingStore.draggingBlock)) {
        return;
    }

    event.preventDefault();
    dropTarget.value = index;

    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
};

const resolveInsertIndexFromEvent = (event: DragEvent, index: number) => {
    const currentTarget = event.currentTarget as HTMLElement | null;
    if (!currentTarget) {
        return index;
    }

    const rect = currentTarget.getBoundingClientRect();
    const offsetY = event.clientY - rect.top;
    return offsetY < rect.height / 2 ? index : index + 1;
};

const handleItemDragOver = (index: number, event: DragEvent) => {
    handleDragOver(resolveInsertIndexFromEvent(event, index), event);
};

const handleItemDrop = (index: number, event: DragEvent) => {
    handleDrop(resolveInsertIndexFromEvent(event, index));
};

const handleListDragOver = (event: DragEvent) => {
    handleDragOver(state.blocks.length, event);
};

const handleListDrop = () => {
    handleDrop(state.blocks.length);
};

const handleDrop = (index: number) => {
    const dragState = landingStore.draggingBlock;
    if (!dragState || isInvalidDropTarget(dragState)) {
        handleDragEnd();
        return;
    }

    if (
        dragState.sourceKind === 'section'
        && dragState.containerNodeId === sectionNodeId.value
        && typeof dragState.blockIndex === 'number'
    ) {
        moveDraggedBlock(index, dragState.blockIndex);
    } else {
        insertDraggedBlock(index, dragState);
    }

    handleDragEnd();
};

const handleDragEnd = () => {
    dropTarget.value = null;
    landingStore.endBlockDrag();
};

const moveDraggedBlock = (targetIndex: number, sourceIndex: number) => {
    const sourceNodeId = buildNestedNodeId(sourceIndex);
    const activeNodeId = landingStore.activeBlock?.nodeId;
    const shouldReselectMovedBlock = activeNodeId === sourceNodeId;

    const [movedBlock] = state.blocks.splice(sourceIndex, 1);
    if (!movedBlock) {
        return;
    }

    let finalIndex = targetIndex;
    if (sourceIndex < targetIndex) {
        finalIndex -= 1;
    }

    finalIndex = Math.max(0, Math.min(finalIndex, state.blocks.length));
    state.blocks.splice(finalIndex, 0, movedBlock);

    if (sectionNodeId.value && activeNodeId?.startsWith(`${sectionNodeId.value}::`)) {
        landingStore.clearActiveBlock();
    }

    if (shouldReselectMovedBlock) {
        nextTick(() => selectNestedBlock(finalIndex));
    }
};

const insertDraggedBlock = (
    targetIndex: number,
    dragState: DraggedLandingBlockState
) => {
    const finalIndex = Math.max(0, Math.min(targetIndex, state.blocks.length));

    state.blocks.splice(finalIndex, 0, cloneBlock(dragState.block));
    dragState.removeFromSource?.();

    nextTick(() => selectNestedBlock(finalIndex));
};

watch(state, () => {
    const payload = buildPayload();
    const current = {
        ...(props.modelValue || props.data || {}),
        bg_type: props.modelValue?.bg_type || props.data?.bg_type || 'solid',
        bg_color: props.modelValue?.bg_color || props.data?.bg_color || '',
        bg_gradient_start: props.modelValue?.bg_gradient_start || props.data?.bg_gradient_start || '#4f46e5',
        bg_gradient_end: props.modelValue?.bg_gradient_end || props.data?.bg_gradient_end || '#7209b7',
        bg_gradient_angle: props.modelValue?.bg_gradient_angle || props.data?.bg_gradient_angle || 135,
        bg_image: props.modelValue?.bg_image || props.data?.bg_image || '',
        padding_class: props.modelValue?.padding_class || props.data?.padding_class || props.modelValue?.padding || 'py-16',
        text_color: props.modelValue?.text_color || props.data?.text_color || '',
        alignment: props.modelValue?.alignment || props.data?.alignment || 'left',
        full_width: props.modelValue?.full_width || props.data?.full_width || false,
        overlay: props.modelValue?.overlay || props.data?.overlay || false,
        blocks: JSON.parse(JSON.stringify(props.modelValue?.blocks || props.data?.blocks || [])),
        margin: props.modelValue?.margin || props.data?.margin || '',
        padding: props.modelValue?.padding || props.data?.padding || '',
    };

    if (JSON.stringify(payload) !== JSON.stringify(current)) {
        emit('update:modelValue', payload);
    }
}, { deep: true });

watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        syncState(newValue);
    }
}, { deep: true, immediate: true });

watch(() => props.data, (newData) => {
    if (newData) {
        syncState(newData);
    }
}, { deep: true });
</script>

<style scoped>
.section-block-children {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.section-block-empty {
    display: flex;
    min-height: 7rem;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 0;
    border: 1px dashed rgba(99, 102, 241, 0.25);
    background: rgba(79, 70, 229, 0.04);
}

.section-block-empty.is-drop-active {
    border-color: rgba(99, 102, 241, 0.7);
    background: rgba(99, 102, 241, 0.12);
}

.section-block-empty__label {
    font-size: 0.66rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #818cf8;
}

.section-block-list {
    display: flex;
    flex-direction: column;
}

.section-block-drop-zone {
    height: 0.6rem;
    border-radius: 0;
    border: 1px dashed transparent;
    background: transparent;
    transition: border-color 0.18s ease, background 0.18s ease, height 0.18s ease;
}

.section-block-children.is-dragging .section-block-drop-zone {
    border-color: rgba(129, 140, 248, 0.2);
}

.section-block-drop-zone.is-active {
    height: 0.95rem;
    border-color: rgba(99, 102, 241, 0.7);
    background: rgba(99, 102, 241, 0.14);
}

.section-block-item {
    border-radius: 0;
    border: 1px solid rgba(226, 232, 240, 0.9);
    background: rgba(255, 255, 255, 0.92);
    padding: 0.65rem 0.75rem 0.75rem;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
    cursor: pointer;
}

.section-block-item:hover,
.section-block-item.is-active {
    border-color: rgba(99, 102, 241, 0.7);
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.2);
}

.section-block-item.is-dragging {
    opacity: 0.45;
}

.dark .section-block-item {
    border-color: rgba(51, 65, 85, 0.95);
    background: rgba(17, 24, 39, 0.74);
}

.section-block-item__bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.55rem;
}

.section-block-item__meta {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    min-width: 0;
    flex-wrap: wrap;
}

.section-block-item__handle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.35rem;
    height: 1.35rem;
    border-radius: 0;
    background: rgba(99, 102, 241, 0.08);
    color: #6366f1;
}

.section-block-item__count {
    font-size: 0.66rem;
    font-weight: 900;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #a5b4fc;
}

.section-block-item__label {
    font-size: 0.64rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #64748b;
}

.dark .section-block-item__label {
    color: #94a3b8;
}

.section-block-item__badge {
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

.dark .section-block-item__badge {
    background: rgba(129, 140, 248, 0.14);
    color: #a5b4fc;
}

.section-block-item__tools {
    display: flex;
    gap: 0.35rem;
}

.section-block-tool {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.45rem;
    height: 1.45rem;
    border-radius: 0;
    border: 1px solid rgba(226, 232, 240, 0.9);
    background: rgba(255, 255, 255, 0.96);
    color: #64748b;
    transition: border-color 0.18s ease, color 0.18s ease;
}

.section-block-tool--danger:hover {
    border-color: rgba(248, 113, 113, 0.45);
    color: #ef4444;
}

.dark .section-block-tool {
    border-color: rgba(71, 85, 105, 0.8);
    background: rgba(15, 23, 42, 0.94);
    color: #cbd5e1;
}

.section-block-add {
    display: inline-flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    border-radius: 0;
    border: 1px dashed rgba(99, 102, 241, 0.32);
    background: rgba(79, 70, 229, 0.05);
    padding: 0.65rem 0.8rem;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #818cf8;
    transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease;
}

.section-block-add:hover {
    border-color: rgba(99, 102, 241, 0.65);
    background: rgba(99, 102, 241, 0.12);
}

.section-block-add--center {
    width: auto;
    min-width: 12rem;
}
</style>
