import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue()],
    build: {
        lib: {
            entry: resolve(__dirname, 'resources/admin/entry.ts'),
            name: 'PolyCMS_SampleModule',
            fileName: () => 'admin.js',
            cssFileName: 'style',
            formats: ['iife'],
        },
        outDir: resolve(__dirname, 'dist'),
        emptyOutDir: true,
        cssCodeSplit: false,
        rollupOptions: {
            external: ['vue', 'vue-router', 'pinia', 'axios'],
            output: {
                globals: {
                    vue: '__POLYCMS_SDK__.Vue',
                    'vue-router': '__POLYCMS_SDK__.VueRouter',
                    pinia: '__POLYCMS_SDK__.Pinia',
                    axios: '__POLYCMS_SDK__.axios',
                },
                assetFileNames: '[name][extname]',
            },
        },
    },
    resolve: { alias: { '@polycms': resolve(__dirname, 'resources/admin/sdk.ts') } },
});
