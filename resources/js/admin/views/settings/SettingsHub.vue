<template>
  <div class="settings-hub">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-admin-theme-text">{{ t('Settings Hub') }}</h1>
      <p class="text-sm text-admin-theme-text-secondary mt-1">{{ t('Configure your site and e-commerce features') }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-24">
      <div class="h-8 w-8 border-4 border-admin-theme-primary border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Categories List -->
    <div v-else v-for="category in categories" :key="category.name" class="mb-10">
      <h2 class="text-lg font-semibold text-admin-theme-text-secondary mb-4 pb-2 border-b border-admin-theme-border">
        {{ t(category.name) }}
      </h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="item in category.items" 
          :key="item.key"
          class="group relative flex items-start p-5 bg-admin-theme-surface rounded-xl shadow-sm border border-admin-theme-border hover:border-admin-theme-primary/50 dark:hover:border-admin-theme-primary transition-all hover:shadow-md cursor-pointer"
          @click="handleItemClick(item)"
        >
          <!-- Pin Icon -->
          <button 
            @click.stop="togglePin(item)"
            class="absolute top-3 right-3 text-gray-400 hover:text-admin-theme-primary transition-colors z-10"
            :class="{'opacity-100 text-admin-theme-primary': isPinned(item.key), 'opacity-0 group-hover:opacity-100': !isPinned(item.key) }"
            :title="isPinned(item.key) ? t('Unpin from sidebar') : t('Pin to sidebar')"
          >
            <component :is="isPinned(item.key) ? BookmarkIconSolid : BookmarkIcon" class="w-5 h-5" />
          </button>

          <div class="flex-shrink-0 p-3 bg-admin-theme-primary/10 rounded-lg group-hover:bg-admin-theme-primary/20 transition-colors">
            <component :is="item.icon" class="w-6 h-6 text-admin-theme-primary" />
          </div>
          <div class="ml-4 pr-6">
            <h3 class="text-base font-bold text-admin-theme-text group-hover:text-admin-theme-primary">
              {{ t(item.label) }}
            </h3>
            <p class="text-sm text-admin-theme-text-muted mt-1 leading-relaxed">
              {{ t(item.description) }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { BookmarkIcon } from '@heroicons/vue/24/outline';
import { BookmarkIcon as BookmarkIconSolid } from '@heroicons/vue/24/solid';
import * as OutlineIcons from '@heroicons/vue/24/outline';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';

const { t } = useTranslation();
const dialog = useDialog();
const router = useRouter();

const loading = ref(true);
const categories = ref<any[]>([]);
const pinnedSettings = ref<any[]>([]);

const getIconComponent = (iconName: any) => {
  if (typeof iconName === 'object' || typeof iconName === 'function') {
    return iconName;
  }
  return (OutlineIcons as any)[iconName] || OutlineIcons.Cog6ToothIcon;
};

const handleItemClick = async (item: any) => {
  if (item.key === 'translations' || item.action === 'translations') {
    try {
      const response = await axios.get('/api/v1/languages');
      const languages = response.data?.data || [];
      const defaultLang = languages.find((l: any) => l.is_default) || languages[0];
      if (defaultLang) {
        router.push({ name: 'admin.settings.languages.translations', params: { id: defaultLang.id }});
      } else {
        router.push({ name: 'admin.settings.languages' });
      }
    } catch (e) {
      router.push({ name: 'admin.settings.languages' });
    }
  } else if (item.route) {
    router.push(item.route);
  }
};

const loadPinnedSettings = async () => {
  try {
    const response = await axios.get('/api/v1/settings/admin_pinned_settings');
    const rawValue = response.data?.data?.value;
    if (typeof rawValue === 'string') {
      pinnedSettings.value = JSON.parse(rawValue);
    } else {
      pinnedSettings.value = Array.isArray(rawValue) ? rawValue : [];
    }
  } catch (error) {
    console.error('Failed to load pinned settings:', error);
    pinnedSettings.value = [];
  }
};

const loadCategories = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/settings/hub');
    const rawCategories = response.data?.data || [];
    
    categories.value = rawCategories.map((category: any) => {
      return {
        ...category,
        items: (category.items || []).map((item: any) => {
          return {
            ...item,
            icon: getIconComponent(item.icon)
          };
        })
      };
    });
  } catch (error) {
    console.error('Failed to load settings categories:', error);
  } finally {
    loading.value = false;
  }
};

const isPinned = (key: string) => {
  return Array.isArray(pinnedSettings.value) ? pinnedSettings.value.some(p => p.key === key) : false;
};

const togglePin = async (item: any) => {
  const isCurrentlyPinned = isPinned(item.key);
  
  if (isCurrentlyPinned) {
    pinnedSettings.value = pinnedSettings.value.filter(p => p.key !== item.key);
  } else {
    pinnedSettings.value.push({
      key: item.key,
      label: item.label,
      route: item.route,
      module: item.module
    });
  }

  try {
    await axios.put('/api/v1/settings/group/admin_ui', {
      settings: {
        admin_pinned_settings: {
          value: pinnedSettings.value,
          type: 'array',
          group: 'admin_ui'
        }
      }
    });
    
    dialog.success(t(isCurrentlyPinned ? 'Settings unpinned from sidebar' : 'Settings pinned to sidebar'));
    window.dispatchEvent(new Event('admin-menu-changed'));
  } catch (error) {
    console.error('Failed to toggle pin:', error);
    dialog.error(t('Failed to update pinned settings'));
    await loadPinnedSettings();
  }
};

onMounted(async () => {
  await Promise.all([
    loadPinnedSettings(),
    loadCategories()
  ]);
});
</script>
