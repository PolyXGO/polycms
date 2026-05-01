import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.ts',
                'resources/js/admin/main.ts',
                'resources/js/ecommerce-bridge.ts',
                'resources/css/app.css',
                'resources/css/landing-blocks.css',
                'modules/Polyx/XemTuoiXongDat/resources/admin/index.ts',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
            '@admin': resolve(__dirname, 'resources/js/admin'),
            '@modules': resolve(__dirname, 'modules'),
            '@polycms': resolve(__dirname, 'resources/js/polycms-sdk.ts'),
        },
    },
});
