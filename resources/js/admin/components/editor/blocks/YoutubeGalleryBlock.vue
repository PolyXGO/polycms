<template>
  <div v-if="mode === 'settings'" class="youtube-gallery-settings space-y-4">
    <div class="bg-admin-theme-input-bg p-4 rounded-xl border border-admin-theme-border">
      <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Display Layout</label>
      <div class="grid grid-cols-2 gap-3">
        <!-- Grid Option -->
        <label 
          class="relative flex flex-col items-center p-3 cursor-pointer rounded-lg border-2 transition-colors"
          :class="state.layout === 'grid' ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
        >
          <input type="radio" v-model="state.layout" value="grid" class="sr-only" />
          <svg class="w-6 h-6 mb-2 text-admin-theme-text-muted" :class="{'text-admin-theme-primary': state.layout === 'grid'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          <span class="text-xs font-semibold text-admin-theme-text">Grid</span>
        </label>
        
        <!-- List Option -->
        <label 
          class="relative flex flex-col items-center p-3 cursor-pointer rounded-lg border-2 transition-colors"
          :class="state.layout === 'list' ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
        >
          <input type="radio" v-model="state.layout" value="list" class="sr-only" />
          <svg class="w-6 h-6 mb-2 text-admin-theme-text-muted" :class="{'text-admin-theme-primary': state.layout === 'list'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          <span class="text-xs font-semibold text-admin-theme-text">List</span>
        </label>

        <!-- Slider Option -->
        <label 
          class="relative flex flex-col items-center p-3 cursor-pointer rounded-lg border-2 transition-colors"
          :class="state.layout === 'slider' ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
        >
          <input type="radio" v-model="state.layout" value="slider" class="sr-only" />
          <svg class="w-6 h-6 mb-2 text-admin-theme-text-muted" :class="{'text-admin-theme-primary': state.layout === 'slider'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          <span class="text-xs font-semibold text-admin-theme-text">Slider</span>
        </label>

        <!-- Gallery Option -->
        <label 
          class="relative flex flex-col items-center p-3 cursor-pointer rounded-lg border-2 transition-colors"
          :class="state.layout === 'gallery' ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
        >
          <input type="radio" v-model="state.layout" value="gallery" class="sr-only" />
          <svg class="w-6 h-6 mb-2 text-admin-theme-text-muted" :class="{'text-admin-theme-primary': state.layout === 'gallery'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
          <span class="text-xs font-semibold text-admin-theme-text">Gallery</span>
        </label>
      </div>

      <!-- Slider specific options -->
      <div v-if="state.layout === 'slider'" class="mt-4 pt-4 border-t border-admin-theme-border grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Visible Items</label>
          <select v-model="state.sliderVisibleItems" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-base text-admin-theme-text text-sm cursor-pointer">
            <option :value="1">1 item</option>
            <option :value="2">2 items</option>
            <option :value="3">3 items</option>
            <option :value="4">4 items</option>
          </select>
        </div>
        <div class="flex items-center mt-6">
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="state.sliderAutoPlay" class="sr-only peer" :disabled="state.sliderContinuous">
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-admin-theme-primary opacity-100 peer-disabled:opacity-50"></div>
            <span class="ml-3 text-xs font-bold uppercase text-gray-400 peer-disabled:text-admin-theme-text-muted">Auto Slide Step</span>
          </label>
        </div>
        
        <!-- Continuous Marquee Options -->
        <div class="col-span-2 pt-2 mt-2 border-t border-admin-theme-border border-dashed grid grid-cols-2 gap-4">
          <div class="flex items-center mt-6">
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="state.sliderContinuous" class="sr-only peer" @change="state.sliderContinuous && (state.sliderAutoPlay = false)">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-admin-theme-primary"></div>
              <span class="ml-3 text-xs font-bold uppercase text-gray-400">Continuous Scroll</span>
            </label>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Scroll Direction</label>
            <select v-model="state.sliderDirection" :disabled="!state.sliderContinuous && !state.sliderAutoPlay" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-base text-admin-theme-text text-sm disabled:opacity-50 cursor-pointer">
              <option value="left">Left to Right</option>
              <option value="right">Right to Left</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="flex items-center justify-between mb-2">
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">YouTube Video URLs</label>
        <div class="flex items-center gap-3">
          <button 
            type="button" 
            @click="fetchAndShowProductVideos"
            class="text-xs font-semibold text-admin-theme-primary hover:text-admin-theme-primary-hover flex items-center gap-1 border border-admin-theme-primary/30 px-2.5 py-1 rounded-lg bg-admin-theme-primary/5 hover:bg-admin-theme-primary/10 transition-colors"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
            Select from Product Videos
          </button>
          <span class="text-xs text-admin-theme-text-muted">{{ state.urls.length }} videos</span>
        </div>
      </div>
      
      <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
        <div
          v-for="(url, index) in state.urls"
          :key="index"
          draggable="true"
          @dragstart="onDragStart(index, $event)"
          @dragover="onDragOver(index, $event)"
          @drop="onDrop(index, $event)"
          @dragend="onDragEnd"
          :class="[
            'flex items-center gap-2 p-2 rounded-lg border bg-admin-theme-base/60 transition-all',
            draggedIndex === index ? 'opacity-40 border-dashed border-admin-theme-primary' : 'border-admin-theme-border hover:border-admin-theme-border/80'
          ]"
        >
          <!-- Drag Handle -->
          <div class="cursor-grab active:cursor-grabbing text-gray-400 hover:text-admin-theme-text p-1 shrink-0 select-none" title="Drag to reorder">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
            </svg>
          </div>

          <div class="flex-1 min-w-0">
            <input 
              v-model="state.urls[index]" 
              type="text" 
              placeholder="https://www.youtube.com/watch?v=..."
              class="w-full px-3 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary text-xs font-mono"
              @blur="validateUrl(index)"
            />
            <div v-if="url && !isValidYoutube(url)" class="text-[11px] text-red-500 mt-0.5">
              Invalid YouTube URL
            </div>
          </div>
          <button 
            type="button" 
            @click="removeUrl(index)"
            class="p-1.5 text-admin-theme-text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors shrink-0"
            title="Remove"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
      </div>
      
      <button 
        type="button" 
        @click="addUrl"
        class="mt-3 flex items-center gap-1 text-sm font-medium text-admin-theme-primary hover:text-admin-theme-primary-hover transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
        Add another video
      </button>
    </div>

    <!-- Product Videos Picker Modal -->
    <teleport to="body">
      <div v-if="showProductVideosPicker" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col text-left">
          <!-- Modal Header -->
          <div class="p-4 border-b border-admin-theme-border flex items-center justify-between">
            <h3 class="text-base font-bold text-admin-theme-text">Select from Product Videos</h3>
            <button type="button" @click="showProductVideosPicker = false" class="text-admin-theme-text-muted hover:text-admin-theme-text">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <!-- Search Bar -->
          <div class="p-4 border-b border-admin-theme-border bg-admin-theme-base/30">
            <input 
              v-model="productVideoSearch" 
              type="text" 
              placeholder="Search videos by title or product name..."
              class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm"
            />
          </div>

          <!-- Videos List -->
          <div class="flex-1 overflow-y-auto p-4 space-y-2">
            <div v-if="loadingProductVideos" class="text-center py-8 text-admin-theme-text-muted">
              <div class="animate-spin inline-block w-6 h-6 border-[3px] border-current border-t-transparent text-admin-theme-primary rounded-full mb-2"></div>
              <p>Loading videos...</p>
            </div>
            <div v-else-if="filteredProductVideos.length === 0" class="text-center py-8 text-admin-theme-text-muted">
              <p>No videos found.</p>
            </div>
            <div v-else class="space-y-2">
              <label 
                v-for="video in filteredProductVideos" 
                :key="video.url"
                class="flex items-start gap-3 p-3 rounded-lg border border-admin-theme-border hover:bg-admin-theme-hover cursor-pointer transition-colors"
              >
                <input 
                  type="checkbox" 
                  v-model="selectedProductVideoUrls" 
                  :value="video.url"
                  class="mt-1 rounded text-admin-theme-primary focus:ring-admin-theme-primary"
                />
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-semibold text-admin-theme-text truncate">{{ video.title }}</div>
                  <div class="text-xs text-admin-theme-text-muted mt-0.5 flex items-center gap-1.5">
                    <span class="px-1.5 py-0.5 rounded bg-admin-theme-base border border-admin-theme-border font-medium text-[10px] text-admin-theme-text-secondary truncate max-w-[150px] inline-block">{{ video.product_name }}</span>
                    <span class="truncate font-mono">{{ video.url }}</span>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="p-4 border-t border-admin-theme-border flex items-center justify-between bg-admin-theme-base/30">
            <span class="text-xs text-admin-theme-text-muted">{{ selectedProductVideoUrls.length }} selected</span>
            <div class="flex items-center gap-3">
              <button 
                type="button" 
                @click="showProductVideosPicker = false"
                class="px-4 py-2 text-sm text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-input-bg transition-colors"
              >
                Cancel
              </button>
              <button 
                type="button" 
                @click="addSelectedProductVideos"
                :disabled="selectedProductVideoUrls.length === 0"
                class="px-4 py-2 text-sm font-medium bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors disabled:opacity-50"
              >
                Add Selected
              </button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </div>

  <div v-else class="youtube-gallery-block-preview my-4 border border-admin-theme-border rounded-xl bg-admin-theme-base overflow-hidden relative shadow-sm">
    <div class="bg-admin-theme-input-bg px-3 py-2 flex items-center border-b border-admin-theme-border select-none">
      <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
      <span class="font-medium text-sm text-admin-theme-text">YouTube Video Gallery</span>
      <span class="text-xs px-2 py-0.5 rounded-full bg-admin-theme-base border border-admin-theme-border text-admin-theme-text-muted capitalize ml-2">
        {{ state.layout }}
      </span>
      <span class="text-xs text-admin-theme-text-muted ml-2">({{ state.urls.filter(Boolean).length }} videos)</span>
    </div>
    
    <div class="p-4">
      <div v-if="state.urls.filter(Boolean).length === 0" class="text-center text-admin-theme-text-muted py-8 text-sm bg-admin-theme-input-bg rounded-lg border border-dashed border-admin-theme-border">
        No YouTube videos added yet. Choose settings to add videos.
      </div>
      
      <div v-else-if="state.layout !== 'slider'" :class="layoutClass" class="relative pointer-events-none">
        <template v-for="(url, index) in state.urls.filter(Boolean).slice(0, 4)" :key="index">
          <div class="aspect-video relative rounded-md overflow-hidden bg-admin-theme-base-alt">
            <iframe :src="getEmbedUrl(url)" class="w-full h-full border-0" tabindex="-1"></iframe>
            <div v-if="index === 3 && state.urls.filter(Boolean).length > 4" class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm">
              <span class="text-white font-bold text-2xl">+{{ state.urls.filter(Boolean).length - 3 }}</span>
            </div>
          </div>
        </template>
      </div>
      
      <!-- Slider Preview -->
      <div v-else class="youtube-slider-preview pb-2 pointer-events-none flex flex-row flex-nowrap overflow-x-auto w-full">
        <template v-for="(url, index) in state.urls.filter(Boolean).slice(0, 6)" :key="index">
          <div class="px-2" :style="{ width: (100 / state.sliderVisibleItems) + '%', flex: '0 0 ' + (100 / state.sliderVisibleItems) + '%', maxWidth: (100 / state.sliderVisibleItems) + '%' }">
            <div class="relative rounded-md overflow-hidden bg-admin-theme-base-alt w-full aspect-video">
              <iframe :src="getEmbedUrl(url)" class="w-full h-full border-0" tabindex="-1"></iframe>
              <div v-if="index === 5 && state.urls.filter(Boolean).length > 6" class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm">
                <span class="text-white font-bold text-2xl">+{{ state.urls.filter(Boolean).length - 5 }}</span>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, watch, onMounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps<{
  modelValue: Record<string, any> | null;
  mode?: 'settings' | 'preview';
  data?: Record<string, any> | null;
}>();

const emit = defineEmits(['update:modelValue']);
const isSyncingFromProps = ref(false);

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
  urls: readAttr('urls', ['']),
  layout: readAttr('layout', 'grid'),
  sliderVisibleItems: readAttr('sliderVisibleItems', 1),
  sliderAutoPlay: readAttr('sliderAutoPlay', false),
  sliderContinuous: readAttr('sliderContinuous', false),
  sliderDirection: readAttr('sliderDirection', 'left')
});

const layoutClass = computed(() => {
  if (state.layout === 'grid') return 'grid grid-cols-2 gap-4';
  if (state.layout === 'list') return 'flex flex-col gap-4';
  if (state.layout === 'gallery') return 'grid grid-cols-3 gap-2';
  return 'grid grid-cols-2 gap-4';
});

const getEmbedUrl = (url: string) => {
  const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
  const videoId = match ? match[1] : null;
  return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
};

// Selection logic
const showProductVideosPicker = ref(false);
const loadingProductVideos = ref(false);
const productVideos = ref<any[]>([]);
const productVideoSearch = ref('');
const selectedProductVideoUrls = ref<string[]>([]);

const fetchAndShowProductVideos = async () => {
  showProductVideosPicker.value = true;
  loadingProductVideos.value = true;
  try {
    const response = await axios.get('/api/v1/products/preview-videos');
    productVideos.value = response.data || [];
  } catch (e) {
    console.error('Failed to fetch product videos:', e);
  } finally {
    loadingProductVideos.value = false;
  }
};

const filteredProductVideos = computed(() => {
  const query = productVideoSearch.value.toLowerCase().trim();
  if (!query) return productVideos.value;
  return productVideos.value.filter(v => 
    (v.title && v.title.toLowerCase().includes(query)) ||
    (v.product_name && v.product_name.toLowerCase().includes(query)) ||
    (v.url && v.url.toLowerCase().includes(query))
  );
});

const addSelectedProductVideos = () => {
  let currentUrls = state.urls.map(u => u.trim()).filter(u => u !== '');
  selectedProductVideoUrls.value.forEach(url => {
    if (!currentUrls.includes(url)) {
      currentUrls.push(url);
    }
  });
  if (currentUrls.length === 0) currentUrls.push('');
  state.urls = currentUrls;
  showProductVideosPicker.value = false;
  selectedProductVideoUrls.value = [];
};

const isValidYoutube = (url: string) => {
  if (!url) return true;
  return /(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/.test(url);
};

const validateUrl = (index: number) => {
  let url = state.urls[index];
  if (url) state.urls[index] = url.trim();
};

const draggedIndex = ref<number | null>(null);

const onDragStart = (index: number, e: DragEvent) => {
  draggedIndex.value = index;
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(index));
  }
};

const onDragOver = (index: number, e: DragEvent) => {
  e.preventDefault();
  if (e.dataTransfer) {
    e.dataTransfer.dropEffect = 'move';
  }
};

const onDrop = (index: number, e: DragEvent) => {
  e.preventDefault();
  if (draggedIndex.value !== null && draggedIndex.value !== index) {
    const moved = state.urls.splice(draggedIndex.value, 1)[0];
    state.urls.splice(index, 0, moved);
  }
  draggedIndex.value = null;
};

const onDragEnd = () => {
  draggedIndex.value = null;
};

const addUrl = () => {
  state.urls.push('');
};

const removeUrl = (index: number) => {
  state.urls.splice(index, 1);
  if (state.urls.length === 0) addUrl();
};

function buildPayload() {
  const base = props.modelValue || props.data || {};
  return {
    ...base,
    urls: state.urls.filter(Boolean),
    layout: state.layout,
    sliderVisibleItems: state.sliderVisibleItems,
    sliderAutoPlay: state.sliderAutoPlay,
    sliderContinuous: state.sliderContinuous,
    sliderDirection: state.sliderDirection
  };
}

function syncState(source?: Record<string, any>) {
  if (!source) return;
  isSyncingFromProps.value = true;
  state.urls = source.urls && source.urls.length > 0 ? [...source.urls] : [''];
  state.layout = source.layout || 'grid';
  state.sliderVisibleItems = source.sliderVisibleItems || 1;
  state.sliderAutoPlay = source.sliderAutoPlay || false;
  state.sliderContinuous = source.sliderContinuous || false;
  state.sliderDirection = source.sliderDirection || 'left';
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
