# Form Validation Components

A standardized validation components library for PolyCMS admin panel that provides reusable, customizable form validation components with real-time feedback, error display, and integration with toast notifications.

## Components

- **FormField** - Wrapper component for form inputs with validation
- **FormInput** - Text input with validation
- **FormTextarea** - Textarea with validation
- **FormSelect** - Select dropdown with validation
- **FormCheckbox** - Checkbox with validation
- **FormRadio** - Radio button with validation
- **FormError** - Standalone error message component

## Composable

- **useValidation** - Composable for managing validation state and rules

## Installation

Components are already available in the admin panel. Import them as needed:

```typescript
import FormField from '@/admin/components/forms/FormField.vue';
import FormInput from '@/admin/components/forms/FormInput.vue';
import { useValidation } from '@/admin/composables/useValidation';
```

## Basic Usage

### Simple Input with Validation

```vue
<template>
  <FormField name="email" label="Email" :required="true">
    <FormInput
      v-model="form.email"
      name="email"
      type="email"
      :rules="['required', 'email']"
    />
  </FormField>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import FormField from '@/admin/components/forms/FormField.vue';
import FormInput from '@/admin/components/forms/FormInput.vue';

const form = ref({
  email: ''
});
</script>
```

### With useValidation Composable

```vue
<template>
  <form @submit.prevent="handleSubmit">
    <FormField name="name" label="Name" :required="true">
      <FormInput
        v-model="form.name"
        name="name"
        :rules="validationRules.name"
        @validate="handleValidate"
      />
    </FormField>

    <FormField name="email" label="Email" :required="true">
      <FormInput
        v-model="form.email"
        name="email"
        type="email"
        :rules="validationRules.email"
      />
    </FormField>

    <button type="submit" :disabled="hasErrors">Submit</button>
  </form>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useValidation } from '@/admin/composables/useValidation';
import FormField from '@/admin/components/forms/FormField.vue';
import FormInput from '@/admin/components/forms/FormInput.vue';

const form = ref({
  name: '',
  email: ''
});

const { validateForm, hasErrors, setErrors } = useValidation({
  showToast: true
});

const validationRules = {
  name: ['required', { type: 'min', value: 3 }],
  email: ['required', 'email']
};

const handleValidate = (result: any) => {
  console.log('Validation result:', result);
};

const handleSubmit = async () => {
  const errors = await validateForm(form.value, validationRules);
  if (errors.length === 0 || errors.every(e => e.valid)) {
    // Submit form
    console.log('Form is valid');
  } else {
    // Handle errors
    const errorMap: Record<string, string> = {};
    errors.forEach(error => {
      if (!error.valid && error.error) {
        errorMap[error.field || ''] = error.error;
      }
    });
    setErrors(errorMap);
  }
};
</script>
```

### Server-Side Validation

```vue
<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useValidation } from '@/admin/composables/useValidation';

const { setErrors } = useValidation();

const form = ref({
  name: '',
  email: ''
});

const submitForm = async () => {
  try {
    await axios.post('/api/v1/resource', form.value);
  } catch (error: any) {
    if (error.response?.data?.errors) {
      // Set errors from API response
      setErrors(error.response.data.errors);
    }
  }
};
</script>
```

## Validation Rules

### Built-in Rules

- `'required'` - Field is required
- `'email'` - Valid email format
- `'numeric'` - Numeric value
- `'url'` - Valid URL format
- `{ type: 'min', value: number }` - Minimum length
- `{ type: 'max', value: number }` - Maximum length
- `{ type: 'pattern', value: RegExp }` - Regex pattern match
- `{ type: 'match', field: string }` - Match another field value
- `{ type: 'custom', validator: Function }` - Custom validation function

### Examples

```typescript
// Simple rules
const rules = ['required', 'email'];

// With custom messages
const rules = [
  { type: 'required', message: 'Please enter your email' },
  { type: 'email', message: 'Invalid email format' }
];

// With min/max
const rules = [
  'required',
  { type: 'min', value: 8, message: 'Password must be at least 8 characters' },
  { type: 'max', value: 50, message: 'Password must be less than 50 characters' }
];

// Custom validation
const rules = [
  'required',
  {
    type: 'custom',
    validator: (value: string) => {
      return value.includes('@') || 'Must contain @ symbol';
    },
    message: 'Custom validation failed'
  }
];

// Async validation
const rules = [
  'required',
  {
    type: 'custom',
    validator: async (value: string) => {
      const response = await axios.get(`/api/check-availability?email=${value}`);
      return response.data.available || 'Email already taken';
    }
  }
];
```

## Component Props

### FormField

- `name` (string, required) - Field name
- `label` (string, optional) - Field label
- `required` (boolean, optional) - Whether field is required
- `error` (string, optional) - Error message to display
- `hint` (string, optional) - Helper text/hint
- `disabled` (boolean, optional) - Whether field is disabled

### FormInput

- `name` (string, required) - Field name
- `modelValue` (string | number, required) - v-model value
- `type` (string, optional) - Input type (default: 'text')
- `placeholder` (string, optional) - Placeholder text
- `disabled` (boolean, optional) - Whether input is disabled
- `readonly` (boolean, optional) - Whether input is readonly
- `required` (boolean, optional) - Whether input is required
- `rules` (ValidationRule[], optional) - Validation rules
- `validateOn` ('blur' | 'input' | 'submit', optional) - When to validate (default: 'blur')
- `showError` (boolean, optional) - Whether to show error message inline (default: true)
- `showPasswordToggle` (boolean, optional) - Show password toggle for password inputs (default: true)

### FormTextarea

Similar to FormInput, plus:
- `rows` (number, optional) - Number of rows (default: 4)
- `resize` ('none' | 'both' | 'horizontal' | 'vertical', optional) - Resize behavior (default: 'vertical')

### FormSelect

- `name` (string, required) - Field name
- `modelValue` (any, required) - v-model value
- `options` (Option[], required) - Array of {value, label} objects
- `placeholder` (string, optional) - Placeholder text
- Other props similar to FormInput

### FormCheckbox / FormRadio

- `name` (string, required) - Field name
- `modelValue` (boolean for checkbox, any for radio, required) - v-model value
- `label` (string, optional) - Label text
- `value` (any, required for radio) - Radio button value
- Other props similar to FormInput

## useValidation Options

```typescript
const { validateField, validateForm, errors, hasErrors } = useValidation({
  showToast: true,              // Show toast notifications on validation errors
  toastPosition: 'top-right',  // Toast position
  validateOn: 'blur',          // When to validate: 'blur', 'input', 'submit'
  debounce: 300                // Debounce for input validation (ms)
});
```

## Dark Mode

All components automatically support dark mode via TailwindCSS `dark:` classes. No additional configuration needed.

## Accessibility

All components include proper ARIA attributes:
- `aria-invalid` - Set to 'true' when field has error
- `aria-describedby` - References error message element
- `aria-required` - Set to 'true' for required fields
- `role="alert"` - On error messages

## Integration with Toast Notifications

Validation components can optionally show toast notifications on validation errors using the existing `useDialog` composable:

```typescript
const { validateField } = useValidation({ 
  showToast: true,
  toastPosition: 'top-right'
});
```

## Examples

See the component files for more detailed examples and usage patterns.
