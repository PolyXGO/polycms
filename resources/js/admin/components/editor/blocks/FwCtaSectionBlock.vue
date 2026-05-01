<template>
    <!-- Settings Mode -->
    <div v-if="mode === 'settings'" class="fw-cta-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
            <input v-model="state.heading" type="text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Ready to get started?">
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Text</label>
            <textarea v-model="state.text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-20 focus:ring-2 focus:ring-indigo-500" placeholder="Description text..."></textarea>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Button Text</label>
            <input v-model="state.button_text" type="text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Get Started Free">
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Button URL</label>
            <input v-model="state.button_url" type="text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="/login">
        </div>
    </div>

    <!-- Preview Mode -->
    <div v-else class="fw-cta-preview">
        <div class="fw-cta-preview__inner">
            <h2 class="fw-cta-preview__heading">{{ state.heading || 'Ready to get started?' }}</h2>
            <p v-if="state.text" class="fw-cta-preview__text">{{ state.text }}</p>
            <span v-if="state.button_text" class="fw-cta-preview__btn">{{ state.button_text }}</span>
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

function cloneValue<T>(value: T): T {
    if (value === undefined || value === null) return value;
    return JSON.parse(JSON.stringify(value));
}

function hasAttr(source: Record<string, any> | null | undefined, key: string) {
    return Boolean(source) && Object.prototype.hasOwnProperty.call(source, key);
}

function readAttr<T>(key: string, fallback: T): T {
    if (hasAttr(props.modelValue, key)) return cloneValue(props.modelValue?.[key]) as T;
    if (hasAttr(props.data, key)) return cloneValue(props.data?.[key]) as T;
    return cloneValue(fallback) as T;
}

function readSourceAttr<T>(source: Record<string, any> | null | undefined, key: string, fallback: T): T {
    if (hasAttr(source, key)) return cloneValue(source?.[key]) as T;
    return cloneValue(fallback) as T;
}

const state = reactive({
    heading: readAttr('heading', 'Ready to get started?'),
    text: readAttr('text', 'Set up your site in minutes. No credit card required.'),
    button_text: readAttr('button_text', 'Get Started Free'),
    button_url: readAttr('button_url', '/login'),
    margin: readAttr('margin', ''),
    padding: readAttr('padding', ''),
});

const isSyncingFromProps = ref(false);

function buildPayload() {
    return {
        ...(props.modelValue || {}),
        heading: state.heading,
        text: state.text,
        button_text: state.button_text,
        button_url: state.button_url,
        margin: state.margin,
        padding: state.padding,
    };
}

function syncState(source?: Record<string, any> | null) {
    isSyncingFromProps.value = true;
    state.heading = readSourceAttr(source, 'heading', 'Ready to get started?');
    state.text = readSourceAttr(source, 'text', 'Set up your site in minutes. No credit card required.');
    state.button_text = readSourceAttr(source, 'button_text', 'Get Started Free');
    state.button_url = readSourceAttr(source, 'button_url', '/login');
    state.margin = readSourceAttr(source, 'margin', '');
    state.padding = readSourceAttr(source, 'padding', '');
    nextTick(() => { isSyncingFromProps.value = false; });
}

function emitPayload() {
    emit('update:modelValue', buildPayload());
}

watch(state, () => {
    if (isSyncingFromProps.value) return;
    if (props.mode === 'settings') emitPayload();
}, { deep: true });

watch(() => props.data, (newData) => {
    if (props.mode === 'preview') syncState(newData);
}, { deep: true, immediate: true });

watch(() => props.modelValue, (newValue) => {
    if (props.mode === 'settings') syncState(newValue);
}, { deep: true, immediate: true });
</script>

<style scoped>
.fw-cta-preview {
    padding: 0;
}

.fw-cta-preview__inner {
    padding: 3.5rem 1.75rem;
    border-radius: 0.75rem;
    background: #f3f4f6;
    text-align: center;
    border: 1px solid #e5e7eb;
}

.fw-cta-preview__heading {
    margin: 0 0 0.75rem;
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 800;
    line-height: 1.2;
    color: #111827;
}

.fw-cta-preview__text {
    max-width: 30rem;
    margin: 0 auto 1.5rem;
    font-size: 0.95rem;
    line-height: 1.6;
    color: #6b7280;
}

.fw-cta-preview__btn {
    display: inline-block;
    padding: 0.7rem 2rem;
    border-radius: 0.5rem;
    background: #111827;
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
}
</style>
