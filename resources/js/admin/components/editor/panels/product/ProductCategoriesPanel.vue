<template>
    <CategorySelector
        v-model="selectedCategories"
        type="product"
        :label="$t('Product Categories')"
        :add-label="$t('Add new category')"
        :parent-placeholder="$t('Parent Category')"
        @update:modelValue="handleUpdate"
        @created="handleCreated"
    />
</template>

<script setup lang="ts">
import { inject, getCurrentInstance } from 'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import CategorySelector from '../shared/CategorySelector.vue';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('ProductCategoriesPanel must be used within editor context');
}

if (!context.state.productCategories) {
    throw new Error('Product editor context missing categories state');
}

const selectedCategories = context.state.productCategories;

const handleUpdate = (value: number[]) => {
    selectedCategories.value = value;
};

const handleCreated = (category: { id: number }) => {
    if (category?.id) {
        selectedCategories.value = Array.from(new Set([...selectedCategories.value, category.id]));
    }
};
</script>

