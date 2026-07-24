import { defineStore } from 'pinia';
import { ref, shallowRef, markRaw } from 'vue';
import type { Component } from 'vue';
import axios from 'axios';

export interface ActiveBlockState {
    /** Unique node ID from Tiptap */
    nodeId: string;
    /** Block type key (e.g., 'hero_section', 'section', 'row') */
    type: string;
    /** Block data attributes */
    data: Record<string, any>;
    /** Function to update block attributes in the editor */
    updateAttributes: (attrs: Record<string, any>) => void;
    /** Component to render in the sidebar for settings */
    settingsComponent?: Component;
}

export interface DraggedLandingBlockState {
    nodeId: string;
    sourceKind: 'root' | 'row' | 'section';
    containerNodeId?: string;
    rowColumnIndex?: number;
    blockIndex?: number;
    block: {
        type: string;
        data: Record<string, any>;
    };
    root?: {
        pos: number;
        size: number;
        json: Record<string, any>;
    };
    removeFromSource?: () => void;
}

export interface LandingDropTargetState {
    nodeId: string;
    placement: 'before' | 'after';
}

export const useLandingStore = defineStore('landing', () => {
    const activeBlock = shallowRef<ActiveBlockState | null>(null);
    const parentActiveBlock = shallowRef<ActiveBlockState | null>(null);
    const draggingBlock = shallowRef<DraggedLandingBlockState | null>(null);
    const dropTarget = shallowRef<LandingDropTargetState | null>(null);
    const sidebarVisible = ref(true);
    const optionsWidth = ref<number | null>(null);
    const currentPostType = ref<string>('post');
    const autoHideSidebar = ref(localStorage.getItem('polycms_auto_hide_sidebar') !== 'false');
    const activeItemIndex = ref<number>(-1);

    const copiedBlockData = ref<Record<string, any> | null>(null);
    const copiedBlockType = ref<string | null>(null);

    const copyBlockOptions = (type: string, data: Record<string, any>) => {
        copiedBlockType.value = type;
        copiedBlockData.value = JSON.parse(JSON.stringify(data || {}));
    };

    const pasteBlockOptions = (targetType: string, currentData: Record<string, any>): Record<string, any> => {
        if (!copiedBlockData.value) return currentData;
        const copied = JSON.parse(JSON.stringify(copiedBlockData.value));
        if (targetType === copiedBlockType.value) {
            return {
                ...currentData,
                ...copied
            };
        }
        const commonKeys = [
            'viewport_full_width', 'container_width', 'margin', 'padding', 
            'bg_color', 'text_color', 'border_color', 'border_radius',
            'shadow', 'custom_class', 'background_type', 'background_image'
        ];
        const merged = { ...currentData };
        commonKeys.forEach(key => {
            if (key in copied) {
                merged[key] = copied[key];
            }
        });
        return merged;
    };

    const setActiveBlock = (block: ActiveBlockState | null) => {
        if (block && block.settingsComponent) {
            block.settingsComponent = markRaw(block.settingsComponent);
        }

        // Handle nested selection: if current active block is a container like modalLink and selecting a child block
        const isModalContainer = (type?: string) => {
            if (!type) return false;
            const t = type.toLowerCase();
            return t === 'modallink' || t === 'modal_link' || t.includes('modal');
        };

        if (block && activeBlock.value && activeBlock.value.nodeId !== block.nodeId && isModalContainer(activeBlock.value.type)) {
            parentActiveBlock.value = activeBlock.value;
            activeBlock.value = block;
            activeItemIndex.value = -1;
            return;
        }

        if (!block) {
            activeBlock.value = null;
            parentActiveBlock.value = null;
        } else {
            if (isModalContainer(block.type) && parentActiveBlock.value?.nodeId === block.nodeId) {
                parentActiveBlock.value = null;
            }
            activeBlock.value = block;
        }
        activeItemIndex.value = -1;
    };

    const clearChildActiveBlock = () => {
        if (parentActiveBlock.value) {
            activeBlock.value = parentActiveBlock.value;
            parentActiveBlock.value = null;
        } else {
            activeBlock.value = null;
        }
        activeItemIndex.value = -1;
    };

    const clearActiveBlock = () => {
        activeBlock.value = null;
        parentActiveBlock.value = null;
        activeItemIndex.value = -1;
    };

    const startBlockDrag = (block: DraggedLandingBlockState) => {
        draggingBlock.value = block;
    };

    const setBlockDropTarget = (target: LandingDropTargetState | null) => {
        dropTarget.value = target;
    };

    const endBlockDrag = () => {
        draggingBlock.value = null;
        dropTarget.value = null;
    };

    const updateActiveBlockData = (newData: Record<string, any>) => {
        if (activeBlock.value) {
            activeBlock.value.data = { ...activeBlock.value.data, ...newData };
            activeBlock.value.updateAttributes({ data: activeBlock.value.data });
        }
    };
    const setActiveBlockData = (newData: Record<string, any>) => {
        if (activeBlock.value) {
            activeBlock.value = {
                ...activeBlock.value,
                data: JSON.parse(JSON.stringify(newData))
            };
        }
    };
    const toggleSidebar = () => {
        sidebarVisible.value = !sidebarVisible.value;
    };

    const setOptionsWidth = (width: number) => {
        optionsWidth.value = width;
    };

    const setPostType = (type: string) => {
        currentPostType.value = type;
    };

    const toggleAutoHideSidebar = () => {
        autoHideSidebar.value = !autoHideSidebar.value;
        localStorage.setItem('polycms_auto_hide_sidebar', String(autoHideSidebar.value));
    };

    const savePreference = async (key: string, value: any) => {
        try {
            await axios.put(`/api/v1/editor-panels/${currentPostType.value}`, {
                preferences: {
                    [key]: value
                }
            });
        } catch (err) {
            // eslint-disable-next-line no-console
            console.error('Failed to save landing preference', err);
        }
    };

    const fetchPreferences = async () => {
        try {
            const response = await axios.get(`/api/v1/editor-panels/${currentPostType.value}`);
            const prefs = response.data?.preferences || {};
            if (prefs.optionsWidth) {
                optionsWidth.value = Number(prefs.optionsWidth);
            }
        } catch (err) {
            // eslint-disable-next-line no-console
            console.error('Failed to fetch landing preferences', err);
        }
    };

    return {
        activeBlock,
        parentActiveBlock,
        draggingBlock,
        dropTarget,
        sidebarVisible,
        optionsWidth,
        currentPostType,
        autoHideSidebar,
        activeItemIndex,
        copiedBlockData,
        copiedBlockType,
        copyBlockOptions,
        pasteBlockOptions,
        setActiveBlock,
        clearActiveBlock,
        clearChildActiveBlock,
        startBlockDrag,
        setBlockDropTarget,
        endBlockDrag,
        updateActiveBlockData,
        setActiveBlockData,
        toggleSidebar,
        setOptionsWidth,
        setPostType,
        toggleAutoHideSidebar,
        savePreference,
        fetchPreferences,
    };
});
