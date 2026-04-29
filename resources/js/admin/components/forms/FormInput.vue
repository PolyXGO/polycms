<template>
    <input
        :id="inputId"
        :type="showPassword ? 'text' : type"
        :value="modelValue"
        :inputmode="inputmode"
        :placeholder="placeholder"
        :disabled="disabled || (lockWhileValidating && validating)"
        :readonly="readonly"
        :required="required"
        :class="inputClasses"
        :aria-invalid="hasError ? 'true' : 'false'"
        :aria-describedby="ariaDescribedBy"
        :aria-required="required ? 'true' : undefined"
        @beforeinput="handleBeforeInput"
        @input="handleInput"
        @keydown="handleKeydown"
        @paste="handlePaste"
        @blur="handleBlur"
        @focus="handleFocus"
    />
    <button
        v-if="type === 'password' && showPasswordToggle"
        type="button"
        @click="togglePassword"
        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none"
        :aria-label="showPassword ? 'Hide password' : 'Show password'"
    >
        <svg
            v-if="showPassword"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 01-4.243-4.243m4.242 4.242L9.88 9.88"
            />
        </svg>
        <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
            />
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
        </svg>
    </button>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useValidation, type ValidationRule } from '../../composables/useValidation';

interface Props {
    name: string;
    modelValue: string | number;
    type?: string;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    required?: boolean;
    rules?: ValidationRule[];
    validateOn?: 'blur' | 'input' | 'submit';
    showError?: boolean;
    showPasswordToggle?: boolean;
    inputmode?: string;
    // Restrict input to numeric characters.
    numericOnly?: boolean;
    // Allow one decimal separator when numericOnly = true.
    allowDecimal?: boolean;
    // Keep legacy behavior when needed: disable field while async validation is running.
    lockWhileValidating?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    validateOn: 'blur',
    showError: true,
    showPasswordToggle: true,
    inputmode: undefined,
    numericOnly: false,
    allowDecimal: false,
    lockWhileValidating: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
    validate: [result: { valid: boolean; error?: string; field?: string }];
}>();

const { validateField, getFieldError, isFieldValidating } = useValidation();
const showPassword = ref(false);
const hasValidated = ref(false);

const inputId = computed(() => `input-${props.name}`);
const error = computed(() => getFieldError(props.name));
const hasError = computed(() => !!error.value);
const validating = computed(() => isFieldValidating(props.name));
const hasSuccess = computed(() => hasValidated.value && !hasError.value && props.modelValue);

const inputClasses = computed(() => {
    const baseClasses = 'w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200';
    const paddingRight = props.type === 'password' && props.showPasswordToggle ? 'pr-10' : '';

    if (hasError.value) {
        return `${baseClasses} ${paddingRight} border-red-500 focus:border-red-500 focus:ring-red-500 error-glow`;
    }
    if (hasSuccess.value) {
        return `${baseClasses} ${paddingRight} border-green-500 focus:border-green-500 focus:ring-green-500`;
    }
    return `${baseClasses} ${paddingRight} border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500`;
});

const ariaDescribedBy = computed(() => {
    const parts: string[] = [];
    if (error.value) {
        parts.push(`${props.name}-error`);
    }
    return parts.length > 0 ? parts.join(' ') : undefined;
});

const sanitizeNumericValue = (value: string): string => {
    if (!props.numericOnly) {
        return value;
    }

    let normalized = value.replace(',', '.').replace(/[^0-9.]/g, '');
    if (!props.allowDecimal) {
        return normalized.replace(/\./g, '');
    }

    const firstDot = normalized.indexOf('.');
    if (firstDot !== -1) {
        normalized = normalized.slice(0, firstDot + 1) + normalized.slice(firstDot + 1).replace(/\./g, '');
    }

    return normalized;
};

const handleBeforeInput = (event: InputEvent) => {
    if (!props.numericOnly) {
        return;
    }

    const inputType = event.inputType || '';
    if (inputType.startsWith('delete') || inputType.startsWith('history')) {
        return;
    }

    const data = event.data ?? '';
    if (!data) {
        return;
    }

    const target = event.target as HTMLInputElement;
    const current = target.value ?? '';
    const start = target.selectionStart ?? current.length;
    const end = target.selectionEnd ?? current.length;
    const next = `${current.slice(0, start)}${data}${current.slice(end)}`;

    const pattern = props.allowDecimal ? /^\d*\.?\d*$/ : /^\d*$/;
    if (!pattern.test(next)) {
        event.preventDefault();
    }
};

const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const nextValue = sanitizeNumericValue(target.value);
    if (nextValue !== target.value) {
        target.value = nextValue;
    }
    emit('update:modelValue', nextValue);

    if (props.validateOn === 'input' && props.rules && props.rules.length > 0) {
        validateField(props.name, nextValue, props.rules).then(result => {
            emit('validate', result);
        });
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (!props.numericOnly) {
        return;
    }

    const allowedKeys = new Set([
        'Backspace',
        'Delete',
        'Tab',
        'Escape',
        'Enter',
        'ArrowLeft',
        'ArrowRight',
        'ArrowUp',
        'ArrowDown',
        'Home',
        'End',
    ]);

    if (
        allowedKeys.has(event.key) ||
        event.ctrlKey ||
        event.metaKey
    ) {
        return;
    }

    if (/^\d$/.test(event.key)) {
        return;
    }

    if (props.allowDecimal && event.key === '.') {
        const currentValue = String(props.modelValue ?? '');
        if (!currentValue.includes('.')) {
            return;
        }
    }

    event.preventDefault();
};

const handlePaste = (event: ClipboardEvent) => {
    if (!props.numericOnly) {
        return;
    }

    const pasted = event.clipboardData?.getData('text') ?? '';
    const pattern = props.allowDecimal ? /^\d*\.?\d*$/ : /^\d*$/;
    if (!pattern.test(pasted.trim())) {
        event.preventDefault();
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

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

// Watch for external error changes
watch(() => error.value, (newError) => {
    if (newError) {
        hasValidated.value = true;
    }
});

onMounted(() => {
    // Initial validation if needed
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
