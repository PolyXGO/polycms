<template>
  <div class="relative inline-block text-left">
    <!-- Button -->
    <button
      type="button"
      @click="isOpen = !isOpen"
      :disabled="disabled"
      class="px-2 py-1 rounded text-sm font-medium transition-colors border-none bg-admin-theme-input-bg text-admin-theme-text hover:bg-admin-theme-base focus:outline-none flex flex-col items-center justify-center relative h-[30px] min-w-[30px]"
      :class="{ 'opacity-50 cursor-not-allowed': disabled, 'bg-admin-theme-base': isOpen }"
      :title="title"
    >
      <slot name="icon"></slot>
      <div 
        class="absolute bottom-0.5 w-[70%] h-1 rounded-sm border border-black/10 dark:border-white/10"
        :style="{ backgroundColor: modelValue || 'transparent' }"
      ></div>
    </button>

    <!-- Overlay to close -->
    <div 
      v-if="isOpen" 
      @click="isOpen = false" 
      class="fixed inset-0 z-40"
    ></div>

    <!-- Popover -->
    <div
      v-if="isOpen"
      class="absolute z-50 mt-1 w-64 rounded-md shadow-lg bg-admin-theme-base border border-admin-theme-border p-2 focus:outline-none"
    >
      <!-- Predefined Colors Grid -->
      <div class="grid grid-cols-8 gap-1 mb-2">
        <button
          v-for="color in presetColors"
          :key="color"
          type="button"
          @click="selectColor(color)"
          class="w-6 h-6 rounded-sm border border-admin-theme-border hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-admin-theme-primary focus:ring-offset-1"
          :style="{ backgroundColor: color }"
          :title="color"
        ></button>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between border-t border-admin-theme-border pt-2 gap-2">
        <button
          type="button"
          @click="selectColor('')"
          class="flex items-center justify-center p-1.5 rounded-md text-admin-theme-text-secondary hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors flex-1 border border-transparent hover:border-red-200"
          :title="$t('Clear Color') || 'Clear Color'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </button>
        
        <div class="relative flex-1 flex justify-end">
          <label class="cursor-pointer p-1.5 rounded-md text-admin-theme-text-secondary hover:text-admin-theme-primary hover:bg-admin-theme-primary/10 transition-colors border border-transparent hover:border-admin-theme-primary/30 flex items-center justify-center" :title="$t('Custom Color') || 'Custom Color'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
            <input 
              type="color" 
              :value="modelValue || '#000000'"
              @input="handleNativeColor"
              class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
            >
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  modelValue?: string;
  title?: string;
  disabled?: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const isOpen = ref(false);

const presetColors = [
  '#000000', '#434343', '#666666', '#999999', '#b7b7b7', '#cccccc', '#d9d9d9', '#ffffff',
  '#980000', '#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#4a86e8', '#0000ff',
  '#e6b8af', '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#c9daf8', '#cfe2f3',
  '#cc4125', '#e06666', '#f6b26b', '#ffd966', '#93c47d', '#76a5af', '#6d9eeb', '#6fa8dc',
  '#a61c00', '#cc0000', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3c78d8', '#3d85c6',
];

const selectColor = (color: string) => {
  emit('update:modelValue', color);
  isOpen.value = false;
};

const handleNativeColor = (event: Event) => {
  const target = event.target as HTMLInputElement;
  selectColor(target.value);
};
</script>
