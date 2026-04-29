<template>
    <div
        :class="[
            'menu-item',
            `level-${level}`,
            draggingId === item.id ? 'opacity-50 cursor-grabbing' : 'cursor-move',
        ]"
        draggable="true"
        @dragstart.stop="handleDragStart"
        @dragend="handleDragEnd"
        @dragover.stop.prevent="handleDragOver"
        @drop.stop.prevent="handleDrop"
    >
        <!-- Drop Zone: Before -->
        <div
            v-if="showDropZone && dragOverPosition === 'before'"
            class="drop-zone drop-zone-before"
            :style="{ marginLeft: `${level * 24}px` }"
        >
            <div class="drop-zone-content"></div>
        </div>

        <div
            ref="itemBar"
            :class="[
                'flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900/40 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-colors',
                dragOverId === item.id && dragOverPosition === 'inside' ? 'ring-2 ring-indigo-400 ring-offset-2 bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-600' : '',
            ]"
            :style="{ marginLeft: `${level * 24}px` }"
        >
            <!-- Drag Handle -->
            <div class="cursor-move text-gray-400 dark:text-gray-500 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                </svg>
            </div>

            <!-- Expand/Collapse -->
            <button
                v-if="hasChildren"
                @click="$emit('toggle-expand', item.id)"
                class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0"
            >
                <svg
                    class="w-4 h-4 transition-transform"
                    :class="{ 'rotate-90': isExpanded }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <div v-else class="w-4 flex-shrink-0"></div>

            <!-- Item Info -->
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ item.title }}
                </div>
                <div v-if="item.url" class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                    {{ item.url }}
                </div>
            </div>

            <!-- Page Type hidden as requested -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <!-- <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    {{ formatType(item.type) }}
                </span> -->

                <!-- Actions (Toggle, Edit, Delete) -->
                <div class="flex items-center gap-2">
                    <!-- Active Toggle -->
                    <FormToggle
                        :name="`active-${item.id}`"
                        :model-value="!!item.active"
                        @update:model-value="(val) => $emit('toggle-active', item.id, val)"
                        class="mr-2"
                    />

                    <button
                        @click="$emit('edit', item)"
                        class="p-1 text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400"
                        :title="$t('Edit')"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button
                        @click="$emit('delete', item.id)"
                        class="p-1 text-gray-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-400"
                        :title="$t('Delete')"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Drop Zone: Inside (when item has no children or children are collapsed) -->
        <div
            v-if="showDropZone && dragOverPosition === 'inside' && (!hasChildren || !isExpanded)"
            class="drop-zone drop-zone-inside mt-2"
            :style="{ marginLeft: `${(level + 1) * 24}px` }"
        >
            <div class="drop-zone-content drop-zone-inside-content">
                <span class="drop-zone-text">{{ $t('Drop here to make it a child') }}</span>
            </div>
        </div>

        <!-- Children -->
        <div v-if="hasChildren && isExpanded" class="mt-2 space-y-2">
            <!-- Drop Zone: Before first child (when dropping inside) -->
            <div
                v-if="showDropZone && dragOverPosition === 'inside' && item.children && item.children.length > 0"
                class="drop-zone drop-zone-before"
                :style="{ marginLeft: `${(level + 1) * 24}px` }"
            >
                <div class="drop-zone-content"></div>
            </div>

            <MenuItem
                v-for="(child, childIndex) in item.children"
                :key="`menu-item-child-${child.id}-${childIndex}`"
                :item="child"
                :level="level + 1"
                :index="typeof childIndex === 'number' ? childIndex : parseInt(String(childIndex), 10)"
                :dragging-id="draggingId"
                :drag-over-id="dragOverId"
                :drag-over-position="dragOverPosition"
                :expanded-items="expandedItems"
                @drag-start="(id) => $emit('drag-start', id)"
                @drag-end="$emit('drag-end')"
                @drag-over="(id, position) => $emit('drag-over', id, position)"
                @drop="(targetId, position) => $emit('drop', targetId, position)"
                @delete="(id) => $emit('delete', id)"
                @edit="(item) => $emit('edit', item)"
                @toggle-expand="(id) => $emit('toggle-expand', id)"
                @toggle-active="(id, val) => $emit('toggle-active', id, val)"
            />

            <!-- Drop Zone: After last child (when dropping inside) -->
            <div
                v-if="showDropZone && dragOverPosition === 'inside' && item.children && item.children.length > 0"
                class="drop-zone drop-zone-after"
                :style="{ marginLeft: `${(level + 1) * 24}px` }"
            >
                <div class="drop-zone-content"></div>
            </div>
        </div>

        <!-- Drop Zone: After -->
        <div
            v-if="showDropZone && dragOverPosition === 'after'"
            class="drop-zone drop-zone-after"
            :style="{ marginLeft: `${level * 24}px` }"
        >
            <div class="drop-zone-content"></div>
        </div>

        <!-- Drop Zone: Outdent (Visual feedback when dragging left) -->
        <div
            v-if="showDropZone && dragOverPosition === 'outdent' && level > 0"
            class="drop-zone drop-zone-outdent mt-1"
            :style="{ marginLeft: `${(level - 1) * 24}px` }"
        >
            <div class="drop-zone-line"></div>
            <div class="absolute -top-5 left-2 px-2 py-0.5 bg-indigo-500 text-[10px] text-white rounded shadow-sm whitespace-nowrap z-30">
                {{ $t('Outdent to parent level') }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, getCurrentInstance } from 'vue';
import { useTranslation } from '../../composables/useTranslation';
import FormToggle from '../../components/forms/FormToggle.vue';

const props = defineProps<{
    item: any;
    level: number;
    index: number;
    draggingId: number | null;
    dragOverId?: number | null;
    dragOverPosition?: string | null;
    expandedItems?: Set<number>;
}>();

const emit = defineEmits<{
    (e: 'drag-start', itemId: number): void;
    (e: 'drag-end'): void;
    (e: 'drag-over', itemId: number, position: string): void;
    (e: 'drop', targetId: number, position: string): void;
    (e: 'delete', itemId: number): void;
    (e: 'edit', item: any): void;
    (e: 'toggle-expand', itemId: number): void;
    (e: 'toggle-active', itemId: number, active: boolean): void;
}>();

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const hasChildren = computed(() => {
    return props.item.children && props.item.children.length > 0;
});

const isExpanded = computed(() => {
    return props.expandedItems?.has(props.item.id) ?? true;
});

const dragOverId = computed(() => props.dragOverId);
const dragOverPosition = computed(() => props.dragOverPosition);

const showDropZone = computed(() => {
    return dragOverId.value === props.item.id && dragOverPosition.value;
});

const formatType = (type: string): string => {
    if (!type) return 'Custom';
    const typeMap: Record<string, string> = {
        'custom': 'Custom Link',
        'post': 'Post',
        'page': 'Page',
        'category': 'Category',
        'product': 'Product',
        'tag': 'Tag',
    };
    return typeMap[type] || type.charAt(0).toUpperCase() + type.slice(1);
};

const handleDragStart = (e: DragEvent) => {
    if (!e.dataTransfer) return;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', props.item.id.toString());
    emit('drag-start', props.item.id);
};

const handleDragEnd = () => {
    emit('drag-end');
};

const itemBar = ref<HTMLElement | null>(null);

const handleDragOver = (e: DragEvent) => {
    if (!e.dataTransfer || !itemBar.value) return;
    e.dataTransfer.dropEffect = 'move';

    // Stop propagation to prevent parent handlers from interfering
    e.stopPropagation();

    const rect = itemBar.value.getBoundingClientRect();
    const y = e.clientY - rect.top;
    const height = rect.height;
    const x = e.clientX - rect.left;
    const width = rect.width;

    // Determine drop position based on mouse position relative to the ITEM BAR only
    let position: 'before' | 'after' | 'inside';

    // X-axis logic: Outdent if dragging significantly to the left of the item
    // This allows child items to be pulled out to the parent's level
    const outdentThreshold = -12; // 12px to the left of the item bar
    
    if (x < outdentThreshold && props.level > 0) {
        // Dragging far left - signal intent to outdent
        position = 'outdent' as any;
    } else if (x > width * 0.4) {
        // Dragging to the right side (40% threshold) = make it a child (inside)
        position = 'inside';
    } else if (y < height / 3) {
        // Top third = before
        position = 'before';
    } else if (y > (height * 2) / 3) {
        // Bottom third = after
        position = 'after';
    } else {
        // Middle area = inside
        position = 'inside';
    }

    emit('drag-over', props.item.id, position);
};

const handleDrop = (e: DragEvent) => {
    if (!e.dataTransfer) return;

    // Stop propagation to prevent parent handlers from interfering
    e.stopPropagation();

    const draggedId = parseInt(e.dataTransfer.getData('text/plain'));

    if (draggedId === props.item.id) {
        return;
    }

    // Use the position that was determined during dragOver
    // This ensures consistency between visual feedback and actual drop
    const position = dragOverPosition.value || 'inside';

    emit('drop', props.item.id, position);
};
</script>

<style scoped>
.menu-item {
    transition: all 0.2s ease-in-out;
}

.menu-item[draggable="true"]:hover {
    opacity: 0.9;
}

.menu-item.dragging {
    opacity: 0.5;
}

/* Drop Zone Styles */
.drop-zone {
    margin: 4px 0;
    position: relative;
    z-index: 10;
    transition: all 0.2s ease;
}

.drop-zone-content {
    height: 2px;
    background-color: #3b82f6;
    border-radius: 2px;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.4);
}

.drop-zone-inside {
    margin-top: 8px;
    margin-bottom: 8px;
}

.drop-zone-inside .drop-zone-content {
    height: 40px;
    background-color: rgba(59, 130, 246, 0.1);
    border: 2px dashed #3b82f6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.drop-zone-outdent {
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

.drop-zone-text {
    font-size: 12px;
    color: #3b82f6;
    font-weight: 500;
}
</style>
