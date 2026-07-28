<template>
  <div class="space-y-4">
    <p class="text-sm text-admin-theme-text-secondary">
      {{ t('Enter a new role name (leave blank to auto-generate):') }}
    </p>

    <FormField
      name="cloneName"
      :required="false"
      :error="validationErrors.cloneName"
    >
      <FormInput
        v-model="cloneName"
        name="cloneName"
        type="text"
        :placeholder="role.label + ' Copy'"
        class="w-full"
      />
    </FormField>

    <div class="flex justify-end space-x-3 mt-6">
      <button
        type="button"
        @click="handleCancel"
        class="px-4 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-surface text-admin-theme-text-secondary hover:bg-admin-theme-base transition-colors"
      >
        {{ t('Cancel') }}
      </button>
      <button
        type="button"
        @click="handleClone"
        :disabled="loading"
        class="px-4 py-2 bg-admin-theme-primary hover:bg-admin-theme-primary-hover text-white rounded-lg transition-colors flex items-center"
      >
        <span v-if="loading" class="mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
        {{ t('Clone') }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useTranslation } from '@/admin/composables/useTranslation';
import FormField from '@/admin/components/forms/FormField.vue';
import FormInput from '@/admin/components/forms/FormInput.vue';
import { useDialog } from '@/admin/composables/useDialog';

const props = defineProps<{
  role: {
    id: number;
    name: string;
    label: string;
  };
  onSuccess?: (clonedRole?: any) => void;
  onCancel?: () => void;
}>();

const { t } = useTranslation();
const dialog = useDialog();

const cloneName = ref(`${props.role.label || props.role.name} Copy`);
const loading = ref(false);
const validationErrors = ref<Record<string, string>>({});

const handleCancel = () => {
  if (props.onCancel) {
    props.onCancel();
  }
};

const handleClone = async () => {
  loading.value = true;
  validationErrors.value = {};
  
  try {
    const name = cloneName.value.trim();
    const response = await axios.post(`/api/v1/roles/${props.role.id}/clone`, name ? { name } : {});
    const clonedRole = response.data?.data;
    dialog.success(t('Role cloned successfully'));
    if (props.onSuccess) {
      props.onSuccess(clonedRole);
    }
  } catch (error: any) {
    console.error('Failed to clone role:', error);
    const message = error.response?.data?.error?.message || t('Failed to clone role.');
    dialog.error(message);
  } finally {
    loading.value = false;
  }
};
</script>
