<template>
 <div class="email-templates">
 <div class="mb-6">
 <div class="mb-2 flex items-center gap-4">
 <router-link :to="{ name:'admin.settings.index' }" class="text-admin-theme-primary hover:text-admin-theme-primary-hover font-medium flex items-center text-sm">
 <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
 </svg>
 {{ t('Back to Hub') }}
 </router-link>
 </div>
 <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Email Templates') }}</h1>
 </div>

 <div v-if="loading" class="text-center py-12 bg-admin-theme-surface rounded-lg shadow">
 <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-admin-theme-primary"></div>
 <p class="mt-2 text-admin-theme-text-secondary">{{ t('Loading templates...') }}</p>
 </div>

 <div v-else class="bg-admin-theme-surface rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-admin-theme-border">
 <thead class="bg-admin-theme-base/50">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Template Name') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Group') }}</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Status') }}</th>
 <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('Actions') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-admin-theme-border">
 <tr v-for="template in templates" :key="template.id" class="hover:bg-black/5 dark:hover:bg-white/5">
 <td class="px-6 py-4">
 <div class="font-medium text-admin-theme-text">{{ template.key }}</div>
 <div class="text-sm text-admin-theme-text-muted">{{ template.subject }}</div>
 </td>
 <td class="px-6 py-4">
 <span class="px-2 py-1 text-xs font-medium rounded-full bg-admin-theme-base text-admin-theme-text-secondary capitalize">
 {{ template.group ||'General' }}
 </span>
 </td>
 <td class="px-6 py-4">
 <span v-if="template.is_active" class="text-green-600 dark:text-green-400 flex items-center text-sm">
 <span class="w-2 h-2 rounded-full bg-green-600 mr-2"></span>
 {{ t('Enabled') }}
 </span>
 <span v-else class="text-gray-400 flex items-center text-sm">
 <span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span>
 {{ t('Disabled') }}
 </span>
 </td>
 <td class="px-6 py-4 text-right">
 <router-link 
 :to="{ name:'admin.settings.email-templates.edit', params: { id: template.id } }"
 class="text-admin-theme-primary hover:text-admin-theme-primary dark:hover:text-admin-theme-primary font-medium"
 >
 {{ t('Edit') }}
 </router-link>
 </td>
 </tr>
 <tr v-if="templates.length === 0">
 <td colspan="4" class="px-6 py-12 text-center text-admin-theme-text-muted">
 {{ t('No templates found.') }}
 </td>
 </tr>
 </tbody>
 </table>
 </div>
 </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from'vue';
import axios from'axios';
import { useTranslation } from'@/admin/composables/useTranslation';

const { t } = useTranslation();
const templates = ref<any[]>([]);
const loading = ref(true);

const loadTemplates = async () => {
 try {
 const response = await axios.get('/api/v1/email-templates');
 templates.value = response.data.data;
 } catch (error) {
 console.error('Error loading email templates:', error);
 } finally {
 loading.value = false;
 }
};

onMounted(loadTemplates);
</script>
