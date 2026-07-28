<template>
 <div class="space-y-4">
 <div class="space-y-1">
 <label class="block text-sm font-semibold text-admin-theme-text-secondary">{{ $t('Name *') }}</label>
 <input
 v-model="form.name"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary transition-shadow"
 required
 @input="helpers.generateSlug?.()"
 />
 </div>

 <div class="space-y-1">
 <label class="block text-sm font-semibold text-admin-theme-text-secondary">{{ $t('Slug *') }}</label>
 <input
 v-model="form.slug"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary transition-shadow"
 required
 @input="handleSlugInput"
 @blur="handleSlugInput"
 />
 <div v-if="form.slug" class="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-admin-theme-text-muted">
 <span class="font-bold">{{ $t('Permalink:') }}</span>
 <a :href="permalink" target="_blank" rel="noopener" class="text-admin-theme-primary dark:text-admin-theme-primary font-semibold hover:underline break-all">
 {{ permalink }}
 </a>
 </div>
 </div>

 <div class="space-y-1">
 <label class="block text-sm font-semibold text-admin-theme-text-secondary">{{ $t('SKU') }}</label>
 <input v-model="form.sku" type="text" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary transition-shadow" :placeholder="$t('Stock keeping unit')" />
 </div>
 </div>
</template>

<script setup lang="ts">
import { computed, inject, getCurrentInstance } from'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import { EditorContextKey } from'../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
 throw new Error('ProductTitlePanel must be used within editor context');
}

const form = context.form.value;
const helpers = context.helpers ?? {};

const permalink = computed(() => helpers.getPermalink?.() ??'');

const handleSlugInput = (event: Event) => {
 const target = event.target as HTMLInputElement;
 helpers.onSlugInput?.(event);
 form.slug = target.value;
};
</script>

