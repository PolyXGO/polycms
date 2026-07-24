<template>
 <div class="space-y-6">
 <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
 <div>
 <h1 class="text-2xl font-bold text-admin-theme-text">Widgets</h1>
 <p class="text-sm text-admin-theme-text-secondary mt-1">
 Drag widgets from the library into a sidebar or footer. Register new widget areas via code.
 </p>
 </div>
 <div class="flex items-center gap-3">
 <!-- Language Selector Dropdown -->
 <div v-if="systemLanguages.length > 1" class="flex items-center gap-2">
 <span class="text-sm text-admin-theme-text-secondary font-medium">Language:</span>
 <select 
 v-model="selectedLocale" 
 @change="refreshAll" 
 class="bg-admin-theme-surface border border-admin-theme-border text-admin-theme-text text-sm rounded-lg focus:ring-admin-theme-primary focus:border-admin-theme-primary block p-2 transition-colors cursor-pointer"
 >
 <option v-for="lang in systemLanguages" :key="lang.code" :value="lang.code">
 {{ lang.name }} ({{ lang.native_name }})
 </option>
 </select>
 </div>

 <button
 type="button"
 @click="refreshAll"
 class="inline-flex items-center px-4 py-2 rounded-lg border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
 :disabled="widgetTypesLoading || widgetAreasLoading"
 >
 <svg
 class="w-4 h-4 mr-2"
 :class="{'animate-spin': widgetTypesLoading || widgetAreasLoading}"
 viewBox="0 0 24 24"
 fill="none"
 >
 <path
 d="M4 4v5h.582M4 4h5m11 11v5h-.581M20 20h-5"
 stroke="currentColor"
 stroke-width="2"
 stroke-linecap="round"
 stroke-linejoin="round"
 />
 <path
 d="M5.346 14.652A7 7 0 0112 5a6.999 6.999 0 016.652 5.346M18.654 9.348A7 7 0 0112 19a6.999 6.999 0 01-6.652-5.346"
 stroke="currentColor"
 stroke-width="2"
 stroke-linecap="round"
 stroke-linejoin="round"
 />
 </svg>
 Refresh
 </button>
 </div>
 </div>

 <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
 <section class="xl:col-span-3 bg-admin-theme-surface rounded-lg shadow">
 <div class="px-6 py-5 border-b border-admin-theme-border flex items-center justify-between">
 <h2 class="text-lg font-semibold text-admin-theme-text">Available Widgets</h2>
 <span class="text-xs text-admin-theme-text-muted">{{ widgetTypes.length }} types</span>
 </div>
 <div v-if="widgetTypesLoading" class="p-8 flex items-center justify-center">
 <div class="h-8 w-8 border-2 border-admin-theme-primary border-t-transparent rounded-full animate-spin"></div>
 </div>
 <div v-else class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
 <div
 v-for="group in groupedWidgetTypes"
 :key="group.key"
 class="border border-admin-theme-border rounded-lg"
 >
 <div class="px-4 py-2 bg-admin-theme-base/40 border-b border-admin-theme-border text-sm font-semibold text-admin-theme-text-secondary">
 {{ group.label }}
 </div>
 <ul class="divide-y divide-admin-theme-border">
 <li
 v-for="widget in group.widgets"
 :key="widget.type"
 class="px-4 py-3 cursor-move hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
 draggable="true"
 @dragstart="onWidgetTypeDragStart(widget, $event)"
 @dragend="resetDragState"
 >
 <div class="text-sm font-medium text-admin-theme-text">
 {{ widget.label }}
 </div>
 <p class="text-xs text-admin-theme-text-muted mt-1">
 {{ widget.description ||'No description provided.' }}
 </p>
 <span class="inline-block mt-2 text-xs text-admin-theme-primary">
 {{ widget.type }}
 </span>
 </li>
 </ul>
 </div>
 </div>
 </section>

 <section class="xl:col-span-7">
 <div v-if="widgetAreasLoading" class="bg-admin-theme-surface rounded-lg shadow p-6 flex items-center justify-center h-full">
 <div class="h-8 w-8 border-2 border-admin-theme-primary border-t-transparent rounded-full animate-spin"></div>
 </div>
 <div v-else-if="!selectedArea" class="bg-admin-theme-surface rounded-lg shadow p-6 flex items-center justify-center text-sm text-admin-theme-text-muted">
 No widget areas registered. Add areas via code.
 </div>
 <div
 v-else
 class="bg-admin-theme-surface rounded-lg shadow flex flex-col h-full"
 >
 <div class="px-6 py-5 border-b border-admin-theme-border">
 <div class="flex items-start justify-between gap-4">
 <div>
 <h2 class="text-lg font-semibold text-admin-theme-text">
 {{ selectedArea.name }}
 </h2>
 <p v-if="selectedArea.description" class="text-sm text-admin-theme-text-secondary mt-1">
 {{ selectedArea.description }}
 </p>
 <p class="text-xs text-admin-theme-text-muted mt-2">
 Key: <span class="font-mono">{{ selectedArea.key }}</span>
 </p>
 </div>
 <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
 :class="selectedArea.locked ?'bg-admin-theme-base text-admin-theme-text-secondary' :'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200'">
 {{ selectedArea.locked ?'Core Area' :'Custom Area' }}
 </span>
 </div>
 </div>

  <!-- Layout Chooser Panel -->
  <div class="px-6 py-4 bg-admin-theme-base/20 border-b border-admin-theme-border flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-6">
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary">Area Layout:</span>
        <select
          v-model="selectedArea.layout"
          @change="updateAreaLayout(selectedArea)"
          class="text-xs px-2.5 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary transition-all cursor-pointer"
        >
          <option value="1-col">1 Column (100%)</option>
          <option value="2-col">2 Columns (50% / 50%)</option>
          <option value="3-col">3 Columns (33% / 33% / 33%)</option>
          <option value="4-col">4 Columns (25% / 25% / 25% / 25%)</option>
          <option value="5-col">5 Columns (20% / 20% / 20% / 20% / 20%)</option>
          <option value="split-left">2 Columns (66% / 33%)</option>
          <option value="split-right">2 Columns (33% / 66%)</option>
        </select>
      </div>

      <div v-if="!selectedArea.layout || selectedArea.layout === '1-col'" class="flex items-center gap-2">
        <span class="text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary">Alignment:</span>
        <div class="flex items-center border border-admin-theme-border rounded-lg overflow-hidden bg-admin-theme-input-bg p-0.5">
          <button
            type="button"
            @click="selectedArea.alignment = 'left'; updateAreaAlignment(selectedArea)"
            class="p-1.5 rounded transition-all focus:outline-none"
            :class="selectedArea.alignment === 'left' ? 'bg-admin-theme-primary text-white shadow-xs' : 'text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5'"
            title="Align Left"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h16" />
            </svg>
          </button>
          <button
            type="button"
            @click="selectedArea.alignment = 'right'; updateAreaAlignment(selectedArea)"
            class="p-1.5 rounded transition-all focus:outline-none"
            :class="selectedArea.alignment === 'right' ? 'bg-admin-theme-primary text-white shadow-xs' : 'text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5'"
            title="Align Right"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M10 12h10M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <div v-if="selectedArea.layout && selectedArea.layout !== '1-col'" class="flex items-center gap-1.5 bg-admin-theme-primary/10 px-2 py-1 rounded text-xs text-admin-theme-primary">
      <span>Drag widgets into sub-columns below</span>
    </div>
  </div>

  <!-- Multi-column Grid View (layout !== '1-col') -->
  <div
    v-if="selectedArea.layout && selectedArea.layout !== '1-col'"
    class="flex-1 overflow-y-auto p-6"
    :class="dragOverAreaId === selectedArea.id && draggingHasPayload ? 'bg-admin-theme-primary/5' : ''"
    @dragover="dragOverAreaId = selectedArea.id"
  >
     <div class="grid gap-6" :class="gridClassForLayout(selectedArea.layout)">
         <div 
           v-for="colIndex in getColumnCount(selectedArea.layout)" 
           :key="colIndex"
           class="bg-admin-theme-base/10 rounded-xl border-2 border-dashed border-admin-theme-border/70 p-4 min-h-[350px] transition-all hover:border-admin-theme-primary/40"
           :class="[
             getColSpanClass(selectedArea.layout, colIndex - 1),
             dragOverAreaId === selectedArea.id && draggingHasPayload ? 'hover:bg-admin-theme-primary/5' : ''
           ]"
           @dragover.prevent="dragOverAreaId = selectedArea.id"
           @drop="handleColDrop(selectedArea, colIndex - 1, $event)"
         >
             <h4 class="text-xs font-bold uppercase tracking-widest text-admin-theme-text-secondary mb-3 flex items-center justify-between pb-2 border-b border-admin-theme-border/60">
                <span class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-admin-theme-primary"></span>
                  Column {{ colIndex }}
                </span>
                <div class="flex items-center gap-2">
                  <div class="flex items-center border border-admin-theme-border rounded bg-admin-theme-input-bg h-[26px] p-0.5">
                    <button
                      type="button"
                      @click="updateColumnAlignment(selectedArea, colIndex - 1, 'left')"
                      class="h-full px-1.5 rounded transition-all focus:outline-none flex items-center justify-center"
                      :class="(selectedArea.column_alignments ? selectedArea.column_alignments['col_' + (colIndex - 1)] : 'left') === 'left' ? 'bg-admin-theme-primary text-white shadow-xs' : 'text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5'"
                      title="Align Left"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h16" />
                      </svg>
                    </button>
                    <button
                      type="button"
                      @click="updateColumnAlignment(selectedArea, colIndex - 1, 'right')"
                      class="h-full px-1.5 rounded transition-all focus:outline-none flex items-center justify-center"
                      :class="(selectedArea.column_alignments ? selectedArea.column_alignments['col_' + (colIndex - 1)] : 'left') === 'right' ? 'bg-admin-theme-primary text-white shadow-xs' : 'text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5'"
                      title="Align Right"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M10 12h10M4 18h16" />
                      </svg>
                    </button>
                  </div>
                  <span class="text-[10px] bg-admin-theme-border px-2 rounded text-admin-theme-text-muted font-mono font-bold h-[26px] flex items-center justify-center">{{ getColWidgets(selectedArea, colIndex - 1).length }} widgets</span>
                </div>
             </h4>
             
             <div v-if="getColWidgets(selectedArea, colIndex - 1).length === 0" class="text-xs text-admin-theme-text-muted text-center py-16">
                Drop widgets here
             </div>
             
             <ul v-else class="space-y-3">
                 <li 
                   v-for="widget in getColWidgets(selectedArea, colIndex - 1)" 
                   :key="widget.id"
                   class="bg-admin-theme-surface border border-admin-theme-border rounded-lg p-2.5 px-3 shadow-sm cursor-move relative transition-all hover:shadow-md hover:border-admin-theme-primary/30"
                   draggable="true"
                   @dragstart="onWidgetInstanceDragStart(widget, $event)"
                   @dragend="resetDragState"
                   @dragover="handleWidgetDragOver(selectedArea, widget, $event)"
                   @drop="handleWidgetDrop(selectedArea, widget, $event)"
                 >
                     <!-- Widget card inside column -->
                     <div class="flex items-center justify-between gap-2">
                         <div class="min-w-0">
                             <div class="flex items-center gap-1.5 flex-wrap">
                                 <span class="text-xs font-bold text-admin-theme-text truncate max-w-[120px]">{{ widget.title || widget.definition?.label }}</span>
                                 <span class="text-[9px] text-admin-theme-text-muted bg-admin-theme-base/50 px-1 py-0.5 rounded font-mono">{{ widget.widget_type }}</span>
                             </div>
                         </div>
                          <div class="flex items-center gap-1.5 flex-shrink-0">
                              <button 
                                type="button" 
                                class="p-1 text-admin-theme-text-secondary hover:text-admin-theme-primary transition-colors focus:outline-none"
                                @click="toggleWidgetExpanded(widget)"
                                :title="widget.isExpanded ? 'Hide' : 'Edit'"
                              >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                              </button>
                              <button 
                                type="button" 
                                class="p-1 text-red-500 hover:text-red-700 transition-colors focus:outline-none"
                                @click="deleteWidget(selectedArea, widget)"
                                title="Remove"
                              >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                              </button>
                          </div>
                     </div>
                     
                     <!-- Widget Configuration inside Column (if expanded) -->
                     <div v-if="widget.isExpanded" class="mt-4 pt-4 border-t border-admin-theme-border space-y-4 text-left">
                         <!-- Widget Title -->
                         <div>
                             <label class="block text-[11px] font-bold uppercase tracking-wider text-admin-theme-text-secondary mb-1.5">Widget Title</label>
                             <input 
                               v-model="widget.formTitle" 
                               type="text" 
                               class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
                               placeholder="Enter widget title..."
                             />
                         </div>
                         
                         <!-- Widget settings -->
                         <div v-if="widget.definition?.config_schema" class="space-y-4">
                             <div v-for="(field, key) in widget.definition.config_schema" :key="key" class="space-y-1.5">
                                 <label class="block text-[11px] font-bold text-admin-theme-text-secondary">{{ field.label || key }}</label>
                                 <template v-if="field.type === 'textarea'">
                                     <textarea v-model="widget.formConfig[key]" rows="3" class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary" :placeholder="'Enter ' + (field.label || key).toLowerCase() + '...'"></textarea>
                                 </template>
                                 <template v-else-if="field.type === 'richtext' || field.type === 'tiptap'">
                                     <TiptapEditor v-model="widget.formConfig[key]" :placeholder="'Enter ' + (field.label || key).toLowerCase() + '...'" class="w-full border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" />
                                 </template>
                                 <template v-else-if="field.type === 'number'">
                                     <input type="number" v-model.number="widget.formConfig[key]" class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary" :placeholder="'Enter ' + (field.label || key).toLowerCase() + '...'" />
                                 </template>
                                 <template v-else-if="field.type === 'select'">
                                     <select v-model="widget.formConfig[key]" class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary">
                                         <option v-for="option in field.options || []" :key="option.value" :value="option.value">{{ option.label }}</option>
                                     </select>
                                 </template>
                                 <template v-else-if="field.type === 'boolean'">
                                     <label class="inline-flex items-center gap-2 text-xs text-admin-theme-text-secondary cursor-pointer">
                                         <input type="checkbox" v-model="widget.formConfig[key]" class="w-4 h-4 text-admin-theme-primary border-admin-theme-border rounded focus:ring-admin-theme-primary" />
                                         Enable
                                     </label>
                                 </template>
                                 <template v-else-if="field.type === 'tags'">
                                     <input type="text" v-model="widget.formConfig[key]" class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary" placeholder="Comma separated IDs" />
                                 </template>
                                 <template v-else-if="field.type === 'social_links_editor'">
    <div class="space-y-4 border-t border-admin-theme-border/40 pt-4 mt-2">
      <div class="flex justify-between items-center">
        <h4 class="text-xs font-bold uppercase tracking-wider text-admin-theme-text-secondary">
          Social Links Configuration
        </h4>
        <button
          type="button"
          @click="addGlobalSocial"
          class="flex items-center gap-1 px-2 py-1 bg-admin-theme-primary/10 hover:bg-admin-theme-primary/20 text-admin-theme-primary text-[10px] font-bold uppercase tracking-widest rounded-lg transition-colors cursor-pointer"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          Add Social
        </button>
      </div>

      <div v-if="globalSocialLinks.length > 0" class="space-y-3 max-h-[350px] overflow-y-auto pr-1">
        <div 
          v-for="(item, index) in globalSocialLinks" 
          :key="index"
          class="p-3 border border-admin-theme-border rounded-lg bg-admin-theme-base/10 space-y-2 relative"
        >
          <div class="flex items-center justify-between border-b border-admin-theme-border/30 pb-2">
            <span class="text-[10px] font-bold text-admin-theme-text-muted uppercase">Platform #{{ index + 1 }}</span>
            <button
              type="button"
              @click="deleteGlobalSocial(index)"
              class="text-red-500 hover:text-red-700 transition-colors"
              title="Delete social link"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>

          <div class="space-y-2">
            <div>
              <label class="block text-[9px] font-bold uppercase text-admin-theme-text-muted mb-1">Icon</label>
              <FormIconPicker
                v-model="item.icon"
                label="Select Icon"
                @update:modelValue="saveGlobalSocialLinks"
              />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase text-admin-theme-text-muted mb-1">Platform Name</label>
              <input
                type="text"
                v-model="item.name"
                @input="saveGlobalSocialLinks"
                placeholder="Facebook, YouTube..."
                class="w-full px-2 py-1.5 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary font-semibold"
              />
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase text-admin-theme-text-muted mb-1">Profile URL</label>
              <input
                type="url"
                v-model="item.url"
                @input="saveGlobalSocialLinks"
                placeholder="https://..."
                class="w-full px-2 py-1.5 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary font-semibold"
              />
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="text-center py-6 border border-dashed border-admin-theme-border rounded-lg">
        <p class="text-xs text-admin-theme-text-muted font-bold">No social profiles configured</p>
        <button
          type="button"
          @click="addGlobalSocial"
          class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 bg-admin-theme-primary text-admin-theme-primary-content hover:bg-admin-theme-primary-hover text-[10px] font-bold uppercase tracking-widest rounded-lg transition-colors cursor-pointer"
        >
          Add Profile
        </button>
      </div>
    </div>
  </template>
  <template v-else-if="field.type === 'translatable_text'">
                                     <div class="space-y-2">
                                       <div v-for="lang in systemLanguages" :key="lang.code" class="flex items-center gap-2">
                                         <span class="text-[10px] font-semibold text-admin-theme-text-muted w-16">{{ lang.name }}:</span>
                                         <input type="text" :value="widget.formConfig[key] ? (widget.formConfig[key][lang.code] || '') : ''" @input="e => { if (!widget.formConfig[key]) widget.formConfig[key] = {}; widget.formConfig[key][lang.code] = (e.target as HTMLInputElement).value; }" class="flex-1 px-2.5 py-1.5 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary" />
                                       </div>
                                     </div>
                                 </template>
                                 <template v-else-if="field.type === 'menu_items'">
                                     <div class="space-y-3">
                                       <div v-if="!widget.formConfig[key] || widget.formConfig[key].length === 0" class="text-xs text-admin-theme-text-muted py-2">
                                         No menu items. Click "Add Item" to start.
                                       </div>
                                       <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                                         <div 
                                           v-for="(item, index) in (widget.formConfig[key] || [])" 
                                           :key="index"
                                           class="p-2.5 border border-admin-theme-border rounded-lg bg-admin-theme-base/20 space-y-2 relative group/item"
                                           draggable="true"
                                           @dragstart="onMenuItemDragStart(widget.formConfig[key], index, $event)"
                                           @dragover.prevent
                                           @drop="onMenuItemDrop(widget.formConfig[key], index, $event)"
                                         >
                                           <div class="flex items-center justify-between border-b border-admin-theme-border/50 pb-1.5">
                                             <div class="flex items-center gap-1.5 cursor-move text-admin-theme-text-secondary">
                                               <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                 <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
                                               </svg>
                                               <span class="text-[10px] font-semibold">Item #{{ index + 1 }}</span>
                                             </div>
                                             
                                             <button
                                               type="button"
                                               @click="deleteMenuItem(widget.formConfig[key], index)"
                                               class="text-red-500 hover:text-red-700 transition-colors"
                                               title="Delete item"
                                             >
                                               <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                 <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                               </svg>
                                             </button>
                                           </div>

                                           <div class="grid grid-cols-1 gap-2">
                                             <div>
                                               <input
                                                 type="text"
                                                 v-model="item.title"
                                                 class="w-full px-2 py-1 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
                                                 placeholder="Title"
                                               />
                                             </div>

                                             <div>
                                               <input
                                                 type="text"
                                                 v-model="item.link"
                                                 @input="handleLinkChange(item)"
                                                 class="w-full px-2 py-1 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
                                                 placeholder="Link"
                                               />
                                             </div>

                                             <div class="grid grid-cols-2 gap-2">
                                               <div>
                                                 <select
                                                   v-model="item.relSelect"
                                                   @change="handleRelSelectChange(item)"
                                                   class="w-full px-1.5 py-1 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none"
                                                 >
                                                   <option value="">Standard</option>
                                                   <option value="nofollow">No Follow</option>
                                                   <option value="custom">Custom...</option>
                                                 </select>
                                                 <input
                                                   v-if="item.relSelect === 'custom'"
                                                   type="text"
                                                   v-model="item.rel"
                                                   class="w-full mt-1 px-1.5 py-0.5 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
                                                   placeholder="Custom rel"
                                                 />
                                               </div>

                                               <div>
                                                 <select
                                                   v-model="item.target"
                                                   class="w-full px-1.5 py-1 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none"
                                                 >
                                                   <option value="_self">Same tab</option>
                                                   <option value="_blank">New tab</option>
                                                 </select>
                                               </div>
                                             </div>

                                             <div class="space-y-1">
                                               <FormIconPicker
                                                 v-model="item.icon"
                                                 label="Icon"
                                               />
                                             </div>
                                           </div>
                                         </div>
                                       </div>

                                       <button
                                         type="button"
                                         @click="addMenuItem(widget.formConfig, key)"
                                         class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
                                       >
                                         <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                         </svg>
                                         Add Link Item
                                       </button>
                                     </div>
                                 </template>
                                 <template v-else>
                                     <input type="text" v-model="widget.formConfig[key]" class="w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary" :placeholder="'Enter ' + (field.label || key).toLowerCase() + '...'" />
                                 </template>
                                 <p v-if="field.description" class="text-[10px] text-admin-theme-text-muted mt-1">
                                     {{ field.description }}
                                 </p>
                             </div>
                         </div>
                         
                         <div class="flex items-center justify-between pt-3 border-t border-admin-theme-border/50">
                             <label class="inline-flex items-center gap-2 text-xs text-admin-theme-text-secondary cursor-pointer">
                                 <input type="checkbox" v-model="widget.formActive" @change="updateWidgetActive(widget)" class="w-4 h-4 text-admin-theme-primary border-admin-theme-border rounded" />
                                 Active
                             </label>
                             <button 
                               type="button" 
                               class="px-4 py-2 text-xs bg-admin-theme-primary hover:bg-admin-theme-primary/95 text-white font-medium rounded-lg transition-all flex items-center gap-1.5 shadow-sm"
                               @click="saveWidget(widget)"
                             >
                               Save Widget
                             </button>
                         </div>
                     </div>
                 </li>
             </ul>
         </div>
     </div>
  </div>

  <!-- Standard Single list view (layout === '1-col' or empty) -->
  <div
    v-else
    class="flex-1 overflow-y-auto"
    :class="dragOverAreaId === selectedArea.id && draggingHasPayload ?'bg-admin-theme-primary/10/60' :''"
    @dragover="handleAreaDragOver(selectedArea, $event)"
    @drop="handleAreaDrop(selectedArea, $event)"
  >
 <div
 v-if="selectedArea.widgets.length === 0"
 class="px-6 py-8 text-center text-sm text-admin-theme-text-muted"
 >
 Drag widgets here to activate them.
 </div>
 <ul class="divide-y divide-admin-theme-border">
 <li
 v-for="widget in selectedArea.widgets"
 :key="widget.id"
 class="px-6 py-3"
 :class="dragOverWidgetId === widget.id ?'bg-admin-theme-primary/10/70/30' :'bg-transparent'"
 draggable="true"
 @dragstart="onWidgetInstanceDragStart(widget, $event)"
 @dragend="resetDragState"
 @dragover="handleWidgetDragOver(selectedArea, widget, $event)"
 @drop="handleWidgetDrop(selectedArea, widget, $event)"
 >
 <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
 <div>
 <div class="flex items-center gap-2">
 <h3 class="text-sm font-semibold text-admin-theme-text">
 {{ getWidgetLabel(widget) }}
 </h3>
 <span class="text-xs text-admin-theme-text-muted">
 {{ widget.widget_type }}
 </span>
 </div>
 <p v-if="widget.definition?.description" class="text-xs text-admin-theme-text-muted mt-1">
 {{ widget.definition.description }}
 </p>
 </div>
 <div class="flex items-center gap-2">
 <label class="inline-flex items-center gap-2 text-sm text-admin-theme-text-secondary">
 <input
 type="checkbox"
 v-model="widget.formActive"
 @change="updateWidgetActive(widget)"
 class="w-4 h-4 text-admin-theme-primary border-admin-theme-border rounded focus:ring-admin-theme-primary"
 />
 Active
 </label>
  <button
  type="button"
  class="p-1.5 text-admin-theme-text-secondary hover:text-admin-theme-primary transition-colors focus:outline-none"
  @click="toggleWidgetExpanded(widget)"
  :title="widget.isExpanded ?'Collapse' :'Configure'"
  >
    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  </button>
  <button
  type="button"
  class="p-1.5 text-red-500 hover:text-red-700 transition-colors focus:outline-none"
  @click="deleteWidget(selectedArea, widget)"
  title="Remove"
  >
    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
    </svg>
  </button>
 </div>
 </div>

 <div
 v-if="widget.isExpanded"
 class="mt-4 bg-admin-theme-base/20 border border-admin-theme-border rounded-lg p-4 space-y-4"
 >
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 Widget Title
 </label>
 <input
 v-model="widget.formTitle"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 placeholder="Enter widget title..."
 />
 </div>

 <div v-if="widget.definition?.config_schema">
 <h4 class="text-sm font-semibold text-admin-theme-text mb-3">Settings</h4>
 <div class="space-y-4">
 <div
 v-for="(field, key) in widget.definition.config_schema"
 :key="key"
 class="space-y-1"
 >
 <label class="block text-sm font-medium text-admin-theme-text-secondary">
 {{ field.label || key }}
 </label>

 <template v-if="field.type ==='textarea'">
 <textarea
 v-model="widget.formConfig[key]"
 :rows="field.rows || 4"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 :placeholder="'Enter ' + (field.label || key).toLowerCase() + '...'"
 ></textarea>
 </template>
 <template v-else-if="field.type === 'richtext' || field.type === 'tiptap'">
 <TiptapEditor
 v-model="widget.formConfig[key]"
 :placeholder="'Enter ' + (field.label || key).toLowerCase() + '...'"
 class="w-full border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
 />
 </template>
 <template v-else-if="field.type ==='number'">
 <input
 type="number"
 v-model.number="widget.formConfig[key]"
 :min="field.min ?? undefined"
 :max="field.max ?? undefined"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 />
 </template>
 <template v-else-if="field.type ==='select'">
 <select
 v-model="widget.formConfig[key]"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 >
 <option
 v-for="option in field.options || []"
 :key="option.value"
 :value="option.value"
 >
 {{ option.label }}
 </option>
 </select>
 </template>
 <template v-else-if="field.type ==='boolean'">
 <label class="inline-flex items-center gap-2 text-sm text-admin-theme-text-secondary">
 <input
 type="checkbox"
 v-model="widget.formConfig[key]"
 class="w-4 h-4 text-admin-theme-primary border-admin-theme-border rounded focus:ring-admin-theme-primary"
 />
 Enable
 </label>
 </template>
 <template v-else-if="field.type ==='tags'">
 <input
 type="text"
 v-model="widget.formConfig[key]"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 placeholder="Comma separated IDs (e.g. 1,2,3)"
 />
 </template>
 <template v-else-if="field.type === 'translatable_text'">
    <div class="space-y-2">
      <div v-for="lang in systemLanguages" :key="lang.code" class="flex items-center gap-2">
        <span class="text-xs font-semibold text-admin-theme-text-muted w-20">{{ lang.name }}:</span>
        <input
          type="text"
          :value="widget.formConfig[key] ? (widget.formConfig[key][lang.code] || '') : ''"
          @input="e => { 
            if (!widget.formConfig[key]) widget.formConfig[key] = {}; 
            widget.formConfig[key][lang.code] = (e.target as HTMLInputElement).value; 
          }"
          class="flex-1 px-3 py-1.5 text-sm border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
        />
      </div>
    </div>
  </template>
  <template v-else-if="field.type === 'menu_items'">
    <div class="space-y-3">
      <div v-if="!widget.formConfig[key] || widget.formConfig[key].length === 0" class="text-xs text-admin-theme-text-muted py-2">
        No menu items. Click "Add Item" to start.
      </div>
      <div class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
        <div 
          v-for="(item, index) in (widget.formConfig[key] || [])" 
          :key="index"
          class="p-3 border border-admin-theme-border rounded-lg bg-admin-theme-base/20 space-y-2 relative group/item"
          draggable="true"
          @dragstart="onMenuItemDragStart(widget.formConfig[key], index, $event)"
          @dragover.prevent
          @drop="onMenuItemDrop(widget.formConfig[key], index, $event)"
        >
          <div class="flex items-center justify-between border-b border-admin-theme-border/50 pb-2">
            <div class="flex items-center gap-2 cursor-move text-admin-theme-text-secondary">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
              </svg>
              <span class="text-xs font-semibold">Item #{{ index + 1 }}</span>
            </div>
            
            <button
              type="button"
              @click="deleteMenuItem(widget.formConfig[key], index)"
              class="text-red-500 hover:text-red-700 transition-colors"
              title="Delete item"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="md:col-span-2">
              <input
                type="text"
                v-model="item.title"
                class="w-full px-2 py-1.5 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
                placeholder="Title"
              />
            </div>

            <div class="md:col-span-2">
              <input
                type="text"
                v-model="item.link"
                @input="handleLinkChange(item)"
                class="w-full px-2 py-1.5 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
                placeholder="Link"
              />
            </div>

            <div>
              <div class="flex flex-col gap-1">
                <select
                  v-model="item.relSelect"
                  @change="handleRelSelectChange(item)"
                  class="w-full px-2 py-1.5 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none"
                >
                  <option value="">Standard (Follow)</option>
                  <option value="nofollow">No Follow (nofollow)</option>
                  <option value="custom">Custom...</option>
                </select>
                <input
                  v-if="item.relSelect === 'custom'"
                  type="text"
                  v-model="item.rel"
                  class="w-full px-2 py-1 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
                  placeholder="Custom rel value"
                />
              </div>
            </div>

            <div>
              <select
                v-model="item.target"
                class="w-full px-2 py-1.5 text-xs border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text focus:outline-none"
              >
                <option value="_self">Same tab (_self)</option>
                <option value="_blank">New tab (_blank)</option>
              </select>
            </div>

            <div class="space-y-1 md:col-span-2">
              <FormIconPicker
                v-model="item.icon"
                label="Icon"
              />
            </div>
          </div>
        </div>
      </div>

      <button
        type="button"
        @click="addMenuItem(widget.formConfig, key)"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
      >
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Add Link Item
      </button>
    </div>
  </template>
  <template v-else>
 <input
 type="text"
 v-model="widget.formConfig[key]"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 :placeholder="'Enter ' + (field.label || key).toLowerCase() + '...'"
 />
 </template>
 <p v-if="field.description" class="text-xs text-admin-theme-text-muted">
 {{ field.description }}
 </p>
 </div>
 </div>
 </div>

 <div class="flex items-center justify-end gap-2 pt-2">
 <button
 type="button"
 class="px-4 py-2 text-sm border border-admin-theme-border rounded-lg text-admin-theme-text-secondary hover:bg-admin-theme-base"
 @click="revertWidgetChanges(widget)"
 >
 Reset
 </button>
 <button
 type="button"
 class="px-4 py-2 text-sm rounded-lg bg-admin-theme-primary hover:bg-admin-theme-primary-hover text-admin-theme-primary-content disabled:opacity-60"
 :disabled="widget.isSaving"
 @click="saveWidget(widget)"
 >
 <svg
 v-if="widget.isSaving"
 class="w-4 h-4 mr-2 inline-block align-middle animate-spin"
 viewBox="0 0 24 24"
 fill="none"
 >
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a12 12 0 00-12 12h4z"></path>
 </svg>
 Save Widget
 </button>
 </div>
 </div>
 </li>
 </ul>
 </div>
 </div>
 </section>

 <section class="xl:col-span-2 bg-admin-theme-surface rounded-lg shadow h-full">
 <div class="px-6 py-5 border-b border-admin-theme-border">
 <h2 class="text-lg font-semibold text-admin-theme-text">Widget Areas</h2>
 <p class="text-xs text-admin-theme-text-muted mt-1">Click to manage widgets or drop to move items.</p>
 </div>
 <div class="max-h-[70vh] overflow-y-auto divide-y divide-admin-theme-border">
 <button
 v-for="area in widgetAreas"
 :key="area.id"
 type="button"
 class="w-full text-left px-6 py-4 transition-colors"
 :class="[
 area.id === selectedAreaId ?'bg-admin-theme-primary/10/30' :'hover:bg-black/5 dark:hover:bg-white/5',
 dragOverAreaId === area.id && draggingHasPayload ?'ring-2 ring-indigo-400 dark:ring-indigo-500' :''
 ]"
 @click="selectArea(area.id)"
 @dragover="handleAreaDragOver(area, $event)"
 @drop="handleAreaDrop(area, $event)"
 >
 <div class="flex items-center justify-between gap-3">
 <div>
 <p class="text-sm font-semibold text-admin-theme-text">
 {{ area.name }}
 </p>
 <p class="text-xs text-admin-theme-text-muted">
 {{ area.widgets.length }} widget{{ area.widgets.length === 1 ?'' :'s' }}
 </p>
 </div>
 <span class="text-xs font-medium text-admin-theme-text-muted">
 {{ area.key }}
 </span>
 </div>
 </button>
 </div>
 </section>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import FormIconPicker from '../../components/forms/FormIconPicker.vue';
import TiptapEditor from '../../components/TiptapEditor';

interface WidgetFieldOption {
 value: string | number;
 label: string;
}

interface WidgetField {
 type: string;
 label: string;
 description?: string;
 rows?: number;
 options?: WidgetFieldOption[];
 default?: unknown;
 min?: number;
 max?: number;
}

interface WidgetDefinition {
 type: string;
 label: string;
 description?: string;
 icon?: string | null;
 category: string;
 config_schema: Record<string, WidgetField>;
 default_config: Record<string, unknown>;
}

interface ApiWidgetInstance {
 id: number;
 widget_type: string;
 title: string | null;
 config: Record<string, unknown>;
 order: number;
 active: boolean;
 definition: WidgetDefinition | null;
}

interface WidgetInstance extends ApiWidgetInstance {
 definition: WidgetDefinition | null;
 isExpanded: boolean;
 isSaving: boolean;
 formTitle: string;
 formConfig: Record<string, any>;
 formActive: boolean;
}

interface ApiWidgetArea {
  id: number;
  name: string;
  key: string;
  description: string | null;
  order: number;
  locked: boolean;
  layout?: string;
  alignment?: string;
  column_alignments?: Record<string, string>;
  widgets: ApiWidgetInstance[];
}

interface WidgetArea extends ApiWidgetArea {
 widgets: WidgetInstance[];
}

interface WidgetCategoryGroup {
 key: string;
 label: string;
 widgets: WidgetDefinition[];
}

const dialog = useDialog();

const widgetTypesLoading = ref(false);
const widgetAreasLoading = ref(false);

const globalSocialLinks = ref<Array<{ name: string; icon: string; url: string }>>([]);
const hasLoadedSocials = ref(false);

const loadGlobalSocialLinks = async () => {
  try {
    const response = await axios.get('/api/v1/settings/socials');
    if (response.data?.success) {
      const socialLinksSetting = response.data.data.social_links?.value;
      if (socialLinksSetting) {
        globalSocialLinks.value = typeof socialLinksSetting === 'string' ? JSON.parse(socialLinksSetting) : socialLinksSetting;
      }
      hasLoadedSocials.value = true;
    }
  } catch (e) {
    console.error('Failed to load socials:', e);
  }
};

const saveGlobalSocialLinks = async () => {
  try {
    const payload = {
      settings: {
        social_links: JSON.stringify(globalSocialLinks.value),
        social_facebook: globalSocialLinks.value.find(item => item.name.toLowerCase() === 'facebook')?.url || '',
        social_youtube: globalSocialLinks.value.find(item => item.name.toLowerCase() === 'youtube')?.url || '',
        social_github: globalSocialLinks.value.find(item => item.name.toLowerCase() === 'github')?.url || '',
        social_envato: globalSocialLinks.value.find(item => item.name.toLowerCase() === 'envato')?.url || '',
        social_twitter: globalSocialLinks.value.find(item => item.name.toLowerCase() === 'twitter')?.url || '',
        social_instagram: globalSocialLinks.value.find(item => item.name.toLowerCase() === 'instagram')?.url || '',
        social_linkedin: globalSocialLinks.value.find(item => item.name.toLowerCase() === 'linkedin')?.url || '',
      }
    };
    await axios.put('/api/v1/settings/socials', payload);
  } catch (e) {
    console.error('Failed to save global social links:', e);
  }
};

const addGlobalSocial = () => {
  globalSocialLinks.value.push({
    name: 'New Social',
    icon: 'ki-share',
    url: ''
  });
  saveGlobalSocialLinks();
};

const deleteGlobalSocial = (index: number) => {
  globalSocialLinks.value.splice(index, 1);
  saveGlobalSocialLinks();
};

const widgetTypes = ref<WidgetDefinition[]>([]);
const widgetCategoryLabels = ref<Record<string, string>>({});
const widgetAreas = ref<WidgetArea[]>([]);
const selectedAreaId = ref<number | null>(null);
const selectedLocale = ref('en');

const draggingState = reactive<{
 type: WidgetDefinition | null;
 widgetId: number | null;
}>({
 type: null,
 widgetId: null,
});

const dragOverAreaId = ref<number | null>(null);
const dragOverWidgetId = ref<number | null>(null);

const groupedWidgetTypes = computed<WidgetCategoryGroup[]>(() => {
 const groups: Record<string, WidgetCategoryGroup> = {};

 widgetTypes.value.forEach((widget) => {
 const key = widget.category ||'general';
 if (!groups[key]) {
 groups[key] = {
 key,
 label: widgetCategoryLabels.value[key] || formatCategoryLabel(key),
 widgets: [],
 };
 }
 groups[key].widgets.push(widget);
 });

 return Object.values(groups)
 .map((group) => ({
 ...group,
 widgets: group.widgets.sort((a, b) => a.label.localeCompare(b.label)),
 }))
 .sort((a, b) => a.label.localeCompare(b.label));
});

const selectedArea = computed<WidgetArea | null>(() => {
 if (widgetAreas.value.length === 0) {
 return null;
 }
 if (selectedAreaId.value === null) {
 return widgetAreas.value[0];
 }
 return widgetAreas.value.find((area) => area.id === selectedAreaId.value) ?? widgetAreas.value[0];
});

const draggingHasPayload = computed(() => draggingState.type !== null || draggingState.widgetId !== null);

const systemLanguages = ref<any[]>([]);

// Drag and drop state for menu items
let draggingMenuItemList: any[] | null = null;
let draggingMenuItemIndex: number | null = null;

function onMenuItemDragStart(list: any[], index: number, event: DragEvent) {
  draggingMenuItemList = list;
  draggingMenuItemIndex = index;
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move';
  }
}

function onMenuItemDrop(list: any[], targetIndex: number, event: DragEvent) {
  if (draggingMenuItemList === list && draggingMenuItemIndex !== null && draggingMenuItemIndex !== targetIndex) {
    const item = list[draggingMenuItemIndex];
    list.splice(draggingMenuItemIndex, 1);
    list.splice(targetIndex, 0, item);
  }
  draggingMenuItemList = null;
  draggingMenuItemIndex = null;
}

function addMenuItem(formConfig: Record<string, any>, key: string) {
  if (!formConfig[key]) {
    formConfig[key] = [];
  }
  formConfig[key].push({
    title: '',
    link: '',
    rel: '',
    relSelect: '',
    target: '_self',
    icon: ''
  });
}

function isSameDomain(url: string): boolean {
  if (!url) return true;
  if (url.startsWith('/') || url.startsWith('#') || (!url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('//'))) {
    return true;
  }
  try {
    const urlObj = new URL(url);
    return urlObj.hostname === window.location.hostname;
  } catch (e) {
    return false;
  }
}

function handleLinkChange(item: any) {
  if (item._customRel) return;
  if (isSameDomain(item.link)) {
    item.relSelect = '';
    item.rel = '';
  } else {
    item.relSelect = 'nofollow';
    item.rel = 'nofollow';
  }
}

function handleRelSelectChange(item: any) {
  item._customRel = true;
  if (item.relSelect !== 'custom') {
    item.rel = item.relSelect;
  }
}

async function deleteMenuItem(list: any[], index: number) {
  const confirmed = await dialog.confirm({
    title: 'Delete Link Item',
    message: 'Are you sure you want to remove this link item?',
    type: 'danger',
    confirmText: 'Delete',
  });
  if (confirmed) {
    list.splice(index, 1);
  }
}

async function loadLanguages() {
  try {
    const response = await axios.get('/api/v1/languages');
    systemLanguages.value = response.data?.data ?? [];
    if (systemLanguages.value.length === 0) {
      systemLanguages.value = [{ code: 'en', name: 'English', native_name: 'English' }];
    }
    const defaultLang = systemLanguages.value.find(l => l.is_default) || systemLanguages.value[0];
    selectedLocale.value = defaultLang ? defaultLang.code : 'en';
  } catch (error) {
    console.error('Failed to load languages:', error);
    systemLanguages.value = [{ code: 'en', name: 'English', native_name: 'English' }];
  }
}

onMounted(async () => {
  await loadLanguages();
  await refreshAll();
  await loadGlobalSocialLinks();
});

async function refreshAll() {
  await loadWidgetAreas(selectedAreaId.value ?? undefined);
  await loadWidgetTypes();
}

function formatCategoryLabel(category: string): string {
 return category
 .split(/[-_]/)
 .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
 .join('');
}

async function loadWidgetTypes(): Promise<void> {
 widgetTypesLoading.value = true;
 try {
 const response = await axios.get('/api/v1/widgets/types');
 const data = response.data?.data;
 widgetTypes.value = data?.widgets ?? [];
 const categories = data?.categories ?? [];
 const labelMap: Record<string, string> = {};
 categories.forEach((cat: { key: string; label: string }) => {
 labelMap[cat.key] = cat.label;
 });
 widgetCategoryLabels.value = labelMap;

 widgetAreas.value.forEach((area) => {
 area.widgets.forEach((widget) => {
 widget.definition =
 widget.definition ?? findWidgetDefinition(widget.widget_type) ?? null;
 widget.formConfig = prepareConfigForForm(widget.definition, widget.config ?? {});
 });
 });
 } catch (error) {
 console.error('Failed to load widget types:', error);
 dialog.error('Unable to load widget types. Please try again.');
 } finally {
 widgetTypesLoading.value = false;
 }
}

async function loadWidgetAreas(preserveSelectionId?: number): Promise<void> {
 widgetAreasLoading.value = true;
 try {
 const response = await axios.get('/api/v1/widget-areas', {
   params: { 
     locale: selectedLocale.value,
     _t: Date.now()
   }
 });
 const areas: ApiWidgetArea[] = response.data?.data ?? [];
 widgetAreas.value = areas.map((area) => transformArea(area));

  const savedAreaId = localStorage.getItem('selected_widget_area_id');
  if (widgetAreas.value.length === 0) {
  selectedAreaId.value = null;
  } else if (preserveSelectionId) {
  const exists = widgetAreas.value.some((area) => area.id === preserveSelectionId);
  selectedAreaId.value = exists ? preserveSelectionId : widgetAreas.value[0].id;
  } else if (savedAreaId && widgetAreas.value.some((area) => area.id === Number(savedAreaId))) {
  selectedAreaId.value = Number(savedAreaId);
  } else if (!selectedAreaId.value) {
  selectedAreaId.value = widgetAreas.value[0].id;
  }
 } catch (error) {
 console.error('Failed to load widget areas:', error);
 dialog.error('Unable to load widget areas. Please try again.');
 } finally {
 widgetAreasLoading.value = false;
 }
}

function transformArea(area: ApiWidgetArea): WidgetArea {
  return {
    ...area,
    alignment: area.alignment || 'left',
    column_alignments: area.column_alignments || {},
    widgets: (area.widgets ?? []).map((widget) => transformWidget(widget)),
  };
}

function transformWidget(widget: ApiWidgetInstance): WidgetInstance {
 const definition = widget.definition ?? findWidgetDefinition(widget.widget_type) ?? null;
 return {
 ...widget,
 definition,
 isExpanded: false,
 isSaving: false,
 formTitle: widget.title ??'',
 formConfig: prepareConfigForForm(definition, widget.config ?? {}),
 formActive: widget.active,
 };
}

function findWidgetDefinition(type: string): WidgetDefinition | undefined {
 return widgetTypes.value.find((widget) => widget.type === type);
}

function prepareConfigForForm(definition: WidgetDefinition | null, config: Record<string, unknown>): Record<string, any> {
  const formConfig: Record<string, any> = {};
  if (!definition) {
    return { ...config };
  }

  const schema = definition.config_schema || {};
  const defaults = definition.default_config || {};

  Object.entries(schema).forEach(([key, field]) => {
    const value = config[key] ?? defaults[key] ?? field.default ?? null;

    switch (field.type) {
      case 'boolean':
        formConfig[key] = Boolean(value);
        break;
      case 'number':
        formConfig[key] = value !== null && value !== undefined ? Number(value) : null;
        break;
      case 'translatable_text':
        formConfig[key] = typeof value === 'object' && value !== null ? { ...value } : {};
        break;
      case 'menu_items':
        if (Array.isArray(value)) {
          formConfig[key] = value.map(item => {
            const relVal = item.rel ?? '';
            const relSelect = (relVal === '' || relVal === 'nofollow') ? relVal : 'custom';
            return {
              title: item.title ?? '',
              link: item.link ?? '',
              rel: relVal,
              relSelect: relSelect,
              target: item.target ?? '_self',
              icon: item.icon ?? ''
            };
          });
        } else {
          formConfig[key] = [];
        }
        break;
      case 'tags':
        if (Array.isArray(value)) {
          formConfig[key] = value.join(',');
        } else if (typeof value === 'string') {
          formConfig[key] = value;
        } else {
          formConfig[key] = '';
        }
        break;
      default:
        formConfig[key] = value ?? '';
    }
  });

  return formConfig;
}

function prepareConfigForSave(widget: WidgetInstance): Record<string, unknown> {
  const definition = widget.definition;
  if (!definition) {
    return widget.formConfig;
  }

  const schema = definition.config_schema || {};
  const payload: Record<string, unknown> = {};

  Object.entries(schema).forEach(([key, field]) => {
    let value = widget.formConfig[key];

    switch (field.type) {
      case 'boolean':
        value = Boolean(value);
        break;
      case 'number':
        if (value === '' || value === null || value === undefined) {
          value = null;
        } else {
          value = Number(value);
        }
        break;
      case 'translatable_text':
        value = typeof value === 'object' && value !== null ? value : {};
        break;
      case 'menu_items':
        value = (Array.isArray(value) ? value : []).map(item => ({
          title: item.title ?? '',
          link: item.link ?? '',
          rel: item.relSelect === 'custom' ? (item.rel ?? '') : (item.relSelect ?? ''),
          target: item.target ?? '_self',
          icon: item.icon ?? ''
        }));
        break;
      case 'tags':
        if (Array.isArray(value)) {
          value = value;
        } else if (typeof value === 'string') {
          const items = value
            .split(',')
            .map((part) => part.trim())
            .filter((part) => part.length > 0)
            .map((part) => {
              const numeric = Number(part);
              return Number.isNaN(numeric) ? part : numeric;
            });
          value = items;
        } else {
          value = [];
        }
        break;
      default:
        value = value ?? null;
    }

    payload[key] = value;
  });

  if (widget.formConfig.layout_column !== undefined) {
    payload.layout_column = widget.formConfig.layout_column;
  }

  return payload;
}

function selectArea(areaId: number): void {
 selectedAreaId.value = areaId;
 localStorage.setItem('selected_widget_area_id', String(areaId));
}

function onWidgetTypeDragStart(widget: WidgetDefinition, event: DragEvent): void {
 draggingState.type = widget;
 draggingState.widgetId = null;
 dragOverAreaId.value = null;
 dragOverWidgetId.value = null;
 event.dataTransfer?.setData('text/plain', widget.type);
 event.dataTransfer?.setDragImage(createDragImage(widget.label), 0, 0);
}

function onWidgetInstanceDragStart(widget: WidgetInstance, event: DragEvent): void {
 draggingState.type = null;
 draggingState.widgetId = widget.id;
 dragOverAreaId.value = null;
 dragOverWidgetId.value = null;
 event.dataTransfer?.setData('text/plain', String(widget.id));
 event.dataTransfer?.setDragImage(createDragImage(getWidgetLabel(widget)), 0, 0);
}

function handleAreaDragOver(area: WidgetArea, event: DragEvent): void {
 if (!draggingHasPayload.value) return;
 event.preventDefault();
 event.stopPropagation();
 dragOverAreaId.value = area.id;
 dragOverWidgetId.value = null;
 if (event.dataTransfer) {
 event.dataTransfer.dropEffect ='move';
 }
}

async function handleAreaDrop(area: WidgetArea, event: DragEvent): Promise<void> {
  if (!draggingHasPayload.value) return;
  event.preventDefault();
  event.stopPropagation();
  await commitDrop(area, area.widgets.length, 0);
  resetDragState();
}

function handleWidgetDragOver(area: WidgetArea, widget: WidgetInstance, event: DragEvent): void {
  if (!draggingHasPayload.value) return;
  event.preventDefault();
  event.stopPropagation();
  dragOverAreaId.value = area.id;
  dragOverWidgetId.value = widget.id;
  if (event.dataTransfer) {
    event.dataTransfer.dropEffect = 'move';
  }
}

async function handleWidgetDrop(area: WidgetArea, widget: WidgetInstance, event: DragEvent): Promise<void> {
  if (!draggingHasPayload.value) return;
  event.preventDefault();
  event.stopPropagation();
  const targetIndex = Math.max(area.widgets.findIndex((item) => item.id === widget.id), 0);
  
  const targetCol = widget.config?.layout_column || 'col_0';
  const colIndex = Number(targetCol.replace('col_', ''));
  
  await commitDrop(area, targetIndex, colIndex);
  resetDragState();
}

async function commitDrop(targetArea: WidgetArea, targetIndex: number, colIndex?: number): Promise<void> {
  const areaRef = widgetAreas.value.find((area) => area.id === targetArea.id);
  if (!areaRef) return;

  const targetColIndex = colIndex !== undefined ? colIndex : 0;

  if (draggingState.type) {
    await addWidgetByType(areaRef, draggingState.type, targetIndex, targetColIndex);
  } else if (draggingState.widgetId !== null) {
    await moveWidgetById(draggingState.widgetId, areaRef, targetIndex, targetColIndex);
  }
}

async function addWidgetByType(area: WidgetArea, definition: WidgetDefinition, targetIndex: number, colIndex: number): Promise<void> {
  try {
    const response = await axios.post('/api/v1/widget-instances', {
      widget_area_id: area.id,
      widget_type: definition.type,
      locale: selectedLocale.value,
      config: {
        layout_column: `col_${colIndex}`
      }
    });

    const newWidget = transformWidget(response.data?.data as ApiWidgetInstance);
    const areaRef = widgetAreas.value.find((item) => item.id === area.id);
    if (!areaRef) return;

    const insertIndex = Math.min(Math.max(targetIndex, 0), areaRef.widgets.length);
    areaRef.widgets.splice(insertIndex, 0, newWidget);
    await persistAreaOrder(areaRef);
    selectedAreaId.value = area.id;
    dialog.success('Widget added.');
  } catch (error: any) {
    console.error('Failed to add widget:', error);
    const message = error?.response?.data?.message ?? 'Unable to add widget.';
    dialog.error(message);
    await loadWidgetAreas(selectedAreaId.value ?? undefined);
  }
}

async function moveWidgetById(widgetId: number, targetArea: WidgetArea, targetIndex: number, colIndex: number): Promise<void> {
  const sourceArea = widgetAreas.value.find((area) =>
    area.widgets.some((widget) => widget.id === widgetId)
  );

  if (!sourceArea) return;

  const sourceIndex = sourceArea.widgets.findIndex((widget) => widget.id === widgetId);
  if (sourceIndex === -1) return;

  const [widget] = sourceArea.widgets.splice(sourceIndex, 1);
  const targetAreaRef = widgetAreas.value.find((area) => area.id === targetArea.id);

  if (!targetAreaRef) return;

  const areaIdChanged = sourceArea.id !== targetAreaRef.id;

  try {
    const payload: any = {
      config: {
        ...widget.formConfig,
        layout_column: `col_${colIndex}`
      }
    };
    if (areaIdChanged) {
      payload.widget_area_id = targetAreaRef.id;
    }

    await axios.put(`/api/v1/widget-instances/${widgetId}`, payload);
    
    if (!widget.config) widget.config = {};
    if (!widget.formConfig) widget.formConfig = {};
    widget.config.layout_column = `col_${colIndex}`;
    widget.formConfig.layout_column = `col_${colIndex}`;
  } catch (error: any) {
    console.error('Failed to move widget:', error);
    const message = error?.response?.data?.message ?? 'Unable to move widget.';
    dialog.error(message);
    await loadWidgetAreas(selectedAreaId.value ?? undefined);
    return;
  }

  const insertIndex = Math.min(Math.max(targetIndex, 0), targetAreaRef.widgets.length);
  targetAreaRef.widgets.splice(insertIndex, 0, widget);

  if (targetAreaRef.id !== sourceArea.id) {
    await persistAreaOrder(sourceArea);
  }
  await persistAreaOrder(targetAreaRef);
  selectedAreaId.value = targetAreaRef.id;
}

async function persistAreaOrder(area: WidgetArea): Promise<void> {
 if (area.widgets.length === 0) {
 return;
 }

 const ids = area.widgets.map((widget) => widget.id);
 try {
 await axios.post(`/api/v1/widget-areas/${area.id}/reorder`, {
 widget_ids: ids,
 locale: selectedLocale.value,
 });
 area.widgets.forEach((widget, index) => {
 widget.order = (index + 1) * 10;
 });
 } catch (error) {
 console.error('Failed to persist widget order:', error);
 dialog.error('Unable to reorder widgets. The list will be refreshed.');
 await loadWidgetAreas(selectedAreaId.value ?? undefined);
 }
}

async function saveWidget(widget: WidgetInstance): Promise<void> {
 widget.isSaving = true;
 try {
 const payload = {
 title: widget.formTitle,
 config: prepareConfigForSave(widget),
 };
 await axios.put(`/api/v1/widget-instances/${widget.id}`, payload);
 widget.title = widget.formTitle;
 widget.config = payload.config;
 dialog.success('Widget saved.');
 } catch (error: any) {
 console.error('Failed to save widget:', error);
 const message = error?.response?.data?.message ??'Unable to save widget.';
 dialog.error(message);
 } finally {
 widget.isSaving = false;
 }
}

async function updateWidgetActive(widget: WidgetInstance): Promise<void> {
 try {
 await axios.put(`/api/v1/widget-instances/${widget.id}`, {
 active: widget.formActive,
 });
 widget.active = widget.formActive;
 } catch (error: any) {
 console.error('Failed to update widget status:', error);
 widget.formActive = widget.active;
 const message = error?.response?.data?.message ??'Unable to update widget status.';
 dialog.error(message);
 }
}

async function deleteWidget(area: WidgetArea, widget: WidgetInstance): Promise<void> {
 const confirmed = await dialog.confirm({
 title:'Remove Widget',
 message: `Remove"${getWidgetLabel(widget)}" from this area?`,
 type:'danger',
 confirmText:'Remove',
 });

 if (!confirmed) {
 return;
 }

 widget.isSaving = true;
 try {
 await axios.delete(`/api/v1/widget-instances/${widget.id}`);
 const areaRef = widgetAreas.value.find((item) => item.id === area.id);
 if (areaRef) {
 areaRef.widgets = areaRef.widgets.filter((item) => item.id !== widget.id);
 await persistAreaOrder(areaRef);
 }
 dialog.success('Widget removed.');
 } catch (error: any) {
 console.error('Failed to delete widget:', error);
 const message = error?.response?.data?.message ??'Unable to remove widget.';
 dialog.error(message);
 await loadWidgetAreas(selectedAreaId.value ?? undefined);
 } finally {
 widget.isSaving = false;
 }
}

function revertWidgetChanges(widget: WidgetInstance): void {
 widget.formTitle = widget.title ??'';
 widget.formConfig = prepareConfigForForm(widget.definition, widget.config ?? {});
 widget.formActive = widget.active;
}

function toggleWidgetExpanded(widget: WidgetInstance): void {
 widget.isExpanded = !widget.isExpanded;
}

function getWidgetLabel(widget: WidgetInstance): string {
 const baseLabel = widget.definition?.label ?? widget.widget_type;
 if (widget.title && widget.title.trim() !=='') {
 return `${widget.title} (${baseLabel})`;
 }
 return baseLabel;
}

function resetDragState(): void {
 draggingState.type = null;
 draggingState.widgetId = null;
 dragOverAreaId.value = null;
 dragOverWidgetId.value = null;
}

async function updateAreaLayout(area: WidgetArea) {
  try {
    await axios.put(`/api/v1/widget-areas/${area.id}`, {
      layout: area.layout
    });
    dialog.success('Area layout updated.');
  } catch (error) {
    console.error('Failed to update area layout:', error);
    dialog.error('Failed to update area layout.');
  }
}

async function updateAreaAlignment(area: WidgetArea) {
  try {
    await axios.put(`/api/v1/widget-areas/${area.id}`, {
      alignment: area.alignment
    });
    dialog.success('Area alignment updated.');
  } catch (error) {
    console.error('Failed to update area alignment:', error);
    dialog.error('Failed to update area alignment.');
  }
}

async function updateColumnAlignment(area: WidgetArea, colIndex: number, align: string) {
  try {
    if (!area.column_alignments) {
      area.column_alignments = {};
    }
    area.column_alignments['col_' + colIndex] = align;

    await axios.put(`/api/v1/widget-areas/${area.id}`, {
      column_alignments: area.column_alignments
    });
    dialog.success(`Column ${colIndex + 1} alignment updated.`);
  } catch (error) {
    console.error('Failed to update column alignment:', error);
    dialog.error('Failed to update column alignment.');
  }
}

async function handleColDrop(area: WidgetArea, colIndex: number, event: DragEvent) {
  if (!draggingHasPayload.value) return;
  event.preventDefault();
  event.stopPropagation();
  
  await commitDrop(area, area.widgets.length, colIndex);
  resetDragState();
}

function getColumnCount(layout: string): number {
  if (layout === '2-col' || layout === 'split-left' || layout === 'split-right') return 2;
  if (layout === '3-col') return 3;
  if (layout === '4-col') return 4;
  if (layout === '5-col') return 5;
  return 1;
}

function gridClassForLayout(layout: string): string {
  if (layout === '2-col') return 'grid-cols-2';
  if (layout === '3-col') return 'grid-cols-3';
  if (layout === '4-col') return 'grid-cols-4';
  if (layout === '5-col') return 'grid-cols-5';
  if (layout === 'split-left' || layout === 'split-right') return 'grid-cols-12';
  return 'grid-cols-1';
}

function getColSpanClass(layout: string, colIndex: number): string {
  if (layout === 'split-left') {
    return colIndex === 0 ? 'col-span-8' : 'col-span-4';
  }
  if (layout === 'split-right') {
    return colIndex === 0 ? 'col-span-4' : 'col-span-8';
  }
  return '';
}

function getColWidgets(area: WidgetArea, colIndex: number): WidgetInstance[] {
  const colKey = `col_${colIndex}`;
  return area.widgets.filter((w) => {
    const col = w.config?.layout_column || 'col_0';
    return col === colKey;
  });
}

function createDragImage(label: string): HTMLElement {
 const el = document.createElement('div');
 el.style.position ='absolute';
 el.style.top ='-9999px';
 el.style.left ='-9999px';
 el.style.padding ='6px 10px';
 el.style.borderRadius ='6px';
 el.style.background ='#4f46e5';
 el.style.color ='#fff';
 el.style.fontSize ='12px';
 el.style.boxShadow ='0 4px 10px rgb(var(--admin-theme-primary) / 0.3)';
 el.textContent = label;
 document.body.appendChild(el);
 setTimeout(() => document.body.removeChild(el), 0);
 return el;
}
</script>
