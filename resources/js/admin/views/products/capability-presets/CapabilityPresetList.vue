<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-admin-theme-text">{{ $t('Capability Presets') }}</h1>
        <p class="mt-1 text-sm text-admin-theme-text-muted">
          {{ $t('Manage capability presets to quickly add features to product packages.') }}
        </p>
      </div>
      <button
        @click="createNewPreset"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-admin-theme-primary-content bg-admin-theme-primary hover:bg-admin-theme-primary-hover"
      >
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        {{ $t('Add New Preset') }}
      </button>
    </div>

    <div v-if="loading" class="py-12 text-center text-admin-theme-text-muted">
      {{ $t('Loading presets...') }}
    </div>

    <div v-else class="flex flex-col lg:flex-row gap-6">
      
      <!-- Presets List (Left Side) -->
      <div class="lg:w-1/3 flex flex-col gap-4">
        <div class="bg-admin-theme-surface shadow-sm border border-admin-theme-border rounded-lg overflow-hidden">
          <div class="px-4 py-3 bg-admin-theme-base/50 border-b border-admin-theme-border">
            <h3 class="text-sm font-semibold text-admin-theme-text uppercase tracking-wide">{{ $t('Presets') }}</h3>
          </div>
          
          <ul class="divide-y divide-admin-theme-border max-h-[800px] overflow-y-auto">
            <li 
              v-for="preset in presets" 
              :key="preset.id" 
              @click="selectPreset(preset)"
              class="px-4 py-3 cursor-pointer hover:bg-admin-theme-base/50 transition-colors flex justify-between items-center group"
              :class="{'bg-admin-theme-primary/10 border-l-4 border-admin-theme-primary': activePreset?.id === preset.id, 'border-l-4 border-transparent': activePreset?.id !== preset.id}"
            >
              <div>
                <span class="block text-sm font-medium text-admin-theme-text">{{ preset.name }}</span>
                <span class="block text-xs text-admin-theme-text-muted mt-0.5">{{ preset.group || $t('Uncategorized') }}</span>
              </div>
              <button 
                @click.stop="deletePreset(preset.id)" 
                class="text-admin-theme-text-muted hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded-full hover:bg-admin-theme-base"
                :title="$t('Delete Preset')"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              </button>
            </li>
            <li v-if="presets.length === 0" class="px-4 py-8 text-center text-sm text-admin-theme-text-muted">
              {{ $t('No presets found.') }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Preset Editor (Right Side) -->
      <div class="lg:w-2/3 h-full">
        <div v-if="activePreset" class="bg-admin-theme-surface shadow-sm border border-admin-theme-border rounded-lg overflow-hidden flex flex-col">
          
          <div class="px-5 py-4 border-b border-admin-theme-border flex justify-between items-center bg-admin-theme-base/50">
            <h3 class="text-lg font-medium text-admin-theme-text">
              {{ activePreset.id ? $t('Edit Preset') : $t('New Preset') }}
            </h3>
            <div class="flex gap-2">
              <button 
                v-if="!activePreset.id"
                @click="activePreset = null"
                class="px-3 py-1.5 border border-admin-theme-border rounded-md text-sm font-medium text-admin-theme-text bg-admin-theme-input-bg hover:bg-admin-theme-bg"
              >
                {{ $t('Cancel') }}
              </button>
              <button 
                @click="savePreset"
                :disabled="saving"
                class="px-4 py-1.5 border border-transparent rounded-md text-sm font-medium text-admin-theme-primary-content bg-admin-theme-primary hover:bg-admin-theme-primary-hover disabled:opacity-50"
              >
                {{ saving ? $t('Saving...') : $t('Save Preset') }}
              </button>
            </div>
          </div>

          <div class="p-5 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Name') }} <span class="text-red-500">*</span></label>
                <input
                  v-model="activePreset.name"
                  type="text"
                  class="w-full px-3 py-2 border border-admin-theme-border rounded-md shadow-sm focus:ring-admin-theme-primary focus:border-admin-theme-primary sm:text-sm"
                  :placeholder="$t('e.g. Free installation')"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ $t('Group') }}</label>
                <input
                  v-model="activePreset.group"
                  type="text"
                  class="w-full px-3 py-2 border border-admin-theme-border rounded-md shadow-sm focus:ring-admin-theme-primary focus:border-admin-theme-primary sm:text-sm"
                  :placeholder="$t('e.g. Support, Updates, Customization')"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-admin-theme-text-secondary mb-3">{{ $t('Translations') }}</label>
              
              <div class="space-y-4">
                <div v-for="locale in availableLocales" :key="locale" class="flex gap-4 items-start">
                  <div class="w-24 pt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-admin-theme-base border border-admin-theme-border text-admin-theme-text uppercase">
                      {{ locale }}
                    </span>
                  </div>
                  <div class="flex-1">
                    <input
                      v-model="activePreset.translations[locale]"
                      type="text"
                      class="w-full px-3 py-2 border border-admin-theme-border rounded-md shadow-sm focus:ring-admin-theme-primary focus:border-admin-theme-primary sm:text-sm"
                      :placeholder="locale === 'vi' ? 'Tiếng Việt' : 'Tiếng Anh'"
                    />
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
        
        <div v-else class="bg-admin-theme-surface shadow-sm border border-admin-theme-border rounded-lg h-64 flex items-center justify-center text-admin-theme-text-muted">
          <p>{{ $t('Select a preset to edit or create a new one.') }}</p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const presets = ref<any[]>([]);
const loading = ref(true);
const saving = ref(false);
const activePreset = ref<any>(null);
const availableLocales = ['en', 'vi'];

const fetchPresets = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/v1/admin/capability-presets');
    presets.value = data.data;
  } catch (error) {
    console.error('Error fetching presets:', error);
  } finally {
    loading.value = false;
  }
};

const createNewPreset = () => {
  activePreset.value = {
    id: null,
    name: '',
    group: '',
    translations: { en: '', vi: '' }
  };
};

const selectPreset = (preset: any) => {
  activePreset.value = { 
    ...preset,
    translations: preset.translations || { en: '', vi: '' }
  };
};

const savePreset = async () => {
  if (!activePreset.value.name) return;
  
  saving.value = true;
  try {
    if (activePreset.value.id) {
      const { data } = await axios.put(`/api/v1/admin/capability-presets/${activePreset.value.id}`, activePreset.value);
      const index = presets.value.findIndex(p => p.id === data.data.id);
      if (index !== -1) presets.value[index] = data.data;
    } else {
      const { data } = await axios.post('/api/v1/admin/capability-presets', activePreset.value);
      presets.value.push(data.data);
      activePreset.value = data.data;
    }
  } catch (error) {
    console.error('Error saving preset:', error);
  } finally {
    saving.value = false;
  }
};

const deletePreset = async (id: number) => {
  if (!confirm('Are you sure you want to delete this preset?')) return;
  
  try {
    await axios.delete(`/api/v1/admin/capability-presets/${id}`);
    presets.value = presets.value.filter(p => p.id !== id);
    if (activePreset.value?.id === id) {
      activePreset.value = null;
    }
  } catch (error) {
    console.error('Error deleting preset:', error);
  }
};

onMounted(() => {
  fetchPresets();
});
</script>
