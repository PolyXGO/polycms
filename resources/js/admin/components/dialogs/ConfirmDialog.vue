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
                v-if="dialogStore.confirm.show"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="dialogStore.closeConfirm(false)"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 dark:bg-black/70"></div>

                <!-- Dialog -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full max-w-md transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl transition-all"
                        @click.stop
                    >
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <!-- Icon -->
                                <div
                                    :class="[
                                        'flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full',
                                        iconBgClass
                                    ]"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        :class="iconClass"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            :d="iconPath"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3
                                        v-if="dialogStore.confirm.title"
                                        class="text-lg font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ dialogStore.confirm.title }}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">
                                {{ dialogStore.confirm.message }}
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                            <button
                                ref="cancelButtonRef"
                                @click="dialogStore.closeConfirm(false)"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition-colors"
                            >
                                {{ dialogStore.confirm.cancelText }}
                            </button>
                            <button
                                ref="confirmButtonRef"
                                @click="dialogStore.closeConfirm(true)"
                                :class="[
                                    'px-4 py-2 rounded-lg font-medium transition-colors',
                                    buttonClass
                                ]"
                            >
                                {{ dialogStore.confirm.confirmText }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { useDialogStore } from '../../stores/dialog';

const dialogStore = useDialogStore();
const confirmButtonRef = ref<HTMLButtonElement | null>(null);
const cancelButtonRef = ref<HTMLButtonElement | null>(null);

// Handle keyboard events
const handleKeydown = (event: KeyboardEvent) => {
    if (!dialogStore.confirm.show) return;

    // Enter key - confirm
    if (event.key === 'Enter') {
        event.preventDefault();
        event.stopPropagation();
        dialogStore.closeConfirm(true);
        return;
    }

    // Escape key - cancel
    if (event.key === 'Escape') {
        event.preventDefault();
        event.stopPropagation();
        dialogStore.closeConfirm(false);
        return;
    }
};

// Auto-focus confirm button when dialog opens
watch(() => dialogStore.confirm.show, (isOpen) => {
    if (isOpen) {
        // Use nextTick to ensure DOM is updated
        setTimeout(() => {
            confirmButtonRef.value?.focus();
        }, 100);
    }
});

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});

const iconClass = computed(() => {
    const types: Record<string, string> = {
        warning: 'text-yellow-600 dark:text-yellow-400',
        danger: 'text-red-600 dark:text-red-400',
        info: 'text-blue-600 dark:text-blue-400',
    };
    return types[dialogStore.confirm.type] || types.info;
});

const iconBgClass = computed(() => {
    const types: Record<string, string> = {
        warning: 'bg-yellow-100 dark:bg-yellow-900/20',
        danger: 'bg-red-100 dark:bg-red-900/20',
        info: 'bg-blue-100 dark:bg-blue-900/20',
    };
    return types[dialogStore.confirm.type] || types.info;
});

const iconPath = computed(() => {
    const types: Record<string, string> = {
        warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        danger: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };
    return types[dialogStore.confirm.type] || types.info;
});

const buttonClass = computed(() => {
    const types: Record<string, string> = {
        warning: 'bg-yellow-600 hover:bg-yellow-700 text-white',
        danger: 'bg-red-600 hover:bg-red-700 text-white',
        info: 'bg-blue-600 hover:bg-blue-700 text-white',
    };
    return types[dialogStore.confirm.type] || 'bg-indigo-600 hover:bg-indigo-700 text-white';
});
</script>

