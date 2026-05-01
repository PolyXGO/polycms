<template>
    <div class="pricing-matrix-wrapper" :style="{ padding: state.padding }">
        
        <!-- Settings Mode: Show Controls -->
        <div v-if="mode === 'settings'">
            <div v-if="!adminSettingsLoaded" class="p-4 text-center text-gray-400">
                <i class="fas fa-spinner fa-spin mr-2"></i> Loading currency settings...
            </div>
            <div v-else>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Pricing Matrix</h4>
                        <p class="text-xs text-gray-500">
                            Configure display style.
                        </p>
                    </div>
                </div>
                
                <div class="form-group mb-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Display Style</label>
                    <select v-model="state.style" class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm">
                        <option value="cards">Horizontal Cards</option>
                        <option value="table">Comparison Table</option>
                        <option value="list">Simple List</option>
                    </select>
                </div>
                <div class="mt-4 p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-md">
                    <p class="text-[10px] text-indigo-700 dark:text-indigo-300 italic">
                        Note: Data is pulled from "Service Configuration".
                    </p>
                </div>
            </div>
        </div>

        <!-- Preview Mode: Show Content -->
        <div v-else>
            <!-- Placeholder if no packages -->
            <div v-if="!hasPackages" class="p-6 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">Pricing Matrix</h4>
                    <p class="text-xs text-gray-500">
                        This block will automatically display the product's pricing packages.
                        <span class="block mt-1 text-indigo-600 dark:text-indigo-400">Please define packages in Service Configuration.</span>
                    </p>
                </div>
            </div>

            <!-- Live Preview -->
            <div v-else class="pricing">
                <!-- Cards Style -->
                <div v-if="state.style === 'cards'" class="pricing-cards">
                    <div v-for="(pkg, index) in packages" :key="index" class="pricing-card" :class="{ 'featured': pkg.is_featured }">
                        <div class="pricing-header">
                            <h3>{{ pkg.name }}</h3>
                            <div class="price">
                                {{ formatPrice(pkg.price) }}
                            </div>
                            <p>
                                <span v-if="pkg.is_featured">Most Popular</span>
                                <span v-else-if="pkg.duration_value && pkg.access_type !== 'permanent'">
                                    {{ pkg.duration_value }} {{ pkg.duration_unit }}{{ pkg.duration_value > 1 ? 's' : '' }}
                                </span>
                                <span v-else-if="pkg.access_type === 'permanent'">One-time payment</span>
                                <span v-else>Per use</span>
                            </p>
                        </div>
                        <ul class="pricing-features">
                            <li v-for="(val, key) in (pkg.capabilities || {})" :key="key">
                                <i class="fas fa-check"></i> 
                                <span>
                                    <template v-if="val === true || val === 'true' || val === 1 || val === '1'">
                                        {{ key }}
                                    </template>
                                    <template v-else>
                                        {{ val }}
                                    </template>
                                </span>
                            </li>
                        </ul>
                        <div class="pricing-action">
                            <a href="#" class="primary-btn" @click.prevent>Select {{ pkg.name }}</a>
                        </div>
                    </div>
                </div>

                <!-- Comparison Table Style -->
                <div v-else-if="state.style === 'table'" class="pricing-table-container" style="overflow-x: auto; background: #fff; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #f1f5f9;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 600px; table-layout: fixed;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="text-align: left; padding: 2rem 1.5rem; border-bottom: 2px solid #e2e8f0; vertical-align: bottom; width: 25%;">
                                    <span style="font-size: 1.1rem; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em;">Features</span>
                                </th>
                                <th v-for="(pkg, index) in packages" :key="index" style="text-align: center; padding: 2rem 1rem; border-bottom: 2px solid #e2e8f0;">
                                    <div style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">{{ pkg.name }}</div>
                                    <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary); background: var(--gradient); -webkit-background-clip: text; background-clip: text; color: transparent;">
                                        {{ formatPrice(pkg.price) }}
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="key in allCapabilityKeys" :key="key" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1.25rem 1.5rem; font-weight: 500; color: #475569; background: #fff;">{{ key }}</td>
                                <td v-for="(pkg, index) in packages" :key="index" style="text-align: center; padding: 1.25rem 1rem; background: #fff;">
                                    <template v-if="(pkg.capabilities && (pkg.capabilities[key] === true || pkg.capabilities[key] === 'true' || pkg.capabilities[key] === 1 || pkg.capabilities[key] === '1'))">
                                        <div style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #ecfdf5; border-radius: 50%; margin: 0 auto;">
                                            <i class="fas fa-check" style="color: #10b981; font-size: 0.875rem;"></i>
                                        </div>
                                    </template>
                                    <template v-else-if="(pkg.capabilities && !pkg.capabilities[key])">
                                         <i class="fas fa-times" style="color: #cbd5e1; font-size: 1rem;"></i>
                                    </template>
                                    <template v-else>
                                        <span style="font-weight: 500; color: #334155;">{{ pkg.capabilities ? pkg.capabilities[key] : '' }}</span>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc;">
                                <td style="padding: 1.5rem;"></td>
                                <td v-for="(pkg, index) in packages" :key="index" style="text-align: center; padding: 1.5rem 1rem;">
                                    <a href="#" class="primary-btn" style="display: inline-block; padding: 12px 28px; font-weight: 600; border-radius: 0.75rem; width: 100%; max-width: 160px; text-align: center;" @click.prevent>
                                        Select
                                    </a>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Simple List Style -->
                <div v-else-if="state.style === 'list'" class="pricing-list" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div v-for="(pkg, index) in packages" :key="index" class="pricing-list-item" style="display: grid; grid-template-columns: auto 1fr auto auto; align-items: center; gap: 1.5rem; padding: 1.25rem 1.5rem; border-radius: 1rem; background: #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #f8fafc;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--gradient, linear-gradient(135deg, #4361ee 0%, #7209b7 100%)); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                            <i class="fas fa-rocket"></i>
                        </div>
                        
                        <div style="min-width: 0;">
                            <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 700; color: #1e293b;">{{ pkg.name }}</h3>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <span v-for="(val, key) in (pkg.capabilities ? Object.fromEntries(Object.entries(pkg.capabilities).slice(0, 3)) : {})" :key="key" style="font-size: 0.75rem; padding: 2px 10px; background: #f1f5f9; border-radius: 999px; color: #64748b; font-weight: 500; white-space: nowrap;">
                                    {{ key }}
                                </span>
                            </div>
                        </div>

                        <div style="text-align: right; white-space: nowrap;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); background: var(--gradient); -webkit-background-clip: text; background-clip: text; color: transparent;">
                                {{ formatPrice(pkg.price) }}
                            </div>
                            <div v-if="pkg.duration_value" style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">
                                / {{ pkg.duration_value }} {{ pkg.duration_unit }}
                            </div>
                        </div>

                        <div>
                            <a href="#" class="primary-btn" style="display: inline-block; padding: 10px 24px; font-weight: 600; border-radius: 999px; white-space: nowrap;" @click.prevent>
                                Select
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch, inject, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { EditorContextKey } from '../../../editor/context';
import { useCurrency } from '../../../../Composables/useCurrency';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

// Access Editor Context for Live Data
const context = inject(EditorContextKey);
const page = usePage(); // Initialize usePage
const { formatCurrency, settings, adminSettings } = useCurrency();

const state = reactive({
    style: (props.modelValue?.style || props.data?.style || 'cards') === 'comparison_table' ? 'table' : (props.modelValue?.style || props.data?.style || 'cards'),
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

// Determine if we are waiting for settings in admin
const adminSettingsLoaded = computed(() => {
    // If we have settings from Inertia (page.props.settings), we are good
    // Otherwise check if adminSettings.loaded is true
    const pageSettings = page?.props?.settings as any;
    return pageSettings?.currency || adminSettings.loaded;
});

// Computed Packages from Service Config
const packages = computed(() => {
    const form = context?.form.value;
    if (!form) return [];
    
    // Priority: Live Config > Saved Services
    const liveConfig = form.service_config;
    if (liveConfig && Array.isArray(liveConfig) && liveConfig.length > 0) {
        return liveConfig;
    }
    
    return form.services || [];
});

// Helper for unique capability keys (for table view)
const allCapabilityKeys = computed(() => {
    const keys = new Set<string>();
    packages.value.forEach((pkg: any) => {
        if (pkg.capabilities) {
            Object.keys(pkg.capabilities).forEach(k => keys.add(k));
        }
    });
    return Array.from(keys);
});

// Helper checks
const hasPackages = computed(() => packages.value && packages.value.length > 0);

const formatPrice = (price: any) => {
    if (!price || parseFloat(price) === 0) return 'Free';
    return formatCurrency(price);
};

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', {
            ...props.modelValue,
            ...newValue,
        });
    }
}, { deep: true });

watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        if (newVal.style !== undefined) state.style = newVal.style;
        if (newVal.margin !== undefined) state.margin = newVal.margin;
        if (newVal.padding !== undefined) state.padding = newVal.padding;
    }
}, { deep: true });

watch(() => props.data, (newData) => {
    if (newData) {
        state.style = newData.style || 'cards';
        state.margin = newData.margin || '';
        state.padding = newData.padding || '';
    }
}, { deep: true });
</script>

<style>
/* CSS Variables & Shared Styles */
.pricing-matrix-wrapper {
    --primary: #4361ee;
    --gradient: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);
    --dark: #1e293b;
    --gray: #64748b;
    --block-radius: 0.75rem;
    --block-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --block-border: #e5e7eb;
}

.dark .pricing-matrix-wrapper {
    --block-border: #374151;
}

/* Pricing Matrix Styles from landing-blocks.css */
.pricing {
    padding: 0 !important; /* Adjusted for editor */
    background: transparent !important;
}

.pricing-cards {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    grid-auto-rows: 1fr !important;
    justify-content: center !important;
    gap: 25px !important;
    margin-top: 20px !important; /* Reduced for editor */
    max-width: 1200px !important;
    margin-left: auto !important;
    margin-right: auto !important;
}

@media (max-width: 1024px) {
    .pricing-cards {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}

@media (max-width: 768px) {
    .pricing-cards {
        grid-template-columns: 1fr !important;
    }
}

.pricing-card {
    background: white !important;
    border-radius: 12px !important;
    padding: 35px 25px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
    position: relative !important;
    border: 2px solid #eee !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
    text-align: center !important;
}

.dark .pricing-card {
    background: #1f2937 !important;
    border-color: #374151 !important;
}

.pricing-card.featured {
    border-color: var(--primary) !important;
    transform: scale(1.03) !important;
}

.pricing-card.featured::before {
    content: 'MOST POPULAR' !important;
    position: absolute !important;
    top: -12px !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    background: var(--gradient) !important;
    color: white !important;
    padding: 5px 18px !important;
    border-radius: 20px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
    white-space: nowrap !important;
}

.pricing-card:hover {
    transform: translateY(-8px) !important;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
}

.pricing-card.featured:hover {
    transform: scale(1.03) translateY(-8px) !important;
}

.pricing-header {
    text-align: center !important;
    margin-bottom: 28px !important;
}

.pricing-header h3 {
    font-size: 22px !important;
    margin-bottom: 10px !important;
    font-family: 'Poppins', sans-serif !important;
    font-weight: 700 !important;
    color: var(--dark) !important;
}

.pricing-header .price {
    font-size: 44px !important;
    font-weight: 800 !important;
    background: var(--gradient, linear-gradient(135deg, #4361ee 0%, #7209b7 100%)) !important;
    -webkit-background-clip: text !important;
    background-clip: text !important;
    color: transparent !important;
    margin: 8px 0 !important;
    line-height: normal !important;
}

.pricing-header p {
    font-size: 16px !important;
    color: var(--gray) !important;
}

.pricing-features {
    list-style: none !important;
    margin-bottom: 28px !important;
    padding: 0 !important;
    flex-grow: 1 !important;
    text-align: left !important;
}

.pricing-features li {
    padding: 10px 0 !important;
    border-bottom: 1px solid #f1f1f1 !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    color: var(--dark) !important;
    font-size: 15px !important;
}

.dark .pricing-features li {
    border-bottom-color: #374151 !important;
}

.pricing-features li:last-child {
    border-bottom: none !important;
}

.pricing-features li i {
    color: #4cc9f0 !important;
    font-size: 16px !important;
}

.pricing-action {
    margin-top: auto !important;
}

.pricing .primary-btn {
    background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%) !important;
    color: #ffffff !important;
    display: inline-block !important;
    width: 100% !important;
    padding: 14px 25px !important;
    margin-top: 15px !important;
    border-radius: 30px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    border: none !important;
    text-align: center !important;
    box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3) !important;
    font-size: 15px !important;
}

.pricing .primary-btn:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4) !important;
    color: #ffffff !important;
    text-decoration: none !important;
}
</style>
