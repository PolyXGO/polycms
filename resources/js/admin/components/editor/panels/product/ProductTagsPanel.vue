<template>
 <TagSelector type="product" v-model="selectedTags" :placeholder="$t('Add new product tag')" :locale="formLocale" @update:modelValue="handleUpdate" />
</template>

<script setup lang="ts">
import { inject, computed, getCurrentInstance } from'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import TagSelector from'../shared/TagSelector.vue';
import { EditorContextKey } from'../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
 throw new Error('ProductTagsPanel must be used within editor context');
}

if (!context.state.productTags) {
 throw new Error('Product editor context missing tags state');
}

const selectedTags = context.state.productTags;
const formLocale = computed(() => context.form.value?.locale || 'en');

const handleUpdate = (value: any) => {
 selectedTags.value = value;
};
</script>

