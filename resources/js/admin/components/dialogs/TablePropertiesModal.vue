<template>
  <div class="flex flex-col md:flex-row gap-4 h-[350px]">
    <!-- Tabs Sidebar -->
    <div class="w-full md:w-1/4 border-r border-admin-theme-border pr-2">
      <button 
        @click="activeTab = 'general'"
        :class="['w-full text-left px-3 py-2 text-sm rounded-lg mb-1', activeTab === 'general' ? 'text-admin-theme-primary font-medium bg-admin-theme-primary/10' : 'text-admin-theme-text hover:bg-admin-theme-base']"
      >
        {{ t('General') }}
      </button>
      <button 
        @click="activeTab = 'advanced'"
        :class="['w-full text-left px-3 py-2 text-sm rounded-lg', activeTab === 'advanced' ? 'text-admin-theme-primary font-medium bg-admin-theme-primary/10' : 'text-admin-theme-text hover:bg-admin-theme-base']"
      >
        {{ t('Advanced') }}
      </button>
    </div>

    <!-- Tab Content -->
    <div class="w-full md:w-3/4 pr-2 overflow-y-auto">
      <!-- General Tab -->
      <div v-if="activeTab === 'general'" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Width') }}</label>
            <input v-model="form.width" type="text" placeholder="100%" class="w-full px-3 py-1.5 border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text text-sm" />
          </div>
          <div>
            <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Height') }}</label>
            <input v-model="form.height" type="text" placeholder="e.g. 200px" class="w-full px-3 py-1.5 border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Cell spacing') }}</label>
            <input v-model="form.cellSpacing" type="text" class="w-full px-3 py-1.5 border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text text-sm" />
          </div>
          <div>
            <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Cell padding') }}</label>
            <input v-model="form.cellPadding" type="text" class="w-full px-3 py-1.5 border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Border width') }}</label>
            <input v-model="form.borderWidth" type="text" placeholder="1px" class="w-full px-3 py-1.5 border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text text-sm" />
          </div>
          <div>
            <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Caption') }}</label>
            <label class="inline-flex items-center gap-2 mt-2 cursor-pointer text-sm text-admin-theme-text">
              <input type="checkbox" v-model="form.showCaption" class="rounded border-admin-theme-border text-admin-theme-primary focus:ring-admin-theme-primary">
              {{ t('Show caption') }}
            </label>
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Alignment') }}</label>
          <select v-model="form.alignment" class="w-1/2 px-3 py-1.5 border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text text-sm">
            <option value="">{{ t('None') }}</option>
            <option value="left">{{ t('Left') }}</option>
            <option value="center">{{ t('Center') }}</option>
            <option value="right">{{ t('Right') }}</option>
          </select>
        </div>
      </div>

      <!-- Advanced Tab -->
      <div v-if="activeTab === 'advanced'" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Border style') }}</label>
          <select v-model="form.borderStyle" class="w-full px-3 py-1.5 border border-admin-theme-border rounded bg-admin-theme-input-bg text-admin-theme-text text-sm">
            <option value="">{{ t('None') }}</option>
            <option value="solid">{{ t('Solid') }}</option>
            <option value="dashed">{{ t('Dashed') }}</option>
            <option value="dotted">{{ t('Dotted') }}</option>
            <option value="double">{{ t('Double') }}</option>
            <option value="groove">{{ t('Groove') }}</option>
            <option value="ridge">{{ t('Ridge') }}</option>
            <option value="inset">{{ t('Inset') }}</option>
            <option value="outset">{{ t('Outset') }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Border color') }}</label>
          <div class="flex items-center gap-2 border border-admin-theme-border rounded bg-admin-theme-input-bg p-1">
            <input type="color" v-model="form.borderColor" class="w-8 h-8 rounded border-none p-0 cursor-pointer bg-transparent" />
            <input type="text" v-model="form.borderColor" class="flex-1 bg-transparent border-none text-sm text-admin-theme-text focus:ring-0" placeholder="#000000" />
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-admin-theme-text-secondary mb-1">{{ t('Background color') }}</label>
          <div class="flex items-center gap-2 border border-admin-theme-border rounded bg-admin-theme-input-bg p-1">
            <input type="color" v-model="form.backgroundColor" class="w-8 h-8 rounded border-none p-0 cursor-pointer bg-transparent" />
            <input type="text" v-model="form.backgroundColor" class="flex-1 bg-transparent border-none text-sm text-admin-theme-text focus:ring-0" placeholder="#ffffff" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="pt-4 mt-2 flex items-center justify-end gap-3 border-t border-admin-theme-border">
    <button
      type="button"
      class="px-4 py-2 text-sm text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-input-bg transition-colors"
      @click="cancel"
    >
      {{ t('Cancel') }}
    </button>
    <button
      type="button"
      class="px-4 py-2 text-sm font-medium bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors"
      @click="submit"
    >
      {{ t('Save') }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue';
import { useTranslation } from '@/admin/composables/useTranslation';

const props = defineProps<{
  type: string;
  initialStyle?: string;
  onSubmit?: (payload: any) => void;
  onPreview?: (payload: any) => void;
}>();

const emit = defineEmits<{
  close: [];
}>();

const { t } = useTranslation();
const activeTab = ref('general');

const form = reactive({
  width: '',
  height: '',
  cellSpacing: '',
  cellPadding: '',
  borderWidth: '',
  showCaption: false,
  alignment: '',
  borderStyle: '',
  borderColor: '',
  backgroundColor: '',
});

const parseStyle = (styleStr: string) => {
  const styles: Record<string, string> = {};
  if (!styleStr) return styles;
  styleStr.split(';').forEach(rule => {
    const parts = rule.split(':');
    if (parts.length >= 2) {
      const key = parts[0].trim();
      const value = parts.slice(1).join(':').trim();
      styles[key] = value;
    }
  });
  return styles;
};

onMounted(() => {
  if (props.initialStyle) {
    const styles = parseStyle(props.initialStyle);
    form.width = styles['width'] || '';
    form.height = styles['height'] || '';
    form.borderWidth = styles['border-width'] || '';
    form.borderStyle = styles['border-style'] || '';
    form.borderColor = styles['border-color'] || '';
    form.backgroundColor = styles['background-color'] || '';
    form.alignment = styles['text-align'] || '';
  }
});

const buildStyleString = () => {
  const styles: string[] = [];
  if (form.width) styles.push(`width: ${form.width}`);
  if (form.height) styles.push(`height: ${form.height}`);
  if (form.borderWidth) styles.push(`border-width: ${form.borderWidth}`);
  if (form.borderStyle) styles.push(`border-style: ${form.borderStyle}`);
  if (form.borderColor) styles.push(`border-color: ${form.borderColor}`);
  if (form.backgroundColor) styles.push(`background-color: ${form.backgroundColor}`);
  if (form.alignment) styles.push(`text-align: ${form.alignment}`);
  if (form.cellPadding) styles.push(`padding: ${form.cellPadding}`);
  return styles.join('; ');
};

let previewTimeout: number | null = null;
watch(form, () => {
  if (props.onPreview) {
    if (previewTimeout) {
      window.clearTimeout(previewTimeout);
    }
    previewTimeout = window.setTimeout(() => {
      props.onPreview({
        style: buildStyleString(),
        showCaption: form.showCaption,
      });
    }, 50); // 50ms debounce
  }
}, { deep: true });

const cancel = () => {
  if (props.onPreview) {
    // Revert to initial style
    props.onPreview({ style: props.initialStyle || '' });
  }
  emit('close');
};

const submit = () => {
  props.onSubmit?.({
    style: buildStyleString(),
    showCaption: form.showCaption,
  });
  emit('close');
};
</script>
