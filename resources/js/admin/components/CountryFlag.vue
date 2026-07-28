<template>
  <span 
    class="country-flag-container inline-flex items-center justify-center flex-shrink-0"
    :class="customClass"
    :style="containerStyle"
  >
    <!-- EN / US Flag -->
    <svg 
      v-if="normalizedCode === 'en' || normalizedCode === 'us' || normalizedCode === 'gb'"
      class="flag-svg flag-en" 
      xmlns="http://www.w3.org/2000/svg" 
      viewBox="0 0 36 36" 
      :style="flagStyle"
    >
      <clipPath :id="`us-clip-${uniqId}`"><circle cx="18" cy="18" r="18"/></clipPath>
      <g :clip-path="`url(#us-clip-${uniqId})`">
        <rect width="36" height="36" fill="#B22234"/>
        <rect y="2.77" width="36" height="2.77" fill="#FFF"/>
        <rect y="8.31" width="36" height="2.77" fill="#FFF"/>
        <rect y="13.85" width="36" height="2.77" fill="#FFF"/>
        <rect y="19.38" width="36" height="2.77" fill="#FFF"/>
        <rect y="24.92" width="36" height="2.77" fill="#FFF"/>
        <rect y="30.46" width="36" height="2.77" fill="#FFF"/>
        <rect width="18" height="19.38" fill="#3C3B6E"/>
        <g fill="#FFF">
          <circle cx="3" cy="3" r="0.6"/><circle cx="6" cy="3" r="0.6"/><circle cx="9" cy="3" r="0.6"/><circle cx="12" cy="3" r="0.6"/><circle cx="15" cy="3" r="0.6"/>
          <circle cx="4.5" cy="6.5" r="0.6"/><circle cx="7.5" cy="6.5" r="0.6"/><circle cx="10.5" cy="6.5" r="0.6"/><circle cx="13.5" cy="6.5" r="0.6"/>
          <circle cx="3" cy="10" r="0.6"/><circle cx="6" cy="10" r="0.6"/><circle cx="9" cy="10" r="0.6"/><circle cx="12" cy="10" r="0.6"/><circle cx="15" cy="10" r="0.6"/>
          <circle cx="4.5" cy="13.5" r="0.6"/><circle cx="7.5" cy="13.5" r="0.6"/><circle cx="10.5" cy="13.5" r="0.6"/><circle cx="13.5" cy="13.5" r="0.6"/>
          <circle cx="3" cy="17" r="0.6"/><circle cx="6" cy="17" r="0.6"/><circle cx="9" cy="17" r="0.6"/><circle cx="12" cy="17" r="0.6"/><circle cx="15" cy="17" r="0.6"/>
        </g>
      </g>
    </svg>

    <!-- VI / VN Flag -->
    <svg 
      v-else-if="normalizedCode === 'vi' || normalizedCode === 'vn'"
      class="flag-svg flag-vi" 
      xmlns="http://www.w3.org/2000/svg" 
      viewBox="0 0 36 36" 
      :style="flagStyle"
    >
      <clipPath :id="`vi-clip-${uniqId}`"><circle cx="18" cy="18" r="18"/></clipPath>
      <g :clip-path="`url(#vi-clip-${uniqId})`">
        <rect width="36" height="36" fill="#DA251D"/>
        <polygon fill="#FFFF00" points="18,8 21.1,17.4 31,17.4 23,23.3 26.1,32.7 18,26.8 9.9,32.7 13,23.3 5,17.4 14.9,17.4"/>
      </g>
    </svg>

    <!-- ZH / CN Flag -->
    <svg 
      v-else-if="normalizedCode === 'zh-cn' || normalizedCode === 'zh' || normalizedCode === 'cn' || normalizedCode === 'zh_hans'"
      class="flag-svg flag-zh" 
      xmlns="http://www.w3.org/2000/svg" 
      viewBox="0 0 36 36" 
      :style="flagStyle"
    >
      <clipPath :id="`zh-clip-${uniqId}`"><circle cx="18" cy="18" r="18"/></clipPath>
      <g :clip-path="`url(#zh-clip-${uniqId})`">
        <rect width="36" height="36" fill="#EE1C25"/>
        <polygon fill="#FFFF00" points="8,4.5 9.2,7.8 12.7,7.8 9.9,9.8 11,13.1 8,11.1 5,13.1 6.1,9.8 3.3,7.8 6.8,7.8" transform="translate(1, 2)"/>
        <polygon fill="#FFFF00" points="14,3.5 14.5,4.7 15.7,4.7 14.7,5.5 15.1,6.7 14,5.9 12.9,6.7 13.3,5.5 12.3,4.7 13.5,4.7"/>
        <polygon fill="#FFFF00" points="16,5.5 16.5,6.7 17.7,6.7 16.7,7.5 17.1,8.7 16,7.9 14.9,8.7 15.3,7.5 14.3,6.7 15.5,6.7"/>
        <polygon fill="#FFFF00" points="16,8.5 16.5,9.7 17.7,9.7 16.7,10.5 17.1,11.7 16,10.9 14.9,11.7 15.3,10.5 14.3,9.7 15.5,9.7"/>
        <polygon fill="#FFFF00" points="14,10.5 14.5,11.7 15.7,11.7 14.7,12.5 15.1,13.7 14,12.9 12.9,13.7 13.3,12.5 12.3,11.7 13.5,11.7"/>
      </g>
    </svg>

    <!-- RU Flag -->
    <svg 
      v-else-if="normalizedCode === 'ru'"
      class="flag-svg flag-ru" 
      xmlns="http://www.w3.org/2000/svg" 
      viewBox="0 0 36 36" 
      :style="flagStyle"
    >
      <clipPath :id="`ru-clip-${uniqId}`"><circle cx="18" cy="18" r="18"/></clipPath>
      <g :clip-path="`url(#ru-clip-${uniqId})`">
        <rect width="36" height="12" fill="#FFF"/>
        <rect y="12" width="36" height="12" fill="#0039A6"/>
        <rect y="24" width="36" height="12" fill="#D52B1E"/>
      </g>
    </svg>

    <!-- Other country code -> Load from flagcdn.com -->
    <img 
      v-else-if="normalizedCode && normalizedCode !== 'xx'"
      :src="`https://flagcdn.com/${normalizedCode}.svg`"
      :alt="normalizedCode.toUpperCase()"
      :style="flagImageStyle"
      @error="onImageError"
    />

    <!-- Fallback Globe -->
    <svg 
      v-else
      class="flag-svg flag-fallback" 
      xmlns="http://www.w3.org/2000/svg" 
      viewBox="0 0 36 36" 
      fill="none" 
      stroke="currentColor" 
      stroke-width="2" 
      :style="flagStyle"
    >
      <circle cx="18" cy="18" r="17" fill="#ECEFF1"/>
      <circle cx="18" cy="18" r="17"/>
      <path d="M1 18h34M18 1v34M4 10h28M4 26h28"/>
      <path d="M18 1c6 0 11 8 11 17s-5 17-11 17S7 27 7 18s5-17 11-17z"/>
    </svg>
  </span>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';

interface Props {
  code: string | null | undefined;
  customClass?: string;
  size?: string;
}

const props = withDefaults(defineProps<Props>(), {
  code: '',
  customClass: '',
  size: '1.1em'
});

const uniqId = Math.random().toString(36).substring(2, 9);
const hasError = ref(false);

watch(() => props.code, () => {
  hasError.value = false;
});

const normalizedCode = computed(() => {
  if (!props.code) return '';
  let clean = props.code.toLowerCase().trim();
  // Handle locale codes like zh-CN, en_US
  if (clean.includes('_')) {
    clean = clean.split('_')[1] || clean.split('_')[0];
  } else if (clean.includes('-')) {
    // Specifically handle zh-cn / zh-tw
    if (clean === 'zh-cn') return 'zh-cn';
    clean = clean.split('-')[1] || clean.split('-')[0];
  }
  
  // Specific mappings
  if (clean === 'uk') return 'gb';
  
  return hasError.value ? '' : clean;
});

const containerStyle = computed(() => ({
  width: props.size,
  height: props.size,
}));

const flagStyle = computed(() => ({
  verticalAlign: 'middle',
  display: 'inline-block',
  borderRadius: '50%',
  width: '100%',
  height: '100%',
  objectFit: 'cover' as const,
}));

const flagImageStyle = computed(() => ({
  verticalAlign: 'middle',
  display: 'inline-block',
  borderRadius: '50%',
  width: '100%',
  height: '100%',
  objectFit: 'cover' as const,
  aspectRatio: '1/1',
}));

const onImageError = () => {
  hasError.value = true;
};
</script>
