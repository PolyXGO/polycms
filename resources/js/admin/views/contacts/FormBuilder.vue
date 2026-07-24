<template>
  <div class="form-builder-container">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-admin-theme-text">
          {{ isEdit ? t('Edit Contact Form') : t('Create Contact Form') }}
        </h1>
        <p class="text-sm text-admin-theme-text-secondary mt-1">
          {{ t('Design and configure fields for your form') }}
        </p>
      </div>
      <div class="flex gap-3">
        <router-link
          :to="{ name: 'admin.contacts.forms' }"
          class="px-4 py-2 border border-admin-theme-border text-admin-theme-text rounded-lg hover:bg-admin-theme-base/10 transition-colors font-medium text-sm"
        >
          {{ t('Cancel') }}
        </router-link>
        <button
          @click="saveForm"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 shadow-sm font-medium text-sm flex items-center gap-2"
          :disabled="saving"
        >
          <span v-if="saving">{{ t('Saving...') }}</span>
          <span v-else>{{ t('Save Form') }}</span>
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <!-- Form Properties & Fields Builder (Left Column) -->
      <div class="lg:col-span-7 space-y-6">
        <!-- Basic Settings Card -->
        <div class="bg-admin-theme-surface rounded-xl border border-admin-theme-border p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">{{ t('Form Details') }}</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Form Name') }} *</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:border-blue-500"
                :placeholder="t('e.g. Newsletter Signup')"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Form Slug') }}</label>
              <input
                v-model="form.slug"
                type="text"
                class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:border-blue-500 font-mono text-sm"
                :placeholder="t('auto-generated if empty')"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Form Type') }}</label>
              <select
                v-model="form.type"
                class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:outline-none focus:border-blue-500"
              >
                <option value="contact">{{ t('Contact Form') }}</option>
                <option value="newsletter">{{ t('Newsletter Form') }}</option>
                <option value="feedback">{{ t('Feedback Form') }}</option>
                <option value="custom">{{ t('Custom Form') }}</option>
              </select>
            </div>
            <div class="flex items-center mt-6">
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:bg-gray-700 peer-checked:bg-blue-600"></div>
                <span class="ml-3 text-sm font-medium text-admin-theme-text-secondary">{{ t('Active') }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Form Fields Builder Card -->
        <div class="bg-admin-theme-surface rounded-xl border border-admin-theme-border p-6 shadow-sm">
          <div class="flex justify-between items-center mb-4 border-b border-admin-theme-border pb-2">
            <h2 class="text-lg font-semibold text-admin-theme-text">{{ t('Form Fields') }}</h2>
            <button
              @click="addField"
              type="button"
              class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-1"
            >
              + {{ t('Add Field') }}
            </button>
          </div>

          <!-- Fields list -->
          <div class="space-y-4">
            <div
              v-for="(field, index) in form.fields"
              :key="index"
              class="border border-admin-theme-border rounded-xl p-4 bg-admin-theme-base/20 space-y-3 relative group"
            >
              <!-- Field index/delete header -->
              <div class="flex justify-between items-center">
                <span class="text-xs font-semibold text-admin-theme-text-muted uppercase tracking-wider">
                  {{ t('Field') }} #{{ index + 1 }}
                </span>
                <button
                  @click="removeField(index)"
                  type="button"
                  class="text-red-500 hover:text-red-700 text-xs font-medium"
                >
                  {{ t('Remove') }}
                </button>
              </div>

              <!-- Field detail inputs -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Field Label') }} *</label>
                  <input
                    v-model="field.label"
                    type="text"
                    class="w-full px-2.5 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:border-blue-500"
                    :placeholder="t('e.g. Email Address')"
                    @input="syncFieldName(index)"
                    required
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Field Key / Name') }} *</label>
                  <input
                    v-model="field.name"
                    type="text"
                    class="w-full px-2.5 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:border-blue-500 font-mono"
                    :placeholder="t('e.g. email')"
                    required
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Field Type') }}</label>
                  <select
                    v-model="field.type"
                    class="w-full px-2.5 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:border-blue-500"
                  >
                    <option value="text">{{ t('Short Text') }}</option>
                    <option value="email">{{ t('Email') }}</option>
                    <option value="textarea">{{ t('Long Text (Textarea)') }}</option>
                    <option value="tel">{{ t('Phone Number') }}</option>
                    <option value="number">{{ t('Number') }}</option>
                    <option value="checkbox">{{ t('Checkbox') }}</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Placeholder Text') }}</label>
                  <input
                    v-model="field.placeholder"
                    type="text"
                    class="w-full px-2.5 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm focus:outline-none focus:border-blue-500"
                    :placeholder="t('e.g. Enter your email...')"
                  />
                </div>
                <div class="flex items-center md:mt-5">
                  <label class="flex items-center gap-2 text-xs font-medium text-admin-theme-text-secondary cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="field.required"
                      class="rounded border-admin-theme-border text-blue-600 focus:ring-blue-500 w-4 h-4"
                    />
                    <span>{{ t('Required Field') }}</span>
                  </label>
                </div>
              </div>
            </div>

            <div v-if="form.fields.length === 0" class="text-center py-6 text-admin-theme-text-muted">
              {{ t('No fields defined yet. Click "Add Field" to start building your form.') }}
            </div>
          </div>
        </div>
      </div>

      <!-- Form Preview Panel (Right Column) -->
      <div class="lg:col-span-5">
        <div class="sticky top-6 bg-admin-theme-surface rounded-xl border border-admin-theme-border p-6 shadow-sm min-h-[400px] flex flex-col">
          <h2 class="text-lg font-semibold text-admin-theme-text mb-4 border-b border-admin-theme-border pb-2">
            {{ t('Live Form Preview') }}
          </h2>
          
          <div class="flex-1 border border-admin-theme-border border-dashed rounded-lg p-5 bg-admin-theme-base/10 flex flex-col justify-center">
            <div class="w-full max-w-sm mx-auto bg-admin-theme-surface rounded-xl border border-admin-theme-border p-6 shadow-md">
              <h3 class="text-lg font-bold text-admin-theme-text mb-4 text-center">
                {{ form.name || t('Form Preview') }}
              </h3>
              
              <div class="space-y-4">
                <div v-for="(field, idx) in form.fields" :key="idx" class="flex flex-col gap-1">
                  <label v-if="field.type !== 'checkbox'" class="text-xs font-semibold text-admin-theme-text-secondary">
                    {{ field.label || t('Label') }}
                    <span v-if="field.required" class="text-red-500 font-bold">*</span>
                  </label>
                  
                  <textarea
                    v-if="field.type === 'textarea'"
                    disabled
                    :placeholder="field.placeholder"
                    rows="3"
                    class="w-full px-3 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text-muted text-sm cursor-not-allowed"
                  ></textarea>
                  
                  <label v-else-if="field.type === 'checkbox'" class="flex items-center gap-2 text-xs font-semibold text-admin-theme-text-secondary cursor-not-allowed">
                    <input type="checkbox" disabled class="rounded border-admin-theme-border w-4 h-4 cursor-not-allowed">
                    <span>{{ field.label || t('Checkbox Label') }}</span>
                    <span v-if="field.required" class="text-red-500 font-bold">*</span>
                  </label>
                  
                  <input
                    v-else
                    :type="field.type"
                    disabled
                    :placeholder="field.placeholder"
                    class="w-full px-3 py-1.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text-muted text-sm cursor-not-allowed"
                  />
                </div>
                
                <button
                  type="button"
                  disabled
                  class="w-full py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold opacity-75 cursor-not-allowed"
                >
                  {{ t('Submit') }}
                </button>
              </div>
            </div>
          </div>
          
          <div class="mt-4 text-xs text-admin-theme-text-muted text-center leading-relaxed">
            {{ t('Preview is dynamically rendered based on the field options on the left.') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();
const router = useRouter();
const route = useRoute();

const isEdit = computed(() => !!route.params.id);
const saving = ref(false);

const form = ref<{
  name: string;
  slug: string;
  type: string;
  is_active: boolean;
  fields: Array<{
    name: string;
    type: string;
    label: string;
    placeholder: string;
    required: boolean;
  }>;
}>({
  name: '',
  slug: '',
  type: 'contact',
  is_active: true,
  fields: []
});

const loadForm = async () => {
  if (!isEdit.value) {
    // Populate default fields for new forms
    form.value.fields = [
      { name: 'name', type: 'text', label: t('Full Name'), placeholder: t('Your name...'), required: true },
      { name: 'email', type: 'email', label: t('Email Address'), placeholder: t('Your email...'), required: true },
      { name: 'message', type: 'textarea', label: t('Message'), placeholder: t('Write your message here...'), required: true }
    ];
    return;
  }
  
  try {
    const response = await axios.get(`/api/v1/contacts/forms/${route.params.id}`);
    const data = response.data;
    form.value = {
      name: data.name,
      slug: data.slug,
      type: data.type,
      is_active: data.is_active,
      fields: data.fields || []
    };
  } catch (e) {
    console.error(e);
    dialog.error(t('Failed to load form'));
    router.push({ name: 'admin.contacts.forms' });
  }
};

const addField = () => {
  form.value.fields.push({
    name: 'field_' + (form.value.fields.length + 1),
    type: 'text',
    label: t('New Field'),
    placeholder: '',
    required: false
  });
};

const removeField = (index: number) => {
  form.value.fields.splice(index, 1);
};

const syncFieldName = (index: number) => {
  const field = form.value.fields[index];
  if (field && field.label) {
    // Auto-generate key name from label if it was default
    const formatted = field.label
      .toLowerCase()
      .replace(/[^a-z0-9_]/g, '_')
      .replace(/_+/g, '_')
      .replace(/^_+|_+$/g, '');
    
    // Only update if it looks like a auto-generated field name
    if (field.name.startsWith('field_') || field.name === '') {
      field.name = formatted || 'field_' + (index + 1);
    }
  }
};

const saveForm = async () => {
  if (!form.value.name) {
    dialog.error(t('Form Name is required.'));
    return;
  }

  // Validate fields keys
  const keys = form.value.fields.map(f => f.name);
  const duplicateKeys = keys.filter((item, index) => keys.indexOf(item) !== index);
  if (duplicateKeys.length > 0) {
    dialog.error(t('Fields cannot have duplicate keys: ') + duplicateKeys.join(', '));
    return;
  }

  saving.value = true;
  try {
    if (isEdit.value) {
      await axios.put(`/api/v1/contacts/forms/${route.params.id}`, form.value);
      dialog.success(t('Form updated successfully'));
    } else {
      await axios.post('/api/v1/contacts/forms', form.value);
      dialog.success(t('Form created successfully'));
    }
    router.push({ name: 'admin.contacts.forms' });
  } catch (e: any) {
    console.error(e);
    if (e.response?.data?.errors?.slug) {
      dialog.error(t('Form slug already exists. Please choose a different slug or rename the form.'));
    } else {
      dialog.error(t('Failed to save form.'));
    }
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadForm();
});
</script>

<style scoped>
.form-builder-container {
  max-width: 100%;
}
</style>
