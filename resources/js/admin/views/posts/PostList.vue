<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('Posts') || 'Posts' }}</h1>
            <router-link
                :to="{ name: 'admin.posts.create' }"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                v-if="hasPermission('posts.create')"
            >
                + {{ $t('New Post') || 'New Post' }}
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    :placeholder="$t('Search...') || 'Search posts...'"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                    @input="loadPosts"
                />
                <select v-model="filters.status" @change="loadPosts" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">{{ $t('All Status') || 'All Status' }}</option>
                    <option value="draft">{{ $t('Draft') || 'Draft' }}</option>
                    <option value="published">{{ $t('Published') || 'Published' }}</option>
                    <option value="archived">{{ $t('Archived') || 'Archived' }}</option>
                </select>
                <select v-model="filters.type" @change="loadPosts" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">{{ $t('All Types') || 'All Types' }}</option>
                    <option value="post">{{ $t('Post') || 'Post' }}</option>
                    <option value="page">{{ $t('Page') || 'Page' }}</option>
                    <option value="news">{{ $t('News') || 'News' }}</option>
                </select>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Title') || 'Title' }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Type') || 'Type' }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Status') || 'Status' }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Author') || 'Author' }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Published') || 'Published' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Actions') || 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="post in posts" :key="post.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a 
                                :href="post.frontend_url" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 hover:underline"
                            >
                                {{ post.title }}
                            </a>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ post.slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                {{ $t(post.type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="[
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                post.status === 'published' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                post.status === 'draft' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' :
                                'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                            ]">
                                {{ $t(post.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ post.user?.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <router-link
                                :to="{ name: 'admin.posts.edit', params: { id: post.id }, query: { type: post.type } }"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-4"
                            >
                                {{ $t('Edit') }}
                            </router-link>
                            <button
                                @click="deletePost(post.id)"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                            >
                                {{ $t('Delete') }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="posts.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            {{ $t('No posts found.') }} <router-link :to="{ name: 'admin.posts.create' }" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">{{ $t('Create one') }}</router-link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                {{ $t('Showing') }} {{ pagination.from }} {{ $t('to') }} {{ pagination.to }} {{ $t('of') }} {{ pagination.total }} {{ $t('results') }}
            </div>
            <div class="flex space-x-2">
                <button
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                    {{ $t('Previous') }}
                </button>
                <button
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                    {{ $t('Next') || 'Next' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, getCurrentInstance, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import { useAuthStore } from '../../stores/auth';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const dialog = useDialog();
const authStore = useAuthStore();

const hasPermission = (permission: string) => {
    // Temporary implementation: Admins have all permissions
    return authStore.isAdmin;
};

const posts = ref<any[]>([]);
const filters = ref({
    search: '',
    status: '',
    type: '',
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
});

const loadPosts = async () => {
    try {
        const params: any = {
            page: pagination.value.current_page,
            per_page: pagination.value.per_page,
            compact: 1,
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.status) params.status = filters.value.status;
        if (filters.value.type) params.type = filters.value.type;

        const response = await axios.get('/api/v1/posts', { params });
        posts.value = response.data.data;
        const meta = response.data.meta;
        pagination.value = {
            current_page: meta.current_page,
            last_page: meta.last_page,
            per_page: meta.per_page,
            total: Array.isArray(meta.total) ? meta.total[0] : meta.total,
            from: meta.from,
            to: meta.to,
        };
    } catch (error) {
        console.error('Error loading posts:', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadPosts();
};

const deletePost = async (id: number) => {
    const confirmed = await dialog.confirm({
        title: 'Delete Post',
        message: 'Are you sure you want to delete this post?',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });

    if (!confirmed) return;

    try {
        await axios.delete(`/api/v1/posts/${id}`);
        loadPosts();
        dialog.success('Post deleted successfully');
    } catch (error: any) {
        console.error('Error deleting post:', error);
        const message = error.response?.data?.message || 'Failed to delete post';
        dialog.error(message);
    }
};

const createRoute = computed(() => ({
    name: 'admin.posts.create',
    ...(filters.value.type ? { query: { type: filters.value.type } } : {}),
}));

onMounted(() => {
    loadPosts();
});
</script>
