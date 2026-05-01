<template>
  <div class="space-y-6">
    <div class="mb-6">
      <div class="mb-2 flex items-center gap-4">
          <router-link :to="{ name: 'admin.settings.index' }" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center text-sm">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              {{ t('Back to Hub') }}
          </router-link>
      </div>
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            {{ t('Language Settings') }}
          </h2>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Panel: Add Language -->
      <div class="lg:col-span-1">
        <AddLanguageForm 
            :existing-languages="languages" 
            @created="fetchLanguages" 
        />
      </div>

      <!-- Right Panel: List -->
      <div class="lg:col-span-2">
        <LanguageList 
            :languages="languages" 
            @updated="fetchLanguages" 
            @deleted="fetchLanguages"
            @upload="openUploadModal"
        />
      </div>
    </div>

    <!-- Hidden Upload Input -->
    <input 
        type="file" 
        ref="fileInput" 
        accept=".zip,.json" 
        class="hidden" 
        @change="handleFileUpload"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AddLanguageForm from '@/admin/components/languages/AddLanguageForm.vue';
import LanguageList from '@/admin/components/languages/LanguageList.vue';
import { useDialog } from '@/admin/composables/useDialog';
import { useTranslation } from '@/admin/composables/useTranslation';
import axios from 'axios';

const languages = ref([]);
const fileInput = ref<HTMLInputElement | null>(null);
const uploadTargetLang = ref<any>(null);
const dialog = useDialog();
const { t } = useTranslation();

const fetchLanguages = async () => {
  try {
    const response = await axios.get('/api/v1/languages');
    languages.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch languages', error);
  }
};

const openUploadModal = (lang: any) => {
    uploadTargetLang.value = lang;
    if (fileInput.value) {
        fileInput.value.value = ''; // Reset
        fileInput.value.click();
    }
};

const handleFileUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files || target.files.length === 0 || !uploadTargetLang.value) return;

    const file = target.files[0];
    const formData = new FormData();
    formData.append('file', file);

    const loadingToast = dialog.info('Uploading...'); // This might return a dismiss function

    try {
        await axios.post(`/api/v1/languages/${uploadTargetLang.value.id}/upload`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        // cleanup toast if needed, useDialog.ts doesn't show clear implementation of dismiss for info but returns a function
        if (typeof loadingToast === 'function') loadingToast();
        
        dialog.success(`Translations uploaded for ${uploadTargetLang.value.name}`);
    } catch (error: any) {
        if (typeof loadingToast === 'function') loadingToast();
        dialog.error(error.response?.data?.message || 'Upload failed');
    }
};

onMounted(() => {
  fetchLanguages();
});
</script>
