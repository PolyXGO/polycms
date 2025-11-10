<template>
    <div class="editor-page">
        <header class="editor-page__header">
            <h1 class="editor-page__title">
                {{ isEdit ? ($t('Edit Post') || 'Edit Post') : ($t('Create Post') || 'Create New Post') }}
            </h1>
            <div class="editor-page__actions">
                <a
                    v-if="isEdit && form.slug"
                    :href="getPermalink()"
                    target="_blank"
                    class="editor-page__action editor-page__action--primary"
                >
                    View Page
                </a>
                <button type="button" class="editor-page__action" @click="router.back()">
                    Cancel
                </button>
            </div>
        </header>

        <form class="editor-form" @submit.prevent="savePost">
            <EditorPanelLayout :type="panelType" :components="panelComponents" />
        </form>

        <MediaPicker ref="mediaPickerRef" :multiple="false" :accepted-types="['image']" @select="handleMediaSelect" />

        <div class="editor-floating-actions">
            <button type="button" class="editor-floating-actions__primary" :disabled="loading" @click="savePost">
                {{ loading ? 'Saving…' : 'Save Post' }}
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
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore - Vue SFC typing provided via shim
import MediaPicker from '../../components/MediaPicker';
import { useSlugify } from '../../composables/useSlugify';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import { usePermalinkSettings } from '../../composables/usePermalinkSettings';
import EditorPanelLayout from '../../components/editor/EditorPanelLayout.vue';
import PostPrimaryPanel from '../../components/editor/panels/post/PostPrimaryPanel.vue';
import PostExcerptPanel from '../../components/editor/panels/post/PostExcerptPanel.vue';
import PostCategoriesPanel from '../../components/editor/panels/post/PostCategoriesPanel.vue';
import PostTagsPanel from '../../components/editor/panels/post/PostTagsPanel.vue';
import PostFeaturedImagePanel from '../../components/editor/panels/post/PostFeaturedImagePanel.vue';
import PostPublishPanel from '../../components/editor/panels/post/PostPublishPanel.vue';
import PostSeoPanel from '../../components/editor/panels/post/PostSeoPanel.vue';
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

const props = defineProps<{ defaultType?: string }>();

const extractRouteType = () => (typeof route.query.type === 'string' ? route.query.type : null);

const initialPanelType = props.defaultType ?? extractRouteType() ?? 'post';

const isEdit = computed(() => !!route.params.id);

const loading = ref(false);
const contentHtml = ref<string>('');
const slugManuallyEdited = ref(false);
const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const featuredImage = ref<MediaItem | null>(null);
const selectedCategories = ref<number[]>([]);
const selectedTags = ref<TagItem[]>([]);

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
};

const editorState = {
    contentHtml,
    featuredImage,
    selectedCategories,
    selectedTags,
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
    featuredImage.value = null;
    slugManuallyEdited.value = false;
    selectedCategories.value = [];
    selectedTags.value = [];
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

const getPermalink = (): string => {
    if (!form.value.slug) return '';
    const type = form.value.type === 'page' ? 'page' : 'post';
    return buildUrl(type, form.value.slug);
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
                window.open(url, '_blank');
            }
        },
    },
    state: editorState,
});

provide(EditorContextKey, panelContext);

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
        });
        contentHtml.value = post.content_html || '';
        panelType.value = post.type || 'post';
        selectedCategories.value = Array.isArray(post.categories) ? post.categories.map((cat: any) => cat.id) : [];
        selectedTags.value = Array.isArray(post.tags)
            ? post.tags.map((tag: any) => ({ id: tag.id, name: tag.name }))
            : [];

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

        const payload: any = {
            title: form.value.title,
            slug: form.value.slug,
            type: form.value.type,
            status: form.value.status,
            excerpt: form.value.excerpt || null,
            content_html: isEmptyContent ? null : contentHtml.value,
            featured_image: featuredImage.value ? featuredImage.value.url : null,
            meta_title: form.value.meta_title || null,
            meta_description: form.value.meta_description || null,
            meta_keywords: form.value.meta_keywords || null,
            categories: selectedCategories.value,
            tags: selectedTags.value.map((tag) => tag.id),
        };

        if (isEdit.value) {
            await axios.put(`/api/v1/posts/${route.params.id}`, payload);
            await loadPost();
            dialog.success($t('Post updated successfully') || 'Post updated successfully');
        } else {
            const response = await axios.post('/api/v1/posts', payload);
            dialog.success($t('Post created successfully') || 'Post created successfully');
            router.push({
                name: 'admin.posts.edit',
                params: { id: response.data.data.id },
                query: { type: form.value.type },
            });
        }
    } catch (error: any) {
        console.error('Error saving post:', error);
        const message = error.response?.data?.message || ($t('Failed to save post') || 'Failed to save post');
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
    }
);

onMounted(() => {
    loadPost();
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
