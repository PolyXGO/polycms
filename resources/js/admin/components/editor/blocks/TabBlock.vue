<template>
    <!-- Settings Mode (compact vertical layout for sidebar) -->
    <div v-if="mode === 'settings'" class="tabs-block-settings">
        <div class="form-group">
            <label>Style</label>
            <select v-model="state.style">
                <option value="underline">Underline</option>
                <option value="pills">Pills</option>
                <option value="blocks">Blocks</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Alignment</label>
            <select v-model="state.alignment">
                <option value="start">Left</option>
                <option value="center">Center</option>
                <option value="end">Right</option>
            </select>
        </div>

        <div class="tabs-items-list">
            <div v-for="(item, index) in state.items" :key="index" class="tabs-item-row">
                <div class="tabs-item-header">
                    <span class="tabs-item-num">#{{ Number(index) + 1 }}</span>
                    <input v-model="item.title" type="text" placeholder="Tab Title" class="tabs-item-title">
                    <div class="tabs-item-actions">
                        <button @click="moveItem(Number(index), -1)" v-if="Number(index) > 0" class="btn-icon" title="Move up">↑</button>
                        <button @click="moveItem(Number(index), 1)" v-if="Number(index) < state.items.length - 1" class="btn-icon" title="Move down">↓</button>
                        <button @click="removeItem(Number(index))" class="btn-icon btn-danger" title="Delete">×</button>
                    </div>
                </div>
                <textarea v-model="item.content" placeholder="Provide content here..."></textarea>
            </div>
            
            <button @click="addItem" class="btn-add">+ Add Tab</button>
        </div>
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="tabs-block-preview" :style="{ padding: state.padding }">
        <div class="tabs-preview-header" :class="`tabs-preview-header--${state.alignment}`">
            <span v-for="(item, i) in state.items" :key="i" class="tabs-preview-tab" :class="{ 'active': i === 0 }">
                {{ item.title || `Tab ${Number(i) + 1}` }}
            </span>
        </div>
        <div class="tabs-preview-body">
            {{ state.items[0]?.content || 'Tab content...' }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';

const props = defineProps<{
    modelValue: any;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
    items: props.modelValue?.items || props.data?.items || [
        { title: 'Tab 1', content: 'Tab 1 content here...' },
        { title: 'Tab 2', content: 'Tab 2 content here...' }
    ],
    style: props.modelValue?.style || props.data?.style || 'underline',
    alignment: props.modelValue?.alignment || props.data?.alignment || 'start',
    margin: props.modelValue?.margin || props.data?.margin || '',
    padding: props.modelValue?.padding || props.data?.padding || '',
});

const addItem = () => {
    state.items.push({ title: 'New Tab', content: 'Provide content here...' });
};

const removeItem = (index: number) => {
    state.items.splice(index, 1);
};

const moveItem = (index: number, direction: number) => {
    const newIndex = index + direction;
    if (newIndex < 0 || newIndex >= state.items.length) return;
    const temp = state.items[index];
    state.items[index] = state.items[newIndex];
    state.items[newIndex] = temp;
};

watch(() => ({ ...state }), (newValue) => {
    if (props.mode === 'settings') {
        emit('update:modelValue', { ...props.modelValue, ...newValue });
    }
}, { deep: true });

watch(() => props.data, (newData) => {
    if (newData) {
        state.items = newData.items || state.items;
        state.style = newData.style || state.style;
        state.alignment = newData.alignment || state.alignment;
        state.margin = newData.margin || '';
        state.padding = newData.padding || '';
    }
}, { deep: true });
</script>

<style scoped>
/* Settings Mode Styles */
.tabs-block-settings {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.tabs-block-settings .form-group {
    display: block;
}

.tabs-block-settings label {
    display: block;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    margin-bottom: 0.375rem;
}

.tabs-block-settings select {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
    background: #fff;
}

.dark .tabs-block-settings select {
    background: #111827;
    border-color: #374151;
    color: #f9fafb;
}

.tabs-items-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.tabs-item-row {
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
}

.dark .tabs-item-row {
    background: #111827;
    border-color: #374151;
}

.tabs-item-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.tabs-item-num {
    font-size: 10px;
    font-weight: 700;
    color: #6366f1;
}

.tabs-item-title {
    flex: 1;
    padding: 0.375rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    background: #fff;
}

.dark .tabs-item-title {
    background: #1f2937;
    border-color: #374151;
    color: #f9fafb;
}

.tabs-item-actions {
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

.tabs-item-row textarea {
    width: 100%;
    min-height: 60px;
    padding: 0.5rem;
    font-size: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    background: #fff;
    resize: vertical;
}

.dark .tabs-item-row textarea {
    background: #1f2937;
    border-color: #374151;
    color: #f9fafb;
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

/* Preview Mode Styles */
.tabs-block-preview {
    background: #f9fafb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.dark .tabs-block-preview {
    background: #1f2937;
}

.tabs-preview-header {
    display: flex;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.tabs-preview-header--start { justify-content: flex-start; }
.tabs-preview-header--center { justify-content: center; }
.tabs-preview-header--end { justify-content: flex-end; }

.dark .tabs-preview-header {
    border-color: #374151;
}

.tabs-preview-tab {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    background: transparent;
    border-radius: 0.25rem;
}

.tabs-preview-tab.active {
    color: #4f46e5;
    background: #eef2ff;
}

.dark .tabs-preview-tab.active {
    background: rgba(99, 102, 241, 0.2);
    color: #a5b4fc;
}

.tabs-preview-body {
    padding: 0.75rem;
    font-size: 0.75rem;
    color: #6b7280;
}
</style>
