import { useDialogStore, type AlertOptions, type ConfirmOptions, type ModalOptions, type MessageOptions } from '../stores/dialog';

/**
 * Composable for easy access to dialog functions
 * 
 * @example
 * ```ts
 * const dialog = useDialog();
 * 
 * // Alert
 * await dialog.alert({ message: 'Operation successful!' });
 * 
 * // Confirm
 * const confirmed = await dialog.confirm({ 
 *   message: 'Are you sure?' 
 * });
 * if (confirmed) {
 *   // proceed
 * }
 * 
 * // Message
 * dialog.success('Saved successfully!');
 * dialog.error('Something went wrong!');
 * 
 * // Modal
 * dialog.showModal({
 *   title: 'Settings',
 *   component: SettingsComponent,
 *   props: { id: 123 }
 * });
 * ```
 */
export function useDialog() {
    const dialogStore = useDialogStore();

    // Cache action methods to avoid conflicts with state properties
    const alertAction = dialogStore.alert.bind(dialogStore);
    const confirmAction = dialogStore.confirm.bind(dialogStore);

    return {
        /**
         * Show alert dialog
         */
        alert: (options: AlertOptions | string) => {
            if (typeof options === 'string') {
                return alertAction({ message: options });
            }
            return alertAction(options);
        },

        /**
         * Show confirm dialog
         */
        confirm: (options: ConfirmOptions | string) => {
            if (typeof options === 'string') {
                return confirmAction({ message: options });
            }
            return confirmAction(options);
        },

        /**
         * Show modal
         */
        showModal: (options: ModalOptions) => {
            dialogStore.showModal(options);
        },

        /**
         * Close modal
         */
        closeModal: () => {
            dialogStore.closeModal();
        },

        /**
         * Show message/toast
         */
        message: (options: MessageOptions | string) => {
            if (typeof options === 'string') {
                return dialogStore.message({ message: options });
            }
            return dialogStore.message(options);
        },

        /**
         * Show success message
         */
        success: (message: string, duration?: number) => {
            return dialogStore.success(message, duration);
        },

        /**
         * Show error message
         */
        error: (message: string, duration?: number) => {
            return dialogStore.error(message, duration);
        },

        /**
         * Show warning message
         */
        warning: (message: string, duration?: number) => {
            return dialogStore.warning(message, duration);
        },

        /**
         * Show info message
         */
        info: (message: string, duration?: number) => {
            return dialogStore.info(message, duration);
        },
    };
}

