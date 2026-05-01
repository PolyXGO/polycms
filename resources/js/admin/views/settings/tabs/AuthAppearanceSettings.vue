<template>
    <div>
        <form @submit.prevent="$emit('save')" class="space-y-6">
            
            <!-- Auth Login Text -->
            <div>
                <label for="auth_login_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ getSettingLabel('auth_login_text') || 'Login Page Footer Text' }}
                </label>
                <textarea
                    id="auth_login_text"
                    :value="getSettingValue('auth_login_text')"
                    @input="updateValue('auth_login_text', ($event.target as HTMLTextAreaElement).value)"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="PolyXGO with love&#10;Copyright 2026 © PolyXGO."
                ></textarea>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('auth_login_text') }}
                </p>
            </div>
            
            <!-- Show Version Info -->
            <div>
                <FormToggle
                    name="auth_show_version"
                    :modelValue="['true', '1', true, 1].includes(getSettingValue('auth_show_version'))"
                    :label="getSettingLabel('auth_show_version') || 'Show System Version'"
                    @update:modelValue="updateValue('auth_show_version', $event)"
                />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('auth_show_version') || 'Display the PolyCMS and Laravel framework version numbers.' }}
                </p>
            </div>

            <!-- Auth Layout Position -->
            <div>
                <label for="auth_layout_position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ getSettingLabel('auth_layout_position') }}
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <label 
                        v-for="option in getSetting('auth_layout_position')?.options" 
                        :key="option.value"
                        class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none dark:bg-gray-800"
                        :class="getSettingValue('auth_layout_position') === option.value ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-300 dark:border-gray-600'"
                        @click="updateValue('auth_layout_position', option.value)"
                    >
                        <div class="flex flex-1">
                            <div class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ $t(option.label) }}</span>
                            </div>
                        </div>
                        <div v-show="getSettingValue('auth_layout_position') === option.value" class="h-5 w-5 text-indigo-600 dark:text-indigo-400">
                            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('auth_layout_position') }}
                </p>
            </div>

            <!-- Show Logo -->
            <div>
                <FormToggle
                    name="auth_show_logo"
                    :modelValue="['true', '1', true, 1].includes(getSettingValue('auth_show_logo'))"
                    :label="getSettingLabel('auth_show_logo') || 'Show Brand Logo/Name'"
                    @update:modelValue="updateValue('auth_show_logo', $event)"
                />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('auth_show_logo') }}
                </p>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>

            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $t('Background Settings') }}</h3>

            <!-- Auth Background Mode -->
            <div>
                <label for="auth_bg_mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ getSettingLabel('auth_bg_mode') }}
                </label>
                <select
                    id="auth_bg_mode"
                    :value="getSettingValue('auth_bg_mode')"
                    @change="updateValue('auth_bg_mode', ($event.target as HTMLSelectElement).value)"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option v-for="option in getSetting('auth_bg_mode')?.options" :key="option.value" :value="option.value">
                        {{ $t(option.label) }}
                    </option>
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('auth_bg_mode') }}
                </p>
            </div>

            <!-- Auth Background Gallery (Always visible) -->
            <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ getSettingLabel('auth_bg_images') }}
                </label>
                
                <div class="flex items-center gap-4 mb-4">
                    <button
                        type="button"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        @click="openMediaPicker('auth_bg_images', true)"
                    >
                        {{ parsedGallery.length > 0 ? $t('Change Gallery Images') : $t('Select Gallery Images') }}
                    </button>
                    <button
                        v-if="parsedGallery.length > 0"
                        type="button"
                        class="px-4 py-2 text-red-600 hover:text-red-800 transition-colors"
                        @click="updateValue('auth_bg_images', [])"
                    >
                        {{ $t('Clear Gallery') }}
                    </button>
                </div>

                <div v-if="parsedGallery.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <div v-for="(img, idx) in parsedGallery" :key="idx" class="relative group aspect-w-4 aspect-h-3">
                        <img :src="img.url || img" class="object-cover w-full h-full rounded-lg border transition-all" :class="getSettingValue('auth_bg_fixed_image') === (img.url || img) ? 'border-2 border-indigo-500' : 'border-gray-200 dark:border-gray-700'" />
                        
                        <!-- Actions -->
                        <div class="absolute top-1 right-1 flex gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity drop-shadow">
                            <!-- Set Default Button -->
                            <button type="button" @click.stop="updateValue('auth_bg_fixed_image', img.url || img)" class="bg-white dark:bg-gray-800 rounded-full p-1" :class="getSettingValue('auth_bg_fixed_image') === (img.url || img) ? 'text-yellow-500' : 'text-gray-400 hover:text-yellow-500'" :title="$t('Set as Default (Single) Image')">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            </button>
                            
                            <!-- Remove Button -->
                            <button type="button" @click.stop="removeGalleryImage(idx)" class="bg-white dark:bg-gray-800 text-red-500 hover:text-red-600 rounded-full p-1" :title="$t('Remove Image')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-500">{{ $t('No images selected. Default random images will not be shown.') }}</div>

                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('auth_bg_images') }}
                </p>
            </div>

            <!-- Styling Range sliders -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                <!-- Background Overlay -->
                <div>
                    <label for="auth_bg_overlay" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ getSettingLabel('auth_bg_overlay') }}: <span class="font-bold text-indigo-600">{{ getSettingValue('auth_bg_overlay') || 50 }}%</span>
                    </label>
                    <input
                        id="auth_bg_overlay"
                        :value="getSettingValue('auth_bg_overlay')"
                        @input="updateValue('auth_bg_overlay', Number(($event.target as HTMLInputElement).value))"
                        type="range" min="0" max="100" step="1"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ getSettingDescription('auth_bg_overlay') }}
                    </p>
                </div>

                <!-- Card Glassmorphism -->
                <div>
                    <label for="auth_card_glassmorphism" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ getSettingLabel('auth_card_glassmorphism') }}: <span class="font-bold text-indigo-600">{{ getSettingValue('auth_card_glassmorphism') || 10 }}%</span>
                    </label>
                    <input
                        id="auth_card_glassmorphism"
                        :value="getSettingValue('auth_card_glassmorphism')"
                        @input="updateValue('auth_card_glassmorphism', Number(($event.target as HTMLInputElement).value))"
                        type="range" min="0" max="100" step="1"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ getSettingDescription('auth_card_glassmorphism') }}
                    </p>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="saving" class="flex items-center">
                        <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        {{ $t('Saving...') }}
                    </span>
                    <span v-else>{{ $t('Save Changes') }}</span>
                </button>
            </div>
        </form>

        <MediaPicker
            ref="mediaPickerRef"
            :multiple="pickerMultiple"
            :accepted-types="['image']"
            @select="handleMediaSelect"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useTranslation } from '../../../composables/useTranslation';
import FormToggle from '../../../components/forms/FormToggle.vue';
import MediaPicker from '../../../components/MediaPicker.vue';

interface Setting {
    key: string;
    value: any;
    type: string;
    label: string;
    description: string;
    options?: { label: string; value: any }[];
}

interface Props {
    settings: {
        [key: string]: Setting;
    };
    saving: boolean;
    group: string;
}

const props = defineProps<Props>();
const { t } = useTranslation();

const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const getSetting = (key: string): Setting | undefined => {
    return props.settings[key];
};

const getSettingValue = (key: string): any => {
    return props.settings[key]?.value;
};

const getSettingLabel = (key: string): string => {
    return props.settings[key]?.label ?? key;
};

const getSettingDescription = (key: string): string => {
    return props.settings[key]?.description ?? '';
};

const updateValue = (key: string, value: any) => {
    emit('update', props.group, key, value);
};

// Media Picker
const mediaPickerRef = ref<any>(null);
const currentPickerField = ref<string>('');
const pickerMultiple = ref<boolean>(false);

const openMediaPicker = (field: string, multiple: boolean = false) => {
    currentPickerField.value = field;
    pickerMultiple.value = multiple;
    if (mediaPickerRef.value) {
        mediaPickerRef.value.open();
    }
};

const handleMediaSelect = (media: any) => {
    if (!currentPickerField.value) return;

    if (pickerMultiple.value) {
        // media is an array
        const currentVals = parsedGallery.value;
        const newVals = Array.isArray(media) ? media.map((m: any) => m.url || m) : [media.url || media];
        updateValue(currentPickerField.value, [...newVals]);
    } else {
        // media is a single object
        updateValue(currentPickerField.value, media.url || media);
    }
};

// Parse gallery images for display
const parsedGallery = computed(() => {
    const raw = getSettingValue('auth_bg_images');
    if (!raw) return [];
    try {
        if (typeof raw === 'string') return JSON.parse(raw);
        if (Array.isArray(raw)) return raw;
    } catch(e) {
        return [];
    }
    return [];
});

const removeGalleryImage = (index: number) => {
    const list = [...parsedGallery.value];
    list.splice(index, 1);
    updateValue('auth_bg_images', list);
};
</script>
