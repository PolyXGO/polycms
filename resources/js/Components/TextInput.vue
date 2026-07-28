<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="border-gray-300 focus:border-gray-900 focus:ring-gray-900 rounded-md shadow-sm dark:border-gray-700 dark:bg-black dark:text-gray-300 dark:focus:border-gray-100 dark:focus:ring-gray-100"
        v-model="model"
        ref="input"
    />
</template>
