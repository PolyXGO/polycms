<template>
    <div ref="hostRef" class="relative h-full w-full overflow-hidden bg-white dark:bg-gray-950">
        <iframe
            v-if="iframeSrc"
            ref="iframeRef"
            class="absolute left-0 top-0 border-0 pointer-events-none bg-white"
            :style="iframeStyle"
            :src="iframeSrc"
            loading="lazy"
            tabindex="-1"
            aria-hidden="true"
        ></iframe>

        <iframe
            v-else-if="html"
            ref="iframeRef"
            class="absolute left-0 top-0 border-0 pointer-events-none bg-white"
            :style="iframeStyle"
            :srcdoc="srcdoc"
            loading="lazy"
            tabindex="-1"
            aria-hidden="true"
        ></iframe>

        <div
            v-else
            class="absolute inset-0 flex items-center justify-center px-4 text-center text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400"
        >
            {{ fallbackLabel }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

declare global {
    interface Window {
        polycmsThemePreviewAssets?: {
            frontendStyles?: string[];
        };
    }
}

const props = withDefaults(defineProps<{
    src?: string | null;
    html?: string | null;
    fallbackLabel?: string;
    viewportWidth?: number;
    viewportHeight?: number;
    contentKind?: string;
    fitMode?: 'width' | 'contain';
}>(), {
    src: '',
    html: '',
    fallbackLabel: 'Preview',
    viewportWidth: 1440,
    viewportHeight: 1080,
    contentKind: 'part',
    fitMode: 'width',
});

const hostRef = ref<HTMLElement | null>(null);
const iframeRef = ref<HTMLIFrameElement | null>(null);
const hostWidth = ref(0);
const hostHeight = ref(0);
const isDark = ref(false);
const contentHeight = ref(0);

let resizeObserver: ResizeObserver | null = null;
let classObserver: MutationObserver | null = null;

const updateBounds = () => {
    const rect = hostRef.value?.getBoundingClientRect();
    hostWidth.value = rect?.width || 0;
    hostHeight.value = rect?.height || 0;
};

const syncThemeMode = () => {
    isDark.value = document.documentElement.classList.contains('dark');
};

const stylesheetUrls = computed(() => {
    const themeStyles = window.polycmsThemePreviewAssets?.frontendStyles || [];

    return Array.from(new Set(themeStyles.filter(Boolean)));
});

const iframeSrc = computed(() => {
    if (!props.src) {
        return '';
    }

    try {
        const url = new URL(props.src, window.location.origin);
        url.searchParams.set('dark', isDark.value ? '1' : '0');
        return url.toString();
    } catch {
        return props.src;
    }
});

const effectiveContentHeight = computed(() => {
    if (contentHeight.value > 0) {
        return contentHeight.value;
    }

    return props.viewportHeight;
});

const scale = computed(() => {
    if (!hostWidth.value) {
        return 0.1;
    }

    const widthScale = hostWidth.value / props.viewportWidth;

    if (props.fitMode === 'contain' && hostHeight.value) {
        const heightScale = hostHeight.value / effectiveContentHeight.value;
        return Math.max(Math.min(widthScale, heightScale), 0.05);
    }

    return Math.max(Math.min(widthScale, 1), 0.05);
});

const offsetX = computed(() => Math.max((hostWidth.value - props.viewportWidth * scale.value) / 2, 0));
const offsetY = computed(() => {
    if (props.fitMode !== 'contain') {
        return 0;
    }

    return Math.max((hostHeight.value - effectiveContentHeight.value * scale.value) / 2, 0);
});

const iframeStyle = computed(() => ({
    width: `${props.viewportWidth}px`,
    height: `${effectiveContentHeight.value}px`,
    transform: `translate(${offsetX.value}px, ${offsetY.value}px) scale(${scale.value})`,
    transformOrigin: 'top left',
}));

const srcdoc = computed(() => {
    const links = stylesheetUrls.value
        .map((href) => `<link rel="stylesheet" href="${href}">`)
        .join('');

    return `<!doctype html>
<html class="${isDark.value ? 'dark' : ''}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    ${links}
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: ${props.viewportWidth}px;
            min-height: ${props.contentKind === 'template' ? `${props.viewportHeight}px` : '0'};
            overflow: hidden;
            background: ${isDark.value ? '#030712' : '#ffffff'};
        }
        body {
            color: ${isDark.value ? '#f9fafb' : '#111827'};
        }
        .layout-asset-preview-root {
            width: 100%;
            min-height: ${props.contentKind === 'template' ? `${props.viewportHeight}px` : '0'};
            pointer-events: none;
        }
        .layout-asset-preview-root a,
        .layout-asset-preview-root button,
        .layout-asset-preview-root input,
        .layout-asset-preview-root textarea,
        .layout-asset-preview-root select,
        .layout-asset-preview-root iframe,
        .layout-asset-preview-root video {
            pointer-events: none !important;
        }
        .layout-asset-preview-root .section-full-viewport {
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            left: auto !important;
            right: auto !important;
        }
        .layout-asset-preview-root .container,
        .layout-asset-preview-root .container-wide,
        .layout-asset-preview-root .container-fluid {
            width: 100% !important;
            max-width: none !important;
            margin-left: auto !important;
            margin-right: auto !important;
            padding-left: 32px !important;
            padding-right: 32px !important;
        }
        .layout-asset-preview-root > h1,
        .layout-asset-preview-root > h2,
        .layout-asset-preview-root > h3,
        .layout-asset-preview-root > h4,
        .layout-asset-preview-root > h5,
        .layout-asset-preview-root > h6 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-weight: 600;
            line-height: 1.2;
            color: inherit;
        }
        .layout-asset-preview-root > h1 {
            font-size: 2em;
        }
        .layout-asset-preview-root > h2 {
            font-size: 1.5em;
        }
        .layout-asset-preview-root > h3 {
            font-size: 1.25em;
        }
        .layout-asset-preview-root > p {
            margin: 0 0 1.5rem;
            line-height: 1.8;
            color: inherit;
        }
        .layout-asset-preview-root > ul,
        .layout-asset-preview-root > ol {
            margin: 1em 0;
            padding-left: 1.5em;
            color: inherit;
        }
        .layout-asset-preview-root > blockquote {
            margin: 1em 0;
            padding-left: 1em;
            border-left: 4px solid ${isDark.value ? '#4b5563' : '#e5e7eb'};
            font-style: italic;
        }
        .layout-asset-preview-root > pre {
            margin: 1em 0;
            overflow-x: auto;
            border-radius: 0.5rem;
            padding: 1em;
            background: ${isDark.value ? '#1f2937' : '#f3f4f6'};
        }
        .layout-asset-preview-root > code {
            border-radius: 0.25rem;
            padding: 0.2em 0.4em;
            font-size: 0.9em;
            background: ${isDark.value ? '#374151' : '#f3f4f6'};
        }
        .layout-asset-preview-root > img {
            max-width: 100%;
            height: auto;
            margin: 1em 0;
            border-radius: 0.5rem;
        }
        .layout-asset-preview-root > a {
            color: ${isDark.value ? '#818cf8' : '#4f46e5'};
            text-decoration: underline;
        }
    </style>
</head>
<body class="${isDark.value ? 'dark' : ''}">
    <div class="layout-asset-preview-root prose">${props.html || ''}</div>
    <script>
        (() => {
            const root = document.querySelector('.layout-asset-preview-root');
            const notifyParent = () => {
                const height = Math.max(
                    document.documentElement.scrollHeight,
                    document.body.scrollHeight,
                    root?.scrollHeight || 0,
                    Math.ceil(root?.getBoundingClientRect()?.height || 0)
                );

                window.parent?.postMessage({
                    type: 'layout-asset-preview:metrics',
                    height,
                    width: window.innerWidth,
                }, '*');
            };

            const scheduleNotify = () => window.requestAnimationFrame(notifyParent);

            window.addEventListener('load', scheduleNotify);
            window.addEventListener('resize', scheduleNotify);

            if (typeof ResizeObserver !== 'undefined' && root) {
                const rootObserver = new ResizeObserver(scheduleNotify);
                rootObserver.observe(root);
            }

            if (document.fonts?.ready) {
                document.fonts.ready.then(scheduleNotify).catch(() => {});
            }

            document.querySelectorAll('img').forEach((image) => {
                if (!image.complete) {
                    image.addEventListener('load', scheduleNotify, { once: true });
                    image.addEventListener('error', scheduleNotify, { once: true });
                }
            });

            setTimeout(scheduleNotify, 0);
            setTimeout(scheduleNotify, 150);
            setTimeout(scheduleNotify, 500);
        })();
    <\/script>
</body>
</html>`;
});

const handleMessage = (event: MessageEvent) => {
    if (!iframeRef.value?.contentWindow || event.source !== iframeRef.value.contentWindow) {
        return;
    }

    if (event.data?.type !== 'layout-asset-preview:metrics') {
        return;
    }

    const nextHeight = Number(event.data.height);
    if (Number.isFinite(nextHeight) && nextHeight > 0) {
        contentHeight.value = nextHeight;
    }
};

watch(
    [iframeSrc, () => props.html, () => props.viewportHeight],
    () => {
        contentHeight.value = props.viewportHeight;
    },
    { immediate: true }
);

onMounted(() => {
    updateBounds();
    syncThemeMode();
    window.addEventListener('message', handleMessage);

    if (hostRef.value) {
        resizeObserver = new ResizeObserver(() => updateBounds());
        resizeObserver.observe(hostRef.value);
    }

    classObserver = new MutationObserver(() => syncThemeMode());
    classObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    classObserver?.disconnect();
    window.removeEventListener('message', handleMessage);
});
</script>
