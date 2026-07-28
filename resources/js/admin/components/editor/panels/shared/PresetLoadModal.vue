<template>
    <div class="space-y-4">
        <div v-if="isLoading" class="text-center py-8 flex justify-center">
            <svg class="w-8 h-8 animate-spin text-admin-theme-primary" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
        </div>
        <div v-else-if="presets.length === 0" class="text-center py-8 text-admin-theme-text-secondary text-sm">
            {{ t('No presets found for this type.') }}
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[70vh] overflow-y-auto pr-2">
            <div 
                v-for="preset in presets" 
                :key="preset.id"
                @click="selectPreset(preset)"
                class="border border-admin-theme-border rounded-lg hover:border-admin-theme-primary hover:bg-admin-theme-primary/5 cursor-pointer transition-colors flex flex-col h-full bg-admin-theme-input-bg overflow-hidden shadow-sm hover:shadow"
            >
                <!-- Header -->
                <div class="p-3 border-b border-admin-theme-border bg-admin-theme-base">
                    <div class="font-medium text-sm text-admin-theme-text">{{ preset.name }}</div>
                    <div v-if="preset.description" class="text-[11px] text-admin-theme-text-muted mt-1 line-clamp-2" :title="preset.description">{{ preset.description }}</div>
                </div>
                
                <!-- Preview Area -->
                <div class="flex-1 p-4 flex items-center justify-center min-h-[100px] relative overflow-hidden" :class="{'bg-[#f8f9fa] dark:bg-[#111827]': type === 'button_style'}">
                    <!-- Background pattern for transparency/better contrast check -->
                    <div class="absolute inset-0 opacity-20 dark:opacity-[0.05]" style="background-image: radial-gradient(circle, #000 1px, transparent 1px); background-size: 16px 16px;" v-if="type === 'button_style'"></div>
                    
                    <div class="pointer-events-none relative z-10 w-full flex justify-center">
                        <ButtonBlock v-if="type === 'button_style'" :data="parsePresetData(preset)" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '@/admin/composables/useTranslation';
import ButtonBlock from '@/admin/components/editor/blocks/atomic/ButtonBlock.vue';

const props = defineProps<{
    type: string;
    onLoad?: (preset: any) => void;
}>();

const emit = defineEmits<{
    close: [];
}>();

const { t } = useTranslation();

const presets = ref<any[]>([]);
const isLoading = ref(true);

const fetchPresets = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/v1/presets', {
            params: { type: props.type }
        });
        presets.value = response.data.data || [];
    } catch (e) {
        console.error('Failed to load presets', e);
    } finally {
        isLoading.value = false;
    }
};

const selectPreset = (preset: any) => {
    if (props.onLoad) {
        props.onLoad(preset);
    }
    emit('close');
};

const parsePresetData = (preset: any) => {
    let parsed = preset.payload || preset.value;
    if (typeof parsed === 'string') {
        try {
            parsed = JSON.parse(parsed);
        } catch (e) {
            parsed = {};
        }
    }
    return {
        label: 'Preview',
        alignment: 'center', // override alignment for center preview
        ...parsed
    };
};

onMounted(() => {
    fetchPresets();
});
</script>
