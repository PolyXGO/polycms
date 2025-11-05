<template>
    <div>
        <form @submit.prevent="$emit('save')" class="space-y-6">
            <!-- Site Title -->
            <div>
                <label for="site_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Site Title
                </label>
                <input
                    id="site_title"
                    :value="getSettingValue('site_title')"
                    @input="updateValue('site_title', ($event.target as HTMLInputElement).value)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('site_title') }}
                </p>
            </div>

            <!-- Tagline -->
            <div>
                <label for="tagline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tagline
                </label>
                <input
                    id="tagline"
                    :value="getSettingValue('tagline')"
                    @input="updateValue('tagline', ($event.target as HTMLInputElement).value)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('tagline') }}
                </p>
            </div>

            <!-- Site Icon -->
            <div>
                <label for="site_icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Site Icon
                </label>
                <div class="flex items-center gap-4">
                    <input
                        id="site_icon"
                        :value="getSettingValue('site_icon')"
                        @input="updateValue('site_icon', ($event.target as HTMLInputElement).value)"
                        type="text"
                        class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="URL or path to site icon"
                    />
                    <button
                        type="button"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                        @click="openMediaPicker"
                    >
                        Select Icon
                    </button>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('site_icon') }}
                </p>
                <div v-if="getSettingValue('site_icon')" class="mt-2">
                    <img
                        :src="getSettingValue('site_icon')"
                        alt="Site Icon"
                        class="w-16 h-16 object-contain border border-gray-300 dark:border-gray-600 rounded"
                    />
                </div>
            </div>

            <!-- Timezone -->
            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Timezone
                </label>
                <select
                    id="timezone"
                    :value="getSettingValue('timezone')"
                    @change="updateValue('timezone', ($event.target as HTMLSelectElement).value)"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
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
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('timezone') }}
                </p>
            </div>

            <!-- Date Format -->
            <div>
                <label for="date_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date Format
                </label>
                <div class="space-y-2">
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                :value="'Y-m-d'"
                                :checked="getSettingValue('date_format') === 'Y-m-d'"
                                @change="updateValue('date_format', 'Y-m-d')"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ formatDate(new Date(), 'Y-m-d') }} (Y-m-d)
                            </span>
                        </label>
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                :value="'m/d/Y'"
                                :checked="getSettingValue('date_format') === 'm/d/Y'"
                                @change="updateValue('date_format', 'm/d/Y')"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ formatDate(new Date(), 'm/d/Y') }} (m/d/Y)
                            </span>
                        </label>
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                :value="'d/m/Y'"
                                :checked="getSettingValue('date_format') === 'd/m/Y'"
                                @change="updateValue('date_format', 'd/m/Y')"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ formatDate(new Date(), 'd/m/Y') }} (d/m/Y)
                            </span>
                        </label>
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                :value="'F j, Y'"
                                :checked="getSettingValue('date_format') === 'F j, Y'"
                                @change="updateValue('date_format', 'F j, Y')"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ formatDate(new Date(), 'F j, Y') }} (F j, Y)
                            </span>
                        </label>
                    </div>
                    <div class="mt-2">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                :value="'custom'"
                                :checked="!['Y-m-d', 'm/d/Y', 'd/m/Y', 'F j, Y'].includes(getSettingValue('date_format'))"
                                @change="showCustomDateFormat = true"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Custom:</span>
                            <input
                                v-if="showCustomDateFormat || !['Y-m-d', 'm/d/Y', 'd/m/Y', 'F j, Y'].includes(getSettingValue('date_format'))"
                                :value="getSettingValue('date_format')"
                                @input="updateValue('date_format', ($event.target as HTMLInputElement).value)"
                                type="text"
                                class="ml-2 flex-1 px-3 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                                placeholder="Y-m-d"
                            />
                        </label>
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('date_format') }}
                </p>
            </div>

            <!-- Time Format -->
            <div>
                <label for="time_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Time Format
                </label>
                <div class="space-y-2">
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                :value="'H:i'"
                                :checked="getSettingValue('time_format') === 'H:i'"
                                @change="updateValue('time_format', 'H:i')"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ formatTime(new Date(), 'H:i') }} (24-hour format)
                            </span>
                        </label>
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                :value="'g:i A'"
                                :checked="getSettingValue('time_format') === 'g:i A'"
                                @change="updateValue('time_format', 'g:i A')"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ formatTime(new Date(), 'g:i A') }} (12-hour format)
                            </span>
                        </label>
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('time_format') }}
                </p>
            </div>

            <!-- Week Starts On -->
            <div>
                <label for="week_starts_on" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Week Starts On
                </label>
                <select
                    id="week_starts_on"
                    :value="getSettingValue('week_starts_on')"
                    @change="updateValue('week_starts_on', ($event.target as HTMLSelectElement).value)"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="0">Sunday</option>
                    <option value="1">Monday</option>
                    <option value="2">Tuesday</option>
                    <option value="3">Wednesday</option>
                    <option value="4">Thursday</option>
                    <option value="5">Friday</option>
                    <option value="6">Saturday</option>
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ getSettingDescription('week_starts_on') }}
                </p>
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
                        Saving...
                    </span>
                    <span v-else>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

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
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const showCustomDateFormat = ref(false);

const getSettingValue = (key: string): any => {
    return props.settings[key]?.value ?? '';
};

const getSettingDescription = (key: string): string => {
    return props.settings[key]?.description ?? '';
};

const updateValue = (key: string, value: any) => {
    emit('update', 'general', key, value);
};

const formatDate = (date: Date, format: string): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    return format
        .replace('Y', String(year))
        .replace('m', month)
        .replace('d', day)
        .replace('F', monthNames[date.getMonth()])
        .replace('j', String(date.getDate()));
};

const formatTime = (date: Date, format: string): string => {
    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    
    if (format === 'H:i') {
        return `${String(hours).padStart(2, '0')}:${minutes}`;
    } else {
        hours = hours % 12;
        hours = hours ? hours : 12;
        return `${hours}:${minutes} ${ampm}`;
    }
};

const openMediaPicker = () => {
    // TODO: Implement media picker
    console.log('Media picker not implemented yet');
};
</script>
