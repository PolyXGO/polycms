<template>
  <CategorySelector
    v-model="selectedCategories"
    v-model:primary-category-id="primaryCategoryId"
    type="product"
    :label="$t('Product Categories')"
    :add-label="$t('Add new category')"
    :parent-placeholder="$t('Parent Category')"
    :locale="formLocale"
    @update:modelValue="handleUpdate"
    @created="handleCreated"
  />
</template>

<script setup lang="ts">
import { inject, computed, getCurrentInstance } from 'vue';
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
const formLocale = computed(() => context.form.value?.locale || 'en');

const primaryCategoryId = computed({
  get: () => {
    const raw = context.form.value?.settings?.seo?.primary_category_id ?? context.form.value?.primary_category_id;
    return raw ? Number(raw) : null;
  },
  set: (val: number | null) => {
    if (!context.form.value.settings) context.form.value.settings = {};
    if (!context.form.value.settings.seo) context.form.value.settings.seo = {};
    context.form.value.settings.seo.primary_category_id = val ? Number(val) : null;
    context.form.value.primary_category_id = val ? Number(val) : null;
  },
});

const handleUpdate = (value: number[]) => {
  selectedCategories.value = value;
};

const handleCreated = (category: { id: number }) => {
  if (category?.id) {
    selectedCategories.value = Array.from(new Set([...selectedCategories.value, category.id]));
  }
};
</script>

