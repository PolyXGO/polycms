<template>
    <div>
        <input
            v-if="editing"
            v-model="localText"
            @blur="handleBlur"
            @keyup.enter="handleBlur"
            class="w-full text-2xl font-bold border-none outline-none bg-transparent"
            :placeholder="`Heading ${attrs.level || 2}`"
        />
        <component
            :is="`h${attrs.level || 2}`"
            v-else
            @dblclick="editing = true"
            class="text-2xl font-bold cursor-pointer hover:bg-gray-100 rounded px-2 py-1"
        >
            {{ attrs.text || `Heading ${attrs.level || 2}` }}
        </component>
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
