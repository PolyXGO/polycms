<template>
  <!-- Settings Mode (for sidebar) -->
  <div v-if="mode === 'settings'" class="latest-posts-block-settings space-y-4">
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
      <input v-model="state.heading" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary" placeholder="Latest Updates">
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
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Post Count</label>
        <input v-model.number="state.count" type="number" min="1" max="12" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
      </div>
      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Columns</label>
        <select v-model.number="state.columns" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
          <option :value="2">2 Columns</option>
          <option :value="3">3 Columns</option>
          <option :value="4">4 Columns</option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Offset (Skip)</label>
        <input v-model.number="state.offset" type="number" min="0" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary" placeholder="0">
      </div>
      <div class="form-group flex items-end pb-1 px-1">
        <FormToggle
          name="show_view_all"
          v-model="state.show_view_all"
          size="sm"
          label='Show "View All" Button'
        />
      </div>
    </div>

    <div class="form-group space-y-2 mt-4 px-1 border-t border-admin-theme-border pt-4">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Display Fields</label>
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
        label="Show Categories"
      />
      <FormToggle
        name="show_excerpt"
        v-model="state.show_excerpt"
        size="sm"
        label="Show Excerpt"
      />
      <FormToggle
        name="show_author"
        v-model="state.show_author"
        size="sm"
        label="Show Author"
      />
      <FormToggle
        name="show_date"
        v-model="state.show_date"
        size="sm"
        label="Show Date"
      />
    </div>
  </div>

  <!-- Preview Mode (for main editor area) -->
  <div v-else class="latest-posts-block-preview text-admin-theme-text dark:text-gray-100" :style="{ padding: state.padding, margin: state.margin }">
    <div class="latest-posts-header flex justify-between items-center mb-6">
      <h2 class="latest-posts-heading text-2xl font-extrabold text-gray-900 dark:text-gray-50">{{ state.heading || 'Latest Updates' }}</h2>
      <div v-if="state.show_view_all" class="latest-posts-view-all text-xs font-semibold px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-lg text-admin-theme-primary dark:text-indigo-400">
        View All &rarr;
      </div>
    </div>

    <div class="latest-posts-grid" :style="{ '--grid-cols': state.columns || 3 }">
      <div v-for="i in Math.min(state.count || 3, 6)" :key="i" class="latest-post-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm flex flex-col">
        <!-- Media (Image) -->
        <div v-if="state.show_media" class="latest-post-image aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 dark:text-gray-500">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
        </div>
        <div v-if="state.show_title || state.show_categories || state.show_excerpt || state.show_author || state.show_date" class="latest-post-content p-4 flex flex-col flex-1">
          <div v-if="state.show_categories || state.show_date" class="latest-post-meta flex items-center gap-2 mb-2 text-xs">
            <span v-if="state.show_categories" class="latest-post-badge bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider text-[9px]">Category</span>
            <span v-if="state.show_date" class="latest-post-date text-gray-400 dark:text-gray-500">Just now</span>
          </div>
          <h3 v-if="state.show_title" class="latest-post-title text-base font-bold text-gray-950 dark:text-gray-100 mb-2 leading-snug">Sample Blog Post Title for Preview</h3>
          <p v-if="state.show_excerpt" class="latest-post-excerpt text-xs text-gray-600 dark:text-gray-400 line-clamp-2 leading-relaxed">This is a placeholder excerpt for the blog post preview. The actual content will be loaded dynamically on the frontend.</p>
          <div v-if="state.show_author" class="latest-post-author text-[11px] text-gray-400 dark:text-gray-500 mt-2 font-medium">By Admin</div>
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

const DEFAULT_HEADING = 'Latest Updates';
const DEFAULT_COUNT = 6;
const DEFAULT_COLUMNS = 3;
const DEFAULT_SHOW_VIEW_ALL = true;

const categories = ref<any[]>([]);

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/v1/categories', {
      params: {
        per_page: 100,
        type: 'post'
      }
    });
    categories.value = response.data?.data ?? [];
  } catch (error) {
    console.error('Error fetching post categories:', error);
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
  count: readAttr('count', DEFAULT_COUNT),
  columns: readAttr('columns', DEFAULT_COLUMNS),
  show_view_all: readAttr('show_view_all', DEFAULT_SHOW_VIEW_ALL),
  margin: readAttr('margin', ''),
  padding: readAttr('padding', ''),
  category_id: readAttr('category_id', ''),
  offset: readAttr('offset', 0),
  show_media: readAttr('show_media', true),
  show_title: readAttr('show_title', true),
  show_categories: readAttr('show_categories', true),
  show_excerpt: readAttr('show_excerpt', true),
  show_author: readAttr('show_author', true),
  show_date: readAttr('show_date', true),
});

const isSyncingFromProps = ref(false);

function buildPayload() {
  return {
    ...(props.modelValue || {}),
    heading: state.heading,
    count: state.count,
    columns: state.columns,
    show_view_all: state.show_view_all,
    margin: state.margin,
    padding: state.padding,
    category_id: state.category_id,
    offset: state.offset,
    show_media: state.show_media,
    show_title: state.show_title,
    show_categories: state.show_categories,
    show_excerpt: state.show_excerpt,
    show_author: state.show_author,
    show_date: state.show_date,
  };
}

function syncState(source?: Record<string, any> | null) {
  isSyncingFromProps.value = true;
  state.heading = readSourceAttr(source, 'heading', DEFAULT_HEADING);
  state.count = readSourceAttr(source, 'count', DEFAULT_COUNT);
  state.columns = readSourceAttr(source, 'columns', DEFAULT_COLUMNS);
  state.show_view_all = readSourceAttr(source, 'show_view_all', DEFAULT_SHOW_VIEW_ALL);
  state.margin = readSourceAttr(source, 'margin', '');
  state.padding = readSourceAttr(source, 'padding', '');
  state.category_id = readSourceAttr(source, 'category_id', '');
  state.offset = readSourceAttr(source, 'offset', 0);
  state.show_media = readSourceAttr(source, 'show_media', true);
  state.show_title = readSourceAttr(source, 'show_title', true);
  state.show_categories = readSourceAttr(source, 'show_categories', true);
  state.show_excerpt = readSourceAttr(source, 'show_excerpt', true);
  state.show_author = readSourceAttr(source, 'show_author', true);
  state.show_date = readSourceAttr(source, 'show_date', true);

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
 if (props.mode ==='settings') {
 emitPayload();
 }
}, { deep: true });

watch(() => props.modelValue, (newValue) => {
  syncState(newValue);
}, { deep: true, immediate: true });

watch(() => props.data, (newData) => {
  if (newData) {
    syncState(newData);
  }
}, { deep: true, immediate: true });
</script>

<style scoped>
.latest-posts-block-preview {
 background: transparent;
 padding: 2rem 0;
}

.latest-posts-header {
 display: flex;
 justify-content: space-between;
 align-items: center;
 margin-bottom: 2rem;
}

.latest-posts-heading {
 font-size: 1.875rem;
 font-weight: 700;
 color: #111827;
 margin: 0;
}

.latest-posts-view-all {
 font-size: 0.875rem;
 font-weight: 600;
 color: rgb(var(--admin-theme-primary));
 padding: 0.5rem 1rem;
 border: 1px solid #e5e7eb;
 border-radius: 0.375rem;
}

.latest-posts-grid {
 display: grid;
 grid-template-columns: repeat(var(--grid-cols, 3), minmax(0, 1fr));
 gap: 1.5rem;
}

.latest-post-card {
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
}

.latest-post-image {
  aspect-ratio: 16 / 9;
  display: flex;
  align-items: center;
  justify-content: center;
}

.latest-post-content {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.latest-post-meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  font-size: 0.75rem;
}

.latest-post-badge {
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.latest-post-title {
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  line-height: 1.4;
}

.latest-post-excerpt {
  font-size: 0.875rem;
  margin: 0;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
