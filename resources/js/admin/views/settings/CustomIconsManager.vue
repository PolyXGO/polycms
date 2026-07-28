<template>
  <div class="space-y-6">
    <!-- Header with Breadcrumbs -->
    <div class="mb-6">
      <div class="mb-2 flex items-center gap-4">
        <router-link 
          :to="{ name: 'admin.settings.index' }" 
          class="text-admin-theme-primary hover:text-admin-theme-primary-hover font-medium flex items-center text-sm"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          {{ t('Back to Hub') }}
        </router-link>
      </div>
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold leading-7 text-admin-theme-text sm:text-3xl sm:truncate">
          {{ t('Custom Icons Manager') }}
        </h2>
      </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Panel: Add/Edit Form -->
      <div class="lg:col-span-1">
        <div class="bg-admin-theme-surface shadow-sm border border-admin-theme-border rounded-xl p-6 space-y-4">
          <div class="border-b border-admin-theme-border/40 pb-3 flex justify-between items-center">
            <h3 class="text-sm font-bold uppercase tracking-wider text-admin-theme-text-secondary">
              {{ isEditing ? t('Edit Icon') : t('Add New Icon') }}
            </h3>
            <button
              v-if="isEditing"
              type="button"
              @click="resetForm"
              class="text-xs font-semibold text-admin-theme-primary hover:underline cursor-pointer"
            >
              {{ t('Cancel Edit') }}
            </button>
          </div>

          <form @submit.prevent="saveIcon" class="space-y-4">
            <!-- Icon Identifier -->
            <div>
              <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">
                {{ t('Icon Name / Slug') }} <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.name"
                required
                placeholder="e.g. svg, brand-name, bank-icon"
                class="w-full bg-admin-theme-input-bg border border-admin-theme-border rounded-lg px-3 py-2 text-xs focus:ring-1 focus:ring-admin-theme-primary text-admin-theme-text font-semibold focus:outline-none"
              />
              <p class="text-[10px] text-admin-theme-text-muted mt-1">
                Lowercase letters, numbers, and hyphens only.
              </p>
            </div>

            <!-- Category -->
            <div>
              <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">
                {{ t('Category') }}
              </label>
              <input
                type="text"
                v-model="form.category"
                placeholder="general"
                class="w-full bg-admin-theme-input-bg border border-admin-theme-border rounded-lg px-3 py-2 text-xs focus:ring-1 focus:ring-admin-theme-primary text-admin-theme-text font-semibold focus:outline-none"
              />
            </div>

            <!-- SVG Code Markup -->
            <div>
              <label class="block text-xs font-bold text-admin-theme-text-secondary mb-1.5">
                {{ t('SVG Code') }} <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="form.svg_code"
                required
                rows="6"
                placeholder='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">...</svg>'
                class="w-full bg-admin-theme-input-bg border border-admin-theme-border rounded-lg px-3 py-2 text-xs focus:ring-1 focus:ring-admin-theme-primary text-admin-theme-text font-semibold focus:outline-none font-mono"
              ></textarea>
            </div>

            <!-- Real-time SVG Live Preview -->
            <div v-if="isValidSvg" class="space-y-1.5">
              <label class="block text-[10px] font-black uppercase tracking-wider text-admin-theme-text-muted">
                {{ t('Live Preview') }}
              </label>
              <div class="flex items-center gap-3 p-3 bg-admin-theme-base/20 border border-admin-theme-border/40 rounded-lg">
                <!-- Outer Circle rendering (Frontend Mockup) -->
                <div class="flex flex-col items-center">
                  <span class="text-[9px] font-bold text-admin-theme-text-muted mb-1">Circular</span>
                  <div 
                    class="w-10 h-10 rounded-full border border-admin-theme-border flex items-center justify-center text-admin-theme-primary overflow-hidden"
                  >
                    <div 
                      class="w-5 h-5 flex items-center justify-center [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:overflow-hidden"
                      v-html="form.svg_code"
                    ></div>
                  </div>
                </div>

                <!-- Admin Picker cell preview -->
                <div class="flex flex-col items-center">
                  <span class="text-[9px] font-bold text-admin-theme-text-muted mb-1">Admin Picker</span>
                  <div class="w-10 h-10 flex items-center justify-center bg-admin-theme-base border border-admin-theme-border rounded-lg">
                    <div 
                      class="w-4 h-4 flex items-center justify-center text-admin-theme-primary [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:overflow-hidden"
                      v-html="form.svg_code"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="pt-2">
              <button
                type="submit"
                :disabled="saving"
                class="w-full px-4 py-2 bg-admin-theme-primary text-admin-theme-primary-content hover:bg-admin-theme-primary-hover rounded-lg font-bold text-xs uppercase tracking-widest transition-colors cursor-pointer flex items-center justify-center gap-2"
              >
                <div v-if="saving" class="h-3 w-3 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                {{ isEditing ? t('Update Icon') : t('Save Icon') }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Right Panel: List of Custom Icons -->
      <div class="lg:col-span-2">
        <div class="bg-admin-theme-surface shadow-sm border border-admin-theme-border rounded-xl p-6 space-y-4">
          <!-- Search Header -->
          <div class="flex items-center gap-3">
            <div class="relative flex-1">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-admin-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <input
                v-model="search"
                type="text"
                :placeholder="t('Search custom icons...')"
                class="w-full bg-admin-theme-base border-none rounded-lg pl-9 pr-4 py-2 text-xs focus:ring-1 focus:ring-admin-theme-primary text-admin-theme-text font-semibold focus:outline-none"
              />
            </div>
          </div>

          <!-- Icons Grid -->
          <div v-if="filteredIcons.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            <div 
              v-for="icon in filteredIcons" 
              :key="icon.id"
              class="border border-admin-theme-border rounded-xl p-3 bg-admin-theme-base/10 hover:border-admin-theme-primary/30 transition-all flex flex-col items-center relative group"
            >
              <!-- Icon SVG Cell -->
              <div class="w-12 h-12 flex items-center justify-center bg-admin-theme-base border border-admin-theme-border rounded-lg mb-2 shadow-sm">
                <div 
                  class="w-6 h-6 flex items-center justify-center text-admin-theme-text-secondary [&_svg]:w-full [&_svg]:h-full [&_svg]:block [&_svg]:overflow-hidden"
                  v-html="icon.svg_code"
                ></div>
              </div>

              <!-- Name -->
              <span class="text-xs font-bold text-admin-theme-text truncate w-full text-center">{{ icon.name }}</span>
              <span class="text-[9px] font-black uppercase text-admin-theme-text-muted mt-0.5">{{ icon.category || 'general' }}</span>

              <!-- Hover Actions overlay -->
              <div class="absolute inset-0 bg-admin-theme-surface/90 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                <button
                  type="button"
                  @click="editIcon(icon)"
                  class="px-2.5 py-1.5 bg-admin-theme-primary/10 hover:bg-admin-theme-primary/20 text-admin-theme-primary rounded-lg text-[10px] font-bold uppercase tracking-wider transition-colors cursor-pointer"
                >
                  {{ t('Edit') }}
                </button>
                <button
                  type="button"
                  @click="confirmDelete(icon)"
                  class="px-2.5 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-500 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-colors cursor-pointer"
                >
                  {{ t('Delete') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16 border border-dashed border-admin-theme-border rounded-xl">
            <svg class="mx-auto h-12 w-12 text-admin-theme-text-muted opacity-30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-sm font-bold text-admin-theme-text-secondary">
              {{ search ? t('No icons match search query') : t('No custom icons registered yet') }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useTranslation } from '../../composables/useTranslation';
import { useDialog } from '../../composables/useDialog';
import axios from 'axios';

interface CustomIconData {
  id?: number;
  name: string;
  svg_code: string;
  category: string;
}

const { t } = useTranslation();
const dialog = useDialog();

const icons = ref<CustomIconData[]>([]);
const search = ref('');
const saving = ref(false);
const isEditing = ref(false);
const editingIconId = ref<number | null>(null);

const form = ref<CustomIconData>({
  name: '',
  svg_code: '',
  category: 'general'
});

const isValidSvg = computed(() => {
  const code = form.value.svg_code.trim();
  return code.startsWith('<svg') && code.endsWith('</svg>');
});

const filteredIcons = computed(() => {
  if (!search.value) return icons.value;
  const q = search.value.toLowerCase();
  return icons.value.filter(icon => 
    icon.name.toLowerCase().includes(q) || 
    (icon.category && icon.category.toLowerCase().includes(q))
  );
});

const fetchIcons = async () => {
  try {
    const response = await axios.get('/api/v1/custom-icons');
    if (response.data?.success) {
      icons.value = response.data.data || [];
    }
  } catch (error) {
    console.error('Failed to fetch custom icons', error);
  }
};

const saveIcon = async () => {
  // Validate name format
  const nameRegex = /^[a-z0-9\-]+$/i;
  if (!nameRegex.test(form.value.name)) {
    dialog.error(t('Name/slug must contain only lowercase letters, numbers, and hyphens.'));
    return;
  }

  // Validate SVG format
  if (!isValidSvg.value) {
    dialog.error(t('The SVG code must be a valid SVG string starting with <svg> and ending with </svg>.'));
    return;
  }

  saving.value = true;
  try {
    if (isEditing.value && editingIconId.value) {
      const response = await axios.put(`/api/v1/custom-icons/${editingIconId.value}`, form.value);
      if (response.data?.success) {
        dialog.success(t('Custom icon updated successfully.'));
        resetForm();
        await fetchIcons();
      }
    } else {
      const response = await axios.post('/api/v1/custom-icons', form.value);
      if (response.data?.success) {
        dialog.success(t('Custom icon added successfully.'));
        resetForm();
        await fetchIcons();
      }
    }
  } catch (error: any) {
    console.error('Failed to save custom icon', error);
    const msg = error.response?.data?.message || t('Failed to save custom icon');
    dialog.error(msg);
  } finally {
    saving.value = false;
  }
};

const editIcon = (icon: CustomIconData) => {
  isEditing.value = true;
  editingIconId.value = icon.id || null;
  form.value = {
    name: icon.name,
    svg_code: icon.svg_code,
    category: icon.category || 'general'
  };
};

const confirmDelete = (icon: CustomIconData) => {
  dialog.confirm({
    title: t('Delete Icon'),
    message: `${t('Are you sure you want to delete the icon')} "${icon.name}"?`,
    onConfirm: async () => {
      try {
        const response = await axios.delete(`/api/v1/custom-icons/${icon.id}`);
        if (response.data?.success) {
          dialog.success(t('Icon deleted successfully.'));
          if (editingIconId.value === icon.id) {
            resetForm();
          }
          await fetchIcons();
        }
      } catch (error: any) {
        console.error('Failed to delete icon', error);
        dialog.error(error.response?.data?.message || t('Failed to delete icon'));
      }
    }
  });
};

const resetForm = () => {
  isEditing.value = false;
  editingIconId.value = null;
  form.value = {
    name: '',
    svg_code: '',
    category: 'general'
  };
};

onMounted(() => {
  fetchIcons();
});
</script>
