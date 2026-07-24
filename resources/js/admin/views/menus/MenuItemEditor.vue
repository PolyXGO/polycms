<template>
 <div
 v-if="show"
 class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
 >
 <div class="bg-admin-theme-surface rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
 <div class="px-6 py-4 border-b border-admin-theme-border">
 <h3 class="text-lg font-semibold text-admin-theme-text">
 {{ t('Edit Menu Item') }}
 </h3>
 </div>

 <form @submit.prevent="save" class="p-6 space-y-4">
 <!-- Label -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ t('Navigation Label') }}
 </label>
 <input
 v-model="formData.title"
 type="text"
 required
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 />
 </div>

 <!-- URL (for custom links) -->
 <div v-if="item.type ==='custom'">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ t('URL') }}
 </label>
 <input
 v-model="formData.url"
 type="url"
 required
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 />
 </div>
  <div v-else-if="item.type === 'language'" class="space-y-3">
    <div class="text-sm text-admin-theme-text-muted">
      {{ t('URL') }}: <span class="font-mono text-admin-theme-primary font-semibold">{{ t('Language selection dropdown') }}</span>
    </div>
    <div>
      <label class="inline-flex items-center gap-2 cursor-pointer">
        <input
          v-model="formData.show_label"
          type="checkbox"
          class="w-4 h-4 text-admin-theme-primary border-admin-theme-border rounded focus:ring-admin-theme-primary"
        />
        <span class="text-sm text-admin-theme-text-secondary">
          {{ t('Show label text') }}
        </span>
      </label>
    </div>
  </div>
  <div v-else-if="item.type === 'search'" class="space-y-3">
    <div class="text-sm text-admin-theme-text-muted">
      {{ t('URL') }}: <span class="font-mono text-admin-theme-primary font-semibold">{{ t('Search Action') }}</span>
    </div>
    <div>
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
        {{ t('Display Style') }}
      </label>
      <select
        v-model="formData.search_style"
        class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
      >
        <option value="icon_modal">{{ t('Search Icon opens Modal') }}</option>
        <option value="icon_expand">{{ t('Search Icon expands input') }}</option>
        <option value="icon_inline">{{ t('Search Icon leading inline input') }}</option>
        <option value="pill">{{ t('Pill shape input') }}</option>
        <option value="minimal">{{ t('Minimal bottom-border only input') }}</option>
        <option value="form">{{ t('Standard box input') }}</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
        {{ t('Placeholder text') }}
      </label>
      <input
        v-model="formData.search_placeholder"
        type="text"
        :placeholder="t('Search...')"
        class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
      />
    </div>
    <div class="grid grid-cols-3 gap-3">
      <div>
        <label class="block text-xs font-semibold text-admin-theme-text-secondary mb-1">
          {{ t('Border Width') }}
        </label>
        <select
          v-model="formData.search_border_width"
          class="w-full px-2 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
        >
          <option value="">{{ t('Default') }}</option>
          <option value="0px">{{ t('None (0px)') }}</option>
          <option value="1px">1px</option>
          <option value="2px">2px</option>
          <option value="3px">3px</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-admin-theme-text-secondary mb-1">
          {{ t('Border Radius') }}
        </label>
        <input
          v-model="formData.search_border_radius"
          type="text"
          placeholder="e.g. 8px, 9999px"
          class="w-full px-2 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
        />
      </div>
      <div>
        <label class="block text-xs font-semibold text-admin-theme-text-secondary mb-1">
          {{ t('Border Color') }}
        </label>
        <div class="flex items-center gap-2">
          <input
            v-model="formData.search_border_color"
            type="color"
            class="h-8 w-8 p-0 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg cursor-pointer"
          />
          <input
            v-model="formData.search_border_color"
            type="text"
            placeholder="#e2e8f0"
            class="w-full min-w-0 px-1 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-xs focus:outline-none focus:ring-1"
          />
        </div>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-3 mt-3">
      <div>
        <label class="block text-xs font-semibold text-admin-theme-text-secondary mb-1">
          {{ t('Background Color') }}
        </label>
        <div class="flex items-center gap-2">
          <input
            v-model="formData.search_bg_color"
            type="color"
            class="h-8 w-8 p-0 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg cursor-pointer"
          />
          <input
            v-model="formData.search_bg_color"
            type="text"
            placeholder="#f8fafc"
            class="w-full min-w-0 px-1 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-xs focus:outline-none focus:ring-1"
          />
        </div>
      </div>
      <div>
        <label class="block text-xs font-semibold text-admin-theme-text-secondary mb-1">
          {{ t('Background Hover Color') }}
        </label>
        <div class="flex items-center gap-2">
          <input
            v-model="formData.search_bg_hover_color"
            type="color"
            class="h-8 w-8 p-0 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg cursor-pointer"
          />
          <input
            v-model="formData.search_bg_hover_color"
            type="text"
            placeholder="#eef2ff"
            class="w-full min-w-0 px-1 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-xs focus:outline-none focus:ring-1"
          />
        </div>
      </div>
    </div>
  </div>
  <div v-else class="text-sm text-admin-theme-text-muted">
  {{ t('URL') }}: <span class="font-mono">{{ item.url || t('Auto-generated') }}</span>
  </div>

 <!-- Target -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ t('Open link in') }}
 </label>
 <select
 v-model="formData.target"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 >
 <option value="_self">{{ t('Same window') }}</option>
 <option value="_blank">{{ t('New window') }}</option>
 </select>
 </div>

 <!-- CSS Class -->
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ t('CSS Classes') }}
 </label>
 <input
 v-model="formData.css_class"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 :placeholder="t('optional')"
 />
 <p class="text-xs text-admin-theme-text-muted mt-1">
 {{ t('Separate multiple classes with spaces') }}
 </p>
 </div>

 <!-- Active -->
 <div>
 <label class="inline-flex items-center gap-2 cursor-pointer">
 <input
 v-model="formData.active"
 type="checkbox"
 class="w-4 h-4 text-admin-theme-primary border-admin-theme-border rounded focus:ring-admin-theme-primary"
 />
 <span class="text-sm text-admin-theme-text-secondary">
 {{ t('Enable this menu item') }}
 </span>
 </label>
 </div>

 <!-- Actions -->
 <div class="flex justify-end gap-3 pt-4 border-t border-admin-theme-border">
 <button
 type="button"
 @click="$emit('close')"
 class="px-4 py-2 border border-admin-theme-border rounded-lg hover:bg-admin-theme-base text-admin-theme-text-secondary transition-colors"
 >
 {{ t('Cancel') }}
 </button>
 <button
 type="submit"
 :disabled="saving"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors disabled:opacity-50"
 >
 <span v-if="saving" class="flex items-center">
 <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
 {{ t('Saving...') }}
 </span>
 <span v-else>{{ t('Save') }}</span>
 </button>
 </div>
 </form>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';

const props = defineProps<{
 show: boolean;
 item: any | null;
 menuId: number;
}>();

const emit = defineEmits<{
 (e: 'close'): void;
 (e: 'saved', item: any): void;
}>();

const { t } = useTranslation();
const dialog = useDialog();

const saving = ref(false);
const formData = ref({
 title: '',
 url: '',
 target: '_self',
 css_class: '',
 active: true,
 show_label: true,
 search_style: 'icon_modal',
 search_placeholder: 'Search...',
 search_border_width: '',
 search_border_radius: '',
 search_border_color: '',
 search_bg_color: '',
 search_bg_hover_color: '',
});

watch(() => props.item, (newItem) => {
 if (newItem) {
 let showLabel = true;
 let searchStyle = 'icon_modal';
 let searchPlaceholder = 'Search...';
 let searchBorderWidth = '';
 let searchBorderRadius = '';
 let searchBorderColor = '';
 let searchBgColor = '';
 let searchBgHoverColor = '';
 if (newItem.type === 'language') {
 try {
 if (newItem.url && newItem.url.startsWith('{')) {
 const parsed = JSON.parse(newItem.url);
 showLabel = parsed.show_label !== false;
 } else if (newItem.url && newItem.url.includes('show_label=')) {
 showLabel = !newItem.url.includes('show_label=0');
 }
 } catch (e) {
 console.error(e);
 }
 } else if (newItem.type === 'search') {
 try {
 if (newItem.url && newItem.url.startsWith('{')) {
 const parsed = JSON.parse(newItem.url);
 searchStyle = parsed.search_style || 'icon_modal';
 searchPlaceholder = parsed.search_placeholder || 'Search...';
 searchBorderWidth = parsed.search_border_width || '';
 searchBorderRadius = parsed.search_border_radius || '';
 searchBorderColor = parsed.search_border_color || '';
 searchBgColor = parsed.search_bg_color || '';
 searchBgHoverColor = parsed.search_bg_hover_color || '';
 }
 } catch (e) {
 console.error(e);
 }
 }
 formData.value = {
 title: newItem.title || '',
 url: newItem.url || '',
 target: newItem.target || '_self',
 css_class: newItem.css_class || '',
 active: newItem.active !== false,
 show_label: showLabel,
 search_style: searchStyle,
 search_placeholder: searchPlaceholder,
 search_border_width: searchBorderWidth,
 search_border_radius: searchBorderRadius,
 search_border_color: searchBorderColor,
 search_bg_color: searchBgColor,
 search_bg_hover_color: searchBgHoverColor,
 };
 }
}, { immediate: true });

const save = async () => {
 if (!props.item) return;

 saving.value = true;
 try {
 const dataToSend = { ...formData.value };
 if (props.item.type === 'language') {
 dataToSend.url = JSON.stringify({
 show_label: dataToSend.show_label
 });
 } else if (props.item.type === 'search') {
 dataToSend.url = JSON.stringify({
 search_style: dataToSend.search_style,
 search_placeholder: dataToSend.search_placeholder,
 search_border_width: dataToSend.search_border_width,
 search_border_radius: dataToSend.search_border_radius,
 search_border_color: dataToSend.search_border_color,
 search_bg_color: dataToSend.search_bg_color,
 search_bg_hover_color: dataToSend.search_bg_hover_color,
 });
 }
 delete (dataToSend as any).show_label;
 delete (dataToSend as any).search_style;
 delete (dataToSend as any).search_placeholder;
 delete (dataToSend as any).search_border_width;
 delete (dataToSend as any).search_border_radius;
 delete (dataToSend as any).search_border_color;
 delete (dataToSend as any).search_bg_color;
 delete (dataToSend as any).search_bg_hover_color;

 const response = await axios.put(
 `/api/v1/menus/${props.menuId}/items/${props.item.id}`,
 dataToSend
 );

 emit('saved', response.data.data);
 dialog.success(t('Menu item updated successfully'));
 } catch (error: any) {
 console.error('Error updating menu item:', error);
 const message = error.response?.data?.error?.message || t('Failed to update menu item');
 dialog.error(message);
 } finally {
 saving.value = false;
 }
};
</script>
