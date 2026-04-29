<template>
    <!-- Settings Mode (compact vertical layout for sidebar) -->
    <div v-if="mode === 'settings'" class="accordion-block-settings">
        <div class="form-group">
            <label>Style</label>
            <select v-model="state.style">
                <option value="standard">Standard (List)</option>
                <option value="separate">Separate (Cards)</option>
                <option value="bordered">Bordered (Full)</option>
            </select>
        </div>

        <div class="accordion-items-list">
            <div v-for="(item, index) in state.items" :key="index" class="accordion-item-row">
                <div class="accordion-item-header">
                    <span class="accordion-item-num">#{{ Number(index) + 1 }}</span>
                    <input v-model="item.title" type="text" placeholder="Item Title" class="accordion-item-title">
                    <div class="accordion-item-actions">
                        <button @click="moveItem(Number(index), -1)" v-if="Number(index) > 0" class="btn-icon" title="Move up">↑</button>
                        <button @click="moveItem(Number(index), 1)" v-if="Number(index) < state.items.length - 1" class="btn-icon" title="Move down">↓</button>
                        <button @click="removeItem(Number(index))" class="btn-icon btn-danger" title="Delete">×</button>
                    </div>
                </div>
                <textarea v-model="item.description" placeholder="Provide description here..."></textarea>
                <div class="accordion-item-toggle-wrapper">
                    <span>Open by default</span>
                    <button 
                        type="button"
                        @click="toggleItemOpen(Number(index))"
                        :class="['toggle-switch', { 'is-active': state.items[index].open }]"
                    >
                        <span class="toggle-knob" />
                    </button>
                </div>
            </div>
            
            <button @click="addItem" class="btn-add">+ Add Item</button>
        </div>
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="faq-container" :class="[`style-${state.style}`]" :style="{ padding: state.padding }">
        <div v-for="(item, i) in state.items" :key="i" class="faq-item">
            <div class="faq-question" @click="toggleItemOpen(Number(i))">
                <span :class="{ 'font-bold': item.open }">{{ item.title || `Item ${Number(i) + 1}` }}</span>
                <svg :class="{ 'rotate-180': item.open }" class="w-4 h-4 transition-transform text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </div>
            <div class="faq-answer" :class="{ 'active': item.open }">
                <div class="prose dark:prose-invert max-w-none pb-4">
                    {{ item.description }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch, toRaw } from 'vue';

const props = defineProps<{
    modelValue: any;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

// Local state for the form/preview
const state = reactive({
    items: props.modelValue?.items || props.data?.items || [
        { title: 'New Question', description: 'Provide the answer here...', open: false }
    ],
    style: props.modelValue?.style || props.data?.style || 'standard',
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

const addItem = () => {
    state.items.push({ title: 'New Question', description: 'Provide the answer here...', open: false });
};

const removeItem = (index: number) => {
    state.items.splice(index, 1);
};

const moveItem = (index: number, direction: number) => {
    const newIndex = index + direction;
    if (newIndex < 0 || newIndex >= state.items.length) return;
    const items = [...state.items];
    const temp = items[index];
    items[index] = items[newIndex];
    items[newIndex] = temp;
    state.items = items;
};

const toggleItemOpen = (index: number) => {
    state.items[index].open = !state.items[index].open;
};

// Deep watch the local state and emit updates upwards
watch(state, (newValue) => {
    const plainData = JSON.parse(JSON.stringify(toRaw(newValue)));
    emit('update:modelValue', { ...props.modelValue, ...plainData });
}, { deep: true });

// Reactively update local state if props change (external update)
watch([() => props.modelValue, () => props.data], ([newVal, newData]) => {
    const incomingData = newVal || newData;
    if (incomingData) {
        // Compare to avoid infinite loop
        if (JSON.stringify(incomingData.items) !== JSON.stringify(state.items)) {
            state.items = JSON.parse(JSON.stringify(incomingData.items || []));
        }
        if (incomingData.style !== state.style) {
            state.style = incomingData.style || 'standard';
        }
        if (incomingData.margin !== state.margin) {
            state.margin = incomingData.margin || '';
        }
        if (incomingData.padding !== state.padding) {
            state.padding = incomingData.padding || '';
        }
    }
}, { deep: true });
</script>

<style scoped>
/* Settings Mode Styles */
.accordion-block-settings {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.accordion-block-settings .form-group {
    display: block;
}

.accordion-block-settings label {
    display: block;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    margin-bottom: 0.375rem;
}

.accordion-block-settings select {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
    background: #fff;
}

.dark .accordion-block-settings select {
    background: #111827;
    border-color: #374151;
    color: #f9fafb;
}

.accordion-items-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.accordion-item-row {
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
}

.dark .accordion-item-row {
    background: #111827;
    border-color: #374151;
}

.accordion-item-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.accordion-item-num {
    font-size: 10px;
    font-weight: 700;
    color: #6366f1;
}

.accordion-item-title {
    flex: 1;
    padding: 0.375rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    background: #fff;
}

.dark .accordion-item-title {
    background: #1f2937;
    border-color: #374151;
    color: #f9fafb;
}

.accordion-item-actions {
    display: flex;
    gap: 0.25rem;
}

.btn-icon {
    width: 1.5rem;
    height: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    cursor: pointer;
}

.btn-icon:hover {
    background: #f3f4f6;
}

.btn-icon.btn-danger:hover {
    background: #fef2f2;
    color: #ef4444;
}

.dark .btn-icon {
    background: #1f2937;
    border-color: #374151;
    color: #9ca3af;
}

.accordion-item-row textarea {
    width: 100%;
    min-height: 60px;
    padding: 0.5rem;
    font-size: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    background: #fff;
    resize: vertical;
    margin-bottom: 0.5rem;
}

.dark .accordion-item-row textarea {
    background: #1f2937;
    border-color: #374151;
    color: #f9fafb;
}

.accordion-item-toggle-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f3f4f6;
}

.dark .accordion-item-toggle-wrapper {
    border-top-color: #374151;
}

.accordion-item-toggle-wrapper span {
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 600;
    color: #6b7280;
}

.toggle-switch {
    position: relative;
    display: inline-flex;
    height: 1.25rem;
    width: 2.25rem;
    flex-shrink: 0;
    cursor: pointer;
    border-radius: 9999px;
    border: 2px solid transparent;
    transition: background-color 0.2s ease-in-out;
    background-color: #e5e7eb;
    padding: 0;
}

.dark .toggle-switch {
    background-color: #374151;
}

.toggle-switch.is-active {
    background-color: #6366f1;
}

.toggle-knob {
    pointer-events: none;
    display: inline-block;
    height: 1rem;
    width: 1rem;
    transform: translateX(0);
    border-radius: 9999px;
    background-color: #fff;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease-in-out;
}

.toggle-switch.is-active .toggle-knob {
    transform: translateX(1rem);
}

.btn-add {
    padding: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6366f1;
    background: transparent;
    border: 1px dashed #c7d2fe;
    border-radius: 0.375rem;
    cursor: pointer;
}

.btn-add:hover {
    background: #eef2ff;
    border-color: #6366f1;
}

.btn-add:hover {
    background: #eef2ff;
    border-color: #6366f1;
}
</style>
