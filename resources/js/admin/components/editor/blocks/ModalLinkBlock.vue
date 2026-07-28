<template>
  <div v-if="mode === 'settings'" class="modal-link-settings space-y-4">
    <div class="form-group">
      <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Trigger Label</label>
      <input
        v-model="state.labelText"
        type="text"
        class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary"
        placeholder="e.g. Click Here"
      >
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div class="form-group">
        <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Display Style</label>
        <select
          v-model="state.displayMode"
          class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2 text-sm cursor-pointer"
        >
          <option value="button">Button Link</option>
          <option value="link">Simple Text Link</option>
        </select>
      </div>

      <div class="form-group">
        <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Modal Size</label>
        <select
          v-model="state.modalSize"
          class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2 text-sm cursor-pointer"
        >
          <option value="sm">Small Width</option>
          <option value="lg">Large Width</option>
          <option value="full">Full Width (Responsive)</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Content Type</label>
      <select
        v-model="state.contentType"
        class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2 text-sm cursor-pointer"
      >
        <option value="html">Custom HTML Content</option>
        <option value="iframe">Iframe URL</option>
      </select>
    </div>

    <div v-if="state.contentType === 'iframe'" class="form-group">
      <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Iframe Target URL</label>
      <input
        v-model="state.iframeUrl"
        type="text"
        class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary"
        placeholder="https://example.com/embed-content"
      >
    </div>

    <!-- Custom HTML / Rich Text TipTap Editor -->
    <div v-else class="form-group space-y-2">
      <div class="flex items-center justify-between">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Custom Content (TipTap Editor)</label>
        <!-- Editor Mode Switcher -->
        <div class="inline-flex rounded-lg border border-admin-theme-border bg-admin-theme-base p-0.5 text-[10px] font-bold">
          <button
            type="button"
            @click="editorTab = 'visual'"
            class="px-2 py-1 rounded transition-colors flex items-center gap-1"
            :class="editorTab === 'visual' ? 'bg-admin-theme-primary text-white shadow-sm' : 'text-gray-400 hover:text-white'"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
            <span>Visual TipTap</span>
          </button>
          <button
            type="button"
            @click="editorTab = 'code'"
            class="px-2 py-1 rounded transition-colors flex items-center gap-1"
            :class="editorTab === 'code' ? 'bg-admin-theme-primary text-white shadow-sm' : 'text-gray-400 hover:text-white'"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
            <span>HTML Code</span>
          </button>
        </div>
      </div>

      <!-- TipTap Rich Visual Editor -->
      <div v-if="editorTab === 'visual'" class="modal-link-tiptap-wrapper border border-admin-theme-border rounded-xl overflow-hidden bg-admin-theme-base">
        <TiptapEditor
          v-model="state.contentHtml"
          placeholder="Type or format your modal popup content here..."
        />
      </div>

      <!-- Raw HTML Code Editor -->
      <textarea
        v-else
        v-model="state.contentHtml"
        rows="8"
        class="w-full rounded-lg border border-admin-theme-border bg-admin-theme-base p-2.5 text-xs focus:ring-2 focus:ring-admin-theme-primary font-mono leading-relaxed"
        placeholder="<div>Provide custom HTML elements or text blocks here...</div>"
      ></textarea>
    </div>
  </div>

  <div v-else class="modal-link-block-preview my-4 border border-admin-theme-border rounded-xl bg-admin-theme-base overflow-hidden relative shadow-sm">
    <div class="bg-admin-theme-input-bg px-3 py-2 flex items-center border-b border-admin-theme-border select-none">
      <svg class="w-5 h-5 text-admin-theme-primary mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
      </svg>
      <span class="font-medium text-sm text-admin-theme-text">Landing Modal Link</span>
      <span class="text-xs px-2 py-0.5 rounded-full bg-admin-theme-base border border-admin-theme-border text-admin-theme-text-muted uppercase ml-2">
        {{ state.contentType }}
      </span>
    </div>
    
    <div class="p-5 flex flex-col items-center justify-center bg-admin-theme-base-alt/40">
      <div class="text-center">
        <span 
          :class="state.displayMode === 'link' 
            ? 'font-semibold text-admin-theme-primary hover:text-admin-theme-primary-hover underline decoration-dotted underline-offset-4 decoration-2' 
            : 'inline-block px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-semibold text-sm rounded-full shadow-md'"
        >
          {{ state.labelText }}
        </span>
        <p class="text-xs text-admin-theme-text-secondary mt-3">
          <span v-if="state.contentType === 'html'">Opens Modal displaying: <strong class="text-admin-theme-primary">Rich TipTap HTML Content</strong></span>
          <span v-else>Opens Modal displaying iframe: <strong class="text-admin-theme-primary">{{ state.iframeUrl || 'No link added yet' }}</strong></span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch, nextTick } from 'vue';
import TiptapEditor from '../../TiptapEditor.vue';

const props = defineProps<{
  modelValue: Record<string, any> | null;
  mode?: 'settings' | 'preview';
  data?: Record<string, any> | null;
}>();

const emit = defineEmits(['update:modelValue']);
const isSyncingFromProps = ref(false);
const editorTab = ref<'visual' | 'code'>('visual');

function cloneValue<T>(value: T): T {
  if (value === undefined || value === null) return value;
  return JSON.parse(JSON.stringify(value));
}

function hasAttr(source: Record<string, any> | null | undefined, key: string) {
  return Boolean(source) && Object.prototype.hasOwnProperty.call(source, key);
}

function readAttr<T>(key: string, fallback: T): T {
  if (hasAttr(props.modelValue, key)) {
    return cloneValue(props.modelValue?.[key]) as T;
  }
  if (hasAttr(props.data, key)) {
    return cloneValue(props.data?.[key]) as T;
  }
  return cloneValue(fallback) as T;
}

const state = reactive({
  labelText: readAttr('labelText', 'Click here'),
  modalSize: readAttr('modalSize', 'lg'),
  contentType: readAttr('contentType', 'html'),
  contentHtml: readAttr('contentHtml', ''),
  iframeUrl: readAttr('iframeUrl', ''),
  displayMode: readAttr('displayMode', 'button')
});

function buildPayload() {
  const base = props.modelValue || props.data || {};
  return {
    ...base,
    labelText: state.labelText,
    modalSize: state.modalSize,
    contentType: state.contentType,
    contentHtml: state.contentHtml,
    iframeUrl: state.iframeUrl,
    displayMode: state.displayMode
  };
}

function syncState(source?: Record<string, any>) {
  if (!source) return;
  isSyncingFromProps.value = true;
  state.labelText = source.labelText || 'Click here';
  state.modalSize = source.modalSize || 'lg';
  state.contentType = source.contentType || 'html';
  state.contentHtml = source.contentHtml || '';
  state.iframeUrl = source.iframeUrl || '';
  state.displayMode = source.displayMode || 'button';
  nextTick(() => {
    isSyncingFromProps.value = false;
  });
}

watch(
  state,
  () => {
    if (isSyncingFromProps.value) return;
    if (props.mode === 'settings') {
      emit('update:modelValue', buildPayload());
    }
  },
  { deep: true }
);

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) syncState(newValue);
  },
  { deep: true, immediate: true }
);
</script>

<style scoped>
.modal-link-tiptap-wrapper :deep(.tiptap-editor .ProseMirror) {
  min-height: 140px;
  max-height: 350px;
  overflow-y: auto;
  padding: 0.75rem;
}
</style>
