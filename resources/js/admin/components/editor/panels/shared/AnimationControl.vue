<template>
    <div class="animation-control tw-space-y-3">
        <div class="tw-font-medium tw-text-sm text-admin-theme-text">Animation</div>
        
        <!-- Animation Type -->
        <div>
            <label class="tw-block tw-text-xs text-admin-theme-text-muted tw-mb-1">Type</label>
            <select 
                v-model="modelValue.animation_type"
                @change="emitUpdate"
                class="tw-w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text tw-rounded-md tw-shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 sm:tw-text-sm"
            >
                <option value="none">None</option>
                <optgroup label="Fading Entrances">
                    <option value="fade-in">Fade In</option>
                    <option value="fade-in-up">Fade In Up</option>
                    <option value="fade-in-down">Fade In Down</option>
                </optgroup>
                <optgroup label="Zooming Entrances">
                    <option value="zoom-in">Zoom In</option>
                </optgroup>
                <optgroup label="Attention Seekers">
                    <option value="bounce">Bounce</option>
                    <option value="pulse">Pulse</option>
                    <option value="shake">Shake</option>
                </optgroup>
            </select>
        </div>

        <div v-if="modelValue.animation_type && modelValue.animation_type !== 'none'" class="tw-grid tw-grid-cols-2 tw-gap-3">
            <!-- Duration -->
            <div>
                <label class="tw-block tw-text-xs text-admin-theme-text-muted tw-mb-1">Duration (ms)</label>
                <input 
                    type="number" 
                    v-model.number="modelValue.animation_duration"
                    @input="emitUpdate"
                    step="100"
                    min="100"
                    max="5000"
                    class="tw-w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text tw-rounded-md tw-shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 sm:tw-text-sm"
                    placeholder="1000"
                />
            </div>
            
            <!-- Delay -->
            <div>
                <label class="tw-block tw-text-xs text-admin-theme-text-muted tw-mb-1">Delay (ms)</label>
                <input 
                    type="number" 
                    v-model.number="modelValue.animation_delay"
                    @input="emitUpdate"
                    step="100"
                    min="0"
                    max="10000"
                    class="tw-w-full border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text tw-rounded-md tw-shadow-sm focus:border-admin-theme-primary focus:ring-admin-theme-primary/20 sm:tw-text-sm"
                    placeholder="0"
                />
            </div>
            
            <!-- Infinite Loop -->
            <div class="tw-col-span-2 tw-mt-1">
                <label class="tw-flex tw-items-center tw-cursor-pointer">
                    <input 
                        type="checkbox" 
                        v-model="modelValue.animation_infinite"
                        @change="emitUpdate"
                        class="tw-rounded border-admin-theme-border bg-admin-theme-input-bg text-admin-theme-text text-admin-theme-primary tw-shadow-sm focus:border-admin-theme-primary focus:tw-ring focus:tw-ring-indigo-200 focus:tw-ring-opacity-50"
                    />
                    <span class="tw-ml-2 tw-text-xs text-admin-theme-text-muted">Repeat Infinite Loop</span>
                </label>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { watch } from 'vue';

const props = defineProps<{
    modelValue: {
        animation_type?: string;
        animation_duration?: number;
        animation_delay?: number;
        animation_infinite?: boolean;
    }
}>();

const emit = defineEmits(['update:modelValue']);

// Provide defaults if not set
if (props.modelValue && props.modelValue.animation_type && props.modelValue.animation_type !== 'none') {
    if (!props.modelValue.animation_duration) props.modelValue.animation_duration = 1000;
    if (props.modelValue.animation_delay === undefined) props.modelValue.animation_delay = 0;
    if (props.modelValue.animation_infinite === undefined) props.modelValue.animation_infinite = false;
}

const emitUpdate = () => {
    emit('update:modelValue', props.modelValue);
};
</script>
