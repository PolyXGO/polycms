<template>
  <div class="space-y-4">
    <!-- Loading state -->
    <div v-if="loading" class="py-12 flex flex-col items-center justify-center space-y-3">
      <span class="h-8 w-8 border-4 border-admin-theme-primary border-t-transparent rounded-full animate-spin"></span>
      <p class="text-sm text-admin-theme-text-muted">{{ t('Loading products...') }}</p>
    </div>

    <!-- Main Form -->
    <div v-else class="space-y-4">
      <!-- Product Dropdown -->
      <FormField
        name="productId"
        :label="t('Related Product') || 'Related Product'"
        :required="form.elementType !== 'pulse_chart'"
        :error="errors.productId"
      >
        <select
          v-model="form.productId"
          id="productId"
          class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text hover:bg-admin-theme-base focus:ring-1 focus:ring-admin-theme-primary cursor-pointer"
        >
          <option value="">-- {{ t('Select Product') }} --</option>
          <option v-for="product in products" :key="product.id" :value="product.id">
            {{ product.name }} (ID: {{ product.id }})
          </option>
        </select>
      </FormField>

      <!-- Element Type Selector -->
      <FormField
        name="elementType"
        :label="t('Element Type') || 'Element Type'"
        :required="true"
      >
        <select
          v-model="form.elementType"
          id="elementType"
          class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text hover:bg-admin-theme-base focus:ring-1 focus:ring-admin-theme-primary cursor-pointer"
        >
          <option value="roadmap_changelog">{{ t('Roadmap & Changelog Block') }}</option>
          <option value="release_banner">{{ t('Latest Release Banner Sentence') }}</option>
          <option value="pulse_chart">{{ t('Project Development Pulse Chart') || 'Project Development Pulse Chart' }}</option>
        </select>
      </FormField>

      <!-- Limit Input (only for roadmap_changelog) -->
      <FormField
        v-if="form.elementType === 'roadmap_changelog'"
        name="limit"
        :label="t('Max Releases to Display') || 'Max Releases to Display'"
        :required="true"
        :error="errors.limit"
      >
        <FormInput
          v-model.number="form.limit"
          name="limit"
          type="number"
          min="1"
          max="50"
        />
      </FormField>

      <!-- Footer Buttons -->
      <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-admin-theme-border">
        <button
          type="button"
          @click="handleCancel"
          class="px-4 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-surface text-admin-theme-text-secondary hover:bg-admin-theme-base transition-colors"
        >
          {{ t('Cancel') }}
        </button>
        <button
          type="button"
          @click="handleInsert"
          class="px-4 py-2 bg-admin-theme-primary hover:bg-admin-theme-primary-hover text-white rounded-lg transition-colors flex items-center"
        >
          {{ t('Insert') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useTranslation } from '@/admin/composables/useTranslation';
import FormField from '@/admin/components/forms/FormField.vue';
import FormInput from '@/admin/components/forms/FormInput.vue';

const props = defineProps<{
  editor: any;
}>();

const emit = defineEmits(['close']);

const { t } = useTranslation();

const loading = ref(true);
const products = ref<any[]>([]);
const errors = ref<Record<string, string>>({});

const form = ref({
  productId: '',
  elementType: 'roadmap_changelog',
  limit: 5
});

onMounted(async () => {
  try {
    const response = await axios.get('/api/v1/products', {
      params: {
        per_page: 100,
        compact: 1,
        primary_locale: 1
      }
    });
    products.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to load products for editor picker:', error);
  } finally {
    loading.value = false;
  }
});

const handleCancel = () => {
  emit('close');
};

const handleInsert = () => {
  errors.value = {};
  
  if (!form.value.productId && form.value.elementType !== 'pulse_chart') {
    errors.value.productId = t('Please select a product');
    return;
  }

  if (props.editor) {
    const blockType = form.value.elementType === 'roadmap_changelog' 
      ? 'project_hub_roadmap' 
      : (form.value.elementType === 'pulse_chart' ? 'project_hub_chart' : 'project_hub_release_banner');
      
    props.editor.chain().focus().setLandingBlock({
      type: blockType,
      data: {
        product_id: form.value.productId || undefined,
        limit: form.value.elementType === 'roadmap_changelog' ? (parseInt(String(form.value.limit)) || 5) : undefined,
        style: form.value.elementType === 'release_banner' ? 'text' : undefined
      }
    }).run();
  }

  emit('close');
};
</script>
