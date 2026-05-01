<template>
    <div>
        <form @submit.prevent="$emit('save')" class="space-y-6">

            <!-- Driver Section -->
            <div>
                <label for="media_driver" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ t('Driver') }}
                </label>
                <select
                    id="media_driver"
                    :value="getValue('media_driver', 'local')"
                    @change="updateValue('media_driver', ($event.target as HTMLSelectElement).value)"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option v-for="opt in getDriverOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </select>
                
                <div v-if="getValue('media_driver', 'local') !== 'local' && getCurrentDriverHint" class="mt-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-medium mb-1">{{ t('Configuration Required') }}</p>
                        <p>{{ t('You have selected a third-party driver. You must configure its connection details.') }}</p>
                        <router-link :to="getCurrentDriverHint" class="mt-2 inline-flex items-center text-blue-600 dark:text-blue-400 hover:underline">
                            {{ t('Go to configuration page') }} &rarr;
                        </router-link>
                    </div>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div>
                    <FormCheckbox
                        name="media_use_original_name"
                        :modelValue="getValue('media_use_original_name', false)"
                        @update:modelValue="updateValue('media_use_original_name', $event)"
                        :label="t('Use original name for file path')"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 pl-6">
                        {{ t('Preserve original file names and non-Latin characters instead of converting them to URL-friendly slugs.') }}
                    </p>
                </div>

                <div>
                    <FormCheckbox
                        name="media_convert_uuid"
                        :modelValue="getValue('media_convert_uuid', false)"
                        @update:modelValue="updateValue('media_convert_uuid', $event)"
                        :label="t('Convert file name to UUID')"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 pl-6">{{ t('Convert file names to UUID for better security and to prevent duplicates.') }}</p>
                </div>

                <div>
                    <FormCheckbox
                        name="media_keep_original_size_quality"
                        :modelValue="getValue('media_keep_original_size_quality', false)"
                        @update:modelValue="updateValue('media_keep_original_size_quality', $event)"
                        :label="t('Keep original file size and quality')"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 pl-6">{{ t('Prevent images from being resized or compressed during upload.') }}</p>
                </div>
            </div>

            <!-- Image Quality Settings -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    {{ t('Image quality') }}
                </label>
                <div class="mb-5">
                     <RangeSlider 
                        :modelValue="getValue('media_image_quality', 75)" 
                        @update:modelValue="updateValue('media_image_quality', $event)"
                        :min="1" 
                        :max="100" 
                        :step="1" 
                        unit=""
                    />
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t('Set image encoding quality (1-100). Lower values produce smaller files.') }}
                </p>
            </div>

            <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div>
                    <FormCheckbox
                        name="media_convert_to_webp"
                        :modelValue="getValue('media_convert_to_webp', false)"
                        @update:modelValue="updateValue('media_convert_to_webp', $event)"
                        :label="t('Convert JPG, JPEG, PNG image to WebP')"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 pl-6">{{ t('Automatically convert JPG and PNG images to WebP format during upload.') }}</p>
                </div>
            </div>

            <!-- Default Placeholder Image -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <label for="media_default_placeholder" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ t('Default placeholder image') }}
                </label>
                <!-- Hidden input to store the URL -->
                <input
                    id="media_default_placeholder"
                    :value="getValue('media_default_placeholder', '')"
                    @input="updateValue('media_default_placeholder', ($event.target as HTMLInputElement).value)"
                    type="hidden"
                />
                <div class="flex items-center gap-4">
                    <div v-if="getValue('media_default_placeholder', '')" class="flex-shrink-0">
                        <img
                            :src="getValue('media_default_placeholder', '')"
                            alt="Default Placeholder"
                            class="h-16 w-auto object-contain border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700"
                        />
                    </div>
                    <div v-else class="flex-shrink-0 w-16 h-16 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <button
                            type="button"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                            @click="openMediaPicker('media_default_placeholder')"
                        >
                            {{ getValue('media_default_placeholder', '') ? t('Change Image') : t('Select Image') }}
                        </button>
                        <button
                            v-if="getValue('media_default_placeholder', '')"
                            type="button"
                            class="ml-2 px-4 py-2 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            @click="updateValue('media_default_placeholder', '')"
                        >
                            {{ t('Remove') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div>
                    <label for="media_max_upload_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ t('Max upload filesize (MB)') }}
                    </label>
                    <input
                        id="media_max_upload_size"
                        :value="getValue('media_max_upload_size', 2)"
                        @input="updateValue('media_max_upload_size', Number(($event.target as HTMLInputElement).value))"
                        type="number"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ t('Your server allows a maximum upload size of') }} <strong>{{ getValue('media_server_max_upload_size', 2) }} MB</strong>. {{ t('You can restrict this by setting a lower value below.') }}
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="mb-4">
                        <FormCheckbox
                            name="media_customize_upload_path"
                            :modelValue="getValue('media_customize_upload_path', false)"
                            @update:modelValue="updateValue('media_customize_upload_path', $event)"
                            :label="t('Customize upload path')"
                        />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 pl-6">{{ t('Allow customizing the upload directory for media files.') }}</p>
                        
                        <div v-if="getValue('media_customize_upload_path', false)" class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 ml-6">
                            <label for="media_upload_path" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex justify-between">
                                <span>{{ t('Upload path') }}</span>
                                <span class="text-gray-400 font-normal">({{ getValue('media_upload_path', 'storage').length }}/250)</span>
                            </label>
                            <input
                                id="media_upload_path"
                                :value="getValue('media_upload_path', 'storage')"
                                @input="updateValue('media_upload_path', ($event.target as HTMLInputElement).value)"
                                type="text"
                                maxlength="250"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 mb-2"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ t('That folder will be created in /public. The default folder is "storage". Then it will be uploaded into "/public/storage".') }}
                            </p>
                            
                            <div class="mt-4 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg flex items-start">
                                <svg class="w-5 h-5 text-orange-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <p class="text-sm text-orange-800 dark:text-orange-200 leading-snug">
                                    {{ t('The system won\'t move existing files to the new folder. If you want to move existing files to the new folder, you need to do it manually.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <FormCheckbox
                            name="media_enable_chunk_upload"
                            :modelValue="getValue('media_enable_chunk_upload', false)"
                            @update:modelValue="updateValue('media_enable_chunk_upload', $event)"
                            :label="t('Enable the chunk upload')"
                        />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 pl-6">{{ t('Enable chunked uploading for very large files.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Image Processing Library -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    {{ t('Image processing library') }}
                </label>
                <div class="flex items-center space-x-6">
                    <label class="flex items-center" :class="{ 'opacity-50 cursor-not-allowed': !getValue('media_has_gd', true), 'cursor-pointer': getValue('media_has_gd', true) }">
                        <input 
                            type="radio" 
                            name="media_image_processing_library" 
                            value="gd" 
                            :disabled="!getValue('media_has_gd', true)"
                            :checked="getValue('media_image_processing_library', 'gd') === 'gd'"
                            @change="updateValue('media_image_processing_library', 'gd')"
                            class="mr-2 text-indigo-600 focus:ring-indigo-500 form-radio disabled:bg-gray-200 dark:disabled:bg-gray-700"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            GD Library
                            <span v-if="!getValue('media_has_gd', true)" class="text-red-500 text-xs ml-1">({{ t('Not installed') }})</span>
                        </span>
                    </label>
                    <label class="flex items-center" :class="{ 'opacity-50 cursor-not-allowed': !getValue('media_has_imagick', true), 'cursor-pointer': getValue('media_has_imagick', true) }">
                        <input 
                            type="radio" 
                            name="media_image_processing_library" 
                            value="imagick" 
                            :disabled="!getValue('media_has_imagick', true)"
                            :checked="getValue('media_image_processing_library', 'gd') === 'imagick'"
                            @change="updateValue('media_image_processing_library', 'imagick')"
                            class="mr-2 text-indigo-600 focus:ring-indigo-500 form-radio disabled:bg-gray-200 dark:disabled:bg-gray-700"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            Imagick
                            <span v-if="!getValue('media_has_imagick', true)" class="text-red-500 text-xs ml-1">({{ t('Not installed') }})</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Thumbnails -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                <div>
                    <FormCheckbox
                        name="media_enable_thumbnails"
                        :modelValue="getValue('media_enable_thumbnails', true)"
                        @update:modelValue="updateValue('media_enable_thumbnails', $event)"
                        :label="t('Enable thumbnail sizes')"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 pl-6">{{ t('Generate smaller thumbnail versions of uploaded images.') }}</p>
                </div>

                <div v-if="getValue('media_enable_thumbnails', true)" class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg space-y-4 bg-gray-50 dark:bg-gray-800/50">
                    <h4 class="font-medium text-gray-900 dark:text-white">Media thumbnails sizes:</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thumb (Default: 150x150)</label>
                            <input
                                :value="getValue('media_thumb_width', 150)"
                                @input="updateValue('media_thumb_width', Number(($event.target as HTMLInputElement).value))"
                                type="number"
                                placeholder="Width"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">&nbsp;</label>
                            <input
                                :value="getValue('media_thumb_height', 150)"
                                @input="updateValue('media_thumb_height', Number(($event.target as HTMLInputElement).value))"
                                type="number"
                                placeholder="Height"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Featured (Default: 565x375)</label>
                            <input
                                :value="getValue('media_featured_width', 565)"
                                @input="updateValue('media_featured_width', Number(($event.target as HTMLInputElement).value))"
                                type="number"
                                placeholder="Width"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">&nbsp;</label>
                            <input
                                :value="getValue('media_featured_height', 375)"
                                @input="updateValue('media_featured_height', Number(($event.target as HTMLInputElement).value))"
                                type="number"
                                placeholder="Height"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-8">
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="saving" class="flex items-center">
                        <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        {{ t('Saving...') }}
                    </span>
                    <span v-else>{{ t('Save Changes') }}</span>
                </button>
            </div>
        </form>
        
        <!-- Media Picker Modal -->
        <MediaPicker
            ref="mediaPickerRef"
            :multiple="false"
            :accepted-types="['image']"
            @select="handleMediaSelect"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useTranslation } from '../../../composables/useTranslation';
import FormCheckbox from '../../../components/forms/FormCheckbox.vue';
import RangeSlider from '../../../components/editor/controls/RangeSlider.vue';
import MediaPicker from '../../../components/MediaPicker.vue';

interface Setting {
    key: string;
    value: any;
    type: string;
    label?: string;
    description?: string;
    options?: { label: string; value: any }[];
}

interface Props {
    settings: Record<string, Setting>;
    saving: boolean;
    group: string;
}

const props = defineProps<Props>();
const { t } = useTranslation();

const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const getValue = (key: string, defaultValue: any) => {
    // If setting doesn't exist yet, we initialize it
    if (!props.settings[key]) {
        return defaultValue;
    }
    
    // Explicit string conversion for boolean toggles from API
    if (typeof defaultValue === 'boolean') {
        const val = props.settings[key].value;
        return ['true', '1', 1, true].includes(val);
    }
    
    return props.settings[key].value ?? defaultValue;
};

const updateValue = (key: string, value: any) => {
    emit('update', props.group, key, value);
};

const getDriverOptions = computed(() => {
    return props.settings['media_driver']?.options || [{ label: 'Local disk', value: 'local' }];
});

const getCurrentDriverHint = computed(() => {
    const hints = props.settings['media_driver_hints']?.value || {};
    const driver = getValue('media_driver', 'local');
    return hints[driver] || null;
});

const mediaPickerRef = ref<any>(null);
const activeMediaKey = ref('');

const openMediaPicker = (key: string) => {
    activeMediaKey.value = key;
    mediaPickerRef.value?.open();
};

const handleMediaSelect = (items: any | any[]) => {
    const selectedItems = Array.isArray(items) ? items : [items];
    if (selectedItems.length > 0 && activeMediaKey.value) {
        updateValue(activeMediaKey.value, selectedItems[0].url);
    }
};
</script>
