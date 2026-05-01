<template>
    <Teleport to="body">
        <Transition name="slide-panel">
            <div 
                class="landing-options-panel" 
                v-if="activeBlock"
                :class="{ 'resizing': isResizing }"
                :style="{ width: panelWidth + 'px' }"
                ref="panelRef"
            >
                <!-- Resize Handle -->
                <div class="resize-handle" @mousedown="startResizing"></div>

                <div class="landing-options-panel__header">
                    <div class="panel-header-info">
                        <div class="panel-header-icon" v-html="blockIcon"></div>
                        <div class="panel-header-text">
                            <h4>{{ blockLabel }}</h4>
                            <span>{{ activeBlock.type }}</span>
                        </div>
                    </div>
                    <button @click="clearSelection" class="panel-close-btn" title="Close">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <div class="landing-options-panel__content">
                    <component 
                        v-if="settingsComponent"
                        :is="settingsComponent"
                        :key="settingsComponentKey"
                        :modelValue="blockData"
                        @update:modelValue="onDataUpdate"
                        :is-editor="true"
                        mode="settings"
                    />
                    <div v-else class="panel-empty-msg">
                        No settings available for this block.
                    </div>

                    <!-- Common Block Settings -->
                    <div v-if="activeBlock" class="common-settings">
                        <div class="common-settings-header">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                            <span>Layout & Spacing</span>
                        </div>
                        
                        <div class="settings-grid">
                            <div class="form-group mb-6">
                                <div class="px-1">
                                    <FormToggle
                                        name="viewport_full_width"
                                        :modelValue="Boolean(blockData.viewport_full_width)"
                                        size="sm"
                                        label="Viewport Full Width (Breakout)"
                                        @update:modelValue="updateBooleanOption('viewport_full_width', $event)"
                                    />
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="mb-0">Margin</label>
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ parseSpacingValue(blockData.margin) }}px</span>
                                </div>
                                <div class="spacing-slider-container">
                                    <input 
                                        type="range" 
                                        min="0" 
                                        max="100" 
                                        :value="parseSpacingValue(blockData.margin)" 
                                        @input="updateSpacing('margin', ($event.target as HTMLInputElement).value)" 
                                        class="spacing-slider"
                                        :style="getRangeStyle(parseSpacingValue(blockData.margin), 0, 100)"
                                    />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="mb-0">Padding</label>
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ parseSpacingValue(blockData.padding) }}px</span>
                                </div>
                                <div class="spacing-slider-container">
                                    <input 
                                        type="range" 
                                        min="0" 
                                        max="100" 
                                        :value="parseSpacingValue(blockData.padding)" 
                                        @input="updateSpacing('padding', ($event.target as HTMLInputElement).value)" 
                                        class="spacing-slider"
                                        :style="getRangeStyle(parseSpacingValue(blockData.padding), 0, 100)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { computed, watch, ref, toRaw, Teleport, Transition, onMounted, onUnmounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useLandingStore } from '@/admin/stores/landingStore';
import { landingBlockRegistry } from '@/admin/editor/landingBlockRegistry';
import FormToggle from '@/admin/components/forms/FormToggle.vue';

const landingStore = useLandingStore();
const { activeBlock, optionsWidth } = storeToRefs(landingStore);

const panelRef = ref<HTMLElement | null>(null);
const panelWidth = ref(300); // Default fallback
const isResizing = ref(false);

// Initialize width from store or calculate 30%
const initWidth = () => {
    if (optionsWidth.value) {
        panelWidth.value = optionsWidth.value;
    } else {
        const defaultWidth = window.innerWidth * 0.3;
        panelWidth.value = defaultWidth;
        // Sync default to store so other components (like Save button) can react
        landingStore.setOptionsWidth(defaultWidth);
    }
};

const startResizing = (event: MouseEvent) => {
    isResizing.value = true;
    event.preventDefault();
};

const handleMouseMove = (event: MouseEvent) => {
    if (!isResizing.value) return;
    
    // Calculate new width: viewport width - current mouse X
    const newWidth = window.innerWidth - event.clientX;
    
    // Min/Max constraints
    if (newWidth > 250 && newWidth < window.innerWidth * 0.8) {
        panelWidth.value = newWidth;
    }
};

const stopResizing = () => {
    if (isResizing.value) {
        isResizing.value = false;
        // Save to store and persist to backend
        landingStore.setOptionsWidth(panelWidth.value);
        landingStore.savePreference('optionsWidth', panelWidth.value);
    }
};

const blockLabel = computed(() => {
    if (!activeBlock.value) return '';
    return landingBlockRegistry.get(activeBlock.value.type)?.label || activeBlock.value.type;
});

const blockIcon = computed(() => {
    if (!activeBlock.value) return '';
    return landingBlockRegistry.get(activeBlock.value.type)?.icon || `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>`;
});

const settingsComponent = computed(() => {
    if (!activeBlock.value) return null;
    if (activeBlock.value.settingsComponent) {
        return activeBlock.value.settingsComponent;
    }
    const definition = landingBlockRegistry.get(activeBlock.value.type);
    return definition?.settingsComponent || definition?.component || null;
});

const settingsComponentKey = computed(() => {
    if (!activeBlock.value) {
        return 'landing-settings-empty';
    }

    return `${activeBlock.value.type}:${activeBlock.value.nodeId}`;
});

const blockData = ref<Record<string, any>>({});

watch(activeBlock, (newVal) => {
    if (newVal) {
        blockData.value = { ...toRaw(newVal.data) };
    } else {
        blockData.value = {};
    }
}, { immediate: true });

// Sync store width back to local panelWidth if store changes (e.g. after fetch)
watch(optionsWidth, (newVal) => {
    if (newVal && !isResizing.value) {
        panelWidth.value = newVal;
    }
});

const onDataUpdate = (newData: Record<string, any>) => {
    blockData.value = { ...newData };
    if (activeBlock.value) {
        landingStore.updateActiveBlockData(newData);
    }
};

const parseSpacingValue = (value: string | undefined): number => {
    if (!value) return 0;
    // Extract first number found, default to 0
    const match = value.match(/(\d+)/);
    return match ? parseInt(match[1]) : 0;
};

const updateSpacing = (key: 'margin' | 'padding', value: string) => {
    blockData.value[key] = `${value}px`;
    onDataUpdate(blockData.value);
};

const updateBooleanOption = (key: string, value: boolean) => {
    blockData.value[key] = value;
    onDataUpdate(blockData.value);
};

const getRangeStyle = (value: number, min: number, max: number) => {
    const range = max - min;
    const percentage = range <= 0 ? 0 : ((value - min) / range) * 100;
    return { '--range-percent': `${percentage}%` } as Record<string, string>;
};

const clearSelection = () => {
    landingStore.clearActiveBlock();
};

// Handle click outside to close panel
const handleGlobalClick = (event: MouseEvent) => {
    if (!activeBlock.value || isResizing.value || !landingStore.autoHideSidebar) return;
    
    const target = event.target as HTMLElement;
    
    // Don't close if clicking inside the panel
    const panel = document.querySelector('.landing-options-panel');
    if (panel && panel.contains(target)) {
        return;
    }
    
    // Don't close if clicking on a landing block wrapper
    const landingBlockWrapper = target.closest('.landing-block-wrapper');
    if (landingBlockWrapper) {
        return;
    }
    
    // Don't close if clicking on toolbar buttons or dropdowns
    if (target.closest('.tiptap-toolbar') || target.closest('[role="menu"]') || target.closest('.dropdown')) {
        return;
    }
    
    // Clear selection when clicking outside
    clearSelection();
};

onMounted(async () => {
    initWidth();
    // Try to fetch preferences if not already loaded in this session
    if (!optionsWidth.value) {
        await landingStore.fetchPreferences();
    }
    
    document.addEventListener('click', handleGlobalClick);
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', stopResizing);
});

onUnmounted(() => {
    document.removeEventListener('click', handleGlobalClick);
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', stopResizing);
});
</script>

<style scoped>
.landing-options-panel {
    position: fixed;
    /* Account for topbar height - typically 32px */
    top: 32px;
    right: 0;
    /* Default width is handled by :style binding, but let's keep a fallback */
    width: 300px;
    min-width: 250px;
    /* Height minus topbar */
    height: calc(100vh - 32px);
    background: #fff;
    border-left: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    z-index: 1000; /* Increased z-index to stay above everything */
    box-shadow: -8px 0 32px rgba(0, 0, 0, 0.12);
}

.resize-handle {
    position: absolute;
    left: -4px;
    top: 0;
    width: 8px;
    height: 100%;
    cursor: ew-resize;
    z-index: 10;
    transition: background-color 0.2s;
}

.resize-handle:hover,
.landing-options-panel.resizing .resize-handle {
    background-color: rgba(99, 102, 241, 0.3);
}

.dark .landing-options-panel {
    background: #1f2937;
    border-color: #374151;
    box-shadow: -8px 0 32px rgba(0, 0, 0, 0.4);
}

.landing-options-panel__header {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9fafb;
    flex-shrink: 0;
}

.dark .landing-options-panel__header {
    background: #111827;
    border-color: #374151;
}

.panel-header-info {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    min-width: 0;
}

.panel-header-icon {
    width: 1.75rem;
    height: 1.75rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef2ff;
    border-radius: 0.375rem;
    color: #6366f1;
}

.dark .panel-header-icon {
    background: rgba(99, 102, 241, 0.2);
    color: #a5b4fc;
}

.panel-header-text {
    min-width: 0;
}

.panel-header-text h4 {
    font-size: 0.8125rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dark .panel-header-text h4 {
    color: #f9fafb;
}

.panel-header-text span {
    font-size: 0.625rem;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.panel-close-btn {
    width: 1.75rem;
    height: 1.75rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-radius: 0.375rem;
    color: #9ca3af;
    cursor: pointer;
    transition: all 0.15s;
}

.panel-close-btn:hover {
    background: #e5e7eb;
    color: #374151;
}

.dark .panel-close-btn:hover {
    background: #374151;
    color: #d1d5db;
}

.landing-options-panel__content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1rem;
}

.panel-empty-msg {
    padding: 1rem;
    text-align: center;
    color: #9ca3af;
    font-size: 0.8125rem;
}

/* Force vertical stacking for all form elements */
.landing-options-panel__content :deep(.form-group) {
    margin-bottom: 0.875rem;
    display: block;
}

.landing-options-panel__content :deep(label) {
    display: block;
    margin-bottom: 0.375rem;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
}

.landing-options-panel__content :deep(input:not([type="checkbox"])),
.landing-options-panel__content :deep(textarea),
.landing-options-panel__content :deep(select) {
    width: 100%;
    max-width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
    background: #fff;
}

.dark .landing-options-panel__content :deep(input),
.dark .landing-options-panel__content :deep(textarea),
.dark .landing-options-panel__content :deep(select) {
    background: #111827;
    border-color: #374151;
    color: #f9fafb;
}

.landing-options-panel__content :deep(textarea) {
    min-height: 70px;
    resize: vertical;
}

.landing-options-panel__content :deep(.grid) {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

/* Slide animation */
.slide-panel-enter-active,
.slide-panel-leave-active {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.slide-panel-enter-from,
.slide-panel-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

/* Common Settings Styling */
.common-settings {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f3f4f6;
}

.dark .common-settings {
    border-top-color: #374151;
}

.common-settings-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: #4b5563;
}

.dark .common-settings-header {
    color: #9ca3af;
}

.common-settings-header span {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.settings-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.spacing-inputs {
    display: flex;
    gap: 0.5rem;
}

.spacing-slider-container {
    padding: 0.4rem 0 0.25rem;
}

.spacing-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 8px;
    border-radius: 9999px;
    background: linear-gradient(to right, #6366f1 0%, #6366f1 var(--range-percent), #cbd5e1 var(--range-percent), #cbd5e1 100%);
    outline: none;
    cursor: pointer;
    transition: background 0.2s;
    padding: 0 !important;
    border: none !important;
    box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.38);
}

.dark .spacing-slider {
    background: linear-gradient(to right, #818cf8 0%, #818cf8 var(--range-percent), #374151 var(--range-percent), #374151 100%);
    box-shadow: inset 0 0 0 1px rgba(75, 85, 99, 0.65);
}

.spacing-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #6366f1;
    cursor: pointer;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.28);
}

.dark .spacing-slider::-webkit-slider-thumb {
    border-color: #111827;
}

.spacing-slider::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #6366f1;
    cursor: pointer;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.28);
}
</style>
