<template>
    <div class="flex items-center">
        <input
            :id="checkboxId"
            type="checkbox"
            :checked="modelValue"
            :disabled="disabled || validating"
            :required="required"
            :class="checkboxClasses"
            :aria-invalid="hasError ? 'true' : 'false'"
            :aria-describedby="ariaDescribedBy"
            :aria-required="required ? 'true' : undefined"
            @change="handleChange"
            @blur="handleBlur"
        />
        <label
            v-if="label"
            :for="checkboxId"
            class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useValidation, type ValidationRule } from '../../composables/useValidation';

interface Props {
    name: string;
    modelValue: boolean;
    label?: string;
    disabled?: boolean;
    required?: boolean;
    rules?: ValidationRule[];
    validateOn?: 'blur' | 'input' | 'submit';
    showError?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    validateOn: 'blur',
    showError: true,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    validate: [result: { valid: boolean; error?: string; field?: string }];
}>();

const { validateField, getFieldError, isFieldValidating } = useValidation();
const hasValidated = ref(false);

const checkboxId = computed(() => `checkbox-${props.name}`);
const error = computed(() => getFieldError(props.name));
const hasError = computed(() => !!error.value);
const validating = computed(() => isFieldValidating(props.name));

const checkboxClasses = computed(() => {
    const baseClasses = 'w-4 h-4 text-brand-blue border-gray-300 rounded focus:ring-brand-blue dark:border-gray-600 dark:bg-gray-700 transition-all duration-200';

    if (hasError.value) {
        return `${baseClasses} border-red-500 focus:ring-red-500 error-glow-checkbox`;
    }
    return baseClasses;
});

const ariaDescribedBy = computed(() => {
    const parts: string[] = [];
    if (error.value) {
        parts.push(`${props.name}-error`);
    }
    return parts.length > 0 ? parts.join(' ') : undefined;
});

const handleChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.checked);

    if (props.validateOn === 'input' && props.rules && props.rules.length > 0) {
        validateField(props.name, target.checked, props.rules).then(result => {
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

watch(() => error.value, (newError) => {
    if (newError) {
        hasValidated.value = true;
    }
});
</script>

<style scoped>
.error-glow-checkbox {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.5),
                0 0 6px rgba(239, 68, 68, 0.3),
                0 0 10px rgba(239, 68, 68, 0.2);
}

.error-glow-checkbox:focus {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.6),
                0 0 8px rgba(239, 68, 68, 0.4),
                0 0 12px rgba(239, 68, 68, 0.3);
}

.dark .error-glow-checkbox {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.6),
                0 0 8px rgba(239, 68, 68, 0.4),
                0 0 12px rgba(239, 68, 68, 0.3);
}

.dark .error-glow-checkbox:focus {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.7),
                0 0 10px rgba(239, 68, 68, 0.5),
                0 0 14px rgba(239, 68, 68, 0.4);
}
</style>
