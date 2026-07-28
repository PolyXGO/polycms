<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
                {{ t('Name') }}
            </label>
            <input 
                type="text" 
                v-model="form.name" 
                class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text" 
                :placeholder="t('e.g. Primary Marketing Button')"
            >
        </div>
        <div>
            <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
                {{ t('Description') }}
            </label>
            <textarea 
                v-model="form.description" 
                rows="2" 
                class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text"
            ></textarea>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-admin-theme-border mt-6">
            <button 
                type="button" 
                class="px-4 py-2 text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-base transition-colors"
                @click="emit('close')"
            >
                {{ t('Cancel') }}
            </button>
            <button 
                @click="submit" 
                :disabled="isSaving || !form.name" 
                type="button" 
                class="px-4 py-2 flex items-center text-sm font-medium text-admin-theme-primary-content bg-admin-theme-primary border border-transparent rounded-lg shadow-sm hover:bg-admin-theme-primary-hover disabled:opacity-50 transition-colors"
            >
                <svg v-if="isSaving" class="w-4 h-4 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ t('Save') }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import axios from 'axios';
import { useTranslation } from '@/admin/composables/useTranslation';
import { useDialogStore } from '@/admin/stores/dialog';

const props = defineProps<{
    type: string;
    modelValue: any;
    onSaved?: (preset: any) => void;
}>();

const emit = defineEmits<{
    close: [];
}>();

const { t } = useTranslation();
const dialogStore = useDialogStore();

const form = reactive({
    name: '',
    description: ''
});

const isSaving = ref(false);

const submit = async () => {
    if (!form.name || isSaving.value) return;
    
    isSaving.value = true;
    try {
        const response = await axios.post('/api/v1/presets', {
            type: props.type,
            name: form.name,
            description: form.description,
            value: props.modelValue
        });
        
        dialogStore.success(t('Preset saved successfully'));

        if (props.onSaved) {
            props.onSaved(response.data.data);
        }
        
        emit('close');
    } catch (error: any) {
        console.error('Failed to save preset:', error);
        dialogStore.error(error.response?.data?.message || t('Failed to save preset'));
    } finally {
        isSaving.value = false;
    }
};
</script>
