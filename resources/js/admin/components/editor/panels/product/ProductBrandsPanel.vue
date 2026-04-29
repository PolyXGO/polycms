<template>
    <CategorySelector
        v-model="selectedBrands"
        type="product_brand"
        :label="$t('Brands')"
        :add-label="$t('Add New Brand')"
        :parent-placeholder="$t('Parent Brand')"
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
    throw new Error('ProductBrandsPanel must be used within editor context');
}

if (!context.state.productBrands) {
    throw new Error('Product editor context missing brands state');
}

const selectedBrands = context.state.productBrands;

const handleUpdate = (value: number[]) => {
    selectedBrands.value = value;
};

const handleCreated = (category: { id: number }) => {
    if (category?.id) {
        selectedBrands.value = Array.from(new Set([...selectedBrands.value, category.id]));
    }
};
</script>

