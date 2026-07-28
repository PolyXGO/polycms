<template>
    <Modal :show="show" :center="true" maxWidth="md" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                {{ title }}
            </h2>

            <div class="mt-4">
                <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ label }}
                </label>
                <input
                    ref="inputRef"
                    v-model="inputValue"
                    type="text"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white"
                    :placeholder="placeholder"
                    @keyup.enter="submit"
                />
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button
                    @click="close"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors text-sm font-bold"
                >
                    Cancel
                </button>
                <button
                    @click="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm font-bold shadow-sm"
                >
                    Confirm
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import Modal from './Modal.vue';

const props = defineProps({
    show: Boolean,
    title: String,
    label: String,
    initialValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['close', 'submit']);

const inputValue = ref(props.initialValue);
const inputRef = ref(null);

watch(() => props.show, (newVal) => {
    if (newVal) {
        inputValue.value = props.initialValue;
        nextTick(() => {
            inputRef.value?.focus();
            inputRef.value?.select();
        });
    }
});

const close = () => {
    emit('close');
};

const submit = () => {
    emit('submit', inputValue.value);
};
</script>
