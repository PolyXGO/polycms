<template>
    <div class="form-group mb-0">
        <label v-if="label" class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ label }}</label>
        <div class="relative">
            <select 
                :value="modelValue" 
                @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
                class="w-full appearance-none rounded-xl border border-gray-200 bg-white py-2.5 pl-3 pr-9 text-sm font-medium text-gray-700 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            >
                <option value="primary">Primary Button</option>
                <option value="secondary">Secondary Button</option>
                <option value="light">Light Button</option>
                <option value="ghost">Ghost Button</option>
                <option value="danger">Danger Button</option>
                <option value="custom">Custom Style</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    modelValue: string;
    label?: string;
}>();

defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>
