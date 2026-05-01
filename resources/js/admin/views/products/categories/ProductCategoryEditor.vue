<template>
    <div class="editor-page">
        <header class="editor-page__header">
            <h1 class="editor-page__title">
                {{ isEditing ? ($t('Edit Product Category') || 'Edit Product Category') : ($t('New Product Category') || 'New Product Category') }}
            </h1>
            <div class="editor-page__actions">
                <a
                    v-if="isEditing && form.slug"
                    :href="getViewUrl()"
                    target="_blank"
                    class="editor-page__action editor-page__action--primary"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    View Page
                </a>
                <button type="button" class="editor-page__action" @click="router.back()">
                    Cancel
                </button>
            </div>
        </header>

        <form class="editor-form" @submit.prevent="save">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Name') || 'Name' }}</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    :placeholder="$t('Category Name') || 'Category Name'"
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
                                            {{ isEditingSlug ? 'Done' : 'Edit Slug' }}
                                        </button>
                                        <button 
                                            type="button" 
                                            class="px-3 py-1.5 text-xs font-semibold bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-200 hover:border-indigo-500 hover:text-indigo-600 transition-colors" 
                                            @click="copyPermalink"
                                        >
                                            Copy Link
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 pl-3">
                                    You can adjust the permalink structure at <router-link to="/admin/settings/group/permalinks" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Permalink Settings</router-link>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Summary') || 'Summary' }}</label>
                                <textarea
                                    v-model="form.summary"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    :placeholder="$t('Brief summary...') || 'Brief summary...'"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Description') || 'Description' }}</label>
                                <TiptapEditor
                                    v-model="form.description"
                                    :placeholder="$t('Full description...') || 'Full description...'"
                                />
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
                    <!-- Status & Parent -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Parent Category') || 'Parent Category' }}</label>
                                <select
                                    v-model="form.parent_id"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                >
                                    <option :value="null">{{ $t('None (Root)') || 'None (Root)' }}</option>
                                    <option
                                        v-for="cat in availableParents"
                                        :key="cat.id"
                                        :value="cat.id"
                                    >
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Order') || 'Order' }}</label>
                                <input
                                    v-model.number="form.order"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ $t('Featured Image') || 'Featured Image' }}</label>
                        <div
                            @click="openMediaLibrary"
                            class="relative aspect-video rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500 transition-colors overflow-hidden group"
                        >
                            <template v-if="form.image">
                                <img :src="form.image" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-white text-sm font-medium">{{ $t('Change Image') || 'Change Image' }}</span>
                                </div>
                                <button
                                    @click.stop="form.image = ''"
                                    class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors shadow-lg"
                                    title="Remove image"
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
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $t('Choose image') || 'Choose image' }}</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <MediaPicker
            ref="mediaPickerRef"
            @select="handleMediaSelect"
        />

        <div class="editor-floating-actions" :style="floatingActionsStyle">
            <button 
                type="button" 
                class="editor-floating-actions__primary" 
                :disabled="saving" 
                @click="save"
                :title="saving ? ($t('Saving…') || 'Saving…') : ($t('Save Category') || 'Save Category')"
            >
                <svg v-if="!saving" class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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

        <LandingBlockOptionsPanel />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, getCurrentInstance, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import TiptapEditor from '../../../components/TiptapEditor.vue';
import MediaPicker from '../../../components/MediaPicker';
import { useLandingStore } from '../../../stores/landingStore';
import { useSlugify } from '../../../composables/useSlugify';
import { useTranslation } from '../../../composables/useTranslation';
import { useDialog } from '../../../composables/useDialog';

import { getProductCategoryUrl } from '../../../utils/permalink';
import LandingBlockOptionsPanel from '../../../components/editor/panels/LandingBlockOptionsPanel.vue';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;
const route = useRoute();
const router = useRouter();
const dialog = useDialog();
const { slugify } = useSlugify();
const landingStore = useLandingStore();

const isEditing = computed(() => !!route.params.id);
const saving = ref(false);
const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const categories = ref<any[]>([]);

const isEditingSlug = ref(false);
const slugInputRef = ref<HTMLInputElement | null>(null);

const onSlugInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.value.slug = slugify(target.value);
};

const toggleSlugEdit = async () => {
    isEditingSlug.value = !isEditingSlug.value;
    if (isEditingSlug.value) {
        await nextTick();
        slugInputRef.value?.focus();
        slugInputRef.value?.select();
    } else {
        // Save when "Done" is clicked
        save();
    }
};

const slugPrefix = computed(() => {
    const origin = window.location.origin;
    const full = getProductCategoryUrl(form.value.slug);
    const slug = form.value.slug;
    if (!slug) return origin + full;
    const parts = full.split(slug);
    return origin + parts[0];
});

const fullPermalink = computed(() => {
    return getProductCategoryUrl(form.value.slug);
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

const form = ref({
    name: '',
    slug: '',
    summary: '',
    description: '',
    parent_id: null as number | null,
    order: 0,
    image: '',
    meta: {
        meta_title: '',
        meta_description: '',
        meta_keywords: ''
    } as Record<string, any>,
});

const availableParents = computed(() => {
    return categories.value.filter(c => c.id !== Number(route.params.id));
});

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

const getViewUrl = () => {
    // Use permalink utility to construct URL based on settings
    return getProductCategoryUrl(form.value.slug);
};

const generateSlug = () => {
    if (!isEditing.value && form.value.name) {
        form.value.slug = slugify(form.value.name);
    }
};

const loadCategories = async () => {
    try {
        const response = await axios.get('/api/v1/product-categories');
        categories.value = response.data.data;
    } catch (error) {
        console.error('Error loading categories:', error);
    }
};

const loadCategory = async () => {
    if (!isEditing.value) return;
    try {
        const response = await axios.get(`/api/v1/product-categories/${route.params.id}`);
        const category = response.data.data;
        form.value = {
            name: category.name,
            slug: category.slug,
            summary: category.summary || '',
            description: category.description || '',
            parent_id: category.parent_id,
            order: category.order || 0,
            image: category.image || '',
            meta: (category.meta && typeof category.meta === 'object' && !Array.isArray(category.meta) && Object.keys(category.meta).length > 0)
                ? category.meta
                : { meta_title: '', meta_description: '', meta_keywords: '' },
        };
    } catch (error) {
        console.error('Error loading category:', error);
        dialog.error('Failed to load category');
    }
};

const openMediaLibrary = () => {
    mediaPickerRef.value?.open();
};

const handleMediaSelect = (media: any) => {
    const selected = Array.isArray(media) ? media[0] : media;
    if (!selected) return;
    form.value.image = selected.url;
};

const save = async () => {
    saving.value = true;
    try {
        if (isEditing.value) {
            await axios.put(`/api/v1/product-categories/${route.params.id}`, form.value);
            await loadCategory();
            dialog.success('Category updated successfully');
        } else {
            const response = await axios.post('/api/v1/product-categories', form.value);
            dialog.success('Category created successfully');
            router.push({
                name: 'admin.product-categories.edit',
                params: { id: response.data.data.id }
            });
        }
    } catch (error: any) {
        console.error('Error saving category:', error);
        const message = error.response?.data?.message || 'Failed to save category';
        dialog.error(message);
    } finally {
        saving.value = false;
    }
};

onMounted(async () => {
    landingStore.setPostType('product-categories');
    
    await loadCategories();
    await loadCategory();
});

watch(() => route.params.id, (newId) => {
    if (!newId) {
        form.value = {
            name: '',
            slug: '',
            summary: '',
            description: '',
            parent_id: null,
            order: 0,
            image: '',
            meta: { meta_title: '', meta_description: '', meta_keywords: '' },
        };
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
