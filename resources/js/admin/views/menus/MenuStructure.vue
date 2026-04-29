<template>
    <div class="flex flex-col h-full w-full min-w-0">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('Menu structure') }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ $t('Drag the items into the order you prefer. Click the arrow on the right of the item to reveal more configuration options.') }}
            </p>
        </div>

        <div
            class="flex-1 overflow-y-auto p-6 min-h-0"
            @dragover.prevent="handleRootDragOver"
            @drop.prevent="handleRootDrop"
        >
            <div v-if="!items || items.length === 0" class="space-y-2">
                <!-- Root Drop Zone: Empty State -->
                <div
                    v-if="draggingId && dragOverItemId === null && dragOverPosition === 'root'"
                    class="drop-zone drop-zone-root"
                >
                    <div class="drop-zone-content drop-zone-inside-content">
                        <span class="drop-zone-text">{{ $t('Drop here to add as root item') }}</span>
                    </div>
                </div>
                <div v-else class="text-center py-12 text-sm text-gray-500 dark:text-gray-400">
                    {{ $t('No menu items yet. Add items from the left panel.') }}
                </div>
            </div>
            <div v-else class="space-y-2">
                <!-- Root Drop Zone: Before first item -->
                <div
                    v-if="draggingId && dragOverItemId === null && dragOverPosition === 'before-first'"
                    class="drop-zone drop-zone-before"
                >
                    <div class="drop-zone-content"></div>
                </div>

                <MenuItem
                    v-for="(item, index) in items"
                    :key="`menu-item-${item.id}-${index}`"
                    :item="item"
                    :level="0"
                    :index="index"
                    :dragging-id="draggingId"
                    :drag-over-id="dragOverItemId"
                    :drag-over-position="dragOverPosition"
                    :expanded-items="expandedItems"
                    @drag-start="handleDragStart"
                    @drag-end="handleDragEnd"
                    @drag-over="handleDragOver"
                    @drop="(targetId, pos) => handleDrop(targetId, pos as any)"
                    @delete="handleDelete"
                    @edit="handleEdit"
                    @toggle-expand="toggleExpand"
                    @toggle-active="handleToggleActive"
                />

                <!-- Root Drop Zone: Outdent indicator or After last item -->
                <div
                    v-if="draggingId && (
                        (dragOverItemId === null && dragOverPosition === 'after-last') || 
                        (dragOverPosition === 'outdent')
                    )"
                    class="drop-zone drop-zone-root-level mt-2"
                >
                    <div class="drop-zone-line"></div>
                </div>
            </div>

            <!-- Botom Safe Area for Root Drop -->
            <div 
                v-if="draggingId"
                class="h-24 mt-4 mb-12 border-2 border-dashed border-gray-200 dark:border-gray-700/50 rounded-xl flex items-center justify-center transition-all bg-gray-50/30 dark:bg-gray-900/10 hover:border-indigo-400 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 group overflow-hidden"
                @dragover.prevent="dragOverPosition = 'after-last'; dragOverItemId = null;"
                @drop.stop.prevent="handleRootDrop"
            >
                <div class="flex flex-col items-center gap-1 opacity-40 group-hover:opacity-100 transition-opacity pointer-events-none">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 group-hover:text-indigo-500">{{ $t('Drop here to move to end of root') }}</span>
                    <ArrowDownIcon class="w-5 h-5 text-gray-400 group-hover:text-indigo-400" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Improved Drop Zone Styles */
.drop-zone-root-level {
    position: relative;
    height: 4px;
    z-index: 20;
}

.drop-zone-line {
    position: absolute;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #3b82f6;
    border-radius: 2px;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.4);
}

.drop-zone-line::before {
    content: '';
    position: absolute;
    left: -4px;
    top: -3px;
    width: 8px;
    height: 8px;
    background-color: #3b82f6;
    border-radius: 50%;
}

.drop-zone-root {
    margin: 8px 0;
    position: relative;
    z-index: 10;
}

.drop-zone-root .drop-zone-content {
    height: 60px;
    background-color: rgba(59, 130, 246, 0.1);
    border: 2px dashed #3b82f6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.drop-zone-inside-content {
    height: 40px;
    background-color: rgba(59, 130, 246, 0.1);
    border: 2px dashed #3b82f6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.drop-zone-text {
    font-size: 12px;
    color: #3b82f6;
    font-weight: 500;
}

.drop-zone-before,
.drop-zone-after {
    margin: 4px 0;
    position: relative;
    z-index: 10;
}

.drop-zone-before .drop-zone-content,
.drop-zone-after .drop-zone-content {
    height: 2px;
    background-color: #3b82f6;
    border: 2px dashed #3b82f6;
    border-radius: 2px;
    margin: 0;
}
</style>

<script setup lang="ts">
import { ref, watch, getCurrentInstance, computed } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import MenuItem from './MenuItem.vue';
import { ArrowDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    menuId: number;
    items: any[];
    customApi?: boolean;
    apiBaseUrl?: string;
}>();

const emit = defineEmits<{
    (e: 'item-deleted'): void;
    (e: 'item-edited', item: any): void;
    (e: 'toggle-active', itemId: number, active: boolean): void;
}>();

const { t } = useTranslation();
const dialog = useDialog();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const baseApiUrl = computed(() => {
    if (props.customApi && props.apiBaseUrl) {
        return props.apiBaseUrl;
    }
    return `/api/v1/menus/${props.menuId}/items`;
});

const draggingId = ref<number | null>(null);
const dragOverItemId = ref<number | null>(null);
const dragOverPosition = ref<string | null>(null);
const expandedItems = ref<Set<number>>(new Set());

const handleDragStart = (itemId: number) => {
    draggingId.value = itemId;
};

const handleDragEnd = () => {
    draggingId.value = null;
    dragOverItemId.value = null;
    dragOverPosition.value = null;
};

const handleDragOver = (itemId: number, position: string) => {
    dragOverItemId.value = itemId;
    dragOverPosition.value = position;
};

const handleRootDragOver = (e: DragEvent) => {
    if (!draggingId.value || !e.dataTransfer) return;
    e.dataTransfer.dropEffect = 'move';

    // Check if dragging over empty area or between items
    const container = e.currentTarget as HTMLElement;
    const rect = container.getBoundingClientRect();
    const y = e.clientY - rect.top;
    const scrollTop = container.scrollTop;
    const relativeY = y + scrollTop;

    // If no items, show root drop zone
    if (!props.items || props.items.length === 0) {
        dragOverItemId.value = null;
        dragOverPosition.value = 'root';
        return;
    }

    // Check if near top (before first item)
    const firstItem = container.querySelector('.menu-item');
    if (firstItem) {
        const firstRect = firstItem.getBoundingClientRect();
        const firstTop = firstRect.top - rect.top + scrollTop;
        if (relativeY < firstTop - 20) {
            dragOverItemId.value = null;
            dragOverPosition.value = 'before-first';
            return;
        }
    }

    // Check if near bottom (after last item)
    const lastItem = container.querySelector('.menu-item:last-child');
    if (lastItem) {
        const lastRect = lastItem.getBoundingClientRect();
        const lastBottom = lastRect.bottom - rect.top + scrollTop;
        if (relativeY > lastBottom + 20) {
            dragOverItemId.value = null;
            dragOverPosition.value = 'after-last';
            return;
        }
    }

    // If not near edges, let individual items handle it
    // Don't set root drop zone
};

const handleRootDrop = async (e: DragEvent) => {
    if (!draggingId.value) return;

    // Use draggingId.value directly as it's more reliable than dataTransfer
    const draggedId = draggingId.value;

    // Find dragged item
    const draggedInfo = findItem(props.items || [], draggedId);
    if (!draggedInfo) return;

    // Determine position and target
    let position: 'before' | 'after' | 'inside' = 'after';
    let targetItemId: number | null = null;

    if (dragOverPosition.value === 'root' || (props.items && props.items.length <= 1)) {
        // Empty list, explicit root zone, OR single item list (could be moving child back to itself as root)
        // await performRootMove(draggedId, draggedInfo); // Replaced by direct logic below
        // return;
        const withoutDragged = removeItem(props.items || [], draggedId);
        console.log(`Moving item ${draggedId} to root level. Remaining items:`, withoutDragged.length);

        const draggedWithChildren = {
            ...draggedInfo.item,
            parent_id: null,
            children: draggedInfo.item.children || []
        };

        // Add as first or last item depending on dragOverPosition
        let newItems: any[];
        if (dragOverPosition.value === 'after-last') { // This condition is for the root drop zone at the end
            newItems = [...withoutDragged, draggedWithChildren];
        } else { // Default to 'before-first' or 'root' (which implies first if no other items)
            newItems = [draggedWithChildren, ...withoutDragged];
        }

        const formattedItems = formatItemsForApi(newItems);

        try {
            await axios.put(`${baseApiUrl.value}/reorder`, {
                items: formattedItems,
            });

            const response = await axios.get(baseApiUrl.value);
            const reloadedItems = response.data?.data || [];

            emit('items-updated', Array.isArray(reloadedItems) ? reloadedItems : []);
            dialog.success(t('Menu items reordered'));
        } catch (error: any) {
            console.error('Error reordering items:', error);
            dialog.error(t('Failed to reorder menu items'));
        }

        // Reset drag state
        draggingId.value = null;
        dragOverItemId.value = null;
        dragOverPosition.value = null;
        return; // Important: return after handling root move
    } else if (dragOverPosition.value === 'after-last') {
        // Drop as last root item
        if (props.items && props.items.length > 0) {
            const lastItem = props.items[props.items.length - 1];
            targetItemId = lastItem.id;
            position = 'after';
        }
    } else if (dragOverPosition.value === 'before-first') {
        // Drop as first root item
        if (props.items && props.items.length > 0) {
            const firstItem = props.items[0];
            targetItemId = firstItem.id;
            position = 'before';
        }
    }

    if (targetItemId) {
        // Special case: if dropping on itself at root, it's still a move if it was a child
        await handleDrop(targetItemId, position, true);
    }

    // Reset drag state
    draggingId.value = null;
    dragOverItemId.value = null;
    dragOverPosition.value = null;
};

const handleDrop = async (targetItemId: number, position: 'before' | 'after' | 'inside' | 'outdent', forceMove: boolean = false) => {
    if (!draggingId.value) return;
    
    // Normal cases: don't move if dropping on itself unless it's an outdent or forced root move
    if (draggingId.value === targetItemId && position !== 'outdent' && !forceMove) {
        return;
    }

    console.log(`Dragging item ${draggingId.value} to target ${targetItemId} with position ${position}`);

    // Find dragged and target items with their parent info
    const draggedInfo = findItem(props.items, draggingId.value);
    let targetInfo = findItem(props.items, targetItemId);

    if (!draggedInfo || !targetInfo) {
        console.warn('Could not find dragged or target item', { draggingId: draggingId.value, targetItemId });
        return;
    }

    // Handle outdent gesture
    let finalPosition = position as 'before' | 'after' | 'inside';
    if (position === 'outdent') {
        if (targetInfo.parent) {
            // Find the parent of the target to make it a sibling of the parent
            const parentInfo = findItem(props.items, targetInfo.parent.id);
            if (parentInfo) {
                // Target becomes the parent, and we drop 'after' it
                targetItemId = targetInfo.parent.id;
                targetInfo = parentInfo;
                finalPosition = 'after';
                console.log(`Outdenting to become sibling of parent: ${targetItemId}`);
            }
        } else {
            // Already at root level, just drop 'after'
            finalPosition = 'after';
        }
    }

    // Double check if we are still effectively dropping on ourselves at the SAME level
    // This happens if we drag a child and drop it "after" itself at the same parent
    if (draggedInfo.item.id === targetInfo.item.id && finalPosition !== 'inside') {
        // If the parents are the same, it's a no-op
        const draggedParentId = draggedInfo.parent ? draggedInfo.parent.id : null;
        const targetParentId = targetInfo.parent ? targetInfo.parent.id : null;
        
        if (draggedParentId === targetParentId && !forceMove) {
            console.log('No-op: dropping on itself at the same level');
            return;
        }
    }

    // Only check circular reference when dropping inside (creating child relationship)
    if (finalPosition === 'inside') {
        // ... (circular reference check remains same)
        const findInDescendants = (targetId: number, children: any[]): boolean => {
            if (!children || children.length === 0) return false;
            for (const child of children) {
                if (child.id === targetId) return true;
                if (child.children && findInDescendants(targetId, child.children)) return true;
            }
            return false;
        };

        if (findInDescendants(targetItemId, draggedInfo.item.children || [])) {
            dialog.error(t('Cannot move item into its own child'));
            return;
        }
    }

    // Build new structure - pass full dragged item with children preserved
    const newItems = rebuildStructure(props.items, draggedInfo.item, targetInfo.item, finalPosition);

    // Convert to format expected by backend
    const formattedItems = formatItemsForApi(newItems);

    try {
        await axios.put(`${baseApiUrl.value}/reorder`, {
            items: formattedItems,
        });

        const response = await axios.get(baseApiUrl.value);
        const reloadedItems = response.data?.data || [];

        emit('items-updated', Array.isArray(reloadedItems) ? reloadedItems : []);
        dialog.success(t('Menu items reordered'));
    } catch (error: any) {
        console.error('Error reordering items:', error);
        dialog.error(t('Failed to reorder menu items'));
    }
};

const handleDelete = async (itemId: number) => {
    const confirmed = await dialog.confirm(t('Are you sure you want to delete this menu item?'));
    if (!confirmed) return;

    try {
        await axios.delete(`${baseApiUrl.value}/${itemId}`);
        emit('item-deleted');
        dialog.success(t('Menu item deleted'));
    } catch (error: any) {
        console.error('Error deleting item:', error);
        dialog.error(t('Failed to delete menu item'));
    }
};

const handleEdit = (item: any) => {
    emit('item-edited', item);
};
const handleToggleActive = (itemId: number, active: boolean) => {
    emit('toggle-active', itemId, active);
};

const toggleExpand = (itemId: number) => {
    if (expandedItems.value.has(itemId)) {
        expandedItems.value.delete(itemId);
    } else {
        expandedItems.value.add(itemId);
    }
};

const findItem = (items: any[], id: number, parent: any = null): { item: any; parent: any } | null => {
    for (const item of items) {
        if (item.id === id) {
            return { item, parent };
        }
        if (item.children && item.children.length > 0) {
            const found = findItem(item.children, id, item);
            if (found) return found;
        }
    }
    return null;
};

// Helper to find item without parent info (for backward compatibility)
const findItemSimple = (items: any[], id: number): any => {
    const result = findItem(items, id);
    return result ? result.item : null;
};

const rebuildStructure = (items: any[], dragged: any, target: any, position: string): any[] => {
    // 1. Remove dragged item from its current position first
    const withoutDragged = removeItem(items, dragged.id);

    // 2. Find target in the new structure (after removing dragged item)
    const targetInfo = findItem(withoutDragged, target.id);

    // 3. Determine new parent (Default to root if no target found or target is root)
    let targetParentId: number | null = null;
    
    if (targetInfo) {
        if (position === 'inside') {
            targetParentId = target.id;
        } else {
            targetParentId = targetInfo.parent ? targetInfo.parent.id : null;
        }
    }

    // 4. Create the item instance with NEW parent and preserved children
    const draggedWithChildren = {
        ...dragged,
        parent_id: targetParentId,
        children: dragged.children || []
    };

    if (!targetInfo) {
        // Target not found usually means we're dropping on ourselves in a way that left 0 items.
        // Fallback: Drop at root.
        console.log('Target not found after removal, moving to root fallback');
        return [draggedWithChildren];
    }

    console.log(`Rebuilding structure: moving ${dragged.id} ${position} ${target.id}. New Parent: ${targetParentId}`);

    // 5. Insert at new position
    const result = insertItem(withoutDragged, draggedWithChildren, targetInfo.item, position, targetParentId);
    return result;
};

const removeItem = (items: any[], id: number): any[] => {
    return items
        .filter(item => item.id !== id)
        .map(item => ({
            ...item,
            children: item.children ? removeItem(item.children, id) : [],
        }));
};

const insertItem = (items: any[], dragged: any, target: any, position: string, targetParentId: number | null = null): any[] => {
    const result: any[] = [];

    for (const item of items) {
        if (item.id === target.id) {
            if (position === 'before') {
                // Insert before target - same level as target
                // CRITICAL: Set parent_id explicitly, including null for root items
                // CRITICAL: Preserve all nested children recursively
                const draggedItem = {
                    ...dragged,
                    parent_id: targetParentId, // Can be null for root level
                    children: preserveChildrenStructure(dragged.children || [])
                };
                result.push(draggedItem);
                result.push({
                    ...item,
                    children: preserveChildrenStructure(item.children || [])
                });
            } else if (position === 'after') {
                // Insert after target - same level as target
                // CRITICAL: Set parent_id explicitly, including null for root items
                // CRITICAL: Preserve all nested children recursively
                const draggedItem = {
                    ...dragged,
                    parent_id: targetParentId, // Can be null for root level
                    children: preserveChildrenStructure(dragged.children || [])
                };
                result.push({
                    ...item,
                    children: preserveChildrenStructure(item.children || [])
                });
                result.push(draggedItem);
            } else if (position === 'inside') {
                // Insert inside target - becomes child of target
                // CRITICAL: Preserve all nested children recursively
                const draggedItem = {
                    ...dragged,
                    parent_id: item.id, // Parent is the target item
                    children: preserveChildrenStructure(dragged.children || [])
                };
                result.push({
                    ...item,
                    children: [...(item.children || []).map((child: any) => ({
                        ...child,
                        children: preserveChildrenStructure(child.children || [])
                    })), draggedItem],
                });
            }
        } else {
            // Recursively search in children
            // CRITICAL: Preserve all nested children structure
            result.push({
                ...item,
                children: item.children ? insertItem(item.children, dragged, target, position, targetParentId) : [],
            });
        }
    }

    return result;
};

// Helper to preserve nested children structure recursively
const preserveChildrenStructure = (children: any[]): any[] => {
    if (!children || children.length === 0) {
        return [];
    }
    return children.map((child: any) => ({
        ...child,
        children: preserveChildrenStructure(child.children || [])
    }));
};

const formatItemsForApi = (items: any[], parentId: number | null = null): any[] => {
    return items.map((item, index) => {
        // CRITICAL: Use item.parent_id if it exists (set by rebuildStructure/insertItem)
        // This ensures parent_id is correctly preserved when moving items
        // Check if parent_id property exists on the item (even if it's null)
        let itemParentId: number | null | undefined;
        if ('parent_id' in item) {
            // parent_id is explicitly set (can be null for root items)
            itemParentId = item.parent_id;
        } else {
            // parent_id not set, use parameter (for backward compatibility)
            itemParentId = parentId;
        }

        const formatted: any = {
            id: item.id,
            order: index,
        };

        // CRITICAL: Set parent_id explicitly - null is valid and must be sent to API
        // This allows moving child items back to root level
        // Always include parent_id in the formatted object, even if it's null
        if (itemParentId === null || itemParentId === undefined) {
            formatted.parent_id = null; // Explicitly null for root items
        } else {
            formatted.parent_id = itemParentId; // Number for child items
        }

        // Recursively format children (they will have parent_id = item.id)
        if (item.children && item.children.length > 0) {
            formatted.children = formatItemsForApi(item.children, item.id);
        }

        return formatted;
    });
};

watch(() => props.items, (newItems) => {
    // Auto-expand all items when items change
    const allIds = new Set<number>();
    const collectIds = (items: any[]) => {
        items.forEach(item => {
            allIds.add(item.id);
            if (item.children) collectIds(item.children);
        });
    };
    collectIds(newItems);
    expandedItems.value = allIds;
}, { immediate: true });
</script>
