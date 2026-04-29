<template>
    <CategorySelector
        v-model="selectedCategories"
        type="post"
        :label="'Categories'"
        :add-label="'Add Category'"
        :parent-placeholder="'Parent Category'"
        @update:modelValue="handleUpdate"
        @created="handleCreated"
    />
</template>

<script setup lang="ts">
import { inject } from 'vue';
import CategorySelector from '../shared/CategorySelector.vue';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('PostCategoriesPanel must be used within editor context');
}

if (!context.state.selectedCategories) {
    throw new Error('Editor context missing selectedCategories state');
}

const selectedCategories = context.state.selectedCategories;

const handleUpdate = (value: number[]) => {
    selectedCategories.value = value;
};

const handleCreated = (category: { id: number }) => {
    if (category?.id) {
        selectedCategories.value = Array.from(new Set([...selectedCategories.value, category.id]));
    }
};
</script>

