<template>
    <div class="editor-page">
        <header class="editor-page__header">
            <h1 class="editor-page__title">
                {{ isEdit ? ($t('Edit Product') || 'Edit Product') : ($t('New Product') || 'New Product') }}
            </h1>
            <div class="editor-page__actions">
                <a
                    v-if="isEdit && form.slug"
                    :href="getViewUrl()"
                    target="_blank"
                    class="editor-page__action editor-page__action--primary"
                >
                    {{ $t('View Product') }}
                </a>
                <button type="button" class="editor-page__action" @click="router.back()">
                    {{ $t('Cancel') }}
                </button>
            </div>
        </header>

        <form class="editor-form" @submit.prevent="saveProduct">
            <EditorPanelLayout type="product" :components="panelComponents" />
        </form>

        <MediaPicker
            ref="mediaPickerRef"
            :multiple="currentPickerMode === 'gallery'"
            :accepted-types="['image']"
            @select="handleMediaSelect"
        />

        <div class="editor-floating-actions" :style="floatingActionsStyle">
            <button 
                type="button" 
                class="editor-floating-actions__primary" 
                :disabled="loading" 
                @click="saveProduct"
                :title="loading ? ($t('Saving…') || 'Saving…') : ($t('Save Product') || 'Save Product')"
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

        <!-- Landing Block Options Panel (Teleported to body) -->
        <LandingBlockOptionsPanel />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, getCurrentInstance, watch, onMounted, provide } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import MediaPicker from '../../components/MediaPicker';
import { useSlugify } from '../../composables/useSlugify';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import { usePermalinkSettings } from '../../composables/usePermalinkSettings';
import { useAuthStore } from '../../stores/auth';
import EditorPanelLayout from '../../components/editor/EditorPanelLayout.vue';
import ProductPrimaryPanel from '../../components/editor/panels/product/ProductPrimaryPanel.vue';
import ProductPricingPanel from '../../components/editor/panels/product/ProductPricingPanel.vue';
import ProductMediaPanel from '../../components/editor/panels/product/ProductMediaPanel.vue';
import ProductCategoriesPanel from '../../components/editor/panels/product/ProductCategoriesPanel.vue';
import ProductTagsPanel from '../../components/editor/panels/product/ProductTagsPanel.vue';
import ProductBrandsPanel from '../../components/editor/panels/product/ProductBrandsPanel.vue';
import ProductPublishPanel from '../../components/editor/panels/product/ProductPublishPanel.vue';
import ProductSeoPanel from '../../components/editor/panels/product/ProductSeoPanel.vue';
import ProductServicePanel from '../../components/editor/panels/product/ProductServicePanel.vue';
import ProductThemeTemplatePanel from '../../components/editor/panels/product/ProductThemeTemplatePanel.vue';
import ProductVariantsPanel from '../../components/editor/panels/product/ProductVariantsPanel.vue';
import ThemeLayoutPanel from '../../components/editor/panels/shared/ThemeLayoutPanel.vue';
import { EditorContextKey, createEditorContext } from '../../editor/context';
import { registerEditorPanelComponent } from '../../editor/panelRegistry';
import { useGlobalSaveHotkey } from '../../composables/useGlobalSaveHotkey';

import type { TagItem } from '../../components/editor/panels/shared/TagSelector.vue';
import LandingBlockOptionsPanel from '../../components/editor/panels/LandingBlockOptionsPanel.vue';
import { useLandingStore } from '../../stores/landingStore';

const landingStore = useLandingStore();
const postType = 'product';
landingStore.setPostType(postType);

interface MediaItem {
    id: number;
    name: string;
    file_name: string;
    url: string;
    type: string;
    size: number;
    created_at: string;
    width?: number;
    height?: number;
}

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const route = useRoute();
const { slugify } = useSlugify();
const dialog = useDialog();
const authStore = useAuthStore();
const { ensureStructureLoaded, buildUrl } = usePermalinkSettings();

ensureStructureLoaded();

const isEdit = computed(() => !!route.params.id);

const loading = ref(false);
const slugManuallyEdited = ref(false);
const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const currentPickerMode = ref<'featured' | 'gallery'>('featured');
const featuredImage = ref<MediaItem | null>(null);
const galleryImages = ref<MediaItem[]>([]);
const selectedCategories = ref<number[]>([]);
const selectedTags = ref<TagItem[]>([]);
const selectedBrands = ref<number[]>([]);

const defaultFormState = (type?: string) => ({
    name: '',
    slug: '',
    sku: '',
    type: type ?? 'product',
    status: 'draft',
    price: 0,
    sale_price: null as number | null,
    stock_quantity: 0,
    stock_status: 'in_stock',
    manage_stock: false,
    featured: false,
    allow_refund: true,
    refund_window_days: null as number | null,
    refund_policy_note: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    created_at: null as string | null,
    updated_at: null as string | null,
    published_at: null as string | null,
    scheduled_at: null as string | null,
    layout: 'default',
    template_theme: null as string | null,
    settings: {
        show_gallery: true,
        demo_url: '',
        preview_url: '',
        seo: {
            focus_keyword: '',
            canonical_url: '',
            primary_category_id: null as number | null,
            social_title: '',
            social_description: '',
            social_image: '',
        },
        faq: {
            enabled: false,
            source: 'none',
            global_mode: 'all',
            global_ids: [] as string[],
            custom_items: [] as Array<{ id: string; question: string; answer: string; open?: boolean }>,
        },
        tabs: {
            enabled: false,
            source: 'none',
            global_mode: 'all',
            global_ids: [] as string[],
            default_tab_id: null as string | null,
            custom_items: [] as Array<{ id: string; title: string; content: string; active_default?: boolean }>,
        },
    } as Record<string, any>,
    service_config: null as Record<string, any>[] | null,
    services: [] as any[],
    _variants: [] as any[],
    _attributes: [] as any[],
});

const form = ref(defaultFormState());

const panelComponents = {
    primary: ProductPrimaryPanel,
    pricing: ProductPricingPanel,
    variants: ProductVariantsPanel,
    media: ProductMediaPanel,
    categories: ProductCategoriesPanel,
    tags: ProductTagsPanel,
    brands: ProductBrandsPanel,
    publish: ProductPublishPanel,
    seo: ProductSeoPanel,
    service: ProductServicePanel,
    theme_template: ProductThemeTemplatePanel,
    layout: ThemeLayoutPanel,
};

const descriptionHtml = ref('');
const descriptionBlocks = ref<any>(null);

const editorState = {
    descriptionHtml,
    descriptionBlocks,
    featuredImage,
    galleryImages,
    productCategories: selectedCategories,
    productTags: selectedTags,
    productBrands: selectedBrands,
};

const resetForm = () => {
    Object.assign(form.value, defaultFormState(form.value.type));
    descriptionHtml.value = '';
    descriptionBlocks.value = null;
    featuredImage.value = null;
    galleryImages.value = [];
    slugManuallyEdited.value = false;
    selectedCategories.value = [];
    selectedTags.value = [];
    selectedBrands.value = [];
};

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

    if (!inputValue || inputValue.trim() === '') {
        if (form.value.name) {
            form.value.slug = slugify(form.value.name);
            slugManuallyEdited.value = false;
        }
        return;
    }

    const lowerInput = inputValue.toLowerCase();
    const hasNonSlugChars =
        /[^a-z0-9\-]/.test(lowerInput) ||
        /[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/.test(lowerInput);

    if (hasNonSlugChars) {
        form.value.slug = slugify(inputValue);
        slugManuallyEdited.value = true;
    } else {
        slugManuallyEdited.value = true;
    }
};

const getPermalink = (): string => {
    if (!form.value.slug) return '';
    return buildUrl('product', form.value.slug);
};

const getViewUrl = (): string => {
    return getPermalink();
};

const openMediaPicker = (mode: 'featured' | 'gallery') => {
    currentPickerMode.value = mode;
    mediaPickerRef.value?.open();
};

const handleMediaSelect = (media: MediaItem | MediaItem[]) => {
    if (currentPickerMode.value === 'featured') {
        featuredImage.value = Array.isArray(media) ? media[0] || null : media;
    } else {
        if (Array.isArray(media)) {
            media.forEach((img) => {
                if (!galleryImages.value.find((existing) => existing.id === img.id)) {
                    galleryImages.value.push(img);
                }
            });
        } else if (!galleryImages.value.find((existing) => existing.id === media.id)) {
                galleryImages.value.push(media);
        }
    }
};

const removeFeaturedImage = () => {
    featuredImage.value = null;
};

const removeGalleryImage = (index: number) => {
    galleryImages.value.splice(index, 1);
};

const moveGalleryImage = (index: number, direction: 'up' | 'down') => {
    if (direction === 'up' && index > 0) {
        const temp = galleryImages.value[index];
        galleryImages.value[index] = galleryImages.value[index - 1];
        galleryImages.value[index - 1] = temp;
    } else if (direction === 'down' && index < galleryImages.value.length - 1) {
        const temp = galleryImages.value[index];
        galleryImages.value[index] = galleryImages.value[index + 1];
        galleryImages.value[index + 1] = temp;
    }
};

const panelContext = createEditorContext({
    type: 'product',
    form,
    loading,
    helpers: {
        generateSlug,
        onSlugInput,
        getPermalink,
        openMediaPicker,
        removeFeaturedImage,
        removeGalleryImage,
        moveGalleryImage,
        save: () => saveProduct(),
        preview: () => {
            const url = getPermalink();
            if (url) {
                // Add preview token for admin to view draft products
                // Try to get token from authStore, fallback to localStorage
                const token = authStore.token || localStorage.getItem('auth_token');
                const previewUrl = token ? `${url}?preview_token=${encodeURIComponent(token)}` : url;
                window.open(previewUrl, '_blank');
            }
        },
    },
    state: editorState,
});

provide(EditorContextKey, panelContext);

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

const loadProduct = async () => {
    if (!route.params.id) return;

    try {
        const response = await axios.get(`/api/v1/products/${route.params.id}`);
        const product = response.data.data;
        Object.assign(form.value, defaultFormState(product.type), {
            name: product.name || '',
            slug: product.slug || '',
            sku: product.sku || '',
            type: product.type || 'product',
            status: product.status || 'draft',
            price: product.price ?? 0,
            sale_price: product.sale_price ?? null,
            stock_quantity: product.stock_quantity ?? 0,
            stock_status: product.stock_status || 'in_stock',
            manage_stock: product.manage_stock ?? false,
            featured: product.featured ?? false,
            allow_refund: product.allow_refund ?? true,
            refund_window_days: product.refund_window_days ?? null,
            refund_policy_note: product.refund_policy_note || '',
            meta_title: product.meta?.title || '',
            meta_description: product.meta?.description || '',
            meta_keywords: product.meta?.keywords || '',
            created_at: product.created_at || null,
            updated_at: product.updated_at || null,
            published_at: product.published_at || product.created_at || null,
            scheduled_at: product.scheduled_at || null,
            layout: product.layout || 'default',
            template_theme: product.template_theme || null,
            settings: {
                show_gallery: true,
                demo_url: '',
                preview_url: '',
                ...((product.settings && !Array.isArray(product.settings)) ? product.settings : {}),
            },
            service_config: product.service_config || null,
            services: product.services || [],
            _attributes: product.attributes || [],
            _variants: product.variants || [],
        });
        if (!form.value.settings.seo || typeof form.value.settings.seo !== 'object' || Array.isArray(form.value.settings.seo)) {
            form.value.settings.seo = {};
        }
        form.value.settings.seo = {
            focus_keyword: '',
            canonical_url: '',
            primary_category_id: null,
            social_title: '',
            social_description: '',
            social_image: '',
            ...(form.value.settings.seo || {}),
        };
        if (!form.value.settings.faq || typeof form.value.settings.faq !== 'object' || Array.isArray(form.value.settings.faq)) {
            form.value.settings.faq = {};
        }
        form.value.settings.faq = {
            enabled: false,
            source: 'none',
            global_mode: 'all',
            global_ids: [],
            custom_items: [],
            ...(form.value.settings.faq || {}),
        };
        if (!form.value.settings.tabs || typeof form.value.settings.tabs !== 'object' || Array.isArray(form.value.settings.tabs)) {
            form.value.settings.tabs = {};
        }
        form.value.settings.tabs = {
            enabled: false,
            source: 'none',
            global_mode: 'all',
            global_ids: [],
            default_tab_id: null,
            custom_items: [],
            ...(form.value.settings.tabs || {}),
        };
        descriptionHtml.value = product.description_html || '';
        descriptionBlocks.value = product.description_blocks || null;
        form.value.type = product.type || 'product';

        selectedCategories.value = Array.isArray(product.categories)
            ? product.categories
                  .map((category: any) => Number(category.id))
                  .filter((id: number) => Number.isFinite(id))
            : [];

        selectedTags.value = Array.isArray(product.tags)
            ? product.tags
                  .map((tag: any) => ({
                      id: Number(tag.id),
                      name: tag.name,
                  }))
                  .filter((tag: TagItem) => Number.isFinite(tag.id) && typeof tag.name === 'string')
            : [];

        selectedBrands.value = Array.isArray(product.brands)
            ? product.brands
                  .map((brand: any) => Number(brand.id))
                  .filter((id: number) => Number.isFinite(id))
            : [];

        if (product.media && Array.isArray(product.media)) {
            const mediaItems = [...product.media];
            const primaryMedia = mediaItems.find((m: any) => m.pivot?.is_primary);

            const buildMediaPayload = (media: any) => ({
                id: media.id,
                name: media.name,
                file_name: media.file_name,
                url: media.url,
                type: media.type,
                size: media.size,
                created_at: media.created_at,
                width: media.width,
                height: media.height,
            });

            if (primaryMedia) {
                featuredImage.value = buildMediaPayload(primaryMedia);
            } else if (mediaItems.length > 0) {
                const firstMedia = mediaItems[0];
                featuredImage.value = buildMediaPayload(firstMedia);
            } else {
                featuredImage.value = null;
            }

            const featuredId = featuredImage.value?.id ?? null;

            const galleryMedia = mediaItems
                .filter((m: any) => (m.pivot?.is_primary ? false : featuredId ? m.id !== featuredId : true))
                .sort((a: any, b: any) => (a.pivot?.order || 0) - (b.pivot?.order || 0))
                .map((m: any) => buildMediaPayload(m));

            galleryImages.value = galleryMedia;
        } else {
            featuredImage.value = null;
            galleryImages.value = [];
        }
    } catch (error: any) {
        console.error('Error loading product:', error);
        const message = error.response?.data?.message || $t('Failed to load product');
        dialog.error(message);
    }
};

const collectMediaIds = () => {
    const ids: number[] = [];
    if (featuredImage.value) {
        ids.push(featuredImage.value.id);
    }
    galleryImages.value.forEach((img) => {
        if (!ids.includes(img.id)) {
            ids.push(img.id);
        }
    });
    return ids;
};

const saveProduct = async () => {
    loading.value = true;

    try {
        let descriptionHtmlValue: string | null = descriptionHtml.value || '';
        const isEmptyDescription =
            !descriptionHtmlValue ||
            descriptionHtmlValue.trim() === '' ||
            descriptionHtmlValue.trim() === '<p></p>' ||
            descriptionHtmlValue.trim() === '<p><br></p>';

        if (isEmptyDescription) {
            descriptionHtmlValue = null;
        }

        const nowIso = new Date().toISOString();

        let publishedAt: string | null = form.value.published_at || null;
        let scheduledAt: string | null = form.value.scheduled_at || null;

        if (form.value.status === 'published') {
            if (scheduledAt && new Date(scheduledAt) > new Date()) {
                form.value.status = 'draft';
            } else {
                publishedAt = publishedAt ?? nowIso;
                scheduledAt = null;
            }
        } else if (scheduledAt) {
            // keep schedule in future, clear published
            if (new Date(scheduledAt) <= new Date()) {
                publishedAt = scheduledAt;
                scheduledAt = null;
                form.value.status = 'published';
            } else {
                publishedAt = null;
            }
        } else {
            publishedAt = null;
        }

        const payload: Record<string, any> = {
            name: form.value.name,
            type: form.value.type,
            slug: form.value.slug,
            sku: form.value.sku || null,
            status: form.value.status,
            description_html: descriptionHtmlValue,
            description_blocks: descriptionBlocks.value,
            price: form.value.price,
            sale_price: form.value.sale_price || null,
            stock_quantity: form.value.stock_quantity || 0,
            stock_status: form.value.stock_status,
            manage_stock: form.value.manage_stock ? 1 : 0,
            featured: form.value.featured ? 1 : 0,
            allow_refund: form.value.allow_refund ? 1 : 0,
            refund_window_days: form.value.allow_refund
                ? (form.value.refund_window_days ?? null)
                : null,
            refund_policy_note: form.value.refund_policy_note || null,
            meta_title: form.value.meta_title || null,
            meta_description: form.value.meta_description || null,
            meta_keywords: form.value.meta_keywords || null,
            categories: selectedCategories.value,
            tags: selectedTags.value.map((tag) => tag.id),
            brands: selectedBrands.value,
            media_ids: collectMediaIds(),
            published_at: publishedAt,
            scheduled_at: scheduledAt,
            layout: form.value.layout || 'default',
            template_theme: form.value.template_theme || null,
            settings: form.value.settings || {},
            service_config: form.value.service_config,
            variants: (form.value as any)._variants || [],
            attributes: (form.value as any)._attributes || [],
        };

        if (isEdit.value) {
            await axios.put(`/api/v1/products/${route.params.id}`, payload);
            await loadProduct();
            dialog.success($t('Product updated successfully'));
        } else {
            const response = await axios.post('/api/v1/products', payload);
            dialog.success($t('Product created successfully'));
            router.push({ name: 'admin.products.edit', params: { id: response.data.data.id } });
        }
    } catch (error: any) {
        console.error('Error saving product:', error);
        const message = error.response?.data?.message || $t('Failed to save product');
        dialog.error(message);
    } finally {
        loading.value = false;
    }
};

watch(
    () => route.params.id,
    async (newId) => {
        if (newId) {
            await loadProduct();
        } else {
            resetForm();
        }
    },
    { immediate: true }
);

onMounted(() => {
    registerEditorPanelComponent('layout', ThemeLayoutPanel);
    registerEditorPanelComponent('theme_template', ProductThemeTemplatePanel);
    loadProduct();
});

useGlobalSaveHotkey(() => saveProduct(), loading);
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
}

.editor-page__action--primary {
    background: #4f46e5;
    border-color: #4f46e5;
    color: white;
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
