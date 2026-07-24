<template>
    <div class="block-settings-panel space-y-6">
        <PresetManager v-if="!hidePresetManager" type="button_style" :modelValue="modelValue" @update:modelValue="onPresetLoad" />
        
        <!-- Basic Settings -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-admin-theme-text mb-1">Label</label>
                <input 
                    type="text" 
                    v-model="modelValue.label" 
                    @input="emitUpdate"
                    class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 sm:text-sm"
                    placeholder="Click Me"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-admin-theme-text mb-1">URL</label>
                <input 
                    type="text" 
                    v-model="modelValue.url" 
                    @input="emitUpdate"
                    class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 sm:text-sm"
                    placeholder="https://"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-admin-theme-text mb-1">Target</label>
                    <select 
                        v-model="modelValue.target"
                        @change="emitUpdate"
                        class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 sm:text-sm"
                    >
                        <option value="_self">Same Window</option>
                        <option value="_blank">New Tab</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-admin-theme-text mb-1">Alignment</label>
                    <select 
                        v-model="modelValue.alignment"
                        @change="emitUpdate"
                        class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 sm:text-sm"
                    >
                        <option value="left">Left</option>
                        <option value="center">Center</option>
                        <option value="right">Right</option>
                        <option value="full">Full Width</option>
                    </select>
                </div>
            </div>
        </div>

        <hr class="my-6 border-admin-theme-border">

        <!-- Styling (Vercel Customization) -->
        <div class="space-y-4">
            <h4 class="text-sm font-semibold text-admin-theme-text">Styling</h4>
            
            <div>
                <label class="block text-sm font-medium text-admin-theme-text mb-1">Style Preset</label>
                <select 
                    v-model="modelValue.style"
                    @change="emitUpdate"
                    class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 sm:text-sm"
                >
                    <option value="primary">Primary (Black/White)</option>
                    <option value="secondary">Secondary (White/Black)</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            <div v-if="modelValue.style === 'custom'" class="space-y-3 p-3 bg-admin-theme-base rounded-md">
                <!-- Normal / Hover Tabs -->
                <div class="flex gap-2 mb-2">
                    <button type="button" @click="activeTab = 'normal'" :class="activeTab === 'normal' ? 'bg-admin-theme-primary/20 text-admin-theme-primary' : 'text-admin-theme-text-muted hover:text-admin-theme-text'" class="px-3 py-1 text-xs font-bold uppercase rounded">Normal</button>
                    <button type="button" @click="activeTab = 'hover'" :class="activeTab === 'hover' ? 'bg-admin-theme-primary/20 text-admin-theme-primary' : 'text-admin-theme-text-muted hover:text-admin-theme-text'" class="px-3 py-1 text-xs font-bold uppercase rounded">Hover</button>
                </div>

                <div v-if="activeTab === 'normal'" class="space-y-3">
                    <div>
                        <label class="block text-xs text-admin-theme-text-muted mb-1">Background Type</label>
                        <select v-model="modelValue.bg_type" @change="emitUpdate" class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm sm:text-sm">
                            <option value="solid">Solid</option>
                            <option value="gradient">Gradient</option>
                        </select>
                    </div>

                    <div v-if="modelValue.bg_type === 'solid'" class="flex items-center justify-between gap-4">
                        <ColorPicker v-model="modelValue.bg_color" @update:modelValue="emitUpdate" label="Background" class="flex-1" />
                        <ColorPicker v-model="modelValue.text_color" @update:modelValue="emitUpdate" label="Text" class="flex-1" />
                        <ColorPicker v-model="modelValue.border_color" @update:modelValue="emitUpdate" label="Border" class="flex-1" />
                    </div>
                    
                    <div v-if="modelValue.bg_type === 'gradient'" class="space-y-6">
                        <div class="flex items-center justify-between gap-2">
                            <ColorPicker v-model="modelValue.bg_gradient_start" @update:modelValue="emitUpdate" label="Start" class="flex-1" />
                            <ColorPicker v-model="modelValue.bg_gradient_end" @update:modelValue="emitUpdate" label="End" class="flex-1" />
                            <ColorPicker v-model="modelValue.text_color" @update:modelValue="emitUpdate" label="Text" class="flex-1" />
                            <ColorPicker v-model="modelValue.border_color" @update:modelValue="emitUpdate" label="Border" class="flex-1" />
                        </div>
                        <div>
                            <label class="block text-[10px] text-admin-theme-text-muted mb-4 uppercase text-center">Gradient Angle</label>
                            <AnglePicker v-model="modelValue.bg_gradient_angle" @update:modelValue="emitUpdate" />
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'hover'" class="space-y-3">
                    <div>
                        <label class="block text-xs text-admin-theme-text-muted mb-1">Hover Background Type</label>
                        <select v-model="modelValue.hover_bg_type" @change="emitUpdate" class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm sm:text-sm">
                            <option value="solid">Solid</option>
                            <option value="gradient">Gradient</option>
                        </select>
                    </div>

                    <div v-if="modelValue.hover_bg_type === 'solid'" class="flex items-center justify-between gap-4">
                        <ColorPicker v-model="modelValue.hover_bg_color" @update:modelValue="emitUpdate" label="Background" class="flex-1" />
                        <ColorPicker v-model="modelValue.hover_text_color" @update:modelValue="emitUpdate" label="Text" class="flex-1" />
                        <ColorPicker v-model="modelValue.hover_border_color" @update:modelValue="emitUpdate" label="Border" class="flex-1" />
                    </div>
                    
                    <div v-if="modelValue.hover_bg_type === 'gradient'" class="space-y-6">
                        <div class="flex items-center justify-between gap-2">
                            <ColorPicker v-model="modelValue.hover_bg_gradient_start" @update:modelValue="emitUpdate" label="Start" class="flex-1" />
                            <ColorPicker v-model="modelValue.hover_bg_gradient_end" @update:modelValue="emitUpdate" label="End" class="flex-1" />
                            <ColorPicker v-model="modelValue.hover_text_color" @update:modelValue="emitUpdate" label="Text" class="flex-1" />
                            <ColorPicker v-model="modelValue.hover_border_color" @update:modelValue="emitUpdate" label="Border" class="flex-1" />
                        </div>
                        <div>
                            <label class="block text-[10px] text-admin-theme-text-muted mb-4 uppercase text-center">Gradient Angle</label>
                            <AnglePicker v-model="modelValue.hover_bg_gradient_angle" @update:modelValue="emitUpdate" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs text-admin-theme-text-muted mb-1">Border Radius</label>
                    <input 
                        type="text" 
                        v-model="modelValue.border_radius" 
                        @input="emitUpdate"
                        class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm sm:text-sm"
                        placeholder="6px"
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-admin-theme-text mb-2">Inner Padding (px)</label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1">
                        <label class="block text-[10px] text-center text-admin-theme-text-muted uppercase mb-1">Top</label>
                        <input type="number" v-model="modelValue.inner_padding_top" @input="emitUpdate" class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm text-center sm:text-sm p-1">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] text-center text-admin-theme-text-muted uppercase mb-1">Right</label>
                        <input type="number" v-model="modelValue.inner_padding_right" @input="emitUpdate" class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm text-center sm:text-sm p-1">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] text-center text-admin-theme-text-muted uppercase mb-1">Bottom</label>
                        <input type="number" v-model="modelValue.inner_padding_bottom" @input="emitUpdate" class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm text-center sm:text-sm p-1">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] text-center text-admin-theme-text-muted uppercase mb-1">Left</label>
                        <input type="number" v-model="modelValue.inner_padding_left" @input="emitUpdate" class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm text-center sm:text-sm p-1">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-admin-theme-text-muted mb-1">Font Size</label>
                    <input 
                        type="text" 
                        v-model="modelValue.font_size" 
                        @input="emitUpdate"
                        class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm sm:text-sm"
                        placeholder="0.875rem"
                    />
                </div>
                <div>
                    <label class="block text-xs text-admin-theme-text-muted mb-1">Font Weight</label>
                    <select 
                        v-model="modelValue.font_weight"
                        @change="emitUpdate"
                        class="w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text rounded-md shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 sm:text-sm"
                    >
                        <option value="400">Regular (400)</option>
                        <option value="500">Medium (500)</option>
                        <option value="600">Semibold (600)</option>
                        <option value="700">Bold (700)</option>
                    </select>
                </div>
            </div>
        </div>

        <hr class="border-admin-theme-border">

        <!-- Animation settings using the reusable control -->
        <AnimationControl 
            :modelValue="modelValue" 
            @update:modelValue="(val) => { Object.assign(modelValue, val); emitUpdate(); }" 
        />
        
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AnimationControl from '../shared/AnimationControl.vue';
import PresetManager from '../shared/PresetManager.vue';
import ColorPicker from '@/admin/components/Form/ColorPicker.vue';
import AnglePicker from '@/admin/components/Form/AnglePicker.vue';

const props = defineProps<{
    modelValue: any;
    hidePresetManager?: boolean;
}>();

const emit = defineEmits(['update:modelValue']);
const activeTab = ref('normal');

// Initialize defaults for missing fields to avoid undefined errors in template
if (!props.modelValue.style) props.modelValue.style = 'primary';
if (!props.modelValue.alignment) props.modelValue.alignment = 'left';
if (!props.modelValue.target) props.modelValue.target = '_self';
if (!props.modelValue.label) props.modelValue.label = 'Click Me';
if (props.modelValue.inner_padding_top === undefined) props.modelValue.inner_padding_top = 8;
if (props.modelValue.inner_padding_right === undefined) props.modelValue.inner_padding_right = 16;
if (props.modelValue.inner_padding_bottom === undefined) props.modelValue.inner_padding_bottom = 8;
if (props.modelValue.inner_padding_left === undefined) props.modelValue.inner_padding_left = 16;

const onPresetLoad = (val) => {
    Object.assign(props.modelValue, val);
    emitUpdate();
};

const emitUpdate = () => {
    emit('update:modelValue', props.modelValue);
};
</script>
