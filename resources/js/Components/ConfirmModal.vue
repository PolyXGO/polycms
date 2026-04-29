<template>
    <Modal :show="show" :center="true" maxWidth="md" @close="close">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mr-3 shrink-0">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ title }}
                </h2>
            </div>

            <div class="mt-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ message }}
                </p>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button
                    @click="close"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors text-sm font-bold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                >
                    {{ cancelText }}
                </button>
                <button
                    @click="confirm"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors text-sm font-bold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from './Modal.vue';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: 'Confirm'
    },
    message: String,
    confirmText: {
        type: String,
        default: 'Confirm'
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    }
});

const emit = defineEmits(['close', 'confirm']);

const close = () => {
    emit('close');
};

const confirm = () => {
    emit('confirm');
};
</script>
