<template>
    <div :class="wrapperClasses">
        <button 
            :id="toggleId"
            type="button"
            :disabled="disabled || validating"
            @click="toggleValue"
            @blur="handleBlur"
            :class="toggleClasses"
            :aria-invalid="hasError ? 'true' : 'false'"
            :aria-describedby="ariaDescribedBy"
        >
            <span :class="knobClasses" />
        </button>

        <label
            v-if="label"
            :for="toggleId"
            :class="labelClasses"
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
    size?: 'sm' | 'md' | 'lg';
    disabled?: boolean;
    required?: boolean;
    rules?: ValidationRule[];
    validateOn?: 'blur' | 'input' | 'submit';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    validateOn: 'blur',
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    validate: [result: { valid: boolean; error?: string; field?: string }];
}>();

const { validateField, getFieldError, isFieldValidating } = useValidation();
const hasValidated = ref(false);

const toggleId = computed(() => `toggle-${props.name}`);
const error = computed(() => getFieldError(props.name));
const hasError = computed(() => !!error.value);
const validating = computed(() => isFieldValidating(props.name));

const ariaDescribedBy = computed(() => {
    return error.value ? `${props.name}-error` : undefined;
});

const sizeClasses = {
    sm: {
        wrapper: 'flex min-w-0 items-center gap-2 py-0.5',
        toggle: 'h-5 w-9',
        knob: 'h-4 w-4',
        knobOn: 'translate-x-4',
        knobOff: 'translate-x-0',
        label: 'cursor-pointer text-[11px] font-bold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400',
    },
    md: {
        wrapper: 'flex min-w-0 items-center gap-3 py-1',
        toggle: 'h-6 w-11',
        knob: 'h-5 w-5',
        knobOn: 'translate-x-5',
        knobOff: 'translate-x-0',
        label: 'cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300',
    },
    lg: {
        wrapper: 'flex min-w-0 items-center gap-3.5 py-1.5',
        toggle: 'h-7 w-12',
        knob: 'h-6 w-6',
        knobOn: 'translate-x-5',
        knobOff: 'translate-x-0',
        label: 'cursor-pointer text-base font-medium text-gray-700 dark:text-gray-300',
    },
} as const;

const currentSize = computed(() => sizeClasses[props.size]);

const wrapperClasses = computed(() => currentSize.value.wrapper);

const toggleClasses = computed(() => {
    const baseClasses = `toggle-switch relative inline-flex shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2 dark:focus:ring-offset-gray-900 ${currentSize.value.toggle}`;
    
    let classes = baseClasses;
    if (props.modelValue) {
        classes += ' bg-brand-blue';
    } else {
        classes += ' bg-gray-200 dark:bg-gray-700';
    }
    
    if (props.disabled || validating.value) {
        classes += ' opacity-50 cursor-not-allowed';
    }
    
    if (hasError.value) {
        classes += ' ring-red-500 border-red-500';
    }
    
    return classes;
});

const knobClasses = computed(() => {
    const baseClasses = `toggle-knob pointer-events-none inline-block transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${currentSize.value.knob}`;
    return props.modelValue
        ? `${baseClasses} ${currentSize.value.knobOn}`
        : `${baseClasses} ${currentSize.value.knobOff}`;
});

const labelClasses = computed(() => currentSize.value.label);

const toggleValue = () => {
    if (props.disabled || validating.value) return;
    const newValue = !props.modelValue;
    emit('update:modelValue', newValue);

    if (props.validateOn === 'input' && props.rules?.length) {
        validateField(props.name, newValue, props.rules).then(result => {
            emit('validate', result);
        });
    }
};

const handleBlur = () => {
    if (props.validateOn === 'blur' && props.rules?.length) {
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
/* No scoped styles needed anymore as we use Tailwind classes in computed properties */
</style>
