<template>
    <div class="html-block-editor p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="form-group">
            <div class="flex justify-between items-center mb-2">
                 <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Custom HTML / Scripts</label>
                 <label class="flex items-center gap-2 cursor-pointer">
                     <input type="checkbox" v-model="state.wrap_raw" class="form-checkbox h-3 w-3 text-indigo-600">
                     <span class="text-xs text-gray-500">Wrap Raw (no container)</span>
                 </label>
            </div>
            <textarea 
                v-model="state.html" 
                class="w-full bg-gray-900 border-gray-700 rounded-lg p-4 text-xs font-mono text-green-400 h-64 focus:ring-1 focus:ring-indigo-500 outline-none"
                placeholder="<div>Your custom code here...</div>"
            ></textarea>
            <p class="mt-2 text-[10px] text-gray-400 italic">Caution: Adding script tags may affect editor performance. Use with care.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';

const props = defineProps<{
    modelValue: any;
}>();

const emit = defineEmits(['update:modelValue']);

const state = reactive({
    html: props.modelValue?.html || '',
    wrap_raw: props.modelValue?.wrap_raw || false,
});

watch(() => ({ ...state }), (newValue) => {
    emit('update:modelValue', {
        ...props.modelValue,
        ...newValue,
    });
}, { deep: true });
</script>
