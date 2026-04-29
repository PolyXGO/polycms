<!DOCTYPE html>
<html lang="en" class="{{ $isDark ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $asset->name }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ theme_asset('css/landing-blocks.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ theme_asset('css/style.css') }}?v={{ time() }}">

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            min-height: {{ $asset->kind === 'template' ? '100%' : '0' }};
            overflow: hidden;
            background: {{ $isDark ? '#111827' : '#ffffff' }};
        }

        body {
            color: {{ $isDark ? '#f9fafb' : '#111827' }};
        }

        .layout-asset-preview-document {
            min-height: {{ $asset->kind === 'template' ? '100vh' : '0' }};
            background: inherit;
            pointer-events: none;
        }

        .layout-asset-preview-document a,
        .layout-asset-preview-document button,
        .layout-asset-preview-document input,
        .layout-asset-preview-document textarea,
        .layout-asset-preview-document select,
        .layout-asset-preview-document iframe,
        .layout-asset-preview-document video {
            pointer-events: none !important;
        }

        .layout-asset-preview-document .section-full-viewport {
            width: 100vw !important;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            left: auto !important;
            right: auto !important;
        }

        .layout-asset-preview-document .container,
        .layout-asset-preview-document .container-wide,
        .layout-asset-preview-document .container-fluid {
            width: 100% !important;
            max-width: none !important;
            margin-left: auto !important;
            margin-right: auto !important;
            padding-left: 32px !important;
            padding-right: 32px !important;
        }

        .layout-asset-preview-document > h1,
        .layout-asset-preview-document > h2,
        .layout-asset-preview-document > h3,
        .layout-asset-preview-document > h4,
        .layout-asset-preview-document > h5,
        .layout-asset-preview-document > h6 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-weight: 600;
            line-height: 1.2;
            color: inherit;
        }

        .layout-asset-preview-document > h1 {
            font-size: 2em;
        }

        .layout-asset-preview-document > h2 {
            font-size: 1.5em;
        }

        .layout-asset-preview-document > h3 {
            font-size: 1.25em;
        }

        .layout-asset-preview-document > p {
            margin: 0 0 1.5rem;
            line-height: 1.8;
            color: inherit;
        }

        .layout-asset-preview-document > ul,
        .layout-asset-preview-document > ol {
            margin: 1em 0;
            padding-left: 1.5em;
            color: inherit;
        }

        .layout-asset-preview-document > blockquote {
            margin: 1em 0;
            padding-left: 1em;
            border-left: 4px solid {{ $isDark ? '#4b5563' : '#e5e7eb' }};
            font-style: italic;
        }

        .layout-asset-preview-document > pre {
            margin: 1em 0;
            overflow-x: auto;
            border-radius: 0.5rem;
            padding: 1em;
            background: {{ $isDark ? '#1f2937' : '#f3f4f6' }};
        }

        .layout-asset-preview-document > code {
            border-radius: 0.25rem;
            padding: 0.2em 0.4em;
            font-size: 0.9em;
            background: {{ $isDark ? '#374151' : '#f3f4f6' }};
        }

        .layout-asset-preview-document > img {
            max-width: 100%;
            height: auto;
            margin: 1em 0;
            border-radius: 0.5rem;
        }

        .layout-asset-preview-document > a {
            color: {{ $isDark ? '#818cf8' : '#4f46e5' }};
            text-decoration: underline;
        }
    </style>
</head>
<body class="{{ $isDark ? 'dark' : '' }}">
    <div class="layout-asset-preview-document prose">{!! $contentHtml !!}</div>

    <script>
        (() => {
            const root = document.querySelector('.layout-asset-preview-document');
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
    </script>
</body>
</html>
