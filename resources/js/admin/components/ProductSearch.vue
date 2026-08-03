<template>
  <div class="relative">
    <label v-if="label" class="block text-sm font-medium text-admin-theme-text-secondary mb-1">{{ label }}</label>
    
    <!-- Selected Items Chips -->
    <div class="flex flex-wrap gap-2 mb-2" v-if="selectedItems && selectedItems.length > 0">
      <div
        v-for="item in selectedItems"
        :key="item.id"
        class="bg-blue-500/15 text-blue-400 border border-blue-500/30 px-2.5 py-1 rounded-md text-xs font-medium flex items-center gap-1.5"
      >
        <span v-if="item.locale" class="uppercase text-[10px] font-bold px-1 py-0.5 rounded bg-blue-500/20 text-blue-300">{{ item.locale }}</span>
        <span>{{ item.name }} <span v-if="item.sku" class="opacity-75">({{ item.sku }})</span></span>
        <button
          type="button"
          @click="remove(item.id)"
          class="text-blue-400 hover:text-blue-200 transition-colors text-base leading-none font-bold"
        >
          &times;
        </button>
      </div>
    </div>

    <!-- Search Input -->
    <input 
      type="text" 
      v-model="searchQuery" 
      @input="handleInput"
      class="w-full px-3 py-2 border border-admin-theme-border rounded-lg bg-admin-theme-input-bg text-admin-theme-text text-sm"
      :placeholder="placeholder || t('Search products by name or SKU...')"
    />
    <p class="text-[11px] text-admin-theme-text-muted mt-1">{{ t('Leave empty to apply coupon to all products, or select specific products.') }}</p>
    
    <!-- Dropdown -->
    <div v-if="showDropdown && (results.length > 0 || loading)" class="absolute z-10 w-full mt-1 bg-admin-theme-surface border border-admin-theme-border rounded-md shadow-lg max-h-60 overflow-y-auto">
      <div v-if="loading" class="p-3 text-sm text-admin-theme-text-muted text-center">{{ t('Loading products...') }}</div>
      <ul v-else-if="results.length > 0">
        <li 
          v-for="prod in results" 
          :key="prod.id" 
          @click="select(prod)" 
          class="px-4 py-2 hover:bg-admin-theme-base cursor-pointer text-sm text-admin-theme-text flex items-center justify-between border-b border-admin-theme-border/50 last:border-0"
        >
          <div>
            <div class="font-medium text-sm flex items-center gap-1.5">
              <span v-if="prod.locale" class="uppercase text-[10px] font-bold px-1 py-0.5 rounded bg-gray-500/20 text-gray-400">{{ prod.locale }}</span>
              <span>{{ prod.name }}</span>
            </div>
            <div class="text-admin-theme-text-muted text-xs flex items-center gap-2 mt-0.5">
              <span v-if="prod.sku">SKU: {{ prod.sku }}</span>
              <span>Price: {{ prod.price }}</span>
            </div>
          </div>
          <span v-if="modelValue.includes(prod.id)" class="text-xs text-emerald-400 font-semibold">✓ Added</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, getCurrentInstance } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';

const instance = getCurrentInstance();
const t = instance?.appContext.config.globalProperties.$t || ((v: string) => v);

const props = defineProps({
  modelValue: {
    type: Array as () => number[],
    default: () => []
  },
  initialProducts: {
    type: Array as () => any[],
    default: () => []
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const searchQuery = ref('');
const results = ref<any[]>([]);
const selectedItems = ref<any[]>([]);
const loading = ref(false);
const showDropdown = ref(false);

const syncInitialProducts = () => {
  if (props.initialProducts && props.initialProducts.length > 0) {
    selectedItems.value = [...props.initialProducts];
  }
};

watch(() => props.initialProducts, syncInitialProducts, { immediate: true });

const handleInput = debounce(async () => {
  if (searchQuery.value.length < 1) {
    results.value = [];
    showDropdown.value = false;
    return;
  }
  
  loading.value = true;
  showDropdown.value = true;
  
  try {
    const response = await axios.get('/api/v1/products', {
      params: { search: searchQuery.value, per_page: 20 }
    });
    results.value = response.data.data || response.data || [];
  } catch (error) {
    console.error('Product search error', error);
    results.value = [];
  } finally {
    loading.value = false;
  }
}, 300);

const select = (prod: any) => {
  if (!props.modelValue.includes(prod.id)) {
    const newValue = [...props.modelValue, prod.id];
    emit('update:modelValue', newValue);
    if (!selectedItems.value.some(item => item.id === prod.id)) {
      selectedItems.value.push({ id: prod.id, name: prod.name, sku: prod.sku, locale: prod.locale });
    }
  }
  searchQuery.value = '';
  showDropdown.value = false;
};

const remove = (id: number) => {
  const newValue = props.modelValue.filter(item => item !== id);
  emit('update:modelValue', newValue);
  selectedItems.value = selectedItems.value.filter(item => item.id !== id);
};
</script>
