<template>
    <div class="preset-manager bg-admin-theme-base p-3 rounded-md border border-admin-theme-border mb-4">
        <div class="flex items-center justify-between w-full">
            <div class="text-xs font-medium text-admin-theme-text flex items-center">
                <BookmarkIcon class="w-4 h-4 mr-1 text-admin-theme-primary" /> Presets
            </div>
            <div class="flex items-center space-x-3">
                <button 
                    type="button" 
                    @click="openLoadModal"
                    class="text-[10px] font-bold text-admin-theme-primary hover:text-admin-theme-primary-hover uppercase tracking-wider px-2 py-1 bg-admin-theme-primary/10 rounded transition-colors"
                >
                    Load
                </button>
                <button 
                    type="button" 
                    @click="openSaveModal"
                    class="text-[10px] font-bold text-green-500 hover:text-green-400 uppercase tracking-wider px-2 py-1 bg-green-500/10 rounded transition-colors"
                >
                    Save
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { BookmarkIcon } from '@heroicons/vue/24/outline';
import { useDialogStore } from '@/admin/stores/dialog';
import PresetLoadModal from './PresetLoadModal.vue';
import PresetSaveModal from './PresetSaveModal.vue';
import { useTranslation } from '@/admin/composables/useTranslation';

const props = defineProps<{
    type: string;
    modelValue: any;
}>();

const emit = defineEmits(['update:modelValue']);

const dialogStore = useDialogStore();
const { t } = useTranslation();

const openLoadModal = () => {
    dialogStore.showModal({
        title: t('Load Preset'),
        size: 'xl',
        closable: true,
        component: PresetLoadModal,
        props: {
            type: props.type,
            onLoad: (preset: any) => {
                let parsedPayload = preset.payload || preset.value;
                if (typeof parsedPayload === 'string') {
                    try {
                        parsedPayload = JSON.parse(parsedPayload);
                    } catch (e) {
                        console.error('Failed to parse preset payload:', e);
                    }
                }
                const newModelValue = { ...props.modelValue, ...parsedPayload };
                emit('update:modelValue', newModelValue);
                dialogStore.success(t('Preset loaded successfully'));
            }
        }
    });
};

const openSaveModal = () => {
    // Copy payload and strip personal settings
    const payload = { ...props.modelValue };
    if (props.type === 'button_style') {
        delete payload.label;
        delete payload.url;
        delete payload.target;
        delete payload.rel;
    }

    dialogStore.showModal({
        title: t('Save as Preset'),
        size: 'md',
        closable: true,
        component: PresetSaveModal,
        props: {
            type: props.type,
            modelValue: payload,
            onSaved: () => {
                // optional: emit event if needed
            }
        }
    });
};
</script>
