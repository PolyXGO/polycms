<template>
    <div class="editor-page">
        <header class="editor-page__header">
            <h1 class="editor-page__title">
                {{ isEdit
                    ? (panelType === 'page' ? ($t('Edit Page') || 'Edit Page') : ($t('Edit Post') || 'Edit Post'))
                    : (panelType === 'page' ? ($t('New Page') || 'New Page') : ($t('New Post') || 'New Post'))
                }}
            </h1>
            <div class="editor-page__actions">
                <a
                    v-if="isEdit && form.slug"
                    :href="getViewUrl()"
                    target="_blank"
                    class="editor-page__action editor-page__action--primary"
                >
                    {{ panelType === 'page' ? ($t('View Page') || 'View Page') : ($t('View Post') || 'View Post') }}
                </a>
                <button type="button" class="editor-page__action" @click="router.back()">
                    Cancel
                </button>
            </div>
        </header>

        <form class="editor-form" @submit.prevent="savePost">
            <EditorPanelLayout :type="panelType" :components="panelComponents" />
        </form>

        <!-- Hook Target for Plugins/Themes -->
        <div id="admin-post-form-after-editor"></div>

        <MediaPicker ref="mediaPickerRef" :multiple="false" :accepted-types="['image']" @select="handleMediaSelect" />

        <div class="editor-floating-actions" :style="floatingActionsStyle">
            <button 
                type="button" 
                class="editor-floating-actions__primary" 
                :disabled="loading" 
                @click="savePost"
                :title="loading 
                    ? ($t('Saving…') || 'Saving…') 
                    : (panelType === 'page' ? ($t('Save Page') || 'Save Page') : ($t('Save Post') || 'Save Post'))"
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
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore - Vue SFC typing provided via shim
import MediaPicker from '../../components/MediaPicker';
import { useSlugify } from '../../composables/useSlugify';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import { usePermalinkSettings } from '../../composables/usePermalinkSettings';
import { useAuthStore } from '../../stores/auth';
import EditorPanelLayout from '../../components/editor/EditorPanelLayout.vue';
import PostPrimaryPanel from '../../components/editor/panels/post/PostPrimaryPanel.vue';
import PostExcerptPanel from '../../components/editor/panels/post/PostExcerptPanel.vue';
import PostCategoriesPanel from '../../components/editor/panels/post/PostCategoriesPanel.vue';
import PostTagsPanel from '../../components/editor/panels/post/PostTagsPanel.vue';
import PostFeaturedImagePanel from '../../components/editor/panels/post/PostFeaturedImagePanel.vue';
import PostPublishPanel from '../../components/editor/panels/post/PostPublishPanel.vue';
import PostSeoPanel from '../../components/editor/panels/post/PostSeoPanel.vue';
import PostTemplatePanel from '../../components/editor/panels/post/PostTemplatePanel.vue';
import PostThemeTemplatePanel from '../../components/editor/panels/post/PostThemeTemplatePanel.vue';
import ThemeLayoutPanel from '../../components/editor/panels/shared/ThemeLayoutPanel.vue';
import { EditorContextKey, createEditorContext } from '../../editor/context';
import { registerEditorPanelComponent } from '../../editor/panelRegistry';
import { useGlobalSaveHotkey } from '../../composables/useGlobalSaveHotkey';

import type { TagItem } from '../../components/editor/panels/shared/TagSelector.vue';
import LandingBlockOptionsPanel from '../../components/editor/panels/LandingBlockOptionsPanel.vue';
import { useLandingStore } from '../../stores/landingStore';

const landingStore = useLandingStore();

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

const props = defineProps<{ defaultType?: string }>();

const extractRouteType = () => {
    // Check route name first (for pages routes)
    if (route.name && typeof route.name === 'string') {
        if (route.name.startsWith('admin.pages.')) {
            return 'page';
        }
    }
    // Then check query parameter
    if (typeof route.query.type === 'string') {
        return route.query.type;
    }
    return null;
};

const initialPanelType = props.defaultType ?? extractRouteType() ?? 'post';

const isEdit = computed(() => !!route.params.id);

const loading = ref(false);
const contentHtml = ref<string>('');
const contentRaw = ref<any>(null);
const slugManuallyEdited = ref(false);
const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const featuredImage = ref<MediaItem | null>(null);
const selectedCategories = ref<number[]>([]);
const selectedTags = ref<TagItem[]>([]);
const metaFields = ref<Record<string, string | null>>({});
const savedFrontendUrl = ref<string | null>(null);

const panelType = ref<string>(initialPanelType);

const defaultFormState = (type?: string) => ({
    title: '',
    slug: '',
    type: type ?? panelType.value ?? 'post',
    status: 'draft',
    excerpt: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    created_at: null as string | null,
    updated_at: null as string | null,
    published_at: null as string | null,
    scheduled_at: null as string | null,
    layout: 'default',
    layout_template_id: null as number | null,
    template_theme: null as string | null,
    show_featured_image: true,
});

const form = ref(defaultFormState(panelType.value));

const panelComponents = {
    primary: PostPrimaryPanel,
    excerpt: PostExcerptPanel,
    categories: PostCategoriesPanel,
    tags: PostTagsPanel,
    featured_image: PostFeaturedImagePanel,
    publish: PostPublishPanel,
    seo: PostSeoPanel,
    theme_template: PostThemeTemplatePanel,
    template: PostTemplatePanel,
    layout: ThemeLayoutPanel,
};

const editorState = {
    contentHtml,
    contentRaw,
    featuredImage,
    selectedCategories,
    selectedTags,
    metaFields,
    postTypes: [
        { label: 'Post', value: 'post' },
        { label: 'Page', value: 'page' },
        { label: 'News', value: 'news' },
    ],
    postStatuses: [
        { label: 'Draft', value: 'draft' },
        { label: 'Published', value: 'published' },
        { label: 'Archived', value: 'archived' },
    ],
};

const resetForm = () => {
    Object.assign(form.value, defaultFormState(panelType.value));
    contentHtml.value = '';
    contentRaw.value = null;
    featuredImage.value = null;
    slugManuallyEdited.value = false;
    selectedCategories.value = [];
    selectedTags.value = [];
    metaFields.value = {};
};

const generateSlug = () => {
    if (isEdit.value) {
        return;
    }
    const freshSlug = form.value.title ? slugify(form.value.title) : '';
    form.value.slug = freshSlug;
    slugManuallyEdited.value = false;
};

const onSlugInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const inputValue = target.value;

    if (!inputValue || inputValue.trim() === '') {
        if (form.value.title) {
            form.value.slug = slugify(form.value.title);
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

const isFlexidocsTemplate = computed(() => {
    const tt = form.value.template_theme || '';
    return tt === 'flexidocs' || tt.startsWith('flexidocs::');
});

const getPermalink = (): string => {
    if (!form.value.slug) return '';
    const baseUrl = window.location.origin;

    // Real-time preview for flexidocs
    if (isFlexidocsTemplate.value) {
        // Find if a flexidocs category is selected
        const hasFlexiDocCat = false; // We could resolve this deep, but for preview /docs/ is standard
        return `${baseUrl}/docs/${form.value.slug}`;
    }

    // Use backend-computed frontend_url if available
    if (savedFrontendUrl.value) {
        return baseUrl + savedFrontendUrl.value;
    }
    
    const type = form.value.type === 'page' ? 'page' : 'post';
    return buildUrl(type, form.value.slug);
};

const getViewUrl = (): string => {
    return getPermalink();
};

const openMediaPicker = () => {
    mediaPickerRef.value?.open();
};

const handleMediaSelect = (media: MediaItem | MediaItem[]) => {
    if (Array.isArray(media)) {
        featuredImage.value = media[0] || null;
    } else {
        featuredImage.value = media;
    }
};

const removeFeaturedImage = () => {
    featuredImage.value = null;
};

const panelContext = createEditorContext({
    type: 'post',
    form,
    loading,
    helpers: {
        generateSlug,
        onSlugInput,
        getPermalink,
        openMediaPicker,
        removeFeaturedImage,
        save: () => savePost(),
        preview: () => {
            const url = getPermalink();
            if (url) {
                // Add preview token for admin to view draft posts
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

const loadPost = async () => {
    if (!route.params.id) return;

    try {
        const response = await axios.get(`/api/v1/posts/${route.params.id}`);
        const post = response.data.data;
        Object.assign(form.value, defaultFormState(post.type || panelType.value), {
            title: post.title || '',
            slug: post.slug || '',
            type: post.type || panelType.value,
            status: post.status || 'draft',
            excerpt: post.excerpt || '',
            meta_title: post.meta?.title || '',
            meta_description: post.meta?.description || '',
            meta_keywords: post.meta?.keywords || '',
            created_at: post.created_at || null,
            updated_at: post.updated_at || null,
            published_at: post.published_at || null,
            scheduled_at: post.scheduled_at || null,
            layout: post.layout || 'default',
            layout_template_id: post.layout_template_id || null,
            template_theme: post.template_theme || null,
            show_featured_image: post.show_featured_image ?? true,
        });
        // Store the backend-computed frontend_url (respects theme URL hooks)
        savedFrontendUrl.value = post.frontend_url || null;
        contentHtml.value = post.content_html || '';
        contentRaw.value = post.content_raw || null;
        // Set panel type from post type, but don't override if we're on a pages route
        const postType = post.type || 'post';
        if (route.name && typeof route.name === 'string' && route.name.startsWith('admin.pages.')) {
            // If we're on a pages route, ensure type is page
            panelType.value = 'page';
            form.value.type = 'page';
        } else {
            panelType.value = postType;
        }
        selectedCategories.value = Array.isArray(post.categories) ? post.categories.map((cat: any) => cat.id) : [];
        selectedTags.value = Array.isArray(post.tags)
            ? post.tags.map((tag: any) => ({ id: tag.id, name: tag.name }))
            : [];
        metaFields.value = post.meta_fields && typeof post.meta_fields === 'object' ? { ...post.meta_fields } : {};

        if (post.featured_image) {
            const buildFeaturedPayload = (media: any) => ({
                id: media?.id ?? 0,
                name: media?.name ?? 'Featured Image',
                file_name: media?.file_name ?? post.featured_image.split('/').pop() ?? '',
                url: media?.url ?? post.featured_image,
                type: media?.type ?? 'image',
                size: media?.size ?? 0,
                created_at: media?.created_at ?? '',
                width: media?.width,
                height: media?.height,
            });

            try {
                const mediaResponse = await axios.get('/api/v1/media', {
                    params: { search: post.featured_image.split('/').pop() },
                });
                if (mediaResponse.data.data && mediaResponse.data.data.length > 0) {
                    const media = mediaResponse.data.data[0];
                    featuredImage.value = buildFeaturedPayload(media);
                } else {
                    featuredImage.value = buildFeaturedPayload(null);
                }
            } catch (error) {
                featuredImage.value = buildFeaturedPayload(null);
            }
        } else {
            featuredImage.value = null;
        }
    } catch (error: any) {
        console.error('Error loading post:', error);
        const message = error.response?.data?.message || 'Failed to load post';
        dialog.error(message);
    }
};

const savePost = async () => {
    loading.value = true;

    try {
        const isEmptyContent =
            !contentHtml.value ||
            contentHtml.value.trim() === '' ||
            contentHtml.value.trim() === '<p></p>' ||
            contentHtml.value.trim() === '<p><br></p>';

        const nowIso = new Date().toISOString();

        let publishedAt: string | null = form.value.published_at || null;
        let scheduledAt: string | null = form.value.scheduled_at || null;

        if (form.value.status === 'published') {
            if (scheduledAt && new Date(scheduledAt) > new Date()) {
                // if scheduled in future but marked published, treat as draft until publish time
                form.value.status = 'draft';
            } else {
                publishedAt = publishedAt ?? nowIso;
                scheduledAt = null;
            }
        } else if (scheduledAt) {
            // keep as provided
            publishedAt = null;
        } else {
            publishedAt = null;
        }

        const payload: any = {
            title: form.value.title,
            slug: form.value.slug,
            type: form.value.type,
            status: form.value.status,
            excerpt: form.value.excerpt || null,
            content_html: isEmptyContent ? null : contentHtml.value,
            content_raw: contentRaw.value,
            featured_image: featuredImage.value ? featuredImage.value.url : null,
            meta_title: form.value.meta_title || null,
            meta_description: form.value.meta_description || null,
            meta_keywords: form.value.meta_keywords || null,
            categories: selectedCategories.value,
            tags: selectedTags.value.map((tag) => tag.id),
            published_at: publishedAt,
            scheduled_at: scheduledAt,
            layout: form.value.layout || 'default',
            layout_template_id: form.value.layout_template_id || null,
            template_theme: form.value.template_theme || null,
            show_featured_image: form.value.show_featured_image ?? true,
            meta_fields: Object.keys(metaFields.value).length > 0 ? metaFields.value : undefined,
        };

        if (isEdit.value) {
            await axios.put(`/api/v1/posts/${route.params.id}`, payload);
            await loadPost();
            dialog.success($t('Post updated successfully') || 'Post updated successfully');
        } else {
            const response = await axios.post('/api/v1/posts', payload);
            dialog.success($t('Post created successfully') || 'Post created successfully');
            const editRouteName = form.value.type === 'page' ? 'admin.pages.edit' : 'admin.posts.edit';
            router.push({
                name: editRouteName,
                params: { id: response.data.data.id },
                query: { type: form.value.type },
            });
        }
    } catch (error: any) {
        const errorMessage = panelType.value === 'page'
            ? ($t('Failed to save page') || 'Failed to save page')
            : ($t('Failed to save post') || 'Failed to save post');
        const validationErrors = error.response?.data?.errors;
        let message = error.response?.data?.message || errorMessage;
        if (validationErrors) {
            const details = Object.values(validationErrors).flat().join('\n');
            message += '\n' + details;
        }
        dialog.error(message);
    } finally {
        loading.value = false;
    }
};

watch(
    () => route.params.id,
    (newId) => {
        if (newId) {
            void loadPost();
        } else {
            resetForm();
        }
    }
);

watch(
    () => route.query.type,
    (value) => {
        if (isEdit.value) {
            return;
        }
        const normalized = typeof value === 'string' ? value : null;
        if (normalized && normalized !== panelType.value) {
            panelType.value = normalized;
        }
    }
);

watch(
    () => form.value.type,
    (value) => {
        if (typeof value === 'string' && value && value !== panelType.value) {
            panelType.value = value;
        }
    }
);

watch(
    panelType,
    (value) => {
        if (!isEdit.value && form.value.type !== value) {
            form.value.type = value;
        }
        landingStore.setPostType(value);
    },
    { immediate: true }
);

onMounted(() => {
    registerEditorPanelComponent('layout', ThemeLayoutPanel);
    registerEditorPanelComponent('template', PostTemplatePanel);
    registerEditorPanelComponent('theme_template', PostThemeTemplatePanel);
    loadPost();
});

useGlobalSaveHotkey(() => savePost(), loading);
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
