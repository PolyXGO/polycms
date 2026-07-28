/**
 * Global dialog helper functions
 * These functions can be imported and used anywhere in the application
 * 
 * @example
 * ```ts
 * import { showAlert, showConfirm, showSuccess } from '@/admin/utils/dialog';
 * 
 * await showAlert('Operation successful!');
 * const confirmed = await showConfirm('Are you sure?');
 * showSuccess('Saved!');
 * ```
 */

import { useDialogStore } from '../stores/dialog';
import type { AlertOptions, ConfirmOptions, MessageOptions } from '../stores/dialog';

// Get store instance
const getDialogStore = () => {
    // This will work if called from within a Vue component context
    // For global usage, we'll need to ensure the store is available
    return useDialogStore();
};

/**
 * Show alert dialog
 */
export const showAlert = async (options: AlertOptions | string): Promise<void> => {
    const store = getDialogStore();
    if (typeof options === 'string') {
        return store.alert({ message: options });
    }
    return store.alert(options);
};

/**
 * Show confirm dialog
 */
export const showConfirm = async (options: ConfirmOptions | string): Promise<boolean> => {
    const store = getDialogStore();
    if (typeof options === 'string') {
        return store.confirm({ message: options });
    }
    return store.confirm(options);
};

/**
 * Show success message
 */
export const showSuccess = (message: string, duration?: number) => {
    const store = getDialogStore();
    return store.success(message, duration);
};

/**
 * Show error message
 */
export const showError = (message: string, duration?: number) => {
    const store = getDialogStore();
    return store.error(message, duration);
};

/**
 * Show warning message
 */
export const showWarning = (message: string, duration?: number) => {
    const store = getDialogStore();
    return store.warning(message, duration);
};

/**
 * Show info message
 */
export const showInfo = (message: string, duration?: number) => {
    const store = getDialogStore();
    return store.info(message, duration);
};

/**
 * Show message/toast
 */
export const showMessage = (options: MessageOptions | string) => {
    const store = getDialogStore();
    if (typeof options === 'string') {
        return store.message({ message: options });
    }
    return store.message(options);
};

