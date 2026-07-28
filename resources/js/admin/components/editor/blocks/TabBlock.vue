<template>
  <!-- Settings Mode (sidebar options) -->
  <div v-if="mode === 'settings'" class="tabs-block-settings space-y-4 text-xs">
    <div class="space-y-3">
      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-admin-theme-text-muted mb-1">Tab Header Style</label>
        <select v-model="state.style" class="w-full bg-admin-theme-base border border-admin-theme-border rounded-lg p-2 text-xs focus:ring-1 focus:ring-admin-theme-primary">
          <option value="underline">Underline (Thanh gạch dưới)</option>
          <option value="pills">Pills (Nút bo tròn)</option>
          <option value="blocks">Blocks (Khối khung thẻ)</option>
        </select>
      </div>

      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-admin-theme-text-muted mb-1">Header Alignment</label>
        <select v-model="state.alignment" class="w-full bg-admin-theme-base border border-admin-theme-border rounded-lg p-2 text-xs focus:ring-1 focus:ring-admin-theme-primary">
          <option value="start">Left (Căn trái)</option>
          <option value="center">Center (Căn giữa)</option>
          <option value="end">Right (Căn phải)</option>
        </select>
      </div>
    </div>

    <!-- Tab Items Manager -->
    <div class="border-t border-admin-theme-border/60 pt-3 space-y-3">
      <div class="flex items-center justify-between">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-admin-theme-text-muted">
          Tabs List ({{ state.items.length }})
        </label>
        <button @click="addItem" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-[10px] font-bold uppercase cursor-pointer">
          + Add Tab
        </button>
      </div>

      <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
        <div 
          v-for="(item, index) in state.items" 
          :key="index" 
          class="p-2.5 bg-admin-theme-base/60 border rounded-lg space-y-2 transition-colors"
          :class="activeTabIdx === index ? 'border-cyan-500 ring-1 ring-cyan-500/30' : 'border-admin-theme-border'"
        >
          <div class="flex items-center justify-between gap-2">
            <span class="font-bold text-[10px] uppercase text-cyan-500">Tab #{{ index + 1 }}</span>
            <div class="flex items-center gap-1">
              <button @click="moveItem(index, -1)" v-if="index > 0" class="p-1 hover:bg-admin-theme-base text-gray-400 rounded" title="Move up">↑</button>
              <button @click="moveItem(index, 1)" v-if="index < state.items.length - 1" class="p-1 hover:bg-admin-theme-base text-gray-400 rounded" title="Move down">↓</button>
              <button @click="removeItem(index)" class="p-1 text-red-400 hover:bg-red-500/10 rounded font-bold" title="Delete">×</button>
            </div>
          </div>

          <input 
            v-model="item.title" 
            type="text" 
            placeholder="Tab Title..." 
            class="w-full bg-admin-theme-base border border-admin-theme-border rounded p-1.5 text-xs font-semibold focus:ring-1 focus:ring-admin-theme-primary"
          />

          <textarea 
            v-model="item.content" 
            rows="3"
            placeholder="Provide content here..."
            class="w-full bg-admin-theme-base border border-admin-theme-border rounded p-1.5 text-xs focus:ring-1 focus:ring-admin-theme-primary"
          ></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Preview Mode (Main Canvas & Nested Inside Row Grid Columns) -->
  <div v-else class="tabs-block-preview my-2 select-none w-full" :style="{ padding: state.padding }">
    <!-- Interactive Tab Navigation Header -->
    <div class="tabs-preview-header flex flex-wrap gap-2 mb-4 border-b border-slate-200 dark:border-slate-800 pb-2" :class="getAlignClass(state.alignment)">
      <button 
        v-for="(item, i) in state.items" 
        :key="i"
        type="button"
        @click.stop="activeTabIdx = i"
        class="tabs-preview-tab px-4 py-2 text-xs font-bold transition-all rounded-lg cursor-pointer"
        :class="getTabHeaderClass(i)"
      >
        {{ item.title || `Tab ${Number(i) + 1}` }}
      </button>
    </div>

    <!-- Active Tab Body Container -->
    <div class="tabs-preview-body p-4 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-xs leading-relaxed text-slate-700 dark:text-slate-200 min-h-[80px]">
      <div v-if="state.items[activeTabIdx]" class="space-y-2">
        <div class="flex items-center justify-between text-[10px] font-bold text-cyan-500 uppercase tracking-wider mb-1">
          <span>Active Tab: {{ state.items[activeTabIdx].title }}</span>
          <span class="opacity-60">(Click tab above to switch)</span>
        </div>
        <div class="prose dark:prose-invert max-w-none text-xs font-normal" v-html="renderFormattedContent(state.items[activeTabIdx].content)"></div>
      </div>
      <div v-else class="text-slate-400 italic">No tab content...</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps<{
  modelValue?: any;
  mode?: 'settings' | 'preview';
  data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const activeTabIdx = ref(0);

const state = reactive({
  items: JSON.parse(JSON.stringify(props.modelValue?.items || props.data?.items || [
    { title: 'Tab 1', content: 'Provide Tab 1 content here...' },
    { title: 'Tab 2', content: 'Provide Tab 2 content here...' }
  ])),
  style: props.modelValue?.style || props.data?.style || 'underline',
  alignment: props.modelValue?.alignment || props.data?.alignment || 'start',
  margin: props.modelValue?.margin || props.data?.margin || '',
  padding: props.modelValue?.padding || props.data?.padding || '',
});

const getAlignClass = (align: string) => {
  if (align === 'center') return 'justify-center';
  if (align === 'end') return 'justify-end';
  return 'justify-start';
};

const getTabHeaderClass = (index: number) => {
  const isActive = index === activeTabIdx.value;
  const style = state.style || 'underline';

  if (style === 'pills') {
    return isActive 
      ? 'bg-emerald-600 text-white shadow-md' 
      : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-300 dark:hover:bg-slate-700';
  } else if (style === 'blocks') {
    return isActive 
      ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 border-2 border-emerald-500 shadow-sm' 
      : 'bg-slate-100 dark:bg-slate-900 text-slate-500 dark:text-slate-400 hover:bg-slate-200 border border-slate-200 dark:border-slate-800';
  } else {
    // Underline
    return isActive 
      ? 'text-emerald-600 dark:text-emerald-400 border-b-2 border-emerald-600 font-extrabold' 
      : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent';
  }
};

const renderFormattedContent = (text?: string) => {
  if (!text) return '<p class="italic opacity-60">Empty tab content...</p>';
  return text.replace(/\n/g, '<br>');
};

const addItem = () => {
  state.items.push({ title: `Tab ${state.items.length + 1}`, content: 'Provide content here...' });
  activeTabIdx.value = state.items.length - 1;
};

const removeItem = (index: number) => {
  if (state.items.length <= 1) return;
  state.items.splice(index, 1);
  if (activeTabIdx.value >= state.items.length) {
    activeTabIdx.value = Math.max(0, state.items.length - 1);
  }
};

const moveItem = (index: number, direction: number) => {
  const newIndex = index + direction;
  if (newIndex < 0 || newIndex >= state.items.length) return;
  const temp = state.items[index];
  state.items[index] = state.items[newIndex];
  state.items[newIndex] = temp;
  activeTabIdx.value = newIndex;
};

// Sync state to parent v-model in settings mode
let isUpdatingFromProps = false;

watch(
  state,
  (val) => {
    if (props.mode === 'settings' && !isUpdatingFromProps) {
      emit('update:modelValue', JSON.parse(JSON.stringify(val)));
    }
  },
  { deep: true }
);

watch(
  () => props.modelValue,
  (newVal) => {
    if (props.mode === 'settings' && newVal) {
      isUpdatingFromProps = true;
      if (newVal.style !== undefined) state.style = newVal.style;
      if (newVal.alignment !== undefined) state.alignment = newVal.alignment;
      if (Array.isArray(newVal.items)) state.items = JSON.parse(JSON.stringify(newVal.items));
      Promise.resolve().then(() => {
        isUpdatingFromProps = false;
      });
    }
  },
  { deep: true, immediate: true }
);

watch(
  () => props.data,
  (newData) => {
    if (newData) {
      if (newData.style !== undefined) state.style = newData.style;
      if (newData.alignment !== undefined) state.alignment = newData.alignment;
      if (Array.isArray(newData.items)) state.items = JSON.parse(JSON.stringify(newData.items));
    }
  },
  { deep: true, immediate: true }
);
</script>
