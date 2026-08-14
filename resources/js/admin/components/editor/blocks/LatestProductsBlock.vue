<template>
  <!-- Settings Mode (for sidebar) -->
  <div v-if="mode === 'settings'" class="latest-products-block-settings space-y-4">
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading (Optional - Leave blank to hide)</label>
      <input v-model="state.heading" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary" placeholder="Featured Products (leave empty to hide)">
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Filter Products By</label>
        <select v-model="state.filter_by" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary font-medium">
          <option value="featured">★ Featured (with Backfill)</option>
          <option value="best_sellers">🔥 Best Sellers</option>
          <option value="best_rated">★ Best Rated</option>
          <option value="trending">⚡ Trending</option>
          <option value="newest">✨ Newest</option>
          <option value="price_asc">💵 Price: Low to High</option>
          <option value="price_desc">💰 Price: High to Low</option>
        </select>
      </div>

      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Layout Mode</label>
        <select v-model="state.layout" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary font-medium">
          <option value="slider">Touch Slider (Carousel)</option>
          <option value="grid">Standard Grid</option>
        </select>
      </div>
    </div>

    <!-- Slider Specific Controls (Autoplay, Continuous Motion, Direction, Speed, Pause on Hover) -->
    <div v-if="state.layout === 'slider'" class="p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200/80 dark:border-gray-700/60 space-y-3">
      <div class="text-[10px] font-bold uppercase tracking-wider text-admin-theme-primary dark:text-indigo-400">Slider Autoplay Settings</div>
      <FormToggle
        name="slider_autoplay"
        v-model="state.slider_autoplay"
        size="sm"
        label="Enable Auto Slider (Autoplay)"
      />
      <div v-if="state.slider_autoplay" class="space-y-3 pt-1">
        <div class="grid grid-cols-2 gap-3">
          <div class="form-group">
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Motion Style</label>
            <select v-model="state.slider_mode" class="w-full bg-white dark:bg-gray-900 border-admin-theme-border rounded-lg p-1.5 text-xs focus:ring-2 focus:ring-admin-theme-primary">
              <option value="continuous">Continuous 1-Direction Flow (Seamless)</option>
              <option value="stepped">Stepped Card Slide (1-Direction)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Scroll Direction</label>
            <select v-model="state.slider_direction" class="w-full bg-white dark:bg-gray-900 border-admin-theme-border rounded-lg p-1.5 text-xs focus:ring-2 focus:ring-admin-theme-primary">
              <option value="left">Leftward (Forward ➔)</option>
              <option value="right">Rightward (Reverse ⬅)</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="form-group">
            <label class="block text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Speed (Seconds)</label>
            <input v-model.number="state.slider_speed" type="number" min="1" max="60" class="w-full bg-white dark:bg-gray-900 border-admin-theme-border rounded-lg p-1.5 text-xs focus:ring-2 focus:ring-admin-theme-primary" placeholder="4">
          </div>
          <div class="form-group flex items-end pb-1">
            <FormToggle
              name="pause_on_hover"
              v-model="state.pause_on_hover"
              size="sm"
              label="Pause on Hover / Touch"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Category</label>
      <select v-model="state.category_id" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
        <option value="">All Categories</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.name }}
        </option>
      </select>
    </div>
    
    <div class="grid grid-cols-2 gap-3">
      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Product Count</label>
        <input v-model.number="state.count" type="number" min="1" max="24" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
      </div>
      <div v-if="state.layout === 'grid'" class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Grid Columns</label>
        <select v-model.number="state.columns" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
          <option :value="2">2 Columns</option>
          <option :value="3">3 Columns</option>
          <option :value="4">4 Columns</option>
        </select>
      </div>
      <div v-else class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Offset (Skip)</label>
        <input v-model.number="state.offset" type="number" min="0" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary" placeholder="0">
      </div>
    </div>

    <div v-if="state.layout === 'grid'" class="grid grid-cols-2 gap-3">
      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Offset (Skip)</label>
        <input v-model.number="state.offset" type="number" min="0" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary" placeholder="0">
      </div>
      <div class="form-group flex items-end pb-1 px-1">
        <FormToggle
          name="show_view_all"
          v-model="state.show_view_all"
          size="sm"
          label='Show "View All"'
        />
      </div>
    </div>

    <div v-else class="form-group px-1">
      <FormToggle
        name="show_view_all"
        v-model="state.show_view_all"
        size="sm"
        label='Show "View All" Button'
      />
    </div>

    <div class="form-group space-y-2 mt-4 px-1 border-t border-admin-theme-border pt-4">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Display Fields & Badges</label>
      <FormToggle
        name="show_badge"
        v-model="state.show_badge"
        size="sm"
        label="Show Status Badge (★ Featured, 🔥 Best Seller, etc.)"
      />
      <FormToggle
        name="show_media"
        v-model="state.show_media"
        size="sm"
        label="Show Image"
      />
      <FormToggle
        name="show_title"
        v-model="state.show_title"
        size="sm"
        label="Show Title"
      />
      <FormToggle
        name="show_categories"
        v-model="state.show_categories"
        size="sm"
        label="Show Category"
      />
      <FormToggle
        name="show_price"
        v-model="state.show_price"
        size="sm"
        label="Show Price"
      />
    </div>
  </div>

  <!-- Preview Mode (for main editor canvas area) -->
  <div v-else class="latest-products-block-preview text-admin-theme-text dark:text-gray-100" :style="{ padding: state.padding, margin: state.margin }">
    <!-- Section Header (Only rendered when heading, view all or nav buttons exist) -->
    <div v-if="state.heading || state.show_view_all || state.layout === 'slider'"
         class="latest-products-header flex items-center mb-4"
         :class="state.heading ? 'justify-between' : 'justify-end'">
      <div v-if="state.heading" class="flex items-center gap-2">
        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold shadow-xs"
              :class="getFilterBadgeMeta().iconBgClass">
          {{ getFilterBadgeMeta().icon }}
        </span>
        <h2 class="latest-products-heading text-xl font-bold text-gray-900 dark:text-gray-50">{{ state.heading }}</h2>
      </div>

      <div class="flex items-center gap-2">
        <div v-if="state.show_view_all" class="text-xs font-semibold px-2.5 py-1 border border-gray-200 dark:border-gray-700 rounded-lg text-admin-theme-primary dark:text-indigo-400 cursor-pointer">
          View All &rarr;
        </div>
        <div v-if="state.layout === 'slider'" class="flex items-center gap-1">
          <button type="button" class="w-6 h-6 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 flex items-center justify-center text-xs">‹</button>
          <button type="button" class="w-6 h-6 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 flex items-center justify-center text-xs">›</button>
        </div>
      </div>
    </div>

    <!-- Slider Preview Mode -->
    <div v-if="state.layout === 'slider'" class="flex gap-4 overflow-x-auto pb-3 pt-1">
      <div v-for="i in Math.min(state.count || 4, 6)" :key="i" class="flex-none w-[240px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm flex flex-col">
        <!-- Media (Image) -->
        <div v-if="state.show_media" class="relative w-full aspect-[16/10] bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
          <span v-if="state.show_badge" class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-bold shadow-xs" :class="getFilterBadgeMeta().badgeClass">
            {{ getFilterBadgeMeta().label }}
          </span>
        </div>

        <div class="p-3 flex flex-col flex-1">
          <div v-if="state.show_categories" class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">WORDPRESS / MODULE</div>
          <h3 v-if="state.show_title" class="text-xs font-bold text-gray-900 dark:text-gray-100 mb-2 leading-snug">Sample Product Title #{{ i }}</h3>
          <div v-if="state.show_price" class="flex items-center gap-1.5 text-xs font-bold text-gray-900 dark:text-white mt-auto">
            <span class="text-emerald-600 dark:text-emerald-400">$49.00</span>
            <span class="text-[10px] text-gray-400 line-through font-normal">$69.00</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Grid Preview Mode -->
    <div v-else class="grid gap-4" :style="{ gridTemplateColumns: `repeat(${state.columns || 3}, minmax(0, 1fr))` }">
      <div v-for="i in Math.min(state.count || 3, 6)" :key="i" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm flex flex-col">
        <!-- Media (Image) -->
        <div v-if="state.show_media" class="relative w-full aspect-[16/10] bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
          <span v-if="state.show_badge" class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-bold shadow-xs" :class="getFilterBadgeMeta().badgeClass">
            {{ getFilterBadgeMeta().label }}
          </span>
        </div>

        <div class="p-3.5 flex flex-col flex-1">
          <div v-if="state.show_categories" class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">CATEGORIES</div>
          <h3 v-if="state.show_title" class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-2 leading-snug">Sample Product Grid #{{ i }}</h3>
          <div v-if="state.show_price" class="flex items-center gap-2 text-xs font-bold text-gray-900 dark:text-white mt-auto">
            <span class="text-emerald-600 dark:text-emerald-400 font-bold">$99.00</span>
            <span class="text-[11px] text-gray-400 line-through font-normal">$129.00</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { nextTick, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import FormToggle from '../../forms/FormToggle.vue';

const props = defineProps<{
  modelValue: any;
  isEditor?: boolean;
  mode?: 'settings' | 'preview';
  data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const DEFAULT_HEADING = 'Featured Products';
const DEFAULT_FILTER = 'featured';
const DEFAULT_LAYOUT = 'slider';
const DEFAULT_COUNT = 8;
const DEFAULT_COLUMNS = 3;
const DEFAULT_SHOW_VIEW_ALL = true;

const categories = ref<any[]>([]);

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/v1/product-categories', {
      params: {
        per_page: 100
      }
    });
    categories.value = response.data?.data ?? [];
  } catch (error) {
    console.error('Error fetching product categories:', error);
  }
};

onMounted(() => {
  fetchCategories();
});

function cloneValue<T>(value: T): T {
  if (value === undefined || value === null) {
    return value;
  }
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

function readSourceAttr<T>(source: Record<string, any> | null | undefined, key: string, fallback: T): T {
  if (hasAttr(source, key)) {
    return cloneValue(source?.[key]) as T;
  }
  return cloneValue(fallback) as T;
}

const state = reactive({
  heading: readAttr('heading', DEFAULT_HEADING),
  filter_by: readAttr('filter_by', DEFAULT_FILTER),
  layout: readAttr('layout', DEFAULT_LAYOUT),
  count: readAttr('count', DEFAULT_COUNT),
  columns: readAttr('columns', DEFAULT_COLUMNS),
  show_view_all: readAttr('show_view_all', DEFAULT_SHOW_VIEW_ALL),
  margin: readAttr('margin', ''),
  padding: readAttr('padding', ''),
  category_id: readAttr('category_id', ''),
  offset: readAttr('offset', 0),
  show_price: readAttr('show_price', true),
  show_media: readAttr('show_media', true),
  show_title: readAttr('show_title', true),
  show_categories: readAttr('show_categories', true),
  show_badge: readAttr('show_badge', true),
  slider_autoplay: readAttr('slider_autoplay', true),
  slider_mode: readAttr('slider_mode', 'continuous'),
  slider_direction: readAttr('slider_direction', 'left'),
  slider_speed: readAttr('slider_speed', 4),
  pause_on_hover: readAttr('pause_on_hover', true),
});

const isSyncingFromProps = ref(false);

function getFilterBadgeMeta() {
  const f = state.filter_by;
  if (f === 'best_sellers') {
    return {
      icon: '🔥',
      label: '🔥 Best Seller',
      iconBgClass: 'bg-orange-100 text-orange-600 dark:bg-orange-950 dark:text-orange-400',
      badgeClass: 'bg-orange-600 text-white',
    };
  }
  if (f === 'trending') {
    return {
      icon: '⚡',
      label: '⚡ Trending',
      iconBgClass: 'bg-purple-100 text-purple-600 dark:bg-purple-950 dark:text-purple-400',
      badgeClass: 'bg-purple-600 text-white',
    };
  }
  if (f === 'best_rated') {
    return {
      icon: '★',
      label: '★ Best Rated',
      iconBgClass: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-400',
      badgeClass: 'bg-yellow-500 text-slate-900',
    };
  }
  if (f === 'newest') {
    return {
      icon: '✨',
      label: '✨ Newest',
      iconBgClass: 'bg-sky-100 text-sky-600 dark:bg-sky-950 dark:text-sky-400',
      badgeClass: 'bg-sky-600 text-white',
    };
  }
  return {
    icon: '★',
    label: '★ Featured',
    iconBgClass: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-400',
    badgeClass: 'bg-slate-900/90 text-yellow-300',
  };
}

function buildPayload() {
  return {
    ...(props.modelValue || {}),
    heading: state.heading,
    filter_by: state.filter_by,
    layout: state.layout,
    count: state.count,
    columns: state.columns,
    show_view_all: state.show_view_all,
    margin: state.margin,
    padding: state.padding,
    category_id: state.category_id,
    offset: state.offset,
    show_price: state.show_price,
    show_media: state.show_media,
    show_title: state.show_title,
    show_categories: state.show_categories,
    show_badge: state.show_badge,
    slider_autoplay: state.slider_autoplay,
    slider_mode: state.slider_mode,
    slider_direction: state.slider_direction,
    slider_speed: state.slider_speed,
    pause_on_hover: state.pause_on_hover,
  };
}

function syncState(source?: Record<string, any> | null) {
  isSyncingFromProps.value = true;
  state.heading = readSourceAttr(source, 'heading', DEFAULT_HEADING);
  state.filter_by = readSourceAttr(source, 'filter_by', DEFAULT_FILTER);
  state.layout = readSourceAttr(source, 'layout', DEFAULT_LAYOUT);
  state.count = readSourceAttr(source, 'count', DEFAULT_COUNT);
  state.columns = readSourceAttr(source, 'columns', DEFAULT_COLUMNS);
  state.show_view_all = readSourceAttr(source, 'show_view_all', DEFAULT_SHOW_VIEW_ALL);
  state.margin = readSourceAttr(source, 'margin', '');
  state.padding = readSourceAttr(source, 'padding', '');
  state.category_id = readSourceAttr(source, 'category_id', '');
  state.offset = readSourceAttr(source, 'offset', 0);
  state.show_price = readSourceAttr(source, 'show_price', true);
  state.show_media = readSourceAttr(source, 'show_media', true);
  state.show_title = readSourceAttr(source, 'show_title', true);
  state.show_categories = readSourceAttr(source, 'show_categories', true);
  state.show_badge = readSourceAttr(source, 'show_badge', true);
  state.slider_autoplay = readSourceAttr(source, 'slider_autoplay', true);
  state.slider_mode = readSourceAttr(source, 'slider_mode', 'continuous');
  state.slider_direction = readSourceAttr(source, 'slider_direction', 'left');
  state.slider_speed = readSourceAttr(source, 'slider_speed', 4);
  state.pause_on_hover = readSourceAttr(source, 'pause_on_hover', true);

  nextTick(() => {
    isSyncingFromProps.value = false;
  });
}

function emitPayload() {
  emit('update:modelValue', buildPayload());
}

watch(state, () => {
  if (isSyncingFromProps.value) {
    return;
  }
  emitPayload();
});

watch(
  () => [props.modelValue, props.data],
  () => {
    if (isSyncingFromProps.value) {
      return;
    }
    syncState(props.modelValue || props.data || null);
  },
  { deep: true }
);
</script>
