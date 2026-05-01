<template>
    <div class="h-full flex flex-col">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex-shrink-0">Media Library</h1>
        <div class="flex-1 overflow-hidden pb-4">
            <MediaManager class="!h-full" />
        </div>
    </div>
</template>

<script setup lang="ts">
import MediaManager from '../../components/MediaManager.vue';
</script>
