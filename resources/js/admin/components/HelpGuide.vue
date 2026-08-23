<template>
  <button type="button" @click.prevent="openModal" class="inline-flex items-center justify-center text-admin-theme-text-muted hover:text-admin-theme-primary transition-colors focus:outline-none cursor-pointer" :class="iconClass" :title="tooltip || t('Help')">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
    </svg>
  </button>

  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[999999] overflow-y-auto"
        @click.self="closeModal"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-xs"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            class="relative w-full transform overflow-hidden rounded-2xl bg-admin-theme-surface border border-admin-theme-border shadow-2xl transition-all"
            :class="maxWidth"
            @click.stop
          >
            <!-- Header -->
            <div class="px-6 py-4 border-b border-admin-theme-border flex items-center justify-between bg-black/[0.02] dark:bg-white/[0.02]">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-admin-theme-primary/10 text-admin-theme-primary flex items-center justify-center text-xs font-bold shrink-0">
                  <i class="fas fa-question"></i>
                </div>
                <h3 class="text-base font-bold text-admin-theme-text">
                  {{ title }}
                </h3>
              </div>
              <button
                type="button"
                @click="closeModal"
                class="w-7 h-7 flex items-center justify-center rounded-lg text-admin-theme-text-muted hover:text-admin-theme-text hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Body -->
            <div class="p-6 text-admin-theme-text-secondary text-sm max-h-[80vh] overflow-y-auto">
              <slot>
                <div v-if="description" v-html="description"></div>
              </slot>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3 border-t border-admin-theme-border flex justify-end bg-black/[0.02] dark:bg-white/[0.02]">
              <button 
                type="button" 
                @click="closeModal"
                class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-admin-theme-input-bg border border-admin-theme-border text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer"
              >
                {{ t('Close') || 'Đóng' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useTranslation } from '../composables/useTranslation';

const { t } = useTranslation();

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  tooltip: {
    type: String,
    default: ''
  },
  iconClass: {
    type: String,
    default: 'ml-1'
  },
  maxWidth: {
    type: String,
    default: 'max-w-xl'
  }
});

const isOpen = ref(false);

const openModal = () => {
  isOpen.value = true;
};

const closeModal = () => {
  isOpen.value = false;
};
</script>
