<template>
    <!-- Settings Mode (for sidebar) -->
    <div v-if="mode === 'settings'" class="stats-bar-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Stats</label>
            <div v-for="(stat, index) in state.stats" :key="index" class="flex gap-2 mb-2 group">
                <input v-model="stat.value" type="text" class="w-20 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded p-1 text-xs font-bold" placeholder="99.9%">
                <input v-model="stat.label" type="text" class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded p-1 text-xs" placeholder="Uptime">
                <button @click="removeStat(index)" class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <button @click="addStat" class="w-full py-1 border border-dashed border-gray-300 dark:border-gray-600 rounded text-[10px] text-gray-500 hover:text-indigo-500 hover:border-indigo-500">+ Add Stat</button>
        </div>
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="stats-bar-block-preview" :style="{ padding: state.padding, margin: state.margin }">
        <div class="stats-bar-preview-inner">
            <div v-for="(stat, index) in state.stats" :key="index" class="stats-bar-item">
                <div class="stats-bar-value">{{ stat.value || '0' }}</div>
                <div class="stats-bar-label">{{ stat.label || 'Stat' }}</div>
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

const DEFAULT_STATS = [
    { value: '99.9%', label: 'Uptime' },
    { value: '<200ms', label: 'Response Time' },
    { value: '50+', label: 'Hooks & Filters' },
    { value: '100%', label: 'Open Source' },
];

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
    stats: readAttr('stats', DEFAULT_STATS),
    margin: readAttr('margin', ''),
    padding: readAttr('padding', ''),
});

const isSyncingFromProps = ref(false);

function buildPayload() {
    return {
        ...(props.modelValue || {}),
        stats: cloneValue(state.stats),
        margin: state.margin,
        padding: state.padding,
    };
}

function syncState(source?: Record<string, any> | null) {
    isSyncingFromProps.value = true;
    state.stats = readSourceAttr(source, 'stats', DEFAULT_STATS);
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

const addStat = () => {
    state.stats.push({ value: '0', label: 'New Stat' });
};

const removeStat = (index: number) => {
    state.stats.splice(index, 1);
};
</script>

<style scoped>
.stats-bar-block-preview {
    background: transparent;
    padding: 3rem 0;
}

.stats-bar-preview-inner {
    display: flex;
    justify-content: space-around;
    align-items: center;
    flex-wrap: wrap;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.stats-bar-item {
    text-align: center;
    flex: 1;
    min-width: 150px;
}

.stats-bar-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stats-bar-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Dark mode support in editor */
:global(.dark) .stats-bar-value {
    color: #f3f4f6;
}

:global(.dark) .stats-bar-label {
    color: #9ca3af;
}
</style>
