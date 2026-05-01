<template>
    <button type="button" @click.prevent="openModal" class="inline-flex items-center justify-center text-gray-400 hover:text-indigo-500 transition-colors focus:outline-none" :class="iconClass" :title="tooltip || t('Help')">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
        </svg>
    </button>

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
                v-if="isOpen"
                class="fixed inset-0 z-[999999] overflow-y-auto"
                @click.self="closeModal"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 dark:bg-black/70"></div>

                <!-- Modal -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl transition-all max-w-md"
                        @click.stop
                    >
                        <!-- Header -->
                        <div
                            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between"
                        >
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ title }}
                            </h3>
                            <button
                                @click="closeModal"
                                class="ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
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
                        <div class="p-6 text-gray-700 dark:text-gray-300 prose dark:prose-invert max-w-none text-sm">
                            <slot>
                                <div v-if="description" v-html="description"></div>
                            </slot>
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
