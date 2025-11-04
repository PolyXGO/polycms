<template>
    <div class="block-editor">
        <div class="space-y-2">
            <div
                v-for="(block, index) in blocks"
                :key="block.id || index"
                :class="[
                    'relative group border-2 rounded-lg p-4 transition-all',
                    selectedBlockId === block.id
                        ? 'border-indigo-500 bg-indigo-50'
                        : 'border-transparent hover:border-gray-300'
                ]"
                @click="selectBlock(block.id)"
            >
                <BlockToolbar
                    v-if="selectedBlockId === block.id"
                    :can-move-up="index > 0"
                    :can-move-down="index < blocks.length - 1"
                    @move-up="moveBlock(index, index - 1)"
                    @move-down="moveBlock(index, index + 1)"
                    @duplicate="duplicateBlock(index)"
                    @delete="deleteBlock(index)"
                />
                <component
                    :is="getBlockComponent(block.type)"
                    :block="block"
                    :selected="selectedBlockId === block.id"
                    @update:attrs="updateBlockAttrs(index, $event)"
                />
            </div>
        </div>
        <div class="mt-4">
            <button
                @click="showBlockPicker = true"
                class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-indigo-500 hover:text-indigo-600 transition-colors"
            >
                + Add Block
            </button>
        </div>
        <div
            v-if="showBlockPicker"
            @click.self="showBlockPicker = false"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full m-4 max-h-[80vh] overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Add Block</h3>
                    <button @click="showBlockPicker = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <button
                            v-for="blockType in availableBlockTypes"
                            :key="blockType.type"
                            @click="addBlock(blockType.type)"
                            class="p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors text-left"
                        >
                            <div class="text-sm font-medium text-gray-900">{{ blockType.label }}</div>
                            <div v-if="blockType.category" class="text-xs text-gray-500 mt-1">{{ blockType.category }}</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { blockRegistry } from '@/admin/core/BlockRegistry';
import type { Block } from '@/admin/blocks/types';
import BlockToolbar from './BlockToolbar.vue';
import { generateId } from '@/admin/utils';
import { registerCoreBlocks } from './registerBlocks';

// Register core blocks on load
registerCoreBlocks();

const props = defineProps<{
    modelValue: Block[];
}>();

const emit = defineEmits<{
    'update:modelValue': [blocks: Block[]];
}>();

const blocks = computed({
    get: () => props.modelValue.map(block => ({
        ...block,
        id: block.id || generateId(),
    })),
    set: (value) => emit('update:modelValue', value),
});

const selectedBlockId = ref<string | null>(null);
const showBlockPicker = ref(false);

const availableBlockTypes = computed(() => blockRegistry.getAll());

const getBlockComponent = (type: string) => {
    const config = blockRegistry.get(type);
    return config?.component || null;
};

const selectBlock = (blockId: string | null) => {
    selectedBlockId.value = blockId;
};

const addBlock = (type: string) => {
    const newBlock = blockRegistry.createBlock(type);
    newBlock.id = generateId();
    blocks.value = [...blocks.value, newBlock];
    selectedBlockId.value = newBlock.id;
    showBlockPicker.value = false;
};

const deleteBlock = (index: number) => {
    blocks.value = blocks.value.filter((_, i) => i !== index);
    selectedBlockId.value = null;
};

const duplicateBlock = (index: number) => {
    const block = blocks.value[index];
    const duplicated = { ...block, id: generateId() };
    blocks.value = [
        ...blocks.value.slice(0, index + 1),
        duplicated,
        ...blocks.value.slice(index + 1),
    ];
    selectedBlockId.value = duplicated.id;
};

const moveBlock = (fromIndex: number, toIndex: number) => {
    const newBlocks = [...blocks.value];
    const [removed] = newBlocks.splice(fromIndex, 1);
    newBlocks.splice(toIndex, 0, removed);
    blocks.value = newBlocks;
    selectedBlockId.value = removed.id;
};

const updateBlockAttrs = (index: number, attrs: Record<string, any>) => {
    const newBlocks = [...blocks.value];
    newBlocks[index] = {
        ...newBlocks[index],
        attrs: { ...newBlocks[index].attrs, ...attrs },
    };
    blocks.value = newBlocks;
};

onMounted(() => {
    document.addEventListener('click', (e) => {
        const target = e.target as HTMLElement;
        if (!target.closest('.block-editor')) {
            selectedBlockId.value = null;
        }
    });
});
</script>
