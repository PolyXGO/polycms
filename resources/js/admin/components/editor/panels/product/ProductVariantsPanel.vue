<template>
    <div v-if="form" class="variant-panel">
        <div class="variant-panel__content">
            <!-- Prompt to enable when not variable type -->
            <div v-if="form.type !== 'variable' && selectedAttributes.length === 0" class="variant-panel__enable">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $t('This product does not have variants enabled.') }}</p>
                <button type="button" @click="enableVariants" class="variant-panel__enable-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ $t('Enable Variants') }}
                </button>
            </div>

            <!-- Main content (when variable or has attributes) -->
            <template v-if="form.type === 'variable' || selectedAttributes.length > 0">
            <!-- Header -->
            <div class="variant-panel__header">
                <h3 class="variant-panel__title">{{ $t('Product Variants') }}</h3>
                <div class="relative inline-block text-left" v-if="hasAvailableGlobalAttributes">
                    <button type="button" @click="showingAttributeDropdown = !showingAttributeDropdown" class="variant-panel__add-btn">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ $t('Select Attribute') }}
                    </button>
                    <!-- Dropdown menu -->
                    <div v-if="showingAttributeDropdown" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-10 overflow-hidden">
                        <div class="py-1 max-h-64 overflow-y-auto" role="menu" aria-orientation="vertical">
                            <template v-for="(attrs, groupName) in groupedAvailableGlobalAttributes" :key="groupName">
                                <div class="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-700">
                                    {{ groupName || $t('Uncategorized') }}
                                </div>
                                <button
                                    v-for="gba in attrs"
                                    :key="gba.id"
                                    @click="selectGlobalAttribute(gba)"
                                    class="w-full text-left block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900"
                                    role="menuitem"
                                >
                                    {{ gba.name }}
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <div v-else-if="globalAttributesLoaded" class="text-xs text-gray-500">
                    {{ selectedAttributes.length ? $t('All attributes added') : $t('No global attributes exist.') }}
                    <a href="/admin/products/attributes" class="text-indigo-600 hover:underline inline-block ml-1">{{ $t('Manage') }}</a>
                </div>
            </div>

            <!-- Attributes Selection -->
            <div v-for="(sAttr, idx) in selectedAttributes" :key="idx" class="variant-attr">
                <div class="variant-attr__header">
                    <div>
                        <div class="flex items-center gap-3">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ sAttr.globalDef.name }}</h4>
                            <div class="flex items-center">
                                <input type="checkbox" v-model="sAttr.is_specification" :id="'spec-' + idx" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer">
                                <label :for="'spec-' + idx" class="ml-1.5 text-xs text-gray-500 cursor-pointer select-none">{{ $t('Show in Specification tab') }}</label>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $t('Select values to generate variants') }}</p>
                    </div>
                    <button type="button" @click="removeSelectedAttribute(idx)" class="variant-attr__remove-btn" :title="$t('Remove attribute')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>

                <div class="flex flex-wrap gap-2 mt-3">
                    <label
                        v-for="val in sAttr.globalDef.values"
                        :key="val.id"
                        class="variant-value-checkbox cursor-pointer select-none"
                        :class="{'variant-value-checkbox--active': sAttr.selected_value_ids.includes(val.id)}"
                    >
                        <input type="checkbox" :value="val.id" v-model="sAttr.selected_value_ids" class="sr-only">
                        <span v-if="sAttr.globalDef.display_type === 'color_swatch' && val.hex_color" class="w-3 h-3 rounded-full mr-1.5 border border-black/10 inline-block align-middle" :style="{ backgroundColor: val.hex_color }"></span>
                        <span class="align-middle">{{ val.value }}</span>
                    </label>
                </div>
            </div>

            <!-- Generate Variants -->
            <div v-if="selectedAttributes.length > 0" class="variant-panel__generate">
                <div v-if="hasValidSelections" class="variant-panel__generate-info">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ potentialVariantCount }} {{ $t('combinations will be generated') }}
                    </span>
                </div>
                <button
                    v-if="hasValidSelections"
                    type="button"
                    @click="generateVariants"
                    class="variant-panel__generate-btn"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    {{ $t('Generate Variants') }}
                </button>
            </div>

            <!-- Variants Table -->
            <div v-if="variants.length > 0" class="variant-table-wrap">
                <div class="variant-table__header">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $t('Variant List') }} ({{ variants.length }})</h4>
                </div>
                <div class="variant-table-scroll">
                    <table class="variant-table">
                        <thead>
                            <tr>
                                <th class="text-left w-14">{{ $t('Image') }}</th>
                                <th class="text-left">{{ $t('Variant') }}</th>
                                <th class="text-left">{{ $t('SKU') }}</th>
                                <th class="text-right">{{ $t('Price') }}</th>
                                <th class="text-right">{{ $t('Sale') }}</th>
                                <th class="text-right">{{ $t('Stock') }}</th>
                                <th class="text-center">{{ $t('Active') }}</th>
                                <th class="text-center">{{ $t('Default') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(variant, vIdx) in variants" :key="vIdx">
                                <td>
                                    <div 
                                        @click="openMediaPickerForVariant(vIdx)"
                                        class="w-10 h-10 border border-gray-200 dark:border-gray-700 rounded overflow-hidden flex items-center justify-center bg-gray-50 dark:bg-gray-800 hover:ring-2 hover:ring-indigo-500 transition-all relative group cursor-pointer"
                                        :title="$t('Select image')"
                                    >
                                        <img v-if="getVariantImageUrl(variant)" :src="getVariantImageUrl(variant)" class="block w-full h-full object-cover"/>
                                        <svg v-else class="w-4 h-4 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        
                                        <button v-if="variant.image_id" type="button" @click.stop="removeVariantImage(vIdx)" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" :title="$t('Remove image')">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <span class="variant-table__name">{{ variantDisplayName(variant) }}</span>
                                </td>
                                <td>
                                    <input v-model="variant.sku" type="text" :placeholder="$t('Auto')" class="variant-table__input variant-table__input--sku"/>
                                </td>
                                <td>
                                    <input v-model.number="variant.price" type="number" min="0" step="0.01" :placeholder="String(form.price || 0)" class="variant-table__input variant-table__input--price"/>
                                </td>
                                <td>
                                    <input v-model.number="variant.sale_price" type="number" min="0" step="0.01" placeholder="-" class="variant-table__input variant-table__input--price"/>
                                </td>
                                <td>
                                    <input v-model.number="variant.stock_quantity" type="number" min="0" class="variant-table__input variant-table__input--stock"/>
                                </td>
                                <td class="text-center">
                                    <input v-model="variant.is_active" type="checkbox" class="variant-table__checkbox"/>
                                </td>
                                <td class="text-center">
                                    <input type="radio" name="default_variant" :checked="variant.is_default" @change="setDefaultVariant(vIdx)" class="variant-table__radio"/>
                                </td>
                                <td>
                                    <button type="button" @click="removeVariant(vIdx)" class="variant-table__remove">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            </template>
        </div>
        
        <!-- Media Picker Component -->
        <MediaPicker 
            ref="mediaPickerRef" 
            @select="handleMediaSelect" 
            :multiple="false" 
            :accepted-types="['image/*']" 
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, inject, getCurrentInstance, watch, onMounted } from 'vue';
import { EditorContextKey } from '../../../../editor/context';
import MediaPicker from '../../../MediaPicker.vue';
import axios from 'axios';

const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || ((s: string) => s);

const context = inject(EditorContextKey);
if (!context) throw new Error('ProductVariantsPanel must be used within editor context');

const form = context.form;

// Global Attributes Data
interface GlobalAttributeValue { id: number; value: string; slug: string; hex_color: string | null; }
interface GlobalAttribute { id: number; name: string; slug: string; display_type: string; values: GlobalAttributeValue[]; }
const globalAttributes = ref<GlobalAttribute[]>([]);
const globalAttributesLoaded = ref(false);

// Selected Attributes Data
interface SelectedAttribute {
    attribute_id: number;
    globalDef: GlobalAttribute;
    selected_value_ids: number[];
    is_specification?: boolean;
}

const selectedAttributes = ref<SelectedAttribute[]>([]);

// Variants Data
interface Variant { attribute_values: Record<string, string>; sku: string; price: number | null; sale_price: number | null; stock_quantity: number; stock_status: string; manage_stock: boolean; is_active: boolean; position: number; image_id?: number; image?: any; is_default?: boolean; }
const variants = ref<Variant[]>([]);

const showingAttributeDropdown = ref(false);

// Media Picker Support
const mediaPickerRef = ref<any>(null);
const editingVariantIndex = ref<number | null>(null);

function openMediaPickerForVariant(idx: number) {
    editingVariantIndex.value = idx;
    if (mediaPickerRef.value) {
        mediaPickerRef.value.open();
    }
}

function handleMediaSelect(media: any) {
    if (editingVariantIndex.value !== null && variants.value[editingVariantIndex.value]) {
        variants.value[editingVariantIndex.value].image_id = media.id;
        // Also keep temporary display url
        (variants.value[editingVariantIndex.value] as any)._temp_image_url = media.url;
    }
    editingVariantIndex.value = null;
}

function removeVariantImage(idx: number) {
    variants.value[idx].image_id = undefined;
    if ((variants.value[idx] as any)._temp_image_url) {
        delete (variants.value[idx] as any)._temp_image_url;
    }
    if ((variants.value[idx] as any).image) {
        delete (variants.value[idx] as any).image;
    }
}

function getVariantImageUrl(variant: any): string | undefined {
    if (variant._temp_image_url) return variant._temp_image_url;
    if (variant.image?.url) return variant.image.url;
    return undefined;
}

const groupedAvailableGlobalAttributes = computed(() => {
    const selectedIds = selectedAttributes.value.map(sa => sa.attribute_id);
    const available = globalAttributes.value.filter(ga => !selectedIds.includes(ga.id));
    
    const map: Record<string, GlobalAttribute[]> = {};
    available.forEach(attr => {
        const groupName = (attr as any).group?.name || '';
        if (!map[groupName]) map[groupName] = [];
        map[groupName].push(attr);
    });

    const sortedMap: Record<string, GlobalAttribute[]> = {};
    Object.keys(map).sort((a,b) => {
        if (!a) return 1;
        if (!b) return -1;
        return a.localeCompare(b);
    }).forEach(k => {
        sortedMap[k] = map[k];
    });
    return sortedMap;
});

const hasAvailableGlobalAttributes = computed(() => Object.keys(groupedAvailableGlobalAttributes.value).length > 0);

const hasValidSelections = computed(() => selectedAttributes.value.some(sa => sa.selected_value_ids.length > 0));
const potentialVariantCount = computed(() => {
    const valid = selectedAttributes.value.filter(sa => sa.selected_value_ids.length > 0);
    return valid.length === 0 ? 0 : valid.reduce((t, sa) => t * sa.selected_value_ids.length, 1);
});

onMounted(() => {
    fetchGlobalAttributes();
});

async function fetchGlobalAttributes() {
    try {
        const { data } = await axios.get('/api/v1/product-attributes');
        globalAttributes.value = data.data;
        globalAttributesLoaded.value = true;
        
        // Restore from form for Edit Mode
        const initialAttributes = (form.value && (form.value._attributes || form.value.attributes)) || [];
        if (initialAttributes.length > 0 && selectedAttributes.value.length === 0) {
            selectedAttributes.value = initialAttributes.map((dbAttr: any) => {
                const globalDef = globalAttributes.value.find(ga => ga.id === dbAttr.attribute_id) || dbAttr.globalDef;
                return {
                    attribute_id: dbAttr.attribute_id,
                    globalDef: globalDef,
                    selected_value_ids: dbAttr.selected_value_ids || [],
                    is_specification: dbAttr.is_specification !== undefined ? dbAttr.is_specification : true
                };
            }).filter((sa: any) => sa.globalDef);
        }
        
        const initialVariants = (form.value && (form.value._variants || form.value.variants)) || [];
        if (initialVariants.length > 0 && variants.value.length === 0) {
            variants.value = JSON.parse(JSON.stringify(initialVariants));
        }
    } catch (e) {
        console.error('Failed to load global attributes', e);
    }
}

function enableVariants() {
    if (form.value) form.value.type = 'variable';
}

function selectGlobalAttribute(gba: GlobalAttribute) {
    selectedAttributes.value.push({
        attribute_id: gba.id,
        globalDef: gba,
        selected_value_ids: [], // Start with none selected, let user click checkboxes
        is_specification: true
    });
    showingAttributeDropdown.value = false;
    if (form.value && form.value.type !== 'variable') {
        form.value.type = 'variable';
    }
}

function removeSelectedAttribute(idx: number) {
    selectedAttributes.value.splice(idx, 1);
    if (selectedAttributes.value.length === 0 && form.value) form.value.type = 'product';
}

function variantDisplayName(variant: Variant): string {
    return Object.values(variant.attribute_values).join(' / ');
}

function generateVariants() {
    const validAttrs = selectedAttributes.value.filter(sa => sa.selected_value_ids.length > 0);
    if (validAttrs.length === 0) return;
    
    const combinations = cartesian(validAttrs.map(sa => {
        // Map selected value IDs back to their string values
        const selectedValueStrings = sa.selected_value_ids.map(id => {
            const vDef = sa.globalDef.values.find(v => v.id === id);
            return vDef ? vDef.value : String(id);
        });
        
        return {
            key: sa.globalDef.slug,
            values: selectedValueStrings,
        };
    }));

    const existing = new Map<string, Variant>();
    variants.value.forEach(v => existing.set(JSON.stringify(v.attribute_values), v));

    variants.value = combinations.map((combo, idx) => {
        const key = JSON.stringify(combo);
        return existing.has(key)
            ? { ...existing.get(key)!, position: idx }
            : { attribute_values: combo, sku: '', price: null, sale_price: null, stock_quantity: 0, stock_status: 'in_stock', manage_stock: true, is_active: true, is_default: false, position: idx };
    });
}

const setDefaultVariant = (index: number) => {
    variants.value.forEach((v, i) => {
        v.is_default = (i === index);
    });
};

function removeVariant(idx: number) { variants.value.splice(idx, 1); }

function cartesian(attrs: { key: string; values: string[] }[]): Record<string, string>[] {
    if (attrs.length === 0) return [{}];
    const [first, ...rest] = attrs;
    const restCombos = cartesian(rest);
    const result: Record<string, string>[] = [];
    for (const val of first.values) for (const combo of restCombos) result.push({ [first.key]: val, ...combo });
    return result;
}

watch([selectedAttributes, variants], () => {
    if (form.value) {
        (form.value as any)._variants = variants.value;
        // Output format expected by Product store/update endpoints
        // Convert to new pivot structure
        (form.value as any)._attributes = selectedAttributes.value.map((sa, i) => ({
            attribute_id: sa.attribute_id,
            position: i,
            selected_value_ids: sa.selected_value_ids,
            is_specification: sa.is_specification ?? true
        }));
    }
}, { deep: true });

// Document listener to close dropdown
onMounted(() => {
    document.addEventListener('click', (e) => {
        if (!(e.target as Element).closest('.variant-panel__add-btn') && !(e.target as Element).closest('.origin-top-right')) {
            showingAttributeDropdown.value = false;
        }
    });
});
</script>

<style scoped>
.variant-panel { }
.variant-panel__content { display: flex; flex-direction: column; gap: 1rem; }

.variant-panel__enable { text-align: center; padding: 1.5rem; background: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 0.5rem; }
:root.dark .variant-panel__enable { background: rgba(30,41,59,0.5); border-color: #334155; }
.variant-panel__enable-btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500;
    color: #fff; background: #4f46e5; border: none; border-radius: 0.5rem;
    cursor: pointer; transition: background 0.15s;
}
.variant-panel__enable-btn:hover { background: #4338ca; }
.variant-panel__header { display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 20; }
.variant-panel__title { font-size: 0.875rem; font-weight: 600; color: #1e293b; }
:root.dark .variant-panel__title { color: #f1f5f9; }

.variant-panel__add-btn {
    display: inline-flex; align-items: center; gap: 0.375rem;
    padding: 0.375rem 0.75rem; font-size: 0.75rem; font-weight: 500;
    color: #4f46e5; background: #eef2ff; border: 1px solid #c7d2fe;
    border-radius: 0.5rem; cursor: pointer; transition: all 0.15s;
}
.variant-panel__add-btn:hover { background: #e0e7ff; border-color: #a5b4fc; }
:root.dark .variant-panel__add-btn { background: rgba(79,70,229,0.15); border-color: rgba(79,70,229,0.3); color: #a5b4fc; }

/* Attribute Block */
.variant-attr {
    padding: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem;
}
:root.dark .variant-attr { background: rgba(30,41,59,0.5); border-color: #334155; }

.variant-attr__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }

.variant-attr__remove-btn {
    padding: 0.5rem; color: #ef4444; background: none; border: none; cursor: pointer;
    border-radius: 0.375rem; transition: all 0.15s;
}
.variant-attr__remove-btn:hover { background: #fee2e2; }
:root.dark .variant-attr__remove-btn:hover { background: rgba(239,68,68,0.15); }

/* Custom Checkboxes */
.variant-value-checkbox {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 9999px;
    font-size: 0.8125rem;
    color: #4b5563;
    background-color: white;
    transition: all 0.2s;
}
:root.dark .variant-value-checkbox {
    border-color: #4b5563;
    background-color: #1f2937;
    color: #d1d5db;
}
.variant-value-checkbox:hover {
    border-color: #9ca3af;
    background-color: #f3f4f6;
}
:root.dark .variant-value-checkbox:hover {
    border-color: #6b7280;
    background-color: #374151;
}
.variant-value-checkbox--active {
    border-color: #4f46e5;
    background-color: #eef2ff;
    color: #4338ca;
    font-weight: 500;
}
:root.dark .variant-value-checkbox--active {
    border-color: #6366f1;
    background-color: rgba(99, 102, 241, 0.15);
    color: #e0e7ff;
}

/* Generate */
.variant-panel__generate { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.variant-panel__generate-btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 600;
    color: #fff; background: #4f46e5; border: none; border-radius: 0.5rem;
    cursor: pointer; transition: background 0.15s;
}
.variant-panel__generate-btn:hover { background: #4338ca; }

/* Variants Table */
.variant-table-wrap { border: 1px solid #e2e8f0; border-radius: 0.5rem; overflow: hidden; }
:root.dark .variant-table-wrap { border-color: #334155; }
.variant-table__header { padding: 0.75rem 1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
:root.dark .variant-table__header { background: #1e293b; border-color: #334155; }
.variant-table-scroll { overflow-x: auto; }
.variant-table { width: 100%; border-collapse: collapse; }
.variant-table th { padding: 0.5rem 0.75rem; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
:root.dark .variant-table th { color: #94a3b8; border-color: #334155; }
.variant-table td { padding: 0.5rem 0.75rem; border-bottom: 1px solid #f1f5f9; font-size: 0.8125rem; color: #0f172a; }
:root.dark .variant-table td { border-color: #1e293b; color: #e2e8f0; }
.variant-table tr:hover td { background: #f8fafc; }
:root.dark .variant-table tr:hover td { background: rgba(15,23,42,0.5); }

.variant-table__name { font-weight: 600; color: #1e293b; }
:root.dark .variant-table__name { color: #f1f5f9; }

.variant-table__input {
    padding: 0.25rem 0.375rem; font-size: 0.75rem;
    border: 1px solid #e2e8f0; border-radius: 0.25rem; background: #fff; color: #0f172a;
}
.variant-table__input:focus { outline: none; border-color: #6366f1; }
:root.dark .variant-table__input { background: #0f172a; color: #f1f5f9; border-color: #334155; }
.variant-table__input--sku { width: 5.5rem; }
.variant-table__input--price { width: 4.5rem; text-align: right; }
.variant-table__input--stock { width: 3.5rem; text-align: right; }

.variant-table__checkbox { width: 1rem; height: 1rem; accent-color: #4f46e5; cursor: pointer; }
.variant-table__remove { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 0.25rem; border-radius: 0.25rem; transition: all 0.15s; }
.variant-table__remove:hover { color: #ef4444; background: #fee2e2; }
:root.dark .variant-table__remove:hover { background: rgba(239,68,68,0.15); }
</style>
