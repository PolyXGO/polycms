export interface ResolvedVideoSource {
    kind: 'youtube' | 'vimeo' | 'file';
    originalUrl: string;
    embedUrl?: string;
    fileUrl?: string;
}

const DIRECT_VIDEO_FILE_REGEX = /\.(mp4|webm|ogg|mov|m4v)(\?.*)?$/i;

export function resolveVideoSource(url?: string | null): ResolvedVideoSource | null {
    const normalizedUrl = String(url || '').trim();

    if (!normalizedUrl) {
        return null;
    }

    const youtubeMatch = normalizedUrl.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?/ ]{11})/i);
    if (youtubeMatch?.[1]) {
        return {
            kind: 'youtube',
            originalUrl: normalizedUrl,
            embedUrl: `https://www.youtube.com/embed/${youtubeMatch[1]}`,
        };
    }

    const vimeoMatch = normalizedUrl.match(/vimeo\.com\/(?:video\/)?([0-9]+)/i);
    if (vimeoMatch?.[1]) {
        return {
            kind: 'vimeo',
            originalUrl: normalizedUrl,
            embedUrl: `https://player.vimeo.com/video/${vimeoMatch[1]}`,
        };
    }

    if (DIRECT_VIDEO_FILE_REGEX.test(normalizedUrl)) {
        return {
            kind: 'file',
            originalUrl: normalizedUrl,
            fileUrl: normalizedUrl,
        };
    }

    return null;
}

export function aspectRatioToCss(value?: string | null) {
    const ratio = String(value || '16/9').trim();
    const [width, height] = ratio.split('/').map((part) => Number.parseFloat(part));

    if (!Number.isFinite(width) || !Number.isFinite(height) || width <= 0 || height <= 0) {
        return '16 / 9';
    }

    return `${width} / ${height}`;
}
