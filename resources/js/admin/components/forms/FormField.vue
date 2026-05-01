<template>
    <div class="form-field">
        <label
            v-if="label || $slots.label"
            :for="fieldId"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
        >
            <slot name="label">
                {{ label }}
                <span v-if="required" class="text-red-500">*</span>
            </slot>
        </label>

        <div class="relative">
            <slot />
        </div>

        <FormError
            v-if="error || $slots.error"
            :message="error"
            :field="name"
        >
            <template v-if="$slots.error">
                <slot name="error" />
            </template>
        </FormError>

        <p
            v-if="hint || $slots.hint"
            :id="hintId"
            class="mt-1 text-xs text-gray-500 dark:text-gray-400"
        >
            <slot name="hint">
                {{ hint }}
            </slot>
        </p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import FormError from './FormError.vue';

interface Props {
    name: string;
    label?: string;
    required?: boolean;
    error?: string;
    hint?: string;
    disabled?: boolean;
}

const props = defineProps<Props>();

const fieldId = computed(() => `field-${props.name}`);
const hintId = computed(() => `hint-${props.name}`);
</script>

<style scoped>
.form-field {
    @apply w-full;
}
</style>
