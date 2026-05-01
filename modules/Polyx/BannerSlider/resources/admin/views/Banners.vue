<template>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Banners</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage promotional banners for topbar display</p>
            </div>
            <router-link
                :to="{ name: 'admin.banner-slider.banners.create' }"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
                + New Banner
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search banners..."
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                    @input="loadBanners"
                />
                <select v-model="filters.active" @change="loadBanners" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="true">Active</option>
                    <option value="false">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Banners Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Preview</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Link</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="banner in banners" :key="banner.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="[
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                (banner.type || 'image') === 'text' ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' :
                                'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200'
                            ]">
                                {{ (banner.type || 'image') === 'text' ? 'Text' : 'Image' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <img
                                v-if="banner.image_url"
                                :src="banner.image_url"
                                :alt="banner.title || 'Banner'"
                                class="w-20 h-12 object-cover rounded"
                            />
                            <div v-else-if="(banner.type || 'image') === 'text'" class="w-20 h-12 rounded flex items-center justify-center text-xs" :style="{
                                background: banner.gradient_color ? `linear-gradient(${banner.gradient_degree || 135}deg, ${banner.background_color || '#ffffff'}, ${banner.gradient_color})` : (banner.background_color || '#ffffff'),
                                color: banner.text_color || '#000000'
                            }">
                                Text
                            </div>
                            <div v-else class="w-20 h-12 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ banner.title || '(No title)' }}
                            </div>
                            <div v-if="banner.description" class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                {{ banner.description }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a
                                v-if="banner.link"
                                :href="banner.link"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 hover:underline truncate max-w-xs block"
                            >
                                {{ banner.link }}
                            </a>
                            <span v-else class="text-sm text-gray-400">—</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ banner.order }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="[
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                banner.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                            ]">
                                {{ banner.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div v-if="banner.start_date || banner.end_date" class="text-xs">
                                <div v-if="banner.start_date">
                                    From: {{ formatDate(banner.start_date) }}
                                </div>
                                <div v-if="banner.end_date">
                                    To: {{ formatDate(banner.end_date) }}
                                </div>
                            </div>
                            <span v-else class="text-gray-400">—</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button
                                @click="toggleActive(banner.id)"
                                :class="[
                                    'mr-4',
                                    banner.active ? 'text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300' :
                                    'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400'
                                ]"
                                :title="banner.active ? 'Click to deactivate' : 'Click to activate'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="banner.active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </button>
                            <router-link
                                :to="{ name: 'admin.banner-slider.banners.edit', params: { id: banner.id } }"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-4"
                            >
                                {{ $t('Edit') }}
                            </router-link>
                            <button
                                @click="duplicateBanner(banner.id)"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-4"
                                :title="$t('Duplicate')"
                            >
                                {{ $t('Duplicate') }}
                            </button>
                            <button
                                @click="deleteBanner(banner.id)"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                            >
                                {{ $t('Delete') }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="banners.length === 0">
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            {{ $t('No banners found.') }} <router-link :to="{ name: 'admin.banner-slider.banners.create' }" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">{{ $t('Create one') }}</router-link>
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
import { useDialog, useTranslation } from '@polycms';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const dialog = useDialog();

const banners = ref<any[]>([]);
const filters = ref({
    search: '',
    active: '',
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
});

const loadBanners = async () => {
    try {
        const params: any = {
            page: pagination.value.current_page,
            per_page: pagination.value.per_page,
        };

        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.active !== '') params.active = filters.value.active === 'true';

        const response = await axios.get('/api/v1/banner-slider/banners', { params });
        banners.value = response.data.data;
        pagination.value = {
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            per_page: response.data.meta.per_page,
            total: response.data.meta.total,
            from: response.data.meta.from,
            to: response.data.meta.to,
        };
    } catch (error) {
        console.error('Error loading banners:', error);
        dialog.error('Failed to load banners');
    }
};

const changePage = (page: number) => {
    pagination.value.current_page = page;
    loadBanners();
};

const formatDate = (dateString: string): string => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString();
};

const deleteBanner = async (id: number) => {
    const confirmed = await dialog.confirm({
        title: 'Delete Banner',
        message: 'Are you sure you want to delete this banner?',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger',
    });

    if (!confirmed) return;

    try {
        await axios.delete(`/api/v1/banner-slider/banners/${id}`);
        loadBanners();
        dialog.success('Banner deleted successfully');
    } catch (error: any) {
        console.error('Error deleting banner:', error);
        const message = error.response?.data?.message || 'Failed to delete banner';
        dialog.error(message);
    }
};

const toggleActive = async (id: number) => {
    try {
        await axios.post(`/api/v1/banner-slider/banners/${id}/toggle-active`);
        loadBanners();
    } catch (error: any) {
        console.error('Error toggling banner status:', error);
        const message = error.response?.data?.message || 'Failed to toggle banner status';
        dialog.error(message);
    }
};

const duplicateBanner = async (id: number) => {
    try {
        const response = await axios.post(`/api/v1/banner-slider/banners/${id}/duplicate`);
        loadBanners();
        dialog.success('Banner duplicated successfully');

        // Optionally navigate to edit page of the duplicated banner
        if (response.data?.data?.id) {
            router.push({ name: 'admin.banner-slider.banners.edit', params: { id: response.data.data.id } });
        }
    } catch (error: any) {
        console.error('Error duplicating banner:', error);
        const message = error.response?.data?.message || 'Failed to duplicate banner';
        dialog.error(message);
    }
};

onMounted(() => {
    loadBanners();
});
</script>
