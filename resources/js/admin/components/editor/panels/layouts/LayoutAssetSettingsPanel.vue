<template>
 <div v-if="form" class="space-y-5">
 <div>
 <label class="mb-1 block text-sm font-medium text-admin-theme-text-secondary">
 Category
 </label>
    <select
      v-model="form.category"
      class="w-full rounded-lg border bg-admin-theme-input-bg px-3 py-2 text-sm text-admin-theme-text transition-all duration-200 focus:border-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-input-border"
      :disabled="isReadOnly"
    >
      <option value="">Select a category</option>
      <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
        {{ cat.name }}
      </option>
    </select>
    <p class="mt-1 text-xs text-admin-theme-text-muted">
      Select the category for this layout asset. <router-link :to="{ name: 'admin.appearance.categories.index' }" class="text-admin-theme-primary hover:underline">Manage Categories</router-link>
    </p>
 </div>

 <div>
 <label class="mb-1 block text-sm font-medium text-admin-theme-text-secondary">
 Description
 </label>
 <textarea
 v-model="form.description"
 rows="4"
 class="w-full rounded-lg border bg-admin-theme-input-bg px-3 py-2 text-sm text-admin-theme-text transition-all duration-200 placeholder-gray-400 focus:border-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-input-border dark:placeholder-gray-500"
 placeholder="Explain when this reusable asset should be used."
 :disabled="isReadOnly"
 />
 </div>

 <div
 v-if="form.kind ==='template'"
 class="space-y-3 rounded-2xl border border-admin-theme-border bg-admin-theme-surface/70 p-4"
 >
 <div>
 <div class="text-sm font-semibold text-admin-theme-text">Applies To</div>
 <div class="text-xs text-admin-theme-text-muted">
 Choose which post types can use this template.
 </div>
 </div>

 <div class="grid grid-cols-1 gap-2">
 <label
 v-for="target in applyTargets"
 :key="target.value"
 class="flex items-center gap-3 rounded-xl border border-admin-theme-border px-3 py-2 text-sm text-admin-theme-text-secondary transition-colors hover:border-indigo-400 hover:text-admin-theme-primary dark:hover:border-admin-theme-primary dark:hover:text-admin-theme-primary"
 >
 <input
 type="checkbox"
 class="h-4 w-4 rounded border-gray-300 text-admin-theme-primary focus:ring-admin-theme-primary"
 :checked="selectedAppliesTo.includes(target.value)"
 :disabled="isReadOnly"
 @change="toggleApplyTarget(target.value)"
 />
 <span>{{ target.label }}</span>
 </label>
 </div>
 </div>

 <div class="space-y-3 rounded-2xl border border-admin-theme-border bg-admin-theme-surface/70 p-4">
 <div class="text-sm font-semibold text-admin-theme-text">Asset Info</div>

 <dl class="space-y-2 text-sm">
 <div class="flex items-center justify-between gap-4">
 <dt class="text-admin-theme-text-muted">Source</dt>
 <dd class="font-medium text-admin-theme-text">{{ sourceLabel }}</dd>
 </div>
 <div class="flex items-center justify-between gap-4">
 <dt class="text-admin-theme-text-muted">Status</dt>
 <dd class="font-medium text-admin-theme-text">{{ form.is_system ?'Default / locked' :'Custom' }}</dd>
 </div>
 <div v-if="form.kind ==='template'" class="flex items-center justify-between gap-4">
 <dt class="text-admin-theme-text-muted">Assigned Content</dt>
 <dd class="font-medium text-admin-theme-text">{{ assignedCount }}</dd>
 </div>
 <div class="flex items-center justify-between gap-4">
 <dt class="text-admin-theme-text-muted">Layout Engine</dt>
 <dd class="font-medium text-admin-theme-text">{{ form.layout ||'landing' }}</dd>
 </div>
 </dl>
 </div>
 </div>
</template>

<script setup lang="ts">
import { computed, inject, ref, onMounted } from 'vue';
import axios from 'axios';
import { EditorContextKey } from'../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
 throw new Error('LayoutAssetSettingsPanel must be used within editor context');
}

const form = context.form;

const isReadOnly = computed<boolean>(() => !!form.value?.is_system);
const assignedCount = computed<number>(() => Number(form.value?.assigned_posts_count ?? 0));
const sourceLabel = computed(() => form.value?.source_name || form.value?.source_type ||'Custom');
const selectedAppliesTo = computed<string[]>(() => Array.isArray(form.value?.applies_to) ? form.value.applies_to : []);

const applyTargets = [
 { value:'page', label:'Pages' },
 { value:'post', label:'Posts' },
 { value:'news', label:'News' },
];

const toggleApplyTarget = (target: string) => {
 const current = new Set(selectedAppliesTo.value);
 if (current.has(target)) {
 current.delete(target);
 } else {
 current.add(target);
 }

 form.value.applies_to = Array.from(current);
};

const categories = ref<any[]>([]);

const loadCategories = async () => {
  try {
    const res = await axios.get('/api/v1/categories', {
      params: { type: 'layout_asset', per_page: 100 }
    });
    categories.value = res.data.data || [];
  } catch (error) {
    console.error('Failed to load categories', error);
  }
};

onMounted(() => {
  loadCategories();
});
</script>
