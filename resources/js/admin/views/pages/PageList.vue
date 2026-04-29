<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('Pages') }}</h1>
            <router-link
                :to="{ name: 'admin.pages.create' }"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
                + {{ $t('New Page') }}
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    :placeholder="$t('Search pages...')"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                    @input="loadPages"
                />
                <select v-model="filters.status" @change="loadPages" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">{{ $t('All Status') }}</option>
                    <option value="draft">{{ $t('Draft') }}</option>
                    <option value="published">{{ $t('Published') }}</option>
                    <option value="archived">{{ $t('Archived') }}</option>
                </select>
            </div>
        </div>

        <!-- Pages Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Title') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Author') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Published') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="page in pages" :key="page.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a 
                                :href="getFrontendUrl(page.slug)" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 hover:underline"
                            >
                                {{ page.title }}
                            </a>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ page.slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="[
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                page.status === 'published' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                page.status === 'draft' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' :
                                'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                            ]">
                                {{ $t(page.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ page.user?.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ page.published_at ? new Date(page.published_at).toLocaleDateString() : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <router-link
                                :to="{ name: 'admin.pages.edit', params: { id: page.id } }"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-4"
                            >
                                {{ $t('Edit') }}
                            </router-link>
                            <button
                                @click="deletePage(page.id)"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                            >
                                {{ $t('Delete') }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="pages.length === 0">
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            {{ $t('No pages found.') }} <router-link :to="{ name: 'admin.pages.create' }" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">{{ $t('Create one') }}</router-link>
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
import { ref, onMounted, getCurrentInstance } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const dialog = useDialog();

const pages = ref<any[]>([]);
const filters = ref({
    search: '',
    status: '',
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
});

const loadPages = async () => {
    try {
        const params: any = {
            page: pagination.value.current_page,
            per_page: pagination.value.per_page,
            compact: 1,
            type: 'page', // Always filter by page type
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.status) params.status = filters.value.status;

        const response = await axios.get('/api/v1/posts', { params });
        pages.value = response.data.data;
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
        console.error('Error loading pages:', error);
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadPages();
};

const getFrontendUrl = (slug: string): string => {
    if (!slug) return '#';
    const baseUrl = window.location.origin;
    return `${baseUrl}/${slug}`;
};

const deletePage = async (id: number) => {
    const confirmed = await dialog.confirm({
        title: 'Delete Page',
        message: 'Are you sure you want to delete this page?',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });

    if (!confirmed) return;

    try {
        await axios.delete(`/api/v1/posts/${id}`);
        loadPages();
        dialog.success('Page deleted successfully');
    } catch (error: any) {
        console.error('Error deleting page:', error);
        const message = error.response?.data?.message || 'Failed to delete page';
        dialog.error(message);
    }
};

onMounted(() => {
    loadPages();
});
</script>
