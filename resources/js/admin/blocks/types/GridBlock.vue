<template>
    <div class="block-grid border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4">
        <div class="mb-2 flex items-center justify-between">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Grid: {{ attrs.columns || 2 }} columns
            </label>
            <select
                :value="localColumns"
                @change="(e) => { localColumns = Number((e.target as HTMLSelectElement).value); updateColumns(); }"
                class="text-sm border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
                <option :value="2">2 Columns</option>
                <option :value="3">3 Columns</option>
                <option :value="4">4 Columns</option>
            </select>
        </div>
        <div :style="{ display: 'grid', gridTemplateColumns: `repeat(${attrs.columns || 2}, minmax(0, 1fr))`, gap: '1rem' }">
            <div
                v-for="(column, colIndex) in columns"
                :key="`col-${colIndex}`"
                class="border border-gray-200 dark:border-gray-700 rounded p-2 min-h-[100px] bg-gray-50 dark:bg-gray-800/50"
            >
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Column {{ colIndex + 1 }}</div>
                <div class="space-y-2">
                    <div
                        v-for="(block, blockIndex) in column.blocks"
                        :key="block.id || blockIndex"
                        class="relative group"
                    >
                        <component
                            :is="getBlockComponent(block.type)"
                            :block="block"
                            :selected="selectedBlockId === block.id"
                            @update:attrs="updateBlockAttrs(colIndex, blockIndex, $event)"
                        />
                    </div>
                    <button
                        type="button"
                        @click="showBlockPicker(colIndex)"
                        class="w-full px-3 py-2 border border-dashed border-gray-300 dark:border-gray-600 rounded text-gray-500 dark:text-gray-400 hover:border-indigo-500 dark:hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm"
                    >
                        + Add Block
                    </button>
                </div>
            </div>
        </div>
        <!-- Block Picker Modal for Column -->
        <div
            v-if="pickerColumn !== null"
            @click.self="pickerColumn = null"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full m-4 max-h-[80vh] overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Block to Column {{ pickerColumn + 1 }}</h3>
                    <button type="button" @click="pickerColumn = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <button
                            type="button"
                            v-for="blockType in availableBlockTypes"
                            :key="blockType.type"
                            @click="addBlockToColumn(pickerColumn, blockType.type)"
                            class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors text-left"
                        >
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ blockType.label }}</div>
                            <div v-if="blockType.category" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ blockType.category }}</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { blockRegistry } from '@/admin/core/BlockRegistry';
import { generateId } from '@/admin/utils';

const props = defineProps<{
    block: any;
    selected: boolean;
}>();

const emit = defineEmits<{
    'update:attrs': [attrs: Record<string, any>];
}>();

const attrs = computed(() => {
    // Ensure attrs always has default values
    const blockAttrs = props.block?.attrs || {};
    return {
        columns: blockAttrs.columns ?? 2,
        blocks: blockAttrs.blocks ?? [],
        ...blockAttrs,
    };
});

const localColumns = ref(attrs.value.columns || 2);
const pickerColumn = ref<number | null>(null);

// Initialize attrs if not present
watch(() => props.block, (newBlock) => {
    if (newBlock && (!newBlock.attrs || newBlock.attrs.columns === undefined)) {
        emit('update:attrs', {
            columns: 2,
            blocks: [],
        });
    }
}, { immediate: true });

// Sync localColumns when attrs.columns changes from outside
watch(() => attrs.value.columns, (newColumns) => {
    if (newColumns !== undefined && newColumns !== localColumns.value) {
        localColumns.value = newColumns;
    }
});

const columns = computed(() => {
    // Use .value to access computed property
    const cols = attrs.value.columns || 2;
    const blocks = attrs.value.blocks || [];
    
    // Always create exactly 'cols' number of columns
    const result: Array<{ blocks: any[] }> = [];
    for (let i = 0; i < cols; i++) {
        result.push({ blocks: [] });
    }
    
    // Distribute blocks to columns (round-robin)
    blocks.forEach((block: any, index: number) => {
        const colIndex = index % cols;
        if (result[colIndex]) {
            result[colIndex].blocks.push(block);
        }
    });
    
    return result;
});

const availableBlockTypes = computed(() => blockRegistry.getAll());

const getBlockComponent = (type: string) => {
    const config = blockRegistry.get(type);
    return config?.component || null;
};

const updateColumns = () => {
    const newColumns = localColumns.value;
    
    // Get all blocks from current distribution
    const allBlocks = [...(attrs.value.blocks || [])];
    
    // Redistribute blocks to new columns (round-robin)
    const redistributedBlocks: any[] = [];
    const tempColumns: any[][] = [];
    for (let i = 0; i < newColumns; i++) {
        tempColumns.push([]);
    }
    
    // Distribute existing blocks to new column structure
    allBlocks.forEach((block: any, index: number) => {
        const colIndex = index % newColumns;
        tempColumns[colIndex].push(block);
    });
    
    // Flatten back to array (round-robin order)
    const columnLengths = tempColumns.map(col => col.length);
    const maxLength = columnLengths.length > 0 ? Math.max(...columnLengths) : 0;
    for (let i = 0; i < maxLength; i++) {
        for (let colIndex = 0; colIndex < newColumns; colIndex++) {
            if (tempColumns[colIndex][i]) {
                redistributedBlocks.push(tempColumns[colIndex][i]);
            }
        }
    }
    
    // Emit only the changed attributes
    emit('update:attrs', {
        columns: newColumns,
        blocks: redistributedBlocks,
    });
};

const showBlockPicker = (colIndex: number) => {
    pickerColumn.value = colIndex;
};

const addBlockToColumn = (colIndex: number, blockType: string) => {
    console.log('[GridBlock] Adding block to column:', colIndex, 'blockType:', blockType);
    
    const newBlock = blockRegistry.createBlock(blockType);
    newBlock.id = generateId();
    
    const allBlocks = [...(attrs.value.blocks || [])];
    const cols = attrs.value.columns || 2;
    
    console.log('[GridBlock] Current state:', {
        totalBlocks: allBlocks.length,
        columns: cols,
        targetColumn: colIndex
    });
    
    // Strategy: Calculate exact insert position based on round-robin distribution
    // Position i in array maps to column (i % cols)
    // Column colIndex has blocks at positions: colIndex, colIndex+cols, colIndex+2*cols, ...
    
    // Step 1: Count how many blocks are currently in column colIndex
    let blocksInTargetColumn = 0;
    for (let i = 0; i < allBlocks.length; i++) {
        if (i % cols === colIndex) {
            blocksInTargetColumn++;
        }
    }
    
    console.log('[GridBlock] Blocks in target column', colIndex, ':', blocksInTargetColumn);
    
    // Step 2: Calculate insert position
    // Column colIndex has blocks at: colIndex, colIndex+cols, colIndex+2*cols, ...
    // The new block should be at: colIndex + blocksInTargetColumn * cols
    let insertIndex = colIndex + blocksInTargetColumn * cols;
    
    console.log('[GridBlock] Calculated insertIndex:', insertIndex, 'array.length:', allBlocks.length);
    
    // Handle edge case: if insertIndex is beyond array length
    if (insertIndex > allBlocks.length) {
        // We need to append, but ensure it maps to colIndex
        // Check what column the current end of array maps to
        const currentRemainder = allBlocks.length % cols;
        
        if (currentRemainder === colIndex) {
            // Perfect! Can append directly - the next position will be colIndex
            insertIndex = allBlocks.length;
        } else {
            // The current end maps to a different column
            // We need to insert "placeholder" blocks (or skip) to reach colIndex
            // But actually, we can't insert gaps, so we must append at the end
            // and then the round-robin will naturally place it in the next cycle
            // However, this means we need to insert at the correct position in the cycle
            // Let's calculate: if we're at position allBlocks.length, what's the next position that maps to colIndex?
            
            // Position allBlocks.length maps to column (allBlocks.length % cols)
            // We want position that maps to colIndex
            // The next position that maps to colIndex is:
            // allBlocks.length + offset, where offset makes (allBlocks.length + offset) % cols === colIndex
            
            let offset = 0;
            if (currentRemainder < colIndex) {
                offset = colIndex - currentRemainder;
            } else {
                // currentRemainder > colIndex, need to wrap around
                offset = cols - currentRemainder + colIndex;
            }
            
            // But we can't insert gaps, so we need to insert at allBlocks.length
            // and then shift to make room, OR we can just append and accept that
            // the block will be in the next cycle at the correct column
            
            // Actually, the simplest approach: append at the end, and the block will be
            // at position allBlocks.length, which maps to currentRemainder
            // If currentRemainder !== colIndex, we need to insert blocks in between
            // But we don't have blocks to insert, so we can't do that.
            
            // The correct solution: calculate the actual position where we should insert
            // to maintain round-robin, even if it means the array will have the block
            // at the end initially, then we verify and fix if needed
            
            insertIndex = allBlocks.length;
            console.log('[GridBlock] Warning: insertIndex calculated as', colIndex + blocksInTargetColumn * cols, 
                       'but array.length is', allBlocks.length, 
                       '- will append at end (column', currentRemainder, ')');
        }
    }
    
    // Step 3: Insert the block
    // If insertIndex > array.length, splice will append at the end
    // But we need to verify it ends up in the correct column
    allBlocks.splice(insertIndex, 0, newBlock);
    
    // Verify: Check if block is in the correct column
    const actualIndex = allBlocks.findIndex(b => b.id === newBlock.id);
    const actualColumn = actualIndex >= 0 ? actualIndex % cols : -1;
    
    console.log('[GridBlock] Block inserted at index:', actualIndex, 'maps to column:', actualColumn, 'expected:', colIndex);
    
    if (actualColumn !== colIndex) {
        console.error(`[GridBlock] ERROR: Block inserted in wrong column! Fixing...`);
        
        // Fix: Remove from wrong position and insert at correct position
        allBlocks.splice(actualIndex, 1);
        
        // Calculate correct position: colIndex + blocksInTargetColumn * cols
        // But we need to account for the fact that we've already added the block
        // So we need to count blocks in target column including the one we just removed
        let correctIndex = colIndex + blocksInTargetColumn * cols;
        
        // If still beyond array length, append at correct position
        if (correctIndex > allBlocks.length) {
            // Find the next position that maps to colIndex
            const currentRemainder = allBlocks.length % cols;
            if (currentRemainder === colIndex) {
                correctIndex = allBlocks.length;
            } else {
                let offset = 0;
                if (currentRemainder < colIndex) {
                    offset = colIndex - currentRemainder;
                } else {
                    offset = cols - currentRemainder + colIndex;
                }
                correctIndex = allBlocks.length + offset;
            }
        }
        
        // Insert at correct position
        allBlocks.splice(correctIndex, 0, newBlock);
        
        // Verify again
        const finalIndex = allBlocks.findIndex(b => b.id === newBlock.id);
        const finalColumn = finalIndex >= 0 ? finalIndex % cols : -1;
        console.log('[GridBlock] After fix: block at index', finalIndex, 'maps to column', finalColumn);
        
        if (finalColumn !== colIndex) {
            console.error(`[GridBlock] CRITICAL: Still in wrong column after fix!`);
        }
    }
    
    // Emit only the changed attribute (blocks)
    emit('update:attrs', {
        blocks: allBlocks,
    });
    pickerColumn.value = null;
};

const updateBlockAttrs = (colIndex: number, blockIndex: number, newAttrs: Record<string, any>) => {
    const allBlocks = [...(attrs.value.blocks || [])];
    const cols = attrs.value.columns || 2;
    
    // Find the actual block index in the flat array
    // Blocks are distributed round-robin, so block at colIndex, blockIndex
    // is at position: blockIndex * cols + colIndex in the original distribution
    // But we need to find it in the current array
    
    // Build column structure to find the block
    const tempColumns: any[][] = [];
    for (let i = 0; i < cols; i++) {
        tempColumns.push([]);
    }
    allBlocks.forEach((block: any, index: number) => {
        const cIndex = index % cols;
        tempColumns[cIndex].push({ block, originalIndex: index });
    });
    
    // Find the block in the specific column
    const targetBlock = tempColumns[colIndex][blockIndex];
    if (!targetBlock) return;
    
    const actualIndex = targetBlock.originalIndex;
    
    allBlocks[actualIndex] = {
        ...allBlocks[actualIndex],
        attrs: {
            ...allBlocks[actualIndex].attrs,
            ...newAttrs,
        },
    };
    
    // Emit only the changed attribute (blocks)
    emit('update:attrs', {
        blocks: allBlocks,
    });
};

const selectedBlockId = ref<string | null>(null);
</script>

