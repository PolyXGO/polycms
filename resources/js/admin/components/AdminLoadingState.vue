<template>
 <div class="text-center py-12 bg-admin-theme-surface rounded-lg shadow">
 <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
 <p class="mt-2 text-admin-theme-text-secondary">{{ message }}</p>
 </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
 message?: string;
}>(), {
 message: 'Loading...',
});
</script>
