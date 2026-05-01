<template>
    <div class="relative">
        <select
            :id="selectId"
            :value="modelValue"
            :disabled="disabled"
            :required="required"
            :class="selectClasses"
            @change="handleChange"
        >
            <option value="" v-if="placeholder">{{ placeholder }}</option>
            <option v-for="country in countries" :key="country.code" :value="country.code">
                {{ country.name }} ({{ country.code }})
            </option>
        </select>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { countries } from '../../data/countries';

interface Props {
    name?: string;
    modelValue: string;
    placeholder?: string;
    disabled?: boolean;
    required?: boolean;
    error?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    name: 'country',
    placeholder: '-- Select Country --',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const selectId = computed(() => `country-select-${props.name}`);

const selectClasses = computed(() => {
    const baseClasses = 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm';
    if (props.error) {
        return `${baseClasses} border-red-500 focus:border-red-500 focus:ring-red-500`;
    }
    return baseClasses;
});

const handleChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    emit('update:modelValue', target.value);
};
</script>
