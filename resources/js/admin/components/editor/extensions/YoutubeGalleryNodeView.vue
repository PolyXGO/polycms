<template>
  <node-view-wrapper 
    class="youtube-gallery-node my-4 border rounded-xl bg-admin-theme-base overflow-hidden relative shadow-sm" 
    :class="{'ring-2 ring-admin-theme-primary': selected}"
  >
    <div class="bg-admin-theme-input-bg px-3 py-2 flex items-center justify-between border-b border-admin-theme-border cursor-pointer select-none group" contenteditable="false">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
        <span class="font-medium text-sm text-admin-theme-text">YouTube Video Gallery</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-admin-theme-base border border-admin-theme-border text-admin-theme-text-muted capitalize">
          {{ layout }}
        </span>
        <span class="text-xs text-admin-theme-text-muted">({{ urls.length }} videos)</span>
        <span v-if="layout === 'slider'" class="text-xs text-admin-theme-primary ml-1">
          (Show: {{ sliderVisibleItems }}, {{ sliderContinuous ? 'Continuous' : (sliderAutoPlay ? 'AutoPlay' : 'Manual') }}, Dir: {{ sliderDirection }})
        </span>
      </div>
      <!-- Quick Action Buttons -->
      <div class="flex items-center gap-2">
        <button 
          type="button" 
          @click.stop="editGallery"
          class="text-xs px-3 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg opacity-0 group-hover:opacity-100 transition-opacity font-medium shadow-sm hover:bg-admin-theme-primary-hover"
        >
          Edit Gallery
        </button>
        <button 
          type="button" 
          @click.stop="deleteNode"
          class="text-xs p-1.5 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-lg opacity-0 group-hover:opacity-100 transition-all shadow-sm flex items-center justify-center"
          title="Delete Gallery"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>
    
    <div class="p-4" contenteditable="false">
      <div v-if="urls.length === 0" class="text-center text-admin-theme-text-muted py-8 text-sm bg-admin-theme-input-bg rounded-lg border border-dashed border-admin-theme-border">
        No YouTube videos added yet. Click to select and edit.
      </div>
      
      <div v-if="layout !== 'slider'" :class="layoutClass" class="relative pointer-events-none">
        <template v-for="(url, index) in urls.slice(0, 4)" :key="index">
          <div class="aspect-video relative rounded-md overflow-hidden bg-admin-theme-base-alt">
            <iframe :src="getEmbedUrl(url)" class="w-full h-full border-0" tabindex="-1"></iframe>
            <div v-if="index === 3 && urls.length > 4" class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm">
              <span class="text-white font-bold text-2xl">+{{ urls.length - 3 }}</span>
            </div>
          </div>
        </template>
      </div>
      
      <!-- Slider Preview -->
      <div v-else class="youtube-slider-preview pb-2 pointer-events-none" style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; scrollbar-width: none !important; width: 100% !important;">
        <template v-for="(url, index) in urls.slice(0, 6)" :key="index">
          <div class="px-2" :style="{ width: (100 / sliderVisibleItems) + '%', flex: '0 0 ' + (100 / sliderVisibleItems) + '%', maxWidth: (100 / sliderVisibleItems) + '%' }">
            <div class="relative rounded-md overflow-hidden bg-admin-theme-base-alt" style="width: 100% !important; aspect-ratio: 16 / 9 !important; position: relative !important;">
              <iframe :src="getEmbedUrl(url)" class="w-full h-full border-0" tabindex="-1"></iframe>
              <div v-if="index === 5 && urls.length > 6" class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm">
                <span class="text-white font-bold text-2xl">+{{ urls.length - 5 }}</span>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';

const props = defineProps(nodeViewProps);

const urls = computed<string[]>(() => props.node.attrs.urls || []);
const layout = computed<string>(() => props.node.attrs.layout || 'grid');
const sliderVisibleItems = computed<number>(() => props.node.attrs.sliderVisibleItems || 1);
const sliderAutoPlay = computed<boolean>(() => props.node.attrs.sliderAutoPlay || false);
const sliderContinuous = computed<boolean>(() => props.node.attrs.sliderContinuous || false);
const sliderDirection = computed<string>(() => props.node.attrs.sliderDirection || 'left');

const getEmbedUrl = (url: string) => {
  const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
  const videoId = match ? match[1] : null;
  return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
};

// Limit to 4 for preview to not load too many iframes in the editor
const previewUrls = computed(() => {
  if (layout.value === 'list') return urls.value.slice(0, 2); // List takes a lot of vertical space
  return urls.value.slice(0, 4);
});

const layoutClass = computed(() => {
  if (layout.value === 'grid') return 'grid grid-cols-2 gap-4';
  if (layout.value === 'list') return 'flex flex-col gap-4';
  if (layout.value === 'gallery') return 'grid grid-cols-3 gap-2'; // Simplified for editor preview
  return 'grid grid-cols-2 gap-4';
});

const editGallery = () => {
  // Dispatch a custom event that TiptapEditor will listen to
  const event = new CustomEvent('open-youtube-gallery-modal', {
    detail: {
      urls: urls.value,
      layout: layout.value,
      sliderVisibleItems: sliderVisibleItems.value,
      sliderAutoPlay: sliderAutoPlay.value,
      sliderContinuous: sliderContinuous.value,
      sliderDirection: sliderDirection.value,
      editor: props.editor,
      getPos: props.getPos
    }
  });
  window.dispatchEvent(event);
};
</script>

<style scoped>
.youtube-slider-preview::-webkit-scrollbar {
  display: none !important;
  width: 0 !important;
  height: 0 !important;
}
.youtube-slider-preview {
  -ms-overflow-style: none !important;
  scrollbar-width: none !important;
}
</style>
