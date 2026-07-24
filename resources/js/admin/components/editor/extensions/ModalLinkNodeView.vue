<template>
  <node-view-wrapper 
    as="span"
    :class="[
      displayMode === 'link' 
        ? 'modal-link-inline-node inline relative cursor-pointer select-none group' 
        : 'modal-link-node block my-4 border rounded-xl bg-admin-theme-base overflow-hidden relative shadow-sm',
      {'ring-2 ring-admin-theme-primary': selected && displayMode !== 'link', 'bg-admin-theme-primary/10 ring-1 ring-admin-theme-primary rounded px-1': selected && displayMode === 'link'}
    ]"
    :style="displayMode === 'link' ? 'display: inline;' : 'display: block;'"
    contenteditable="false"
  >
    <!-- Inline Link Mode -->
    <template v-if="displayMode === 'link'">
      <span 
        @click.stop="editModalLink"
        class="inline font-semibold text-admin-theme-primary hover:text-admin-theme-primary-hover underline decoration-dotted underline-offset-4 decoration-2 transition-colors duration-150"
      >
        {{ labelText }}
      </span>
      <!-- Tiny floating hover bar for editing and deleting inline link -->
      <span class="invisible group-hover:visible absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 px-1.5 py-1 bg-slate-900/95 backdrop-blur text-white rounded-lg shadow-lg flex items-center gap-1.5 z-50 whitespace-nowrap text-xs transition-all duration-150 transform scale-95 group-hover:scale-100 border border-slate-700">
        <button 
          type="button" 
          @click.stop="editModalLink" 
          class="px-1.5 py-0.5 hover:bg-slate-800 rounded text-sky-400 font-semibold flex items-center gap-1 transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
          Edit
        </button>
        <span class="h-3.5 w-px bg-slate-700"></span>
        <button 
          type="button" 
          @click.stop="deleteNode" 
          class="px-1.5 py-0.5 hover:bg-red-500 rounded text-red-400 hover:text-white font-semibold flex items-center gap-1 transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          Delete
        </button>
      </span>
    </template>

    <!-- Button block mode -->
    <template v-else>
      <div class="bg-admin-theme-input-bg px-3 py-2 flex items-center justify-between border-b border-admin-theme-border cursor-pointer select-none group" contenteditable="false">
        <div class="flex items-center gap-2">
          <!-- SVG Icon representing a click / pop-up trigger -->
          <svg class="w-5 h-5 text-admin-theme-primary animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
          </svg>
          <span class="font-medium text-sm text-admin-theme-text">Landing Modal Link</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-admin-theme-base border border-admin-theme-border text-admin-theme-text-muted uppercase">
            {{ contentType }}
          </span>
          <span class="text-xs text-admin-theme-text-muted">
            Size: {{ modalSize === 'sm' ? 'Small' : (modalSize === 'lg' ? 'Large' : 'Full Width') }}
          </span>
        </div>
        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-2">
          <button 
            type="button" 
            @click.stop="editModalLink"
            class="text-xs px-3 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg opacity-0 group-hover:opacity-100 transition-opacity font-medium shadow-sm hover:bg-admin-theme-primary-hover"
          >
            Edit Element
          </button>
          <button 
            type="button" 
            @click.stop="deleteNode"
            class="text-xs p-1.5 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-lg opacity-0 group-hover:opacity-100 transition-all shadow-sm flex items-center justify-center"
            title="Delete Element"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
      
      <div class="p-5 flex flex-col items-center justify-center bg-admin-theme-base-alt/40" contenteditable="false">
        <!-- Visual representation of the trigger button inside editor -->
        <div class="text-center">
          <span class="inline-block px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-semibold text-sm rounded-full shadow-md cursor-pointer select-none">
            {{ labelText }}
          </span>
          <p class="text-xs text-admin-theme-text-secondary mt-3">
            <span v-if="contentType === 'html'">Opens Modal displaying: <strong class="text-admin-theme-primary">Rich HTML Content</strong></span>
            <span v-else>Opens Modal displaying iframe: <strong class="text-admin-theme-primary">{{ iframeUrl || 'No link added yet' }}</strong></span>
          </p>
        </div>
      </div>
    </template>
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';

const props = defineProps(nodeViewProps);

const labelText = computed<string>(() => props.node.attrs.labelText || 'Click here');
const modalSize = computed<string>(() => props.node.attrs.modalSize || 'lg');
const contentType = computed<string>(() => props.node.attrs.contentType || 'html');
const contentHtml = computed<string>(() => props.node.attrs.contentHtml || '');
const iframeUrl = computed<string>(() => props.node.attrs.iframeUrl || '');
const displayMode = computed<string>(() => props.node.attrs.displayMode || 'button');

const editModalLink = () => {
  // Dispatch custom event to let the TiptapEditor container know
  const event = new CustomEvent('open-modal-link-modal', {
    detail: {
      labelText: labelText.value,
      modalSize: modalSize.value,
      contentType: contentType.value,
      contentHtml: contentHtml.value,
      iframeUrl: iframeUrl.value,
      displayMode: displayMode.value,
      editor: props.editor,
      getPos: props.getPos
    }
  });
  window.dispatchEvent(event);
};
</script>

<style scoped>
.modal-link-node {
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
</style>
