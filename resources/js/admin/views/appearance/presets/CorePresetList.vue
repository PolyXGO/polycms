<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-admin-theme-text">Presets</h1>
                <p class="mt-1 text-sm text-admin-theme-text-muted">Manage reusable templates, macros, and styling blocks.</p>
            </div>
            <div>
                <button @click="openPresetModal(null)" class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors">
                    + New Preset
                </button>
            </div>
        </div>

        <div class="flex gap-6 items-start">
            <!-- Categories Sidebar -->
            <div class="w-64 shrink-0">
                <div class="bg-admin-theme-surface rounded-lg shadow-sm border border-admin-theme-border">
                    <div class="p-4 border-b border-admin-theme-border flex justify-between items-center bg-admin-theme-base rounded-t-lg">
                        <h2 class="text-sm font-medium text-admin-theme-text">Categories</h2>
                        <button @click="openCategoryModal()" class="text-xs font-medium text-admin-theme-primary hover:text-admin-theme-primary-hover">
                            + New Category
                        </button>
                    </div>
                    <div class="p-2 space-y-1">
                        <button 
                            @click="selectCategory(null)" 
                            :class="['w-full text-left px-3 py-2 rounded-md text-sm flex items-center transition-colors', selectedCategoryId === null ? 'bg-admin-theme-primary/10 text-admin-theme-primary font-medium' : 'text-admin-theme-text hover:bg-admin-theme-base']"
                        >
                            <RectangleGroupIcon class="w-5 h-5 mr-2" /> All Presets
                        </button>
                        
                        <div v-for="category in categories" :key="category.id" class="flex items-center group">
                            <button 
                                @click="selectCategory(category.id)" 
                                :class="['flex-1 text-left px-3 py-2 rounded-md text-sm flex items-center truncate transition-colors', selectedCategoryId === category.id ? 'bg-admin-theme-primary/10 text-admin-theme-primary font-medium' : 'text-admin-theme-text hover:bg-admin-theme-base']"
                            >
                                <FolderIcon class="w-5 h-5 mr-2" /> 
                                <span class="truncate">{{ category.name }}</span>
                            </button>
                            <div class="hidden group-hover:flex space-x-1 px-2">
                                <button @click="openCategoryModal(category)" class="text-admin-theme-text-muted hover:text-admin-theme-primary p-1" title="Edit">
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button @click="deleteCategory(category)" class="text-admin-theme-text-muted hover:text-red-500 p-1" title="Delete">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Presets Main Area -->
            <div class="flex-1 bg-admin-theme-surface rounded-lg shadow-sm border border-admin-theme-border overflow-hidden">
                <div class="p-4 border-b border-admin-theme-border bg-admin-theme-base flex justify-between items-center">
                    <h2 class="text-sm font-medium text-admin-theme-text">
                        {{ selectedCategoryName }}
                    </h2>
                </div>
                
                <div v-if="isLoading" class="p-12 text-center flex justify-center">
                    <ArrowPathIcon class="w-8 h-8 animate-spin text-admin-theme-primary" />
                </div>
                
                <div v-else-if="filteredPresets.length === 0" class="p-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-admin-theme-base mb-4">
                        <ArchiveBoxIcon class="w-8 h-8 text-admin-theme-text-muted" />
                    </div>
                    <h3 class="text-lg font-medium text-admin-theme-text mb-1">No presets found</h3>
                    <p class="text-sm text-admin-theme-text-muted mb-4">You can create presets from the block editor or add them manually here.</p>
                    <button @click="openPresetModal(null)" class="px-4 py-2 bg-admin-theme-base text-admin-theme-text border border-admin-theme-border rounded-lg hover:bg-admin-theme-surface transition-colors text-sm">
                        + Create Preset
                    </button>
                </div>
                
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                    <div v-for="preset in filteredPresets" :key="preset.id" class="border border-admin-theme-border rounded-lg p-4 hover:shadow-md transition-shadow bg-admin-theme-surface">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-medium text-admin-theme-text text-sm truncate pr-2">{{ preset.name }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-admin-theme-primary/10 text-admin-theme-primary shrink-0">
                                {{ preset.type }}
                            </span>
                        </div>
                        <p class="text-xs text-admin-theme-text-muted mb-4 h-8 overflow-hidden text-ellipsis">{{ preset.description || 'No description' }}</p>
                        
                        <!-- PREVIEW BLOCK -->
                        <div v-if="preset.type === 'button_style'" class="mb-4 flex items-center justify-center p-4 bg-admin-theme-base/50 rounded-lg border border-admin-theme-border overflow-hidden h-24">
                            <ButtonBlock :modelValue="preset.payload" mode="preview" />
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t border-admin-theme-border">
                            <span class="text-[10px] text-admin-theme-text-muted">{{ preset.is_system ? 'System' : 'Custom' }}</span>
                            <div class="flex space-x-2">
                                <button @click="openPresetModal(preset)" class="text-admin-theme-text-muted hover:text-admin-theme-primary text-xs flex items-center" title="Edit">
                                    <PencilIcon class="w-3 h-3 mr-1" /> Edit
                                </button>
                                <button v-if="!preset.is_system" @click="deletePreset(preset)" class="text-admin-theme-text-muted hover:text-red-500 text-xs flex items-center" title="Delete">
                                    <TrashIcon class="w-3 h-3 mr-1" /> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <div v-if="isCategoryModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-admin-theme-surface rounded-lg shadow-xl w-96 max-w-full border border-admin-theme-border">
                <div class="px-4 py-3 border-b border-admin-theme-border flex justify-between items-center bg-admin-theme-base rounded-t-lg">
                    <h3 class="text-lg font-medium text-admin-theme-text">{{ editingCategory ? 'Edit Category' : 'New Category' }}</h3>
                    <button @click="isCategoryModalOpen = false" class="text-admin-theme-text-muted hover:text-admin-theme-text">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-admin-theme-text mb-1">Name</label>
                        <input type="text" v-model="categoryForm.name" class="w-full border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/20 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-admin-theme-text mb-1">Parent Category</label>
                        <select v-model="categoryForm.parent_id" class="w-full border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/20 sm:text-sm">
                            <option :value="null">None (Root Category)</option>
                            <option v-for="cat in categories.filter(c => c.id !== editingCategory?.id)" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                        <p class="text-[10px] text-admin-theme-text-muted mt-1">Optional. Select a parent to make this a sub-category.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-admin-theme-text mb-1">Description</label>
                        <textarea v-model="categoryForm.description" rows="2" class="w-full border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/20 sm:text-sm"></textarea>
                    </div>
                </div>
                <div class="px-4 py-3 bg-admin-theme-base border-t border-admin-theme-border flex justify-end space-x-2 rounded-b-lg">
                    <button @click="isCategoryModalOpen = false" type="button" class="px-4 py-2 text-sm font-medium text-admin-theme-text bg-admin-theme-surface border border-admin-theme-border rounded-md shadow-sm hover:bg-admin-theme-base transition-colors">Cancel</button>
                    <button @click="saveCategory" :disabled="isSaving || !categoryForm.name" type="button" class="px-4 py-2 text-sm font-medium text-admin-theme-primary-content bg-admin-theme-primary rounded-md shadow-sm hover:bg-admin-theme-primary-hover disabled:opacity-50 transition-colors flex items-center">
                        <ArrowPathIcon v-if="isSaving" class="w-4 h-4 mr-1 animate-spin" /> Save
                    </button>
                </div>
            </div>
        </div>

        <!-- Preset Edit Modal -->
        <div v-if="isPresetModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-admin-theme-surface rounded-lg shadow-xl w-full max-w-5xl border border-admin-theme-border flex flex-col max-h-[90vh]">
                <div class="px-4 py-3 border-b border-admin-theme-border flex justify-between items-center bg-admin-theme-base rounded-t-lg">
                    <h3 class="text-lg font-medium text-admin-theme-text">{{ editingPreset ? 'Edit Preset' : 'New Preset' }}</h3>
                    <button @click="isPresetModalOpen = false" class="text-admin-theme-text-muted hover:text-admin-theme-text">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <!-- Left Column: Basic Info & Live Preview -->
                        <div class="lg:col-span-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-admin-theme-text mb-1">Name</label>
                                <input type="text" v-model="presetForm.name" class="w-full border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/20 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-admin-theme-text mb-1">Type</label>
                                <select v-model="presetForm.type" class="w-full border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/20 sm:text-sm" :disabled="!!editingPreset">
                                    <option value="button_style">Button Style</option>
                                    <option value="text_snippet">Text Snippet</option>
                                    <option value="macro">Macro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-admin-theme-text mb-1">Category</label>
                                <select v-model="presetForm.category_id" class="w-full border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/20 sm:text-sm">
                                    <option :value="null">None (Uncategorized)</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-admin-theme-text mb-1">Description</label>
                                <textarea v-model="presetForm.description" rows="3" class="w-full border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/20 sm:text-sm"></textarea>
                            </div>
                            
                            <div v-if="presetForm.type === 'button_style' && parsedFormPayload" class="sticky top-0 pt-4">
                                <label class="block text-sm font-medium text-admin-theme-text mb-2">Live Preview</label>
                                <div class="flex items-center justify-center p-6 bg-admin-theme-base/50 rounded-lg border border-admin-theme-border overflow-hidden min-h-[120px]">
                                    <ButtonBlock :modelValue="parsedFormPayload" mode="preview" />
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Settings Panel -->
                        <div class="lg:col-span-7 h-full flex flex-col">
                            <div class="border border-admin-theme-border rounded-lg overflow-hidden bg-admin-theme-base flex-1 flex flex-col">
                                <div class="flex bg-admin-theme-surface border-b border-admin-theme-border shrink-0">
                                    <button @click="editorMode = 'visual'" type="button" :class="editorMode === 'visual' ? 'bg-admin-theme-base font-medium text-admin-theme-primary border-b-2 border-admin-theme-primary' : 'text-admin-theme-text-muted hover:text-admin-theme-text'" class="px-6 py-3 text-sm transition-colors">Visual Editor</button>
                                    <button @click="editorMode = 'code'" type="button" :class="editorMode === 'code' ? 'bg-admin-theme-base font-medium text-admin-theme-primary border-b-2 border-admin-theme-primary' : 'text-admin-theme-text-muted hover:text-admin-theme-text'" class="px-6 py-3 text-sm transition-colors">JSON Code</button>
                                </div>
                                
                                <div class="p-0 flex-1 overflow-y-auto custom-scrollbar">
                                    <!-- Code Mode -->
                                    <div v-show="editorMode === 'code'" class="p-4 h-full flex flex-col">
                                        <textarea v-model="presetForm.payload_raw" class="flex-1 w-full font-mono text-xs border border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring focus:ring-admin-theme-primary/20 p-4" placeholder="{}" style="min-height: 400px;"></textarea>
                                        <p class="text-[10px] text-admin-theme-text-muted mt-2">Must be valid JSON.</p>
                                    </div>

                                    <!-- Visual Mode -->
                                    <div v-show="editorMode === 'visual'" class="p-6">
                                        <div v-if="presetForm.type === 'button_style'">
                                            <ButtonSettings 
                                                :modelValue="parsedFormPayload || {}" 
                                                @update:modelValue="handleVisualUpdate" 
                                                :hidePresetManager="true"
                                            />
                                        </div>
                                        <div v-else class="text-sm text-admin-theme-text-muted text-center py-12">
                                            Visual editor not available for this preset type.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-admin-theme-base border-t border-admin-theme-border flex justify-end space-x-2 rounded-b-lg">
                    <button @click="isPresetModalOpen = false" type="button" class="px-4 py-2 text-sm font-medium text-admin-theme-text bg-admin-theme-surface border border-admin-theme-border rounded-md shadow-sm hover:bg-admin-theme-base transition-colors">Cancel</button>
                    <button @click="savePreset" :disabled="isSaving || !presetForm.name" type="button" class="px-4 py-2 text-sm font-medium text-admin-theme-primary-content bg-admin-theme-primary rounded-md shadow-sm hover:bg-admin-theme-primary-hover disabled:opacity-50 transition-colors flex items-center">
                        <ArrowPathIcon v-if="isSaving" class="w-4 h-4 mr-1 animate-spin" /> Save
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useDialog } from '@/admin/composables/useDialog';
import ButtonBlock from '@/admin/components/editor/blocks/atomic/ButtonBlock.vue';
import ButtonSettings from '@/admin/components/editor/panels/blocks/ButtonSettings.vue';
import { 
    FolderIcon, 
    RectangleGroupIcon, 
    PencilIcon, 
    TrashIcon, 
    ArchiveBoxIcon, 
    XMarkIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

const dialog = useDialog();

const categories = ref<any[]>([]);
const presets = ref<any[]>([]);
const isLoading = ref(false);
const selectedCategoryId = ref<number | null>(null);

// Form States
const isCategoryModalOpen = ref(false);
const editingCategory = ref<any>(null);
const categoryForm = ref({ name: '', description: '', parent_id: null as number | null });

const isPresetModalOpen = ref(false);
const editingPreset = ref<any>(null);
const presetForm = ref({ name: '', description: '', category_id: null as number | null, type: 'button_style', payload_raw: '{}' });
const isSaving = ref(false);
const editorMode = ref<'visual' | 'code'>('visual');

const parsedFormPayload = computed(() => {
    try {
        return JSON.parse(presetForm.value.payload_raw);
    } catch (e) {
        return null;
    }
});

const handleVisualUpdate = (newPayload: any) => {
    presetForm.value.payload_raw = JSON.stringify(newPayload, null, 4);
};

const fetchData = async () => {
    isLoading.value = true;
    try {
        const [catRes, presetRes] = await Promise.all([
            axios.get('/api/v1/presets/categories'),
            axios.get('/api/v1/presets')
        ]);
        categories.value = catRes.data.data;
        presets.value = presetRes.data.data;
    } catch (e) {
        console.error(e);
        dialog.error('Failed to load presets data');
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchData();
});

const selectedCategoryName = computed(() => {
    if (selectedCategoryId.value === null) return 'All Presets';
    const cat = categories.value.find(c => c.id === selectedCategoryId.value);
    return cat ? cat.name : 'Unknown Category';
});

const filteredPresets = computed(() => {
    if (selectedCategoryId.value === null) return presets.value;
    return presets.value.filter(p => p.category_id === selectedCategoryId.value);
});

const selectCategory = (id: number | null) => {
    selectedCategoryId.value = id;
};

// Category Logic
const openCategoryModal = (cat: any = null) => {
    editingCategory.value = cat;
    if (cat) {
        categoryForm.value = { name: cat.name, description: cat.description || '', parent_id: cat.parent_id };
    } else {
        categoryForm.value = { name: '', description: '', parent_id: null };
    }
    isCategoryModalOpen.value = true;
};

const saveCategory = async () => {
    isSaving.value = true;
    try {
        if (editingCategory.value) {
            await axios.put(`/api/v1/presets/categories/${editingCategory.value.id}`, categoryForm.value);
        } else {
            await axios.post('/api/v1/presets/categories', categoryForm.value);
        }
        await fetchData();
        isCategoryModalOpen.value = false;
    } catch (e) {
        console.error(e);
        dialog.error('Failed to save category');
    } finally {
        isSaving.value = false;
    }
};

const deleteCategory = async (cat: any) => {
    const isConfirmed = await dialog.confirm(
        'Delete Category? This will not delete the presets inside it, they will become uncategorized.'
    );

    if (isConfirmed) {
        try {
            await axios.delete(`/api/v1/presets/categories/${cat.id}`);
            if (selectedCategoryId.value === cat.id) selectedCategoryId.value = null;
            await fetchData();
        } catch (e) {
            console.error(e);
            dialog.error('Failed to delete category');
        }
    }
};

// Preset Logic
const openPresetModal = (preset: any = null) => {
    editingPreset.value = preset;
    if (preset) {
        presetForm.value = { 
            name: preset.name, 
            description: preset.description || '', 
            category_id: preset.category_id,
            type: preset.type,
            payload_raw: JSON.stringify(preset.payload, null, 2)
        };
    } else {
        presetForm.value = { 
            name: '', 
            description: '', 
            category_id: selectedCategoryId.value,
            type: 'button_style',
            payload_raw: '{\n  \n}'
        };
    }
    isPresetModalOpen.value = true;
};

const savePreset = async () => {
    isSaving.value = true;
    try {
        let parsedPayload = {};
        try {
            parsedPayload = JSON.parse(presetForm.value.payload_raw);
        } catch (err) {
            dialog.error('Invalid JSON payload');
            isSaving.value = false;
            return;
        }

        const dataToSend = {
            ...presetForm.value,
            payload: parsedPayload
        };

        if (editingPreset.value) {
            await axios.put(`/api/v1/presets/${editingPreset.value.id}`, dataToSend);
        } else {
            await axios.post('/api/v1/presets', dataToSend);
        }
        await fetchData();
        isPresetModalOpen.value = false;
    } catch (e) {
        console.error(e);
        dialog.error('Failed to save preset');
    } finally {
        isSaving.value = false;
    }
};

const deletePreset = async (preset: any) => {
    const isConfirmed = await dialog.confirm('Delete Preset? This action cannot be undone.');

    if (isConfirmed) {
        try {
            await axios.delete(`/api/v1/presets/${preset.id}`);
            await fetchData();
        } catch (e) {
            console.error(e);
            dialog.error('Failed to delete preset');
        }
    }
};
</script>
