<template>
  <div class="flex flex-col h-[600px] bg-admin-theme-surface overflow-hidden rounded-b-2xl">
    <!-- Compact Search & Selection Header -->
    <div class="p-4 pb-0 flex-shrink-0 space-y-3">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative group flex-1">
          <MagnifyingGlassIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 text-admin-theme-text-muted w-4 h-4 group-focus-within:text-admin-theme-primary transition-colors" />
          <input 
            v-model="search" 
            type="text" 
            :placeholder="t('Search icons...')"
            class="w-full pl-11 pr-4 py-2.5 bg-admin-theme-base border-none rounded-xl text-xs focus:ring-2 focus:ring-admin-theme-primary transition-all font-semibold dark:placeholder-gray-500"
            autofocus
          >
        </div>
        
        <!-- Current Selection Preview -->
        <div v-if="currentIcon" class="flex items-center gap-2 p-1 px-3 bg-admin-theme-primary/10 rounded-xl border border-admin-theme-primary/20 shrink-0 select-none animate-in fade-in slide-in-from-top-1 duration-200">
          <span class="text-[10px] font-bold text-admin-theme-text-muted uppercase tracking-wider">{{ t('Selected') }}:</span>
          <div class="w-7 h-7 flex items-center justify-center bg-admin-theme-surface rounded-lg shadow-sm border border-admin-theme-border/40 text-admin-theme-primary">
            <component v-if="!isBrandIcon(currentIcon) && !isCustomIcon(currentIcon) && !currentIcon.startsWith('ki-')" :is="getHeroIcon(currentIcon)" class="w-4 h-4" />
            <i v-else-if="currentIcon.startsWith('ki-')" :class="['text-sm ki-outline', currentIcon]"></i>
            <span v-else-if="isBrandIcon(currentIcon)" class="w-4 h-4 flex items-center justify-center text-admin-theme-primary [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:overflow-hidden" v-html="getBrandSvg(currentIcon)"></span>
            <span v-else-if="isCustomIcon(currentIcon)" class="w-4 h-4 flex items-center justify-center text-admin-theme-primary [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:overflow-hidden" v-html="getCustomIconSvg(currentIcon)"></span>
          </div>
          <span class="text-xs font-bold text-admin-theme-text max-w-[120px] truncate">
            {{ typeof currentIcon === 'string' && isBrandIcon(currentIcon) ? currentIcon : formatIconName(currentIcon) }}
          </span>
        </div>
      </div>

      <!-- Tabs and Label -->
      <div class="flex items-center justify-between pb-1">
        <div class="flex items-center gap-4">
          <button 
            @click="activeTab = 'heroicons'" 
            class="pb-1.5 text-xs font-black uppercase tracking-widest border-b-2 transition-all cursor-pointer"
            :class="activeTab === 'heroicons' ? 'border-admin-theme-primary text-admin-theme-primary' : 'border-transparent text-admin-theme-text-muted hover:text-admin-theme-text-secondary'"
          >
            General
          </button>
          <button 
            @click="activeTab = 'brands'" 
            class="pb-1.5 text-xs font-black uppercase tracking-widest border-b-2 transition-all cursor-pointer"
            :class="activeTab === 'brands' ? 'border-admin-theme-primary text-admin-theme-primary' : 'border-transparent text-admin-theme-text-muted hover:text-admin-theme-text-secondary'"
          >
            Brands & Socials
          </button>
          <button 
            @click="activeTab = 'custom'" 
            class="pb-1.5 text-xs font-black uppercase tracking-widest border-b-2 transition-all cursor-pointer"
            :class="activeTab === 'custom' ? 'border-admin-theme-primary text-admin-theme-primary' : 'border-transparent text-admin-theme-text-muted hover:text-admin-theme-text-secondary'"
          >
            Custom Icons
          </button>
        </div>
        <span class="text-[10px] font-bold text-admin-theme-text-muted uppercase tracking-widest">
          {{ filteredIcons.length }} {{ t('Available') }}
        </span>
      </div>
    </div>

    <!-- Icons Grid -->
    <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
      <div v-if="filteredIcons.length > 0" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-4">
        <button 
          v-for="icon in filteredIcons" 
          :key="typeof icon === 'string' ? icon : icon.name"
          @click="selectIcon(typeof icon === 'string' ? icon : icon.name)"
          class="group flex flex-col items-center p-3 rounded-2xl border border-transparent hover:border-indigo-100 dark:hover:border-indigo-900/30 hover:bg-admin-theme-primary/10/50 dark:hover:bg-indigo-900/10 transition-all"
          :class="{'bg-admin-theme-primary/10 border-indigo-200 dark:border-indigo-800': currentIcon === (typeof icon === 'string' ? icon : icon.name) }"
        >
          <div class="w-10 h-10 flex items-center justify-center mb-1 text-admin-theme-text-secondary group-hover:text-admin-theme-primary dark:group-hover:text-admin-theme-primary transition-colors">
            <component 
              v-if="typeof icon === 'string' && !isBrandIcon(icon) && !icon.startsWith('ki-')"
              :is="getHeroIcon(icon)" 
              class="w-6 h-6" 
            />
            <i v-else-if="typeof icon === 'string' && icon.startsWith('ki-')" :class="['text-xl ki-outline', icon]"></i>
            <span v-else-if="typeof icon === 'string' && isBrandIcon(icon)" class="w-6 h-6 flex items-center justify-center [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:overflow-hidden" v-html="getBrandSvg(icon)"></span>
            <span v-else class="w-6 h-6 flex items-center justify-center [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:overflow-hidden" v-html="icon.svg_code"></span>
          </div>
          <span class="text-[9px] font-medium text-admin-theme-text-muted truncate w-full text-center group-hover:text-admin-theme-primary transition-colors">
            {{ typeof icon === 'string' ? formatIconName(icon) : icon.name }}
          </span>
        </button>
      </div>
      
      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-20 opacity-30 dark:opacity-20">
        <i class="ki-outline ki-search-list text-5xl mb-4"></i>
        <p class="text-sm font-bold uppercase tracking-widest">{{ t('No icons found') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useTranslation } from '@admin/composables/useTranslation';
import * as HeroIcons from '@heroicons/vue/24/outline';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { HERO_ICONS } from '@admin/utils/icon-list';
import { BRAND_SVGS } from '@admin/utils/brand-svgs';
import axios from 'axios';

interface Props {
 currentIcon?: string;
 onSelect?: (icon: string) => void;
 onClose?: () => void;
}

const props = defineProps<Props>();
const { t } = useTranslation();
const search = ref('');
const activeTab = ref('heroicons');

const BRAND_ICONS = [
  'facebook',
  'youtube',
  'github',
  'twitter',
  'instagram',
  'linkedin',
  'tiktok',
  'pinterest',
  'whatsapp',
  'telegram',
  'dribbble',
  'behance',
  'envato'
];

const customIcons = ref<any[]>([]);

const loadCustomIcons = async () => {
  try {
    const response = await axios.get('/api/v1/custom-icons');
    if (response.data?.success) {
      customIcons.value = response.data.data || [];
    }
  } catch (e) {
    // custom-icons CRUD might not be created yet, fail silently
  }
};

onMounted(async () => {
  await loadCustomIcons();
});

const isBrandIcon = (name: string) => {
  return BRAND_ICONS.includes(name);
};

const getBrandSvg = (name: string) => {
  return BRAND_SVGS[name] || '';
};

const isCustomIcon = (name: string) => {
  return customIcons.value.some(icon => icon.name === name);
};

const getCustomIconSvg = (name: string) => {
  const icon = customIcons.value.find(icon => icon.name === name);
  return icon ? icon.svg_code : '';
};

const isFaIcon = (name: string) => {
  return name.startsWith('fa-') || name.startsWith('fab ');
};

const filteredIcons = computed(() => {
  let list: any[] = [];
  if (activeTab.value === 'heroicons') {
    list = HERO_ICONS;
  } else if (activeTab.value === 'brands') {
    list = BRAND_ICONS;
  } else if (activeTab.value === 'custom') {
    list = customIcons.value;
  }
  
  if (!search.value) return list;
  
  const query = search.value.toLowerCase();
  return list.filter(icon => {
    const name = typeof icon === 'string' ? icon : icon.name;
    return name.toLowerCase().includes(query);
  });
});

const getHeroIcon = (name: string) => {
 return (HeroIcons as any)[name];
};

const formatIconName = (name: string) => {
 return name
 .replace('Icon','')
 .replace('ki-','')
 .split('-')
 .map(word => word.charAt(0).toUpperCase() + word.slice(1))
 .join('');
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
