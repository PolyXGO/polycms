<template>
    <p
        v-if="message"
        :id="errorId"
        class="mt-1 text-sm text-red-600 dark:text-red-400"
        role="alert"
        :aria-live="ariaLive"
    >
        {{ message }}
    </p>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    message?: string;
    field?: string;
    ariaLive?: 'polite' | 'assertive' | 'off';
}

const props = withDefaults(defineProps<Props>(), {
    ariaLive: 'polite',
});

const errorId = computed(() => {
    return props.field ? `${props.field}-error` : 'form-error';
});
</script>
