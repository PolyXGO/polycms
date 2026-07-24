<template>
  <!-- Settings Mode -->
  <div v-if="mode === 'settings'" class="fw-contact-form-settings space-y-4">
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Select Contact Form</label>
      <select v-model="state.form_id" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
        <option value="">-- Select Form --</option>
        <option v-for="form in forms" :key="form.id" :value="form.id">{{ form.name }}</option>
      </select>
    </div>
  </div>

  <!-- Preview Mode -->
  <div v-else class="fw-contact-form-preview flex flex-col gap-4 text-left">
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Contact Form: {{ selectedFormName || 'No Form Selected' }}</div>
    <div class="grid grid-cols-2 gap-3">
      <div class="h-9 bg-white border border-gray-200 rounded px-3 py-2 text-xs text-gray-300">Name *</div>
      <div class="h-9 bg-white border border-gray-200 rounded px-3 py-2 text-xs text-gray-300">Email *</div>
    </div>
    <div class="h-9 bg-white border border-gray-200 rounded px-3 py-2 text-xs text-gray-300">Phone</div>
    <div class="h-9 bg-white border border-gray-200 rounded px-3 py-2 text-xs text-gray-300">Subject *</div>
    <div class="h-20 bg-white border border-gray-200 rounded px-3 py-2 text-xs text-gray-300">Please describe what you need.</div>
    <div class="flex items-center gap-2 text-xs text-gray-400">
      <div class="w-3.5 h-3.5 border border-gray-300 rounded bg-white"></div>
      I agree to the Terms and Privacy Policy
    </div>
    <div class="w-36 h-12 bg-white border border-gray-200 rounded p-2 flex items-center justify-between text-[10px] text-gray-400">
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 border-2 border-gray-300 rounded bg-white"></div>
        <span>I'm not a robot</span>
      </div>
      <div class="text-[8px] text-right">reCAPTCHA</div>
    </div>
    <div class="h-9 bg-black rounded text-white text-xs font-bold flex items-center justify-center cursor-default opacity-80">Submit</div>
  </div>
</template>

<script setup lang="ts">
import { nextTick, reactive, ref, watch, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps<{
  modelValue: any;
  isEditor?: boolean;
  mode?: 'settings' | 'preview';
  data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const forms = ref<any[]>([]);

function cloneValue<T>(value: T): T {
  if (value === undefined || value === null) return value;
  return JSON.parse(JSON.stringify(value));
}

function hasAttr(source: Record<string, any> | null | undefined, key: string) {
  return Boolean(source) && Object.prototype.hasOwnProperty.call(source, key);
}

function readAttr<T>(key: string, fallback: T): T {
  if (hasAttr(props.modelValue, key)) return cloneValue(props.modelValue?.[key]) as T;
  if (hasAttr(props.data, key)) return cloneValue(props.data?.[key]) as T;
  return cloneValue(fallback) as T;
}

function readSourceAttr<T>(source: Record<string, any> | null | undefined, key: string, fallback: T): T {
  if (hasAttr(source, key)) return cloneValue(source?.[key]) as T;
  return cloneValue(fallback) as T;
}

const state = reactive({
  form_id: readAttr('form_id', ''),
});

const isSyncingFromProps = ref(false);

const selectedFormName = computed(() => {
  const form = forms.value.find(f => String(f.id) === String(state.form_id));
  return form ? form.name : '';
});

function buildPayload() {
  return {
    ...(props.modelValue || {}),
    form_id: state.form_id,
  };
}

function syncState(source?: Record<string, any> | null) {
  isSyncingFromProps.value = true;
  state.form_id = readSourceAttr(source, 'form_id', '');
  nextTick(() => { isSyncingFromProps.value = false; });
}

watch(state, () => {
  if (isSyncingFromProps.value) return;
  emit('update:modelValue', buildPayload());
}, { deep: true });

watch(() => props.modelValue, (newVal) => {
  syncState(newVal);
}, { deep: true });

onMounted(async () => {
  try {
    const response = await axios.get('/api/v1/contacts/forms?active=1&per_page=100');
    forms.value = response.data?.data || response.data || [];
  } catch (e) {
    console.error('Failed to load contact forms for block settings:', e);
  }
});
</script>
