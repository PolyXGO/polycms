<template>
    <div>
        <form @submit.prevent="$emit('save')" class="space-y-8">
            <!-- Your homepage displays -->
            <div class="space-y-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ getSettingLabel('reading_show_on_front') }}
                </h3>
                <div class="space-y-3">
                    <label class="flex items-start">
                        <input
                            type="radio"
                            :value="'posts'"
                            :checked="getSettingValue('reading_show_on_front') === 'posts'"
                            @change="updateValue('reading_show_on_front', 'posts')"
                            class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                        />
                        <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                            {{ $t('Your latest posts') }}
                        </span>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-start">
                            <input
                                type="radio"
                                :value="'page'"
                                :checked="getSettingValue('reading_show_on_front') === 'page'"
                                @change="updateValue('reading_show_on_front', 'page')"
                                class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $t('A static page (select below)') }}
                            </span>
                        </label>

                        <!-- Page Selectors -->
                        <div v-if="getSettingValue('reading_show_on_front') === 'page'" class="ml-7 space-y-4 pl-4 border-l-2 border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-4">
                                <label for="reading_page_on_front" class="w-32 text-sm text-gray-600 dark:text-gray-400">
                                    {{ getSettingLabel('reading_page_on_front') }}:
                                </label>
                                <select
                                    id="reading_page_on_front"
                                    :value="getSettingValue('reading_page_on_front')"
                                    @change="updateValue('reading_page_on_front', ($event.target as HTMLSelectElement).value)"
                                    class="flex-1 max-w-md px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">{{ $t('-- Select a page --') }}</option>
                                    <option v-for="page in pages" :key="page.id" :value="page.id">
                                        {{ page.title }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-center gap-4">
                                <label for="reading_page_for_posts" class="w-32 text-sm text-gray-600 dark:text-gray-400">
                                    {{ getSettingLabel('reading_page_for_posts') }}:
                                </label>
                                <select
                                    id="reading_page_for_posts"
                                    :value="getSettingValue('reading_page_for_posts')"
                                    @change="updateValue('reading_page_for_posts', ($event.target as HTMLSelectElement).value)"
                                    class="flex-1 max-w-md px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">{{ $t('-- Select a page --') }}</option>
                                    <option v-for="page in pages" :key="page.id" :value="page.id">
                                        {{ page.title }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Posts per page -->
            <div class="flex items-center gap-4">
                <label for="reading_posts_per_page" class="font-medium text-gray-700 dark:text-gray-300">
                    {{ getSettingLabel('reading_posts_per_page') }}
                </label>
                <div class="flex items-center gap-2">
                    <input
                        id="reading_posts_per_page"
                        :value="getSettingValue('reading_posts_per_page')"
                        @input="updateValue('reading_posts_per_page', ($event.target as HTMLInputElement).value)"
                        type="number"
                        min="1"
                        max="100"
                        class="w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">{{ $t('posts') }}</span>
                </div>
            </div>

            <!-- Feed limit -->
            <div class="flex items-center gap-4">
                <label for="reading_feed_limit" class="font-medium text-gray-700 dark:text-gray-300">
                    {{ getSettingLabel('reading_feed_limit') }}
                </label>
                <div class="flex items-center gap-2">
                    <input
                        id="reading_feed_limit"
                        :value="getSettingValue('reading_feed_limit')"
                        @input="updateValue('reading_feed_limit', ($event.target as HTMLInputElement).value)"
                        type="number"
                        min="1"
                        max="100"
                        class="w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">{{ $t('items') }}</span>
                </div>
            </div>

            <!-- Feed content -->
            <div class="space-y-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ getSettingLabel('reading_feed_full_content') }}
                </h3>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input
                            type="radio"
                            :value="true"
                            :checked="getSettingValue('reading_feed_full_content') === 'true' || getSettingValue('reading_feed_full_content') === '1'"
                            @change="updateValue('reading_feed_full_content', true)"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                        />
                        <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ $t('Full text') }}</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            type="radio"
                            :value="false"
                            :checked="getSettingValue('reading_feed_full_content') === 'false' || getSettingValue('reading_feed_full_content') === '0'"
                            @change="updateValue('reading_feed_full_content', false)"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                        />
                        <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ $t('Excerpt') }}</span>
                    </label>
                </div>
            </div>

            <!-- Search engine visibility -->
            <div class="space-y-1">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            id="reading_search_engine_noindex"
                            type="checkbox"
                            :checked="getSettingValue('reading_search_engine_noindex') === 'true' || getSettingValue('reading_search_engine_noindex') === '1'"
                            @change="updateValue('reading_search_engine_noindex', ($event.target as HTMLInputElement).checked)"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="reading_search_engine_noindex" class="font-medium text-gray-700 dark:text-gray-300">
                            {{ getSettingLabel('reading_search_engine_noindex') }}
                        </label>
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ $t('It is up to search engines to honor this request.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                     <span v-if="saving" class="flex items-center">
                        <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        {{ $t('Saving...') }}
                    </span>
                    <span v-else>{{ $t('Save Changes') }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../../composables/useTranslation';

interface Setting {
    key: string;
    value: any;
    type: string;
    label: string;
    description: string;
}

interface Props {
    settings: {
        [key: string]: Setting;
    };
    saving: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const { t } = useTranslation();
const $t = t;

const pages = ref<any[]>([]);

const loadPages = async () => {
    try {
        const response = await axios.get('/api/v1/posts', {
            params: { type: 'page', status: 'published', per_page: 100, compact: 1 }
        });
        if (response.data && response.data.data) {
            pages.value = response.data.data;
        }
    } catch (error) {
        console.error('Failed to load pages for reading settings:', error);
    }
};

onMounted(() => {
    loadPages();
});

const getSettingValue = (key: string): any => {
    const value = props.settings[key]?.value;
    if (value === null || value === undefined) return '';
    return String(value);
};

const getSettingLabel = (key: string): string => {
    return props.settings[key]?.label ?? key;
};

const updateValue = (key: string, value: any) => {
    emit('update', 'reading', key, value);
};
</script>
