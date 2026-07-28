<template>
  <div v-if="form" class="space-y-3">
    <!-- Current Language -->
    <div class="flex items-center justify-between pb-2 border-b border-admin-theme-border/60">
      <span class="text-xs font-semibold text-admin-theme-text-secondary">
        {{ t('Current Language') || 'Ngôn ngữ hiện tại' }}:
      </span>
      <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-admin-theme-primary/10 text-admin-theme-primary border border-admin-theme-primary/20 flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-admin-theme-primary"></span>
        {{ currentLanguageLabel }}
      </span>
    </div>

    <!-- Active Languages List -->
    <div class="space-y-1.5">
      <span class="text-[10px] font-bold uppercase tracking-wider text-admin-theme-text-muted">
        {{ t('Translations') || 'Bản dịch' }}
      </span>
      
      <div v-if="loadingLanguages" class="flex items-center justify-center py-4 space-x-2">
        <svg class="w-4 h-4 animate-spin text-admin-theme-primary" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-xs text-admin-theme-text-muted">{{ t('Loading...') || 'Đang tải...' }}</span>
      </div>

      <div v-else class="divide-y divide-admin-theme-border/40">
        <div 
          v-for="lang in activeLanguages" 
          :key="lang.code"
          class="flex items-center justify-between py-2 transition-all duration-150"
        >
          <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-admin-theme-text">
              {{ lang.name }}
            </span>
            <span class="text-[9px] uppercase font-bold text-admin-theme-text-muted bg-admin-theme-border/60 px-1.5 py-0.5 rounded font-mono">
              {{ lang.code }}
            </span>
          </div>

          <!-- Actions based on status -->
          <div class="flex items-center">
            <!-- If same as current page locale -->
            <span 
              v-if="lang.code === currentLocale"
              class="text-xs font-semibold text-admin-theme-text-muted italic flex items-center gap-1"
            >
              <svg class="w-3.5 h-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              {{ t('Active') || 'Đang sửa' }}
            </span>

            <!-- If translation exists, provide edit link -->
            <button
              v-else-if="getTranslationFor(lang.code)"
              type="button"
              @click="editTranslation(getTranslationFor(lang.code).id)"
              class="flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-admin-theme-primary border border-admin-theme-primary/30 rounded-lg hover:bg-admin-theme-primary hover:text-admin-theme-primary-content transition-all duration-150 shadow-sm"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              {{ t('Edit') || 'Sửa' }}
            </button>

            <!-- If translation does not exist, provide create/duplicate link -->
            <button
              v-else-if="isEdit"
              type="button"
              :disabled="duplicating === lang.code"
              @click="createTranslation(lang.code)"
              class="flex items-center gap-1 px-2.5 py-1 text-xs font-semibold border border-admin-theme-border/60 rounded-lg text-admin-theme-text hover:bg-admin-theme-primary hover:border-admin-theme-primary hover:text-admin-theme-primary-content transition-all duration-150 shadow-sm"
            >
              <svg 
                v-if="duplicating === lang.code" 
                class="w-3.5 h-3.5 animate-spin text-current" 
                fill="none" 
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg 
                v-else 
                class="w-3.5 h-3.5" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor" 
                stroke-width="2"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
              {{ t('Translate') || 'Dịch' }}
            </button>

            <span 
              v-else 
              class="text-xs text-admin-theme-text-muted italic"
              title="Save this record first to enable translations"
            >
              {{ t('Save first') || 'Lưu trước' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, inject, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { EditorContextKey } from '../../../../editor/context';
import { useDialog } from '../../../../composables/useDialog';
import { useTranslation } from '../../../../composables/useTranslation';

const context = inject(EditorContextKey);
if (!context) {
  throw new Error('LanguageSelectorPanel must be used within editor context');
}

const form = context.form;
const route = useRoute();
const router = useRouter();
const dialog = useDialog();
const { t } = useTranslation();

const activeLanguages = ref<any[]>([]);
const loadingLanguages = ref(false);
const duplicating = ref<string | null>(null);

// Get current record details
const isEdit = computed(() => !!route.params.id);
const currentLocale = computed(() => form.value.locale || 'en');

const currentLanguageLabel = computed(() => {
  const match = activeLanguages.value.find(l => l.code === currentLocale.value);
  return match ? `${match.name} (${match.code.toUpperCase()})` : currentLocale.value.toUpperCase();
});

// Fetch active languages
const fetchActiveLanguages = async () => {
  loadingLanguages.value = true;
  try {
    const response = await axios.get('/api/v1/languages');
    if (response.data?.success) {
      activeLanguages.value = response.data.data.filter((l: any) => l.is_active);
    }
  } catch (error) {
    console.error('Failed to load active languages:', error);
  } finally {
    loadingLanguages.value = false;
  }
};

// Check if a translation exists for this locale
const getTranslationFor = (locale: string) => {
  if (!form.value || !Array.isArray(form.value.translations)) {
    return null;
  }
  return form.value.translations.find((trans: any) => trans.locale === locale);
};

// Navigate to edit translation
const editTranslation = (id: number) => {
  const type = context.type;
  let name = 'admin.posts.edit';
  
  if (type === 'page') {
    name = 'admin.pages.edit';
  } else if (type === 'product') {
    name = 'admin.products.edit';
  } else if (type === 'category') {
    name = 'admin.categories.edit';
  } else if (type === 'product-category') {
    name = 'admin.product-categories.edit';
  } else if (type === 'product-brand') {
    name = 'admin.product-brands.edit';
  } else if (type === 'project') {
    name = 'admin.project-hub.edit';
  }

  router.push({
    name,
    params: { id },
    query: { type }
  });
};

// Create a duplicate translation for target locale
const createTranslation = async (targetLocale: string) => {
  if (!isEdit.value || !route.params.id) {
    return;
  }

  duplicating.value = targetLocale;
  const type = context.type;
  
  // Resolve correct API base endpoint
  let apiEndpoint = '/api/v1/posts';
  if (type === 'product') {
    apiEndpoint = '/api/v1/products';
  } else if (type === 'category') {
    apiEndpoint = '/api/v1/categories';
  } else if (type === 'product-category') {
    apiEndpoint = '/api/v1/product-categories';
  } else if (type === 'product-brand') {
    apiEndpoint = '/api/v1/product-brands';
  } else if (type === 'project') {
    apiEndpoint = '/api/v1/projects';
  }

  try {
    const response = await axios.post(`${apiEndpoint}/${route.params.id}/translate`, {
      locale: targetLocale
    });

    dialog.success(t('Translation created successfully') || 'Tạo bản dịch thành công');
    
    // Redirect to edit the new translation
    if (response.data?.data?.id) {
      editTranslation(response.data.data.id);
    }
  } catch (error: any) {
    console.error('Failed to duplicate translation:', error);
    const message = error.response?.data?.message || t('Failed to create translation');
    dialog.error(message);
  } finally {
    duplicating.value = null;
  }
};

onMounted(() => {
  fetchActiveLanguages();
});
</script>
