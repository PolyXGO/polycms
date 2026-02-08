<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t('Add Language') }}</h3>
    
    <form @submit.prevent="submit">
      <!-- Language Selector with Search -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('Select Language') }}</label>
        <LanguageSelector 
          :exclude="existingCodes" 
          @select="onLanguageSelect"
        />
      </div>

      <div class="grid grid-cols-1 gap-4">
        <FormField name="name" :label="t('Name')" required>
          <FormInput v-model="form.name" name="name" required :placeholder="t('English')" />
        </FormField>

        <FormField name="native_name" :label="t('Native Name')" required>
          <FormInput v-model="form.native_name" name="native_name" required :placeholder="t('English')" />
        </FormField>

        <div class="grid grid-cols-2 gap-4">
          <FormField name="code" :label="t('Code')" required>
            <FormInput v-model="form.code" name="code" required :placeholder="t('en')" maxlength="10" />
          </FormField>

          <FormField name="direction" :label="t('Direction')" required>
            <FormSelect v-model="form.direction" name="direction" :options="[{value: 'ltr', label: 'LTR'}, {value: 'rtl', label: 'RTL'}]" />
          </FormField>
        </div>
      </div>

      <div class="mt-6">
        <button 
          type="submit" 
          class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          :disabled="loading"
        >
          <span v-if="loading">{{ t('Adding...') }}</span>
          <span v-else>{{ t('Add Language') }}</span>
        </button>
      </div>
    </form>

    <!-- Internationalization & SEO Info -->
    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
      <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-900/30 rounded-md text-xs text-indigo-800 dark:text-indigo-200">
        <div class="flex">
          <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="font-semibold mb-1">{{ t('International Standards (i18n) & SEO') }}</p>
            <div class="space-y-2 opacity-90 text-[11px] leading-relaxed">
              <p>
                <strong>Code:</strong> {{ t('Locale format (e.g., en_US, vi_VN). Used for region-specific currency, date formatting, and system-level multi-language handling in E-commerce.') }}
              </p>
              <p>
                <strong>Lang:</strong> {{ t('ISO base language code (e.g., en, vi). Injected into the HTML "lang" attribute to assist search engines (SEO) and screen readers in identifying the main language.') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useValidation } from '@/admin/composables/useValidation';
import { useDialog } from '@/admin/composables/useDialog';
import { useTranslation } from '@/admin/composables/useTranslation';
import FormField from '@/admin/components/forms/FormField.vue';
import FormInput from '@/admin/components/forms/FormInput.vue';
import FormSelect from '@/admin/components/forms/FormSelect.vue';
import LanguageSelector from './LanguageSelector.vue';
import type { LanguagePreset } from '../../data/allLanguages';
import axios from 'axios';

const props = defineProps<{
  existingLanguages?: any[];
}>();

const emit = defineEmits(['created']);
const { validateForm, setErrors } = useValidation();
const dialog = useDialog();
const { t } = useTranslation();

const loading = ref(false);

const existingCodes = computed(() => {
  return props.existingLanguages?.map(lang => lang.code) || [];
});

const form = reactive({
  code: '',
  lang: '',
  name: '',
  native_name: '',
  direction: 'ltr',
  is_active: true
});

const onLanguageSelect = (lang: LanguagePreset) => {
  form.code = lang.code;
  form.lang = lang.lang;
  form.name = lang.name;
  form.native_name = lang.nativeName;
  form.direction = lang.direction;
};

const submit = async () => {
  if (!validateForm(form, {
    code: 'required|min:2' as any,
    name: 'required' as any,
    native_name: 'required' as any,
    direction: 'required' as any
  })) return;

  loading.value = true;
  try {
    const response = await axios.post('/api/v1/languages', form);
    dialog.success(response.data.message);
    emit('created', response.data.data);
    
    // Reset form
    form.code = '';
    form.name = '';
    form.native_name = '';
    form.direction = 'ltr';
  } catch (error: any) {
    if (error.response?.data?.errors) {
      setErrors(error.response.data.errors);
    } else {
      dialog.error(error.response?.data?.message || 'Failed to add language');
    }
  } finally {
    loading.value = false;
  }
};
</script>
