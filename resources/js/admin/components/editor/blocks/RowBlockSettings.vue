<template>
 <div class="row-block-settings space-y-4">
 <div class="border border-admin-theme-border bg-admin-theme-base/70 p-3">
 <div class="flex items-start justify-between gap-3">
 <div class="min-w-0">
 <h4 class="text-[10px] font-bold uppercase tracking-[0.22em] text-admin-theme-primary">Row Layout</h4>
 <p class="mt-1 text-[10.5px] leading-5 text-admin-theme-text-muted">Choose a preset column structure for this row. Existing content is preserved and moved into the closest remaining columns when the layout changes.</p>
 </div>
 <div class="shrink-0 border border-indigo-200/70 bg-admin-theme-primary/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-admin-theme-primary dark:border-admin-theme-primary/25 dark:bg-admin-theme-primary/100/10">
 {{ state.columns }} cols
 </div>
 </div>

 <div class="mt-3">
 <RowLayoutPicker
 :modelValue="state.layout_preset"
 size="sm"
 @update:modelValue="handleLayoutChange"
 />
 </div>
 </div>

 <div class="space-y-3">
 <OptionControl
 v-model="state.gap"
 label="Gap"
 type="icons"
 :options="ROW_GAP_OPTIONS"
 />

 <OptionControl
 v-model="state.vertical_align"
 label="Vertical Alignment"
 type="icons"
 :options="ROW_VERTICAL_ALIGN_OPTIONS"
 />
 </div>

 <div class="border border-dashed border-admin-theme-border bg-admin-theme-base px-2.5 py-2 text-[10.5px] leading-5 text-admin-theme-text-muted dark:text-admin-theme-text-muted">
 Margin and padding stay in the shared <span class="font-semibold text-admin-theme-text-secondary">Layout &amp; Spacing</span> section below.
 </div>
 </div>
</template>

<script setup lang="ts">
import { reactive, watch } from'vue';
import RowLayoutPicker from'../controls/RowLayoutPicker.vue';
import OptionControl from'../controls/OptionControl.vue';
import {
 ROW_GAP_OPTIONS,
 ROW_VERTICAL_ALIGN_OPTIONS,
 applyRowLayout,
 normalizeRowData,
} from'@/admin/editor/rowLayoutPresets';

const props = defineProps<{
 modelValue?: Record<string, any>;
 data?: Record<string, any>;
 mode?:'settings' |'preview';
}>();

const emit = defineEmits<{
 (e:'update:modelValue', value: Record<string, any>): void;
}>();

const state = reactive(normalizeRowData(props.modelValue || props.data));

function syncState(source?: Record<string, any>) {
 Object.assign(state, normalizeRowData(source));
}

function buildPayload() {
 const base = props.modelValue || props.data || {};
 return {
 ...base,
 ...normalizeRowData(state),
 };
}

function handleLayoutChange(presetId: string) {
 Object.assign(state, applyRowLayout(state, presetId));
}

watch(
 () => props.modelValue,
 (newValue) => {
 if (props.mode ==='settings' && newValue) {
 syncState(newValue);
 }
 },
 { deep: true, immediate: true },
);

watch(
 () => props.data,
 (newValue) => {
 if (newValue) {
 syncState(newValue);
 }
 },
 { deep: true, immediate: true },
);

watch(
 state,
 () => {
 if (props.mode !=='settings') {
 return;
 }

 const payload = buildPayload();
 const current = {
 ...(props.modelValue || props.data || {}),
 ...normalizeRowData(props.modelValue || props.data),
 };

 if (JSON.stringify(payload) !== JSON.stringify(current)) {
 emit('update:modelValue', payload);
 }
 },
 { deep: true },
);
</script>
