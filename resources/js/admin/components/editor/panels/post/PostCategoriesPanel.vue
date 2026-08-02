<template>
  <CategorySelector
    v-model="selectedCategories"
    v-model:primary-category-id="primaryCategoryId"
    type="post"
    :label="'Categories'"
    :add-label="'Add Category'"
    :parent-placeholder="'Parent Category'"
    :locale="context.form.value.locale"
    @update:modelValue="handleUpdate"
    @created="handleCreated"
  />
</template>

<script setup lang="ts">
import { computed, inject } from 'vue';
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

const primaryCategoryId = computed({
  get: () => {
    // Always read fresh from context.state.metaFields in case the ref value was replaced
    const currentMeta = context.state.metaFields;
    const unwrappedMeta = currentMeta?.value ?? currentMeta;
    const raw = unwrappedMeta?.primary_category_id ?? context.form.value?.meta_fields?.primary_category_id;
    return raw ? Number(raw) : null;
  },
  set: (val: number | null) => {
    const strVal = val ? String(val) : null;
    const currentMeta = context.state.metaFields;
    if (currentMeta) {
      if (currentMeta.value !== undefined) {
        currentMeta.value = {
          ...(currentMeta.value || {}),
          primary_category_id: strVal,
        };
      } else {
        currentMeta.primary_category_id = strVal;
      }
    }
    if (!context.form.value.meta_fields) {
      context.form.value.meta_fields = {};
    }
    context.form.value.meta_fields.primary_category_id = strVal;
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

