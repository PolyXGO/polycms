<template>
 <!-- Settings Mode -->
 <div v-if="mode ==='settings'" class="icon-box-settings space-y-4">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Icon (Font Awesome class)</label>
 <input v-model="state.icon" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="fas fa-rocket" />
 <p class="mt-1 text-xs text-gray-500">Example: fas fa-rocket, fas fa-star, fas fa-shield-alt</p>
 </div>

 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Title</label>
 <input v-model="state.title" type="text" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="Feature Title" />
 </div>

 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Description</label>
 <textarea v-model="state.description" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg p-2 text-sm h-20 focus:ring-2 focus:ring-admin-theme-primary" placeholder="Describe this feature..."></textarea>
 </div>

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Link URL</label>
 <input v-model="state.link_url" type="url" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11" placeholder="https://" />
 </div>
 <div class="form-group">
 <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Layout</label>
 <select v-model="state.layout" class="w-full bg-admin-theme-base border-admin-theme-border rounded-lg px-3 py-2 text-sm h-11">
 <option value="centered">Centered</option>
 <option value="left">Left Aligned</option>
 <option value="inline">Inline</option>
 </select>
 </div>
 </div>

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
 <ColorPicker v-model="state.icon_color" label="Icon Color" />
 <ColorPicker v-model="state.icon_bg" label="Icon Background" />
 </div>
 </div>

 <!-- Preview Mode -->
 <div v-else class="landing-icon-box" :class="['landing-icon-box--' + state.layout]">
 <component :is="state.link_url ? 'a' : 'div'" :href="state.link_url || undefined" class="landing-icon-box__inner" :target="state.link_url ? '_blank' : undefined">
 <div class="landing-icon-box__icon" :style="iconStyle">
 <i :class="state.icon || 'fas fa-star'"></i>
 </div>
 <div class="landing-icon-box__content">
 <h4 class="landing-icon-box__title">{{ state.title || 'Feature Title' }}</h4>
 <p class="landing-icon-box__desc">{{ state.description || 'Describe your feature or benefit here.' }}</p>
 </div>
 </component>
 </div>
</template>

<script setup lang="ts">
import { reactive, watch, computed } from 'vue';
import ColorPicker from '../../controls/ColorPicker.vue';

const props = defineProps<{
 modelValue: any;
 isEditor?: boolean;
 mode?: 'settings' | 'preview';
 data?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
 icon: props.modelValue?.icon || props.data?.icon || 'fas fa-star',
 title: props.modelValue?.title || props.data?.title || '',
 description: props.modelValue?.description || props.data?.description || '',
 link_url: props.modelValue?.link_url || props.data?.link_url || '',
 layout: props.modelValue?.layout || props.data?.layout || 'centered',
 icon_color: props.modelValue?.icon_color || props.data?.icon_color || '#ffffff',
 icon_bg: props.modelValue?.icon_bg || props.data?.icon_bg || '#4f46e5',
});

const iconStyle = computed(() => ({
 color: state.icon_color || '#ffffff',
 background: state.icon_bg || '#4f46e5',
}));

const buildPayload = () => ({
 icon: state.icon,
 title: state.title,
 description: state.description,
 link_url: state.link_url,
 layout: state.layout,
 icon_color: state.icon_color,
 icon_bg: state.icon_bg,
});

watch(() => ({ ...state }), () => {
 if (props.mode === 'settings') {
 emit('update:modelValue', buildPayload());
 }
}, { deep: true });

watch(() => props.modelValue, (newVal) => {
 if (props.mode === 'preview' && newVal) {
 state.icon = newVal.icon || 'fas fa-star';
 state.title = newVal.title || '';
 state.description = newVal.description || '';
 state.link_url = newVal.link_url || '';
 state.layout = newVal.layout || 'centered';
 state.icon_color = newVal.icon_color || '#ffffff';
 state.icon_bg = newVal.icon_bg || '#4f46e5';
 }
}, { deep: true, immediate: true });
</script>
