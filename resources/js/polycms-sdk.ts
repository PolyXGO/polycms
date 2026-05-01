/**
 * PolyCMS SDK — Development-time shim
 *
 * During development (monolithic Vite build), modules use this file
 * to resolve `@polycms` imports. It re-exports directly from the
 * actual core source files.
 *
 * In production standalone builds, each module has its OWN sdk.ts
 * that reads from window.__POLYCMS_SDK__ instead.
 */

// Composables
export { useDialog } from './admin/composables/useDialog';
export { useTranslation, loadTranslations, t } from './admin/composables/useTranslation';
export { useTableSort } from './admin/composables/useTableSort';
export { usePagination } from './admin/composables/usePagination';
export { useValidation } from './admin/composables/useValidation';
export { useSortable } from './admin/composables/useSortable';
export { useGlobalSaveHotkey } from './admin/composables/useGlobalSaveHotkey';

// Shared Components
export { default as MediaPicker } from './admin/components/MediaPicker';
export { default as HelpGuide } from './admin/components/HelpGuide.vue';
export { default as MenuStructure } from './admin/views/menus/MenuStructure.vue';
export { default as FormIconPicker } from './admin/components/forms/FormIconPicker.vue';
export { default as FormField } from './admin/components/forms/FormField.vue';
export { default as FormInput } from './admin/components/forms/FormInput.vue';
export { default as FormToggle } from './admin/components/forms/FormToggle.vue';
export { default as FormCountrySelect } from './admin/components/forms/FormCountrySelect.vue';
export { default as Modal } from './admin/components/dialogs/Modal.vue';
export { default as SortableHeader } from './admin/components/table/SortableHeader.vue';
export { default as DataPagination } from './admin/components/table/DataPagination.vue';

// Stores
export { useDialogStore } from './admin/stores/dialog';
export { useAuthStore } from './admin/stores/auth';
export { useThemeStore } from './admin/stores/theme';

// Utils
export { Storage } from './admin/utils/storage';

// Router helpers (no-op during dev — routes are loaded via import.meta.glob)
export const registerRoutes = (): void => {
    // During development, routes are registered statically via import.meta.glob in router/index.ts
    // This function is only used in standalone production builds
};
