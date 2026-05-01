<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('Menus') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $t('Create and manage navigation menus for your site') }}
                </p>
            </div>
            <div class="flex gap-3">
                <button
                    v-if="selectedMenu"
                    @click="showDeleteConfirm = true"
                    class="px-4 py-2 border border-red-300 dark:border-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 bg-white dark:bg-gray-700 text-red-600 dark:text-red-400 transition-colors"
                >
                    {{ $t('Delete Menu') }}
                </button>
                <button
                    @click="createNewMenu"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ $t('New Menu') }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Menu Selector -->
        <div v-if="menus.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ $t('Select Menu to Edit') }}
            </label>
            <select
                v-model="selectedMenuId"
                @change="loadMenu"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
            >
                <option value="">{{ $t('-- Select a menu --') }}</option>
                <option v-for="menu in menus" :key="menu.id" :value="menu.id">
                    {{ menu.name }} ({{ menu.all_items_count || 0 }} {{ $t('items') }})
                </option>
            </select>
        </div>

        <!-- Menu Editor -->
        <MenuEditor
            v-if="selectedMenu"
            :menu="selectedMenu"
            @menu-updated="handleMenuUpdated"
            @menu-deleted="handleMenuDeleted"
        />

        <!-- Empty State -->
        <div v-else-if="!loading && menus.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ $t('No menus yet') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $t('Get started by creating a new menu') }}
            </p>
            <div class="mt-6">
                <button
                    @click="createNewMenu"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    {{ $t('Create Your First Menu') }}
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $t('Loading menus...') }}</p>
        </div>

        <!-- Create Menu Modal -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="cancelCreate"
            @keydown.escape="cancelCreate"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('Create New Menu') }}
                </h3>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ $t('Menu Name') }}
                    </label>
                    <input
                        ref="menuNameInput"
                        v-model="newMenuName"
                        type="text"
                        :placeholder="$t('Enter menu name')"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        @keydown.enter="submitCreate"
                    />
                </div>
                <div class="flex justify-end gap-3">
                    <button
                        @click="cancelCreate"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
                    >
                        {{ $t('Cancel') }}
                    </button>
                    <button
                        @click="submitCreate"
                        :disabled="!newMenuName.trim()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ $t('Create') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <div
            v-if="showDeleteConfirm"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showDeleteConfirm = false"
            @keydown.escape="showDeleteConfirm = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    {{ $t('Delete Menu') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    {{ $t('Are you sure you want to delete this menu? This action cannot be undone.') }}
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showDeleteConfirm = false"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
                    >
                        {{ $t('Cancel') }}
                    </button>
                    <button
                        @click="deleteMenu"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                    >
                        {{ $t('Delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, getCurrentInstance, nextTick } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';
import MenuEditor from './MenuEditor.vue';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;
const dialog = useDialog();

interface Menu {
    id: number;
    name: string;
    slug: string;
    location: string | null;
    description: string | null;
    all_items_count?: number;
}

const loading = ref(false);
const menus = ref<Menu[]>([]);
const selectedMenuId = ref<number | null>(null);
const selectedMenu = ref<Menu | null>(null);
const showDeleteConfirm = ref(false);
const showCreateModal = ref(false);
const newMenuName = ref('');
const menuNameInput = ref<HTMLInputElement | null>(null);

const loadMenus = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/menus');
        menus.value = response.data.data || [];

        // Auto-select first menu if available
        if (menus.value.length > 0 && !selectedMenuId.value) {
            selectedMenuId.value = menus.value[0].id;
            await loadMenu();
        }
    } catch (error: any) {
        console.error('Error loading menus:', error);
        dialog.error($t('Failed to load menus'));
    } finally {
        loading.value = false;
    }
};

const loadMenu = async () => {
    if (!selectedMenuId.value) {
        selectedMenu.value = null;
        return;
    }

    try {
        const response = await axios.get(`/api/v1/menus/${selectedMenuId.value}`);
        selectedMenu.value = response.data.data;
    } catch (error: any) {
        console.error('Error loading menu:', error);
        dialog.error($t('Failed to load menu'));
        selectedMenu.value = null;
    }
};

const createNewMenu = () => {
    newMenuName.value = '';
    showCreateModal.value = true;
    nextTick(() => {
        menuNameInput.value?.focus();
    });
};

const cancelCreate = () => {
    showCreateModal.value = false;
    newMenuName.value = '';
};

const submitCreate = async () => {
    const name = newMenuName.value.trim();
    if (!name) return;

    try {
        const response = await axios.post('/api/v1/menus', { name });

        const newMenu = response.data.data;
        menus.value.push(newMenu);
        selectedMenuId.value = newMenu.id;
        showCreateModal.value = false;
        newMenuName.value = '';
        await loadMenu();

        dialog.success($t('Menu created successfully'));
    } catch (error: any) {
        console.error('Error creating menu:', error);
        const message = error.response?.data?.error?.message || $t('Failed to create menu');
        dialog.error(message);
    }
};

const deleteMenu = async () => {
    if (!selectedMenu.value) {
        return;
    }

    try {
        await axios.delete(`/api/v1/menus/${selectedMenu.value.id}`);

        menus.value = menus.value.filter(m => m.id !== selectedMenu.value!.id);
        selectedMenuId.value = null;
        selectedMenu.value = null;
        showDeleteConfirm.value = false;

        dialog.success($t('Menu deleted successfully'));
    } catch (error: any) {
        console.error('Error deleting menu:', error);
        const message = error.response?.data?.error?.message || $t('Failed to delete menu');
        dialog.error(message);
    }
};

const handleMenuUpdated = (updatedMenu: Menu) => {
    const index = menus.value.findIndex(m => m.id === updatedMenu.id);
    if (index !== -1) {
        menus.value[index] = updatedMenu;
    }
    selectedMenu.value = updatedMenu;
};

const handleMenuDeleted = () => {
    if (selectedMenu.value) {
        menus.value = menus.value.filter(m => m.id !== selectedMenu.value!.id);
        selectedMenuId.value = null;
        selectedMenu.value = null;
    }
};

onMounted(() => {
    loadMenus();
});
</script>
