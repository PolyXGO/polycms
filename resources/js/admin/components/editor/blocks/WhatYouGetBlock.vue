<template>
    <!-- Settings Mode (for sidebar) -->
    <div v-if="mode === 'settings'" class="what-you-get-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
            <input 
                v-model="state.heading" 
                type="text" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                placeholder="Section Heading"
            >
        </div>
        
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Subheading</label>
            <textarea 
                v-model="state.subheading" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-20 focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter subheading..."
            ></textarea>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Button Text</label>
                <input 
                    v-model="state.button_text" 
                    type="text" 
                    class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="Tour Our Tool"
                >
            </div>
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Button Link</label>
                <input 
                    v-model="state.button_link" 
                    type="text" 
                    class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="/posts"
                >
            </div>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Features</label>
            <div v-for="(feature, index) in state.features" :key="index" class="flex items-center gap-2 mb-2">
                <input 
                    v-model="state.features[index]" 
                    type="text" 
                    class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="Enter feature..."
                >
                <button @click="removeFeature(index)" class="text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
            <button @click="addFeature" class="w-full py-2 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg text-xs text-gray-500 hover:border-indigo-500 hover:text-indigo-500 transition-colors">
                + Add Feature
            </button>
        </div>

        <div class="form-group p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" v-model="state.show_highlight" class="rounded text-indigo-600 focus:ring-indigo-500">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Show Subscription Highlight</span>
            </label>
            <div v-if="state.show_highlight" class="mt-3 space-y-3">
                <input 
                    v-model="state.highlight_title" 
                    type="text" 
                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    placeholder="Highlight Title"
                >
                <textarea 
                    v-model="state.highlight_text" 
                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-16 focus:ring-2 focus:ring-indigo-500"
                    placeholder="Highlight Text"
                ></textarea>
            </div>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Ownership Banner Title</label>
            <input 
                v-model="state.banner_title" 
                type="text" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                placeholder="After delivery → The entire software is 100% YOURS"
            >
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Ownership Banner Text</label>
            <textarea 
                v-model="state.banner_text" 
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-16 focus:ring-2 focus:ring-indigo-500"
                placeholder="No recurring fees. No royalties..."
            ></textarea>
        </div>
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="what-you-get-block-preview" :style="{ padding: state.padding }">
        <div class="what-you-get-block-preview__title">
            <h2>{{ state.heading || 'Here\'s Exactly What You Get' }}</h2>
            <p>{{ state.subheading || 'A complete invoice SaaS solution that saves you 6+ months of development time' }}</p>
        </div>

        <div v-if="state.button_text" class="what-you-get-block-preview__actions">
            <button type="button" class="what-you-get-block-preview__button">
                {{ state.button_text }}
            </button>
        </div>

        <div class="what-you-get-block-preview__features">
            <div
                v-for="(feature, index) in state.features"
                :key="index"
                class="what-you-get-block-preview__item"
            >
                <span class="what-you-get-block-preview__item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <div class="what-you-get-block-preview__item-copy">
                    <strong>{{ feature }}</strong>
                </div>
            </div>

            <div v-if="state.show_highlight" class="what-you-get-block-preview__highlight">
                <h4>{{ state.highlight_title }}</h4>
                <p>{{ state.highlight_text }}</p>
            </div>
        </div>

        <div v-if="state.banner_title" class="what-you-get-block-preview__banner">
            <h3>{{ state.banner_title }}</h3>
            <p>{{ state.banner_text }}</p>
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
    button_text: props.modelValue?.button_text || props.data?.button_text || '',
    button_link: props.modelValue?.button_link || props.data?.button_link || '',
    features: props.modelValue?.features || props.data?.features || ['Feature 1', 'Feature 2'],
    show_highlight: props.modelValue?.show_highlight !== undefined ? props.modelValue.show_highlight : true,
    highlight_title: props.modelValue?.highlight_title || props.data?.highlight_title || '💰 Automated Subscription Payments',
    highlight_text: props.modelValue?.highlight_text || props.data?.highlight_text || 'We set up automated recurring billing for your SaaS.',
    banner_title: props.modelValue?.banner_title || props.data?.banner_title || 'After delivery → The entire software is 100% YOURS',
    banner_text: props.modelValue?.banner_text || props.data?.banner_text || 'No recurring fees. No royalties.',
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', { ...props.modelValue, ...newValue });
    }
}, { deep: true });

const addFeature = () => {
    state.features.push('New Feature');
};

const removeFeature = (index: number) => {
    state.features.splice(index, 1);
};
</script>

<style scoped>
.what-you-get-block-preview {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    padding: 2.25rem 2rem;
}

.dark .what-you-get-block-preview {
    background: #111827;
    border-color: rgba(71, 85, 105, 0.85);
}

.what-you-get-block-preview__title {
    text-align: center;
    margin-bottom: 2rem;
}

.what-you-get-block-preview__title h2 {
    margin: 0 0 0.75rem;
    font-size: clamp(2rem, 3.6vw, 2.9rem);
    font-weight: 800;
    line-height: 1.1;
    color: #111827;
}

.what-you-get-block-preview__title p {
    margin: 0 auto;
    max-width: 52rem;
    font-size: 1rem;
    line-height: 1.65;
    color: #6b7280;
}

.what-you-get-block-preview__actions {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.what-you-get-block-preview__button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 2.9rem;
    padding: 0 1.4rem;
    border: 1px solid rgba(79, 70, 229, 0.4);
    border-radius: 0.95rem;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #ffffff;
    font-size: 0.92rem;
    font-weight: 700;
    box-shadow: 0 12px 30px rgba(79, 70, 229, 0.18);
}

.what-you-get-block-preview__features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(19rem, 1fr));
    gap: 0.9rem 1rem;
    margin-bottom: 2rem;
}

.what-you-get-block-preview__item {
    display: flex;
    align-items: flex-start;
    padding: 0.85rem 0.95rem;
    border: 1px solid #f0f2ff;
    border-radius: 0.75rem;
    background: #fafbff;
    transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}

.what-you-get-block-preview__item:hover {
    transform: translateY(-2px);
    background: rgba(67, 97, 238, 0.05);
}

.what-you-get-block-preview__item-icon {
    display: inline-flex;
    width: 1.15rem;
    height: 1.15rem;
    margin-right: 0.8rem;
    margin-top: 0.05rem;
    flex: 0 0 auto;
    color: #22c55e;
}

.what-you-get-block-preview__item-icon svg {
    width: 100%;
    height: 100%;
}

.what-you-get-block-preview__item-copy {
    min-width: 0;
}

.what-you-get-block-preview__item-copy strong {
    display: block;
    font-size: 0.98rem;
    font-weight: 600;
    line-height: 1.45;
    color: #111827;
}

.what-you-get-block-preview__highlight {
    grid-column: 1 / -1;
    margin-top: 0.4rem;
    padding: 1.2rem 1.25rem;
    border-left: 4px solid #22c55e;
    border-radius: 0.9rem;
    background: linear-gradient(135deg, #f8f9ff 0%, #e8f4ff 100%);
}

.what-you-get-block-preview__highlight h4 {
    margin: 0 0 0.45rem;
    font-size: 1.08rem;
    font-weight: 700;
    color: #111827;
}

.what-you-get-block-preview__highlight p {
    margin: 0;
    font-size: 0.94rem;
    line-height: 1.6;
    color: #6b7280;
}

.what-you-get-block-preview__banner {
    margin-top: 2rem;
    padding: 1.6rem 1.5rem;
    border-radius: 1rem;
    background: linear-gradient(135deg, #4361ee 0%, #5f60ff 100%);
    color: #ffffff;
    text-align: center;
}

.what-you-get-block-preview__banner h3 {
    margin: 0 0 0.55rem;
    font-size: 1.4rem;
    font-weight: 800;
}

.what-you-get-block-preview__banner p {
    margin: 0;
    font-size: 0.98rem;
    line-height: 1.6;
    opacity: 0.92;
}

.dark .what-you-get-block-preview__title h2 {
    color: #f9fafb;
}

.dark .what-you-get-block-preview__title p {
    color: #9ca3af;
}

.dark .what-you-get-block-preview__item {
    background: #1f2937;
    border-color: #374151;
}

.dark .what-you-get-block-preview__item:hover {
    background: rgba(129, 140, 248, 0.05);
}

.dark .what-you-get-block-preview__item-copy strong {
    color: #f9fafb;
}

.dark .what-you-get-block-preview__highlight {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
}

.dark .what-you-get-block-preview__highlight h4 {
    color: #f9fafb;
}

.dark .what-you-get-block-preview__highlight p {
    color: #9ca3af;
}

@media (max-width: 768px) {
    .what-you-get-block-preview {
        padding: 1.5rem 1.25rem;
    }

    .what-you-get-block-preview__title h2 {
        font-size: 1.8rem;
    }

    .what-you-get-block-preview__features {
        grid-template-columns: 1fr;
    }
}
</style>
