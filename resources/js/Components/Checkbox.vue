<script setup lang="ts">
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps<{
    checked: boolean;
    value?: any;
}>();

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900 dark:border-gray-700 dark:bg-black dark:text-gray-100 dark:focus:ring-gray-100 dark:focus:ring-offset-gray-800"
    />
</template>
