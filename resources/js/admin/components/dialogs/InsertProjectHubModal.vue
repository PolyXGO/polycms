<template>
  <div class="space-y-4">
    <!-- Loading state -->
    <div v-if="loading" class="py-12 flex flex-col items-center justify-center space-y-3">
      <span class="h-8 w-8 border-4 border-admin-theme-primary border-t-transparent rounded-full animate-spin"></span>
      <p class="text-sm text-admin-theme-text-muted">{{ t('Loading Project Hub data...') }}</p>
    </div>

    <!-- Main Form -->
    <div v-else class="space-y-4">
      
      <!-- Element Type Selector -->
      <FormField
        name="elementType"
        :label="t('Element Type') || 'Element Type'"
        :required="true"
      >
        <select
          v-model="form.elementType"
          id="elementType"
          class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text hover:bg-admin-theme-base focus:ring-1 focus:ring-admin-theme-primary cursor-pointer font-semibold"
          @change="onElementTypeChange"
        >
          <option value="mermaid_diagram">{{ t('Mermaid Flowchart & Diagram') || 'Mermaid Flowchart & Diagram' }}</option>
          <option value="roadmap_changelog">{{ t('Roadmap & Changelog Block') }}</option>
          <option value="release_banner">{{ t('Latest Release Banner Sentence') }}</option>
          <option value="pulse_chart">{{ t('Project Development Pulse Chart') }}</option>
        </select>
      </FormField>

      <!-- MERMAID DIAGRAM SELECTOR MODE -->
      <template v-if="form.elementType === 'mermaid_diagram'">
        <!-- Project Selector -->
        <FormField
          name="projectId"
          :label="t('Select Project') || 'Select Project'"
          :required="true"
          :error="errors.projectId"
        >
          <select
            v-model="form.projectId"
            id="projectId"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text hover:bg-admin-theme-base focus:ring-1 focus:ring-admin-theme-primary cursor-pointer"
            @change="onProjectSelectChange"
          >
            <option value="">-- {{ t('Select Project') }} --</option>
            <option v-for="proj in projects" :key="proj.id" :value="proj.id">
              {{ proj.name }} ({{ proj.diagrams?.length || 0 }} diagrams)
            </option>
          </select>
        </FormField>

        <!-- Diagram Selector -->
        <FormField
          name="diagramCode"
          :label="t('Select Diagram') || 'Select Diagram'"
          :required="true"
          :error="errors.diagramCode"
        >
          <select
            v-model="form.diagramCode"
            id="diagramCode"
            class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text hover:bg-admin-theme-base focus:ring-1 focus:ring-admin-theme-primary cursor-pointer font-medium"
            :disabled="!availableDiagrams.length"
          >
            <option value="">-- {{ availableDiagrams.length ? t('Select Diagram') : t('No diagrams in project') }} --</option>
            <option v-for="d in availableDiagrams" :key="d.code || d.id" :value="d.code">
              {{ d.title }} [code: {{ d.code }}] ({{ (d.type || 'flowchart').toUpperCase() }})
            </option>
          </select>
        </FormField>

        <!-- Shortcode Preview -->
        <div v-if="form.diagramCode" class="p-3 rounded-lg border border-indigo-500/20 bg-indigo-500/5 space-y-1">
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-admin-theme-text">Generated Shortcode:</span>
            <span class="font-mono text-indigo-500 font-bold">[project_diagram code="{{ form.diagramCode }}"]</span>
          </div>
          <p class="text-[11px] text-admin-theme-text-muted">
            Will insert interactive Mermaid.js diagram block into your content.
          </p>
        </div>
      </template>

      <!-- PRODUCT-BASED ELEMENTS MODE (Roadmap, Release Banner, Pulse Chart) -->
      <template v-else>
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
      </template>

      <!-- Footer Buttons -->
      <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-admin-theme-border">
        <button
          type="button"
          @click="handleCancel"
          class="px-4 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-surface text-admin-theme-text-secondary hover:bg-admin-theme-base transition-colors text-xs font-semibold"
        >
          {{ t('Cancel') }}
        </button>
        <button
          type="button"
          @click="handleInsert"
          class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center gap-1.5 text-xs font-bold shadow-sm"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ t('Insert Element') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useTranslation } from '@/admin/composables/useTranslation';
import FormField from '@/admin/components/forms/FormField.vue';
import FormInput from '@/admin/components/forms/FormInput.vue';

const props = defineProps<{
  editor: any;
}>();

const emit = defineEmits(['close']);

const route = useRoute();
const { t } = useTranslation();

const loading = ref(true);
const products = ref<any[]>([]);
const projects = ref<any[]>([]);
const errors = ref<Record<string, string>>({});

const form = ref({
  elementType: 'mermaid_diagram',
  productId: '',
  projectId: '',
  diagramCode: '',
  limit: 5,
});

const selectedProject = computed(() => {
  if (!form.value.projectId) return null;
  return projects.value.find((p) => String(p.id) === String(form.value.projectId));
});

const availableDiagrams = computed(() => {
  if (!selectedProject.value || !Array.isArray(selectedProject.value.diagrams)) return [];
  return selectedProject.value.diagrams;
});

const onElementTypeChange = () => {
  errors.value = {};
};

const onProjectSelectChange = () => {
  errors.value = {};
  if (availableDiagrams.value.length > 0) {
    form.value.diagramCode = availableDiagrams.value[0].code || '';
  } else {
    form.value.diagramCode = '';
  }
};

onMounted(async () => {
  try {
    const [prodRes, projRes] = await Promise.all([
      axios.get('/api/v1/products', {
        params: { per_page: 100, compact: 1, primary_locale: 1 },
      }),
      axios.get('/api/v1/projects', {
        params: { per_page: 100 },
      }),
    ]);

    products.value = prodRes.data.data || [];
    projects.value = projRes.data.data?.data || projRes.data.data || projRes.data || [];

    // Check if we are currently editing a project in ProjectHub (e.g. /admin/project-hub/4/edit)
    const currentEditingId = route.params.id ? String(route.params.id) : null;
    if (currentEditingId) {
      const matchProj = projects.value.find((p) => String(p.id) === currentEditingId);
      if (matchProj) {
        form.value.projectId = String(matchProj.id);
        onProjectSelectChange();
      }
    } else if (projects.value.length > 0) {
      // Default to first project
      form.value.projectId = String(projects.value[0].id);
      onProjectSelectChange();
    }
  } catch (error) {
    console.error('Failed to load Project Hub elements for picker:', error);
  } finally {
    loading.value = false;
  }
});

const handleCancel = () => {
  emit('close');
};

const handleInsert = () => {
  errors.value = {};

  if (form.value.elementType === 'mermaid_diagram') {
    if (!form.value.diagramCode) {
      errors.value.diagramCode = t('Please select a diagram');
      return;
    }

    if (props.editor) {
      const projCode = selectedProject.value?.code || selectedProject.value?.slug || form.value.projectId || '';

      props.editor.chain().focus().insertContent({
        type: 'landingBlock',
        attrs: {
          blockType: 'project_diagram',
          blockData: {
            project: projCode,
            code: form.value.diagramCode
          }
        }
      }).run();
    }
    emit('close');
    return;
  }

  if (!form.value.productId && form.value.elementType !== 'pulse_chart') {
    errors.value.productId = t('Please select a product');
    return;
  }

  if (props.editor) {
    const blockType =
      form.value.elementType === 'roadmap_changelog'
        ? 'project_hub_roadmap'
        : form.value.elementType === 'pulse_chart'
        ? 'project_hub_chart'
        : 'project_hub_release_banner';

    props.editor.chain().focus().setLandingBlock({
      type: blockType,
      data: {
        product_id: form.value.productId || undefined,
        limit: form.value.elementType === 'roadmap_changelog' ? parseInt(String(form.value.limit)) || 5 : undefined,
        style: form.value.elementType === 'release_banner' ? 'text' : undefined,
      },
    }).run();
  }

  emit('close');
};
</script>
