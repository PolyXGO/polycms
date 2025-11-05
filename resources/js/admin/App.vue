<template>
    <router-view />
    <DialogProvider />
</template>

<script setup lang="ts">
import DialogProvider from './components/dialogs/DialogProvider.vue';
</script>
