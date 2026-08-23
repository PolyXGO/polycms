<template>
  <div class="product-project-hub-panel space-y-4">
    <!-- Loading State -->
    <div v-if="loadingProjects" class="flex items-center justify-center py-6 text-sm text-admin-theme-text-muted">
      <i class="fas fa-spinner fa-spin mr-2 text-indigo-500"></i> {{ $t('Loading Project Hub info...') || 'Đang tải thông tin Dự án Project Hub...' }}
    </div>

    <!-- Case A: Linked to a ProjectHub Project -->
    <div v-else-if="currentProject" class="p-4 rounded-xl border border-indigo-500/20 bg-indigo-500/5 dark:bg-indigo-950/20 space-y-3">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shrink-0">
            <i :class="getPlatformIcon(currentProject.platform)"></i>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h4 class="text-sm font-bold text-admin-theme-text">{{ currentProject.name }}</h4>
              <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full uppercase" :class="currentProject.status === 'published' ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border border-amber-500/20'">
                {{ currentProject.status }}
              </span>
            </div>
            <div class="flex items-center gap-2 mt-1 text-xs text-admin-theme-text-muted">
              <span v-if="currentProject.project_code" class="font-mono bg-black/5 dark:bg-white/5 px-1.5 py-0.5 rounded">
                Code: {{ currentProject.project_code }}
              </span>
              <span v-if="currentProject.platform" class="uppercase font-semibold text-indigo-500">
                {{ currentProject.platform }}
              </span>
              <span v-if="currentProject.current_version" class="text-admin-theme-text-secondary font-medium">
                v{{ currentProject.current_version }}
              </span>
            </div>
          </div>
        </div>

        <!-- Direct Actions -->
        <div class="flex items-center gap-2 shrink-0">
          <a 
            :href="'/admin/project-hub/' + currentProject.id + '/edit'" 
            target="_blank"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-colors cursor-pointer"
          >
            <i class="fas fa-external-link-alt text-[10px]"></i>
            {{ $t('Edit Project Hub') || 'Chỉnh sửa Project Hub' }}
          </a>

          <a 
            v-if="currentProject.frontend_url"
            :href="currentProject.frontend_url" 
            target="_blank"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg border border-admin-theme-border text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
          >
            <i class="fas fa-eye text-[10px]"></i>
            {{ $t('View Public Page') || 'Xem trang công khai' }}
          </a>

          <button 
            type="button" 
            @click="unlinkProject"
            :disabled="actionLoading"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg border border-rose-500/30 text-rose-500 hover:bg-rose-500/10 transition-colors"
            :title="$t('Unlink Project') || 'Hủy liên kết'"
          >
            <i class="fas" :class="actionLoading ? 'fa-spinner fa-spin' : 'fa-unlink'"></i>
            {{ $t('Unlink') || 'Hủy liên kết' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Case B: Not Linked - Show Link Selector -->
    <div v-else class="p-4 rounded-xl border border-dashed border-admin-theme-border bg-admin-theme-surface space-y-3">
      <div class="flex items-start gap-3">
        <div class="w-9 h-9 rounded-lg bg-admin-theme-input-bg text-admin-theme-text-muted flex items-center justify-center text-sm shrink-0 border border-admin-theme-border">
          <i class="fas fa-link"></i>
        </div>
        <div class="flex-1">
          <h4 class="text-xs font-bold text-admin-theme-text">{{ $t('Connect to Project Hub') || 'Liên kết với Dự án Project Hub' }}</h4>
          <p class="text-xs text-admin-theme-text-muted mt-0.5">
            {{ $t('Connect this product to a Project Hub to enable auto releases, desktop installers (Windows/macOS), and changelog sync.') || 'Liên kết sản phẩm này với một Dự án Project Hub để quản lý phiên bản, bộ cài đặt desktop và đồng bộ nhật ký cập nhật.' }}
          </p>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row items-center gap-2 pt-1">
        <select 
          v-model="selectedProjectId" 
          class="flex-1 w-full px-3 py-2 text-xs border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option :value="null">-- {{ $t('Select a Project Hub to link...') || 'Chọn Dự án Project Hub để liên kết...' }} --</option>
          <option v-for="proj in availableProjects" :key="proj.id" :value="proj.id">
            {{ proj.name }} ({{ proj.project_code || proj.platform }}) - v{{ proj.current_version || '1.0.0' }}
          </option>
        </select>

        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
          <button 
            type="button" 
            :disabled="!selectedProjectId || actionLoading"
            @click="linkSelectedProject"
            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-colors disabled:opacity-50 cursor-pointer"
          >
            <i class="fas" :class="actionLoading ? 'fa-spinner fa-spin' : 'fa-link'"></i>
            {{ $t('Link Project') || 'Liên kết dự án' }}
          </button>
          
          <a 
            href="/admin/project-hub/create" 
            target="_blank"
            class="inline-flex items-center justify-center gap-1 px-3 py-2 text-xs font-medium rounded-lg border border-admin-theme-border text-admin-theme-text-secondary hover:bg-black/5 dark:hover:bg-white/5 transition-colors whitespace-nowrap"
          >
            <i class="fas fa-plus text-[10px]"></i>
            {{ $t('Create New') || 'Tạo mới' }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { inject, ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { EditorContextKey } from '../../../../editor/context';
import { useDialog } from '../../../../composables/useDialog';

const context = inject(EditorContextKey) as any;
const route = useRoute();
const dialog = useDialog();

const loadingProjects = ref(false);
const actionLoading = ref(false);
const availableProjects = ref<any[]>([]);
const selectedProjectId = ref<number | null>(null);

const form = computed(() => context?.form?.value || {});
const currentProductId = computed(() => form.value?.id || Number(route.params.id) || null);

const currentProject = computed(() => {
  if (form.value?.project) {
    return form.value.project;
  }
  if (currentProductId.value && availableProjects.value.length > 0) {
    const found = availableProjects.value.find((p: any) => 
      (p.products || []).some((prod: any) => prod.id === currentProductId.value || prod.product_id === currentProductId.value)
    );
    if (found) return found;
  }
  return null;
});

const getPlatformIcon = (platform: string) => {
  const p = (platform || '').toLowerCase();
  if (p.includes('electron') || p.includes('desktop') || p.includes('win')) return 'fab fa-windows';
  if (p.includes('apple') || p.includes('mac')) return 'fab fa-apple';
  if (p.includes('wordpress') || p.includes('wp')) return 'fab fa-wordpress';
  return 'fas fa-cubes';
};

const fetchProjects = async () => {
  loadingProjects.value = true;
  try {
    const res = await axios.get('/api/v1/projects', { params: { per_page: 100 } });
    availableProjects.value = res.data?.data || res.data || [];
    
    if (currentProductId.value) {
      const match = availableProjects.value.find((p: any) => 
        (p.products || []).some((prod: any) => prod.id === currentProductId.value || prod.product_id === currentProductId.value)
      );
      if (match && context?.form?.value) {
        context.form.value.project = match;
      }
    }
  } catch (err) {
    console.error('Failed to load projects for ProductProjectHubPanel', err);
  } finally {
    loadingProjects.value = false;
  }
};

const linkSelectedProject = async () => {
  const prodId = currentProductId.value;
  if (!selectedProjectId.value || !prodId) {
    dialog.alert({
      title: 'Action Required',
      message: 'Please save the product first before linking to a Project Hub.',
    });
    return;
  }

  actionLoading.value = true;
  try {
    await axios.post(`/api/v1/projects/${selectedProjectId.value}/link-product`, {
      product_id: prodId,
      is_primary: true,
      label: form.value.name || 'Official Product',
    });
    
    await fetchProjects();
    const proj = availableProjects.value.find((p: any) => p.id === selectedProjectId.value);
    if (context?.form?.value) {
      context.form.value.project = proj || { id: selectedProjectId.value, name: 'Linked Project' };
    }
    dialog.success('Product successfully linked to Project Hub!');
  } catch (err: any) {
    dialog.error(err?.response?.data?.message || 'Failed to link project.');
  } finally {
    actionLoading.value = false;
  }
};

const unlinkProject = async () => {
  const proj = currentProject.value;
  const prodId = currentProductId.value;
  if (!proj?.id || !prodId) return;
  
  const confirmed = await dialog.confirm({
    title: 'Unlink Project Hub',
    message: `Are you sure you want to unlink this product from "${proj.name}"?`,
    confirmText: 'Unlink',
    cancelText: 'Cancel',
    type: 'danger',
  });
  if (!confirmed) return;

  actionLoading.value = true;
  try {
    await axios.post(`/api/v1/projects/${proj.id}/unlink-product`, {
      product_id: prodId,
    });
    if (context?.form?.value) {
      context.form.value.project = null;
    }
    selectedProjectId.value = null;
    await fetchProjects();
    dialog.success('Product unlinked successfully.');
  } catch (err: any) {
    dialog.error(err?.response?.data?.message || 'Failed to unlink project.');
  } finally {
    actionLoading.value = false;
  }
};

onMounted(() => {
  fetchProjects();
});

watch(() => form.value?.id, () => {
  fetchProjects();
});

watch(() => route.params.id, () => {
  fetchProjects();
});
</script>
