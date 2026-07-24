<template>
 <Teleport to="body">
 <Transition
 enter-active-class="transition-opacity duration-300"
 enter-from-class="opacity-0"
 enter-to-class="opacity-100"
 leave-active-class="transition-opacity duration-200"
 leave-from-class="opacity-100"
 leave-to-class="opacity-0"
 >
 <div
 v-if="dialogStore.modal.show"
 class="fixed inset-0 z-[999999] overflow-y-auto"
 @click.self="handleClose"
 >
 <!-- Backdrop -->
 <div class="fixed inset-0 bg-black/50 dark:bg-black/70"></div>

 <!-- Modal -->
 <div 
  class="flex min-h-full items-center p-4 transition-all duration-300"
  :class="hasActiveLandingBlock ? 'justify-start pl-8' : 'justify-center'"
 >
 <div
 :class="[
'relative w-full transform overflow-hidden rounded-lg bg-admin-theme-surface shadow-xl transition-all duration-300',
 sizeClass
 ]"
 :style="hasActiveLandingBlock ? { maxWidth: `calc(100vw - ${landingPanelWidth + 48}px)` } : {}"
 @click.stop
 >
 <!-- Header -->
 <div
 v-if="dialogStore.modal.title || dialogStore.modal.closable"
 class="px-6 py-4 border-b border-admin-theme-border flex items-center justify-between"
 >
 <h3
 v-if="dialogStore.modal.title"
 class="text-lg font-medium text-admin-theme-text"
 >
 {{ dialogStore.modal.title }}
 </h3>
 <button
 v-if="dialogStore.modal.closable"
 @click="handleClose"
 class="ml-auto text-admin-theme-text-muted hover:text-admin-theme-text transition-colors"
 >
 <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path
 stroke-linecap="round"
 stroke-linejoin="round"
 stroke-width="2"
 d="M6 18L18 6M6 6l12 12"
 />
 </svg>
 </button>
 </div>

 <!-- Body -->
 <div class="p-6">
 <component
 v-if="dialogStore.modal.component"
 :is="dialogStore.modal.component"
 v-bind="dialogStore.modal.props"
 @close="handleClose"
 />
 <slot v-else />
 </div>
 </div>
 </div>
 </div>
 </Transition>
 </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useDialogStore } from '../../stores/dialog';
import { useLandingStore } from '../../stores/landingStore';
import { storeToRefs } from 'pinia';

const dialogStore = useDialogStore();
const landingStore = useLandingStore();
const { activeBlock, optionsWidth } = storeToRefs(landingStore);

// Detect if a landing block settings panel is active (pushed from nested editor)
const hasActiveLandingBlock = computed(() => !!activeBlock.value);

// Width of the landing panel for calculating modal offset
const landingPanelWidth = computed(() => optionsWidth.value || 300);

const sizeClass = computed(() => {
 // When landing panel is active, don't apply max-w constraints (handled by inline style)
 if (hasActiveLandingBlock.value) return '';
 
 const sizes: Record<string, string> = {
 sm: 'max-w-sm',
 md: 'max-w-md',
 lg: 'max-w-lg',
 xl: 'max-w-xl',
 wide: 'max-w-[80%] mx-auto',
 full: 'max-w-full mx-4',
 };
 return sizes[dialogStore.modal.size] || sizes.md;
});

const handleClose = () => {
 if (dialogStore.modal.closable) {
 dialogStore.closeModal();
 }
};
</script>
