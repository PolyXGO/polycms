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
                v-if="dialogStore.alert.show"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="dialogStore.closeAlert()"
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
                                        v-if="dialogStore.alert.title"
                                        class="text-lg font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ dialogStore.alert.title }}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">
                                {{ dialogStore.alert.message }}
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end">
                            <button
                                @click="dialogStore.closeAlert()"
                                :class="[
                                    'px-4 py-2 rounded-lg font-medium transition-colors',
                                    buttonClass
                                ]"
                            >
                                {{ dialogStore.alert.confirmText }}
                            </button>
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

const dialogStore = useDialogStore();

const iconClass = computed(() => {
    const types: Record<string, string> = {
        success: 'text-green-600 dark:text-green-400',
        error: 'text-red-600 dark:text-red-400',
        warning: 'text-yellow-600 dark:text-yellow-400',
        info: 'text-blue-600 dark:text-blue-400',
    };
    return types[dialogStore.alert.type] || types.info;
});

const iconBgClass = computed(() => {
    const types: Record<string, string> = {
        success: 'bg-green-100 dark:bg-green-900/20',
        error: 'bg-red-100 dark:bg-red-900/20',
        warning: 'bg-yellow-100 dark:bg-yellow-900/20',
        info: 'bg-blue-100 dark:bg-blue-900/20',
    };
    return types[dialogStore.alert.type] || types.info;
});

const iconPath = computed(() => {
    const types: Record<string, string> = {
        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };
    return types[dialogStore.alert.type] || types.info;
});

const buttonClass = computed(() => {
    const types: Record<string, string> = {
        success: 'bg-green-600 hover:bg-green-700 text-white',
        error: 'bg-red-600 hover:bg-red-700 text-white',
        warning: 'bg-yellow-600 hover:bg-yellow-700 text-white',
        info: 'bg-blue-600 hover:bg-blue-700 text-white',
    };
    return types[dialogStore.alert.type] || 'bg-indigo-600 hover:bg-indigo-700 text-white';
});
</script>

