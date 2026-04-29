<template>
    <textarea
        :id="textareaId"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled || validating"
        :readonly="readonly"
        :required="required"
        :rows="rows"
        :class="textareaClasses"
        :style="resizeStyle"
        :aria-invalid="hasError ? 'true' : 'false'"
        :aria-describedby="ariaDescribedBy"
        :aria-required="required ? 'true' : undefined"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
    />
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useValidation, type ValidationRule } from '../../composables/useValidation';

interface Props {
    name: string;
    modelValue: string;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    required?: boolean;
    rules?: ValidationRule[];
    validateOn?: 'blur' | 'input' | 'submit';
    showError?: boolean;
    rows?: number;
    resize?: 'none' | 'both' | 'horizontal' | 'vertical';
}

const props = withDefaults(defineProps<Props>(), {
    validateOn: 'blur',
    showError: true,
    rows: 4,
    resize: 'vertical',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
    validate: [result: { valid: boolean; error?: string; field?: string }];
}>();

const { validateField, getFieldError, isFieldValidating } = useValidation();
const hasValidated = ref(false);

const textareaId = computed(() => `textarea-${props.name}`);
const error = computed(() => getFieldError(props.name));
const hasError = computed(() => !!error.value);
const validating = computed(() => isFieldValidating(props.name));
const hasSuccess = computed(() => hasValidated.value && !hasError.value && props.modelValue);

const textareaClasses = computed(() => {
    const baseClasses = 'w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200';

    if (hasError.value) {
        return `${baseClasses} border-red-500 focus:border-red-500 focus:ring-red-500 error-glow`;
    }
    if (hasSuccess.value) {
        return `${baseClasses} border-green-500 focus:border-green-500 focus:ring-green-500`;
    }
    return `${baseClasses} border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500`;
});

const resizeStyle = computed(() => {
    return { resize: props.resize };
});

const ariaDescribedBy = computed(() => {
    const parts: string[] = [];
    if (error.value) {
        parts.push(`${props.name}-error`);
    }
    return parts.length > 0 ? parts.join(' ') : undefined;
});

const handleInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);

    if (props.validateOn === 'input' && props.rules && props.rules.length > 0) {
        validateField(props.name, target.value, props.rules).then(result => {
            emit('validate', result);
        });
    }
};

const handleBlur = () => {
    if (props.validateOn === 'blur' && props.rules && props.rules.length > 0) {
        hasValidated.value = true;
        validateField(props.name, props.modelValue, props.rules).then(result => {
            emit('validate', result);
        });
    }
};

const handleFocus = () => {
    // Clear validation state on focus if needed
};

watch(() => error.value, (newError) => {
    if (newError) {
        hasValidated.value = true;
    }
});
</script>

<style scoped>
.error-glow {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.5),
                0 0 8px rgba(239, 68, 68, 0.3),
                0 0 12px rgba(239, 68, 68, 0.2);
}

.error-glow:focus {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.6),
                0 0 12px rgba(239, 68, 68, 0.4),
                0 0 16px rgba(239, 68, 68, 0.3);
}

.dark .error-glow {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.6),
                0 0 10px rgba(239, 68, 68, 0.4),
                0 0 14px rgba(239, 68, 68, 0.3);
}

.dark .error-glow:focus {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.7),
                0 0 14px rgba(239, 68, 68, 0.5),
                0 0 18px rgba(239, 68, 68, 0.4);
}
</style>
