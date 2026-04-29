<template>
    <div class="module-action-modal">
        <div v-if="content" v-html="content"></div>
        <div v-else class="text-center py-8">
            <p class="text-gray-500">No content available in your language.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    contentMap: Record<string, string>;
    language: string;
}>();

const content = computed(() => {
    // Try current language, fallback to English
    return props.contentMap[props.language] || props.contentMap['en'] || null;
});
</script>

<style scoped>
.module-action-modal :deep(h4) {
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}
.module-action-modal :deep(p) {
    margin-bottom: 1rem;
}
</style>
