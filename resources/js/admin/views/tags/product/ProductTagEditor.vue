<template>
 <div class="editor-page">
 <header class="editor-page__header">
 <h1 class="editor-page__title">
 {{ isEdit ? ($t('Edit Product Tag') ||'Edit Product Tag') : ($t('New Tag') ||'New Tag') }}
 </h1>
 <div class="editor-page__actions">
 <a
 v-if="isEdit && form.slug"
 :href="getPermalink()"
 target="_blank"
 class="editor-page__action editor-page__action--primary"
 >
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
 </svg>
 View Products
 </a>
 <button type="button" class="editor-page__action" @click="router.back()">
 Cancel
 </button>
 </div>
 </header>

 <div class="editor-floating-actions" :style="floatingActionsStyle">
 <button 
 type="button" 
 class="editor-floating-actions__primary" 
 :disabled="loading" 
 @click="handleSubmit"
 :title="loading ? ($t('Saving…') ||'Saving…') : ($t('Save Tag') ||'Save Tag')"
 >
 <svg v-if="!loading" class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M19 21H5C4.44772 21 4 20.5523 4 20V4C4 3.44772 4.44772 3 5 3H16L20 7V20C20 20.5523 19.5523 21 19 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
 <path d="M17 21V15H7V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
 <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
 </svg>
 <svg v-else class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
 </svg>
 </button>
 </div>

 <form @submit.prevent="handleSubmit" class="editor-form">
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
 <!-- Main Content -->
 <div class="lg:col-span-2 space-y-6">
 <!-- Tag Information -->
 <div class="bg-admin-theme-surface rounded-lg shadow p-6">
 <div class="space-y-4">
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Name') ||'Name' }} *</label>
 <input
 v-model="form.name"
 type="text"
 required
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
 :placeholder="$t('Tag name') ||'Tag name'"
 @input="generateSlug"
 />
 </div>
 <div v-if="form.slug">
 <div class="flex flex-wrap items-center gap-4 bg-admin-theme-base/50 p-3 rounded-xl border border-dashed border-admin-theme-border">
 <div class="flex-1 flex items-center min-w-0 overflow-hidden">
 <span class="text-sm text-admin-theme-text-muted truncate">{{ slugPrefix }}</span>
 <input
 ref="slugInputRef"
 v-model="form.slug"
 type="text"
 class="flex-1 px-2 py-1 bg-transparent border-none text-sm font-semibold text-admin-theme-primary dark:text-admin-theme-primary focus:outline-none min-w-[50px]"
 :readonly="!isEditingSlug"
 required
 @input="onSlugInput"
 />
 </div>
 <div class="flex gap-2 shrink-0">
 <button 
 type="button" 
 class="px-3 py-1.5 text-xs font-semibold bg-admin-theme-input-bg border border-admin-theme-border rounded-lg text-admin-theme-text hover:border-admin-theme-primary hover:text-admin-theme-primary transition-colors" 
 @click="toggleSlugEdit"
 >
 {{ isEditingSlug ?'Done' :'Edit Slug' }}
 </button>
 <button 
 type="button" 
 class="px-3 py-1.5 text-xs font-semibold bg-admin-theme-input-bg border border-admin-theme-border rounded-lg text-admin-theme-text hover:border-admin-theme-primary hover:text-admin-theme-primary transition-colors" 
 @click="copyPermalink"
 >
 Copy Link
 </button>
 </div>
 </div>
 <div class="mt-2 text-[11px] text-admin-theme-text-muted pl-3">
 You can adjust the permalink structure at <router-link :to="{ name: 'admin.settings.group', params: { group: 'permalinks' } }" class="text-admin-theme-primary dark:text-admin-theme-primary hover:underline font-medium">Permalink Settings</router-link>
 </div>
 </div>
 <div>
 <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Description') ||'Description' }}</label>
 <TiptapEditor
 v-model="form.description"
 :placeholder="$t('Tag description...') ||'Tag description...'"
 />
 </div>
 </div>
 </div>
 </div>

 <!-- Sidebar (empty for now, can add metadata later) -->
 <div class="space-y-6">
 <!-- Placeholder for future sidebar content -->
 </div>
 </div>
 </form>

 <LandingBlockOptionsPanel />
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, getCurrentInstance, nextTick } from'vue';
import { useRouter, useRoute } from'vue-router';
import axios from'axios';
import TiptapEditor from'../../../components/TiptapEditor.vue';
import { useLandingStore } from'../../../stores/landingStore';
import { useSlugify } from'../../../composables/useSlugify';

import { useDialog } from'../../../composables/useDialog';
import { getProductTagUrl } from'../../../utils/permalink';
import { useTranslation } from'../../../composables/useTranslation';
import { useValidation } from'../../../composables/useValidation';
import FormField from'../../../components/forms/FormField.vue';
import FormInput from'../../../components/forms/FormInput.vue';
import FormTextarea from'../../../components/forms/FormTextarea.vue';
import LandingBlockOptionsPanel from'../../../components/editor/panels/LandingBlockOptionsPanel.vue';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const route = useRoute();
const { slugify } = useSlugify();
const dialog = useDialog();
const landingStore = useLandingStore();

const floatingActionsStyle = computed(() => {
 const baseOffset = 32; // Default right offset
 if (landingStore.activeBlock) {
 return {
 right: `${(landingStore.optionsWidth || 300) + baseOffset}px`,
 };
 }
 return {
 right: `${baseOffset}px`,
 };
});

const getPermalink = () => {
 // Use permalink utility to construct URL based on settings
 return getProductTagUrl(form.value.slug);
};

// Validation
const validation = useValidation({
 showToast: false,
});

const validationErrors = computed(() => validation.errors.value);

const isEdit = computed(() => !!route.params.id);
const loading = ref(false);
const slugManuallyEdited = ref(false);

const isEditingSlug = ref(false);
const slugInputRef = ref<HTMLInputElement | null>(null);

const toggleSlugEdit = async () => {
 isEditingSlug.value = !isEditingSlug.value;
 if (isEditingSlug.value) {
 await nextTick();
 slugInputRef.value?.focus();
 slugInputRef.value?.select();
 } else {
 // Save when"Done" is clicked
 handleSubmit();
 }
};

const slugPrefix = computed(() => {
 const origin = window.location.origin;
 const full = getProductTagUrl(form.value.slug);
 const slug = form.value.slug;
 if (!slug) return origin + full;
 const parts = full.split(slug);
 return origin + parts[0];
});

const fullPermalink = computed(() => {
 return getProductTagUrl(form.value.slug);
});

const copyPermalink = async () => {
 const text = fullPermalink.value;
 if (!text) return;
 if (navigator.clipboard?.writeText) {
 try {
 await navigator.clipboard.writeText(text);
 } catch (error) {
 console.warn('Copy failed', error);
 }
 } else {
 const textarea = document.createElement('textarea');
 textarea.value = text;
 textarea.style.position ='fixed';
 textarea.style.left ='-1000px';
 document.body.appendChild(textarea);
 textarea.select();
 try {
 document.execCommand('copy');
 } catch (error) {
 console.warn('Copy failed', error);
 }
 document.body.removeChild(textarea);
 }
};

const form = ref({
 name:'',
 slug:'',
 description:'',
});

const generateSlug = () => {
 // Only auto-generate if slug is empty or hasn't been manually edited
 if (!slugManuallyEdited.value && form.value.name) {
 form.value.slug = slugify(form.value.name);
 }
};

const onSlugInput = () => {
 // Mark slug as manually edited when user types in slug field
 slugManuallyEdited.value = true;
};

const loadTag = async () => {
 if (!isEdit.value) return;

 try {
 const response = await axios.get(`/api/v1/product-tags/${route.params.id}`);
 const tag = response.data.data;
 form.value = {
 name: tag.name,
 slug: tag.slug,
 description: tag.description ||'',
 };
 } catch (error: any) {
 console.error('Error loading tag:', error);
 const message = error.response?.data?.message ||'Failed to load tag';
 dialog.error(message);
 router.back();
 }
};

const handleSubmit = async () => {
 // Validate form
 const validationRules: Record<string, any[]> = {
 name: ['required', { type:'min' as const, value: 2 }],
 slug: ['required', { type:'pattern' as const, value: /^[a-z0-9]+(?:-[a-z0-9]+)*$/, message:'Slug must be lowercase alphanumeric with hyphens' }],
 };

 const results = await validation.validateForm(form.value, validationRules);
 const hasValidationErrors = results.some(r => !r.valid);

 if (hasValidationErrors) {
 return;
 }

 loading.value = true;
 validation.clearAllErrors();

 try {
 if (isEdit.value) {
 await axios.put(`/api/v1/product-tags/${route.params.id}`, form.value);
 // Reload tag data to reflect changes, but stay on edit page
 await loadTag();
 dialog.success($t('Tag updated successfully') ||'Tag updated successfully');
 } else {
 const response = await axios.post('/api/v1/product-tags', form.value);
 // Redirect to edit page after creating
 dialog.success($t('Tag created successfully') ||'Tag created successfully');
 router.push({
 name:'admin.product-tags.edit',
 params: { id: response.data.data.id }
 });
 }
 } catch (error: any) {
 console.error('Error saving tag:', error);
 if (error.response?.data?.errors) {
 // Set errors from API response
 validation.setErrors(error.response.data.errors);
 }
 const message = error.response?.data?.message || ($t('Failed to save tag') ||'Failed to save tag');
 dialog.error(message);
 } finally {
 loading.value = false;
 }
};

onMounted(() => {
 landingStore.setPostType('product_tag');
 if (isEdit.value) {
 loadTag();
 }
});
</script>

<style scoped>
.editor-page {
 display: flex;
 flex-direction: column;
 gap: 1.5rem;
}

.editor-page__header {
 display: flex;
 align-items: center;
 justify-content: space-between;
 gap: 1rem;
}

.editor-page__title {
 font-size: 1.75rem;
 font-weight: 700;
 color: rgb(var(--admin-theme-text));
}



.editor-page__actions {
 display: flex;
 gap: 0.75rem;
}

.editor-page__action {
 padding: 0.45rem 0.95rem;
 border-radius: 0.75rem;
 border: 1px solid rgb(var(--admin-theme-border));
 background: rgb(var(--admin-theme-base));
 color: rgb(var(--admin-theme-text));
 font-weight: 600;
 cursor: pointer;
 display: flex;
 align-items: center;
 gap: 0.5rem;
 text-decoration: none;
 transition: all 0.2s;
}

.editor-page__action:hover {
 background: #f1f5f9;
}

.editor-page__action--primary {
 background: rgb(var(--admin-theme-primary));
 border-color: rgb(var(--admin-theme-primary));
 color: rgb(var(--admin-theme-primary-content));
}

.editor-page__action--primary:hover {
 background: rgb(var(--admin-theme-primary-hover));
}





.editor-form {
 display: block;
}


.editor-floating-actions {
 position: fixed;
 display: none;
 bottom: 2rem;
 right: 2rem;
 flex-direction: column;
 gap: 0.75rem;
 z-index: 40;
 transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.editor-floating-actions__primary {
 width: 3.5rem;
 height: 3.5rem;
 display: flex;
 align-items: center;
 justify-content: center;
 border-radius: 9999px;
 background: rgb(var(--admin-theme-primary));
 color: rgb(var(--admin-theme-primary-content));
 border: none;
 box-shadow: 0 10px 25px -5px rgb(var(--admin-theme-primary) / 0.4), 0 8px 10px -6px rgb(var(--admin-theme-primary) / 0.4);
 cursor: pointer;
 transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.editor-floating-actions__primary:hover {
 background: rgb(var(--admin-theme-primary-hover));
 transform: translateY(-2px);
 box-shadow: 0 20px 25px -5px rgb(var(--admin-theme-primary) / 0.4), 0 10px 10px -5px rgb(var(--admin-theme-primary) / 0.4);
}

.editor-floating-actions__primary:active {
 transform: translateY(0);
}

.editor-floating-actions__primary:disabled {
 opacity: 0.7;
 cursor: not-allowed;
 transform: none;
}

@media (min-width: 1024px) {
 .editor-floating-actions {
 display: flex;
 }
}
</style>
