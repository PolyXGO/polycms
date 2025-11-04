<template>
    <div>
        <textarea
            v-if="editing"
            v-model="localText"
            @blur="handleBlur"
            class="w-full min-h-[100px] border-none outline-none bg-transparent resize-none"
            placeholder="Enter paragraph text..."
        />
        <p
            v-else
            @dblclick="editing = true"
            class="cursor-pointer hover:bg-gray-100 rounded px-2 py-1 whitespace-pre-wrap"
        >
            {{ attrs.text || 'Double-click to edit paragraph...' }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    block: any;
    selected: boolean;
}>();

const emit = defineEmits<{
    'update:attrs': [attrs: Record<string, any>];
}>();

const attrs = props.block.attrs || {};
const editing = ref(false);
const localText = ref(attrs.text || '');

const handleBlur = () => {
    editing.value = false;
    emit('update:attrs', { text: localText.value });
};

watch(() => props.selected, (selected) => {
    if (!selected) {
        editing.value = false;
    }
});
</script>
