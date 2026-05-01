<template>
    <div v-if="mode === 'settings'" class="video-block-settings space-y-4">
        <div class="form-group">
            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Video URL</label>
            <input
                v-model="state.url"
                type="text"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2 text-sm focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900"
                placeholder="https://www.youtube.com/watch?v=..."
            >
        </div>

        <div class="form-group">
            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Preview Image</label>
            <div class="flex gap-2">
                <input
                    v-model="state.preview_image"
                    type="text"
                    class="flex-1 rounded-lg border border-gray-200 bg-gray-50 p-2 text-xs focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900"
                    placeholder="Pick an image from Media or paste a URL..."
                >
                <button
                    type="button"
                    class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600"
                    @click="openMediaPicker"
                >
                    Pick
                </button>
            </div>
            <p class="mt-2 text-[11px] leading-5 text-gray-500 dark:text-gray-400">
                Leave this empty to preview the embedded iframe or direct video player in the editor.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Aspect Ratio</label>
                <select
                    v-model="state.aspect_ratio"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-900"
                >
                    <option value="16/9">Widescreen (16:9)</option>
                    <option value="16/10">Laptop (16:10)</option>
                    <option value="4/3">Standard (4:3)</option>
                    <option value="1/1">Square (1:1)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Caption</label>
                <input
                    v-model="state.caption"
                    type="text"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-900"
                    placeholder="Short video description..."
                >
            </div>
        </div>

        <MediaPicker ref="mediaPickerRef" @select="handleMediaSelect" />
    </div>

    <div v-else class="video-block-preview" :style="{ padding: state.padding }">
        <div class="video-preview-container" :style="{ aspectRatio: aspectRatioCss }">
            <template v-if="state.preview_image">
                <img
                    :src="state.preview_image"
                    :alt="state.caption || 'Video preview'"
                    class="video-preview-image"
                >
                <div class="video-preview-overlay"></div>
                <div v-if="resolvedVideo" class="video-preview-play" aria-hidden="true">
                    <svg class="video-preview-play-icon" viewBox="0 0 24 24">
                        <path d="M8 6.5v11l9-5.5-9-5.5Z" fill="currentColor" />
                    </svg>
                </div>
            </template>

            <template v-else-if="resolvedVideo?.kind === 'file'">
                <video :src="resolvedVideo.fileUrl" class="video-preview-player" controls preload="metadata"></video>
            </template>

            <template v-else-if="resolvedVideo?.embedUrl">
                <iframe
                    :src="resolvedVideo.embedUrl"
                    class="video-preview-player"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            </template>

            <div v-else class="video-preview-placeholder">
                <svg class="video-preview-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                </svg>
                <p class="video-preview-label">{{ state.caption || 'Video' }}</p>
                <p v-if="state.url" class="video-preview-url">{{ truncatedUrl }}</p>
            </div>
        </div>

        <p v-if="state.caption" class="video-preview-caption">{{ state.caption }}</p>
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, reactive, ref, watch } from 'vue';
import MediaPicker from '@/admin/components/MediaPicker.vue';
import { aspectRatioToCss, resolveVideoSource } from '@/admin/editor/videoSource';

const props = defineProps<{
    modelValue: Record<string, any> | null;
    mode?: 'settings' | 'preview';
    data?: Record<string, any> | null;
}>();

const emit = defineEmits(['update:modelValue']);

const mediaPickerRef = ref<any>(null);
const isSyncingFromProps = ref(false);

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
    url: readAttr('url', ''),
    preview_image: readAttr('preview_image', ''),
    aspect_ratio: readAttr('aspect_ratio', '16/9'),
    caption: readAttr('caption', ''),
    margin: readAttr('margin', ''),
    padding: readAttr('padding', ''),
});

const truncatedUrl = computed(() => {
    if (!state.url) {
        return '';
    }

    return state.url.length > 48 ? `${state.url.slice(0, 48)}...` : state.url;
});

const resolvedVideo = computed(() => resolveVideoSource(state.url));
const aspectRatioCss = computed(() => aspectRatioToCss(state.aspect_ratio));

function buildPayload() {
    return {
        ...(props.modelValue || {}),
        url: state.url,
        preview_image: state.preview_image,
        aspect_ratio: state.aspect_ratio,
        caption: state.caption,
        margin: state.margin,
        padding: state.padding,
    };
}

function syncState(source: Record<string, any> | null | undefined) {
    isSyncingFromProps.value = true;
    state.url = readSourceAttr(source, 'url', '');
    state.preview_image = readSourceAttr(source, 'preview_image', '');
    state.aspect_ratio = readSourceAttr(source, 'aspect_ratio', '16/9');
    state.caption = readSourceAttr(source, 'caption', '');
    state.margin = readSourceAttr(source, 'margin', '');
    state.padding = readSourceAttr(source, 'padding', '');

    nextTick(() => {
        isSyncingFromProps.value = false;
    });
}

function emitPayload() {
    emit('update:modelValue', buildPayload());
}

function openMediaPicker() {
    mediaPickerRef.value?.open();
}

function handleMediaSelect(media: any) {
    const selected = Array.isArray(media) ? media[0] : media;
    if (!selected?.url) {
        return;
    }

    state.preview_image = selected.url;
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
</script>

<style scoped>
.video-block-preview {
    width: 100%;
}

.video-preview-container {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    background: #0f172a;
    box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.18);
}

.video-preview-player,
.video-preview-image {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
}

.video-preview-player {
    background: #020617;
}

.video-preview-image {
    object-fit: cover;
}

.video-preview-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.04) 0%, rgba(15, 23, 42, 0.16) 100%);
}

.video-preview-play {
    position: absolute;
    top: 50%;
    left: 50%;
    display: inline-flex;
    width: 5rem;
    height: 5rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.24);
    color: #4f46e5;
    transform: translate(-50%, -50%);
}

.video-preview-play-icon {
    width: 1.6rem;
    height: 1.6rem;
    margin-left: 0.18rem;
}

.video-preview-placeholder {
    display: flex;
    min-height: 12rem;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.82);
    text-align: center;
    padding: 1.5rem;
}

.video-preview-icon {
    width: 2.8rem;
    height: 2.8rem;
    opacity: 0.66;
    margin-bottom: 0.7rem;
}

.video-preview-label {
    margin: 0;
    font-size: 0.85rem;
    font-weight: 700;
}

.video-preview-url {
    margin: 0.35rem 0 0;
    font-size: 0.7rem;
    opacity: 0.6;
}

.video-preview-caption {
    margin: 0.75rem 0 0;
    font-size: 0.8rem;
    line-height: 1.5;
    color: #6b7280;
}

.dark .video-preview-container {
    box-shadow: inset 0 0 0 1px rgba(71, 85, 105, 0.68);
}

.dark .video-preview-caption {
    color: #94a3b8;
}
</style>
