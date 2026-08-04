<template>
 <div v-if="form" class="space-y-6">
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1" for="product-name-input">{{ $t('Name') }}</label>
 <input
 id="product-name-input"
 v-model="form.name"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary text-2xl font-bold"
 :placeholder="$t('Product name')"
 required
 @input="helpers.generateSlug?.()"
 />
 </div>

 <div v-if="form?.slug">
 <div class="flex flex-wrap items-center gap-4 bg-admin-theme-base/50 p-3 rounded-xl border border-dashed border-admin-theme-border">
 <div class="flex-1 flex items-center min-w-0 overflow-hidden">
 <span class="text-sm text-admin-theme-text-muted truncate">{{ slugPrefix }}</span>
 <input
 ref="slugInputRef"
 v-model="form.slug"
 type="text"
 class="flex-1 px-2 py-1 bg-transparent border-none text-sm font-semibold text-admin-theme-primary dark:text-admin-theme-primary focus:outline-none min-w-[50px]"
 :readonly="!isEditingSlug"
 required
 @input="handleSlugInput"
 @blur="handleSlugInput"
 />
 </div>
 <div class="flex gap-2 shrink-0">
 <button 
 type="button" 
 class="px-3 py-1.5 text-xs font-semibold bg-admin-theme-input-bg border border-admin-theme-border rounded-lg text-admin-theme-text-secondary hover:border-admin-theme-primary hover:text-admin-theme-primary transition-colors" 
 @click="toggleSlugEdit"
 >
 {{ isEditingSlug ? $t('Done') : $t('Edit Slug') }}
 </button>
 <button 
 type="button" 
 class="px-3 py-1.5 text-xs font-semibold bg-admin-theme-input-bg border border-admin-theme-border rounded-lg text-admin-theme-text-secondary hover:border-admin-theme-primary hover:text-admin-theme-primary transition-colors" 
 @click="copyPermalink"
 >
 {{ $t('Copy Link') }}
 </button>
 </div>
 </div>
 <div class="mt-2 text-[11px] text-admin-theme-text-muted pl-3">
 {{ $t('You can adjust the permalink structure at') }} <router-link :to="{ name: 'admin.settings.group', params: { group: 'permalinks' } }" class="text-admin-theme-primary dark:text-admin-theme-primary hover:underline font-medium">{{ $t('Permalink Settings') }}</router-link>
 </div>
 </div>

 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1" for="product-sku-input">{{ $t('SKU') }}</label>
 <input
 id="product-sku-input"
 v-model="form.sku"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 :placeholder="$t('SKU')"
 />
 </div>

 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1" for="product-description">{{ $t('Description') }}</label>
 <TiptapEditor
 id="product-description"
 v-model="descriptionHtml"
 v-model:json="descriptionBlocks"
 :placeholder="$t('Describe your product...')"
 />
 </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1" for="product-demo-url">
        {{ $t('Live Preview URL') }}
      </label>
      <input
        id="product-demo-url"
        v-model="form.settings.demo_url"
        type="url"
        class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
        placeholder="https://demo.example.com"
      />
    </div>

    <div>
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1" for="product-envato-item-id">
        {{ $t('Envato Item ID (for sync sales)') }}
      </label>
      <div class="flex items-center gap-2">
        <input
          id="product-envato-item-id"
          v-model="form.settings.envato_item_id"
          type="text"
          :disabled="!isDefaultLanguage"
          class="flex-1 px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800"
          placeholder="e.g. 12345678"
        />
        <button
          type="button"
          :disabled="!isDefaultLanguage || !form.settings.envato_item_id || isSyncingEnvato"
          class="px-3.5 py-2 text-xs font-semibold rounded-lg border transition-all inline-flex items-center gap-1.5 shrink-0"
          :class="[
            isDefaultLanguage && form.settings.envato_item_id && !isSyncingEnvato
              ? 'bg-sky-600 hover:bg-sky-700 text-white border-sky-600 shadow-sm cursor-pointer dark:bg-sky-500 dark:hover:bg-sky-600'
              : 'bg-admin-theme-border text-admin-theme-text-muted border-admin-theme-border cursor-not-allowed opacity-60'
          ]"
          @click="syncEnvatoSales"
        >
          <svg
            v-if="isSyncingEnvato"
            class="animate-spin h-3.5 w-3.5 text-current"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg
            v-else
            class="w-3.5 h-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          <span>{{ isSyncingEnvato ? $t('Syncing...') : $t('Sync Sales') }}</span>
        </button>
      </div>
      <p v-if="!isDefaultLanguage" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
        {{ $t('Envato Item ID and Market Sales Sync are managed in the primary language version and automatically synchronized to all language variants.') || 'Envato Item ID và dữ liệu Sync Sales được quản lý ở phiên bản ngôn ngữ gốc và tự động đồng bộ sang tất cả các phiên bản ngôn ngữ.' }}
      </p>
    </div>

    <div>
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1" for="product-preview-direct">
        {{ $t('Preview Target') }}
      </label>
      <div class="flex items-center h-[42px]">
        <label class="inline-flex items-center cursor-pointer">
          <input
            id="product-preview-direct"
            v-model="form.settings.preview_direct"
            type="checkbox"
            class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary h-4 w-4 bg-admin-theme-input-bg"
          />
          <span class="ml-2 text-sm text-admin-theme-text">
            {{ $t('Open in new tab (Direct Link)') }}
          </span>
        </label>
      </div>
    </div>
  </div>

 <!-- Product Videos Section -->
 <div class="pt-4 mt-2 border-t border-admin-theme-border space-y-4">
 <div>
 <h4 class="text-sm font-semibold text-gray-800">{{ $t('Product Videos') }}</h4>
 <p class="text-xs text-admin-theme-text-muted mt-1">
 {{ $t('Add YouTube video URLs to display in the product showcase gallery.') }}
 </p>
 </div>
 
 <div v-if="form.settings.preview_videos && form.settings.preview_videos.length" class="space-y-3">
 <div v-for="(video, index) in form.settings.preview_videos" :key="index" class="p-3 border border-admin-theme-border rounded-lg bg-admin-theme-card-bg space-y-2">
 <div class="flex items-center justify-between gap-2">
 <span class="text-xs font-semibold text-admin-theme-text-secondary">Video #{{ index + 1 }}</span>
 <button type="button" @click="removeVideo(index)" class="text-red-500 hover:text-red-700 text-xs">
 <i class="fas fa-trash mr-1"></i>{{ $t('Remove') }}
 </button>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
 <input
 v-model="video.title"
 type="text"
 class="px-2 py-1 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text"
 :placeholder="$t('Video Title')"
 />
 <input
 v-model="video.link"
 type="url"
 class="px-2 py-1 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text"
 :placeholder="$t('YouTube URL')"
 />
 </div>
 <div class="flex items-center gap-2">
 <input
 :id="'video-primary-' + index"
 type="checkbox"
 :checked="video.primary === '1' || video.primary === 1"
 @change="setPrimaryVideo(index, $event.target.checked)"
 class="h-3 w-3 rounded text-admin-theme-primary focus:ring-admin-theme-primary"
 />
 <label :for="'video-primary-' + index" class="text-xs text-admin-theme-text-muted cursor-pointer">
 {{ $t('Primary Video') }}
 </label>
 </div>
 </div>
 </div>
 
 <button type="button" @click="addVideo" class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold bg-sky-600 text-white hover:bg-sky-700">
 <i class="fas fa-plus mr-1"></i>{{ $t('Add Video') }}
 </button>
 </div>

 <div class="pt-4 mt-2 border-t border-admin-theme-border space-y-4">
 <div class="flex items-center justify-between gap-3">
 <div>
 <h4 class="text-sm font-semibold text-gray-800">{{ $t("FAQ's") }}</h4>
 <p class="text-xs text-admin-theme-text-muted mt-1">
 {{ $t('Configure FAQ content for this product from global FAQs or custom FAQs.') }}
 </p>
 </div>
 <router-link
 to="/admin/settings/global_faqs"
 class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold bg-sky-600 text-white hover:bg-sky-700"
 >
 {{ $t('Manage') }}
 </router-link>
 </div>

 <label class="inline-flex items-center gap-2 text-sm text-admin-theme-text-secondary">
 <input
 v-model="faqConfig.enabled"
 type="checkbox"
 class="h-4 w-4 rounded border-gray-300 text-admin-theme-primary focus:ring-admin-theme-primary"
 />
 {{ $t("Enable FAQ's tab") }}
 </label>

 <div v-if="faqConfig.enabled" class="space-y-4">
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-2">
 {{ $t('FAQ Source') }}
 </label>
 <div class="flex flex-wrap gap-2">
 <button
 v-for="option in faqSourceOptions"
 :key="option.value"
 type="button"
 class="px-3 py-1.5 rounded-md border text-xs font-semibold transition-colors"
 :class="faqConfig.source === option.value
 ?'border-admin-theme-primary bg-admin-theme-primary text-admin-theme-primary-content'
 :'border-admin-theme-border text-admin-theme-text-secondary hover:border-admin-theme-primary'"
 @click="setFaqSource(option.value)"
 >
 {{ option.label }}
 </button>
 </div>
 </div>

 <div v-if="usesGlobalFaqs" class="space-y-3">
 <label class="block text-sm font-medium text-admin-theme-text-secondary">
 {{ $t('Global FAQ Selection') }}
 </label>
 <div class="flex flex-wrap gap-2">
 <button
 type="button"
 class="px-3 py-1.5 rounded-md border border-admin-theme-border text-xs font-medium text-admin-theme-text-secondary hover:border-admin-theme-primary"
 @click="selectAllGlobalFaqs(true)"
 >
 {{ $t('Select All') }}
 </button>
 <button
 type="button"
 class="px-3 py-1.5 rounded-md border border-admin-theme-border text-xs font-medium text-admin-theme-text-secondary hover:border-admin-theme-primary"
 @click="selectAllGlobalFaqs(false)"
 >
 {{ $t('Deselect All') }}
 </button>
 </div>
 <div class="flex flex-wrap gap-2">
 <button
 v-for="option in globalModeOptionsFaq"
 :key="option.value"
 type="button"
 class="px-3 py-1.5 rounded-md border text-xs font-semibold transition-colors"
 :class="faqConfig.global_mode === option.value
 ?'border-admin-theme-primary bg-admin-theme-primary text-admin-theme-primary-content'
 :'border-admin-theme-border text-admin-theme-text-secondary hover:border-admin-theme-primary'"
 @click="faqConfig.global_mode = option.value"
 >
 {{ option.label }}
 </button>
 </div>

 <div v-if="faqConfig.global_mode ==='selected'" class="space-y-2">
 <label class="block text-xs font-semibold uppercase tracking-wide text-admin-theme-text-muted">
 {{ $t('Available Global FAQs') }}
 </label>
 <div class="max-h-56 overflow-auto rounded-lg border border-admin-theme-border p-2 bg-admin-theme-base/60">
 <label
 v-for="item in globalFaqItems"
 :key="item.id"
 class="flex items-start gap-2 p-2 rounded hover:bg-white"
 >
 <input
 :checked="faqConfig.global_ids.includes(item.id)"
 type="checkbox"
 class="mt-0.5 h-4 w-4 rounded border-gray-300 text-admin-theme-primary focus:ring-admin-theme-primary"
 @change="toggleGlobalFaq(item.id, ($event.target as HTMLInputElement).checked)"
 />
 <span class="text-sm text-admin-theme-text-secondary">{{ item.question }}</span>
 </label>
 <p v-if="globalFaqItems.length === 0" class="p-2 text-sm text-admin-theme-text-muted">
 {{ $t("No global FAQ's found. Please add them in Settings Hub > Global FAQ's.") }}
 </p>
 </div>
 </div>
 </div>

 <div v-if="usesCustomFaqs" class="space-y-3">
 <div class="flex items-center justify-between">
 <label class="block text-sm font-medium text-admin-theme-text-secondary">
 {{ $t("Custom FAQ's") }}
 </label>
 <button
 type="button"
 class="px-3 py-1.5 rounded-md bg-admin-theme-primary text-admin-theme-primary-content text-sm font-medium hover:bg-admin-theme-primary-hover"
 @click="addCustomFaq"
 >
 + {{ $t('Add FAQ') }}
 </button>
 </div>

 <div
 v-for="(item, index) in faqConfig.custom_items"
 :key="item.id"
 class="rounded-lg border border-admin-theme-border/90 p-3 space-y-3"
 >
 <div class="flex items-center gap-2">
 <input
 v-model="item.question"
 type="text"
 class="flex-1 px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 :placeholder="$t('Question title')"
 />
 <button
 type="button"
 class="px-2 py-1 text-xs rounded border border-admin-theme-border hover:border-admin-theme-primary"
 :disabled="index === 0"
 @click="moveCustomFaq(index, -1)"
 >
 ↑
 </button>
 <button
 type="button"
 class="px-2 py-1 text-xs rounded border border-admin-theme-border hover:border-admin-theme-primary"
 :disabled="index === faqConfig.custom_items.length - 1"
 @click="moveCustomFaq(index, 1)"
 >
 ↓
 </button>
 <button
 type="button"
 class="px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700"
 @click="removeCustomFaq(index)"
 >
 {{ $t('Delete') }}
 </button>
 </div>
 <TiptapEditor
 v-model="item.answer"
 :placeholder="$t('Answer...')"
 />
 <label class="inline-flex items-center gap-2 text-xs text-admin-theme-text-secondary">
 <input
 v-model="item.open"
 type="checkbox"
 class="h-4 w-4 rounded border-gray-300 text-admin-theme-primary focus:ring-admin-theme-primary"
 />
 {{ $t('Open by default') }}
 </label>
 </div>
 </div>
 </div>
 </div>

 <div class="pt-4 mt-2 border-t border-admin-theme-border space-y-4">
 <div class="flex items-center justify-between gap-3">
 <div>
 <h4 class="text-sm font-semibold text-gray-800">{{ $t('Custom Tabs') }}</h4>
 <p class="text-xs text-admin-theme-text-muted mt-1">
 {{ $t('Configure global tabs and product-specific custom tabs for product detail page.') }}
 </p>
 </div>
 <router-link
 to="/admin/settings/global_tabs"
 class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold bg-sky-600 text-white hover:bg-sky-700"
 >
 {{ $t('Manage') }}
 </router-link>
 </div>

 <label class="inline-flex items-center gap-2 text-sm text-admin-theme-text-secondary">
 <input
 v-model="tabConfig.enabled"
 type="checkbox"
 class="h-4 w-4 rounded border-gray-300 text-admin-theme-primary focus:ring-admin-theme-primary"
 />
 {{ $t('Enable custom tabs') }}
 </label>

 <div v-if="tabConfig.enabled" class="space-y-4">
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-2">
 {{ $t('Tabs Source') }}
 </label>
 <div class="flex flex-wrap gap-2">
 <button
 v-for="option in tabSourceOptions"
 :key="option.value"
 type="button"
 class="px-3 py-1.5 rounded-md border text-xs font-semibold transition-colors"
 :class="tabConfig.source === option.value
 ?'border-admin-theme-primary bg-admin-theme-primary text-admin-theme-primary-content'
 :'border-admin-theme-border text-admin-theme-text-secondary hover:border-admin-theme-primary'"
 @click="setTabSource(option.value)"
 >
 {{ option.label }}
 </button>
 </div>
 </div>

 <div v-if="usesGlobalTabs" class="space-y-3">
 <label class="block text-sm font-medium text-admin-theme-text-secondary">
 {{ $t('Global Tab Selection') }}
 </label>
 <div class="flex flex-wrap gap-2">
 <button
 type="button"
 class="px-3 py-1.5 rounded-md border border-admin-theme-border text-xs font-medium text-admin-theme-text-secondary hover:border-admin-theme-primary"
 @click="selectAllGlobalTabs(true)"
 >
 {{ $t('Select All') }}
 </button>
 <button
 type="button"
 class="px-3 py-1.5 rounded-md border border-admin-theme-border text-xs font-medium text-admin-theme-text-secondary hover:border-admin-theme-primary"
 @click="selectAllGlobalTabs(false)"
 >
 {{ $t('Deselect All') }}
 </button>
 </div>
 <div class="flex flex-wrap gap-2">
 <button
 v-for="option in globalModeOptionsTabs"
 :key="option.value"
 type="button"
 class="px-3 py-1.5 rounded-md border text-xs font-semibold transition-colors"
 :class="tabConfig.global_mode === option.value
 ?'border-admin-theme-primary bg-admin-theme-primary text-admin-theme-primary-content'
 :'border-admin-theme-border text-admin-theme-text-secondary hover:border-admin-theme-primary'"
 @click="tabConfig.global_mode = option.value"
 >
 {{ option.label }}
 </button>
 </div>

 <div v-if="tabConfig.global_mode ==='selected'" class="space-y-2">
 <div class="max-h-56 overflow-auto rounded-lg border border-admin-theme-border p-2 bg-admin-theme-base/60">
 <label
 v-for="item in globalTabItems"
 :key="item.id"
 class="flex items-start gap-2 p-2 rounded hover:bg-white"
 >
 <input
 :checked="tabConfig.global_ids.includes(item.id)"
 type="checkbox"
 class="mt-0.5 h-4 w-4 rounded border-gray-300 text-admin-theme-primary focus:ring-admin-theme-primary"
 @change="toggleGlobalTab(item.id, ($event.target as HTMLInputElement).checked)"
 />
 <div class="text-sm text-admin-theme-text-secondary">
 <div class="font-medium">{{ item.title }}</div>
 </div>
 </label>
 <p v-if="globalTabItems.length === 0" class="p-2 text-sm text-admin-theme-text-muted">
 {{ $t('No global tabs found. Please add them in Settings Hub > Global Tabs.') }}
 </p>
 </div>
 </div>
 </div>

 <div v-if="usesCustomTabs" class="space-y-3">
 <div class="flex items-center justify-between">
 <label class="block text-sm font-medium text-admin-theme-text-secondary">
 {{ $t('Product-Specific Custom Tabs') }}
 </label>
 <button
 type="button"
 class="px-3 py-1.5 rounded-md bg-admin-theme-primary text-admin-theme-primary-content text-sm font-medium hover:bg-admin-theme-primary-hover"
 @click="addCustomTab"
 >
 + {{ $t('Add Custom Tab') }}
 </button>
 </div>

 <div
 v-for="(item, index) in tabConfig.custom_items"
 :key="item.id"
 class="rounded-lg border border-admin-theme-border/90 p-3 space-y-3"
 >
 <div class="flex items-center gap-2">
 <input
 v-model="item.title"
 type="text"
 class="flex-1 px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 :placeholder="$t('Enter tab title')"
 />
 <button
 type="button"
 class="px-2 py-1 text-xs rounded border border-admin-theme-border hover:border-admin-theme-primary"
 :disabled="index === 0"
 @click="moveCustomTab(index, -1)"
 >
 ↑
 </button>
 <button
 type="button"
 class="px-2 py-1 text-xs rounded border border-admin-theme-border hover:border-admin-theme-primary"
 :disabled="index === tabConfig.custom_items.length - 1"
 @click="moveCustomTab(index, 1)"
 >
 ↓
 </button>
 <button
 type="button"
 class="px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700"
 @click="removeCustomTab(index)"
 >
 {{ $t('Delete') }}
 </button>
 </div>
 <TiptapEditor
 v-model="item.content"
 :placeholder="$t('Enter tab content')"
 />
 </div>
 </div>

 </div>
 </div>

  <div v-if="resolvedTabOptions.length > 0" class="pt-4 mt-2 border-t border-admin-theme-border space-y-3">
  <label class="block text-sm font-medium text-admin-theme-text-secondary">
  {{ $t('Default active tab on frontend') }}
  </label>
  <div class="space-y-1" @dragover.prevent.stop @drop.prevent.stop>
  <div
   v-for="(option, index) in resolvedTabOptions"
   :key="option.id"
   draggable="true"
   @dragstart.stop="onDragStart($event, index)"
   @dragend.stop="onDragEnd"
   @drop.prevent.stop="onDrop($event, index)"
   @dragover.prevent.stop
   @dragenter.prevent.stop="onDragEnter(index)"
   class="flex items-center justify-between gap-3 rounded-md border px-3 py-2 cursor-move transition-all duration-150"
   :class="[
    dragOverIndex === index ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-admin-theme-border/50 bg-admin-theme-base hover:bg-admin-theme-base/80'
   ]"
  >
   <div class="flex items-center gap-3 min-w-0 flex-1">
    <div class="text-admin-theme-text-muted/50 shrink-0">
     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
    </div>
    <label class="flex items-center gap-2 cursor-pointer min-w-0 flex-1" @click.stop>
     <input
      :checked="tabConfig.default_tab_id === option.id"
      type="radio"
      name="product-default-tab"
      class="h-4 w-4 border-gray-300 text-admin-theme-primary focus:ring-admin-theme-primary"
      @change="setDefaultTabId(option.id)"
     />
     <span class="text-sm text-admin-theme-text truncate">{{ option.title }}</span>
     <span
      class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide uppercase"
      :class="option.type ==='core'
       ?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
       : option.type ==='global'
       ?'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300'
       :'bg-admin-theme-primary/15 text-admin-theme-primary'"
     >
      {{ option.type ==='core' ? $t('Core') : (option.type ==='global' ? $t('Global') : $t('Custom')) }}
     </span>
    </label>
   </div>
   <router-link
    v-if="option.type ==='global'"
    to="/admin/settings/global_tabs"
    class="text-xs font-medium text-sky-600 hover:text-sky-500 dark:text-sky-400 dark:hover:text-sky-300 shrink-0"
    @click.stop
   >
    {{ $t('Manage') }}
   </router-link>
  </div>
  </div>
  <p class="text-xs text-admin-theme-text-muted">
  {{ $t('Only one tab can be active by default. Global tab content is managed in Settings Hub > Global Tabs.') }}
  </p>
  </div>
  </div>

  <!-- CommerceOffers Suite (Dynamic Pricing, Tiers, Bundles & Order Thresholds) -->
  <div v-if="isCommerceOffersEnabled" class="rounded-xl border border-admin-theme-border/70 bg-admin-theme-card p-4 space-y-4 shadow-sm">
    <div class="flex items-center justify-between border-b border-admin-theme-border pb-3">
      <div>
        <h3 class="text-sm font-semibold text-admin-theme-text flex items-center gap-2">
          <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          {{ $t('CommerceOffers Suite (Dynamic Pricing, N-Tiers & Bundles)') }}
        </h3>
        <p class="text-xs text-admin-theme-text-muted">
          {{ $t('Configure escalation pricing tiers by sales count, volume discounts, and add-on product threshold deals.') }}
        </p>
      </div>
      <button
        type="button"
        class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors flex items-center gap-1"
        :disabled="isSavingOffers"
        @click="saveCommerceOffers"
      >
        <span v-if="isSavingOffers" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
        {{ isSavingOffers ? $t('Saving...') : $t('Save Offers Rules') }}
      </button>
    </div>

    <!-- Tiered Pricing Rules (Escalation by sales_count) -->
    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary">
          1. {{ $t('Tiered Escalation Pricing (By Sales Count)') }}
        </label>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="px-2 py-0.5 text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 rounded font-medium hover:bg-emerald-200 transition-colors"
            @click="applyTieredPreset"
          >
            ⚡ {{ $t('Apply 3-Tier Escalation Preset') }}
          </button>
          <button
            type="button"
            class="text-xs text-emerald-600 hover:text-emerald-500 font-medium"
            @click="addTieredRule"
          >
            + {{ $t('Add Sales Tier') }}
          </button>
        </div>
      </div>

      <div v-if="tieredRules.length === 0" class="text-xs text-admin-theme-text-muted italic">
        {{ $t('No sales threshold tiers configured yet. Baseline product price will be used.') }}
      </div>

      <div v-for="(rule, idx) in tieredRules" :key="idx" class="flex items-center gap-2 bg-admin-theme-base p-2 rounded-lg border border-admin-theme-border/60">
        <div class="flex items-center gap-1">
          <input v-model.number="rule.min_sales" type="number" min="0" class="w-16 px-2 py-1 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text" :placeholder="$t('Min Sales')" @input="onTieredRangeChange(rule)" />
          <span class="text-xs text-admin-theme-text-muted">-</span>
          <input v-model.number="rule.max_sales" type="number" min="0" class="w-16 px-2 py-1 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text" :placeholder="$t('Max')" @input="onTieredRangeChange(rule)" />
        </div>
        <div class="relative flex items-center">
          <input
            v-model.number="rule.percent"
            type="number"
            min="0"
            max="100"
            step="1"
            class="w-16 px-2 py-1 pr-4 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text font-bold text-right"
            :placeholder="$t('% Off')"
            @input="onTieredPercentChange(rule)"
          />
          <span class="absolute right-1.5 text-xs font-bold text-admin-theme-text-muted pointer-events-none">%</span>
        </div>
        <div class="relative flex items-center">
          <span class="absolute left-1.5 text-xs font-bold text-admin-theme-text-muted pointer-events-none">$</span>
          <input
            v-model.number="rule.price"
            type="number"
            step="0.01"
            min="0"
            class="w-24 pl-4 pr-2 py-1 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text font-bold"
            :placeholder="$t('Price ($)')"
            @input="onTieredPriceChange(rule)"
          />
        </div>
        <input v-model="rule.label" type="text" class="flex-1 px-2 py-1 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text" :placeholder="$t('Label (e.g. Early Bird)')" />
        <button type="button" class="text-xs text-red-500 hover:text-red-700 px-2 py-1" @click="tieredRules.splice(idx, 1)">✕</button>
      </div>
    </div>

    <!-- Volume Discounts Rules (Bulk Qty) -->
    <div class="space-y-3 pt-3 border-t border-admin-theme-border/50">
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary">
          2. {{ $t('Volume Bulk Quantity Discounts') }}
        </label>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="px-2 py-0.5 text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 rounded font-medium hover:bg-indigo-200 transition-colors"
            @click="applyVolumePreset"
          >
            ⚡ {{ $t('Apply Volume Discount Preset') }}
          </button>
          <button
            type="button"
            class="text-xs text-emerald-600 hover:text-emerald-500 font-medium"
            @click="addVolumeRule"
          >
            + {{ $t('Add Volume Tier') }}
          </button>
        </div>
      </div>

      <div v-if="volumeRules.length === 0" class="text-xs text-admin-theme-text-muted italic">
        {{ $t('No quantity discount tiers configured.') }}
      </div>

      <div v-for="(rule, idx) in volumeRules" :key="idx" class="flex items-center gap-2 bg-admin-theme-base p-2 rounded-lg border border-admin-theme-border/60">
        <div class="flex items-center gap-1">
          <input v-model.number="rule.min_qty" type="number" min="1" class="w-14 px-2 py-1 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text" :placeholder="$t('Min Qty')" @input="onVolumeRangeChange(rule)" />
          <span class="text-xs text-admin-theme-text-muted">-</span>
          <input v-model.number="rule.max_qty" type="number" min="1" class="w-14 px-2 py-1 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text" :placeholder="$t('Max Qty')" @input="onVolumeRangeChange(rule)" />
        </div>
        <select v-model="rule.discount_type" class="px-2 py-1 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text" @change="onVolumeValueChange(rule)">
          <option value="percentage">% Discount</option>
          <option value="fixed_amount">$ Fixed Off</option>
          <option value="fixed_price">Set Fixed Price</option>
        </select>
        <div class="relative flex items-center">
          <input
            v-model.number="rule.percent"
            type="number"
            min="0"
            max="100"
            step="1"
            class="w-16 px-2 py-1 pr-4 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text font-bold text-right"
            :placeholder="$t('% Off')"
            @input="onVolumePercentChange(rule)"
          />
          <span class="absolute right-1 text-xs font-bold text-admin-theme-text-muted pointer-events-none">%</span>
        </div>
        <input v-model.number="rule.discount_value" type="number" step="0.01" min="0" class="w-20 px-2 py-1 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text font-bold" :placeholder="$t('Value')" @input="onVolumeValueChange(rule)" />
        <input v-model="rule.label" type="text" class="flex-1 px-2 py-1 text-xs border rounded bg-admin-theme-input-bg text-admin-theme-text" :placeholder="$t('Label')" />
        <button type="button" class="text-xs text-red-500 hover:text-red-700 px-2 py-1" @click="volumeRules.splice(idx, 1)">✕</button>
      </div>
    </div>
  </div>
 </template>

<script setup lang="ts">
import { computed, inject, isRef, ref, nextTick, getCurrentInstance, onMounted, watch } from'vue';
import axios from'axios';
import { showSuccess, showError } from '@/admin/utils/dialog';

const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || ((s: string) => s);
import TiptapEditor from'../../../TiptapEditor.ts';
import { EditorContextKey } from'../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
 throw new Error('ProductPrimaryPanel must be used within editor context');
}

const form = context.form;
const helpers = context.helpers ?? {};

const activeLanguages = ref<any[]>([]);
const isDefaultLanguage = computed(() => {
  if (activeLanguages.value.length === 0) {
    return !form.value?.locale || form.value?.locale === 'en';
  }
  const match = activeLanguages.value.find(l => l.code === (form.value?.locale || 'en'));
  return match ? match.is_default : true;
});

onMounted(async () => {
  try {
    const res = await axios.get('/api/v1/languages');
    if (res.data?.success) {
      activeLanguages.value = res.data.data.filter((l: any) => l.is_active);
    }
  } catch (err) {
    console.error('Failed to load languages:', err);
  }
});

const isSyncingEnvato = ref(false);

const syncEnvatoSales = async () => {
  const itemId = (form.value?.settings?.envato_item_id || '').toString().trim();
  if (!itemId) {
    showError($t('Please enter an Envato Item ID first.'));
    return;
  }

  isSyncingEnvato.value = true;
  try {
    const res = await axios.post('/api/v1/market/sync-product-sales', {
      product_id: form.value?.id || null,
      platform: 'envato',
      item_id: itemId,
    });

    if (res.data && res.data.success) {
      const stats = res.data.data || {};
      if (stats.external_sales !== undefined && form.value?.settings) {
        form.value.settings.external_sales = stats.external_sales;
      }
      if (stats.external_rating !== undefined && form.value?.settings) {
        form.value.settings.external_rating = stats.external_rating;
      }
      if (stats.external_rating_count !== undefined && form.value?.settings) {
        form.value.settings.external_rating_count = stats.external_rating_count;
      }
      showSuccess(res.data.message || $t('Sales data synced successfully from Envato!'));
    } else {
      showError(res.data?.message || $t('Failed to sync sales data.'));
    }
  } catch (err: any) {
    const msg = err.response?.data?.message || err.message || $t('Error syncing sales data.');
    showError(msg);
  } finally {
    isSyncingEnvato.value = false;
  }
};

if (!form.value) {
 form.value = {
 name:'',
 slug:'',
 sku:'',
 type: context.type ??'product',
 status:'draft',
 price: 0,
 sale_price: null,
 stock_quantity: 0,
 stock_status:'in_stock',
 manage_stock: false,
 featured: false,
 meta_title:'',
 meta_description:'',
 meta_keywords:'',
 created_at: null,
 updated_at: null,
 } as any;
}

if (!form.value.settings || typeof form.value.settings !=='object' || Array.isArray(form.value.settings)) {
 form.value.settings = {};
}
if (typeof form.value.settings.demo_url !=='string') {
  form.value.settings.demo_url ='';
}
if (typeof form.value.settings.envato_item_id !=='string') {
  form.value.settings.envato_item_id ='';
}
if (typeof form.value.settings.external_sales !== 'number') {
  form.value.settings.external_sales = Number(form.value.settings.external_sales || 0);
}
if (typeof form.value.settings.external_rating !== 'number') {
  form.value.settings.external_rating = Number(form.value.settings.external_rating || 0);
}
if (typeof form.value.settings.external_rating_count !== 'number') {
  form.value.settings.external_rating_count = Number(form.value.settings.external_rating_count || 0);
}
if (typeof form.value.settings.preview_direct !== 'boolean') {
  form.value.settings.preview_direct = true;
}
if (typeof form.value.settings.preview_url !=='string') {
  form.value.settings.preview_url ='';
}
if (!Array.isArray(form.value.settings.preview_videos)) {
  form.value.settings.preview_videos = [];
}

const addVideo = () => {
  if (!Array.isArray(form.value.settings.preview_videos)) {
    form.value.settings.preview_videos = [];
  }
  form.value.settings.preview_videos.push({
    title: '',
    link: '',
    primary: form.value.settings.preview_videos.length === 0 ? '1' : '0'
  });
};

const removeVideo = (index: number) => {
  if (Array.isArray(form.value.settings.preview_videos)) {
    form.value.settings.preview_videos.splice(index, 1);
    let hasPrimary = form.value.settings.preview_videos.some((v: any) => v.primary === '1' || v.primary === 1);
    if (!hasPrimary && form.value.settings.preview_videos.length > 0) {
      form.value.settings.preview_videos[0].primary = '1';
    }
  }
};

const setPrimaryVideo = (index: number, checked: boolean) => {
  if (Array.isArray(form.value.settings.preview_videos)) {
    form.value.settings.preview_videos.forEach((v: any, idx: number) => {
      v.primary = (idx === index && checked) ? '1' : '0';
    });
  }
};

const rawDescription = context.state?.descriptionHtml;
const rawBlocks = context.state?.descriptionBlocks;

const baseRef = isRef(rawDescription) ? rawDescription : ref(rawDescription ??'');
const blocksRef = isRef(rawBlocks) ? rawBlocks : ref(rawBlocks ?? null);

if (!isRef(rawDescription) && context.state) {
 (context.state as Record<string, unknown>).descriptionHtml = baseRef;
}
if (!isRef(rawBlocks) && context.state) {
 (context.state as Record<string, unknown>).descriptionBlocks = blocksRef;
}

const descriptionHtml = computed<string>({
 get: () => (baseRef.value ??'') as string,
 set: (value) => {
 baseRef.value = value ??'';
 },
});

const descriptionBlocks = computed<any>({
 get: () => blocksRef.value,
 set: (value) => {
 blocksRef.value = value;
 },
});

interface FaqItem {
 id: string;
 question: string;
 answer: string;
 open?: boolean;
}

interface TabItem {
 id: string;
 title: string;
 content: string;
 active_default?: boolean;
}

interface ResolvedTabOption {
 id: string;
 title: string;
 type:'core' |'global' |'custom';
}

type SourceMode ='none' |'global' |'custom' |'global_custom';
type GlobalMode ='all' |'selected';

const ensureFaqConfig = () => {
 if (!form.value.settings || typeof form.value.settings !=='object' || Array.isArray(form.value.settings)) {
 form.value.settings = {};
 }
 if (!form.value.settings.faq || typeof form.value.settings.faq !=='object' || Array.isArray(form.value.settings.faq)) {
 form.value.settings.faq = {};
 }

 const current = form.value.settings.faq;
 form.value.settings.faq = {
 enabled: Boolean(current.enabled ?? false),
 source: String(current.source ||'none'),
 global_mode: String(current.global_mode ||'all'),
 global_ids: Array.isArray(current.global_ids) ? current.global_ids.map((id: any) => String(id)) : [],
 custom_items: Array.isArray(current.custom_items)
 ? current.custom_items.map((item: any, index: number) => ({
 id: String(item?.id || `custom-faq-${Date.now()}-${index}`),
 question: String(item?.question ||''),
 answer: String(item?.answer ||''),
 open: Boolean(item?.open),
 }))
 : [],
 };
};

ensureFaqConfig();

const ensureTabConfig = () => {
 if (!form.value.settings || typeof form.value.settings !=='object' || Array.isArray(form.value.settings)) {
 form.value.settings = {};
 }
 if (!form.value.settings.tabs || typeof form.value.settings.tabs !=='object' || Array.isArray(form.value.settings.tabs)) {
 form.value.settings.tabs = {};
 }

 const current = form.value.settings.tabs;
 const fallbackDefaultFromCustom = Array.isArray(current.custom_items)
 ? current.custom_items.find((item: any) => Boolean(item?.active_default))?.id
 : null;
 form.value.settings.tabs = {
 enabled: Boolean(current.enabled ?? false),
 source: String(current.source ||'none'),
 global_mode: String(current.global_mode ||'all'),
 global_ids: Array.isArray(current.global_ids) ? current.global_ids.map((id: any) => String(id)) : [],
 tab_order: Array.isArray(current.tab_order) ? current.tab_order.map((id: any) => String(id)) : [],
 default_tab_id: current.default_tab_id ? String(current.default_tab_id) : (fallbackDefaultFromCustom ? String(fallbackDefaultFromCustom) : null),
 custom_items: Array.isArray(current.custom_items)
 ? current.custom_items.map((item: any, index: number) => ({
 id: String(item?.id || `custom-tab-${Date.now()}-${index}`),
 title: String(item?.title ||''),
 content: String(item?.content ||''),
 active_default: false,
 }))
 : [],
 };
};

ensureTabConfig();

const faqConfig = computed({
 get: () => form.value.settings.faq,
 set: (value) => {
 form.value.settings.faq = value;
 },
});

const usesGlobalFaqs = computed(() => ['global','global_custom'].includes(String(faqConfig.value.source ||'none')));
const usesCustomFaqs = computed(() => ['custom','global_custom'].includes(String(faqConfig.value.source ||'none')));

const faqSourceOptions = computed(() => ([
 { value:'none' as SourceMode, label: $t?.('No FAQs') ||'No FAQs' },
 { value:'global' as SourceMode, label: $t?.('Global FAQs') ||'Global FAQs' },
 { value:'custom' as SourceMode, label: $t?.('Custom FAQs') ||'Custom FAQs' },
 { value:'global_custom' as SourceMode, label: $t?.('Global + Custom FAQs') ||'Global + Custom FAQs' },
]));

const globalModeOptionsFaq = computed(() => ([
 { value:'all' as GlobalMode, label: $t?.('Use all global FAQs') ||'Use all global FAQs' },
 { value:'selected' as GlobalMode, label: $t?.('Select specific global FAQs') ||'Select specific global FAQs' },
]));

const setFaqSource = (value: SourceMode) => {
 faqConfig.value.source = value;
 if (value ==='none') {
 faqConfig.value.global_ids = [];
 faqConfig.value.custom_items = [];
 }
};

const globalFaqItems = ref<FaqItem[]>([]);
const globalTabItems = ref<TabItem[]>([]);

const toggleGlobalFaq = (id: string, checked: boolean) => {
 const ids = Array.isArray(faqConfig.value.global_ids) ? faqConfig.value.global_ids : [];
 if (checked) {
 if (!ids.includes(id)) ids.push(id);
 } else {
 const index = ids.indexOf(id);
 if (index >= 0) ids.splice(index, 1);
 }
 faqConfig.value.global_ids = [...ids];
};

const selectAllGlobalFaqs = (selected: boolean) => {
 faqConfig.value.global_ids = selected ? globalFaqItems.value.map((item) => item.id) : [];
};

const addCustomFaq = () => {
 const items = Array.isArray(faqConfig.value.custom_items) ? faqConfig.value.custom_items : [];
 items.push({
 id: `custom-faq-${Date.now()}-${items.length + 1}`,
 question:'',
 answer:'',
 open: false,
 });
 faqConfig.value.custom_items = [...items];
};

const removeCustomFaq = (index: number) => {
 const items = Array.isArray(faqConfig.value.custom_items) ? [...faqConfig.value.custom_items] : [];
 items.splice(index, 1);
 faqConfig.value.custom_items = items;
};

const moveCustomFaq = (index: number, direction: -1 | 1) => {
 const items = Array.isArray(faqConfig.value.custom_items) ? [...faqConfig.value.custom_items] : [];
 const targetIndex = index + direction;
 if (targetIndex < 0 || targetIndex >= items.length) return;
 const current = items[index];
 items[index] = items[targetIndex];
 items[targetIndex] = current;
 faqConfig.value.custom_items = items;
};

const loadGlobalFaqItems = async () => {
 try {
 const response = await axios.get('/api/v1/settings/group/global_faqs');
 const items = response?.data?.data?.global_faqs_items?.value;
 globalFaqItems.value = Array.isArray(items)
 ? items.map((item: any, index: number) => ({
 id: String(item?.id || `global-faq-${index}`),
 question: String(item?.question ||''),
 answer: String(item?.answer ||''),
 open: Boolean(item?.open),
 }))
 : [];
 } catch (error) {
 console.error('Failed to load global FAQ settings:', error);
 globalFaqItems.value = [];
 }
};

const tabConfig = computed({
 get: () => form.value.settings.tabs,
 set: (value) => {
 form.value.settings.tabs = value;
 },
});

const usesGlobalTabs = computed(() => ['global','global_custom'].includes(String(tabConfig.value.source ||'none')));
const usesCustomTabs = computed(() => ['custom','global_custom'].includes(String(tabConfig.value.source ||'none')));

const tabSourceOptions = computed(() => ([
 { value:'none' as SourceMode, label: $t?.('No tabs') ||'No tabs' },
 { value:'global' as SourceMode, label: $t?.('Global Tabs') ||'Global Tabs' },
 { value:'custom' as SourceMode, label: $t?.('Product-Specific Custom Tabs') ||'Product-Specific Custom Tabs' },
 { value:'global_custom' as SourceMode, label: $t?.('Global + Custom Tabs') ||'Global + Custom Tabs' },
]));

const globalModeOptionsTabs = computed(() => ([
 { value:'all' as GlobalMode, label: $t?.('Use all global tabs') ||'Use all global tabs' },
 { value:'selected' as GlobalMode, label: $t?.('Select specific global tabs') ||'Select specific global tabs' },
]));

const setTabSource = (value: SourceMode) => {
 tabConfig.value.source = value;
 if (value ==='none') {
 tabConfig.value.global_ids = [];
 tabConfig.value.custom_items = [];
 tabConfig.value.default_tab_id = null;
 }
};

const toggleGlobalTab = (id: string, checked: boolean) => {
 const ids = Array.isArray(tabConfig.value.global_ids) ? tabConfig.value.global_ids : [];
 if (checked) {
 if (!ids.includes(id)) ids.push(id);
 } else {
 const index = ids.indexOf(id);
 if (index >= 0) ids.splice(index, 1);
 }
 tabConfig.value.global_ids = [...ids];
};

const selectAllGlobalTabs = (selected: boolean) => {
 tabConfig.value.global_ids = selected ? globalTabItems.value.map((item) => item.id) : [];
};

const addCustomTab = () => {
 const items = Array.isArray(tabConfig.value.custom_items) ? tabConfig.value.custom_items : [];
 const nextId = `custom-tab-${Date.now()}-${items.length + 1}`;
 items.push({
 id: nextId,
 title:'',
 content:'',
 active_default: false,
 });
 tabConfig.value.custom_items = [...items];
 if (!tabConfig.value.default_tab_id) {
 tabConfig.value.default_tab_id = nextId;
 }
};

const removeCustomTab = (index: number) => {
 const items = Array.isArray(tabConfig.value.custom_items) ? [...tabConfig.value.custom_items] : [];
 const removedId = items[index]?.id;
 items.splice(index, 1);
 tabConfig.value.custom_items = items;
 if (removedId && tabConfig.value.default_tab_id === removedId) {
 tabConfig.value.default_tab_id = null;
 }
};

const moveCustomTab = (index: number, direction: -1 | 1) => {
 const items = Array.isArray(tabConfig.value.custom_items) ? [...tabConfig.value.custom_items] : [];
 const targetIndex = index + direction;
 if (targetIndex < 0 || targetIndex >= items.length) return;
 const current = items[index];
 items[index] = items[targetIndex];
 items[targetIndex] = current;
 tabConfig.value.custom_items = items;
};

const loadGlobalTabItems = async () => {
 try {
 const response = await axios.get('/api/v1/settings/group/global_tabs');
 const items = response?.data?.data?.global_tabs_items?.value;
 globalTabItems.value = Array.isArray(items)
 ? items.map((item: any, index: number) => ({
 id: String(item?.id || `global-tab-${index}`),
 title: String(item?.title ||''),
 content: String(item?.content ||''),
 active_default: Boolean(item?.active_default),
 }))
 : [];
 } catch (error) {
 console.error('Failed to load global tab settings:', error);
 globalTabItems.value = [];
 }
};

const selectedGlobalTabItems = computed(() => {
 if (!usesGlobalTabs.value) return [] as TabItem[];
 if (String(tabConfig.value.global_mode ||'all') ==='selected') {
 const selectedIds = new Set((tabConfig.value.global_ids || []).map((id: string) => String(id)));
 return globalTabItems.value.filter((item) => selectedIds.has(String(item.id)));
 }
 return globalTabItems.value;
});

const resolvedTabOptions = computed<ResolvedTabOption[]>(() => {
 const options: ResolvedTabOption[] = [];
 options.push({
 id:'description',
 title: $t?.('Description') ||'Description',
 type:'core',
 });
 if (faqConfig.value.enabled) {
 options.push({
 id:'faqs',
 title: $t?.("FAQ's") ||"FAQ's",
 type:'core',
 });
 }
 if (usesGlobalTabs.value) {
 selectedGlobalTabItems.value.forEach((item) => {
 options.push({
 id: String(item.id),
 title: String(item.title || ($t?.('Untitled tab') ||'Untitled tab')),
 type:'global',
 });
 });
 }
 if (usesCustomTabs.value) {
 (tabConfig.value.custom_items || []).forEach((item: TabItem) => {
 if (!item?.id) return;
 options.push({
 id: String(item.id),
 title: String(item.title || ($t?.('Untitled tab') ||'Untitled tab')),
 type:'custom',
 });
 });
 }
	// Apply saved tab order
	const order = Array.isArray(tabConfig.value.tab_order) ? tabConfig.value.tab_order : [];
	if (order.length > 0) {
	options.sort((a, b) => {
	const indexA = order.indexOf(a.id);
	const indexB = order.indexOf(b.id);
	if (indexA === -1 && indexB === -1) return 0;
	if (indexA === -1) return 1;
	if (indexB === -1) return -1;
	return indexA - indexB;
	});
	}
	return options;
});

const draggedTabIndex = ref<number | null>(null);
const dragOverIndex = ref<number | null>(null);

const onDragStart = (event: DragEvent, index: number) => {
	draggedTabIndex.value = index;
	if (event.dataTransfer) {
	event.dataTransfer.effectAllowed = 'move';
	}
};

const onDragEnd = () => {
	draggedTabIndex.value = null;
	dragOverIndex.value = null;
};

const onDragEnter = (index: number) => {
	dragOverIndex.value = index;
};

const onDrop = (event: DragEvent, targetIndex: number) => {
	event.preventDefault();
	if (draggedTabIndex.value === null || draggedTabIndex.value === targetIndex) {
	draggedTabIndex.value = null;
	dragOverIndex.value = null;
	return;
	}
	const currentOptions = [...resolvedTabOptions.value];
	const [item] = currentOptions.splice(draggedTabIndex.value, 1);
	currentOptions.splice(targetIndex, 0, item);
	tabConfig.value.tab_order = currentOptions.map(opt => opt.id);
	draggedTabIndex.value = null;
	dragOverIndex.value = null;
};

const setDefaultTabId = (id: string) => {
 tabConfig.value.default_tab_id = id;
};

watch(
 resolvedTabOptions,
 (options) => {
 if (!options.length) {
 tabConfig.value.default_tab_id = null;
 return;
 }
 const exists = options.some((item) => item.id === tabConfig.value.default_tab_id);
 if (!exists) {
 tabConfig.value.default_tab_id = options[0].id;
 }
 },
 { immediate: true, deep: true }
);

onMounted(() => {
 loadGlobalFaqItems();
 loadGlobalTabItems();
});

const permalink = computed(() => helpers.getPermalink?.() ??'');
const slugPrefix = computed(() => {
 const link = permalink.value;
 if (!link) {
 return `${window.location.origin}/`;
 }
 try {
 const url = new URL(link);
 const segments = url.pathname.split('/').filter(Boolean);
 segments.pop();
 const basePath = segments.length ? `/${segments.join('/')}/` :'/';
 return `${url.origin}${basePath}`;
 } catch (error) {
 const currentSlug = form.value.slug ??'';
 if (!currentSlug) {
 return link.endsWith('/') ? link : `${link}/`;
 }
 const base = link.endsWith(`${currentSlug}`)
 ? link.slice(0, -currentSlug.length)
 : link;
 return base.endsWith('/') ? base : `${base}/`;
 }
});

const fullPermalink = computed(() => {
 if (!form.value.slug) {
 return permalink.value || slugPrefix.value;
 }
 const prefix = slugPrefix.value.endsWith('/') ? slugPrefix.value : `${slugPrefix.value}/`;
 return `${prefix.replace(/\/+/g,'/')}${form.value.slug}`;
});

const isEditingSlug = ref(false);
const slugInputRef = ref<HTMLInputElement | null>(null);

const toggleSlugEdit = async () => {
 isEditingSlug.value = !isEditingSlug.value;
 if (isEditingSlug.value) {
 await nextTick();
 slugInputRef.value?.focus();
 slugInputRef.value?.select();
 } else {
 // Save when"Done" is clicked
 helpers.save?.();
 }
};

const copyPermalink = async () => {
 const text = fullPermalink.value;
 if (!text) return;
 if (navigator.clipboard?.writeText) {
 try {
 await navigator.clipboard.writeText(text);
 } catch (error) {
 console.warn('Copy failed', error);
 }
 } else {
 const textarea = document.createElement('textarea');
 textarea.value = text;
 textarea.style.position ='fixed';
 textarea.style.left ='-1000px';
 document.body.appendChild(textarea);
 textarea.select();
 try {
 document.execCommand('copy');
 } catch (error) {
 console.warn('Copy failed', error);
 }
 document.body.removeChild(textarea);
 }
};

const handleSlugInput = (event: Event) => {
 const target = event.target as HTMLInputElement;
 helpers.onSlugInput?.(event);
 form.value.slug = target.value;
};

// CommerceOffers Manager State
const activeModulesList = ref<string[]>([]);
const isCommerceOffersEnabled = computed(() => {
  if (Array.isArray(activeModulesList.value) && activeModulesList.value.length > 0) {
    return activeModulesList.value.includes('Polyx.CommerceOffers');
  }
  if (Array.isArray((window as any).PolyCMS_ActiveModules)) {
    return (window as any).PolyCMS_ActiveModules.includes('Polyx.CommerceOffers');
  }
  return false;
});

const isSavingOffers = ref(false);
const tieredRules = ref<any[]>([]);
const volumeRules = ref<any[]>([]);
const bundleRules = ref<any[]>([]);

const getProductBasePrice = (): number => {
  let price = Number(form.value?.price || 0);
  if (price <= 0 && form.value?.services && Array.isArray(form.value.services) && form.value.services.length > 0) {
    const validPrices = form.value.services
      .map((s: any) => Number(s.price || 0))
      .filter((p: number) => p > 0);
    if (validPrices.length > 0) {
      price = Math.min(...validPrices);
    }
  }
  return price > 0 ? price : 10.00;
};

// CommerceOffers Auto Label & Calculation Handlers
const updateTieredLabel = (rule: any) => {
  const basePrice = getProductBasePrice();
  const price = Number(rule.price || 0);
  const min = rule.min_sales ?? 0;
  const max = rule.max_sales;
  const rangeStr = max ? `${min}-${max}` : `${min}+`;

  if (basePrice > 0) {
    rule.percent = Math.max(0, Math.round((1 - price / basePrice) * 100));
  }
  const pct = rule.percent ?? 0;

  if (min === 0 && max && pct > 0) {
    rule.label = $t(`Early Bird ${pct}% Off`);
  } else if (pct > 0) {
    rule.label = $t(`Standard ${pct}% Off`);
  } else if (pct === 0 && price >= basePrice && basePrice > 0) {
    rule.label = $t('Final Full Price');
  } else {
    rule.label = $t(`Buy ${rangeStr}: $${price}`);
  }
};

const onTieredPercentChange = (rule: any) => {
  const basePrice = getProductBasePrice();
  const pct = Math.min(100, Math.max(0, Number(rule.percent || 0)));
  rule.percent = pct;
  if (basePrice > 0) {
    rule.price = Math.round(basePrice * (1 - pct / 100) * 100) / 100;
  }
  updateTieredLabel(rule);
};

const onTieredPriceChange = (rule: any) => {
  const basePrice = getProductBasePrice();
  const price = Number(rule.price || 0);
  if (basePrice > 0) {
    rule.percent = Math.max(0, Math.round((1 - price / basePrice) * 100));
  }
  updateTieredLabel(rule);
};

const onTieredRangeChange = (rule: any) => {
  updateTieredLabel(rule);
};

const updateVolumeLabel = (rule: any) => {
  const basePrice = getProductBasePrice();
  const min = rule.min_qty ?? 1;
  const max = rule.max_qty;
  const rangeStr = max ? `${min}-${max}` : `${min}+`;
  const val = Number(rule.discount_value || 0);

  if (rule.discount_type === 'percentage') {
    rule.percent = val;
    rule.label = $t(`Buy ${rangeStr}: ${val}% Off`);
  } else if (rule.discount_type === 'fixed_amount') {
    rule.percent = basePrice > 0 ? Math.round((val / basePrice) * 100) : 0;
    rule.label = $t(`Buy ${rangeStr}: $${val} Off`);
  } else if (rule.discount_type === 'fixed_price') {
    rule.percent = basePrice > 0 ? Math.round((1 - val / basePrice) * 100) : 0;
    rule.label = $t(`Buy ${rangeStr}: $${val} Fixed`);
  }
};

const onVolumePercentChange = (rule: any) => {
  const basePrice = getProductBasePrice();
  const pct = Math.min(100, Math.max(0, Number(rule.percent || 0)));
  rule.percent = pct;

  if (rule.discount_type === 'percentage') {
    rule.discount_value = pct;
  } else if (rule.discount_type === 'fixed_amount') {
    rule.discount_value = Math.round(basePrice * (pct / 100) * 100) / 100;
  } else if (rule.discount_type === 'fixed_price') {
    rule.discount_value = Math.round(basePrice * (1 - pct / 100) * 100) / 100;
  }
  updateVolumeLabel(rule);
};

const onVolumeValueChange = (rule: any) => {
  const basePrice = getProductBasePrice();
  const val = Number(rule.discount_value || 0);

  if (rule.discount_type === 'percentage') {
    rule.percent = val;
  } else if (rule.discount_type === 'fixed_amount') {
    rule.percent = basePrice > 0 ? Math.round((val / basePrice) * 100) : 0;
  } else if (rule.discount_type === 'fixed_price') {
    rule.percent = basePrice > 0 ? Math.round((1 - val / basePrice) * 100) : 0;
  }
  updateVolumeLabel(rule);
};

const onVolumeRangeChange = (rule: any) => {
  updateVolumeLabel(rule);
};

const applyTieredPreset = () => {
  const basePrice = getProductBasePrice();
  tieredRules.value = [
    { min_sales: 0, max_sales: 10, percent: 40, price: Math.round(basePrice * 0.6 * 100) / 100, label: $t('Early Bird 40% Off') },
    { min_sales: 11, max_sales: 50, percent: 20, price: Math.round(basePrice * 0.8 * 100) / 100, label: $t('Standard 20% Off') },
    { min_sales: 51, max_sales: null, percent: 0, price: basePrice, label: $t('Final Full Price') },
  ];
  showSuccess($t('3-Tier escalation pricing preset loaded successfully!'));
};

const applyVolumePreset = () => {
  volumeRules.value = [
    { min_qty: 2, max_qty: 4, discount_type: 'percentage', discount_value: 10, percent: 10, label: $t('Buy 2-4: 10% Off') },
    { min_qty: 5, max_qty: 9, discount_type: 'percentage', discount_value: 20, percent: 20, label: $t('Buy 5-9: 20% Off') },
    { min_qty: 10, max_qty: null, discount_type: 'percentage', discount_value: 30, percent: 30, label: $t('Buy 10+: 30% Off') },
  ];
  showSuccess($t('Volume bulk discount preset loaded successfully!'));
};

const addTieredRule = () => {
  const basePrice = getProductBasePrice();
  const rule = { min_sales: 0, max_sales: null, percent: 0, price: basePrice, label: '' };
  updateTieredLabel(rule);
  tieredRules.value.push(rule);
};

const addVolumeRule = () => {
  const rule = { min_qty: 2, max_qty: 4, discount_type: 'percentage', discount_value: 10, percent: 10, label: '' };
  updateVolumeLabel(rule);
  volumeRules.value.push(rule);
};

const isOffersLoaded = ref(false);

const isSameRules = (a: any, b: any) => {
  try {
    return JSON.stringify(a || []) === JSON.stringify(b || []);
  } catch (e) {
    return false;
  }
};

watch(
  () => [form.value?.tiered_prices, form.value?.volume_discounts, form.value?.bundle_items, form.value?.price],
  ([newTiered, newVolume, newBundle, newBasePrice]) => {
    const basePrice = Number(newBasePrice || 0);

    if (!isSameRules(newTiered, tieredRules.value)) {
      const parsed = Array.isArray(newTiered) ? JSON.parse(JSON.stringify(newTiered)) : [];
      parsed.forEach((r: any) => {
        if (r.percent === undefined && basePrice > 0 && r.price !== undefined) {
          r.percent = Math.max(0, Math.round((1 - Number(r.price) / basePrice) * 100));
        }
      });
      tieredRules.value = parsed;
    }

    if (!isSameRules(newVolume, volumeRules.value)) {
      const parsed = Array.isArray(newVolume) ? JSON.parse(JSON.stringify(newVolume)) : [];
      parsed.forEach((r: any) => {
        if (r.percent === undefined) {
          const val = Number(r.discount_value || 0);
          if (r.discount_type === 'percentage') r.percent = val;
          else if (r.discount_type === 'fixed_amount') r.percent = basePrice > 0 ? Math.round((val / basePrice) * 100) : 0;
          else if (r.discount_type === 'fixed_price') r.percent = basePrice > 0 ? Math.round((1 - val / basePrice) * 100) : 0;
        }
      });
      volumeRules.value = parsed;
    }

    if (!isSameRules(newBundle, bundleRules.value)) {
      bundleRules.value = Array.isArray(newBundle) ? JSON.parse(JSON.stringify(newBundle)) : [];
    }
    isOffersLoaded.value = true;
  },
  { immediate: true, deep: true }
);

watch(
  [tieredRules, volumeRules, bundleRules],
  ([newTiered, newVolume, newBundle]) => {
    if (form.value && isOffersLoaded.value) {
      let changed = false;
      if (!isSameRules(newTiered, form.value.tiered_prices)) {
        form.value.tiered_prices = newTiered;
        changed = true;
      }
      if (!isSameRules(newVolume, form.value.volume_discounts)) {
        form.value.volume_discounts = newVolume;
        changed = true;
      }
      if (!isSameRules(newBundle, form.value.bundle_items)) {
        form.value.bundle_items = newBundle;
        changed = true;
      }
      if (changed) {
        form.value._sync_commerce_offers = true;
        form.value._force_clear_offers = true;
      }
    }
  },
  { deep: true }
);

const saveCommerceOffers = async () => {
  isSavingOffers.value = true;
  try {
    // 1. Sync current offer rules onto form.value
    if (form.value) {
      form.value.tiered_prices = tieredRules.value;
      form.value.volume_discounts = volumeRules.value;
      form.value.bundle_items = bundleRules.value;
      form.value._sync_commerce_offers = true;
      form.value._force_clear_offers = true;
    }

    // 2. Trigger unified Save All via helpers.save
    if (typeof helpers?.save === 'function') {
      await helpers.save();
    } else {
      const productId = form.value?.id;
      if (productId) {
        await axios.post(`/api/v1/commerce-offers/products/${productId}`, {
          tiered_prices: tieredRules.value,
          volume_discounts: volumeRules.value,
          bundle_items: bundleRules.value,
          _force_clear_offers: true,
        });
      }
    }
  } catch (e: any) {
    showError(e.response?.data?.message || $t('Failed to save offer rules.'));
  } finally {
    isSavingOffers.value = false;
  }
};

onMounted(async () => {
  try {
    const res = await axios.get('/api/v1/modules/active-frontend');
    if (res.data?.data) {
      activeModulesList.value = res.data.data.map((m: any) => m.key);
    }
  } catch (e) {}
});

</script>

<style scoped>
/* Tiptap spacing */
:deep(.ProseMirror) {
 min-height: 200px;
}
</style>

