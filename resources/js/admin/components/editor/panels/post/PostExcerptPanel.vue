<template>
 <div v-if="form" class="space-y-2">
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">Excerpt</label>
 <textarea
 v-model="form.excerpt"
 rows="4"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 placeholder="A short summary for your post..."
 />
 <p class="mt-1 text-xs text-admin-theme-text-muted">Provide a short summary for feeds and meta descriptions.</p>
 </div>
</template>

<script setup lang="ts">
import { inject } from'vue';
import { EditorContextKey } from'../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
 throw new Error('PostExcerptPanel must be used within editor context');
}

const form = context.form;

if (!form.value) {
 form.value = {
 title:'',
 slug:'',
 type: context.type ??'post',
 status:'draft',
 excerpt:'',
 meta_title:'',
 meta_description:'',
 meta_keywords:'',
 } as any;
}

</script>

