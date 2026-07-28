<template>
  <div class="relative group">
  <div 
  @click="openPicker"
  class="flex items-center gap-2 px-3 py-1.5 bg-admin-theme-input-bg border border-admin-theme-border rounded-lg cursor-pointer hover:border-admin-theme-primary transition-all hover:bg-black/5 dark:hover:bg-white/5"
  >
  <div class="w-6 h-6 flex items-center justify-center bg-admin-theme-base rounded-lg text-admin-theme-text-secondary flex-shrink-0">
  <component 
    v-if="isHeroIcon" 
    :is="heroIconComponent" 
    class="w-4 h-4 text-admin-theme-primary" 
  />
  <span v-else-if="modelValue && BRAND_SVGS[modelValue]" class="w-4 h-4 flex items-center justify-center text-admin-theme-primary [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:overflow-hidden" v-html="BRAND_SVGS[modelValue]"></span>
  <span v-else-if="modelValue && customIcons.find(icon => icon.name === modelValue)" class="w-4 h-4 flex items-center justify-center text-admin-theme-primary [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:overflow-hidden" v-html="customIcons.find(icon => icon.name === modelValue).svg_code"></span>
  <i v-else-if="modelValue" :class="['ki-outline text-lg text-admin-theme-primary', modelValue]"></i>
  <i v-else class="ki-outline ki-plus text-sm opacity-20"></i>
  </div>
  <div class="flex-1 min-w-0 text-xs font-semibold text-admin-theme-text-secondary truncate">
  {{ displayValue || label || t('Select icon') }}
  </div>
  <div class="flex items-center gap-1.5 flex-shrink-0">
    <button
      v-if="modelValue"
      type="button"
      @click.stop="clearIcon"
      class="p-1 rounded-md bg-red-500/10 hover:bg-red-500/20 text-red-500 hover:text-red-600 transition-all border border-red-500/20 flex items-center justify-center cursor-pointer"
      title="Clear icon"
    >
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-admin-theme-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
  </div>
  </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, defineAsyncComponent } from 'vue';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';
import * as HeroIconsOutline from '@heroicons/vue/24/outline';
import IconModal from '../dialogs/IconModal.vue';
import { BRAND_SVGS } from '../../utils/brand-svgs';
import axios from 'axios';

const { t } = useTranslation();
const dialog = useDialog();

const customIcons = ref<any[]>([]);

const loadCustomIcons = async () => {
  try {
    const response = await axios.get('/api/v1/custom-icons');
    if (response.data?.success) {
      customIcons.value = response.data.data || [];
    }
  } catch (e) {
    // fail silently
  }
};

onMounted(async () => {
  await loadCustomIcons();
});

const props = defineProps<{
 modelValue: string;
 label?: string;
 name?: string;
}>();

const emit = defineEmits<{
 (e:'update:modelValue', value: string): void;
}>();

const clearIcon = () => {
  emit('update:modelValue', '');
};

const isHeroIcon = computed(() => {
 return props.modelValue && props.modelValue.endsWith('Icon');
});

const heroIconComponent = computed(() => {
 if (!isHeroIcon.value) return null;
 return (HeroIconsOutline as any)[props.modelValue];
});

const displayValue = computed(() => {
 if (!props.modelValue) return'';
 return props.modelValue
 .replace('Icon','')
 .replace('ki-','')
 .split('-')
 .map(word => word.charAt(0).toUpperCase() + word.slice(1))
 .join('');
});

const openPicker = () => {
 dialog.showModal({
 title: t('Select Icon'),
 size:'wide',
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
