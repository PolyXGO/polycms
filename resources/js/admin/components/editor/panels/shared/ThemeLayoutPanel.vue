<template>
 <div v-if="form" class="space-y-4">
 <div class="space-y-1">
 <label class="block text-sm font-semibold text-admin-theme-text-secondary">Page Layout</label>
 <select v-model="form.layout" class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary transition-shadow">
 <option value="default">Default (with Sidebar)</option>
 <option value="fullwidth">Full Width (no Sidebar)</option>
 <option value="landing">Landing Page (Blank Canvas)</option>
 <option value="single-column">Single Column (Top Gallery)</option>
 </select>
 <p class="text-xs text-admin-theme-text-muted">Choose how this content should be displayed on the frontend.</p>
 </div>

 <div v-if="context.type ==='product' && form.layout ==='single-column'" class="space-y-1">
 <label class="flex items-center cursor-pointer">
 <input type="checkbox" v-model="form.settings.show_gallery" class="w-4 h-4 text-admin-theme-primary rounded focus:ring-admin-theme-primary border-admin-theme-input-border transition-all duration-200" />
 <span class="ml-2 text-sm font-medium text-admin-theme-text-secondary">Show Product Gallery</span>
 </label>
 <p class="text-xs text-admin-theme-text-muted">Toggle whether to display the photo gallery at the top of the single column layout.</p>
 </div>
 </div>
</template>

<script setup lang="ts">
import { inject, onMounted } from'vue';
import { EditorContextKey } from'../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
 throw new Error('ThemeLayoutPanel must be used within editor context');
}

const form = context.form;

onMounted(() => {
 if (form.value && !form.value.layout) {
 form.value.layout ='default';
 }
 if (form.value && (!form.value.settings || Array.isArray(form.value.settings))) {
 form.value.settings = {};
 }
 if (form.value && form.value.settings && form.value.settings.show_gallery === undefined) {
 form.value.settings.show_gallery = true;
 }
});
</script>
