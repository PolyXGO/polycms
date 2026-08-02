<template>
 <div v-if="form" class="space-y-4">
 <div>
 <TemplateSelector
 v-model="form.template_theme"
 :view-type="viewType"
 help="Select a specific template from your active themes to render this content."
 />
 </div>

 <!-- Custom Iframe field for FlexiDocs -->
 <div v-if="form.template_theme ==='flexidocs::posts.iframe' || form.template_theme ==='flexidocs::posts.iframe-full'" class="p-4 bg-admin-theme-base rounded-lg border border-admin-theme-border">
 <h4 class="text-sm font-medium text-admin-theme-text mb-3">{{ $t('iFrame Settings') }}</h4>
 <div class="mb-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('iFrame URL') }} <span class="text-red-500">*</span></label>
 <input
 :value="iframeUrl"
 @input="onIframeUrlInput"
 type="url"
 placeholder="https://example.com"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-1 focus:ring-admin-theme-primary outline-none"
 />
 </div>
 <p class="text-xs text-admin-theme-text-muted">
 {{ $t('Enter the URL to display inside the iFrame instead of the original post content.') }}
 </p>
 </div>
 </div>
</template>

<script setup lang="ts">
import { computed, inject } from'vue';
import { EditorContextKey } from'../../../../editor/context';
import TemplateSelector from'../../../../components/TemplateSelector.vue';

const context = inject(EditorContextKey);
if (!context) {
 throw new Error('PostThemeTemplatePanel must be used within editor context');
}

const form = context.form;

// metaFields lives inside context.state — it's a Ref<Record<string, string|null>>
const metaFields = context.state?.metaFields;

const viewType = computed(() => {
 if (form.value?.type ==='page') return'pages.show';
 if (form.value?.type ==='product') return'products.show';
 return'posts.show';
});

// Safe computed accessor for iframe_url from metaFields
const iframeUrl = computed(() => metaFields?.iframe_url ||'');

const onIframeUrlInput = (event: Event) => {
 const val = (event.target as HTMLInputElement).value;
 if (metaFields) {
 metaFields['iframe_url'] = val || null;
 }
};
</script>
