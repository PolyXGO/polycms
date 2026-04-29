<template>
    <select
        :id="selectId"
        :value="modelValue"
        :disabled="disabled || validating"
        :required="required"
        :class="selectClasses"
        :aria-invalid="hasError ? 'true' : 'false'"
        :aria-describedby="ariaDescribedBy"
        :aria-required="required ? 'true' : undefined"
        @change="handleChange"
        @blur="handleBlur"
        @focus="handleFocus"
    >
        <option v-if="placeholder" value="" disabled>
            {{ placeholder }}
        </option>
        <option
            v-for="option in options"
            :key="getOptionValue(option)"
            :value="getOptionValue(option)"
        >
            {{ getOptionLabel(option) }}
        </option>
    </select>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useValidation, type ValidationRule } from '../../composables/useValidation';

interface Option {
    value: any;
    label: string;
    disabled?: boolean;
}

interface Props {
    name: string;
    modelValue: any;
    options: Option[] | Array<{ value: any; label: string }>;
    placeholder?: string;
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
    'update:modelValue': [value: any];
    validate: [result: { valid: boolean; error?: string; field?: string }];
}>();

const { validateField, getFieldError, isFieldValidating } = useValidation();
const hasValidated = ref(false);

const selectId = computed(() => `select-${props.name}`);
const error = computed(() => getFieldError(props.name));
const hasError = computed(() => !!error.value);
const validating = computed(() => isFieldValidating(props.name));
const hasSuccess = computed(() => hasValidated.value && !hasError.value && props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== '');

const selectClasses = computed(() => {
    const baseClasses = 'w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 appearance-none';

    if (hasError.value) {
        return `${baseClasses} border-red-500 focus:border-red-500 focus:ring-red-500 error-glow`;
    }
    if (hasSuccess.value) {
        return `${baseClasses} border-green-500 focus:border-green-500 focus:ring-green-500`;
    }
    return `${baseClasses} border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500`;
});

const ariaDescribedBy = computed(() => {
    const parts: string[] = [];
    if (error.value) {
        parts.push(`${props.name}-error`);
    }
    return parts.length > 0 ? parts.join(' ') : undefined;
});

const getOptionValue = (option: Option | { value: any; label: string }): any => {
    return 'value' in option ? option.value : option;
};

const getOptionLabel = (option: Option | { value: any; label: string }): string => {
    if (typeof option === 'object' && 'label' in option) {
        return option.label;
    }
    return String(option);
};

const handleChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
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
