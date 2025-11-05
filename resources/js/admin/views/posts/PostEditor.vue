<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ isEdit ? 'Edit Post' : 'Create New Post' }}
            </h1>
            <button
                @click="router.back()"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
            >
                Cancel
            </button>
        </div>

        <form @submit.prevent="savePost" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        />
                    </div>
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

            <!-- Block Editor -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Content</h2>
                <BlockEditor v-model="contentBlocks" />
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
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ loading ? 'Saving...' : 'Save Post' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import BlockEditor from '../../blocks/BlockEditor.vue';
import { useSlugify } from '../../composables/useSlugify';
import { useDialog } from '../../composables/useDialog';

const router = useRouter();
const route = useRoute();
const { slugify } = useSlugify();
const dialog = useDialog();

const isEdit = computed(() => !!route.params.id);

const loading = ref(false);
const contentBlocks = ref<any[]>([]);
const slugManuallyEdited = ref(false);
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
    // Only auto-generate if slug is empty or hasn't been manually edited
    if (!slugManuallyEdited.value && form.value.title) {
        form.value.slug = slugify(form.value.title);
    }
};

const onSlugInput = () => {
    // Mark slug as manually edited when user types in slug field
    slugManuallyEdited.value = true;
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
            meta_title: post.meta_title || '',
            meta_description: post.meta_description || '',
            meta_keywords: post.meta_keywords || '',
        };
        contentBlocks.value = post.content_raw?.blocks || [];
    } catch (error: any) {
        console.error('Error loading post:', error);
        const message = error.response?.data?.message || 'Failed to load post';
        dialog.error(message);
    }
};

const savePost = async () => {
    loading.value = true;

    try {
        const data = {
            ...form.value,
            content_raw: { blocks: contentBlocks.value },
        };

        if (isEdit.value) {
            await axios.put(`/api/v1/posts/${route.params.id}`, data);
        } else {
            await axios.post('/api/v1/posts', data);
        }

        router.push({ name: 'admin.posts.index' });
        dialog.success('Post saved successfully');
    } catch (error: any) {
        console.error('Error saving post:', error);
        const message = error.response?.data?.message || 'Failed to save post';
        dialog.error(message);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadPost();
});
</script>
