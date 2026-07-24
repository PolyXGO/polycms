<template>
  <!-- Settings Mode -->
  <div v-if="mode === 'settings'" class="fw-contact-settings space-y-4">
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
      <input v-model="state.heading" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary" placeholder="Let's talk about what your system needs next">
    </div>

    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Description 1</label>
      <textarea v-model="state.desc_1" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm h-16 focus:ring-2 focus:ring-admin-theme-primary"></textarea>
    </div>

    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Description 2</label>
      <textarea v-model="state.desc_2" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm h-16 focus:ring-2 focus:ring-admin-theme-primary"></textarea>
    </div>

    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Description 3</label>
      <textarea v-model="state.desc_3" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm h-16 focus:ring-2 focus:ring-admin-theme-primary"></textarea>
    </div>

    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Description 4</label>
      <textarea v-model="state.desc_4" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm h-16 focus:ring-2 focus:ring-admin-theme-primary"></textarea>
    </div>

    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Select Contact Form</label>
      <select v-model="state.form_id" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
        <option value="">-- Select Form --</option>
        <option v-for="form in forms" :key="form.id" :value="form.id">{{ form.name }}</option>
      </select>
    </div>
  </div>

  <!-- Preview Mode -->
  <div v-else class="fw-contact-preview p-6 bg-white border border-gray-100 rounded-xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
      <!-- Left: Content -->
      <div class="space-y-4 text-left">
        <h2 class="text-2xl font-bold text-gray-900 leading-tight">{{ state.heading || "Let's talk about what your system needs next" }}</h2>
        <div class="space-y-2 text-sm text-gray-500">
          <p v-if="state.desc_1">{{ state.desc_1 }}</p>
          <p v-if="state.desc_2">{{ state.desc_2 }}</p>
          <p v-if="state.desc_3">{{ state.desc_3 }}</p>
          <p v-if="state.desc_4" class="font-semibold text-gray-800">{{ state.desc_4 }}</p>
        </div>
        <div class="inline-flex items-center gap-1 px-3 py-1.5 bg-black text-white text-[11px] font-bold rounded">
          <span>🍃</span> envato<span class="font-light text-gray-400">market</span>
        </div>
      </div>
      <!-- Right: Form Mock -->
      <div class="bg-gray-55 border border-gray-200 rounded-lg p-6 flex flex-col gap-4 text-left">
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
    </div>
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
  heading: readAttr('heading', "Let's talk about what your system needs next"),
  desc_1: readAttr('desc_1', "While using our Perfex CRM modules, feel free to share the features or improvements your system requires."),
  desc_2: readAttr('desc_2', "Your feedback helps us enhance and update our products to better serve real business workflows."),
  desc_3: readAttr('desc_3', "Whether it's a new feature, integration, or complete module idea – we're here to listen and make it happen."),
  desc_4: readAttr('desc_4', "Tell us what you need - we'll handle the rest."),
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
    heading: state.heading,
    desc_1: state.desc_1,
    desc_2: state.desc_2,
    desc_3: state.desc_3,
    desc_4: state.desc_4,
    form_id: state.form_id,
  };
}

function syncState(source?: Record<string, any> | null) {
  isSyncingFromProps.value = true;
  state.heading = readSourceAttr(source, 'heading', "Let's talk about what your system needs next");
  state.desc_1 = readSourceAttr(source, 'desc_1', "While using our Perfex CRM modules, feel free to share the features or improvements your system requires.");
  state.desc_2 = readSourceAttr(source, 'desc_2', "Your feedback helps us enhance and update our products to better serve real business workflows.");
  state.desc_3 = readSourceAttr(source, 'desc_3', "Whether it's a new feature, integration, or complete module idea – we're here to listen and make it happen.");
  state.desc_4 = readSourceAttr(source, 'desc_4', "Tell us what you need - we'll handle the rest.");
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
    // Handle paginated responses correctly
    forms.value = response.data?.data || response.data || [];
  } catch (e) {
    console.error('Failed to load contact forms for block settings:', e);
  }
});
</script>
