<template>
    <div
        class="form-tags-wrapper w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 flex flex-wrap gap-2 min-h-[42px]"
        :class="wrapperClasses"
        @click="focusInput"
    >
        <div v-for="(tag, index) in tags" :key="index" class="flex items-center gap-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 px-2 py-1 rounded text-sm">
            <span>{{ tag }}</span>
            <button type="button" @click.stop="removeTag(index)" class="hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <input
            ref="inputRef"
            type="text"
            v-model="newTag"
            :id="inputId"
            :placeholder="tags.length === 0 ? placeholder : ''"
            :disabled="disabled || validating"
            :readonly="readonly"
            class="flex-1 bg-transparent border-none focus:ring-0 p-0 text-sm text-gray-900 dark:text-white placeholder-gray-400 min-w-[120px] outline-none"
            :aria-invalid="hasError ? 'true' : 'false'"
            :aria-describedby="ariaDescribedBy"
            :aria-required="required ? 'true' : undefined"
            @keydown.enter.prevent="addTag"
            @keyup.comma="addTag"
            @keyup.space="addTag"
            @blur="handleBlur"
            @input="handleInput"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { useValidation, type ValidationRule } from '../../composables/useValidation';

interface Props {
    name: string;
    modelValue: string[] | string; // Can be array of strings or comma-separated string
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    required?: boolean;
    rules?: ValidationRule[];
    validateOn?: 'blur' | 'input' | 'submit';
    showError?: boolean;
    separator?: string; // If modelValue is string, what separator to use (default comma)
    tagType?: 'text' | 'email'; // Type of tag for validation
}

const props = withDefaults(defineProps<Props>(), {
    validateOn: 'blur',
    showError: true,
    separator: ',',
    tagType: 'text',
});

const emit = defineEmits<{
    'update:modelValue': [value: string[] | string];
    validate: [result: { valid: boolean; error?: string; field?: string }];
}>();

const { validateField, getFieldError, isFieldValidating } = useValidation();
const inputRef = ref<HTMLInputElement | null>(null);
const newTag = ref('');
const hasValidated = ref(false);

const inputId = computed(() => `input-tags-${props.name}`);
const error = computed(() => getFieldError(props.name));
const hasError = computed(() => !!error.value);
const validating = computed(() => isFieldValidating(props.name));

// Parsing modelValue into tags array
const tags = computed(() => {
    if (Array.isArray(props.modelValue)) {
         return props.modelValue.filter(t => t !== '[]');
    }
    if (typeof props.modelValue === 'string') {
        try {
             const parsed = JSON.parse(props.modelValue);
             if (Array.isArray(parsed)) return parsed;
        } catch(e) {}
        
        if (!props.modelValue.trim() || props.modelValue === '[]') return [];
        return props.modelValue.split(props.separator).map(s => s.trim()).filter(s => s);
    }
    return [];
});

const wrapperClasses = computed(() => {
    if (hasError.value) {
        return 'border-red-500 focus-within:border-red-500 focus-within:ring-red-500 error-glow';
    }
    return 'border-gray-300 dark:border-gray-600 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500';
});

const ariaDescribedBy = computed(() => {
    const parts: string[] = [];
    if (error.value) {
        parts.push(`${props.name}-error`);
    }
    return parts.length > 0 ? parts.join(' ') : undefined;
});

const focusInput = () => {
    inputRef.value?.focus();
};

const emitUpdate = (newTags: string[]) => {
    if (Array.isArray(props.modelValue)) {
        emit('update:modelValue', newTags);
    } else {
        emit('update:modelValue', newTags.join(props.separator));
    }
    
    if (props.validateOn === 'input' && props.rules) {
         validateField(props.name, newTags, props.rules).then(result => {
            emit('validate', result);
        });
    }
};

const addTag = (event?: KeyboardEvent | Event) => {
    const rawInput = newTag.value;
    if (!rawInput) return;

    // Split by comma, space, semicolon, or newline
    const candidates = rawInput.split(/[\s,;]+/);
    const validTags: string[] = [];
    const currentTags = new Set(tags.value);

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let hasInvalid = false;

    for (const candidate of candidates) {
        const trimmed = candidate.trim();
        if (trimmed) {
            // Validation based on tagType
            if (props.tagType === 'email' && !emailRegex.test(trimmed)) {
                hasInvalid = true;
                continue;
            }
            
            if (!currentTags.has(trimmed)) {
                validTags.push(trimmed);
                currentTags.add(trimmed);
            }
        }
    }

    if (validTags.length > 0) {
        const updated = [...tags.value, ...validTags];
        emitUpdate(updated);
        newTag.value = '';
    } else if (!hasInvalid && !rawInput.trim()) {
         // If input was empty or whitespace
         newTag.value = '';
    }
    
    // If explicit key action (Enter/Comma/Space), clear input even if invalid
    // to match standard tag input UX (don't block user)
    if (event instanceof KeyboardEvent && ['Enter', ',', ' '].includes(event.key)) {
        newTag.value = '';
    }
};

const removeTag = (index: number) => {
    const updated = [...tags.value];
    updated.splice(index, 1);
    emitUpdate(updated);
};

const handleBlur = () => {
    addTag(); // Try to add what's left
    
    if (props.validateOn === 'blur' && props.rules) {
        hasValidated.value = true;
         validateField(props.name, tags.value, props.rules).then(result => {
            emit('validate', result);
        });
    }
};

const handleInput = (event: Event) => {
    // No-op for now unless we want real-time validation of pending tag
};

watch(() => error.value, (newError) => {
    if (newError) hasValidated.value = true;
});

// Expose focus
defineExpose({ focus: focusInput });
</script>

<style scoped>
.error-glow {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.5),
                0 0 8px rgba(239, 68, 68, 0.3),
                0 0 12px rgba(239, 68, 68, 0.2);
}

.dark .error-glow {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.6),
                0 0 10px rgba(239, 68, 68, 0.4),
                0 0 14px rgba(239, 68, 68, 0.3);
}
</style>
