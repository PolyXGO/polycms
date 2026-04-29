<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('Attribute Groups') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $t('Organize global attributes into groups for easier product configuration and logical categorization on the frontend.') }}
                </p>
            </div>
            <button
                @click="createNewGroup"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                {{ $t('Add New Group') }}
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>

        <div v-else class="flex flex-col lg:flex-row gap-6">
            
            <!-- Groups List (Left Side) -->
            <div class="lg:w-1/3 flex flex-col gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">{{ $t('Groups') }}</h3>
                    </div>
                    
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700 max-h-[800px] overflow-y-auto">
                        <li 
                            v-for="group in groups" 
                            :key="group.id" 
                            @click="selectGroup(group)"
                            class="px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex justify-between items-center group"
                            :class="{'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500': activeGroupId === group.id, 'border-l-4 border-transparent': activeGroupId !== group.id}"
                        >
                            <div>
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ group.name }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ group.slug }}</span>
                            </div>
                            <!-- Delete button shown on hover -->
                            <button 
                                v-if="activeGroupId === group.id || true"
                                @click.stop="confirmDeleteGroup(group.id)" 
                                class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600"
                                :title="$t('Delete Group')"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </li>
                        <li v-if="groups.length === 0" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ $t('No attribute groups found.') }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Group Editor & Attribute Assignments (Right Side) -->
            <div class="lg:w-2/3 h-full">
                <div v-if="activeGroup" class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden flex flex-col">
                    
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ isCreating ? $t('New Group Settings') : $t('Edit Settings') }}
                        </h3>
                        <div class="flex gap-2">
                            <button 
                                v-if="isCreating"
                                @click="cancelCreate"
                                class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                {{ $t('Cancel') }}
                            </button>
                            <button 
                                @click="saveActiveGroup"
                                :disabled="isSaving"
                                class="px-4 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                {{ isSaving ? $t('Saving...') : $t('Save Changes') }}
                            </button>
                        </div>
                    </div>

                    <div class="p-5 space-y-6">
                        <!-- Group details -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Group Name') }} <span class="text-red-500">*</span></label>
                                <input
                                    v-model="activeGroup.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                    @blur="generateGroupSlug"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Slug (Identifier)') }}</label>
                                <input
                                    v-model="activeGroup.slug"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white font-mono text-sm"
                                    :placeholder="$t('Leave blank to auto-generate')"
                                />
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end mb-4 gap-4">
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ $t('Assign Attributes') }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-lg">
                                        {{ $t('Toggle the switch or check the items below to include them inside this group. A selected attribute will be instantly reassigned to this group.') }}
                                    </p>
                                </div>
                                <div class="relative w-full sm:w-64">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <input
                                        v-model="attributeSearch"
                                        type="text"
                                        :placeholder="$t('Search attributes...')"
                                        class="block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                </div>
                            </div>
                            
                            <!-- Dense Flexbox Checkbox Layout -->
                            <div class="bg-gray-50 dark:bg-gray-800/80 p-4 rounded-lg border border-gray-200 dark:border-gray-700 min-h-[200px]">
                                <div class="flex flex-wrap gap-2.5">
                                    <label
                                        v-for="attr in filteredAttributes"
                                        :key="attr.id"
                                        class="inline-flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-700 border rounded-md cursor-pointer select-none transition-all duration-200"
                                        :class="[
                                            selectedAttributeIds.includes(attr.id) 
                                                ? 'border-indigo-500 ring-1 ring-indigo-500 bg-indigo-50/30 dark:bg-indigo-900/20' 
                                                : 'border-gray-300 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500'
                                        ]"
                                    >
                                        <div class="relative flex items-start">
                                            <div class="flex items-center h-5">
                                                <input
                                                    type="checkbox"
                                                    :value="attr.id"
                                                    v-model="selectedAttributeIds"
                                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded cursor-pointer"
                                                />
                                            </div>
                                            <div class="ml-2 text-sm leading-5">
                                                <span class="font-medium" :class="selectedAttributeIds.includes(attr.id) ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-200'">{{ attr.name }}</span>
                                                <span class="inline-flex items-center ml-1.5 px-1.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 align-text-bottom">
                                                    {{ attr.display_type === 'color_swatch' ? 'Color' : (attr.display_type === 'image_swatch' ? 'Image' : 'Select') }}
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <div v-if="filteredAttributes.length === 0" class="w-full text-center py-6 text-sm text-gray-500">
                                        {{ attributeSearch ? $t('No attributes found matching your search.') : $t('No attributes created yet in the system.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-else class="h-full flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-12 text-center bg-gray-50/50 dark:bg-gray-800/30">
                    <div>
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ $t('Select an Attribute Group') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">{{ $t('Choose an existing group from the sidebar to manage its settings and assigned attributes, or create a brand new one.') }}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';

const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || ((s: string) => s);
const dialog = useDialog();

interface Attribute {
    id: number;
    name: string;
    slug: string;
    display_type: string;
    group_id: number | null;
}

interface AttributeGroup {
    id: number;
    name: string;
    slug: string;
}

const loading = ref(true);
const isSaving = ref(false);

const groups = ref<AttributeGroup[]>([]);
const allAttributes = ref<Attribute[]>([]);

const activeGroupId = ref<number | null>(null);
const activeGroup = ref<Partial<AttributeGroup> | null>(null);
const isCreating = ref(false);
const selectedAttributeIds = ref<number[]>([]);

const attributeSearch = ref('');

const filteredAttributes = computed(() => {
    let list = allAttributes.value;
    if (attributeSearch.value.trim()) {
        const query = attributeSearch.value.toLowerCase().trim();
        list = list.filter(a => a.name.toLowerCase().includes(query) || a.slug.toLowerCase().includes(query));
    }
    // Sort so checked ones are naturally positioned first, or just alphabetically
    return list.slice().sort((a,b) => {
        const aChecked = selectedAttributeIds.value.includes(a.id);
        const bChecked = selectedAttributeIds.value.includes(b.id);
        if (aChecked && !bChecked) return -1;
        if (!aChecked && bChecked) return 1;
        return a.name.localeCompare(b.name);
    });
});

onMounted(() => {
    fetchData();
});

async function fetchData() {
    loading.value = true;
    try {
        const [groupsRes, attributesRes] = await Promise.all([
            axios.get('/api/v1/product-attribute-groups'),
            axios.get('/api/v1/product-attributes')
        ]);
        groups.value = groupsRes.data.data;
        allAttributes.value = attributesRes.data.data;
    } catch (e: any) {
        dialog.error('Failed to load data');
    } finally {
        loading.value = false;
    }
}

function selectGroup(group: AttributeGroup) {
    isCreating.value = false;
    activeGroupId.value = group.id;
    activeGroup.value = { ...group };
    
    // Auto populate the assignments based on group_id
    selectedAttributeIds.value = allAttributes.value
        .filter(a => a.group_id === group.id)
        .map(a => a.id);
        
    attributeSearch.value = '';
}

function createNewGroup() {
    isCreating.value = true;
    activeGroupId.value = null;
    activeGroup.value = { name: '', slug: '' };
    selectedAttributeIds.value = [];
    attributeSearch.value = '';
}

function cancelCreate() {
    isCreating.value = false;
    activeGroupId.value = null;
    activeGroup.value = null;
}

function generateGroupSlug() {
    if (activeGroup.value && activeGroup.value.name && (!activeGroup.value.slug || activeGroup.value.slug.trim() === '')) {
        let text = activeGroup.value.name;
        // Basic slugification - convert to lowercase, remove accents, replace spaces with hyphens
        text = text.toLowerCase();
        text = text.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); // remove accents
        text = text.replace(/đ/g, "d").replace(/Đ/g, "d");
        text = text.replace(/[^a-z0-9\s-]/g, '');
        text = text.replace(/\s+/g, '-').replace(/-+/g, '-');
        text = text.replace(/^-+|-+$/g, '');
        activeGroup.value.slug = text;
    }
}

async function saveActiveGroup() {
    if (!activeGroup.value?.name?.trim()) {
        return dialog.error($t('Group name is required'));
    }
    
    isSaving.value = true;
    try {
        let groupId = activeGroupId.value;
        
        // 1. Save or Create the Group
        if (isCreating.value) {
            const res = await axios.post('/api/v1/product-attribute-groups', activeGroup.value);
            groupId = res.data.data.id;
            // Update local collections
            groups.value.push(res.data.data);
            dialog.success($t('Group created successfully'));
            isCreating.value = false;
            activeGroupId.value = groupId;
        } else {
            const res = await axios.put(`/api/v1/product-attribute-groups/${groupId}`, activeGroup.value);
            // Update local collections
            const idx = groups.value.findIndex(g => g.id === groupId);
            if(idx !== -1) groups.value[idx] = res.data.data;
            dialog.success($t('Group updated successfully'));
        }
        
        // 2. Sync Attributes
        if (groupId) {
            await axios.post(`/api/v1/product-attribute-groups/${groupId}/sync-attributes`, {
                attribute_ids: selectedAttributeIds.value
            });
            
            // Re-fetch everything silently to sync the attribute `group_id` states
            const attributesRes = await axios.get('/api/v1/product-attributes');
            allAttributes.value = attributesRes.data.data;
            
            // Re-select to apply new local state cleanly
            const updatedGroup = groups.value.find(g => g.id === groupId);
            if (updatedGroup) selectGroup(updatedGroup);
        }
        
    } catch (e: any) {
        dialog.error(e.response?.data?.message || $t('Failed to save group details'));
    } finally {
        isSaving.value = false;
    }
}

async function confirmDeleteGroup(id: number) {
    if (!confirm($t('Delete this group? Attributes belonging to it will become uncategorized.'))) return;
    try {
        await axios.delete(`/api/v1/product-attribute-groups/${id}`);
        dialog.success($t('Group deleted successfully'));
        
        groups.value = groups.value.filter(g => g.id !== id);
        
        // Deselect if we deleted the active one
        if (activeGroupId.value === id) {
            activeGroupId.value = null;
            activeGroup.value = null;
            selectedAttributeIds.value = [];
        }
        
        // The backend `nullOnDelete` will clear group_ids, let's refresh attributes so the UI is up to date
        const attributesRes = await axios.get('/api/v1/product-attributes');
        allAttributes.value = attributesRes.data.data;
        
    } catch (e: any) {
        dialog.error($t('Failed to delete group'));
    }
}
</script>
