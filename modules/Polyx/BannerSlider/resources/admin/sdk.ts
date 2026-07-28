/**
 * PolyCMS SDK Shim for BannerSlider Module
 *
 * Re-exports core composables, components, stores and utilities
 * from the global SDK so module code can use clean imports:
 *
 *   import { useDialog, useTranslation, MediaPicker } from '@polycms';
 *
 * Standard library imports (vue, vue-router, pinia, axios) do NOT
 * need this shim — they are handled by Vite externals + globals.
 */

const SDK = (window as any).__POLYCMS_SDK__;

// --- Composables ---
export const useDialog = SDK.useDialog;
export const useTranslation = SDK.useTranslation;
export const loadTranslations = SDK.loadTranslations;
export const t = SDK.t;
export const useTableSort = SDK.useTableSort;
export const usePagination = SDK.usePagination;
export const useValidation = SDK.useValidation;
export const useSortable = SDK.useSortable;
export const useGlobalSaveHotkey = SDK.useGlobalSaveHotkey;

// --- Shared Components ---
export const MediaPicker = SDK.MediaPicker;
export const HelpGuide = SDK.HelpGuide;
export const MenuStructure = SDK.MenuStructure;
export const FormIconPicker = SDK.FormIconPicker;
export const FormField = SDK.FormField;
export const FormInput = SDK.FormInput;
export const FormToggle = SDK.FormToggle;
export const FormCountrySelect = SDK.FormCountrySelect;
export const Modal = SDK.Modal;
export const SortableHeader = SDK.SortableHeader;
export const DataPagination = SDK.DataPagination;

// --- Stores ---
export const useDialogStore = SDK.useDialogStore;
export const useAuthStore = SDK.useAuthStore;
export const useThemeStore = SDK.useThemeStore;

// --- Utils ---
export const Storage = SDK.Storage;

// --- Router helpers ---
export const registerRoutes = SDK.registerRoutes;
