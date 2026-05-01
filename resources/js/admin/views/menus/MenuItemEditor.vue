<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="$emit('close')"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ t('Edit Menu Item') }}
                </h3>
            </div>

            <form @submit.prevent="save" class="p-6 space-y-4">
                <!-- Label -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ t('Navigation Label') }}
                    </label>
                    <input
                        v-model="formData.title"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>

                <!-- URL (for custom links) -->
                <div v-if="item.type === 'custom'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ t('URL') }}
                    </label>
                    <input
                        v-model="formData.url"
                        type="url"
                        required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t('URL') }}: <span class="font-mono">{{ item.url || t('Auto-generated') }}</span>
                </div>

                <!-- Target -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ t('Open link in') }}
                    </label>
                    <select
                        v-model="formData.target"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <option value="_self">{{ t('Same window') }}</option>
                        <option value="_blank">{{ t('New window') }}</option>
                    </select>
                </div>

                <!-- CSS Class -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ t('CSS Classes') }}
                    </label>
                    <input
                        v-model="formData.css_class"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :placeholder="t('optional')"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ t('Separate multiple classes with spaces') }}
                    </p>
                </div>

                <!-- Active -->
                <div>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            v-model="formData.active"
                            type="checkbox"
                            class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ t('Enable this menu item') }}
                        </span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
                    >
                        {{ t('Cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    >
                        <span v-if="saving" class="flex items-center">
                            <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            {{ t('Saving...') }}
                        </span>
                        <span v-else>{{ t('Save') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import { useTranslation } from '../../composables/useTranslation';

const props = defineProps<{
    show: boolean;
    item: any | null;
    menuId: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved', item: any): void;
}>();

const { t } = useTranslation();
const dialog = useDialog();

const saving = ref(false);
const formData = ref({
    title: '',
    url: '',
    target: '_self',
    css_class: '',
    active: true,
});

watch(() => props.item, (newItem) => {
    if (newItem) {
        formData.value = {
            title: newItem.title || '',
            url: newItem.url || '',
            target: newItem.target || '_self',
            css_class: newItem.css_class || '',
            active: newItem.active !== false,
        };
    }
}, { immediate: true });

const save = async () => {
    if (!props.item) return;

    saving.value = true;
    try {
        const response = await axios.put(
            `/api/v1/menus/${props.menuId}/items/${props.item.id}`,
            formData.value
        );

        emit('saved', response.data.data);
        dialog.success(t('Menu item updated successfully'));
        emit('close');
    } catch (error: any) {
        console.error('Error updating menu item:', error);
        const message = error.response?.data?.error?.message || t('Failed to update menu item');
        dialog.error(message);
    } finally {
        saving.value = false;
    }
};
</script>
