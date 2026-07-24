<template>
 <div class="space-y-8">
 <div>
 <h3 class="text-lg font-medium leading-6 text-admin-theme-text mb-4">{{ $t('Admin Dashboard Theme') }}</h3>
 <p class="text-sm text-admin-theme-text-muted mb-6">{{ $t('Select a theme for your admin control panel. This will immediately change the color scheme and styling of the entire admin area.') }}</p>

 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 <!-- Theme: Nebula -->
 <div 
 class="relative rounded-lg border-2 p-4 cursor-pointer transition-all duration-200"
 :class="[
 themeValue ==='nebula' 
 ?'border-admin-theme-primary bg-admin-theme-primary/10 shadow-md ring-1 ring-admin-theme-primary' 
 :'border-admin-theme-border hover:border-gray-300 hover:border-admin-theme-primary/40 bg-admin-theme-surface'
 ]"
 @click="setTheme('nebula')"
 >
 <div class="flex justify-between items-start mb-4">
 <div>
 <h4 class="text-base font-semibold text-admin-theme-text">Nebula</h4>
 <p class="text-xs text-gray-500 mt-1">{{ $t('The classic PolyCMS interface with vibrant colors and soft shadows.') }}</p>
 </div>
 <div v-if="themeValue ==='nebula'" class="flex-shrink-0 text-admin-theme-primary">
 <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
 </svg>
 </div>
 </div>
 <!-- Mini Preview -->
 <div class="rounded overflow-hidden border border-admin-theme-border bg-gray-100 h-24 flex shadow-inner">
 <div class="w-1/4 bg-gray-800 border-r border-gray-700"></div>
 <div class="flex-1 flex flex-col">
 <div class="h-6 bg-white border-b border-gray-200"></div>
 <div class="flex-1 p-2">
 <div class="h-2 w-1/2 bg-admin-theme-primary rounded mb-2"></div>
 <div class="h-8 bg-white rounded shadow-sm border border-gray-100"></div>
 </div>
 </div>
 </div>
 </div>

 <!-- Theme: Black White -->
 <div 
 class="relative rounded-lg border-2 p-4 cursor-pointer transition-all duration-200"
 :class="[
 themeValue ==='black-white' 
 ?'border-admin-theme-primary bg-gray-50/50 dark:bg-black shadow-md ring-1 ring-admin-theme-primary' 
 :'border-admin-theme-border hover:border-gray-300 hover:border-admin-theme-primary/40 bg-admin-theme-surface'
 ]"
 @click="setTheme('black-white')"
 >
 <div class="flex justify-between items-start mb-4">
 <div>
 <h4 class="text-base font-semibold text-admin-theme-text">Black White</h4>
 <p class="text-xs text-gray-500 mt-1">{{ $t('A minimalist, high-contrast theme featuring a pure black and white aesthetic.') }}</p>
 </div>
 <div v-if="themeValue ==='black-white'" class="flex-shrink-0 text-admin-theme-primary">
 <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
 </svg>
 </div>
 </div>
 <!-- Mini Preview -->
 <div class="rounded overflow-hidden border border-gray-300 dark:border-zinc-700 bg-[#fafafa] h-24 flex shadow-inner">
 <div class="w-1/4 bg-black border-r border-zinc-800"></div>
 <div class="flex-1 flex flex-col">
 <div class="h-6 bg-white border-b border-zinc-200"></div>
 <div class="flex-1 p-2">
 <div class="h-2 w-1/2 bg-black rounded mb-2"></div>
 <div class="h-8 bg-white rounded border border-zinc-200"></div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { computed } from'vue';
import { useTranslation } from'../../../composables/useTranslation';

const props = defineProps<{
 settings: Record<string, any>;
 group: string;
}>();

const emit = defineEmits(['update']);
const { t } = useTranslation();
const $t = t;

// Create setting object if it doesn't exist
if (!props.settings.admin_theme) {
 emit('update', props.group,'admin_theme','nebula');
}

const themeValue = computed({
 get: () => props.settings?.admin_theme?.value ||'nebula',
 set: (val) => {
 emit('update', props.group,'admin_theme', val);
 // Preview theme live by changing HTML attribute
 document.documentElement.setAttribute('data-admin-theme', val);
 }
});

const setTheme = (theme: string) => {
 themeValue.value = theme;
};
</script>
