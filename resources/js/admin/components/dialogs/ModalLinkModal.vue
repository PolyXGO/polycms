<template>
  <div class="space-y-5">
    <!-- Label Text & Display Mode Selector in a nice grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Label Text -->
      <div>
        <label class="block text-sm font-medium text-admin-theme-text-secondary mb-2">{{ t('Label Text') || 'Label Text' }}</label>
        <input 
          v-model="form.labelText" 
          type="text" 
          placeholder="e.g. View details, Learn more"
          class="w-full px-3.5 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm"
        />
      </div>

      <!-- Display Style Selector -->
      <div>
        <label class="block text-sm font-medium text-admin-theme-text-secondary mb-2">{{ t('Display Style') || 'Display Style' }}</label>
        <div class="grid grid-cols-2 gap-2">
          <!-- Button Style -->
          <label 
            class="relative flex items-center justify-center p-2 cursor-pointer rounded-lg border transition-all gap-2 text-center"
            :class="form.displayMode === 'button' ? 'border-admin-theme-primary bg-admin-theme-primary/10 ring-1 ring-admin-theme-primary' : 'border-admin-theme-border bg-admin-theme-base hover:bg-admin-theme-hover'"
          >
            <input type="radio" v-model="form.displayMode" value="button" class="sr-only" />
            <svg class="w-4 h-4 text-admin-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <rect x="3" y="6" width="18" height="12" rx="3" stroke-width="2" />
            </svg>
            <span class="text-xs font-semibold text-admin-theme-text">{{ t('Button Card') || 'Button Card' }}</span>
          </label>

          <!-- Anchor Text Style -->
          <label 
            class="relative flex items-center justify-center p-2 cursor-pointer rounded-lg border transition-all gap-2 text-center"
            :class="form.displayMode === 'link' ? 'border-admin-theme-primary bg-admin-theme-primary/10 ring-1 ring-admin-theme-primary' : 'border-admin-theme-border bg-admin-theme-base hover:bg-admin-theme-hover'"
          >
            <input type="radio" v-model="form.displayMode" value="link" class="sr-only" />
            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
            </svg>
            <span class="text-xs font-semibold text-admin-theme-text">{{ t('Anchor Text') || 'Anchor Text' }}</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Modal Size & Content Type Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Modal Size Selection -->
      <div>
        <label class="block text-sm font-medium text-admin-theme-text-secondary mb-2">{{ t('Popup Size') || 'Popup Size' }}</label>
        <div class="grid grid-cols-3 gap-2">
          <label 
            v-for="size in ['sm', 'lg', 'full']" 
            :key="size"
            class="relative flex flex-col items-center p-2.5 cursor-pointer rounded-lg border-2 transition-colors text-center"
            :class="form.modalSize === size ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
          >
            <input type="radio" v-model="form.modalSize" :value="size" class="sr-only" />
            <span class="text-xs font-semibold text-admin-theme-text uppercase">{{ size === 'sm' ? 'Small' : (size === 'lg' ? 'Large' : 'Full') }}</span>
          </label>
        </div>
      </div>

      <!-- Content Type Selection -->
      <div>
        <label class="block text-sm font-medium text-admin-theme-text-secondary mb-2">{{ t('Content Source') || 'Content Source' }}</label>
        <div class="grid grid-cols-2 gap-2">
          <label 
            v-for="type in ['html', 'iframe']" 
            :key="type"
            class="relative flex flex-col items-center p-2.5 cursor-pointer rounded-lg border-2 transition-colors text-center"
            :class="form.contentType === type ? 'border-admin-theme-primary bg-admin-theme-primary/10' : 'border-transparent bg-admin-theme-base hover:bg-admin-theme-hover'"
          >
            <input type="radio" v-model="form.contentType" :value="type" class="sr-only" />
            <span class="text-xs font-semibold text-admin-theme-text capitalize">{{ type === 'html' ? (t('Rich HTML') || 'Rich HTML') : (t('Iframe Url') || 'Iframe Url') }}</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Dynamic Content Section -->
    <div class="border-t border-admin-theme-border pt-4 mt-2">
      <!-- Rich HTML Editor (via TiptapEditor) -->
      <div v-if="form.contentType === 'html'" class="space-y-2">
        <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Modal Content (HTML)') || 'Modal Content (HTML)' }}</label>
        <div class="border border-admin-theme-border rounded-xl overflow-hidden bg-admin-theme-base min-h-[300px]">
          <TiptapEditor v-model="form.contentHtml" placeholder="Write premium popup content here..." class="w-full" />
        </div>
      </div>

      <!-- Iframe Link Url -->
      <div v-else class="space-y-2">
        <label class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ t('Iframe URL / Embed Link') || 'Iframe URL / Embed Link' }}</label>
        <input 
          v-model="form.iframeUrl" 
          type="text" 
          placeholder="https://example.com/embed-content"
          class="w-full px-3 py-2.5 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text focus:ring-admin-theme-primary focus:border-admin-theme-primary text-sm"
        />
        <p class="text-xs text-admin-theme-text-muted mt-1">
          Make sure the embedded link supports iframe inclusion (allows embedding and has HTTPS).
        </p>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="pt-4 mt-6 flex items-center justify-end gap-3 border-t border-admin-theme-border">
    <button
      type="button"
      class="px-4 py-2 text-sm text-admin-theme-text-secondary bg-admin-theme-base rounded-lg hover:bg-admin-theme-input-bg transition-colors"
      @click="$emit('close')"
    >
      {{ t('Cancel') || 'Cancel' }}
    </button>
    <button
      type="button"
      class="px-5 py-2.5 text-sm font-medium bg-admin-theme-primary text-admin-theme-primary-content rounded-lg hover:bg-admin-theme-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      :disabled="!isValid"
      @click="submit"
    >
      {{ isEditing ? (t('Update Element') || 'Update Element') : (t('Insert Element') || 'Insert Element') }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useTranslation } from '@/admin/composables/useTranslation';
import TiptapEditor from '@/admin/components/TiptapEditor.vue';
import { useLandingStore } from '@/admin/stores/landingStore';

const props = defineProps<{
  initialLabelText?: string;
  initialModalSize?: string;
  initialContentType?: string;
  initialContentHtml?: string;
  initialIframeUrl?: string;
  initialDisplayMode?: string;
  onSubmit?: (payload: { labelText: string, modalSize: string, contentType: string, contentHtml: string, iframeUrl: string, displayMode: string }) => void;
}>();

const emit = defineEmits<{
  close: [];
}>();

const { t } = useTranslation();
const landingStore = useLandingStore();

const isEditing = computed(() => props.initialLabelText !== undefined);

const form = ref({
  labelText: 'Click here',
  modalSize: 'lg',
  contentType: 'html',
  contentHtml: '',
  iframeUrl: '',
  displayMode: 'button'
});

onMounted(() => {
  if (props.initialLabelText !== undefined) {
    form.value.labelText = props.initialLabelText;
  }
  if (props.initialModalSize !== undefined) {
    form.value.modalSize = props.initialModalSize;
  }
  if (props.initialContentType !== undefined) {
    form.value.contentType = props.initialContentType;
  }
  if (props.initialContentHtml !== undefined) {
    form.value.contentHtml = props.initialContentHtml;
  }
  if (props.initialIframeUrl !== undefined) {
    form.value.iframeUrl = props.initialIframeUrl;
  }
  if (props.initialDisplayMode !== undefined) {
    form.value.displayMode = props.initialDisplayMode;
  }
});

// Clean up landing store state when this modal is closed/unmounted
onBeforeUnmount(() => {
  landingStore.clearActiveBlock();
});

const isValid = computed(() => {
  if (!form.value.labelText.trim()) return false;
  if (form.value.contentType === 'iframe') {
    return !!form.value.iframeUrl.trim();
  }
  return true; // HTML content can be empty initially
});

const submit = () => {
  if (!isValid.value) return;
  
  if (props.onSubmit) {
    props.onSubmit({
      labelText: form.value.labelText.trim(),
      modalSize: form.value.modalSize,
      contentType: form.value.contentType,
      contentHtml: form.value.contentHtml,
      iframeUrl: form.value.iframeUrl.trim(),
      displayMode: form.value.displayMode
    });
  }
  emit('close');
};
</script>
