<template>
    <!-- Settings Mode (for sidebar) -->
    <div v-if="mode === 'settings'" class="latest-posts-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
            <input v-model="state.heading" type="text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Latest Updates">
        </div>
        
        <div class="grid grid-cols-2 gap-3">
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Post Count</label>
                <input v-model.number="state.count" type="number" min="1" max="12" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="form-group">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Columns</label>
                <select v-model.number="state.columns" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option :value="2">2 Columns</option>
                    <option :value="3">3 Columns</option>
                    <option :value="4">4 Columns</option>
                </select>
            </div>
        </div>

        <div class="form-group flex items-center gap-2 mt-4">
            <input type="checkbox" id="show_view_all" v-model="state.show_view_all" class="rounded text-indigo-600 focus:ring-indigo-500 bg-gray-50 border-gray-300 dark:bg-gray-900 dark:border-gray-600">
            <label for="show_view_all" class="text-sm text-gray-700 dark:text-gray-300">Show "View All" Button</label>
        </div>
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="latest-posts-block-preview" :style="{ padding: state.padding, margin: state.margin }">
        <div class="latest-posts-header">
            <h2 class="latest-posts-heading">{{ state.heading || 'Latest Updates' }}</h2>
            <div v-if="state.show_view_all" class="latest-posts-view-all">
                View All &rarr;
            </div>
        </div>

        <div class="latest-posts-grid" :style="{ '--grid-cols': state.columns || 3 }">
            <div v-for="i in Math.min(state.count || 3, 6)" :key="i" class="latest-post-card">
                <div class="latest-post-image">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                </div>
                <div class="latest-post-content">
                    <div class="latest-post-meta">
                        <span class="latest-post-badge">Category</span>
                        <span class="latest-post-date">Just now</span>
                    </div>
                    <h3 class="latest-post-title">Sample Blog Post Title for Preview</h3>
                    <p class="latest-post-excerpt">This is a placeholder excerpt for the blog post preview. The actual content will be loaded dynamically on the frontend.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { nextTick, reactive, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: any;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const DEFAULT_HEADING = 'Latest Updates';
const DEFAULT_COUNT = 6;
const DEFAULT_COLUMNS = 3;
const DEFAULT_SHOW_VIEW_ALL = true;

function cloneValue<T>(value: T): T {
    if (value === undefined || value === null) {
        return value;
    }
    return JSON.parse(JSON.stringify(value));
}

function hasAttr(source: Record<string, any> | null | undefined, key: string) {
    return Boolean(source) && Object.prototype.hasOwnProperty.call(source, key);
}

function readAttr<T>(key: string, fallback: T): T {
    if (hasAttr(props.modelValue, key)) {
        return cloneValue(props.modelValue?.[key]) as T;
    }
    if (hasAttr(props.data, key)) {
        return cloneValue(props.data?.[key]) as T;
    }
    return cloneValue(fallback) as T;
}

function readSourceAttr<T>(source: Record<string, any> | null | undefined, key: string, fallback: T): T {
    if (hasAttr(source, key)) {
        return cloneValue(source?.[key]) as T;
    }
    return cloneValue(fallback) as T;
}

const state = reactive({
    heading: readAttr('heading', DEFAULT_HEADING),
    count: readAttr('count', DEFAULT_COUNT),
    columns: readAttr('columns', DEFAULT_COLUMNS),
    show_view_all: readAttr('show_view_all', DEFAULT_SHOW_VIEW_ALL),
    margin: readAttr('margin', ''),
    padding: readAttr('padding', ''),
});

const isSyncingFromProps = ref(false);

function buildPayload() {
    return {
        ...(props.modelValue || {}),
        heading: state.heading,
        count: state.count,
        columns: state.columns,
        show_view_all: state.show_view_all,
        margin: state.margin,
        padding: state.padding,
    };
}

function syncState(source?: Record<string, any> | null) {
    isSyncingFromProps.value = true;
    state.heading = readSourceAttr(source, 'heading', DEFAULT_HEADING);
    state.count = readSourceAttr(source, 'count', DEFAULT_COUNT);
    state.columns = readSourceAttr(source, 'columns', DEFAULT_COLUMNS);
    state.show_view_all = readSourceAttr(source, 'show_view_all', DEFAULT_SHOW_VIEW_ALL);
    state.margin = readSourceAttr(source, 'margin', '');
    state.padding = readSourceAttr(source, 'padding', '');

    nextTick(() => {
        isSyncingFromProps.value = false;
    });
}

function emitPayload() {
    emit('update:modelValue', buildPayload());
}

watch(state, () => {
    if (isSyncingFromProps.value) {
        return;
    }
    if (props.mode === 'settings') {
        emitPayload();
    }
}, { deep: true });

watch(() => props.data, (newData) => {
    if (props.mode === 'preview') {
        syncState(newData);
    }
}, { deep: true, immediate: true });

watch(() => props.modelValue, (newValue) => {
    if (props.mode === 'settings') {
        syncState(newValue);
    }
}, { deep: true, immediate: true });
</script>

<style scoped>
.latest-posts-block-preview {
    background: transparent;
    padding: 2rem 0;
}

.latest-posts-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.latest-posts-heading {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.latest-posts-view-all {
    font-size: 0.875rem;
    font-weight: 600;
    color: #4f46e5;
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
}

.latest-posts-grid {
    display: grid;
    grid-template-columns: repeat(var(--grid-cols, 3), minmax(0, 1fr));
    gap: 1.5rem;
}

.latest-post-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    display: flex;
    flex-direction: column;
}

.latest-post-image {
    aspect-ratio: 16 / 9;
    background-color: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}

.latest-post-content {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.latest-post-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.75rem;
}

.latest-post-badge {
    background-color: #f3f4f6;
    color: #4b5563;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.latest-post-date {
    color: #6b7280;
}

.latest-post-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
}

.latest-post-excerpt {
    font-size: 0.875rem;
    color: #4b5563;
    margin: 0;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Dark mode support in editor */
:global(.dark) .latest-posts-heading,
:global(.dark) .latest-post-title {
    color: #f3f4f6;
}

:global(.dark) .latest-post-card {
    background: #1f2937;
    border-color: #374151;
}

:global(.dark) .latest-post-image {
    background-color: #374151;
    color: #6b7280;
}

:global(.dark) .latest-post-excerpt {
    color: #9ca3af;
}

:global(.dark) .latest-post-badge {
    background-color: #374151;
    color: #d1d5db;
}

:global(.dark) .latest-posts-view-all {
    color: #818cf8;
    border-color: #374151;
}
</style>
