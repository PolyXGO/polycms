<template>
    <div class="editor-page">
        <header class="editor-page__header">
            <h1 class="editor-page__title">
                {{ isEdit ? $t('Edit Category') : $t('New Category') }}
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
                    {{ $t('View Page') }}
                </a>
                <button type="button" class="editor-page__action" @click="router.back()">
                    {{ $t('Cancel') }}
                </button>
            </div>
        </header>

        <div class="editor-floating-actions" :style="floatingActionsStyle">
            <button 
                type="button" 
                class="editor-floating-actions__primary" 
                :disabled="loading" 
                @click="handleSubmit"
                :title="loading ? ($t('Saving…') || 'Saving…') : ($t('Save Category') || 'Save Category')"
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
                    <!-- Basic Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Name (Title) *') }}</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                    :placeholder="$t('Category name')"
                                    @input="generateSlug"
                                />
                            </div>
                            <div v-if="form.slug">
                                <div class="flex flex-wrap items-center gap-4 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                                    <div class="flex-1 flex items-center min-w-0 overflow-hidden">
                                        <span class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ slugPrefix }}</span>
                                        <input
                                            ref="slugInputRef"
                                            v-model="form.slug"
                                            type="text"
                                            class="flex-1 px-2 py-1 bg-transparent border-none text-sm font-semibold text-indigo-600 dark:text-indigo-400 focus:outline-none min-w-[50px]"
                                            :readonly="!isEditingSlug"
                                            required
                                            @input="onSlugInput"
                                        />
                                    </div>
                                    <div class="flex gap-2 shrink-0">
                                        <button 
                                            type="button" 
                                            class="px-3 py-1.5 text-xs font-semibold bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-200 hover:border-indigo-500 hover:text-indigo-600 transition-colors" 
                                            @click="toggleSlugEdit"
                                        >
                                            {{ isEditingSlug ? $t('Done') : $t('Edit Slug') }}
                                        </button>
                                        <button 
                                            type="button" 
                                            class="px-3 py-1.5 text-xs font-semibold bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-200 hover:border-indigo-500 hover:text-indigo-600 transition-colors" 
                                            @click="copyPermalink"
                                        >
                                            {{ $t('Copy Link') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 pl-3">
                                    {{ $t('You can adjust the permalink structure at') }} <router-link to="/admin/settings/group/permalinks" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ $t('Permalink Settings') }}</router-link>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Summary') }}</label>
                                <textarea
                                    v-model="form.summary"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                    :placeholder="$t('Short summary of the category...')"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Description') }}</label>
                                <TiptapEditor v-model="form.description" :placeholder="$t('Enter category description...')" />
                            </div>
                        </div>
                    </div>

                    <!-- SEO Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200 mb-4">{{ $t('SEO Metadata') || 'SEO Metadata' }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                                <input v-model="form.meta.meta_title" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Title shown in search results (leave empty to use category name)" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                                <textarea v-model="form.meta.meta_description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Short description for SEO (leave empty to use summary)"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Keywords</label>
                                <input v-model="form.meta.meta_keywords" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Comma separated keywords" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Category Settings -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="space-y-4">
                            <div v-if="!isTypeFixed">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Type *') }}</label>
                                <select v-model="form.type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="">{{ $t('Select type') }}</option>
                                    <option value="post">{{ $t('Post') }}</option>
                                    <option value="product">{{ $t('Product') }}</option>
                                </select>
                            </div>
                            <div v-else>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Type') }}</label>
                                <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    {{ form.type === 'post' ? $t('Post') : $t('Product') }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Parent Category') }}</label>
                                <select v-model="form.parent_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option :value="null">{{ $t('None (Root Category)') }}</option>
                                    <option
                                        v-for="category in parentCategories"
                                        :key="category.id"
                                        :value="category.id"
                                        :disabled="category.id === parseInt(String(route.params.id))"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            <!-- Theme Template Selection -->
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                <TemplateSelector
                                    v-model="form.template_theme"
                                    :view-type="form.type === 'product' ? 'product-categories.show' : 'categories.show'"
                                    help="Select a specific template from your active themes to render this category."
                                />

                                <!-- Custom Iframe field for Categories in FlexiDocs -->
                                <div v-if="form.template_theme === 'flexidocs::categories.iframe' || form.template_theme === 'flexidocs::categories.iframe-full'" class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">{{ $t('iFrame Settings') }}</h4>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('iFrame URL') }} <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.meta.iframe_url"
                                        type="url"
                                        placeholder="https://example.com"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                    />
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $t('Enter the URL to display inside the iFrame instead of the original category content.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Image -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ $t('Category Image') }}</label>
                        <div
                            @click="openMediaPicker"
                            class="relative aspect-video rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500 transition-colors overflow-hidden group"
                        >
                            <template v-if="imagePreview">
                                <img :src="imagePreview" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-white text-sm font-medium">{{ $t('Change Image') }}</span>
                                </div>
                                <button
                                    @click.stop="removeImage"
                                    class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors shadow-lg"
                                    :title="$t('Remove image')"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>
                            <template v-else>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ imagePreview ? $t('Change Image') : $t('Select Image') }}</span>
                            </template>
                        </div>
                        <MediaPicker
                            ref="mediaPickerRef"
                            :multiple="false"
                            :accepted-types="['image']"
                            @select="handleMediaSelect"
                        />
                    </div>
                </div>
            </div>
        </form>

        <LandingBlockOptionsPanel />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch, getCurrentInstance, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import TiptapEditor from '../../components/TiptapEditor';
import MediaPicker from '../../components/MediaPicker';
import { useLandingStore } from '../../stores/landingStore';
import { useSlugify } from '../../composables/useSlugify';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';
import { useValidation } from '../../composables/useValidation';
import FormField from '../../components/forms/FormField.vue';
import FormInput from '../../components/forms/FormInput.vue';
import FormSelect from '../../components/forms/FormSelect.vue';
import FormTextarea from '../../components/forms/FormTextarea.vue';
import TemplateSelector from '../../components/TemplateSelector.vue';

import LandingBlockOptionsPanel from '../../components/editor/panels/LandingBlockOptionsPanel.vue';
import { getCategoryUrl } from '../../utils/permalink';

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

// Validation
const validation = useValidation({
    showToast: false,
});

const validationErrors = computed(() => validation.errors.value);

const isEdit = computed(() => !!route.params.id);

const loading = ref(false);
const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const imagePreview = ref<string | null>(null);
const parentCategories = ref<any[]>([]);
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
        // Save when "Done" is clicked
        handleSubmit();
    }
};

const isFlexidocsTemplate = computed(() => {
    const tt = form.value.template_theme || '';
    return tt === 'flexidocs' || tt.startsWith('flexidocs::');
});

const slugPrefix = computed(() => {
    const origin = window.location.origin;
    if (isFlexidocsTemplate.value) {
        return origin + '/docs/';
    }
    const full = getCategoryUrl(form.value.slug);
    const slug = form.value.slug;
    if (!slug) return origin + full;
    const parts = full.split(slug);
    return origin + parts[0];
});

const fullPermalink = computed(() => {
    return `${slugPrefix.value}${form.value.slug}`;
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
        textarea.style.position = 'fixed';
        textarea.style.left = '-1000px';
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

// Computed options for parent category select
const parentCategoryOptions = computed(() => {
    const options = [{ value: null, label: 'None (Root Category)' }];
    parentCategories.value.forEach(category => {
        if (category.id !== parseInt(String(route.params.id))) {
            options.push({ value: category.id, label: category.name });
        }
    });
    return options;
});

// Get type from query params or default to 'post'
const defaultType = computed(() => {
    if (route.query.type && typeof route.query.type === 'string') {
        return route.query.type;
    }
    return 'post';
});

// Check if type is fixed (from query params)
const isTypeFixed = computed(() => {
    return !isEdit.value && route.query.type && typeof route.query.type === 'string';
});

const form = ref({
    name: '',
    slug: '',
    type: defaultType.value,
    parent_id: null as number | null,
    description: '',
    summary: '',
    image: '',
    template_theme: null as string | null,
    meta: {
        iframe_url: '',
        meta_title: '',
        meta_description: '',
        meta_keywords: ''
    } as Record<string, any>,
});

const generateSlug = () => {
    if (isEdit.value) {
        return;
    }
    const freshSlug = form.value.name ? slugify(form.value.name) : '';
    form.value.slug = freshSlug;
    slugManuallyEdited.value = false;
};

const onSlugInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const inputValue = target.value;

    // If slug is empty, auto-generate from name
    if (!inputValue || inputValue.trim() === '') {
        if (form.value.name) {
            form.value.slug = slugify(form.value.name);
            slugManuallyEdited.value = false;
        }
        return;
    }

    // Check if input contains non-slug characters (spaces, special chars, Vietnamese accents)
    // Convert to lowercase for comparison
    const lowerInput = inputValue.toLowerCase();
    const hasNonSlugChars = /[^a-z0-9\-]/.test(lowerInput) || /[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/.test(lowerInput);

    // If input has non-slug characters, convert to slug
    if (hasNonSlugChars) {
        form.value.slug = slugify(inputValue);
        slugManuallyEdited.value = true;
    } else {
        // Input is already in slug format, just mark as manually edited
        slugManuallyEdited.value = true;
    }
};

const getPermalink = (): string => {
    if (!form.value.slug) return '';
    const baseUrl = window.location.origin;
    if (isFlexidocsTemplate.value) {
        return `${baseUrl}/docs/${form.value.slug}`;
    }
    return `${baseUrl}${getCategoryUrl(form.value.slug)}`;
};

const removeImage = () => {
    form.value.image = '';
    imagePreview.value = null;
};

const loadParentCategories = async () => {
    try {
        const response = await axios.get('/api/v1/categories', {
            params: { per_page: 100, type: form.value.type },
        });
        parentCategories.value = response.data.data || [];
    } catch (error) {
        console.error('Error loading parent categories:', error);
    }
};

const loadCategory = async () => {
    if (!isEdit.value) return;

    try {
        const response = await axios.get(`/api/v1/categories/${route.params.id}`);
        const category = response.data.data;
        form.value = {
            name: category.name,
            slug: category.slug,
            type: category.type,
            parent_id: category.parent_id || null,
            description: category.description || '',
            summary: category.summary || '', // If summary field exists
            image: category.image || '',
            template_theme: category.template_theme || null,
            meta: (category.meta && typeof category.meta === 'object' && !Array.isArray(category.meta) && Object.keys(category.meta).length > 0)
                ? category.meta
                : { iframe_url: '' },
        };
        imagePreview.value = category.image || null;
        // Load parent categories for the same type
        await loadParentCategories();
    } catch (error) {
        console.error('Error loading category:', error);
        dialog.error($t('Failed to load category') || 'Failed to load category');
    }
};

const handleSubmit = async () => {
    // Validate form
    const validationRules: Record<string, any[]> = {
        name: ['required', { type: 'min' as const, value: 2 }],
        slug: ['required', { type: 'pattern' as const, value: /^[a-z0-9]+(?:-[a-z0-9]+)*$/, message: 'Slug must be lowercase alphanumeric with hyphens' }],
    };

    if (!isTypeFixed.value) {
        validationRules.type = ['required'];
    }

    const results = await validation.validateForm(form.value, validationRules);
    const hasValidationErrors = results.some(r => !r.valid);

    if (hasValidationErrors) {
        return;
    }

    loading.value = true;
    validation.clearAllErrors();

    try {
        const data: any = {
            name: form.value.name,
            slug: form.value.slug,
            type: form.value.type,
            parent_id: form.value.parent_id,
            summary: form.value.summary || null,
            description: form.value.description,
            image: form.value.image || null,
            template_theme: form.value.template_theme || null,
            meta: form.value.meta,
        };

        if (isEdit.value) {
            await axios.put(`/api/v1/categories/${route.params.id}`, data);
            // Reload category data to reflect changes, but stay on edit page
            await loadCategory();
            dialog.success($t('Category updated successfully'));
        } else {
            const response = await axios.post('/api/v1/categories', data);
            // Redirect to edit page after creating
            dialog.success($t('Category created successfully'));
            router.push({
                name: 'admin.categories.edit',
                params: { id: response.data.data.id },
                query: route.query
            });
        }
    } catch (error: any) {
        console.error('Error saving category:', error);
        if (error.response?.data?.errors) {
            // Set errors from API response
            validation.setErrors(error.response.data.errors);
        }
        const message = error.response?.data?.message || $t('Failed to save category');
        dialog.error(message);
    } finally {
        loading.value = false;
    }
};

// Watch for type changes to reload parent categories
watch(() => form.value.type, async () => {
    if (form.value.type) {
        await loadParentCategories();
        // Reset parent_id when type changes
        if (form.value.parent_id) {
            const parentExists = parentCategories.value.find(c => c.id === form.value.parent_id);
            if (!parentExists) {
                form.value.parent_id = null;
            }
        }
    }
});

onMounted(async () => {
    const storeType = form.value.type === 'product' ? 'product-categories' : 'category';
    landingStore.setPostType(storeType);
    if (isEdit.value) {
        await loadCategory();
    } else {
        await loadParentCategories();
    }
    if (form.value.image) {
        imagePreview.value = form.value.image;
    }
});

const openMediaPicker = () => {
    mediaPickerRef.value?.open();
};

const handleMediaSelect = (media: any) => {
    const selected = Array.isArray(media) ? media[0] : media;
    if (!selected) return;
    form.value.image = selected.url;
    imagePreview.value = selected.url;
};
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
    color: #0f172a;
}

.dark .editor-page__title {
    color: #f8fafc;
}

.editor-page__actions {
    display: flex;
    gap: 0.75rem;
}

.editor-page__action {
    padding: 0.45rem 0.95rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #0f172a;
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
    background: #4f46e5;
    border-color: #4f46e5;
    color: white;
}

.editor-page__action--primary:hover {
    background: #4338ca;
}

.dark .editor-page__action {
    background: #1e293b;
    border-color: #334155;
    color: #f8fafc;
}

.dark .editor-page__action:hover {
    background: #334155;
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
    background: #4f46e5;
    color: #ffffff;
    border: none;
    box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4), 0 8px 10px -6px rgba(79, 70, 229, 0.4);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.editor-floating-actions__primary:hover {
    background: #4338ca;
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4), 0 10px 10px -5px rgba(79, 70, 229, 0.4);
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
