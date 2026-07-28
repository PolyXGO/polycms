<template>
    <div class="tree-item-container select-none relative">
        <!-- Item content -->
        <div 
            class="tree-item-content flex items-center py-1 px-2 rounded-md transition-colors cursor-pointer group mb-0.5 relative z-10"
            :class="[
                isActive ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
            ]"
            :style="{ marginLeft: (depth * 20) + 'px' }"
            @click="handleClick"
        >
            <!-- Horizontal connector -->
            <div v-if="depth > 0" class="absolute left-[-12px] top-1/2 w-3 h-px bg-gray-200 dark:bg-gray-700 pointer-events-none"></div>

            <!-- Toggle Icon (+/-) -->
            <div 
                v-if="hasChildrenToggle"
                class="tree-toggle w-3.5 h-3.5 flex items-center justify-center mr-2 rounded-sm border border-gray-300 dark:border-gray-600 group-hover:border-indigo-400 transition-colors bg-white dark:bg-gray-900 shrink-0"
                @click.stop="toggle"
            >
                <div class="relative w-1.5 h-1.5 pointer-events-none">
                    <div class="absolute inset-x-0 top-[calc(50%-0.5px)] h-[1px] bg-current"></div>
                    <div v-if="!expanded" class="absolute inset-y-0 left-[calc(50%-0.5px)] w-[1px] bg-current"></div>
                </div>
            </div>
            <div v-else class="w-3.5 mr-2"></div>

            <!-- Icon Slot / Default Icon -->
            <slot name="icon" :item="item" :expanded="expanded">
                <template v-if="item.type === 'directory' || hasChildrenToggle">
                    <!-- Folder Open Icon -->
                    <svg v-if="expanded" class="w-4 h-4 mr-2 shrink-0" :style="{ color: iconColor }" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1H8a3 3 0 00-3 3v3h10a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                        <path d="M6 12a2 2 0 012-2h8a2 2 0 012 2v2a2 2 0 01-2 2H8a2 2 0 01-2-2v-2z" />
                    </svg>
                    <!-- Folder Closed Icon -->
                    <svg v-else class="w-4 h-4 mr-2 shrink-0" :style="{ color: iconColor }" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                    </svg>
                </template>
                <template v-else>
                    <svg class="w-4 h-4 mr-2 shrink-0 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                    </svg>
                </template>
            </slot>

            <span class="text-[11px] truncate leading-none uppercase tracking-tight" :title="item.name">{{ item.name }}</span>
            
            <div v-if="loading" class="ml-auto">
                <div class="w-3 h-3 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>

        <!-- Recursive Children -->
        <div v-if="expanded" class="relative">
            <!-- Vertical Line -->
            <div 
                v-if="children.length > 0"
                class="absolute left-[calc(var(--line-pos)*20px+7px)] top-[-4px] w-px h-[calc(100%-14px)] bg-gray-200 dark:bg-gray-700 pointer-events-none" 
                :style="{'--line-pos': depth}"
            ></div>
            
            <TreeItem 
                v-for="child in children" 
                :key="child.id || child.path || child.name"
                :item="child"
                :depth="depth + 1"
                :activePath="activePath"
                :fetchChildren="fetchChildren"
                :hasChildrenToggle="child.type === 'directory' || child.hasChildren"
                @navigate="$emit('navigate', $event)"
                @select="$emit('select', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, inject, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    depth: {
        type: Number,
        default: 0
    },
    activePath: {
        type: String,
        default: ''
    },
    fetchChildren: {
        type: Function,
        default: null
    },
    hasChildrenToggle: {
        type: Boolean,
        default: true
    },
    folderColor: {
        type: String,
        default: '#fbbf24' // Yellow-400 equivalent
    },
    activeFolderColor: {
        type: String,
        default: '#3b82f6' // Blue-500 equivalent
    }
});

const emit = defineEmits(['navigate', 'select']);

// Inject registration functions from parent
const registerNode = inject('registerNode', null);
const unregisterNode = inject('unregisterNode', null);

const expanded = ref(false);
const children = ref(props.item.children || []);
const loading = ref(false);

const isActive = computed(() => {
    const itemPath = props.item.id || props.item.path;
    const realId = props.item.real_id || props.item.id;
    return props.activePath === itemPath || props.activePath === realId || props.activePath === props.item.name;
});

const iconColor = computed(() => {
    if (props.item.iconColor) return props.item.iconColor;
    if (isActive.value) return props.activeFolderColor;
    return props.folderColor;
});

const reload = async () => {
    loading.value = true;
    try {
        const result = await props.fetchChildren(props.item);
        children.value = result || [];
        expanded.value = true; // Ensure it stays open after reload
    } catch (e) {
        console.error('TreeItem: Failed to reload children', e);
    } finally {
        loading.value = false;
    }
};

const toggle = async () => {
    expanded.value = !expanded.value;
    if (expanded.value && props.fetchChildren && children.value.length === 0) {
        loading.value = true;
        try {
            const result = await props.fetchChildren(props.item);
            children.value = result || [];
        } catch (e) {
            console.error('TreeItem: Failed to fetch children', e);
        } finally {
            loading.value = false;
        }
    }
};

const handleClick = () => {
    const itemPath = props.item.id || props.item.path;
    emit('navigate', itemPath);
    emit('select', props.item);
    if (props.hasChildrenToggle && !expanded.value) {
        toggle();
    }
};

// Auto-expand logic
watch(() => props.activePath, (newPath) => {
    const itemPath = props.item.id || props.item.path;
    const realId = props.item.real_id || props.item.id;
    
    if (!newPath) return;

    // Logic 1: Recursive Breadcrumb Expansion (root|id1|id2)
    if (newPath.includes('|') && itemPath && newPath.startsWith(itemPath + '|') && !expanded.value) {
        toggle();
    } 
    // Logic 2: Legacy ID/Name Support - Auto expand root if activePath exists
    else if (!newPath.includes('|') && itemPath === 'root' && !expanded.value) {
        // If we have a legacy path saved, at least expand the root to show top-level folders
        toggle();
    }
}, { immediate: true });

// Lifecycle hooks to register this node
onMounted(() => {
    if (registerNode) {
        const nodeId = props.item.id || props.item.path || 'root';
        registerNode(nodeId, { reload });
    }
});

onUnmounted(() => {
    if (unregisterNode) {
        const nodeId = props.item.id || props.item.path || 'root';
        unregisterNode(nodeId);
    }
});
</script>
