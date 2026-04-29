<template>
    <Modal :show="show" :center="true" maxWidth="md" @close="close">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3 shrink-0">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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

            <div class="mt-6 flex justify-end">
                <button
                    @click="close"
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm font-bold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    {{ buttonText }}
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
        default: 'Notification'
    },
    message: String,
    buttonText: {
        type: String,
        default: 'OK'
    }
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};
</script>
