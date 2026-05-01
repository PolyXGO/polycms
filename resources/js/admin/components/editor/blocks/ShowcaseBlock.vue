<template>
    <div v-if="mode === 'settings'" class="showcase-block-settings space-y-4">
        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
            <input
                v-model="state.heading"
                type="text"
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                placeholder="See It In Action"
            >
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Subheading</label>
            <textarea
                v-model="state.subheading"
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm h-16 focus:ring-2 focus:ring-indigo-500"
                placeholder="Section Description"
            ></textarea>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Showcase Title</label>
            <input
                v-model="state.demo_title"
                type="text"
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500"
                placeholder="From Zero to SaaS in 10 Days"
            >
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Steps</label>
            <div
                v-for="(step, index) in state.steps"
                :key="index"
                class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 mb-2 relative group"
            >
                <button
                    type="button"
                    @click="removeStep(Number(index))"
                    class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="space-y-2">
                    <input
                        v-model="step.title"
                        type="text"
                        class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded p-1 text-xs font-bold"
                        placeholder="Step Title"
                    >
                    <textarea
                        v-model="step.text"
                        class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded p-1 text-[10px] h-10"
                        placeholder="Step description..."
                    ></textarea>
                </div>
            </div>
            <button
                type="button"
                @click="addStep"
                class="w-full py-1 border border-dashed border-gray-300 dark:border-gray-600 rounded text-[10px] text-gray-500 hover:text-indigo-500 hover:border-indigo-500"
            >
                + Add Step
            </button>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Preview Image</label>
            <div class="flex gap-2">
                <input
                    v-model="state.preview_image"
                    type="text"
                    class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs"
                    placeholder="Pick from Media or paste an image URL..."
                >
                <button
                    type="button"
                    class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs hover:bg-gray-200 dark:hover:bg-gray-600"
                    @click="openMediaPicker"
                >
                    Pick
                </button>
            </div>
            <p class="mt-2 text-[11px] leading-5 text-gray-500 dark:text-gray-400">
                Leave this blank if you want the embedded video preview to appear directly in the editor.
            </p>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Video Link</label>
            <input
                v-model="state.video_link"
                type="text"
                class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs"
                placeholder="YouTube or direct link"
            >
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Primary Button</label>
            <div class="grid grid-cols-2 gap-2">
                <input
                    v-model="state.button1_text"
                    type="text"
                    class="bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs"
                    placeholder="Text"
                >
                <input
                    v-model="state.button1_url"
                    type="text"
                    class="bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs"
                    placeholder="URL"
                >
            </div>
        </div>

        <div class="form-group">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Secondary Button</label>
            <div class="grid grid-cols-2 gap-2">
                <input
                    v-model="state.button2_text"
                    type="text"
                    class="bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs"
                    placeholder="Text"
                >
                <input
                    v-model="state.button2_url"
                    type="text"
                    class="bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-600 rounded-lg p-2 text-xs"
                    placeholder="URL"
                >
            </div>
        </div>

        <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-4 py-3 text-[11px] leading-5 text-gray-500 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-400">
            Use the swap control in the preview between the two columns to flip the media and content layout.
        </div>
    </div>

    <div v-else class="showcase-block" :style="{ padding: state.padding }">
        <div class="showcase-block__header">
            <h2 class="showcase-block__heading">{{ state.heading || DEFAULT_HEADING }}</h2>
            <p v-if="state.subheading" class="showcase-block__subheading">{{ state.subheading }}</p>
        </div>

        <div class="showcase-block__layout" :class="{ 'showcase-block__layout--reverse': state.image_position === 'left' }">
            <button
                v-if="canSwapInPreview"
                type="button"
                class="showcase-block__swap"
                title="Swap columns"
                aria-label="Swap columns"
                @click.stop="toggleImagePosition"
            >
                <svg class="showcase-block__swap-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M7 7h10l-2.5-2.5 1.4-1.4L21.8 9l-5.9 5.9-1.4-1.4L17 11H7V7Zm10 10H7l2.5 2.5-1.4 1.4L2.2 15l5.9-5.9 1.4 1.4L7 13h10v4Z" fill="currentColor" />
                </svg>
            </button>

            <div class="showcase-block__copy">
                <h3 class="showcase-block__title">{{ state.demo_title || DEFAULT_TITLE }}</h3>

                <div v-if="hasActions" class="showcase-block__actions">
                    <button
                        v-if="state.button1_text"
                        type="button"
                        class="showcase-block__button showcase-block__button--primary"
                    >
                        {{ state.button1_text }}
                    </button>
                    <button
                        v-if="state.button2_text"
                        type="button"
                        class="showcase-block__button showcase-block__button--secondary"
                    >
                        {{ state.button2_text }}
                    </button>
                </div>

                <div class="showcase-block__steps">
                    <div
                        v-for="(step, index) in state.steps"
                        :key="`${index}-${step.title}`"
                        class="showcase-block__step"
                    >
                        <span class="showcase-block__step-badge">{{ Number(index) + 1 }}</span>
                        <div class="showcase-block__step-body">
                            <h4 class="showcase-block__step-title">{{ step.title }}</h4>
                            <p class="showcase-block__step-text">{{ step.text }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="showcase-block__media-wrap">
                <div class="showcase-block__media">
                    <img
                        v-if="state.preview_image"
                        :src="state.preview_image"
                        alt="Showcase preview"
                    >
                    <video
                        v-else-if="resolvedVideo?.kind === 'file'"
                        :src="resolvedVideo.fileUrl"
                        controls
                        preload="metadata"
                    ></video>
                    <iframe
                        v-else-if="resolvedVideo?.embedUrl"
                        :src="resolvedVideo.embedUrl"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                    <div v-else class="showcase-block__placeholder">Preview Image</div>
                    <div v-if="state.preview_image" class="showcase-block__media-overlay"></div>
                    <div v-if="state.preview_image && resolvedVideo" class="showcase-block__play">
                        <svg class="showcase-block__play-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8 6.5v11l9-5.5-9-5.5Z" fill="currentColor" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <MediaPicker ref="mediaPickerRef" @select="handleMediaSelect" />
</template>

<script setup lang="ts">
import { computed, nextTick, reactive, ref, watch } from 'vue';
import MediaPicker from '@/admin/components/MediaPicker.vue';
import { resolveVideoSource } from '@/admin/editor/videoSource';

const props = defineProps<{
    modelValue: Record<string, any> | null;
    isEditor?: boolean;
    mode?: 'settings' | 'preview';
    data?: Record<string, any> | null;
}>();

const emit = defineEmits(['update:modelValue']);
const mediaPickerRef = ref<any>(null);

const DEFAULT_HEADING = 'See It In Action';
const DEFAULT_TITLE = 'From Zero to SaaS in 10 Days';
const DEFAULT_SUBHEADING = 'Section Description';
const DEFAULT_IMAGE = 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1000&q=80';
const DEFAULT_STEPS = [
    { title: 'Branding & Setup', text: 'We customize everything with your logo and colors.' },
    { title: 'Training & Handover', text: 'We walk you through the admin panel.' },
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
    subheading: readAttr('subheading', DEFAULT_SUBHEADING),
    demo_title: readAttr('demo_title', DEFAULT_TITLE),
    steps: readAttr('steps', DEFAULT_STEPS),
    preview_image: readAttr('preview_image', DEFAULT_IMAGE),
    video_link: readAttr('video_link', ''),
    image_position: readAttr('image_position', 'right'),
    button1_text: readAttr('button1_text', 'Start Now'),
    button1_url: readAttr('button1_url', '#'),
    button2_text: readAttr('button2_text', 'Learn More'),
    button2_url: readAttr('button2_url', '#'),
    margin: readAttr('margin', ''),
    padding: readAttr('padding', ''),
});

const hasActions = computed(() => Boolean(state.button1_text || state.button2_text));
const canSwapInPreview = computed(() => props.mode === 'preview' && Boolean(props.isEditor));
const resolvedVideo = computed(() => resolveVideoSource(state.video_link));
const isSyncingFromProps = ref(false);

function buildPayload() {
    return {
        ...(props.modelValue || {}),
        heading: state.heading,
        subheading: state.subheading,
        demo_title: state.demo_title,
        steps: cloneValue(state.steps),
        preview_image: state.preview_image,
        video_link: state.video_link,
        image_position: state.image_position,
        button1_text: state.button1_text,
        button1_url: state.button1_url,
        button2_text: state.button2_text,
        button2_url: state.button2_url,
        margin: state.margin,
        padding: state.padding,
    };
}

function syncState(source: Record<string, any> | null | undefined) {
    isSyncingFromProps.value = true;
    state.heading = readSourceAttr(source, 'heading', DEFAULT_HEADING);
    state.subheading = readSourceAttr(source, 'subheading', DEFAULT_SUBHEADING);
    state.demo_title = readSourceAttr(source, 'demo_title', DEFAULT_TITLE);
    state.steps = readSourceAttr(source, 'steps', DEFAULT_STEPS);
    state.preview_image = readSourceAttr(source, 'preview_image', DEFAULT_IMAGE);
    state.video_link = readSourceAttr(source, 'video_link', '');
    state.image_position = readSourceAttr(source, 'image_position', 'right');
    state.button1_text = readSourceAttr(source, 'button1_text', 'Start Now');
    state.button1_url = readSourceAttr(source, 'button1_url', '#');
    state.button2_text = readSourceAttr(source, 'button2_text', 'Learn More');
    state.button2_url = readSourceAttr(source, 'button2_url', '#');
    state.margin = readSourceAttr(source, 'margin', '');
    state.padding = readSourceAttr(source, 'padding', '');

    nextTick(() => {
        isSyncingFromProps.value = false;
    });
}

function emitPayload() {
    emit('update:modelValue', buildPayload());
}

function toggleImagePosition() {
    state.image_position = state.image_position === 'left' ? 'right' : 'left';

    if (props.mode === 'preview') {
        emitPayload();
    }
}

function openMediaPicker() {
    mediaPickerRef.value?.open();
}

function handleMediaSelect(media: any) {
    const selected = Array.isArray(media) ? media[0] : media;
    if (selected?.url) {
        state.preview_image = selected.url;
    }
}

watch(
    state,
    () => {
        if (isSyncingFromProps.value) {
            return;
        }

        if (props.mode === 'settings') {
            emitPayload();
        }
    },
    { deep: true },
);

watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) {
            syncState(newValue);
        }
    },
    { deep: true, immediate: true },
);

watch(
    () => props.data,
    (newValue) => {
        if (newValue) {
            syncState(newValue);
        }
    },
    { deep: true, immediate: true },
);

function addStep() {
    state.steps.push({ title: 'New Step', text: 'Description of the next step.' });
}

function removeStep(index: number) {
    state.steps.splice(index, 1);
}
</script>

<style scoped>
.showcase-block {
    border-radius: 1.25rem;
    background: transparent;
}

.showcase-block__header {
    margin: 0 auto clamp(2.5rem, 5vw, 4rem);
    max-width: 48rem;
    text-align: center;
}

.showcase-block__heading {
    margin-bottom: 0.85rem;
    font-size: clamp(2rem, 4vw, 3.5rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.05;
    color: #111827;
}

.showcase-block__subheading {
    margin: 0 auto;
    max-width: 36rem;
    font-size: clamp(1rem, 1.5vw, 1.2rem);
    line-height: 1.7;
    color: #6b7280;
}

.showcase-block__layout {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
    align-items: center;
    gap: clamp(2rem, 5vw, 5rem);
}

.showcase-block__layout--reverse .showcase-block__copy {
    order: 2;
}

.showcase-block__layout--reverse .showcase-block__media-wrap {
    order: 1;
}

.showcase-block__copy {
    min-width: 0;
}

.showcase-block__swap {
    position: absolute;
    top: 50%;
    left: 50%;
    z-index: 2;
    display: inline-flex;
    width: 3.5rem;
    height: 3.5rem;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
    color: #4f46e5;
    transform: translate(-50%, -50%);
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, border-color 0.2s ease;
}

.showcase-block__swap:hover {
    border-color: rgba(79, 70, 229, 0.4);
    background: #ffffff;
    box-shadow: 0 18px 44px rgba(15, 23, 42, 0.22);
    transform: translate(-50%, -50%) scale(1.04);
}

.showcase-block__swap-icon {
    width: 1.35rem;
    height: 1.35rem;
}

.showcase-block__title {
    margin-bottom: 0;
    max-width: 12ch;
    font-size: clamp(2.2rem, 4.8vw, 4rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1.1;
    color: #111827;
}

.showcase-block__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin: 2rem 0 2.5rem;
}

.showcase-block__button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 9.5rem;
    border: none;
    border-radius: 0.95rem;
    padding: 0.95rem 1.6rem;
    cursor: default;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
}

.showcase-block__button--primary {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: #ffffff;
    box-shadow: 0 14px 30px rgba(79, 70, 229, 0.28);
}

.showcase-block__button--secondary {
    background: rgba(99, 102, 241, 0.08);
    color: #4f46e5;
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.45);
}

.showcase-block__steps {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.showcase-block__step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.showcase-block__step-badge {
    display: inline-flex;
    width: 3rem;
    height: 3rem;
    flex: none;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    box-shadow: 0 14px 26px rgba(79, 70, 229, 0.2);
    color: #ffffff;
    font-size: 1.05rem;
    font-weight: 800;
}

.showcase-block__step-body {
    padding-top: 0.2rem;
}

.showcase-block__step-title {
    margin-bottom: 0.4rem;
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1.2;
    color: #111827;
}

.showcase-block__step-text {
    margin: 0;
    font-size: 1.05rem;
    line-height: 1.75;
    color: #6b7280;
}

.showcase-block__media-wrap {
    min-width: 0;
}

.showcase-block__media {
    position: relative;
    overflow: hidden;
    border-radius: 1.35rem;
    background: #0f172a;
    aspect-ratio: 16 / 10;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
}

.showcase-block__media img,
.showcase-block__media iframe,
.showcase-block__media video {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
}

.showcase-block__media img,
.showcase-block__media video {
    object-fit: cover;
}

.showcase-block__placeholder {
    display: flex;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.95rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.showcase-block__media-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.04) 0%, rgba(15, 23, 42, 0.16) 100%);
}

.showcase-block__play {
    position: absolute;
    top: 50%;
    left: 50%;
    display: inline-flex;
    width: 5.5rem;
    height: 5.5rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.24);
    color: #4f46e5;
    transform: translate(-50%, -50%);
}

.showcase-block__play-icon {
    width: 1.75rem;
    height: 1.75rem;
    margin-left: 0.2rem;
}

.dark .showcase-block__heading,
.dark .showcase-block__title,
.dark .showcase-block__step-title {
    color: #f8fafc;
}

.dark .showcase-block__subheading,
.dark .showcase-block__step-text {
    color: #94a3b8;
}

.dark .showcase-block__button--secondary {
    background: rgba(99, 102, 241, 0.08);
    color: #a5b4fc;
    box-shadow: inset 0 0 0 1px rgba(129, 140, 248, 0.45);
}

.dark .showcase-block__swap {
    border-color: rgba(129, 140, 248, 0.18);
    background: rgba(15, 23, 42, 0.94);
    color: #a5b4fc;
}

.dark .showcase-block__swap:hover {
    border-color: rgba(129, 140, 248, 0.42);
    background: rgba(15, 23, 42, 1);
}

.dark .showcase-block__media {
    box-shadow: 0 28px 68px rgba(2, 6, 23, 0.34);
}

.dark .showcase-block__play {
    background: rgba(255, 255, 255, 0.94);
    color: #4f46e5;
}

@media (max-width: 1024px) {
    .showcase-block__layout {
        gap: 2.5rem;
    }

    .showcase-block__step-title {
        font-size: 1.2rem;
    }

    .showcase-block__step-text {
        font-size: 0.98rem;
    }
}

@media (max-width: 768px) {
    .showcase-block__layout {
        grid-template-columns: 1fr;
    }

    .showcase-block__swap {
        display: none;
    }

    .showcase-block__layout--reverse .showcase-block__copy,
    .showcase-block__layout--reverse .showcase-block__media-wrap {
        order: initial;
    }

    .showcase-block__title {
        max-width: none;
    }
}
</style>
