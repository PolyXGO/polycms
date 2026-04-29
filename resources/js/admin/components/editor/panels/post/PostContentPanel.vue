<template>
    <div class="space-y-4">
        <TiptapEditor v-model="contentHtml" :placeholder="'Start typing your content...'" />
    </div>
</template>

<script setup lang="ts">
import { computed, inject, isRef, ref } from 'vue';
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore - Vue SFC typing provided via shim
import TiptapEditor from '../../../TiptapEditor.ts';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('PostContentPanel must be used within editor context');
}

if (!context.state.contentHtml) {
    throw new Error('Post editor context missing contentHtml state');
}

const rawContent = context.state?.contentHtml;
const baseRef = isRef(rawContent) ? rawContent : ref(rawContent ?? '');

if (!isRef(rawContent) && context.state) {
    (context.state as Record<string, unknown>).contentHtml = baseRef;
}

const contentHtml = computed<string>({
    get: () => (baseRef.value ?? '') as string,
    set: (value) => {
        baseRef.value = value ?? '';
    },
});
</script>

