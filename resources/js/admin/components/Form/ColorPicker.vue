<template>
    <div class="color-picker-control flex flex-col items-center">
        <label v-if="label" class="block text-[10px] text-admin-theme-text-muted mb-1 uppercase text-center truncate w-full" :title="label">
            {{ label }}
        </label>
        
        <!-- Native Color Picker Wrapper -->
        <div 
            class="relative w-8 h-8 rounded-md overflow-hidden shadow-sm border border-admin-theme-border cursor-pointer transition-transform hover:scale-105 active:scale-95"
            :class="[wrapperClass]"
            :style="{ backgroundColor: modelValue || '#ffffff' }"
        >
            <input 
                type="color" 
                :value="modelValue" 
                @input="onInput"
                class="absolute inset-0 w-[200%] h-[200%] -translate-x-1/4 -translate-y-1/4 opacity-0 cursor-pointer"
            >
        </div>
    </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
    modelValue?: string;
    label?: string;
    wrapperClass?: string;
}>(), {
    modelValue: '#000000',
    label: '',
    wrapperClass: ''
});

const emit = defineEmits(['update:modelValue']);

const onInput = (e: Event) => {
    emit('update:modelValue', (e.target as HTMLInputElement).value);
};
</script>
