<template>
  <Teleport to="body">
    <Transition name="slide-panel">
      <div 
        class="landing-options-panel" 
        v-if="activeBlock"
        :class="{'resizing': isResizing, 'is-split-panel': !!parentActiveBlock }"
        :style="{ width: displayWidth + 'px' }"
        ref="panelRef"
      >
        <!-- Resize Handle -->
        <div class="resize-handle" @mousedown="startResizing"></div>

        <!-- SPLIT PANEL LAYOUT (When parentActiveBlock exists) -->
        <div v-if="parentActiveBlock" class="flex h-full w-full divide-x divide-admin-theme-border overflow-hidden">
          
          <!-- LEFT COLUMN: Parent (Modal Link Editor & Settings) -->
          <div class="w-1/2 flex flex-col h-full overflow-hidden bg-admin-theme-surface">
            <div class="landing-options-panel__header bg-indigo-500/10 dark:bg-indigo-950/30">
              <div class="panel-header-info">
                <div class="panel-header-icon text-indigo-500" v-html="parentBlockIcon"></div>
                <div class="panel-header-text">
                  <h4 class="text-indigo-600 dark:text-indigo-400 font-bold">{{ parentBlockLabel }}</h4>
                  <span class="text-[10px] text-indigo-400 font-semibold tracking-wider uppercase">Level 1 Container</span>
                </div>
              </div>
            </div>

            <div class="landing-options-panel__content flex-1 overflow-y-auto p-4">
              <component 
                v-if="parentSettingsComponent"
                :is="parentSettingsComponent"
                :key="parentSettingsComponentKey"
                :modelValue="parentBlockData"
                @update:modelValue="onParentDataUpdate"
                :is-editor="true"
                mode="settings"
              />
            </div>
          </div>

          <!-- RIGHT COLUMN: Selected Child Landing Block -->
          <div class="w-1/2 flex flex-col h-full overflow-hidden bg-admin-theme-surface">
            <div class="landing-options-panel__header bg-emerald-500/10 dark:bg-emerald-950/30">
              <div class="panel-header-info">
                <div class="panel-header-icon text-emerald-500" v-html="blockIcon"></div>
                <div class="panel-header-text">
                  <h4 class="text-emerald-600 dark:text-emerald-400 font-bold">{{ blockLabel }}</h4>
                  <span class="text-[10px] text-emerald-500 font-semibold tracking-wider uppercase">Child Element Settings</span>
                </div>
              </div>
              <div class="flex items-center gap-1">
                <span v-if="actionToast" class="text-xs bg-admin-theme-primary text-white px-1.5 py-0.5 rounded font-medium shadow-sm transition-all animate-pulse">
                  {{ actionToast }}
                </span>
                <button type="button" @click="handleCopyOptions" class="panel-action-btn p-1 text-admin-theme-text-muted hover:text-admin-theme-primary rounded transition-colors" title="Copy Options">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                </button>
                <button type="button" @click="handlePasteOptions" :disabled="!landingStore.copiedBlockData" class="panel-action-btn p-1 text-admin-theme-text-muted hover:text-admin-theme-primary rounded transition-colors disabled:opacity-40" title="Paste Options">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </button>
                <button type="button" @click="clearChildSelection" class="panel-close-btn p-1 text-admin-theme-text-muted hover:text-admin-theme-text rounded transition-colors" title="Close Child Options">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>

            <div class="landing-options-panel__content flex-1 overflow-y-auto p-4">
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
                      <span class="text-xs font-bold text-admin-theme-primary">{{ parseSpacingValue(blockData.margin) }}px</span>
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
                      <span class="text-xs font-bold text-admin-theme-primary">{{ parseSpacingValue(blockData.padding) }}px</span>
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
        </div>

        <!-- SINGLE COLUMN LAYOUT (Normal) -->
        <div v-else class="flex flex-col h-full w-full overflow-hidden">
          <div class="landing-options-panel__header">
            <div class="panel-header-info">
              <div class="panel-header-icon" v-html="blockIcon"></div>
              <div class="panel-header-text">
                <h4>{{ blockLabel }}</h4>
                <span>{{ activeBlock.type }}</span>
              </div>
            </div>
            <div class="flex items-center gap-1">
              <span v-if="actionToast" class="text-xs bg-admin-theme-primary text-admin-theme-primary-content px-1.5 py-0.5 rounded font-medium shadow-sm transition-all animate-pulse">
                {{ actionToast }}
              </span>
              <button type="button" @click="handleCopyOptions" class="panel-action-btn p-1 text-admin-theme-text-muted hover:text-admin-theme-primary rounded transition-colors" title="Copy Options">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
              </button>
              <button type="button" @click="handlePasteOptions" :disabled="!landingStore.copiedBlockData" class="panel-action-btn p-1 text-admin-theme-text-muted hover:text-admin-theme-primary rounded transition-colors disabled:opacity-40" title="Paste Options">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
              </button>
              <button @click="clearSelection" class="panel-close-btn p-1 text-admin-theme-text-muted hover:text-admin-theme-text rounded transition-colors" title="Close">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>
          </div>
         
          <div class="landing-options-panel__content flex-1 overflow-y-auto p-4">
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
                    <span class="text-xs font-bold text-admin-theme-primary">{{ parseSpacingValue(blockData.margin) }}px</span>
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
                    <span class="text-xs font-bold text-admin-theme-primary">{{ parseSpacingValue(blockData.padding) }}px</span>
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

      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, watch, ref, Teleport, Transition, onMounted, onUnmounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useLandingStore } from '@/admin/stores/landingStore';
import { landingBlockRegistry } from '@/admin/editor/landingBlockRegistry';
import FormToggle from '@/admin/components/forms/FormToggle.vue';

const landingStore = useLandingStore();
const { activeBlock, parentActiveBlock, optionsWidth } = storeToRefs(landingStore);

const panelRef = ref<HTMLElement | null>(null);
const panelWidth = ref(360); // Default fallback width
const isResizing = ref(false);

const actionToast = ref('');
let toastTimer: any = null;

const displayWidth = computed(() => panelWidth.value || 360);

watch(parentActiveBlock, (newParent, oldParent) => {
  if (newParent && !oldParent) {
    panelWidth.value = Math.max(720, (panelWidth.value || 360) * 2);
  } else if (!newParent && oldParent) {
    panelWidth.value = Math.max(360, Math.round((panelWidth.value || 720) / 2));
  }
});

const triggerActionToast = (msg: string) => {
  actionToast.value = msg;
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    actionToast.value = '';
  }, 1500);
};

const handleCopyOptions = () => {
  if (!activeBlock.value) return;
  landingStore.copyBlockOptions(activeBlock.value.type, blockData.value);
  triggerActionToast('Options Copied!');
};

const handlePasteOptions = () => {
  if (!activeBlock.value || !landingStore.copiedBlockData) return;
  const merged = landingStore.pasteBlockOptions(activeBlock.value.type, blockData.value);
  onDataUpdate(merged);
  triggerActionToast('Options Pasted!');
};

const initWidth = () => {
  if (optionsWidth.value) {
    panelWidth.value = optionsWidth.value;
  } else {
    const defaultWidth = Math.max(360, window.innerWidth * 0.28);
    panelWidth.value = defaultWidth;
    landingStore.setOptionsWidth(defaultWidth);
  }
};

const startResizing = (event: MouseEvent) => {
  isResizing.value = true;
  event.preventDefault();
};

const handleMouseMove = (event: MouseEvent) => {
  if (!isResizing.value) return;
  const minW = parentActiveBlock.value ? 600 : 300;
  const newWidth = window.innerWidth - event.clientX;
  if (newWidth >= minW && newWidth <= window.innerWidth * 0.95) {
    panelWidth.value = newWidth;
  }
};

const stopResizing = () => {
  if (isResizing.value) {
    isResizing.value = false;
    landingStore.setOptionsWidth(panelWidth.value);
    landingStore.savePreference('optionsWidth', panelWidth.value);
  }
};

// Child Active Block Computed
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
  if (!activeBlock.value) return 'landing-settings-empty';
  return `${activeBlock.value.type}:${activeBlock.value.nodeId}`;
});

const blockData = ref<Record<string, any>>({});

watch(
  () => activeBlock.value?.data,
  (newData) => {
    if (newData) {
      const currentValStr = JSON.stringify(blockData.value);
      const newValStr = JSON.stringify(newData);
      if (currentValStr !== newValStr) {
        blockData.value = JSON.parse(JSON.stringify(newData));
      }
    } else {
      blockData.value = {};
    }
  },
  { deep: true, immediate: true }
);

// Parent Active Block (Modal Link Container) Computed
const parentBlockLabel = computed(() => {
  if (!parentActiveBlock.value) return '';
  return landingBlockRegistry.get(parentActiveBlock.value.type)?.label || parentActiveBlock.value.type;
});

const parentBlockIcon = computed(() => {
  if (!parentActiveBlock.value) return '';
  return landingBlockRegistry.get(parentActiveBlock.value.type)?.icon || `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>`;
});

const parentSettingsComponent = computed(() => {
  if (!parentActiveBlock.value) return null;
  if (parentActiveBlock.value.settingsComponent) {
    return parentActiveBlock.value.settingsComponent;
  }
  const definition = landingBlockRegistry.get(parentActiveBlock.value.type);
  return definition?.settingsComponent || definition?.component || null;
});

const parentSettingsComponentKey = computed(() => {
  if (!parentActiveBlock.value) return 'parent-settings-empty';
  return `parent:${parentActiveBlock.value.type}:${parentActiveBlock.value.nodeId}`;
});

const parentBlockData = ref<Record<string, any>>({});

watch(
  () => parentActiveBlock.value?.data,
  (newData) => {
    if (newData) {
      parentBlockData.value = JSON.parse(JSON.stringify(newData));
    } else {
      parentBlockData.value = {};
    }
  },
  { deep: true, immediate: true }
);

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

const onParentDataUpdate = (newData: Record<string, any>) => {
  parentBlockData.value = { ...newData };
  if (parentActiveBlock.value) {
    parentActiveBlock.value.data = { ...parentActiveBlock.value.data, ...newData };
    parentActiveBlock.value.updateAttributes({ data: parentActiveBlock.value.data });
  }
};

const parseSpacingValue = (value: string | undefined): number => {
  if (!value) return 0;
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

const clearChildSelection = () => {
  landingStore.clearChildActiveBlock();
};

const handleGlobalClick = (event: MouseEvent) => {
  if (!activeBlock.value || isResizing.value || !landingStore.autoHideSidebar) return;
  
  const target = event.target as HTMLElement;
  const panel = document.querySelector('.landing-options-panel');
  if (panel && panel.contains(target)) return;
  if (target.closest('.landing-block-wrapper')) return;
  if (target.closest('.tiptap-toolbar') || target.closest('[role="menu"]') || target.closest('.dropdown')) return;
  
  clearSelection();
};

onMounted(async () => {
  initWidth();
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
  top: 32px;
  right: 0;
  width: 360px;
  min-width: 300px;
  height: calc(100vh - 32px);
  background: rgb(var(--admin-theme-surface));
  border-left: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  z-index: 9999999;
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
  color: rgb(var(--admin-theme-primary));
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
  background: rgb(var(--admin-theme-surface));
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

.slide-panel-enter-active,
.slide-panel-leave-active {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.slide-panel-enter-from,
.slide-panel-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

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
