<template>
  <div class="contacts-forms-container">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Contact Forms') }}</h1>
        <p class="text-sm text-admin-theme-text-secondary mt-1">{{ t('Manage your site contact and newsletter subscription forms') }}</p>
      </div>
      <router-link
        :to="{ name: 'admin.contacts.forms.create' }"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 shadow-sm flex items-center gap-2 font-medium"
      >
        <span class="text-lg">+</span> {{ t('Create New Form') }}
      </router-link>
    </div>

    <!-- Search / Filter bar -->
    <div class="bg-admin-theme-surface rounded-xl border border-admin-theme-border p-4 mb-6 shadow-sm">
      <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <input
          v-model="search"
          type="text"
          :placeholder="t('Search forms...')"
          class="px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text w-full md:w-1/3 focus:outline-none focus:border-blue-500"
          @input="loadForms"
        />
      </div>
    </div>

    <AdminLoadingState v-if="loading" :message="t('Loading forms...')" />

    <div v-else class="bg-admin-theme-surface rounded-xl border border-admin-theme-border shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-admin-theme-border">
        <thead class="bg-admin-theme-base/50">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Name') }}</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Slug') }}</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Type') }}</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Fields Count') }}</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Status') }}</th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-admin-theme-text-secondary uppercase tracking-wider">{{ t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="bg-admin-theme-surface divide-y divide-admin-theme-border">
          <tr v-for="form in forms" :key="form.id" class="hover:bg-admin-theme-base/10 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="font-medium text-admin-theme-text">{{ form.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-admin-theme-text-muted">
              {{ form.slug }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 py-0.5 inline-flex text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 capitalize">
                {{ form.type }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-theme-text-muted">
              {{ (form.fields || []).length }} {{ t('fields') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span 
                :class="form.is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200' : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-400'" 
                class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full"
              >
                {{ form.is_active ? t('Active') : t('Inactive') }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
              <router-link 
                :to="{ name: 'admin.contacts.forms.edit', params: { id: form.id } }" 
                class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
              >
                {{ t('Edit') }}
              </router-link>
              <button 
                @click="deleteForm(form.id)" 
                class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" 
                :disabled="deleting === form.id"
              >
                {{ deleting === form.id ? t('Deleting...') : t('Delete') }}
              </button>
            </td>
          </tr>
          <tr v-if="forms.length === 0">
            <td colspan="6" class="px-6 py-8 text-center text-admin-theme-text-secondary">
              <div class="flex flex-col items-center justify-center gap-2">
                <svg class="w-8 h-8 text-admin-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ t('No contact forms found. Create one to get started.') }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLoadingState from '../../components/AdminLoadingState.vue';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();

const forms = ref<any[]>([]);
const loading = ref(true);
const search = ref('');
const deleting = ref<number | null>(null);

const loadForms = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/contacts/forms', {
      params: { search: search.value }
    });
    forms.value = response.data.data;
  } catch (e) {
    console.error(e);
    dialog.error(t('Failed to load forms'));
  } finally {
    loading.value = false;
  }
};

const deleteForm = async (id: number) => {
  const confirmed = await dialog.confirm({
    title: t('Delete Form'),
    message: t('Are you sure you want to delete this contact form? Submissions linked to this form will lose their reference.')
  });
  
  if (!confirmed) return;

  deleting.value = id;
  try {
    await axios.delete(`/api/v1/contacts/forms/${id}`);
    dialog.success(t('Form deleted successfully'));
    await loadForms();
  } catch (e) {
    console.error(e);
    dialog.error(t('Failed to delete form'));
  } finally {
    deleting.value = null;
  }
};

onMounted(() => {
  loadForms();
});
</script>

<style scoped>
.contacts-forms-container {
  max-width: 100%;
}
</style>
