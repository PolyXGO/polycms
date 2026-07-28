<template>
 <!-- Settings Mode (for sidebar) -->
 <div v-if="mode ==='settings'" class="features-grid-block-settings space-y-4">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Heading</label>
 <input 
 v-model="state.heading" 
 type="text" 
 class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary"
 placeholder="Why Choose Us"
 >
 </div>
 
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Subheading</label>
 <textarea 
 v-model="state.subheading" 
 class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm h-16 focus:ring-2 focus:ring-admin-theme-primary"
 placeholder="Section Subheading"
 ></textarea>
 </div>

 <div class="grid grid-cols-2 gap-3">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Columns</label>
 <select 
 v-model="state.columns" 
 class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary"
 >
 <option :value="1">1 Column</option>
 <option :value="2">2 Columns</option>
 <option :value="3">3 Columns</option>
 <option :value="4">4 Columns</option>
 </select>
 </div>
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Text Align</label>
 <select 
 v-model="state.align" 
 class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm focus:ring-2 focus:ring-admin-theme-primary"
 >
 <option value="left">Left</option>
 <option value="center">Center</option>
 <option value="right">Right</option>
 </select>
 </div>
 </div>

 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Features</label>
 <div v-for="(feature, index) in state.features" :key="index" class="p-3 bg-admin-theme-base rounded-lg border border-admin-theme-border mb-3 relative group">
 <button @click="removeFeature(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
 </button>
 
 <div class="space-y-3">
 <div class="w-full">
 <label class="block text-[8px] font-bold text-gray-400 mb-1">Icon</label>
 <FormIconPicker v-model="feature.icon" label="Feature Icon" />
 </div>
 <div class="w-full">
 <label class="block text-[8px] font-bold text-gray-400 mb-1">Title</label>
 <input 
 v-model="feature.title" 
 type="text" 
 class="w-full bg-admin-theme-surface border-admin-theme-border rounded p-1 text-xs"
 placeholder="Feature Title"
 >
 </div>
 <div>
 <label class="block text-[8px] font-bold text-gray-400 mb-1">Description</label>
 <textarea 
 v-model="feature.description" 
 class="w-full bg-admin-theme-surface border-admin-theme-border rounded p-1 text-xs h-12"
 placeholder="Short description..."
 ></textarea>
 </div>
 </div>
 </div>
 <button @click="addFeature" class="w-full py-2 border-2 border-dashed border-admin-theme-border rounded-lg text-xs text-gray-500 hover:border-admin-theme-primary hover:text-admin-theme-primary transition-colors">
 + Add Feature Card
 </button>
 </div>
 </div>

 <!-- Preview Mode (for main editor area) -->
 <div v-else class="features-grid-block-preview" :style="{ padding: state.padding }">
 <div class="text-center mb-6">
 <h2 class="text-lg font-bold">{{ state.heading ||'Features Section' }}</h2>
 <p class="text-xs text-gray-500 mt-1">{{ state.subheading }}</p>
 </div>
 
 <div class="grid gap-4" :style="{ gridTemplateColumns: `repeat(${state.columns}, 1fr)` }">
 <div v-for="(feature, index) in state.features" :key="index" class="p-4 bg-admin-theme-base rounded-xl border border-admin-theme-border" :style="{ textAlign: state.align }">
 <div class="w-10 h-10 bg-admin-theme-primary/15/30 text-admin-theme-primary dark:text-admin-theme-primary rounded-lg flex items-center justify-center mb-3" :class="state.align ==='center' ?'mx-auto' : (state.align ==='right' ?'ml-auto' :'')">
 <component v-if="feature.icon && feature.icon.endsWith('Icon')" :is="getHeroIcon(feature.icon)" class="w-5 h-5" />
 <i v-else :class="['text-sm', feature.icon ||'ki-outline ki-star']"></i>
 </div>
 <h3 class="text-sm font-bold mb-1">{{ feature.title ||'Feature Title' }}</h3>
 <p class="text-[10px] text-admin-theme-text-muted leading-tight">{{ feature.description }}</p>
 </div>
 </div>
 </div>
</template>

<script setup lang="ts">
import { reactive, watch } from'vue';
import FormIconPicker from'@admin/components/forms/FormIconPicker.vue';
import * as HeroIconsOutline from'@heroicons/vue/24/outline';

const props = defineProps<{
 modelValue: any;
 isEditor?: boolean;
 mode?:'settings' |'preview';
 data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
 heading: props.modelValue?.heading || props.data?.heading ||'',
 subheading: props.modelValue?.subheading || props.data?.subheading ||'',
 columns: props.modelValue?.columns || props.data?.columns || 3,
 align: props.modelValue?.align || props.data?.align ||'center',
 features: props.modelValue?.features || props.data?.features || [
 { icon:'RocketLaunchIcon', title:'Fast Launch', description:'Go from idea to revenue in 10-14 days.' },
 { icon:'BanknotesIcon', title:'Automated Revenue', description:'Subscription billing is already integrated.' }
 ],
 margin: props.modelValue?.margin || props.data?.margin ||'',
 padding: props.modelValue?.padding || props.data?.padding ||'',
});

watch(() => ({ ...state }), (newValue) => {
  if (props.mode ==='settings') {
    emit('update:modelValue', { ...props.modelValue, ...newValue });
  }
}, { deep: true });

// Watch for modelValue changes in preview mode to support real-time updates
watch(() => props.modelValue, (newValue) => {
  if (props.mode === 'preview' && newValue) {
    state.heading = newValue.heading || '';
    state.subheading = newValue.subheading || '';
    state.columns = newValue.columns || 3;
    state.align = newValue.align || 'center';
    state.features = newValue.features || [];
    state.margin = newValue.margin || '';
    state.padding = newValue.padding || '';
  }
}, { deep: true, immediate: true });

// Watch for external data changes
watch(() => props.data, (newData) => {
  if (newData) {
    state.heading = newData.heading || '';
    state.subheading = newData.subheading || '';
    state.columns = newData.columns || 3;
    state.align = newData.align || 'center';
    state.features = newData.features || [];
    state.margin = newData.margin || '';
    state.padding = newData.padding || '';
  }
}, { deep: true });

const getHeroIcon = (name: string) => {
 return (HeroIconsOutline as any)[name];
};

const addFeature = () => {
 state.features.push({ icon:'CheckCircleIcon', title:'New Feature', description:'Feature description goes here.' });
};

const removeFeature = (index: number) => {
 state.features.splice(index, 1);
};
</script>

<style scoped>
.features-grid-block-preview {
 background: rgb(var(--admin-theme-surface));
 padding: 2rem;
 border-radius: 0.5rem;
 border: 1px solid #e5e7eb;
}

.dark .features-grid-block-preview {
 background: #1f2937;
 border-color: #374151;
}
</style>
