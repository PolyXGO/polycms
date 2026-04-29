<template>
    <TagSelector type="post" v-model="selectedTags" placeholder="Add new tag" @update:modelValue="emitUpdate" />
</template>

<script setup lang="ts">
import { inject } from 'vue';
import TagSelector from '../shared/TagSelector.vue';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('PostTagsPanel must be used within editor context');
}

if (!context.state.selectedTags) {
    throw new Error('Editor context missing selectedTags state');
}

const selectedTags = context.state.selectedTags;

const emitUpdate = (value: any) => {
    selectedTags.value = value;
};
</script>

