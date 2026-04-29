<template>
    <!-- Settings Mode (for sidebar) -->
    <div v-if="mode === 'settings'" class="features-grid-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
            <input 
                v-model="state.heading" 
                type="text" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                placeholder="Why Choose Us"
            >
        </div>
        
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Subheading</label>
            <textarea 
                v-model="state.subheading" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-16 focus:ring-2 focus:ring-indigo-500"
                placeholder="Section Subheading"
            ></textarea>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Columns</label>
            <select 
                v-model="state.columns" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
            >
                <option :value="1">1 Column</option>
                <option :value="2">2 Columns</option>
                <option :value="3">3 Columns</option>
                <option :value="4">4 Columns</option>
            </select>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Features</label>
            <div v-for="(feature, index) in state.features" :key="index" class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 mb-3 relative group">
                <button @click="removeFeature(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <div class="w-10">
                            <label class="block text-[8px] font-bold text-gray-400 mb-1">Icon</label>
                            <input 
                                v-model="feature.icon" 
                                type="text" 
                                class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded p-1 text-[10px]"
                                placeholder="fas fa-rocket"
                            >
                        </div>
                        <div class="flex-1">
                            <label class="block text-[8px] font-bold text-gray-400 mb-1">Title</label>
                            <input 
                                v-model="feature.title" 
                                type="text" 
                                class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded p-1 text-xs"
                                placeholder="Feature Title"
                            >
                        </div>
                    </div>
                    <div>
                        <label class="block text-[8px] font-bold text-gray-400 mb-1">Description</label>
                        <textarea 
                            v-model="feature.description" 
                            class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded p-1 text-xs h-12"
                            placeholder="Short description..."
                        ></textarea>
                    </div>
                </div>
            </div>
            <button @click="addFeature" class="w-full py-2 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg text-xs text-gray-500 hover:border-indigo-500 hover:text-indigo-500 transition-colors">
                + Add Feature Card
            </button>
        </div>
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="features-grid-block-preview" :style="{ padding: state.padding }">
        <div class="text-center mb-6">
            <h2 class="text-lg font-bold">{{ state.heading || 'Features Section' }}</h2>
            <p class="text-xs text-gray-500 mt-1">{{ state.subheading }}</p>
        </div>
        
        <div class="grid gap-4" :style="{ gridTemplateColumns: `repeat(${state.columns}, 1fr)` }">
            <div v-for="(feature, index) in state.features" :key="index" class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i :class="feature.icon || 'fas fa-star'" class="text-sm"></i>
                </div>
                <h3 class="text-sm font-bold mb-1">{{ feature.title || 'Feature Title' }}</h3>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight">{{ feature.description }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
    heading: props.modelValue?.heading || props.data?.heading || '',
    subheading: props.modelValue?.subheading || props.data?.subheading || '',
    columns: props.modelValue?.columns || props.data?.columns || 3,
    features: props.modelValue?.features || props.data?.features || [
        { icon: 'fas fa-rocket', title: 'Fast Launch', description: 'Go from idea to revenue in 10-14 days.' },
        { icon: 'fas fa-money-bill-wave', title: 'Automated Revenue', description: 'Subscription billing is already integrated.' }
    ],
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', { ...props.modelValue, ...newValue });
    }
}, { deep: true });

const addFeature = () => {
    state.features.push({ icon: 'fas fa-check', title: 'New Feature', description: 'Feature description goes here.' });
};

const removeFeature = (index: number) => {
    state.features.splice(index, 1);
};
</script>

<style scoped>
.features-grid-block-preview {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

.dark .features-grid-block-preview {
    background: #1f2937;
    border-color: #374151;
}
</style>
