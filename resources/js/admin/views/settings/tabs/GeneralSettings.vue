<template>
 <div>
 <form @submit.prevent="$emit('save')" class="space-y-6">
 <!-- Site Title -->
 <div>
 <label for="site_title" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('site_title') }}
 </label>
 <input
 id="site_title"
 :value="getSettingValue('site_title')"
 @input="updateValue('site_title', ($event.target as HTMLInputElement).value)"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('site_title') }}
 </p>
 </div>

 <!-- Tagline -->
 <div>
 <label for="tagline" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('tagline') }}
 </label>
 <input
 id="tagline"
 :value="getSettingValue('tagline')"
 @input="updateValue('tagline', ($event.target as HTMLInputElement).value)"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('tagline') }}
 </p>
 </div>

 <!-- Brand Logo -->
 <div>
 <label for="brand_logo" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('brand_logo') }}
 </label>
 <input
 id="brand_logo"
 :value="getSettingValue('brand_logo')"
 @input="updateValue('brand_logo', ($event.target as HTMLInputElement).value)"
 type="hidden"
 />
 <div class="flex items-center gap-4">
 <div v-if="getSettingValue('brand_logo')" class="flex-shrink-0">
 <img
 :src="getSettingValue('brand_logo')"
 alt="Brand Logo"
 class="h-16 w-auto object-contain border border-admin-theme-border rounded p-2 bg-admin-theme-input-bg"
 />
 </div>
 <div v-else class="flex-shrink-0 w-16 h-16 border-2 border-dashed border-admin-theme-border rounded flex items-center justify-center bg-admin-theme-base">
 <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 <div class="flex-1">
 <button type="button" class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors" @click="openMediaPicker('brand_logo')">
 {{ getSettingValue('brand_logo') ? $t('Change Logo') : $t('Select Logo') }}
 </button>
 <button v-if="getSettingValue('brand_logo')" type="button" class="ml-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" @click="updateValue('brand_logo','')">
 {{ $t('Remove') }}
 </button>
 </div>
 </div>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('brand_logo') ||'Upload a logo for your brand. If no logo is set, the brand name will be displayed instead.' }}
 </p>
 <div v-if="getSettingValue('brand_logo')" class="mt-3">
 <FormToggle
 name="show_brand_label"
 :modelValue="['true','1'].includes(getSettingValue('show_brand_label'))"
 :label="$t('Show Site Name next to Logo')"
 @update:modelValue="updateValue('show_brand_label', $event)"
 />
 </div>
 </div>

 <!-- Brand Name -->
 <div>
 <label for="brand_name" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('brand_name') }}
 </label>
 <input
 id="brand_name"
 :value="getSettingValue('brand_name')"
 @input="updateValue('brand_name', ($event.target as HTMLInputElement).value)"
 type="text"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 placeholder="POLYCMS"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('brand_name') ||'Custom brand name to display when no logo is available. Defaults to"POLYCMS" if empty.' }}
 </p>
 </div>

 <!-- Admin Email -->
 <div>
 <label for="admin_email" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('admin_email') }}
 </label>
 <input
 id="admin_email"
 :value="getSettingValue('admin_email')"
 @input="updateValue('admin_email', ($event.target as HTMLInputElement).value)"
 type="email"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 placeholder="admin@example.com"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('admin_email') ||'Email address for the site administrator.' }}
 </p>
 </div>

 <!-- Site Language -->
 <div>
 <label for="site_language" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('site_language') }}
 </label>
 <select
 id="site_language"
 :value="getSettingValue('site_language')"
 @change="updateValue('site_language', ($event.target as HTMLSelectElement).value)"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 >
 <option v-for="lang in activeLanguages" :key="lang.code" :value="lang.code">
 {{ lang.name }}
 </option>
 </select>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('site_language') ||'The default language for your site. Modules and themes can use this for localization.' }}
 </p>
 </div>

 <!-- Front Site Language Direction -->
 <div>
 <label for="site_language_direction" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('site_language_direction') }}
 </label>
 <select
 id="site_language_direction"
 :value="getSettingValue('site_language_direction')"
 @change="updateValue('site_language_direction', ($event.target as HTMLSelectElement).value)"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 >
 <option value="ltr">{{ $t('Left to Right (LTR)') }}</option>
 <option value="rtl">{{ $t('Right to Left (RTL)') }}</option>
 </select>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('site_language_direction') ||'Text direction for the frontend site. This will be applied to the CSS direction property.' }}
 </p>
 </div>

 <!-- Admin Topbar Dark Mode Toggle -->
 <div>
 <FormToggle
 name="admin_topbar_dark_mode"
 :modelValue="['true','1'].includes(getSettingValue('admin_topbar_dark_mode'))"
 :label="getSettingLabel('admin_topbar_dark_mode') ||'Show Topbar Dark/Light Mode Toggle'"
 @update:modelValue="updateValue('admin_topbar_dark_mode', $event)"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('admin_topbar_dark_mode') ||'Enable or disable the theme toggle button on the Admin Topbar.' }}
 </p>
 </div>

 <!-- Site Icon -->
 <div>
 <label for="site_icon" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('site_icon') }}
 </label>
 <!-- Hidden input to store the URL -->
 <input
 id="site_icon"
 :value="getSettingValue('site_icon')"
 @input="updateValue('site_icon', ($event.target as HTMLInputElement).value)"
 type="hidden"
 />
 <div class="flex items-center gap-4">
 <div v-if="getSettingValue('site_icon')" class="flex-shrink-0">
 <img
 :src="getSettingValue('site_icon')"
 alt="Site Icon"
 class="w-16 h-16 object-contain border border-admin-theme-border rounded p-2 bg-admin-theme-input-bg"
 />
 </div>
 <div v-else class="flex-shrink-0 w-16 h-16 border-2 border-dashed border-admin-theme-border rounded flex items-center justify-center bg-admin-theme-base">
 <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 <div class="flex-1">
 <button
 type="button"
 class="px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
 @click="openMediaPicker('site_icon')"
 >
 {{ getSettingValue('site_icon') ? $t('Change Icon') : $t('Select Icon') }}
 </button>
 <button
 v-if="getSettingValue('site_icon')"
 type="button"
 class="ml-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
 @click="updateValue('site_icon','')"
 >
 {{ $t('Remove') }}
 </button>
 </div>
 </div>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('site_icon') }}
 </p>
 </div>

 <!-- Timezone -->
 <div>
 <label for="timezone" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('timezone') }}
 </label>
 <select
 id="timezone"
 :value="getSettingValue('timezone')"
 @change="updateValue('timezone', ($event.target as HTMLSelectElement).value)"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 >
 <option value="UTC">UTC</option>
 <option value="America/New_York">America/New_York (EST/EDT)</option>
 <option value="America/Chicago">America/Chicago (CST/CDT)</option>
 <option value="America/Denver">America/Denver (MST/MDT)</option>
 <option value="America/Los_Angeles">America/Los_Angeles (PST/PDT)</option>
 <option value="Europe/London">Europe/London (GMT/BST)</option>
 <option value="Europe/Paris">Europe/Paris (CET/CEST)</option>
 <option value="Europe/Berlin">Europe/Berlin (CET/CEST)</option>
 <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
 <option value="Asia/Shanghai">Asia/Shanghai (CST)</option>
 <option value="Asia/Hong_Kong">Asia/Hong_Kong (HKT)</option>
 <option value="Asia/Singapore">Asia/Singapore (SGT)</option>
 <option value="Australia/Sydney">Australia/Sydney (AEDT/AEST)</option>
 </select>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('timezone') }}
 </p>
 </div>

 <!-- Date Format -->
 <div>
 <label for="date_format" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('date_format') }}
 </label>
 <div class="space-y-2">
 <div class="flex gap-4">
 <label class="flex items-center">
 <input
 type="radio"
 :value="'Y-m-d'"
 :checked="getSettingValue('date_format') ==='Y-m-d'"
 @change="updateValue('date_format','Y-m-d')"
 class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-border"
 />
 <span class="ml-2 text-sm text-admin-theme-text-secondary">
 {{ formatDate(new Date(),'Y-m-d') }} (Y-m-d)
 </span>
 </label>
 </div>
 <div class="flex gap-4">
 <label class="flex items-center">
 <input
 type="radio"
 :value="'m/d/Y'"
 :checked="getSettingValue('date_format') ==='m/d/Y'"
 @change="updateValue('date_format','m/d/Y')"
 class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-border"
 />
 <span class="ml-2 text-sm text-admin-theme-text-secondary">
 {{ formatDate(new Date(),'m/d/Y') }} (m/d/Y)
 </span>
 </label>
 </div>
 <div class="flex gap-4">
 <label class="flex items-center">
 <input
 type="radio"
 :value="'d/m/Y'"
 :checked="getSettingValue('date_format') ==='d/m/Y'"
 @change="updateValue('date_format','d/m/Y')"
 class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-border"
 />
 <span class="ml-2 text-sm text-admin-theme-text-secondary">
 {{ formatDate(new Date(),'d/m/Y') }} (d/m/Y)
 </span>
 </label>
 </div>
 <div class="flex gap-4">
 <label class="flex items-center">
 <input
 type="radio"
 :value="'F j, Y'"
 :checked="getSettingValue('date_format') ==='F j, Y'"
 @change="updateValue('date_format','F j, Y')"
 class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-border"
 />
 <span class="ml-2 text-sm text-admin-theme-text-secondary">
 {{ formatDate(new Date(),'F j, Y') }} (F j, Y)
 </span>
 </label>
 </div>
 <div class="mt-2">
 <label class="flex items-center">
 <input
 type="radio"
 :value="'custom'"
 :checked="!['Y-m-d','m/d/Y','d/m/Y','F j, Y'].includes(getSettingValue('date_format'))"
 @change="showCustomDateFormat = true"
 class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-border"
 />
 <span class="ml-2 text-sm text-admin-theme-text-secondary">{{ $t('Custom') }}:</span>
 <input
 v-if="showCustomDateFormat || !['Y-m-d','m/d/Y','d/m/Y','F j, Y'].includes(getSettingValue('date_format'))"
 :value="getSettingValue('date_format')"
 @input="updateValue('date_format', ($event.target as HTMLInputElement).value)"
 type="text"
 class="ml-2 flex-1 px-3 py-1 border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text text-sm"
 placeholder="Y-m-d"
 />
 </label>
 </div>
 </div>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('date_format') }}
 </p>
 </div>

 <!-- Time Format -->
 <div>
 <label for="time_format" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('time_format') }}
 </label>
 <div class="space-y-2">
 <div class="flex gap-4">
 <label class="flex items-center">
 <input
 type="radio"
 :value="'H:i'"
 :checked="getSettingValue('time_format') ==='H:i'"
 @change="updateValue('time_format','H:i')"
 class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-border"
 />
 <span class="ml-2 text-sm text-admin-theme-text-secondary">
 {{ formatTime(new Date(),'H:i') }} {{ $t('24-hour format') }}
 </span>
 </label>
 </div>
 <div class="flex gap-4">
 <label class="flex items-center">
 <input
 type="radio"
 :value="'g:i A'"
 :checked="getSettingValue('time_format') ==='g:i A'"
 @change="updateValue('time_format','g:i A')"
 class="h-4 w-4 text-admin-theme-primary focus:ring-admin-theme-primary border-admin-theme-border"
 />
 <span class="ml-2 text-sm text-admin-theme-text-secondary">
 {{ formatTime(new Date(),'g:i A') }} {{ $t('12-hour format') }}
 </span>
 </label>
 </div>
 </div>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('time_format') }}
 </p>
 </div>

 <!-- Week Starts On -->
 <div>
 <label for="week_starts_on" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ getSettingLabel('week_starts_on') }}
 </label>
 <select
 id="week_starts_on"
 :value="getSettingValue('week_starts_on')"
 @change="updateValue('week_starts_on', ($event.target as HTMLSelectElement).value)"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 >
 <option value="0">{{ $t('Sunday') }}</option>
 <option value="1">{{ $t('Monday') }}</option>
 <option value="2">{{ $t('Tuesday') }}</option>
 <option value="3">{{ $t('Wednesday') }}</option>
 <option value="4">{{ $t('Thursday') }}</option>
 <option value="5">{{ $t('Friday') }}</option>
 <option value="6">{{ $t('Saturday') }}</option>
 </select>
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ getSettingDescription('week_starts_on') }}
 </p>
 </div>

 <!-- Session & Security -->
 <div class="pt-6 mt-6 border-t border-admin-theme-border">
 <h3 class="text-base font-semibold text-admin-theme-text mb-1">{{ $t('Session & Security') }}</h3>
 <p class="text-sm text-admin-theme-text-muted mb-4">{{ $t('Configure session duration and API request limits. Increasing these values helps when editing many posts simultaneously.') }}</p>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <!-- Session Lifetime -->
 <div>
 <label for="session_lifetime" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ $t('Session Lifetime (minutes)') }}
 </label>
 <input
 id="session_lifetime"
 :value="getSettingValue('session_lifetime') || '120'"
 @input="updateValue('session_lifetime', ($event.target as HTMLInputElement).value)"
 type="number"
 min="30"
 max="10080"
 step="30"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ $t('How long an admin session stays active before requiring re-login. Default: 120 minutes (2 hours). Recommended: 480+ for heavy editing.') }}
 </p>
 </div>

 <!-- API Rate Limit -->
 <div>
 <label for="api_rate_limit" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">
 {{ $t('API Rate Limit (requests/minute)') }}
 </label>
 <input
 id="api_rate_limit"
 :value="getSettingValue('api_rate_limit') || '300'"
 @input="updateValue('api_rate_limit', ($event.target as HTMLInputElement).value)"
 type="number"
 min="60"
 max="3000"
 step="60"
 class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text placeholder-admin-theme-text-muted focus:ring-admin-theme-primary focus:border-admin-theme-primary"
 />
 <p class="mt-1 text-sm text-admin-theme-text-muted">
 {{ $t('Maximum API requests per minute per user. Increase this if you experience unexpected logouts when editing many items. Default: 300. Recommended: 600+ for heavy editing.') }}
 </p>
 </div>
 </div>
 </div>

 <!-- Save Button -->
 <div class="flex justify-end pt-4 border-t border-admin-theme-border">
 <button
 type="submit"
 :disabled="saving"
 class="px-6 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
 >
 <span v-if="saving" class="flex items-center">
 <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
 {{ $t('Saving...') }}
 </span>
 <span v-else>{{ $t('Save Changes') }}</span>
 </button>
 </div>
 </form>
 <!-- Media Picker -->
 <MediaPicker
 ref="mediaPickerRef"
 :multiple="false"
 :accepted-types="['image']"
 @select="handleMediaSelect"
 />
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted, getCurrentInstance } from 'vue';
import axios from 'axios';
import MediaPicker from '../../../components/MediaPicker';
import FormToggle from '../../../components/forms/FormToggle.vue';
import { useTranslation } from '@/admin/composables/useTranslation';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

interface Setting {
 key: string;
 value: any;
 type: string;
 label: string;
 description: string;
}

interface Props {
 settings: {
 [key: string]: Setting;
 };
 saving: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
 (e:'update', group: string, key: string, value: any): void;
 (e:'save'): void;
}>();

const showCustomDateFormat = ref(false);

const getSettingValue = (key: string): any => {
 // Ensure we return a string even if value is null/undefined
 const value = props.settings[key]?.value;
 return value !== null && value !== undefined ? String(value) :'';
};

const getSettingLabel = (key: string): string => {
 return props.settings[key]?.label ?? key;
};

const getSettingDescription = (key: string): string => {
 return props.settings[key]?.description ??'';
};

const updateValue = (key: string, value: any) => {
 emit('update','general', key, value);
};

const formatDate = (date: Date, format: string): string => {
 const year = date.getFullYear();
 const month = String(date.getMonth() + 1).padStart(2,'0');
 const day = String(date.getDate()).padStart(2,'0');
 const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

 return format
 .replace('Y', String(year))
 .replace('m', month)
 .replace('d', day)
 .replace('F', monthNames[date.getMonth()])
 .replace('j', String(date.getDate()));
};

const formatTime = (date: Date, format: string): string => {
 let hours = date.getHours();
 const minutes = String(date.getMinutes()).padStart(2,'0');
 const ampm = hours >= 12 ?'PM' :'AM';

 if (format ==='H:i') {
 return `${String(hours).padStart(2,'0')}:${minutes}`;
 } else {
 hours = hours % 12;
 hours = hours ? hours : 12;
 return `${hours}:${minutes} ${ampm}`;
 }
};

const mediaPickerRef = ref<any>(null);
const currentPickerField = ref<string>('');
const activeLanguages = ref<any[]>([]);

const loadLanguages = async () => {
 try {
 const response = await axios.get('/api/v1/languages');
 if (response.data && response.data.data) {
 activeLanguages.value = response.data.data.filter((l: any) => l.is_active);
 }
 } catch (error) {
 console.error('Failed to load languages in settings:', error);
 }
};

onMounted(() => {
 loadLanguages();
});

const openMediaPicker = (field: string ='site_icon') => {
 currentPickerField.value = field;
 if (mediaPickerRef.value) {
 mediaPickerRef.value.open();
 }
};

const handleMediaSelect = (media: any) => {
 if (currentPickerField.value && media) {
 // media can be single object or array, handle both
 const selectedMedia = Array.isArray(media) ? media[0] : media;
 if (selectedMedia && selectedMedia.url) {
 updateValue(currentPickerField.value, selectedMedia.url);
 }
 }
 currentPickerField.value ='';
};
</script>
