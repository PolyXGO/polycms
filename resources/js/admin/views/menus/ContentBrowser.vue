<template>
    <div class="flex flex-col h-full w-full min-w-0">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('Add menu items') }}</h2>
        </div>

        <!-- Toggle Buttons -->
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-3">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                        activeTab === tab.id
                            ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Search (hidden for custom links) -->
        <div v-if="activeTab !== 'custom'" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <input
                v-model="searchQuery"
                @input="searchContent"
                type="text"
                :placeholder="$t('Search...')"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
        </div>

        <!-- Custom Link Form -->
        <div v-if="activeTab === 'custom'" class="flex-1 p-6 space-y-4 overflow-y-auto min-h-0">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ $t('URL') }}
                    </label>
                    <input
                        v-model="customLink.url"
                        type="url"
                        :placeholder="$t('https://')"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ $t('Link Text') }}
                    </label>
                    <input
                        v-model="customLink.title"
                        type="text"
                        :placeholder="$t('Enter link text')"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
            </div>
        </div>

        <!-- Content List -->
        <div v-else class="flex-1 p-6 space-y-4 overflow-y-auto min-h-0">
            <!-- Default Pages Section -->
            <div v-if="activeTab !== 'custom'" class="mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    {{ $t('Default Pages') }}
                </h3>
                <div class="space-y-2">
                    <label
                        v-for="defaultPage in defaultPages"
                        :key="defaultPage.id"
                        class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900/40 cursor-pointer border border-gray-200 dark:border-gray-700"
                    >
                        <input
                            type="checkbox"
                            :value="defaultPage.id"
                            v-model="selectedDefaultPages"
                            class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500"
                        />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ defaultPage.title }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ defaultPage.url }}
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Content Items -->
            <div v-if="activeTab !== 'custom' && items.length > 0" class="mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    {{ $t('Content Items') }}
                </h3>
            </div>

            <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
            </div>
            <div v-else-if="items.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                {{ $t('No items found') }}
            </div>
            <div v-else class="space-y-2">
                <label
                    v-for="item in items"
                    :key="item.id"
                    class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900/40 cursor-pointer"
                >
                    <input
                        type="checkbox"
                        :value="item.id"
                        v-model="selectedItems"
                        class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500"
                    />
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ item.title || item.name }}
                        </div>
                        <div v-if="item.slug" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ item.slug }}
                        </div>
                    </div>
                </label>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="mt-4 flex items-center justify-between">
                <button
                    @click="loadPage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
                >
                    {{ $t('Previous') }}
                </button>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $t('Page') }} {{ pagination.current_page }} {{ $t('of') }} {{ pagination.last_page }}
                </span>
                <button
                    @click="loadPage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
                >
                    {{ $t('Next') }}
                </button>
            </div>
        </div>

        <!-- Add to Menu Button -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
            <div v-if="activeTab !== 'custom'" class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ selectedItems.length + selectedDefaultPages.length }} {{ $t('selected') }}
                </span>
                <button
                    v-if="items.length > 0"
                    @click="selectAll"
                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                >
                    {{ selectedItems.length === items.length ? $t('Deselect All') : $t('Select All') }}
                </button>
            </div>
            <button
                @click="addToMenu"
                :disabled="activeTab === 'custom' ? !canAddCustomLink : (selectedItems.length === 0 && selectedDefaultPages.length === 0)"
                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ $t('Add to Menu') }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';
import { usePermalinkSettings } from '../../composables/usePermalinkSettings';
import { getCurrentInstance } from 'vue';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;
const { structure, ensureStructureLoaded } = usePermalinkSettings();

const emit = defineEmits<{
    (e: 'add-items', items: any[]): void;
}>();

const tabs = [
    { id: 'posts', label: $t('Posts') },
    { id: 'pages', label: $t('Pages') },
    { id: 'categories', label: $t('Categories') },
    { id: 'products', label: $t('Products') },
    { id: 'tags', label: $t('Tags') },
    { id: 'custom', label: $t('Custom Links') },
];

const activeTab = ref('posts');
const searchQuery = ref('');
const loading = ref(false);
const items = ref<any[]>([]);
const selectedItems = ref<number[]>([]);
const selectedDefaultPages = ref<string[]>([]);
const pagination = ref<any>(null);
const customLink = ref({
    url: '',
    title: '',
});

// Build URL for default pages based on permalink settings
const buildDefaultPageUrl = (type: 'home' | 'products' | 'blog'): string => {
    const baseUrl = window.location.origin;
    const permalinks = structure.value;

    switch (type) {
        case 'home':
            return `${baseUrl}/`;
        case 'products':
            // Use products archive permalink
            const productsArchive = permalinks.products?.archive || 'products';
            return productsArchive ? `${baseUrl}/${productsArchive}` : `${baseUrl}/products`;
        case 'blog':
            // Use posts archive permalink
            const postsArchive = permalinks.posts?.archive || 'posts';
            return postsArchive ? `${baseUrl}/${postsArchive}` : `${baseUrl}/posts`;
        default:
            return `${baseUrl}/`;
    }
};

// Default pages that can be added to menu
// URL will be computed based on permalink settings
const defaultPages = computed(() => {
    const homeTitle = $t('Home');
    const productsTitle = $t('Products');
    const blogTitle = $t('Blog');

    return [
        {
            id: 'home',
            title: homeTitle,
            url: buildDefaultPageUrl('home'),
            type: 'custom',
        },
        {
            id: 'products',
            title: productsTitle,
            url: buildDefaultPageUrl('products'),
            type: 'custom',
        },
        {
            id: 'blog',
            title: blogTitle,
            url: buildDefaultPageUrl('blog'),
            type: 'custom',
        },
    ];
});

const loadContent = async (page = 1) => {
    if (activeTab.value === 'custom') {
        items.value = [];
        return;
    }

    loading.value = true;
    try {
        const params: any = {
            page,
            per_page: 20,
        };
        if (searchQuery.value) {
            params.search = searchQuery.value;
        }

        const endpoint = `/api/v1/menus/content/${activeTab.value}`;
        const response = await axios.get(endpoint, { params });

        // Handle response structure: { data: { data: [...], current_page: 1, ... }, error: null, ... }
        const responseData = response?.data;

        if (!responseData) {
            items.value = [];
            pagination.value = null;
            return;
        }

        // Check if it's the standard API response format
        const data = responseData.data;

        if (Array.isArray(data)) {
            // Direct array response
            items.value = data;
            pagination.value = null;
        } else if (data && typeof data === 'object' && data.data) {
            // Paginated response: { data: [...], current_page: 1, ... }
            items.value = Array.isArray(data.data) ? data.data : [];
            pagination.value = {
                current_page: data.current_page || 1,
                last_page: data.last_page || 1,
                per_page: data.per_page || 20,
                total: data.total || 0,
            };
        } else {
            // Fallback: try to get items directly
            items.value = [];
            pagination.value = null;
        }
    } catch (error: any) {
        console.error('Error loading content:', error);
        console.error('Error response:', error.response);
        items.value = [];
        pagination.value = null;
    } finally {
        loading.value = false;
    }
};

const searchContent = () => {
    loadContent(1);
};

const loadPage = (page: number) => {
    loadContent(page);
};

const selectAll = () => {
    if (selectedItems.value.length === items.value.length) {
        selectedItems.value = [];
    } else {
        selectedItems.value = items.value.map(item => item.id);
    }
};

const canAddCustomLink = computed(() => {
    return customLink.value.url.trim() !== '' && customLink.value.title.trim() !== '';
});

const addToMenu = () => {
    if (activeTab.value === 'custom') {
        // Handle custom link from form
        if (!canAddCustomLink.value) {
            return;
        }

        emit('add-items', [{
            type: 'custom',
            title: customLink.value.title.trim(),
            url: customLink.value.url.trim(),
        }]);

        // Reset form
        customLink.value = {
            url: '',
            title: '',
        };
        return;
    }

    const itemsToAdd: any[] = [];

    // Add selected default pages
    if (selectedDefaultPages.value.length > 0) {
        const defaultPagesToAdd = defaultPages.value.filter(page =>
            selectedDefaultPages.value.includes(page.id)
        );
        itemsToAdd.push(...defaultPagesToAdd.map(page => ({
            type: 'custom',
            title: page.title,
            url: page.url,
        })));
    }

    // Add selected content items
    const selected = items.value.filter(item => selectedItems.value.includes(item.id));

    // Map tab to type
    const typeMap: Record<string, string> = {
        'posts': 'post',
        'pages': 'page',
        'categories': 'category',
        'products': 'product',
        'tags': 'tag',
    };

    const contentItemsToAdd = selected.map(item => {
        const baseItem: any = {
            type: typeMap[activeTab.value] || activeTab.value,
            linkable_id: item.id,
        };
        return baseItem;
    });

    itemsToAdd.push(...contentItemsToAdd);

    emit('add-items', itemsToAdd);
    selectedItems.value = [];
    selectedDefaultPages.value = [];
};

watch(activeTab, () => {
    selectedItems.value = [];
    selectedDefaultPages.value = [];
    if (activeTab.value === 'custom') {
        // Reset custom link form when switching away
        customLink.value = {
            url: '',
            title: '',
        };
    } else {
        loadContent(1);
    }
});

onMounted(async () => {
    // Load permalink settings first to ensure default pages have correct URLs
    await ensureStructureLoaded();
    loadContent();
});
</script>
