<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Language') }}</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Code') }}</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Status') }}</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('Default') }}</th>
            <th scope="col" class="relative px-6 py-3">
              <span class="sr-only">{{ t('Actions') }}</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="lang in languages" :key="lang.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="ml-0">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ lang.name }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ lang.native_name }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                {{ lang.code }} ({{ lang.direction.toUpperCase() }})
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
               <button 
                @click="toggleActive(lang)"
                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                :class="[lang.is_active ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600']"
                :disabled="lang.is_default"
              >
                <span class="sr-only">{{ t('Use setting') }}</span>
                <span 
                  class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                  :class="[lang.is_active ? 'translate-x-5' : 'translate-x-0']"
                ></span>
              </button>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
               <span v-if="lang.is_default" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  {{ t('Default') }}
                </span>
                <button v-else @click="setDefault(lang)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs">
                  {{ t('Set Default') }}
                </button>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
               <div class="flex justify-end space-x-3">
                  <router-link :to="`/admin/settings/languages/${lang.id}/translations`" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400" :title="t('Edit Translations')">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                  </router-link>
                  <button v-if="!lang.is_default" @click="sync(lang)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400" :title="t('Sync keys from default')">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                      </svg>
                  </button>
                  <button @click="downloadLanguage(lang)" class="text-gray-600 hover:text-gray-900 dark:text-gray-400" :title="t('Download Translations')">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                      </svg>
                  </button>
                  <button @click="$emit('upload', lang)" class="text-gray-600 hover:text-gray-900 dark:text-gray-400" :title="t('Upload Translations')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                  </button>
                  <button v-if="!lang.is_default" @click="confirmDelete(lang)" class="text-red-600 hover:text-red-900 dark:text-red-400" :title="t('Delete')">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                  </button>
               </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useDialog } from '@/admin/composables/useDialog';
import { useTranslation } from '@/admin/composables/useTranslation';
import axios from 'axios';

const props = defineProps<{
  languages: any[];
}>();

const emit = defineEmits(['updated', 'deleted', 'upload']);
const dialog = useDialog();
const { t } = useTranslation();

// Get API token from meta or storage if needed for download link
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''; 

const toggleActive = async (lang: any) => {
    if (lang.is_default) return;
    
    try {
        await axios.put(`/api/v1/languages/${lang.id}`, {
            is_active: !lang.is_active
        });
        emit('updated');
        dialog.success('Status updated');
    } catch (e) {
        dialog.error('Failed to update status');
    }
};

const setDefault = async (lang: any) => {
    try {
        await axios.put(`/api/v1/languages/${lang.id}`, {
            is_default: true
        });
        emit('updated');
        dialog.success(`Default language set to ${lang.name}`);
    } catch (e) {
        dialog.error('Failed to set default language');
    }
};

const sync = async (lang: any) => {
    try {
        const response = await axios.post(`/api/v1/languages/${lang.id}/sync`);
        dialog.success(response.data.message);
    } catch (e: any) {
        dialog.error(e.response?.data?.message || 'Sync failed');
    }
};

const confirmDelete = async (lang: any) => {
    const confirmed = await dialog.confirm(
        `Are you sure you want to delete ${lang.name}? This action cannot be undone.`
    );

    if (confirmed) {
        try {
            await axios.delete(`/api/v1/languages/${lang.id}`);
            emit('deleted');
            dialog.success('Language deleted');
        } catch (e: any) {
            dialog.error(e.response?.data?.message || 'Delete failed');
        }
    }
};

const downloadLanguage = async (lang: any) => {
    try {
        const response = await axios.get(`/api/v1/languages/${lang.id}/download`, {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `${lang.code}.json`);
        document.body.appendChild(link);
        link.click();
        
        // Cleanup
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (e: any) {
        dialog.error(e.response?.data?.message || 'Download failed');
    }
};
</script>
