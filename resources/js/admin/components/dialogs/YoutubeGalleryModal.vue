<template>
  <div class="space-y-4">
    <div class="bg-admin-theme-input-bg p-4 rounded-xl border border-admin-theme-border">
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-2">{{ t('Display Layout') }}</label>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <!-- Grid Option -->
        <label 
          class="relative flex flex-col items-center p-3 cursor-pointer rounded-lg border-2 transition-colors"
          :class="form.layout === 'grid' ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
        >
          <input type="radio" v-model="form.layout" value="grid" class="sr-only" />
          <svg class="w-8 h-8 mb-2 text-admin-theme-text-muted" :class="{'text-admin-theme-primary': form.layout === 'grid'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          <span class="text-xs font-semibold text-admin-theme-text">{{ t('Grid') }}</span>
        </label>
        
        <!-- List Option -->
        <label 
          class="relative flex flex-col items-center p-3 cursor-pointer rounded-lg border-2 transition-colors"
          :class="form.layout === 'list' ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
        >
          <input type="radio" v-model="form.layout" value="list" class="sr-only" />
          <svg class="w-8 h-8 mb-2 text-admin-theme-text-muted" :class="{'text-admin-theme-primary': form.layout === 'list'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          <span class="text-xs font-semibold text-admin-theme-text">{{ t('List') }}</span>
        </label>

        <!-- Slider Option -->
        <label 
          class="relative flex flex-col items-center p-3 cursor-pointer rounded-lg border-2 transition-colors"
          :class="form.layout === 'slider' ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
        >
          <input type="radio" v-model="form.layout" value="slider" class="sr-only" />
          <svg class="w-8 h-8 mb-2 text-admin-theme-text-muted" :class="{'text-admin-theme-primary': form.layout === 'slider'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          <span class="text-xs font-semibold text-admin-theme-text">{{ t('Slider') }}</span>
        </label>

        <!-- Gallery Option -->
        <label 
          class="relative flex flex-col items-center p-3 cursor-pointer rounded-lg border-2 transition-colors"
          :class="form.layout === 'gallery' ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
        >
          <input type="radio" v-model="form.layout" value="gallery" class="sr-only" />
          <svg class="w-8 h-8 mb-2 text-admin-theme-text-muted" :class="{'text-admin-theme-primary': form.layout === 'gallery'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
          <span class="text-xs font-semibold text-admin-theme-text">{{ t('Gallery') }}</span>
        </label>
      </div>

      <!-- Slider specific options -->
      <div v-if="form.layout === 'slider'" class="mt-4 pt-4 border-t border-admin-theme-border grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Visible Items') }}</label>
          <select v-model="form.sliderVisibleItems" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-base text-admin-theme-text text-sm">
            <option :value="1">1 {{ t('item') }}</option>
            <option :value="2">2 {{ t('items') }}</option>
            <option :value="3">3 {{ t('items') }}</option>
            <option :value="4">4 {{ t('items') }}</option>
          </select>
        </div>
        <div class="flex items-center mt-6">
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.sliderAutoPlay" class="sr-only peer" :disabled="form.sliderContinuous">
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-admin-theme-primary opacity-100 peer-disabled:opacity-50"></div>
            <span class="ml-3 text-sm font-medium text-admin-theme-text peer-disabled:text-admin-theme-text-muted">{{ t('Auto Slide Step') }}</span>
          </label>
        </div>
        
        <!-- Continuous Marquee Options -->
        <div class="col-span-2 pt-2 mt-2 border-t border-admin-theme-border border-dashed grid grid-cols-2 gap-4">
          <div class="flex items-center mt-6">
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.sliderContinuous" class="sr-only peer" @change="form.sliderContinuous && (form.sliderAutoPlay = false)">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-admin-theme-primary"></div>
              <span class="ml-3 text-sm font-medium text-admin-theme-text">{{ t('Continuous Scroll') }}</span>
            </label>
          </div>
          <div>
            <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Scroll Direction') }}</label>
            <select v-model="form.sliderDirection" :disabled="!form.sliderContinuous && !form.sliderAutoPlay" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-base text-admin-theme-text text-sm disabled:opacity-50">
              <option value="left">{{ t('Left to Right') }}</option>
              <option value="right">{{ t('Right to Left') }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-medium text-admin-theme-text-secondary">{{ t('YouTube Video URLs') }}</label>
        <div class="flex items-center gap-3">
          <button 
            type="button" 
            @click="fetchAndShowProductVideos"
            class="text-xs font-semibold text-admin-theme-primary hover:text-admin-theme-primary-hover flex items-center gap-1 border border-admin-theme-primary/30 px-2.5 py-1 rounded-lg bg-admin-theme-primary/5 hover:bg-admin-theme-primary/10 transition-colors"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
            {{ t('Select from Product Videos') }}
          </button>
          <span class="text-xs text-admin-theme-text-muted">{{ form.urls.length }} {{ t('videos') }}</span>
        </div>
      </div>
      
      <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
        <div v-for="(url, index) in form.urls" :key="index" class="flex items-start gap-2">
          <div class="flex-1">
            <input 
              v-model="form.urls[index]" 
              type="text" 
              placeholder="https://www.youtube.com/watch?v=..."
              class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm"
              @blur="validateUrl(index)"
            />
            <div v-if="url && !isValidYoutube(url)" class="text-xs text-red-500 mt-1">
              {{ t('Invalid YouTube URL') }}
            </div>
          </div>
          <button 
            type="button" 
            @click="removeUrl(index)"
            class="p-2 text-admin-theme-text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors shrink-0"
            :title="t('Remove')"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
      </div>
      
      <button 
        type="button" 
        @click="addUrl"
        class="mt-3 flex items-center gap-1 text-sm font-medium text-admin-theme-primary hover:text-admin-theme-primary-hover transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
        {{ t('Add another video') }}
      </button>
    </div>
  </div>

  <!-- Product Videos Picker Modal -->
  <teleport to="body">
    <div v-if="showProductVideosPicker" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-admin-theme-surface border border-admin-theme-border rounded-xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col text-left">
        <!-- Modal Header -->
        <div class="p-4 border-b border-admin-theme-border flex items-center justify-between">
          <h3 class="text-base font-bold text-admin-theme-text">{{ t('Select from Product Videos') }}</h3>
          <button type="button" @click="showProductVideosPicker = false" class="text-admin-theme-text-muted hover:text-admin-theme-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <!-- Search Bar -->
        <div class="p-4 border-b border-admin-theme-border bg-admin-theme-base/30">
          <input 
            v-model="productVideoSearch" 
            type="text" 
            :placeholder="t('Search videos by title or product name...')"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm"
          />
        </div>

        <!-- Videos List -->
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
          <div v-if="loadingProductVideos" class="text-center py-8 text-admin-theme-text-muted">
            <div class="animate-spin inline-block w-6 h-6 border-[3px] border-current border-t-transparent text-admin-theme-primary rounded-full mb-2"></div>
            <p>{{ t('Loading videos...') }}</p>
          </div>
          <div v-else-if="filteredProductVideos.length === 0" class="text-center py-8 text-admin-theme-text-muted">
            <p>{{ t('No videos found.') }}</p>
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
          <span class="text-xs text-admin-theme-text-muted">{{ selectedProductVideoUrls.length }} {{ t('selected') }}</span>
          <div class="flex items-center gap-3">
            <button 
              type="button" 
              @click="showProductVideosPicker = false"
              class="px-4 py-2 text-sm text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-input-bg transition-colors"
            >
              {{ t('Cancel') }}
            </button>
            <button 
              type="button" 
              @click="addSelectedProductVideos"
              :disabled="selectedProductVideoUrls.length === 0"
              class="px-4 py-2 text-sm font-medium bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors disabled:opacity-50"
            >
              {{ t('Add Selected') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>

  <div class="pt-4 mt-6 flex items-center justify-end gap-3 border-t border-admin-theme-border">
    <button
      type="button"
      class="px-4 py-2 text-sm text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-input-bg transition-colors"
      @click="$emit('close')"
    >
      {{ t('Cancel') }}
    </button>
    <button
      type="button"
      class="px-4 py-2 text-sm font-medium bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      :disabled="!isValid"
      @click="submit"
    >
      {{ isEditing ? t('Update Gallery') : t('Insert Gallery') }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useTranslation } from '@/admin/composables/useTranslation';
import axios from 'axios';

const props = defineProps<{
  initialUrls?: string[];
  initialLayout?: string;
  initialSliderVisibleItems?: number;
  initialSliderAutoPlay?: boolean;
  initialSliderContinuous?: boolean;
  initialSliderDirection?: string;
  onSubmit?: (payload: { urls: string[], layout: string, sliderVisibleItems: number, sliderAutoPlay: boolean, sliderContinuous: boolean, sliderDirection: string }) => void;
}>();

const emit = defineEmits<{
  close: [];
}>();

const { t } = useTranslation();

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
  let urls = form.value.urls.map(u => u.trim()).filter(u => u !== '');
  
  selectedProductVideoUrls.value.forEach(url => {
    if (!urls.includes(url)) {
      urls.push(url);
    }
  });

  if (urls.length === 0) {
    urls.push('');
  }
  
  form.value.urls = urls;
  showProductVideosPicker.value = false;
  selectedProductVideoUrls.value = [];
};

const isEditing = computed(() => !!props.initialUrls && props.initialUrls.length > 0);

const form = ref({
  urls: [''],
  layout: 'grid',
  sliderVisibleItems: 1,
  sliderAutoPlay: false,
  sliderContinuous: false,
  sliderDirection: 'left'
});

onMounted(() => {
  if (props.initialUrls && props.initialUrls.length > 0) {
    form.value.urls = [...props.initialUrls];
  }
  if (props.initialLayout) {
    form.value.layout = props.initialLayout;
  }
  if (props.initialSliderVisibleItems !== undefined) {
    form.value.sliderVisibleItems = props.initialSliderVisibleItems;
  }
  if (props.initialSliderAutoPlay !== undefined) {
    form.value.sliderAutoPlay = props.initialSliderAutoPlay;
  }
  if (props.initialSliderContinuous !== undefined) {
    form.value.sliderContinuous = props.initialSliderContinuous;
  }
  if (props.initialSliderDirection !== undefined) {
    form.value.sliderDirection = props.initialSliderDirection;
  }
});

const isValidYoutube = (url: string) => {
  if (!url) return true; // Empty is ignored or handled
  return /(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/.test(url);
};

const validateUrl = (index: number) => {
  let url = form.value.urls[index];
  if (url) {
    url = url.trim();
    form.value.urls[index] = url;
  }
};

const addUrl = () => {
  form.value.urls.push('');
};

const removeUrl = (index: number) => {
  form.value.urls.splice(index, 1);
  if (form.value.urls.length === 0) {
    addUrl();
  }
};

const isValid = computed(() => {
  const validUrls = form.value.urls.filter(u => u.trim() && isValidYoutube(u));
  return validUrls.length > 0;
});

const submit = () => {
  if (!isValid.value) return;
  
  const cleanUrls = form.value.urls
    .map(u => u.trim())
    .filter(u => u && isValidYoutube(u));
    
  if (props.onSubmit && cleanUrls.length > 0) {
    props.onSubmit({
      urls: cleanUrls,
      layout: form.value.layout,
      sliderVisibleItems: form.value.sliderVisibleItems,
      sliderAutoPlay: form.value.sliderAutoPlay,
      sliderContinuous: form.value.sliderContinuous,
      sliderDirection: form.value.sliderDirection
    });
  }
  emit('close');
};
</script>
