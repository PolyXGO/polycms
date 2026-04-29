<template>
  <div class="relative" v-click-outside="close">
    <div 
      class="w-full relative cursor-default overflow-hidden rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-left shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500"
    >
      <input
        type="text"
        class="w-full border-none py-2 pl-3 pr-10 text-sm leading-5 text-gray-900 dark:text-gray-100 bg-transparent focus:ring-0"
        :placeholder="placeholder || t('Search language...')"
        :value="query"
        @input="handleInput"
        @focus="isOpen = true"
      />
      <div class="absolute inset-y-0 right-0 flex items-center pr-2">
        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>

    <transition
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <ul
        v-if="isOpen && filteredLanguages.length > 0"
        class="absolute z-50 mt-1 max-height-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-800 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
        style="max-height: 250px;"
      >
        <li
          v-for="lang in filteredLanguages"
          :key="lang.code"
          @click="select(lang)"
          class="relative cursor-pointer select-none py-2 pl-3 pr-9 border-b border-gray-100 dark:border-gray-700 last:border-0 hover:bg-indigo-600 hover:text-white text-gray-900 dark:text-gray-100"
        >
          <div class="flex items-center">
            <span class="block truncate font-medium">{{ lang.name }}</span>
            <span class="ml-2 block truncate text-xs opacity-60">({{ lang.nativeName }}) - {{ lang.code }}</span>
          </div>
        </li>
      </ul>
      <div 
        v-else-if="isOpen && query" 
        class="absolute z-50 mt-1 w-full rounded-md bg-white dark:bg-gray-800 py-2 px-3 text-sm text-gray-500 shadow-lg"
      >
        {{ t('No results found') }}
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { allLanguages, type LanguagePreset } from '../../data/allLanguages';
import { useTranslation } from '../../composables/useTranslation';

interface Props {
  exclude?: string[];
  placeholder?: string;
}

const props = defineProps<Props>();
const emit = defineEmits(['select']);
const { t } = useTranslation();

const query = ref('');
const isOpen = ref(false);

const handleInput = (e: Event) => {
  query.value = (e.target as HTMLInputElement).value;
  isOpen.value = true;
};

const filteredLanguages = computed(() => {
  const q = query.value.toLowerCase().trim();
  const excludeList = props.exclude || [];
  
  if (!q) {
    return allLanguages
      .filter(lang => !excludeList.includes(lang.code))
      .slice(0, 100);
  }

  return allLanguages
    .filter(lang => {
      // Exclude if already in system
      if (excludeList.includes(lang.code)) return false;
      
      const name = lang.name.toLowerCase();
      const nativeName = lang.nativeName.toLowerCase();
      const code = lang.code.toLowerCase();
      const baseLang = lang.lang.toLowerCase();

      return name.includes(q) || 
             nativeName.includes(q) || 
             code.includes(q) ||
             baseLang === q;
    })
    .slice(0, 100);
});

const select = (lang: LanguagePreset) => {
  emit('select', lang);
  query.value = lang.name;
  isOpen.value = false;
};

const close = () => {
  isOpen.value = false;
};

// Simple click outside directive logic
const vClickOutside = {
  mounted(el: any, binding: any) {
    el._clickOutside = (event: Event) => {
      if (!(el === event.target || el.contains(event.target as Node))) {
        binding.value();
      }
    };
    document.addEventListener('click', el._clickOutside);
  },
  unmounted(el: any) {
    document.removeEventListener('click', el._clickOutside);
  },
};
</script>
