<template>
 <div v-if="form" class="space-y-6">
 <div class="grid gap-4 xl:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)]">
 <div>
 <label class="mb-1 block text-sm font-medium text-admin-theme-text-secondary">
 {{ headingLabel }}
 </label>
 <input
 v-model="form.name"
 type="text"
 class="w-full rounded-lg border bg-admin-theme-input-bg px-3 py-2 text-xl font-bold text-admin-theme-text transition-all duration-200 placeholder-gray-400 focus:border-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-input-border dark:placeholder-gray-500"
 :placeholder="namePlaceholder"
 :disabled="isReadOnly"
 @input="helpers.generateSlug?.()"
 />
 </div>

 <div>
 <label class="mb-1 block text-sm font-medium text-admin-theme-text-secondary">
 Slug
 </label>
 <input
 v-model="form.slug"
 type="text"
 class="w-full rounded-lg border bg-admin-theme-input-bg px-3 py-2 text-sm font-medium text-admin-theme-text transition-all duration-200 placeholder-gray-400 focus:border-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-input-border dark:placeholder-gray-500"
 placeholder="my-layout-asset"
 :disabled="isReadOnly"
 @input="helpers.onSlugInput?.($event)"
 />
 </div>
 </div>

 <div
 v-if="isReadOnly"
 class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200"
 >
 This is a default system {{ assetKindLabel }}. Duplicate it first to customize the structure.
 </div>

 <div>
 <label class="mb-2 block text-sm font-medium text-admin-theme-text-secondary">
 {{ structureLabel }}
 </label>
 <div
 v-if="isReadOnly"
 class="overflow-hidden rounded-2xl border border-admin-theme-border bg-admin-theme-surface/80"
 >
 <TiptapEditor
 v-model="contentHtml"
 v-model:json="contentRaw"
 :placeholder="placeholder"
 :editable="false"
 />
 </div>
 <TiptapEditor
 v-else
 v-model="contentHtml"
 v-model:json="contentRaw"
 :placeholder="placeholder"
 />
 </div>
 </div>
</template>

<script setup lang="ts">
import { computed, inject, isRef, ref } from'vue';
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore - Vue SFC typing provided via shim
import TiptapEditor from'../../../TiptapEditor.ts';
import { EditorContextKey } from'../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
 throw new Error('LayoutAssetPrimaryPanel must be used within editor context');
}

const form = context.form;
const helpers = context.helpers ?? {};

const rawContentHtml = context.state?.contentHtml;
const rawContentJson = context.state?.contentRaw;

const htmlRef = isRef(rawContentHtml) ? rawContentHtml : ref(rawContentHtml ??'');
const jsonRef = isRef(rawContentJson) ? rawContentJson : ref(rawContentJson ?? null);

if (!isRef(rawContentHtml) && context.state) {
 context.state.contentHtml = htmlRef;
}

if (!isRef(rawContentJson) && context.state) {
 context.state.contentRaw = jsonRef;
}

const contentHtml = computed<string>({
 get: () => (htmlRef.value ??'') as string,
 set: (value) => {
 htmlRef.value = value ??'';
 },
});

const contentRaw = computed<any>({
 get: () => jsonRef.value,
 set: (value) => {
 jsonRef.value = value;
 },
});

const isReadOnly = computed<boolean>(() => !!form.value?.is_system);
const assetKindLabel = computed(() => (form.value?.kind ==='template' ?'template' :'template part'));
const headingLabel = computed(() => (form.value?.kind ==='template' ?'Template Name' :'Part Name'));
const structureLabel = computed(() => (form.value?.kind ==='template' ?'Template Structure' :'Part Structure'));
const placeholder = computed(() =>
 form.value?.kind ==='template'
 ?'Build a reusable full-page layout using landing parts and elements...'
 :'Build a reusable layout part using landing blocks and elements...'
);
const namePlaceholder = computed(() =>
 form.value?.kind ==='template' ?'Landing starter template' :'Demo showcase part'
);
const previewUrl = computed(() => form.value?.preview_url ||'');
</script>
