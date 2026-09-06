<template>
  <!-- Settings Mode (for sidebar) -->
  <div v-if="mode === 'settings'" class="latest-posts-block-settings space-y-4">
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
      <input v-model="state.heading" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary" placeholder="Latest Updates">
    </div>

    <!-- Posts Selection Mode Switcher -->
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Post Selection Mode</label>
      <div class="grid grid-cols-2 gap-2 bg-admin-theme-input-bg p-1 rounded-xl border border-admin-theme-border">
        <button
          type="button"
          @click="state.selection_mode = 'category'"
          :class="[
            'py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5',
            state.selection_mode === 'category'
              ? 'bg-admin-theme-primary text-admin-theme-primary-content shadow-sm'
              : 'text-admin-theme-text-muted hover:text-admin-theme-text'
          ]"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
          By Category
        </button>
        <button
          type="button"
          @click="state.selection_mode = 'specific'"
          :class="[
            'py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5',
            state.selection_mode === 'specific'
              ? 'bg-admin-theme-primary text-admin-theme-primary-content shadow-sm'
              : 'text-admin-theme-text-muted hover:text-admin-theme-text'
          ]"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
          Specific Posts
        </button>
      </div>
    </div>

    <!-- Mode A: By Category -->
    <div v-if="state.selection_mode === 'category'" class="space-y-4">
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
          <input v-model.number="state.count" type="number" min="1" max="24" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
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
            label='Show "View All"'
          />
        </div>
      </div>
    </div>

    <!-- Mode B: Specific Designated Posts with Drag-Move Reordering -->
    <div v-else class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Designated Posts</label>
          <span class="text-xs text-admin-theme-text-muted">{{ state.specific_posts_data.length }} posts selected</span>
        </div>
        <button
          type="button"
          @click="openPostPicker"
          class="px-3 py-1.5 text-xs font-bold bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:opacity-90 transition-opacity flex items-center gap-1.5 shadow-sm"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Select Posts
        </button>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="form-group">
          <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Columns</label>
          <select v-model.number="state.columns" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
            <option :value="2">2 Columns</option>
            <option :value="3">3 Columns</option>
            <option :value="4">4 Columns</option>
          </select>
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

      <!-- Draggable Selected Posts List -->
      <div v-if="state.specific_posts_data.length === 0" class="p-6 text-center text-admin-theme-text-muted border border-dashed border-admin-theme-border rounded-xl bg-admin-theme-input-bg text-xs">
        <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
        No posts selected yet. Click "Select Posts" to choose posts and drag to sort.
      </div>
      <div v-else class="space-y-2 max-h-72 overflow-y-auto pr-1">
        <div
          v-for="(post, index) in state.specific_posts_data"
          :key="post.id"
          draggable="true"
          @dragstart="onPostDragStart(index, $event)"
          @dragover="onPostDragOver(index, $event)"
          @drop="onPostDrop(index, $event)"
          @dragend="onPostDragEnd"
          :class="[
            'flex items-center gap-2 p-2 rounded-lg border bg-admin-theme-base/60 transition-all select-none',
            draggedPostIndex === index ? 'opacity-40 border-dashed border-admin-theme-primary' : 'border-admin-theme-border hover:border-admin-theme-border/80'
          ]"
        >
          <!-- Drag Handle -->
          <div class="cursor-grab active:cursor-grabbing text-gray-400 hover:text-admin-theme-text p-1 shrink-0" title="Drag to reorder">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
            </svg>
          </div>

          <!-- Thumbnail -->
          <div class="w-10 h-10 rounded-md overflow-hidden bg-gray-100 dark:bg-gray-800 shrink-0 border border-admin-theme-border flex items-center justify-center">
            <img v-if="post.featured_image_url || post.thumbnail_url" :src="post.featured_image_url || post.thumbnail_url" class="w-full h-full object-cover" />
            <svg v-else class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
          </div>

          <!-- Details -->
          <div class="flex-1 min-w-0">
            <div class="text-xs font-semibold text-admin-theme-text truncate">{{ post.title }}</div>
            <div class="text-[10px] text-admin-theme-text-muted mt-0.5 flex items-center gap-1.5">
              <span v-if="post.category_name" class="font-medium bg-admin-theme-base px-1.5 py-0.5 rounded border border-admin-theme-border truncate max-w-[120px]">{{ post.category_name }}</span>
              <span v-if="post.published_at" class="truncate">{{ post.published_at.substring(0, 10) }}</span>
            </div>
          </div>

          <!-- Remove Action -->
          <button
            type="button"
            @click="removeSelectedPost(index)"
            class="p-1.5 text-admin-theme-text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors shrink-0"
            title="Remove from selection"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Display Fields Settings -->
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

    <!-- Post Picker Modal -->
    <teleport to="body">
      <div v-if="showPostPickerModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/65 backdrop-blur-sm p-4">
        <div class="bg-admin-theme-card border border-admin-theme-border rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden text-left">
          <!-- Modal Header -->
          <div class="p-4 border-b border-admin-theme-border flex items-center justify-between bg-admin-theme-base/40">
            <div>
              <h3 class="text-base font-bold text-admin-theme-text">Select Posts</h3>
              <p class="text-xs text-admin-theme-text-muted mt-0.5">Search and select designated articles to display</p>
            </div>
            <button type="button" @click="showPostPickerModal = false" class="p-1 rounded-lg text-admin-theme-text-muted hover:text-admin-theme-text hover:bg-admin-theme-base transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <!-- Search Bar -->
          <div class="p-3 border-b border-admin-theme-border bg-admin-theme-base/20">
            <div class="relative">
              <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
              <input
                v-model="postSearchQuery"
                @input="onSearchInput"
                type="text"
                placeholder="Search posts by title..."
                class="w-full pl-9 pr-4 py-2 border border-admin-theme-border rounded-xl bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:ring-2 focus:ring-admin-theme-primary"
              />
            </div>
          </div>

          <!-- Posts Catalog List -->
          <div class="flex-1 overflow-y-auto p-4 space-y-2">
            <div v-if="loadingPostCatalog" class="text-center py-10 text-admin-theme-text-muted">
              <div class="w-7 h-7 border-2 border-admin-theme-primary border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
              <p class="text-xs">Loading posts...</p>
            </div>
            <div v-else-if="availablePostCatalog.length === 0" class="text-center py-10 text-admin-theme-text-muted text-xs">
              No posts found matching your search.
            </div>
            <div v-else class="space-y-2">
              <label
                v-for="post in availablePostCatalog"
                :key="post.id"
                class="flex items-center gap-3 p-3 rounded-xl border border-admin-theme-border hover:bg-admin-theme-base/80 cursor-pointer transition-colors"
                :class="selectedModalPostIds.includes(post.id) ? 'border-admin-theme-primary bg-admin-theme-primary/5 ring-1 ring-admin-theme-primary/30' : ''"
              >
                <input
                  type="checkbox"
                  :checked="selectedModalPostIds.includes(post.id)"
                  @change="toggleModalPost(post)"
                  class="rounded text-admin-theme-primary focus:ring-admin-theme-primary"
                />
                <div class="w-11 h-11 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 shrink-0 border border-admin-theme-border flex items-center justify-center">
                  <img v-if="post.featured_image_url || post.thumbnail_url" :src="post.featured_image_url || post.thumbnail_url" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-semibold text-admin-theme-text truncate">{{ post.title }}</div>
                  <div class="text-xs text-admin-theme-text-muted mt-0.5 flex items-center gap-2">
                    <span v-if="post.categories?.[0]?.name" class="font-medium bg-admin-theme-base px-1.5 py-0.5 rounded border border-admin-theme-border text-[10px]">{{ post.categories[0].name }}</span>
                    <span v-if="post.published_at" class="text-[11px]">{{ post.published_at.substring(0, 10) }}</span>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="p-4 border-t border-admin-theme-border flex items-center justify-between bg-admin-theme-base/30">
            <span class="text-xs text-admin-theme-text-muted">{{ selectedModalPostIds.length }} posts selected</span>
            <div class="flex items-center gap-3">
              <button
                type="button"
                @click="showPostPickerModal = false"
                class="px-4 py-2 text-xs font-medium text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-input-bg transition-colors"
              >
                Cancel
              </button>
              <button
                type="button"
                @click="applyModalPosts"
                class="px-5 py-2 text-xs font-bold bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:opacity-90 transition-opacity"
              >
                Add Selected ({{ selectedModalPostIds.length }})
              </button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </div>

  <!-- Preview Mode (for main editor area) -->
  <div v-else class="latest-posts-block-preview text-admin-theme-text dark:text-gray-100" :style="{ padding: state.padding, margin: state.margin }">
    <div class="latest-posts-header flex justify-between items-center mb-6">
      <h2 class="latest-posts-heading text-2xl font-extrabold text-gray-900 dark:text-gray-50">{{ state.heading || 'Latest Updates' }}</h2>
      <div v-if="state.show_view_all" class="latest-posts-view-all text-xs font-semibold px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-lg text-admin-theme-primary dark:text-indigo-400">
        View All &rarr;
      </div>
    </div>

    <!-- Specific Posts Mode Preview -->
    <div v-if="state.selection_mode === 'specific' && state.specific_posts_data.length > 0" class="latest-posts-grid" :style="{ '--grid-cols': state.columns || 3 }">
      <div v-for="post in state.specific_posts_data" :key="post.id" class="latest-post-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm flex flex-col">
        <!-- Media (Image) -->
        <div v-if="state.show_media" class="latest-post-image aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 dark:text-gray-500 overflow-hidden">
          <img v-if="post.featured_image_url || post.thumbnail_url" :src="post.featured_image_url || post.thumbnail_url" class="w-full h-full object-cover" />
          <svg v-else width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
        </div>
        <div v-if="state.show_title || state.show_categories || state.show_excerpt || state.show_author || state.show_date" class="latest-post-content p-4 flex flex-col flex-1">
          <div v-if="state.show_categories || state.show_date" class="latest-post-meta flex items-center gap-2 mb-2 text-xs">
            <span v-if="state.show_categories" class="latest-post-badge bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider text-[9px]">
              {{ post.category_name || 'Blog' }}
            </span>
            <span v-if="state.show_date" class="latest-post-date text-gray-400 dark:text-gray-500">
              {{ post.published_at ? post.published_at.substring(0, 10) : 'Recent' }}
            </span>
          </div>
          <h3 v-if="state.show_title" class="latest-post-title text-base font-bold text-gray-950 dark:text-gray-100 mb-2 leading-snug truncate">{{ post.title }}</h3>
          <p v-if="state.show_excerpt" class="latest-post-excerpt text-xs text-gray-600 dark:text-gray-400 line-clamp-2 leading-relaxed">
            {{ post.excerpt || 'Article summary description for the selected post.' }}
          </p>
          <div v-if="state.show_author" class="latest-post-author text-[11px] text-gray-400 dark:text-gray-500 mt-2 font-medium">By {{ post.author_name || 'Author' }}</div>
        </div>
      </div>
    </div>

    <!-- Default / Category Simulated Preview Mode -->
    <div v-else class="latest-posts-grid" :style="{ '--grid-cols': state.columns || 3 }">
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

// Post Picker Modal State
const showPostPickerModal = ref(false);
const postSearchQuery = ref('');
const loadingPostCatalog = ref(false);
const availablePostCatalog = ref<any[]>([]);
const selectedModalPostIds = ref<number[]>([]);
const draggedPostIndex = ref<number | null>(null);

let searchTimeout: any = null;

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

const fetchPostsCatalog = async (query = '') => {
  loadingPostCatalog.value = true;
  try {
    const response = await axios.get('/api/v1/posts', {
      params: {
        search: query,
        per_page: 40,
        type: 'post',
        status: 'published'
      }
    });
    availablePostCatalog.value = response.data?.data ?? [];
  } catch (err) {
    console.error('Error loading posts catalog:', err);
  } finally {
    loadingPostCatalog.value = false;
  }
};

const onSearchInput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchPostsCatalog(postSearchQuery.value);
  }, 300);
};

const openPostPicker = () => {
  selectedModalPostIds.value = [...(state.specific_post_ids || [])];
  postSearchQuery.value = '';
  showPostPickerModal.value = true;
  fetchPostsCatalog('');
};

const toggleModalPost = (post: any) => {
  const idx = selectedModalPostIds.value.indexOf(post.id);
  if (idx > -1) {
    selectedModalPostIds.value.splice(idx, 1);
  } else {
    selectedModalPostIds.value.push(post.id);
  }
};

const applyModalPosts = () => {
  state.specific_post_ids = [...selectedModalPostIds.value];
  
  // Update specific_posts_data preserving order
  const existingMap = new Map<number, any>();
  state.specific_posts_data.forEach(p => existingMap.set(p.id, p));
  availablePostCatalog.value.forEach(p => existingMap.set(p.id, p));

  const updatedData: any[] = [];
  state.specific_post_ids.forEach(id => {
    const item = existingMap.get(id);
    if (item) {
      updatedData.push({
        id: item.id,
        title: item.title,
        excerpt: item.excerpt,
        thumbnail_url: item.thumbnail_url,
        featured_image_url: item.featured_image_url,
        category_name: item.categories?.[0]?.name || '',
        published_at: item.published_at || '',
        author_name: item.user?.name || ''
      });
    }
  });

  state.specific_posts_data = updatedData;
  showPostPickerModal.value = false;
};

const removeSelectedPost = (index: number) => {
  state.specific_posts_data.splice(index, 1);
  state.specific_post_ids.splice(index, 1);
};

// Drag & drop handlers for posts reordering
const onPostDragStart = (index: number, e: DragEvent) => {
  draggedPostIndex.value = index;
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(index));
  }
};

const onPostDragOver = (index: number, e: DragEvent) => {
  e.preventDefault();
  if (e.dataTransfer) {
    e.dataTransfer.dropEffect = 'move';
  }
};

const onPostDrop = (index: number, e: DragEvent) => {
  e.preventDefault();
  if (draggedPostIndex.value !== null && draggedPostIndex.value !== index) {
    const movedItem = state.specific_posts_data.splice(draggedPostIndex.value, 1)[0];
    state.specific_posts_data.splice(index, 0, movedItem);

    const movedId = state.specific_post_ids.splice(draggedPostIndex.value, 1)[0];
    state.specific_post_ids.splice(index, 0, movedId);
  }
  draggedPostIndex.value = null;
};

const onPostDragEnd = () => {
  draggedPostIndex.value = null;
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
  selection_mode: readAttr('selection_mode', 'category') as 'category' | 'specific',
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
  specific_post_ids: readAttr('specific_post_ids', [] as number[]),
  specific_posts_data: readAttr('specific_posts_data', [] as any[]),
});

const isSyncingFromProps = ref(false);

function buildPayload() {
  return {
    ...(props.modelValue || {}),
    heading: state.heading,
    selection_mode: state.selection_mode,
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
    specific_post_ids: state.specific_post_ids,
    specific_posts_data: state.specific_posts_data,
  };
}

function syncState(source?: Record<string, any> | null) {
  isSyncingFromProps.value = true;
  state.heading = readSourceAttr(source, 'heading', DEFAULT_HEADING);
  state.selection_mode = readSourceAttr(source, 'selection_mode', 'category');
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
  state.specific_post_ids = readSourceAttr(source, 'specific_post_ids', []);
  state.specific_posts_data = readSourceAttr(source, 'specific_posts_data', []);

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
  if (props.mode === 'settings') {
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
