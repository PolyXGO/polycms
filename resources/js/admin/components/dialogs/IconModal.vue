<template>
    <div class="flex flex-col h-[600px] bg-white dark:bg-gray-900 overflow-hidden rounded-b-2xl">
        <!-- Search Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
            <div class="relative group">
                <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 w-5 h-5 group-focus-within:text-indigo-500 transition-colors" />
                <input 
                    v-model="search" 
                    type="text" 
                    :placeholder="t('Search icons...')"
                    class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all font-medium dark:text-white dark:placeholder-gray-500"
                    autofocus
                >
            </div>
            
            <!-- Current Selection Preview -->
            <div v-if="currentIcon" class="mt-6 p-4 bg-indigo-50/50 dark:bg-indigo-900/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-800/20 flex items-center gap-4 transition-all animate-in fade-in slide-in-from-top-2 duration-300">
                <div class="w-12 h-12 flex items-center justify-center bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-indigo-100 dark:border-indigo-900/50 text-indigo-600 dark:text-indigo-400">
                    <component :is="getHeroIcon(currentIcon)" class="w-7 h-7" />
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-0.5">
                        {{ t('Current Selection') }}
                    </span>
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200 truncate">
                        {{ formatIconName(currentIcon) }}
                    </span>
                </div>
                <div class="ml-auto">
                    <div class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ t('Active') }}
                    </div>
                </div>
            </div>

            <!-- Tabs and Label -->
            <div class="flex items-center justify-between mt-6">
                <div class="flex items-center gap-2">
                    <span class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-black uppercase tracking-widest">
                        Heroicons
                    </span>
                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-2">
                        {{ HERO_ICONS.length }} {{ t('Icons Available') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Icons Grid -->
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div v-if="filteredIcons.length > 0" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-4">
                <button 
                    v-for="icon in filteredIcons" 
                    :key="icon"
                    @click="selectIcon(icon)"
                    class="group flex flex-col items-center p-3 rounded-2xl border border-transparent hover:border-indigo-100 dark:hover:border-indigo-900/30 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 transition-all"
                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800': currentIcon === icon }"
                >
                    <div class="w-10 h-10 flex items-center justify-center mb-1 text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                        <component 
                            :is="getHeroIcon(icon)" 
                            class="w-6 h-6" 
                        />
                    </div>
                    <span class="text-[9px] font-medium text-gray-500 dark:text-gray-400 truncate w-full text-center group-hover:text-indigo-500 transition-colors">
                        {{ formatIconName(icon) }}
                    </span>
                </button>
            </div>
            
            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-20 opacity-30 dark:opacity-20">
                <i class="ki-outline ki-search-list text-5xl mb-4"></i>
                <p class="text-sm font-bold uppercase tracking-widest">{{ t('No icons found') }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-end bg-gray-50/50 dark:bg-gray-800/30">
            <button 
                @click="props.onClose?.()"
                class="px-6 py-2 text-xs font-black uppercase tracking-widest text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
            >
                {{ t('Cancel') }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useTranslation } from '@admin/composables/useTranslation';
import * as HeroIcons from '@heroicons/vue/24/outline';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { HERO_ICONS } from '@admin/utils/icon-list';

interface Props {
    currentIcon?: string;
    onSelect?: (icon: string) => void;
    onClose?: () => void;
}

const props = defineProps<Props>();
const { t } = useTranslation();
const search = ref('');
const activeTab = ref('heroicons');

const filteredIcons = computed(() => {
    const list = HERO_ICONS;
    if (!search.value) return list;
    
    const query = search.value.toLowerCase();
    return list.filter(icon => icon.toLowerCase().includes(query));
});

const getHeroIcon = (name: string) => {
    return (HeroIcons as any)[name];
};

const formatIconName = (name: string) => {
    return name
        .replace('Icon', '')
        .replace('ki-', '')
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const selectIcon = (icon: string) => {
    if (props.onSelect) {
        props.onSelect(icon);
    }
    props.onClose?.();
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #374151;
}
</style>
