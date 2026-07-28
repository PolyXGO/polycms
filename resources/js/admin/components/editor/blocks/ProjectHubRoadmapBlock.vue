<template>
  <!-- Settings Mode (for sidebar) -->
  <div v-if="mode === 'settings'" class="project-hub-roadmap-settings space-y-4">
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Related Product</label>
      <select v-model="state.product_id" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
        <option value="">Select Product</option>
        <option v-for="product in products" :key="product.id" :value="product.id">
          {{ product.name }} (ID: {{ product.id }})
        </option>
      </select>
    </div>
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Max Releases to Display</label>
      <input v-model.number="state.limit" type="number" min="1" max="50" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary">
    </div>
  </div>

  <!-- Preview Mode (for main editor area) -->
  <div v-else class="project-hub-roadmap-preview p-6 border border-dashed border-admin-theme-border rounded-xl bg-admin-theme-base/10 flex items-center justify-between">
    <div class="flex items-center space-x-3">
      <div class="p-3 bg-admin-theme-primary/10 text-admin-theme-primary rounded-lg">
        <svg class="w-6 h-6 text-admin-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
      </div>
      <div>
        <h4 class="text-sm font-bold text-admin-theme-text">{{ $t('Project Hub Roadmap') || 'Project Hub Roadmap' }}</h4>
        <p class="text-xs text-admin-theme-text-muted">
          {{ $t('Product') }}: <span class="font-semibold text-admin-theme-primary">{{ getProductName(state.product_id) || 'Not Selected' }}</span>
          &bull; {{ $t('Max Releases') }}: <span class="font-semibold">{{ state.limit || 5 }}</span>
        </p>
      </div>
    </div>
    <span class="text-[10px] font-extrabold uppercase tracking-wider bg-admin-theme-primary/10 text-admin-theme-primary px-2.5 py-1 rounded-full">
      Roadmap & Changelog Block
    </span>
  </div>
</template>

<script setup lang="ts">
import { nextTick, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps<{
  modelValue: any;
  isEditor?: boolean;
  mode?: 'settings' | 'preview';
  data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const products = ref<any[]>([]);

const fetchProducts = async () => {
  try {
    const response = await axios.get('/api/v1/products', {
      params: {
        per_page: 100,
        compact: 1,
        primary_locale: 1
      }
    });
    products.value = response.data?.data ?? [];
  } catch (error) {
    console.error('Error fetching products for roadmap block:', error);
  }
};

const getProductName = (id: any) => {
  if (!id) return '';
  const prod = products.value.find(p => String(p.id) === String(id));
  return prod ? prod.name : `ID: ${id}`;
};

onMounted(() => {
  if (props.mode === 'settings') {
    fetchProducts();
  }
});

watch(() => props.mode, (newMode) => {
  if (newMode === 'settings' && products.value.length === 0) {
    fetchProducts();
  }
});

function cloneValue<T>(value: T): T {
  if (value === undefined || value === null) {
    return value;
  }
  return JSON.parse(JSON.stringify(value));
}

function hasAttr(source: Record<string, any> | null | undefined, key: string) {
  return Boolean(source) && Object.prototype.hasOwnProperty.call(source, key);
}

function readAttr<T>(key: string, fallback: T): T {
  if (hasAttr(props.modelValue, key)) {
    return cloneValue(props.modelValue?.[key]) as T;
  }
  if (hasAttr(props.data, key)) {
    return cloneValue(props.data?.[key]) as T;
  }
  return cloneValue(fallback) as T;
}

function readSourceAttr<T>(source: Record<string, any> | null | undefined, key: string, fallback: T): T {
  if (hasAttr(source, key)) {
    return cloneValue(source?.[key]) as T;
  }
  return cloneValue(fallback) as T;
}

const state = reactive({
  product_id: readAttr('product_id', ''),
  limit: readAttr('limit', 5),
});

const isSyncingFromProps = ref(false);

function buildPayload() {
  return {
    ...(props.modelValue || {}),
    product_id: state.product_id,
    limit: state.limit,
  };
}

function syncState(source?: Record<string, any> | null) {
  isSyncingFromProps.value = true;
  state.product_id = readSourceAttr(source, 'product_id', '');
  state.limit = readSourceAttr(source, 'limit', 5);

  nextTick(() => {
    isSyncingFromProps.value = false;
  });
}

function emitPayload() {
  emit('update:modelValue', buildPayload());
}

watch(state, () => {
  if (isSyncingFromProps.value) {
    return;
  }
  if (props.mode === 'settings') {
    emitPayload();
  }
}, { deep: true });

watch(() => props.modelValue, (newValue) => {
  syncState(newValue);
}, { deep: true, immediate: true });

watch(() => props.data, (newData) => {
  if (newData) {
    syncState(newData);
  }
}, { deep: true, immediate: true });
</script>
