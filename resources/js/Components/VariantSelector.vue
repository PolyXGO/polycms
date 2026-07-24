<template>
    <div v-if="attributes.length > 0" class="variant-selector">
        <!-- Attribute Selectors -->
        <div v-for="attr in attributes" :key="attr.id" class="variant-selector__group">
            <label class="variant-selector__label">{{ attr.name }}</label>

            <!-- Dropdown -->
            <select
                v-if="attr.display_type === 'select' || !attr.display_type"
                :value="selectedValues[attr.slug] || ''"
                @change="selectValue(attr.slug, ($event.target as HTMLSelectElement).value)"
                class="variant-selector__select"
            >
                <option value="" disabled>Choose {{ attr.name }}</option>
                <option v-for="val in getAttrValues(attr)" :key="val" :value="val">{{ val }}</option>
            </select>

            <!-- Button swatches -->
            <div v-else-if="attr.display_type === 'button'" class="variant-selector__buttons">
                <button
                    v-for="val in getAttrValues(attr)"
                    :key="val"
                    type="button"
                    @click="selectValue(attr.slug, val)"
                    class="variant-swatch variant-swatch--button"
                    :class="{ 'variant-swatch--active': selectedValues[attr.slug] === val }"
                >
                    {{ val }}
                </button>
            </div>

            <!-- Color swatches -->
            <div v-else-if="attr.display_type === 'color_swatch'" class="variant-selector__buttons">
                <button
                    v-for="val in getColorValues(attr)"
                    :key="val.value"
                    type="button"
                    @click="selectValue(attr.slug, val.value)"
                    class="variant-swatch variant-swatch--color"
                    :class="{ 'variant-swatch--active': selectedValues[attr.slug] === val.value }"
                    :title="val.value"
                >
                    <span class="variant-swatch__color-dot" :style="{ backgroundColor: val.hex || '#ccc' }"></span>
                </button>
            </div>
        </div>

        <!-- Selected Variant Info -->
        <div v-if="selectedVariant" class="variant-selector__info">
            <div v-if="selectedVariant.sku" class="variant-selector__sku">
                SKU: {{ selectedVariant.sku }}
            </div>
            <div class="variant-selector__stock" :class="stockClass">
                {{ stockLabel }}
            </div>
        </div>
        <div v-else-if="hasSelections" class="variant-selector__info variant-selector__info--warning">
            This combination is not available.
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

interface Attribute {
    id: number;
    name: string;
    slug: string;
    values: (string | { value: string; hex?: string })[];
    display_type: string;
}

interface Variant {
    id: number;
    attribute_values: Record<string, string>;
    sku: string;
    price: number | null;
    sale_price: number | null;
    stock_quantity: number;
    stock_status: string;
    is_active: boolean;
}

const props = defineProps<{
    attributes: Attribute[];
    variants: Variant[];
    productPrice: number;
}>();

const emit = defineEmits<{
    (e: 'variant-selected', variant: Variant | null): void;
    (e: 'price-changed', price: number, salePrice: number | null): void;
}>();

const selectedValues = ref<Record<string, string>>({});

const hasSelections = computed(() => Object.keys(selectedValues.value).length > 0);

const selectedVariant = computed(() => {
    const attrs = props.attributes;
    // All attributes must be selected
    if (attrs.some(a => !selectedValues.value[a.slug])) return null;

    return props.variants.find(v => {
        return v.is_active && attrs.every(a => {
            return v.attribute_values[a.slug] === selectedValues.value[a.slug];
        });
    }) || null;
});

const stockClass = computed(() => {
    if (!selectedVariant.value) return '';
    switch (selectedVariant.value.stock_status) {
        case 'in_stock': return 'variant-selector__stock--in';
        case 'out_of_stock': return 'variant-selector__stock--out';
        case 'on_backorder': return 'variant-selector__stock--backorder';
        default: return '';
    }
});

const stockLabel = computed(() => {
    if (!selectedVariant.value) return '';
    const v = selectedVariant.value;
    switch (v.stock_status) {
        case 'in_stock': return v.stock_quantity > 0 ? `In Stock (${v.stock_quantity} available)` : 'In Stock';
        case 'out_of_stock': return 'Out of Stock';
        case 'on_backorder': return 'Available on Backorder';
        default: return '';
    }
});

function getAttrValues(attr: Attribute): string[] {
    return attr.values.map(v => typeof v === 'string' ? v : v.value);
}

function getColorValues(attr: Attribute): { value: string; hex?: string }[] {
    return attr.values.map(v => typeof v === 'string' ? { value: v } : v);
}

function selectValue(slug: string, value: string) {
    selectedValues.value = { ...selectedValues.value, [slug]: value };
}

watch(selectedVariant, (variant) => {
    emit('variant-selected', variant);
    if (variant) {
        const price = variant.price ?? props.productPrice;
        const salePrice = variant.sale_price;
        emit('price-changed', price, salePrice);
    }
}, { immediate: true });
</script>

<style scoped>
.variant-selector { display: flex; flex-direction: column; gap: 1rem; margin: 1.25rem 0; }
.variant-selector__group { display: flex; flex-direction: column; gap: 0.375rem; }
.variant-selector__label { font-size: 0.875rem; font-weight: 600; color: #374151; }
.variant-selector__select { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; background: #fff; color: #0f172a; cursor: pointer; }
.variant-selector__select:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

.variant-selector__buttons { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.variant-swatch { cursor: pointer; transition: all 0.15s; border: 2px solid transparent; }
.variant-swatch--button { padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.8125rem; font-weight: 500; background: #f1f5f9; color: #334155; border-color: #e2e8f0; }
.variant-swatch--button:hover { border-color: #6366f1; background: #eef2ff; }
.variant-swatch--button.variant-swatch--active { border-color: #4f46e5; background: #eef2ff; color: #4f46e5; font-weight: 600; }

.variant-swatch--color { width: 2rem; height: 2rem; border-radius: 50%; padding: 3px; border-color: #d1d5db; background: #fff; display: flex; align-items: center; justify-content: center; }
.variant-swatch--color:hover { border-color: #6366f1; transform: scale(1.1); }
.variant-swatch--color.variant-swatch--active { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.3); }
.variant-swatch__color-dot { width: 100%; height: 100%; border-radius: 50%; border: 1px solid rgba(0,0,0,0.1); }

.variant-selector__info { font-size: 0.8125rem; padding: 0.5rem 0; }
.variant-selector__info--warning { color: #f59e0b; }
.variant-selector__sku { color: #94a3b8; margin-bottom: 0.25rem; }
.variant-selector__stock { font-weight: 500; }
.variant-selector__stock--in { color: #059669; }
.variant-selector__stock--out { color: #dc2626; }
.variant-selector__stock--backorder { color: #d97706; }

/* Dark mode */
:root.dark .variant-selector__label { color: #d1d5db; }
:root.dark .variant-selector__select { background: #1e293b; color: #f1f5f9; border-color: #475569; }
:root.dark .variant-swatch--button { background: #1e293b; color: #d1d5db; border-color: #475569; }
:root.dark .variant-swatch--button:hover { background: #312e81; border-color: #6366f1; }
:root.dark .variant-swatch--button.variant-swatch--active { background: #312e81; color: #a5b4fc; }
</style>
