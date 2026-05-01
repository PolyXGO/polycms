<template>
    <!-- Settings Mode (for sidebar) -->
    <div v-if="mode === 'settings'" class="hero-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
            <input 
                v-model="state.heading" 
                type="text" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter hero heading..."
            >
        </div>
        
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Subheading</label>
            <textarea 
                v-model="state.subheading" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-20 focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter hero subheading..."
            ></textarea>
        </div>
        
        <div class="grid grid-cols-2 gap-3">
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Button Text</label>
                <input 
                    v-model="state.button_text" 
                    type="text" 
                    class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="Get Started"
                >
            </div>
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Button Link</label>
                <input 
                    v-model="state.button_link" 
                    type="text" 
                    class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="#"
                >
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-3">
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Secondary Button</label>
                <input 
                    v-model="state.secondary_button_text" 
                    type="text" 
                    class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="Browse Products"
                >
            </div>
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Secondary Link</label>
                <input 
                    v-model="state.secondary_button_link" 
                    type="text" 
                    class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="#"
                >
            </div>
        </div>
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="hero-block-preview" :style="{ padding: state.padding }">
        <div class="hero-preview-content">
            <h2 class="hero-preview-heading" v-html="state.heading || 'Hero Heading'"></h2>
            <p class="hero-preview-subheading">{{ state.subheading || 'Your subheading goes here...' }}</p>
            <div class="hero-preview-actions" v-if="state.button_text || state.secondary_button_text">
                <div v-if="state.button_text" class="hero-preview-button hero-btn-primary">{{ state.button_text }}</div>
                <div v-if="state.secondary_button_text" class="hero-preview-button hero-btn-secondary">{{ state.secondary_button_text }}</div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch, toRefs } from 'vue';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any; // For read-only preview
}>();

const emit = defineEmits(['update:modelValue']);

// Use local state to avoid direct mutation issues
const state = reactive({
    heading: props.modelValue?.heading || props.data?.heading || '',
    subheading: props.modelValue?.subheading || props.data?.subheading || '',
    button_text: props.modelValue?.button_text || props.data?.button_text || '',
    button_link: props.modelValue?.button_link || props.data?.button_link || '',
    secondary_button_text: props.modelValue?.secondary_button_text || props.data?.secondary_button_text || '',
    secondary_button_link: props.modelValue?.secondary_button_link || props.data?.secondary_button_link || '',
    scale: 1, // Keep for potential animations
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

// Watch for changes and emit back to parent (only in settings mode)
watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', {
            ...props.modelValue,
            ...newValue,
        });
    }
}, { deep: true });

// Watch for external changes to data prop (preview mode)
watch(() => props.data, (newData) => {
    if (newData) {
        state.heading = newData.heading || '';
        state.subheading = newData.subheading || '';
        state.button_text = newData.button_text || '';
        state.button_link = newData.button_link || '';
        state.secondary_button_text = newData.secondary_button_text || '';
        state.secondary_button_link = newData.secondary_button_link || '';
        state.margin = newData.margin || '';
        state.padding = newData.padding || '';
    }
}, { deep: true });
</script>

<style scoped>
.hero-block-preview {
    background: transparent;
    padding: 4rem 2rem;
    text-align: center;
    color: #111827;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-preview-content {
    max-width: 800px;
}

.hero-preview-heading {
    font-size: 3rem;
    font-weight: 800;
    margin: 0 0 1rem 0;
    line-height: 1.1;
    letter-spacing: -0.02em;
}

.hero-preview-subheading {
    font-size: 1.125rem;
    color: #4b5563;
    margin: 0 auto 2rem auto;
    line-height: 1.6;
    max-width: 600px;
}

.hero-preview-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.hero-preview-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    font-size: 1rem;
    font-weight: 500;
    line-height: 1.5;
}

.hero-btn-primary {
    background: #111827;
    color: #ffffff;
    border: 1px solid #111827;
}

.hero-btn-secondary {
    background: #ffffff;
    color: #4b5563;
    border: 1px solid #d1d5db;
}

/* Dark mode support in editor */
:global(.dark) .hero-block-preview {
    color: #f3f4f6;
}

:global(.dark) .hero-preview-subheading {
    color: #9ca3af;
}

:global(.dark) .hero-btn-primary {
    background: #f9fafb;
    color: #111827;
    border-color: #f9fafb;
}

:global(.dark) .hero-btn-secondary {
    background: #1f2937;
    color: #d1d5db;
    border-color: #4b5563;
}
</style>
