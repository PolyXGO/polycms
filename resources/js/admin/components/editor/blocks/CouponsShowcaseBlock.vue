<template>
  <!-- Settings Mode (for sidebar panel) -->
  <div v-if="mode === 'settings'" class="coupons-showcase-block-settings space-y-4">
    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading Title</label>
      <input
        v-model="state.heading"
        type="text"
        class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary"
        placeholder="Coupons & Special Offers"
      />
    </div>

    <div class="form-group">
      <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Subheading Description</label>
      <textarea
        v-model="state.subheading"
        rows="3"
        class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary"
        placeholder="Apply verified promo codes at checkout to save instantly on your purchases."
      ></textarea>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Layout</label>
        <select
          v-model="state.layout"
          class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary font-medium"
        >
          <option value="grid">Grid (3 Columns)</option>
          <option value="list">Vertical List</option>
        </select>
      </div>

      <div class="form-group">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Max Display Count</label>
        <input
          v-model.number="state.limit"
          type="number"
          min="1"
          max="50"
          class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary"
        />
      </div>
    </div>
  </div>

  <!-- Preview Mode (for canvas editor - Vercel DNA) -->
  <div v-else class="coupons-showcase-block-preview text-admin-theme-text dark:text-gray-100 py-8 px-4 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="text-center max-w-xl mx-auto mb-8">
      <div class="inline-flex items-center px-2.5 py-0.5 rounded-full border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 text-xs font-medium mb-2.5">
        <span>Verified Offers</span>
      </div>
      <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1.5 tracking-tight">
        {{ state.heading || 'Coupons & Special Offers' }}
      </h2>
      <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
        {{ state.subheading || 'Apply verified promo codes at checkout to save instantly on your purchases.' }}
      </p>
    </div>

    <!-- Sample Coupons Grid Preview (Vercel Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- Card 1 -->
      <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-colors flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-1.5">
              <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-mono font-bold text-xs border border-gray-200 dark:border-gray-700">
                50% OFF
              </span>
              <span class="text-[10px] font-medium uppercase tracking-wider px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                Exclusive
              </span>
            </div>
            <span class="text-[11px] text-gray-500 dark:text-gray-400">Selected Item</span>
          </div>
          <h4 class="font-semibold text-sm text-gray-900 dark:text-white mb-1">50% OFF for Unikorn members</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 leading-relaxed">Special launch discount on MTMedia Automation Suite.</p>
        </div>
        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <span class="font-mono font-bold text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white">
              UNIKORN50MTM
            </span>
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Copy</span>
          </div>
          <span class="px-2.5 py-1 text-xs font-semibold rounded bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900">Use Code &rarr;</span>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-colors flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-mono font-bold text-xs border border-gray-200 dark:border-gray-700">
              20% OFF
            </span>
            <span class="text-[11px] text-gray-500 dark:text-gray-400">Storewide</span>
          </div>
          <h4 class="font-semibold text-sm text-gray-900 dark:text-white mb-1">20% OFF your first purchase</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 leading-relaxed">Welcome gift for all new customers on all products.</p>
        </div>
        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <span class="font-mono font-bold text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white">
              WELCOME20
            </span>
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Copy</span>
          </div>
          <span class="px-2.5 py-1 text-xs font-semibold rounded bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900">Use Code &rarr;</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';

const props = defineProps<{
  modelValue?: any;
  isEditor?: boolean;
  mode?: 'settings' | 'preview';
  data?: any;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: any): void;
  (e: 'update:data', value: any): void;
}>();

const state = reactive({
  heading: props.data?.heading ?? props.modelValue?.heading ?? 'Coupons & Special Offers',
  subheading: props.data?.subheading ?? props.modelValue?.subheading ?? 'Apply verified promo codes at checkout to save instantly on your purchases.',
  layout: props.data?.layout ?? props.modelValue?.layout ?? 'grid',
  limit: props.data?.limit ?? props.modelValue?.limit ?? 20,
});

watch(state, (newVal) => {
  emit('update:data', { ...newVal });
  emit('update:modelValue', { ...newVal });
}, { deep: true });
</script>
