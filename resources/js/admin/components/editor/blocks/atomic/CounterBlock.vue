<template>
 <!-- Settings Mode -->
 <div v-if="mode ==='settings'" class="counter-block-settings space-y-4">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Value</label>
 <input v-model="state.value" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="1000" />
 </div>

 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Label</label>
 <input v-model="state.label" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="Happy Customers" />
 </div>

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Prefix</label>
 <input v-model="state.prefix" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="e.g. $, >" />
 </div>
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Suffix</label>
 <input v-model="state.suffix" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="e.g. +, %, K" />
 </div>
 </div>

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Icon (optional)</label>
 <input v-model="state.icon" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="fas fa-users" />
 </div>
 <ColorPicker v-model="state.value_color" label="Value Color" />
 </div>

 <AlignmentPicker v-model="state.alignment" label="Alignment" />
 </div>

 <!-- Preview Mode -->
 <div v-else class="landing-counter" :class="['text-' + state.alignment]">
 <div v-if="state.icon" class="landing-counter__icon">
 <i :class="state.icon"></i>
 </div>
 <div class="landing-counter__value" :style="{ color: state.value_color || undefined }">
 <span v-if="state.prefix" class="landing-counter__prefix">{{ state.prefix }}</span>
 <span class="landing-counter__number">{{ state.value || '0' }}</span>
 <span v-if="state.suffix" class="landing-counter__suffix">{{ state.suffix }}</span>
 </div>
 <div class="landing-counter__label">{{ state.label || 'Counter Label' }}</div>
 </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';
import ColorPicker from '../../controls/ColorPicker.vue';
import AlignmentPicker from '../../controls/AlignmentPicker.vue';

const props = defineProps<{
 modelValue: any;
 isEditor?: boolean;
 mode?: 'settings' | 'preview';
 data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
 value: props.modelValue?.value || props.data?.value || '',
 label: props.modelValue?.label || props.data?.label || '',
 prefix: props.modelValue?.prefix || props.data?.prefix || '',
 suffix: props.modelValue?.suffix || props.data?.suffix || '',
 icon: props.modelValue?.icon || props.data?.icon || '',
 value_color: props.modelValue?.value_color || props.data?.value_color || '',
 alignment: props.modelValue?.alignment || props.data?.alignment || 'center',
});

const buildPayload = () => ({
 value: state.value,
 label: state.label,
 prefix: state.prefix,
 suffix: state.suffix,
 icon: state.icon,
 value_color: state.value_color,
 alignment: state.alignment,
});

watch(() => ({ ...state }), () => {
 if (props.mode === 'settings') {
 emit('update:modelValue', buildPayload());
 }
}, { deep: true });

watch(() => props.modelValue, (newVal) => {
 if (props.mode === 'preview' && newVal) {
 state.value = newVal.value || '';
 state.label = newVal.label || '';
 state.prefix = newVal.prefix || '';
 state.suffix = newVal.suffix || '';
 state.icon = newVal.icon || '';
 state.value_color = newVal.value_color || '';
 state.alignment = newVal.alignment || 'center';
 }
}, { deep: true, immediate: true });
</script>
