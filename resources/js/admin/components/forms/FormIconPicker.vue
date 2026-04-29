<template>
    <div class="relative group">
        <div 
            @click="openPicker"
            class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl cursor-pointer hover:border-indigo-500 transition-all hover:shadow-lg hover:shadow-indigo-500/5"
        >
            <div class="w-8 h-8 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-xl text-gray-600 dark:text-gray-400">
                <component 
                    v-if="isHeroIcon" 
                    :is="heroIconComponent" 
                    class="w-5 h-5 text-indigo-500" 
                />
                <i v-else-if="modelValue" :class="['ki-outline text-xl text-indigo-500', modelValue]"></i>
                <i v-else class="ki-outline ki-plus text-lg opacity-20"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-indigo-400 transition-colors">
                    {{ label || t('Icon') }}
                </div>
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                    {{ displayValue || t('Select icon') }}
                </div>
            </div>
            <i class="ki-outline ki-down text-gray-400 group-hover:text-indigo-500 transition-colors"></i>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';
import * as HeroIconsOutline from '@heroicons/vue/24/outline';
import IconModal from '../dialogs/IconModal.vue';

const { t } = useTranslation();
const dialog = useDialog();

const props = defineProps<{
    modelValue: string;
    label?: string;
    name?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const isHeroIcon = computed(() => {
    return props.modelValue && props.modelValue.endsWith('Icon');
});

const heroIconComponent = computed(() => {
    if (!isHeroIcon.value) return null;
    return (HeroIconsOutline as any)[props.modelValue];
});

const displayValue = computed(() => {
    if (!props.modelValue) return '';
    return props.modelValue
        .replace('Icon', '')
        .replace('ki-', '')
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
});

const openPicker = () => {
    dialog.showModal({
        title: t('Select Icon'),
        size: 'wide',
        component: IconModal,
        props: {
            currentIcon: props.modelValue,
            onSelect: (icon: string) => {
                emit('update:modelValue', icon);
            }
        }
    });
};
</script>
