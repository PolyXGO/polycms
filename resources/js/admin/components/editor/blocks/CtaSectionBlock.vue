<template>
    <!-- Settings Mode (for sidebar) -->
    <div v-if="mode === 'settings'" class="cta-section-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
            <input v-model="state.heading" type="text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Ready to Launch?">
        </div>
        
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Main Text</label>
            <textarea v-model="state.text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-20 focus:ring-2 focus:ring-indigo-500" placeholder="Main description text..."></textarea>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Small Info Text</label>
            <input v-model="state.info_text" type="text" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Fill the form below...">
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Form HTML / Shortcode</label>
            <textarea v-model="state.form_html" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs h-20 font-mono" placeholder="<iframe>...</iframe> or [form-id=1]"></textarea>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Stats</label>
            <div v-for="(stat, index) in state.stats" :key="index" class="flex gap-2 mb-2 group">
                <input v-model="stat.number" type="text" class="w-20 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded p-1 text-xs font-bold" placeholder="10+">
                <input v-model="stat.label" type="text" class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded p-1 text-xs" placeholder="Successful Launches">
                <button @click="removeStat(index)" class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <button @click="addStat" class="w-full py-1 border border-dashed border-gray-300 dark:border-gray-600 rounded text-[10px] text-gray-500 hover:text-indigo-500 hover:border-indigo-500">+ Add Stat</button>
        </div>
    </div>

    <!-- Preview Mode (for main editor area) -->
    <div v-else class="cta-section-block-preview" :style="{ padding: state.padding }">
        <div class="cta-section-block-preview__inner">
            <h2 class="cta-section-block-preview__heading">
                {{ state.heading || DEFAULT_HEADING }}
            </h2>
            <p v-if="state.text" class="cta-section-block-preview__text">
                {{ state.text }}
            </p>
            <p v-if="state.info_text" class="cta-section-block-preview__info">
                {{ state.info_text }}
            </p>

            <div class="cta-section-block-preview__form-shell">
                <div
                    v-if="state.form_html?.trim()"
                    class="cta-section-block-preview__form-placeholder"
                >
                    <span class="cta-section-block-preview__form-badge">Embedded Form</span>
                    <span class="cta-section-block-preview__form-snippet">{{ summarizeForm(state.form_html) }}</span>
                </div>
                <div
                    v-else
                    class="cta-section-block-preview__form-placeholder cta-section-block-preview__form-placeholder--empty"
                >
                    Form Placeholder
                </div>
            </div>

            <div v-if="state.stats.length" class="cta-section-block-preview__stats">
                <div v-for="(stat, index) in state.stats" :key="index" class="cta-section-block-preview__stat">
                    <div class="cta-section-block-preview__stat-number">{{ stat.number }}</div>
                    <div class="cta-section-block-preview__stat-label">{{ stat.label }}</div>
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

const DEFAULT_HEADING = 'Ready to Launch Your SaaS Business?';
const DEFAULT_TEXT = 'Stop building from scratch. Get a proven, ready-to-launch invoice SaaS with your branding in days, not months.';
const DEFAULT_INFO_TEXT = 'Fill the form below and we will personally contact you with pricing, demo access, and next steps.';
const DEFAULT_STATS = [
    { number: '10+', label: 'Successful Launches' },
    { number: '100%', label: 'White-Label Ready' },
    { number: '7-10', label: 'Days Delivery' },
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
    heading: readAttr('heading', DEFAULT_HEADING),
    text: readAttr('text', DEFAULT_TEXT),
    info_text: readAttr('info_text', DEFAULT_INFO_TEXT),
    form_html: readAttr('form_html', ''),
    stats: readAttr('stats', DEFAULT_STATS),
    margin: readAttr('margin', ''),
    padding: readAttr('padding', ''),
});

const isSyncingFromProps = ref(false);

function buildPayload() {
    return {
        ...(props.modelValue || {}),
        heading: state.heading,
        text: state.text,
        info_text: state.info_text,
        form_html: state.form_html,
        stats: cloneValue(state.stats),
        margin: state.margin,
        padding: state.padding,
    };
}

function syncState(source?: Record<string, any> | null) {
    isSyncingFromProps.value = true;
    state.heading = readSourceAttr(source, 'heading', DEFAULT_HEADING);
    state.text = readSourceAttr(source, 'text', DEFAULT_TEXT);
    state.info_text = readSourceAttr(source, 'info_text', DEFAULT_INFO_TEXT);
    state.form_html = readSourceAttr(source, 'form_html', '');
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
    state.stats.push({ number: '0', label: 'New Stat' });
};

const removeStat = (index: number) => {
    state.stats.splice(index, 1);
};

const summarizeForm = (html: string) => {
    return html.replace(/\s+/g, ' ').trim().slice(0, 80);
};
</script>

<style scoped>
.cta-section-block-preview {
    padding: 0;
}

.cta-section-block-preview__inner {
    padding: 4.5rem 1.75rem;
    border-radius: 1rem;
    background: linear-gradient(135deg, #4361ee 0%, #5f60ff 100%);
    color: #ffffff;
    text-align: center;
}


.cta-section-block-preview__heading {
    margin: 0 0 1rem;
    font-size: clamp(2rem, 4vw, 2.7rem);
    font-weight: 800;
    line-height: 1.1;
}

.cta-section-block-preview__text {
    max-width: 44rem;
    margin: 0 auto 1.1rem;
    font-size: 1rem;
    line-height: 1.7;
    opacity: 0.92;
}

.cta-section-block-preview__info {
    max-width: 40rem;
    margin: 0 auto 1.4rem;
    font-size: 0.95rem;
    line-height: 1.65;
    opacity: 0.9;
}

.cta-section-block-preview__form-shell {
    max-width: 37.5rem;
    margin: 0 auto;
    padding: 1.5rem;
    border-radius: 0.9rem;
    background: #ffffff;
    color: #111827;
    box-shadow: 0 24px 50px rgba(15, 23, 42, 0.18);
}

.cta-section-block-preview__form-placeholder {
    min-height: 8rem;
    padding: 1.2rem;
    border: 2px dashed #dbe4ff;
    border-radius: 0.75rem;
    background: #f8faff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
}

.cta-section-block-preview__form-placeholder--empty {
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 600;
}

.cta-section-block-preview__form-badge {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #4f46e5;
}

.cta-section-block-preview__form-snippet {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.8rem;
    color: #475569;
}

.cta-section-block-preview__stats {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.18);
}

.cta-section-block-preview__stat {
    min-width: 7rem;
    text-align: center;
}

.cta-section-block-preview__stat-number {
    font-size: 1.55rem;
    font-weight: 800;
    line-height: 1.1;
}

.cta-section-block-preview__stat-label {
    margin-top: 0.35rem;
    font-size: 0.72rem;
    line-height: 1.4;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    opacity: 0.82;
}

@media (max-width: 768px) {
    .cta-section-block-preview__inner {
        padding: 3rem 1.25rem;
    }

    .cta-section-block-preview__heading {
        font-size: 1.85rem;
    }

    .cta-section-block-preview__form-shell {
        padding: 1rem;
    }
}
</style>
