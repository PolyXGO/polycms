<template>
    <div class="space-y-4">
        <TiptapEditor v-model="description" :placeholder="$t('Describe your product...')" />
    </div>
</template>

<script setup lang="ts">
import { computed, inject, isRef, ref, getCurrentInstance } from 'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import TiptapEditor from '../../../TiptapEditor.ts';
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('ProductDescriptionPanel must be used within editor context');
}

const rawDescription = context.state?.descriptionHtml;
const baseRef = isRef(rawDescription) ? rawDescription : ref(rawDescription ?? '');

if (!isRef(rawDescription) && context.state) {
    (context.state as Record<string, unknown>).descriptionHtml = baseRef;
}

const description = computed<string>({
    get: () => (baseRef.value ?? '') as string,
    set: (value) => {
        baseRef.value = value ?? '';
    },
});
</script>

