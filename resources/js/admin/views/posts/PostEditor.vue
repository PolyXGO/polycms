<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ isEdit ? $t('Edit Post') || 'Edit Post' : $t('Create Post') || 'Create New Post' }}
            </h1>
            <div class="flex items-center gap-3">
                <a
                    v-if="isEdit && form.slug"
                    :href="getPermalink()"
                    target="_blank"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    {{ $t('View Page') || 'View Page' }}
                </a>
                <button
                    @click="router.back()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                >
                    {{ $t('Cancel') }}
                </button>
            </div>
        </div>

        <form @submit.prevent="savePost" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Basic Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @input="generateSlug"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug *</label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @input="onSlugInput"
                            @paste="onSlugInput"
                        />
                        <div v-if="form.slug" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-medium">{{ $t('Permalink:') || 'Permalink:' }}</span>
                            <a 
                                :href="getPermalink()" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="ml-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 hover:underline"
                            >
                                {{ getPermalink() }}
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                        <select v-model="form.type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="post">Post</option>
                            <option value="page">Page</option>
                            <option value="news">News</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                        <select v-model="form.status" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excerpt</label>
                        <textarea
                            v-model="form.excerpt"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                    </div>
                </div>
            </div>

            <!-- Featured Image -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Featured Image</h2>
                <div class="flex items-center gap-4">
                    <div v-if="featuredImage" class="relative w-32 h-32 border-2 border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                        <img :src="featuredImage.url" :alt="featuredImage.name" class="w-full h-full object-cover" />
                        <button
                            @click="removeFeaturedImage"
                            type="button"
                            class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <button
                        @click="openMediaPicker"
                        type="button"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                    >
                        {{ featuredImage ? 'Change Image' : 'Select Image' }}
                    </button>
                </div>
            </div>

            <!-- Content Editor -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Content</h2>
                <TiptapEditor v-model="contentHtml" :placeholder="$t('Start typing your content...') || 'Start typing your content...'" />
            </div>

            <!-- SEO Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">SEO Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                        <input v-model="form.meta_title" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Keywords</label>
                        <input v-model="form.meta_keywords" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                        <textarea
                            v-model="form.meta_description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    @click="router.back()"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                >
                    {{ $t('Cancel') }}
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ loading ? $t('Saving') : $t('Save Post') }}
                </button>
            </div>
        </form>

        <!-- Media Picker -->
        <MediaPicker
            ref="mediaPickerRef"
            :multiple="false"
            :accepted-types="['image']"
            @select="handleMediaSelect"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, getCurrentInstance } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import TiptapEditor from '../../components/TiptapEditor.vue';
import MediaPicker from '../../components/MediaPicker.vue';
import { useSlugify } from '../../composables/useSlugify';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';

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

const isEdit = computed(() => !!route.params.id);

const loading = ref(false);
const contentHtml = ref<string>('');
const slugManuallyEdited = ref(false);
const mediaPickerRef = ref<InstanceType<typeof MediaPicker> | null>(null);
const featuredImage = ref<MediaItem | null>(null);
const form = ref({
    title: '',
    slug: '',
    type: 'post',
    status: 'draft',
    excerpt: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

const generateSlug = () => {
    // Auto-generate slug from title if slug is empty or hasn't been manually edited
    if (form.value.title) {
        const titleSlug = slugify(form.value.title);
        if (!form.value.slug || (!slugManuallyEdited.value && form.value.slug === titleSlug)) {
            form.value.slug = titleSlug;
            slugManuallyEdited.value = false;
        }
    }
};

const onSlugInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const inputValue = target.value;
    
    // If slug is empty, auto-generate from title
    if (!inputValue || inputValue.trim() === '') {
        if (form.value.title) {
            form.value.slug = slugify(form.value.title);
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
    
    // Determine URL based on post type
    if (form.value.type === 'page') {
        return `${baseUrl}/${form.value.slug}`;
    } else if (form.value.type === 'post') {
        return `${baseUrl}/posts/${form.value.slug}`;
    } else {
        return `${baseUrl}/posts/${form.value.slug}`;
    }
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

const loadPost = async () => {
    if (!isEdit.value) return;

    try {
        const response = await axios.get(`/api/v1/posts/${route.params.id}`);
        const post = response.data.data;
        form.value = {
            title: post.title,
            slug: post.slug,
            type: post.type,
            status: post.status,
            excerpt: post.excerpt || '',
            meta_title: post.meta?.title || '',
            meta_description: post.meta?.description || '',
            meta_keywords: post.meta?.keywords || '',
        };
        // Use content_html if available, otherwise fallback to content_raw blocks
        contentHtml.value = post.content_html || '';

        // Load featured image
        if (post.featured_image) {
            // Try to find media by URL
            try {
                const mediaResponse = await axios.get('/api/v1/media', {
                    params: { search: post.featured_image.split('/').pop() }
                });
                if (mediaResponse.data.data && mediaResponse.data.data.length > 0) {
                    const media = mediaResponse.data.data[0];
                    featuredImage.value = {
                        id: media.id,
                        name: media.name,
                        file_name: media.file_name,
                        url: media.url,
                        type: media.type,
                        size: media.size,
                        created_at: media.created_at,
                        width: media.width,
                        height: media.height,
                    };
                } else {
                    // If not found in media, create a placeholder object
                    featuredImage.value = {
                        id: 0,
                        name: 'Featured Image',
                        file_name: post.featured_image.split('/').pop() || '',
                        url: post.featured_image,
                        type: 'image',
                        size: 0,
                        created_at: '',
                    };
                }
            } catch (error) {
                // If error, create placeholder
                featuredImage.value = {
                    id: 0,
                    name: 'Featured Image',
                    file_name: post.featured_image.split('/').pop() || '',
                    url: post.featured_image,
                    type: 'image',
                    size: 0,
                    created_at: '',
                };
            }
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
        // Prepare data - convert empty strings to null for nullable fields
        // Check if content_html is empty or just empty paragraph
        const isEmptyContent = !contentHtml.value || 
            contentHtml.value.trim() === '' || 
            contentHtml.value.trim() === '<p></p>' || 
            contentHtml.value.trim() === '<p><br></p>';
        
        const data: any = {
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
        };

        if (isEdit.value) {
            await axios.put(`/api/v1/posts/${route.params.id}`, data);
            // Reload post data to reflect changes, but stay on edit page
            await loadPost();
            dialog.success($t('Post updated successfully') || 'Post updated successfully');
        } else {
            const response = await axios.post('/api/v1/posts', data);
            // Redirect to edit page after creating
            dialog.success($t('Post created successfully') || 'Post created successfully');
            router.push({ 
                name: 'admin.posts.edit', 
                params: { id: response.data.data.id }
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

onMounted(() => {
    loadPost();
});
</script>
