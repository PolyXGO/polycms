<template>
 <div class="form-group mb-0">
 <label v-if="label" class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ label }}</label>
 <div class="inline-flex w-full gap-1 rounded-xl border border-admin-theme-border bg-admin-theme-base p-1 shadow-sm">
 <button 
 v-for="option in options" 
 :key="option.value"
 type="button"
 @click="$emit('update:modelValue', option.value)"
 class="flex h-10 flex-1 items-center justify-center rounded-lg border border-transparent transition-all duration-150"
 :class="modelValue === option.value ?'bg-admin-theme-surface text-admin-theme-primary shadow-sm border-admin-theme-border ' :'text-gray-400 hover:bg-admin-theme-surface/70 hover:text-gray-600 '"
 :title="option.label"
 >
 <div class="w-4 h-4" v-html="option.icon"></div>
 </button>
 </div>
 </div>
</template>

<script setup lang="ts">
defineProps<{
 modelValue: string;
 label?: string;
}>();

defineEmits<{
 (e:'update:modelValue', value: string): void;
}>();

const options = [
 { 
 value:'left', 
 label:'Left', 
 icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h10M4 18h16" stroke-linecap="round" stroke-linejoin="round"/></svg>' 
 },
 { 
 value:'center', 
 label:'Center', 
 icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M7 12h10M4 18h16" stroke-linecap="round" stroke-linejoin="round"/></svg>' 
 },
 { 
 value:'right', 
 label:'Right', 
 icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M10 12h10M4 18h16" stroke-linecap="round" stroke-linejoin="round"/></svg>' 
 },
 { 
 value:'full', 
 label:'Justify', 
 icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/></svg>' 
 }
];
</script>
