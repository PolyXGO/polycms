import { ref, reactive, computed } from 'vue';
import { useDialog } from './useDialog';

/**
 * Validation rule types
 */
export type ValidationRule =
    | { type: 'required'; message?: string }
    | { type: 'email'; message?: string }
    | { type: 'min'; value: number; message?: string }
    | { type: 'max'; value: number; message?: string }
    | { type: 'pattern'; value: RegExp; message?: string }
    | { type: 'numeric'; message?: string }
    | { type: 'url'; message?: string }
    | { type: 'match'; field: string; message?: string }
    | { type: 'custom'; validator: (value: any, formData?: any) => boolean | string | Promise<boolean | string>; message?: string }
    | string; // Shorthand: 'required', 'email', etc.

/**
 * Validation options
 */
export interface ValidationOptions {
    showToast?: boolean;
    toastPosition?: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right' | 'top-center' | 'bottom-center';
    validateOn?: 'blur' | 'input' | 'submit';
    debounce?: number; // For input validation
}

/**
 * Validation result
 */
export interface ValidationResult {
    valid: boolean;
    error?: string;
    field?: string;
}

/**
 * Composable for form validation
 *
 * @example
 * ```ts
 * const { validateField, validateForm, errors, hasErrors } = useValidation({
 *   showToast: true,
 *   toastPosition: 'top-right'
 * });
 *
 * // Validate single field
 * const result = await validateField('email', 'test@example.com', ['required', 'email']);
 *
 * // Validate entire form
 * const formErrors = await validateForm(formData, {
 *   email: ['required', 'email'],
 *   password: ['required', { type: 'min', value: 8 }]
 * });
 * ```
 */
export function useValidation(options: ValidationOptions = {}) {
    const dialog = useDialog();
    const errors = reactive<Record<string, string>>({});
    const validating = reactive<Record<string, boolean>>({});

    const {
        showToast = false,
        toastPosition = 'top-right',
        validateOn = 'blur',
        debounce = 300,
    } = options;

    /**
     * Normalize validation rule
     */
    function normalizeRule(rule: ValidationRule): Exclude<ValidationRule, string> {
        if (typeof rule === 'string') {
            switch (rule) {
                case 'required':
                    return { type: 'required' };
                case 'email':
                    return { type: 'email' };
                case 'numeric':
                    return { type: 'numeric' };
                case 'url':
                    return { type: 'url' };
                default:
                    return { type: 'required' };
            }
        }
        return rule;
    }

    /**
     * Validate a single rule
     */
    async function validateRule(
        rule: Exclude<ValidationRule, string>,
        value: any,
        formData?: Record<string, any>
    ): Promise<string | null> {
        switch (rule.type) {
            case 'required':
                if (value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0)) {
                    return rule.message || 'This field is required';
                }
                break;

            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (value && !emailRegex.test(value)) {
                    return rule.message || 'Please enter a valid email address';
                }
                break;

            case 'min':
                if (value && (typeof value === 'string' ? value.length : Number(value)) < rule.value) {
                    return rule.message || `Minimum length is ${rule.value}`;
                }
                break;

            case 'max':
                if (value && (typeof value === 'string' ? value.length : Number(value)) > rule.value) {
                    return rule.message || `Maximum length is ${rule.value}`;
                }
                break;

            case 'pattern':
                if (value && !rule.value.test(value)) {
                    return rule.message || 'Invalid format';
                }
                break;

            case 'numeric':
                if (value && isNaN(Number(value))) {
                    return rule.message || 'Please enter a valid number';
                }
                break;

            case 'url':
                try {
                    if (value && !new URL(value).href) {
                        return rule.message || 'Please enter a valid URL';
                    }
                } catch {
                    return rule.message || 'Please enter a valid URL';
                }
                break;

            case 'match':
                if (formData && value !== formData[rule.field]) {
                    return rule.message || `This field must match ${rule.field}`;
                }
                break;

            case 'custom':
                const result = await rule.validator(value, formData);
                if (result === false) {
                    return rule.message || 'Validation failed';
                }
                if (typeof result === 'string') {
                    return result;
                }
                break;
        }

        return null;
    }

    /**
     * Validate a single field
     */
    async function validateField(
        name: string,
        value: any,
        rules: ValidationRule[],
        formData?: Record<string, any>
    ): Promise<ValidationResult> {
        validating[name] = true;

        try {
            for (const rule of rules) {
                const normalizedRule = normalizeRule(rule);
                const error = await validateRule(normalizedRule, value, formData);

                if (error) {
                    errors[name] = error;
                    validating[name] = false;

                    // Show toast if enabled
                    if (showToast) {
                        dialog.error(error, 3000);
                    }

                    return {
                        valid: false,
                        error,
                        field: name,
                    };
                }
            }

            // Clear error if validation passes
            delete errors[name];
            validating[name] = false;

            return {
                valid: true,
                field: name,
            };
        } catch (error: any) {
            const errorMessage = error.message || 'Validation error';
            errors[name] = errorMessage;
            validating[name] = false;

            if (showToast) {
                dialog.error(errorMessage, 3000);
            }

            return {
                valid: false,
                error: errorMessage,
                field: name,
            };
        }
    }

    /**
     * Validate entire form
     */
    async function validateForm(
        formData: Record<string, any>,
        rules: Record<string, ValidationRule[]>
    ): Promise<ValidationResult[]> {
        const results: ValidationResult[] = [];

        for (const [fieldName, fieldRules] of Object.entries(rules)) {
            const result = await validateField(fieldName, formData[fieldName], fieldRules, formData);
            results.push(result);
        }

        return results;
    }

    /**
     * Get error for a field
     */
    function getFieldError(name: string): string | undefined {
        return errors[name];
    }

    /**
     * Clear error for a field
     */
    function clearFieldError(name: string): void {
        delete errors[name];
    }

    /**
     * Clear all errors
     */
    function clearAllErrors(): void {
        Object.keys(errors).forEach(key => delete errors[key]);
    }

    /**
     * Check if form has errors
     */
    const hasErrors = computed(() => Object.keys(errors).length > 0);

    /**
     * Set error for a field
     */
    function setFieldError(name: string, message: string): void {
        errors[name] = message;
    }

    /**
     * Set multiple errors (from API response)
     */
    function setErrors(errorsObj: Record<string, string | string[]>): void {
        Object.keys(errorsObj).forEach(key => {
            const error = errorsObj[key];
            errors[key] = Array.isArray(error) ? error[0] : error;
        });
    }

    /**
     * Check if field is validating
     */
    function isFieldValidating(name: string): boolean {
        return validating[name] || false;
    }

    return {
        validateField,
        validateForm,
        getFieldError,
        clearFieldError,
        clearAllErrors,
        hasErrors,
        setFieldError,
        setErrors,
        isFieldValidating,
        errors: computed(() => errors),
        validating: computed(() => validating),
    };
}
