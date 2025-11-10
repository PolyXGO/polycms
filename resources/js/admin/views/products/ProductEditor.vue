<template>
    <div class="editor-page">
        <header class="editor-page__header">
            <h1 class="editor-page__title">
                {{ isEdit ? ($t('Edit Product') || 'Edit Product') : ($t('Create Product') || 'Create New Product') }}
            </h1>
            <div class="editor-page__actions">
                <a
                    v-if="isEdit && form.slug"
                    :href="getPermalink()"
                    target="_blank"
                    class="editor-page__action editor-page__action--primary"
                >
                    View Product
                </a>
                <button type="button" class="editor-page__action" @click="router.back()">
                    Cancel
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

        <div class="editor-floating-actions">
            <button type="button" class="editor-floating-actions__primary" :disabled="loading" @click="saveProduct">
                {{ loading ? 'Saving…' : 'Save Product' }}
            </button>
            <button type="button" class="editor-floating-actions__secondary" @click="router.back()">
                Cancel
            </button>
        </div>
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
import EditorPanelLayout from '../../components/editor/EditorPanelLayout.vue';
import ProductPrimaryPanel from '../../components/editor/panels/product/ProductPrimaryPanel.vue';
import ProductPricingPanel from '../../components/editor/panels/product/ProductPricingPanel.vue';
import ProductMediaPanel from '../../components/editor/panels/product/ProductMediaPanel.vue';
import ProductCategoriesPanel from '../../components/editor/panels/product/ProductCategoriesPanel.vue';
import ProductTagsPanel from '../../components/editor/panels/product/ProductTagsPanel.vue';
import ProductBrandsPanel from '../../components/editor/panels/product/ProductBrandsPanel.vue';
import ProductPublishPanel from '../../components/editor/panels/product/ProductPublishPanel.vue';
import ProductSeoPanel from '../../components/editor/panels/product/ProductSeoPanel.vue';
import { EditorContextKey, createEditorContext } from '../../editor/context';
import type { TagItem } from '../../components/editor/panels/shared/TagSelector.vue';

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
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    created_at: null as string | null,
    updated_at: null as string | null,
});

const form = ref(defaultFormState());

const panelComponents = {
    primary: ProductPrimaryPanel,
    pricing: ProductPricingPanel,
    media: ProductMediaPanel,
    categories: ProductCategoriesPanel,
    tags: ProductTagsPanel,
    brands: ProductBrandsPanel,
    publish: ProductPublishPanel,
    seo: ProductSeoPanel,
};

const descriptionHtml = ref('');

const editorState = {
    descriptionHtml,
    featuredImage,
    galleryImages,
    productCategories: selectedCategories,
    productTags: selectedTags,
    productBrands: selectedBrands,
};

const resetForm = () => {
    Object.assign(form.value, defaultFormState(form.value.type));
    descriptionHtml.value = '';
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
                window.open(url, '_blank');
            }
        },
    },
    state: editorState,
});

provide(EditorContextKey, panelContext);

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
            meta_title: product.meta?.title || '',
            meta_description: product.meta?.description || '',
            meta_keywords: product.meta?.keywords || '',
            created_at: product.created_at || null,
            updated_at: product.updated_at || null,
        });
        descriptionHtml.value = product.description_html || '';
        form.value.type = product.type || 'product';

        selectedCategories.value = Array.isArray(product.categories)
            ? product.categories
                  .map((category: any) => Number(category.id))
                  .filter((id) => Number.isFinite(id))
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
                  .filter((id) => Number.isFinite(id))
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
        const message = error.response?.data?.message || 'Failed to load product';
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
        
        const payload: Record<string, any> = {
            name: form.value.name,
            slug: form.value.slug,
            sku: form.value.sku || null,
            status: form.value.status,
            description_html: descriptionHtmlValue,
            price: form.value.price,
            sale_price: form.value.sale_price || null,
            stock_quantity: form.value.stock_quantity || 0,
            stock_status: form.value.stock_status,
            manage_stock: form.value.manage_stock ? 1 : 0,
            featured: form.value.featured ? 1 : 0,
            meta_title: form.value.meta_title || null,
            meta_description: form.value.meta_description || null,
            meta_keywords: form.value.meta_keywords || null,
            categories: selectedCategories.value,
            tags: selectedTags.value.map((tag) => tag.id),
            brands: selectedBrands.value,
            media_ids: collectMediaIds(),
        };

        if (isEdit.value) {
            await axios.put(`/api/v1/products/${route.params.id}`, payload);
            await loadProduct();
            dialog.success('Product updated successfully');
        } else {
            const response = await axios.post('/api/v1/products', payload);
            dialog.success('Product created successfully');
            router.push({ name: 'admin.products.edit', params: { id: response.data.data.id } });
        }
    } catch (error: any) {
        console.error('Error saving product:', error);
        const message = error.response?.data?.message || 'Failed to save product';
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
    loadProduct();
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
}

.editor-floating-actions__primary {
    padding: 0.65rem 1.35rem;
    border-radius: 0.85rem;
    background: #4f46e5;
    color: #ffffff;
    border: none;
    font-weight: 600;
    box-shadow: 0 10px 30px rgba(79, 70, 229, 0.25);
    cursor: pointer;
}

.editor-floating-actions__secondary {
    padding: 0.55rem 1.2rem;
    border-radius: 0.85rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #0f172a;
    font-weight: 600;
    cursor: pointer;
}

@media (min-width: 1024px) {
    .editor-floating-actions {
        display: flex;
    }
}
</style>
