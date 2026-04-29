<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('Product Attributes') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $t('Manage global attributes and their values to create product variants.') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <router-link
                    :to="{ name: 'admin.products.attribute-groups' }"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    {{ $t('Manage Groups') }}
                </router-link>
                <button
                    @click="openAttributeModal()"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    {{ $t('Add Attribute') }}
                </button>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>

        <div v-else-if="attributes.length === 0" class="text-center py-16 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-white">{{ $t('No attributes') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('Get started by creating a new attribute like Size or Color.') }}</p>
            <div class="mt-6">
                <button @click="openAttributeModal()" type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ $t('New Attribute') }}
                </button>
            </div>
        </div>

        <!-- Attributes List -->
        <div v-else class="space-y-8">
            <template v-for="(groupAttrs, groupName) in groupedAttributes" :key="groupName">
                <div>
                    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-3 ml-1">
                        {{ groupName || $t('Uncategorized') }}
                    </h2>
                    <div class="space-y-4">
                        <div
                            v-for="attr in groupAttrs"
                            :key="attr.id"
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
                        >
                            <div class="px-4 py-4 sm:px-6 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ attr.name }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ formatDisplayType(attr.display_type) }}
                                    </span>
                                    <span class="text-xs text-gray-500">Slug: {{ attr.slug }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="openAttributeModal(attr)" class="text-gray-400 hover:text-indigo-600 transition-colors" :title="$t('Edit Attribute')">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click="deleteAttribute(attr.id)" class="text-gray-400 hover:text-red-500 transition-colors" :title="$t('Delete Attribute')">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex flex-wrap gap-2 items-center">
                                    <!-- Values -->
                                    <div
                                        v-for="val in attr.values"
                                        :key="val.id"
                                        class="group relative inline-flex items-center gap-1.5 pl-2.5 pr-2 py-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-medium cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors"
                                        @click="openValueModal(attr, val)"
                                    >
                                        <span
                                            v-if="attr.display_type === 'color_swatch' && val.hex_color"
                                            class="w-3.5 h-3.5 rounded-full border border-black/10 shadow-sm"
                                            :style="{ backgroundColor: val.hex_color }"
                                        ></span>
                                        <span class="text-gray-800 dark:text-gray-200">{{ val.value }}</span>
                                        <!-- Delete Value button (visible on hover) -->
                                        <button
                                            @click.stop="deleteValue(attr.id, val.id)"
                                            class="opacity-0 group-hover:opacity-100 ml-1 text-gray-400 hover:text-red-500 transition-all p-0.5"
                                            :title="$t('Delete value')"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Add Value Quick Input -->
                                    <div class="inline-flex items-center ml-2 relative">
                                        <input
                                            v-model="quickValues[attr.id]"
                                            type="text"
                                            :placeholder="$t('Add value...')"
                                            class="w-28 px-2 py-1 min-h-[30px] rounded-md border-gray-300 dark:border-gray-600 border-dashed dark:bg-gray-800 text-sm focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 transition-all focus:w-40"
                                            @keydown.enter.prevent="quickAddValue(attr.id)"
                                        />
                                        <!-- Fast color picker for Color attribute -->
                                        <div v-if="attr.display_type === 'color_swatch'" class="absolute right-1 top-1 w-6 h-6 overflow-hidden rounded cursor-pointer border border-gray-300 dark:border-gray-600">
                                            <input
                                                v-model="quickColors[attr.id]"
                                                type="color"
                                                class="absolute -top-2 -left-2 w-10 h-10 cursor-pointer"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Attribute Modal (Create/Edit) -->
        <div v-if="showingAttributeModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showingAttributeModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    {{ editingAttribute ? $t('Edit Attribute') : $t('Add New Attribute') }}
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Attribute Group') }}</label>
                                        <input
                                            v-model="attributeForm.group_name"
                                            list="group-options"
                                            type="text"
                                            :placeholder="$t('e.g. Hardware, Specifications')"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                        />
                                        <datalist id="group-options">
                                            <option v-for="g in groups" :key="g.id" :value="g.name"></option>
                                        </datalist>
                                        <p class="mt-1 text-xs text-gray-500">{{ $t('Optional. Groups help categorize technical items like SSD/RAM into Hardware.') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Attribute Name') }} <span class="text-red-500">*</span></label>
                                        <input
                                            v-model="attributeForm.name"
                                            type="text"
                                            :placeholder="$t('e.g. Size, Color, Processor')"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                            @blur="generateAttributeSlug"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Slug') }}</label>
                                        <input
                                            v-model="attributeForm.slug"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white font-mono text-xs"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">{{ $t('Used for URLs and system references. Leave empty to auto-generate.') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Display Type') }}</label>
                                        <select
                                            v-model="attributeForm.display_type"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                        >
                                            <option value="select">{{ $t('Dropdown Select') }}</option>
                                            <option value="color_swatch">{{ $t('Color Swatch') }}</option>
                                            <option value="button">{{ $t('Button/Pill') }}</option>
                                            <option value="image_swatch">{{ $t('Image Swatch') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            @click="saveAttribute"
                            :disabled="isSaving"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            {{ $t('Save') }}
                        </button>
                        <button
                            type="button"
                            @click="showingAttributeModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ $t('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Value Modal (Edit) -->
        <div v-if="showingValueModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showingValueModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    {{ $t('Edit Value') }}
                                </h3>
                                <div class="mt-4 space-y-4" v-if="valueForm">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Value Label') }} <span class="text-red-500">*</span></label>
                                        <input
                                            v-model="valueForm.value"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Slug') }}</label>
                                        <input
                                            v-model="valueForm.slug"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white font-mono text-xs"
                                        />
                                    </div>
                                    <div v-if="activeAttributeForValue?.display_type === 'color_swatch'">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Color Hex') }}</label>
                                        <div class="flex items-center gap-3">
                                            <input
                                                v-model="valueForm.hex_color"
                                                type="color"
                                                class="h-9 w-16 p-1 border border-gray-300 dark:border-gray-600 rounded cursor-pointer dark:bg-gray-700"
                                            />
                                            <input
                                                v-model="valueForm.hex_color"
                                                type="text"
                                                placeholder="#000000"
                                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white font-mono uppercase"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            @click="saveValue"
                            :disabled="isSavingValue"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            {{ $t('Update Value') }}
                        </button>
                        <button
                            type="button"
                            @click="showingValueModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ $t('Cancel') }}
                        </button>
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

interface AttributeValue {
    id: number;
    attribute_id: number;
    value: string;
    slug: string;
    hex_color: string | null;
    image_url: string | null;
}

interface Attribute {
    id: number;
    name: string;
    slug: string;
    display_type: string;
    values: AttributeValue[];
    group?: AttributeGroup;
}

// Group Support
interface AttributeGroup {
    id: number;
    name: string;
    slug: string;
}
const groups = ref<AttributeGroup[]>([]);
const groupedAttributes = computed(() => {
    const map: Record<string, Attribute[]> = {};
    attributes.value.forEach(attr => {
        const groupName = attr.group?.name || '';
        if (!map[groupName]) map[groupName] = [];
        map[groupName].push(attr);
    });
    // Ensure empty string comes last or first? Let's put 'Uncategorized' at bottom.
    const sortedMap: Record<string, Attribute[]> = {};
    Object.keys(map).sort((a,b) => {
        if (!a) return 1;
        if (!b) return -1;
        return a.localeCompare(b);
    }).forEach(k => {
        sortedMap[k] = map[k];
    });
    return sortedMap;
});

const attributes = ref<Attribute[]>([]);
const loading = ref(true);

// Forms for Quick Add
const quickValues = ref<Record<number, string>>({});
const quickColors = ref<Record<number, string>>({});

// Attribute Modal State
const showingAttributeModal = ref(false);
const editingAttribute = ref<Attribute | null>(null);
const isSaving = ref(false);
const attributeForm = ref({
    name: '',
    slug: '',
    display_type: 'select',
    group_name: ''
});

// Value Modal State
const showingValueModal = ref(false);
const activeAttributeForValue = ref<Attribute | null>(null);
const editingValue = ref<AttributeValue | null>(null);
const isSavingValue = ref(false);
const valueForm = ref({
    value: '',
    slug: '',
    hex_color: '#000000'
});

onMounted(() => {
    fetchGroups();
    fetchAttributes();
});

async function fetchGroups() {
    try {
        const { data } = await axios.get('/api/v1/product-attribute-groups');
        groups.value = data.data;
    } catch (e: any) {
        console.error('Failed to load groups', e);
    }
}

async function fetchAttributes() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/v1/product-attributes');
        attributes.value = data.data;
        // init quick colors mapping
        attributes.value.forEach(a => {
            if (a.display_type === 'color_swatch' && !quickColors.value[a.id]) {
                quickColors.value[a.id] = '#000000';
            }
        });
    } catch (e: any) {
        dialog.error($t('Failed to load attributes'));
    } finally {
        loading.value = false;
    }
}

function formatDisplayType(type: string): string {
    const map: Record<string, string> = {
        'select': 'Dropdown',
        'color_swatch': 'Color Swatch',
        'button': 'Button',
        'image_swatch': 'Image Swatch'
    };
    return map[type] || type;
}

// --- ATTRIBUTE CRUD ---

function openAttributeModal(attr: Attribute | null = null) {
    editingAttribute.value = attr;
    if (attr) {
        attributeForm.value = {
            name: attr.name,
            slug: attr.slug,
            display_type: attr.display_type,
            group_name: (attr as any).group?.name || ''
        };
    } else {
        attributeForm.value = {
            name: '',
            slug: '',
            display_type: 'select',
            group_name: ''
        };
    }
    showingAttributeModal.value = true;
}

function generateAttributeSlug() {
    // Only auto-generate if empty
    if (!attributeForm.value.slug && attributeForm.value.name) {
        attributeForm.value.slug = attributeForm.value.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)+/g, '');
            
        // Smart default for display_type
        if (attributeForm.value.slug.includes('color') || attributeForm.value.slug.includes('màu')) {
            attributeForm.value.display_type = 'color_swatch';
        }
    }
}

async function saveAttribute() {
    if (!attributeForm.value.name.trim()) {
        dialog.error($t('Attribute name is required'));
        return;
    }

    isSaving.value = true;
    try {
        if (editingAttribute.value) {
            await axios.put(`/api/v1/product-attributes/${editingAttribute.value.id}`, attributeForm.value);
            dialog.success($t('Attribute updated'));
        } else {
            await axios.post('/api/v1/product-attributes', attributeForm.value);
            dialog.success($t('Attribute created'));
        }
        showingAttributeModal.value = false;
        fetchAttributes();
    } catch (e: any) {
        dialog.error(e.response?.data?.message || $t('Failed to save attribute'));
    } finally {
        isSaving.value = false;
    }
}

async function deleteAttribute(id: number) {
    if (!confirm($t('Delete this attribute? This will impact all products using it.'))) return;
    try {
        await axios.delete(`/api/v1/product-attributes/${id}`);
        dialog.success($t('Attribute deleted'));
        fetchAttributes();
    } catch (e: any) {
        dialog.error($t('Failed to delete attribute'));
    }
}

// --- VALUE CRUD ---

async function quickAddValue(attributeId: number) {
    const rawVal = quickValues.value[attributeId];
    if (!rawVal || !rawVal.trim()) return;

    const payload: any = { value: rawVal.trim() };
    if (quickColors.value[attributeId]) {
        payload.hex_color = quickColors.value[attributeId];
    }

    try {
        await axios.post(`/api/v1/product-attributes/${attributeId}/values`, payload);
        dialog.success($t('Value added'));
        quickValues.value[attributeId] = '';
        fetchAttributes();
    } catch (e: any) {
        dialog.error(e.response?.data?.message || $t('Failed to add value'));
    }
}

function openValueModal(attr: Attribute, val: AttributeValue) {
    activeAttributeForValue.value = attr;
    editingValue.value = val;
    valueForm.value = {
        value: val.value,
        slug: val.slug,
        hex_color: val.hex_color || '#000000'
    };
    showingValueModal.value = true;
}

async function saveValue() {
    if (!valueForm.value.value.trim()) return;
    
    isSavingValue.value = true;
    try {
        await axios.put(
            `/api/v1/product-attributes/${activeAttributeForValue.value!.id}/values/${editingValue.value!.id}`,
            valueForm.value
        );
        dialog.success($t('Value updated'));
        showingValueModal.value = false;
        fetchAttributes();
    } catch (e: any) {
        dialog.error(e.response?.data?.message || $t('Failed to update value'));
    } finally {
        isSavingValue.value = false;
    }
}

async function deleteValue(attributeId: number, valueId: number) {
    if (!confirm($t('Remove this value? Existing variants will keep using it textually.'))) return;
    try {
        await axios.delete(`/api/v1/product-attributes/${attributeId}/values/${valueId}`);
        dialog.success($t('Value removed'));
        fetchAttributes();
    } catch (e: any) {
        dialog.error($t('Failed to remove value'));
    }
}
</script>
