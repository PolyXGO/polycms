<template>
    <div class="flex items-center">
        <input
            :id="radioId"
            type="radio"
            :name="name"
            :value="value"
            :checked="modelValue === value"
            :disabled="disabled || validating"
            :required="required"
            :class="radioClasses"
            :aria-invalid="hasError ? 'true' : 'false'"
            :aria-describedby="ariaDescribedBy"
            :aria-required="required ? 'true' : undefined"
            @change="handleChange"
            @blur="handleBlur"
        />
        <label
            v-if="label"
            :for="radioId"
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
    modelValue: any;
    value: any;
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
    'update:modelValue': [value: any];
    validate: [result: { valid: boolean; error?: string; field?: string }];
}>();

const { validateField, getFieldError, isFieldValidating } = useValidation();
const hasValidated = ref(false);

const radioId = computed(() => `radio-${props.name}-${props.value}`);
const error = computed(() => getFieldError(props.name));
const hasError = computed(() => !!error.value);
const validating = computed(() => isFieldValidating(props.name));

const radioClasses = computed(() => {
    const baseClasses = 'w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 transition-all duration-200';

    if (hasError.value) {
        return `${baseClasses} border-red-500 focus:ring-red-500 error-glow-radio`;
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
    emit('update:modelValue', props.value);

    if (props.validateOn === 'input' && props.rules && props.rules.length > 0) {
        validateField(props.name, props.value, props.rules).then(result => {
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
.error-glow-radio {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.5),
                0 0 6px rgba(239, 68, 68, 0.3),
                0 0 10px rgba(239, 68, 68, 0.2);
}

.error-glow-radio:focus {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.6),
                0 0 8px rgba(239, 68, 68, 0.4),
                0 0 12px rgba(239, 68, 68, 0.3);
}

.dark .error-glow-radio {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.6),
                0 0 8px rgba(239, 68, 68, 0.4),
                0 0 12px rgba(239, 68, 68, 0.3);
}

.dark .error-glow-radio:focus {
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.7),
                0 0 10px rgba(239, 68, 68, 0.5),
                0 0 14px rgba(239, 68, 68, 0.4);
}
</style>
