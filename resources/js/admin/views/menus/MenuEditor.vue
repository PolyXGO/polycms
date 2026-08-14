<template>
 <div class="w-full">
 <!-- Menu Header -->
 <div class="bg-admin-theme-surface rounded-lg shadow px-6 py-4 mb-6">
 <div class="flex items-center justify-between gap-4">
 <div class="flex-1">
 <input
 v-model="menuName"
 @blur="updateMenuName"
 @keyup.enter="updateMenuName"
 class="text-lg font-semibold bg-transparent border-none focus:outline-none focus:ring-0 text-admin-theme-text w-full"
 :placeholder="t('Menu Name')"
 />
 <p v-if="menu.location" class="text-xs text-admin-theme-text-muted mt-1">
 {{ t('Location') }}: <span class="font-mono">{{ menu.location }}</span>
 </p>
 </div>
 <div class="flex items-center gap-3">
 <select
 v-model="selectedLocation"
 @change="assignLocation"
 class="px-3 py-2 text-sm border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:ring-1 focus:ring-admin-theme-primary"
 >
 <option value="">{{ t('Select Location') }}</option>
 <option value="header">{{ t('Header') }}</option>
 <option value="footer">{{ t('Footer') }}</option>
 <option value="sidebar">{{ t('Sidebar') }}</option>
 <option value="mobile">{{ t('Mobile') }}</option>
 </select>
 <button
 @click="saveMenu"
 :disabled="saving"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors disabled:opacity-50"
 >
 <span v-if="saving" class="flex items-center">
 <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
 {{ t('Saving...') }}
 </span>
 <span v-else>{{ t('Save Menu') }}</span>
 </button>
 </div>
 </div>
 </div>

 <!-- Two-Panel Layout -->
 <div class="menu-editor-layout">
 <!-- Left Panel: Content Browser -->
 <section class="menu-editor-left">
 <div class="bg-admin-theme-surface rounded-lg shadow flex flex-col min-h-[600px] h-full w-full">
 <ContentBrowser :existing-items="menuItems" @add-items="handleAddItems" />
 </div>
 </section>

 <!-- Right Panel: Menu Structure -->
 <section class="menu-editor-right">
 <div class="bg-admin-theme-surface rounded-lg shadow flex flex-col min-h-[600px] h-full w-full">
 <MenuStructure
 :menu-id="menu.id"
 :items="menuItems"
 @items-updated="handleItemsUpdated"
 @item-deleted="handleItemDeleted"
 @item-edited="handleItemEdited"
 @toggle-active="handleItemToggleActive"
 />
 </div>
 </section>
 </div>

 <!-- Menu Item Editor Modal -->
 <MenuItemEditor
 :show="showItemEditor"
 :item="editingItem"
 :menu-id="menu.id"
 @close="showItemEditor = false; editingItem = null"
 @saved="handleItemSaved"
 />
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from'vue';
import axios from'axios';
import { useDialog } from'../../composables/useDialog';
import { useTranslation } from'../../composables/useTranslation';
import ContentBrowser from'./ContentBrowser.vue';
import MenuStructure from'./MenuStructure.vue';
import MenuItemEditor from'./MenuItemEditor.vue';

const props = defineProps<{
 menu: {
 id: number;
 name: string;
 location: string | null;
 };
}>();

const emit = defineEmits<{
 (e:'menu-updated', menu: any): void;
 (e:'menu-deleted'): void;
}>();

const { t } = useTranslation();
const dialog = useDialog();

const menuName = ref(props.menu.name);
const selectedLocation = ref(props.menu.location ||'');
const saving = ref(false);
const menuItems = ref<any[]>([]);
const showItemEditor = ref(false);
const editingItem = ref<any | null>(null);

const loadMenuItems = async () => {
 try {
 const response = await axios.get(`/api/v1/menus/${props.menu.id}/items`);
 const items = response.data?.data || [];
 // Ensure items is always an array
 menuItems.value = Array.isArray(items) ? items : [];
 } catch (error: any) {
 console.error('Error loading menu items:', error);
 dialog.error(t('Failed to load menu items'));
 menuItems.value = [];
 }
};

const updateMenuName = async () => {
 if (menuName.value.trim() === props.menu.name) {
 return;
 }

 try {
 const response = await axios.put(`/api/v1/menus/${props.menu.id}`, {
 name: menuName.value.trim(),
 });
 emit('menu-updated', response.data.data);
 } catch (error: any) {
 console.error('Error updating menu name:', error);
 dialog.error(t('Failed to update menu name'));
 menuName.value = props.menu.name; // Revert
 }
};

const assignLocation = async () => {
 try {
 const response = await axios.post(`/api/v1/menus/${props.menu.id}/assign`, {
 location: selectedLocation.value || null,
 });
 emit('menu-updated', response.data.data);
 dialog.success(t('Menu location updated'));
 } catch (error: any) {
 console.error('Error assigning location:', error);
 dialog.error(t('Failed to assign menu location'));
 }
};

const saveMenu = async () => {
 saving.value = true;
 try {
 await updateMenuName();
 dialog.success(t('Menu saved successfully'));
 } catch (error: any) {
 console.error('Error saving menu:', error);
 } finally {
 saving.value = false;
 }
};

const handleAddItems = async (items: any[]) => {
 for (const item of items) {
 try {
 await axios.post(`/api/v1/menus/${props.menu.id}/items`, item);
 } catch (error: any) {
 console.error('Error adding menu item:', error);
 }
 }
 await loadMenuItems();
};

const handleItemsUpdated = (items: any[]) => {
 menuItems.value = items;
};

const handleItemDeleted = () => {
 loadMenuItems();
};

const handleItemEdited = (item: any) => {
 editingItem.value = item;
 showItemEditor.value = true;
};

const handleItemSaved = () => {
 loadMenuItems();
};

const handleItemToggleActive = async (itemId: number, active: boolean) => {
 try {
 await axios.put(`/api/v1/menus/${props.menu.id}/items/${itemId}`, {
 active: active
 });
 dialog.success(t('Menu item status updated'));
 loadMenuItems();
 } catch (error: any) {
 console.error('Error toggling menu item status:', error);
 dialog.error(t('Failed to update menu item status'));
 }
};

watch(() => props.menu, (newMenu) => {
 menuName.value = newMenu.name;
 selectedLocation.value = newMenu.location ||'';
 loadMenuItems();
}, { immediate: true });

onMounted(() => {
 loadMenuItems();
});
</script>

<style scoped>
.menu-editor-layout {
 display: flex;
 flex-direction: column;
 gap: 1.5rem;
 width: 100%;
}

.menu-editor-left {
 width: 100%;
}

.menu-editor-right {
 width: 100%;
}

@media (min-width: 1024px) {
 .menu-editor-layout {
 flex-direction: row;
 }

 .menu-editor-left {
 flex: 0 0 33.333333%;
 max-width: 33.333333%;
 min-width: 0;
 }

 .menu-editor-right {
 flex: 0 0 66.666667%;
 max-width: 66.666667%;
 min-width: 0;
 }
}
</style>
