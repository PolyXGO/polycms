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
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="hero-block-preview" :style="{ padding: state.padding }">
        <div class="hero-preview-content">
            <h2 class="hero-preview-heading">{{ state.heading || 'Hero Heading' }}</h2>
            <p class="hero-preview-subheading">{{ state.subheading || 'Your subheading goes here...' }}</p>
            <div v-if="state.button_text" class="hero-preview-button">{{ state.button_text }}</div>
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
        state.margin = newData.margin || '';
        state.padding = newData.padding || '';
    }
}, { deep: true });
</script>

<style scoped>
.hero-block-preview {
    background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #a78bfa 100%);
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    color: white;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-preview-content {
    max-width: 500px;
}

.hero-preview-heading {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.hero-preview-subheading {
    font-size: 0.875rem;
    opacity: 0.85;
    margin: 0 0 1rem 0;
    line-height: 1.4;
}

.hero-preview-button {
    display: inline-block;
    background: white;
    color: #4338ca;
    padding: 0.5rem 1.25rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}
</style>
