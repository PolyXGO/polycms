<template>
  <!-- Settings Mode (for sidebar) -->
  <div v-if="mode === 'settings'" class="latest-products-block-settings space-y-4">
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading (Optional - Leave blank to hide)</label>
      <input v-model="state.heading" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary" placeholder="Featured Products (leave empty to hide)">
    </div>

    <!-- Product Selection Mode Switcher -->
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Product Selection Mode</label>
      <div class="grid grid-cols-2 gap-2 bg-admin-theme-input-bg p-1 rounded-xl border border-admin-theme-border">
        <button
          type="button"
          @click="state.selection_mode = 'filter'"
          :class="[
            'py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5',
            state.selection_mode === 'filter'
              ? 'bg-admin-theme-primary text-admin-theme-primary-content shadow-sm'
              : 'text-admin-theme-text-muted hover:text-admin-theme-text'
          ]"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
          Auto Filter
        </button>
        <button
          type="button"
          @click="state.selection_mode = 'custom'"
          :class="[
            'py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5',
            state.selection_mode === 'custom'
              ? 'bg-admin-theme-primary text-admin-theme-primary-content shadow-sm'
              : 'text-admin-theme-text-muted hover:text-admin-theme-text'
          ]"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
          Custom List
        </button>
      </div>
    </div>

    <!-- Mode A: Auto Filter Settings -->
    <div v-if="state.selection_mode === 'filter'" class="space-y-4">
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
    </div>

    <!-- Mode B: Custom Designated Products with Drag-and-Drop Sorting -->
    <div v-else class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Designated Products</label>
          <span class="text-xs text-admin-theme-text-muted">{{ state.custom_products_data.length }} products selected</span>
        </div>
        <button
          type="button"
          @click="openProductPicker"
          class="px-3 py-1.5 text-xs font-bold bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:opacity-90 transition-opacity flex items-center gap-1.5 shadow-sm"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Select Products
        </button>
      </div>

      <!-- Layout Mode Selector for Custom List -->
      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Layout Mode</label>
        <select v-model="state.layout" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary font-medium">
          <option value="slider">Touch Slider (Carousel)</option>
          <option value="grid">Standard Grid</option>
        </select>
      </div>

      <!-- Draggable Selected Products List -->
      <div v-if="state.custom_products_data.length === 0" class="p-6 text-center text-admin-theme-text-muted border border-dashed border-admin-theme-border rounded-xl bg-admin-theme-input-bg text-xs">
        <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
        No products selected yet. Click "Select Products" to choose items and drag to sort.
      </div>
      <div v-else class="space-y-2 max-h-72 overflow-y-auto pr-1">
        <div
          v-for="(prod, index) in state.custom_products_data"
          :key="prod.id"
          draggable="true"
          @dragstart="onProductDragStart(index, $event)"
          @dragover="onProductDragOver(index, $event)"
          @drop="onProductDrop(index, $event)"
          @dragend="onProductDragEnd"
          :class="[
            'flex items-center gap-2 p-2 rounded-lg border bg-admin-theme-base/60 transition-all select-none',
            draggedProductIndex === index ? 'opacity-40 border-dashed border-admin-theme-primary' : 'border-admin-theme-border hover:border-admin-theme-border/80'
          ]"
        >
          <!-- Drag Handle -->
          <div class="cursor-grab active:cursor-grabbing text-gray-400 hover:text-admin-theme-text p-1 shrink-0" title="Drag to reorder">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
            </svg>
          </div>

          <!-- Thumbnail -->
          <div class="w-9 h-9 rounded-md overflow-hidden bg-gray-100 dark:bg-gray-800 shrink-0 border border-admin-theme-border flex items-center justify-center">
            <img v-if="prod.featured_image_url || prod.thumbnail_url" :src="prod.featured_image_url || prod.thumbnail_url" class="w-full h-full object-cover" />
            <svg v-else class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          </div>

          <!-- Details -->
          <div class="flex-1 min-w-0">
            <div class="text-xs font-semibold text-admin-theme-text truncate">{{ prod.name }}</div>
            <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
              {{ typeof prod.price === 'number' ? ('$' + prod.price) : (prod.price || '') }}
            </div>
          </div>

          <!-- Remove Action -->
          <button
            type="button"
            @click="removeSelectedProduct(index)"
            class="p-1.5 text-admin-theme-text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors shrink-0"
            title="Remove from selection"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
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

    <!-- Toggle fields -->
    <div class="form-group px-1">
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

    <!-- Product Picker Modal -->
    <teleport to="body">
      <div v-if="showProductPickerModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/65 backdrop-blur-sm p-4">
        <div class="bg-admin-theme-card border border-admin-theme-border rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden text-left">
          <!-- Modal Header -->
          <div class="p-4 border-b border-admin-theme-border flex items-center justify-between bg-admin-theme-base/40">
            <div>
              <h3 class="text-base font-bold text-admin-theme-text">Select Products for Slider</h3>
              <p class="text-xs text-admin-theme-text-muted mt-0.5">Search and choose products from your catalog</p>
            </div>
            <button type="button" @click="showProductPickerModal = false" class="p-1 rounded-lg text-admin-theme-text-muted hover:text-admin-theme-text hover:bg-admin-theme-base transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <!-- Search Bar -->
          <div class="p-3 border-b border-admin-theme-border bg-admin-theme-base/20">
            <div class="relative">
              <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
              <input
                v-model="productSearchQuery"
                @input="onSearchInput"
                type="text"
                placeholder="Search products by title, SKU..."
                class="w-full pl-9 pr-4 py-2 border border-admin-theme-border rounded-xl bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:ring-2 focus:ring-admin-theme-primary"
              />
            </div>
          </div>

          <!-- Product Catalog List -->
          <div class="flex-1 overflow-y-auto p-4 space-y-2">
            <div v-if="loadingProductCatalog" class="text-center py-10 text-admin-theme-text-muted">
              <div class="w-7 h-7 border-2 border-admin-theme-primary border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
              <p class="text-xs">Loading products...</p>
            </div>
            <div v-else-if="availableProductCatalog.length === 0" class="text-center py-10 text-admin-theme-text-muted text-xs">
              No products found matching your search.
            </div>
            <div v-else class="space-y-2">
              <label
                v-for="prod in availableProductCatalog"
                :key="prod.id"
                class="flex items-center gap-3 p-3 rounded-xl border border-admin-theme-border hover:bg-admin-theme-base/80 cursor-pointer transition-colors"
                :class="selectedModalProductIds.includes(prod.id) ? 'border-admin-theme-primary bg-admin-theme-primary/5 ring-1 ring-admin-theme-primary/30' : ''"
              >
                <input
                  type="checkbox"
                  :checked="selectedModalProductIds.includes(prod.id)"
                  @change="toggleModalProduct(prod)"
                  class="rounded text-admin-theme-primary focus:ring-admin-theme-primary"
                />
                <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 shrink-0 border border-admin-theme-border flex items-center justify-center">
                  <img v-if="prod.featured_image_url || prod.thumbnail_url" :src="prod.featured_image_url || prod.thumbnail_url" class="w-full h-full object-cover" />
                  <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-semibold text-admin-theme-text truncate">{{ prod.name }}</div>
                  <div class="text-xs text-admin-theme-text-muted mt-0.5 flex items-center gap-2">
                    <span v-if="prod.sku" class="font-mono text-[10px] bg-admin-theme-base px-1.5 py-0.5 rounded border border-admin-theme-border">{{ prod.sku }}</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">${{ prod.price }}</span>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="p-4 border-t border-admin-theme-border flex items-center justify-between bg-admin-theme-base/30">
            <span class="text-xs text-admin-theme-text-muted">{{ selectedModalProductIds.length }} products selected</span>
            <div class="flex items-center gap-3">
              <button
                type="button"
                @click="showProductPickerModal = false"
                class="px-4 py-2 text-xs font-medium text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-input-bg transition-colors"
              >
                Cancel
              </button>
              <button
                type="button"
                @click="applyModalProducts"
                class="px-5 py-2 text-xs font-bold bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:opacity-90 transition-opacity"
              >
                Add Selected ({{ selectedModalProductIds.length }})
              </button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
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

    <!-- Custom Products Mode Preview -->
    <div v-if="state.selection_mode === 'custom' && state.custom_products_data.length > 0">
      <!-- Slider Layout -->
      <div v-if="state.layout === 'slider'" class="flex gap-4 overflow-x-auto pb-3 pt-1">
        <div v-for="prod in state.custom_products_data" :key="prod.id" class="flex-none w-[240px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm flex flex-col">
          <div v-if="state.show_media" class="relative w-full aspect-[16/10] bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 overflow-hidden">
            <img v-if="prod.featured_image_url || prod.thumbnail_url" :src="prod.featured_image_url || prod.thumbnail_url" class="w-full h-full object-cover" />
            <svg v-else width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
              <polyline points="21 15 16 10 5 21"/>
            </svg>
            <span v-if="state.show_badge" class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-bold shadow-xs bg-admin-theme-primary text-white">
              Selected
            </span>
          </div>

          <div class="p-3 flex flex-col flex-1">
            <div v-if="state.show_categories" class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">
              {{ prod.category_name || 'PRODUCT' }}
            </div>
            <h3 v-if="state.show_title" class="text-xs font-bold text-gray-900 dark:text-gray-100 mb-2 leading-snug truncate">{{ prod.name }}</h3>
            <div v-if="state.show_price" class="flex items-center gap-1.5 text-xs font-bold text-gray-900 dark:text-white mt-auto">
              <span class="text-emerald-600 dark:text-emerald-400">${{ prod.price }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid Layout -->
      <div v-else class="grid gap-4" :style="{ gridTemplateColumns: `repeat(${state.columns || 3}, minmax(0, 1fr))` }">
        <div v-for="prod in state.custom_products_data" :key="prod.id" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm flex flex-col">
          <div v-if="state.show_media" class="relative w-full aspect-[16/10] bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 overflow-hidden">
            <img v-if="prod.featured_image_url || prod.thumbnail_url" :src="prod.featured_image_url || prod.thumbnail_url" class="w-full h-full object-cover" />
            <svg v-else width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
              <polyline points="21 15 16 10 5 21"/>
            </svg>
          </div>

          <div class="p-3.5 flex flex-col flex-1">
            <div v-if="state.show_categories" class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">
              {{ prod.category_name || 'PRODUCT' }}
            </div>
            <h3 v-if="state.show_title" class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-2 leading-snug truncate">{{ prod.name }}</h3>
            <div v-if="state.show_price" class="flex items-center gap-2 text-xs font-bold text-gray-900 dark:text-white mt-auto">
              <span class="text-emerald-600 dark:text-emerald-400 font-bold">${{ prod.price }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Auto Filter Default Simulation Preview Mode -->
    <div v-else-if="state.layout === 'slider'" class="flex gap-4 overflow-x-auto pb-3 pt-1">
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

// Product Picker Modal State
const showProductPickerModal = ref(false);
const productSearchQuery = ref('');
const loadingProductCatalog = ref(false);
const availableProductCatalog = ref<any[]>([]);
const selectedModalProductIds = ref<number[]>([]);
const draggedProductIndex = ref<number | null>(null);

let searchTimeout: any = null;

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

const fetchProductsCatalog = async (query = '') => {
  loadingProductCatalog.value = true;
  try {
    const response = await axios.get('/api/v1/products', {
      params: {
        search: query,
        per_page: 40,
        status: 'published'
      }
    });
    availableProductCatalog.value = response.data?.data ?? [];
  } catch (err) {
    console.error('Error loading products catalog:', err);
  } finally {
    loadingProductCatalog.value = false;
  }
};

const onSearchInput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchProductsCatalog(productSearchQuery.value);
  }, 300);
};

const openProductPicker = () => {
  selectedModalProductIds.value = [...(state.custom_product_ids || [])];
  productSearchQuery.value = '';
  showProductPickerModal.value = true;
  fetchProductsCatalog('');
};

const toggleModalProduct = (prod: any) => {
  const idx = selectedModalProductIds.value.indexOf(prod.id);
  if (idx > -1) {
    selectedModalProductIds.value.splice(idx, 1);
  } else {
    selectedModalProductIds.value.push(prod.id);
  }
};

const applyModalProducts = () => {
  state.custom_product_ids = [...selectedModalProductIds.value];
  
  // Update custom_products_data preserving order
  const existingMap = new Map<number, any>();
  state.custom_products_data.forEach(p => existingMap.set(p.id, p));
  availableProductCatalog.value.forEach(p => existingMap.set(p.id, p));

  const updatedData: any[] = [];
  state.custom_product_ids.forEach(id => {
    const item = existingMap.get(id);
    if (item) {
      updatedData.push({
        id: item.id,
        name: item.name,
        price: item.price,
        formatted_price: item.formatted_price,
        thumbnail_url: item.thumbnail_url,
        featured_image_url: item.featured_image_url,
        category_name: item.categories?.[0]?.name || item.category_name || ''
      });
    }
  });

  state.custom_products_data = updatedData;
  showProductPickerModal.value = false;
};

const removeSelectedProduct = (index: number) => {
  state.custom_products_data.splice(index, 1);
  state.custom_product_ids.splice(index, 1);
};

// Drag & drop handlers for products reordering
const onProductDragStart = (index: number, e: DragEvent) => {
  draggedProductIndex.value = index;
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(index));
  }
};

const onProductDragOver = (index: number, e: DragEvent) => {
  e.preventDefault();
  if (e.dataTransfer) {
    e.dataTransfer.dropEffect = 'move';
  }
};

const onProductDrop = (index: number, e: DragEvent) => {
  e.preventDefault();
  if (draggedProductIndex.value !== null && draggedProductIndex.value !== index) {
    const movedItem = state.custom_products_data.splice(draggedProductIndex.value, 1)[0];
    state.custom_products_data.splice(index, 0, movedItem);

    const movedId = state.custom_product_ids.splice(draggedProductIndex.value, 1)[0];
    state.custom_product_ids.splice(index, 0, movedId);
  }
  draggedProductIndex.value = null;
};

const onProductDragEnd = () => {
  draggedProductIndex.value = null;
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
  selection_mode: readAttr('selection_mode', 'filter') as 'filter' | 'custom',
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
  custom_product_ids: readAttr('custom_product_ids', [] as number[]),
  custom_products_data: readAttr('custom_products_data', [] as any[]),
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
    selection_mode: state.selection_mode,
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
    custom_product_ids: state.custom_product_ids,
    custom_products_data: state.custom_products_data,
  };
}

function syncState(source?: Record<string, any> | null) {
  isSyncingFromProps.value = true;
  state.heading = readSourceAttr(source, 'heading', DEFAULT_HEADING);
  state.selection_mode = readSourceAttr(source, 'selection_mode', 'filter');
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
  state.custom_product_ids = readSourceAttr(source, 'custom_product_ids', []);
  state.custom_products_data = readSourceAttr(source, 'custom_products_data', []);

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

