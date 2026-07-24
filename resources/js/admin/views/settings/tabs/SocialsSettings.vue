<template>
  <div class="space-y-6">
    <div class="bg-admin-theme-surface shadow rounded-lg p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h3 class="text-lg font-bold text-admin-theme-text">
            {{ t('Social Media Links') }}
          </h3>
          <p class="text-xs text-admin-theme-text-muted mt-1">
            {{ t('Manage social media profiles and links for your widgets and templates.') }}
          </p>
        </div>
        <button
          type="button"
          @click="addSocial"
          class="flex items-center gap-1.5 px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content hover:bg-admin-theme-primary-hover text-xs font-bold uppercase tracking-widest rounded-lg transition-colors cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          {{ t('Add Item') }}
        </button>
      </div>

      <!-- Social Links List -->
      <div v-if="items.length > 0" class="space-y-4">
        <div 
          v-for="(item, index) in items" 
          :key="index"
          class="flex flex-col md:flex-row md:items-center gap-4 p-4 bg-admin-theme-base/20 border border-admin-theme-border rounded-xl group hover:border-admin-theme-primary/30 transition-all"
        >
          <!-- Icon Picker -->
          <div class="w-full md:w-48 flex-shrink-0">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-admin-theme-text-muted mb-1.5">
              {{ t('Icon') }}
            </label>
            <FormIconPicker
              v-model="item.icon"
              :label="t('Select Icon')"
              @update:modelValue="onItemChange"
            />
          </div>

          <!-- Display Name -->
          <div class="w-full md:w-64 flex-shrink-0">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-admin-theme-text-muted mb-1.5">
              {{ t('Platform Name') }}
            </label>
            <input
              type="text"
              v-model="item.name"
              @input="onItemChange"
              :placeholder="t('Facebook, YouTube...')"
              class="w-full bg-admin-theme-surface border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary font-medium"
            />
          </div>

          <!-- Link URL -->
          <div class="flex-1">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-admin-theme-text-muted mb-1.5">
              {{ t('Profile URL') }}
            </label>
            <input
              type="url"
              v-model="item.url"
              @input="onItemChange"
              :placeholder="t('https://...')"
              class="w-full bg-admin-theme-surface border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary font-medium"
            />
          </div>

          <!-- Actions -->
          <div class="flex items-end justify-end md:self-end pb-1.5">
            <button
              type="button"
              @click="deleteSocial(index)"
              class="p-2 text-red-500 hover:text-red-600 rounded-lg hover:bg-red-500/10 transition-all cursor-pointer"
              :title="t('Delete')"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12 border border-dashed border-admin-theme-border rounded-xl">
        <svg class="mx-auto h-12 w-12 text-admin-theme-text-muted opacity-40 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        <p class="text-sm font-bold text-admin-theme-text-secondary">{{ t('No social profiles configured') }}</p>
        <button
          type="button"
          @click="addSocial"
          class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-admin-theme-primary/10 hover:bg-admin-theme-primary/15 text-admin-theme-primary text-xs font-bold uppercase tracking-widest rounded-lg transition-colors cursor-pointer"
        >
          {{ t('Create First Profile') }}
        </button>
      </div>
    </div>

    <!-- Floating / Tab Save Button -->
    <div class="flex justify-end pt-4 border-t border-admin-theme-border">
      <button
        type="button"
        @click="$emit('save')"
        :disabled="saving"
        class="px-6 py-2 bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"
      >
        <span v-if="saving" class="flex items-center">
          <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
          {{ t('Saving...') }}
        </span>
        <span v-else>{{ t('Save Settings') }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useTranslation } from '../../../composables/useTranslation';
import FormIconPicker from '../../../components/forms/FormIconPicker.vue';

interface Setting {
  key: string;
  value: any;
  type: string;
  label: string;
  description: string;
}

interface Props {
  settings: Record<string, Setting>;
  saving: boolean;
  group: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'update', group: string, key: string, value: any): void;
  (e: 'save'): void;
}>();

const { t } = useTranslation();
const items = ref<Array<{ name: string; icon: string; url: string }>>([]);

const parseSocialLinks = (val: any) => {
  if (!val) return [];
  if (typeof val === 'string') {
    try {
      return JSON.parse(val);
    } catch (e) {
      return [];
    }
  }
  return val;
};

// Map platform name to backward-compatible settings keys
const nameToKeyMap: Record<string, string> = {
  'facebook': 'social_facebook',
  'youtube': 'social_youtube',
  'github': 'social_github',
  'envato': 'social_envato',
  'twitter': 'social_twitter',
  'instagram': 'social_instagram',
  'linkedin': 'social_linkedin'
};

watch(() => props.settings.social_links?.value, (newVal) => {
  items.value = parseSocialLinks(newVal);
}, { immediate: true });

const updateSocialLinks = () => {
  // Update the master list
  emit('update', props.group, 'social_links', JSON.stringify(items.value));
  
  // Update individual backward-compatible keys
  Object.keys(nameToKeyMap).forEach(nameKey => {
    const settingKey = nameToKeyMap[nameKey];
    const found = items.value.find(item => item.name.toLowerCase() === nameKey);
    emit('update', props.group, settingKey, found ? found.url : '');
  });
};

const onItemChange = () => {
  updateSocialLinks();
};

const addSocial = () => {
  items.value.push({
    name: 'New Social',
    icon: 'ki-share',
    url: ''
  });
  updateSocialLinks();
};

const deleteSocial = (index: number) => {
  items.value.splice(index, 1);
  updateSocialLinks();
};
</script>
