import { defineStore } from 'pinia';
import { t } from '../composables/useTranslation';

export interface AlertOptions {
    title?: string;
    message: string;
    confirmText?: string;
    type?: 'info' | 'success' | 'warning' | 'error';
}

export interface ConfirmOptions {
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    type?: 'info' | 'warning' | 'danger';
    onConfirm?: () => void | Promise<void>;
    onCancel?: () => void;
}

export interface ModalOptions {
    title?: string;
    component?: any;
    props?: Record<string, any>;
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full' | 'wide';
    closable?: boolean;
    onClose?: () => void;
}

export interface MessageOptions {
    message: string;
    type?: 'success' | 'error' | 'warning' | 'info';
    duration?: number; // milliseconds, 0 = no auto close
    position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
}

interface DialogState {
    alertDialog: {
        show: boolean;
        title?: string;
        message: string;
        confirmText: string;
        type: 'info' | 'success' | 'warning' | 'error';
        resolve?: () => void;
    };
    confirmDialog: {
        show: boolean;
        title?: string;
        message: string;
        confirmText: string;
        cancelText: string;
        type: 'info' | 'warning' | 'danger';
        resolve?: (value: boolean) => void;
    };
    modal: {
        show: boolean;
        title?: string;
        component: any;
        props: Record<string, any>;
        size: 'sm' | 'md' | 'lg' | 'xl' | 'full' | 'wide';
        closable: boolean;
        onClose?: () => void;
    };
    messages: MessageOptions[];
}

export const useDialogStore = defineStore('dialog', {
    state: (): DialogState => ({
        alertDialog: {
            show: false,
            message: '',
            confirmText: t('OK'),

            type: 'info',
        },
        confirmDialog: {
            show: false,
            message: '',
            confirmText: t('Confirm'),
            cancelText: t('Cancel'),
            type: 'info',
        },
        modal: {
            show: false,
            component: null,
            props: {},
            size: 'md',
            closable: true,
        },
        messages: [],
    }),

    actions: {
        /**
         * Show alert dialog
         */
        alert(options: AlertOptions): Promise<void> {
            return new Promise((resolve) => {
                this.alertDialog = {
                    show: true,
                    title: options.title,
                    message: options.message,
                    confirmText: options.confirmText || t('OK'),
                    type: options.type || 'info',
                    resolve,
                };
            });
        },

        /**
         * Close alert dialog
         */
        closeAlert() {
            if (this.alertDialog.resolve) {
                this.alertDialog.resolve();
            }
            this.alertDialog.show = false;
        },

        /**
         * Show confirm dialog
         */
        confirm(options: ConfirmOptions): Promise<boolean> {
            return new Promise((resolve) => {
                this.confirmDialog = {
                    show: true,
                    title: options.title,
                    message: options.message,
                    confirmText: options.confirmText || t('Confirm'),
                    cancelText: options.cancelText || t('Cancel'),
                    type: options.type || 'info',
                    resolve,
                };
            });
        },

        /**
         * Close confirm dialog with result
         */
        closeConfirm(result: boolean) {
            if (this.confirmDialog.resolve) {
                this.confirmDialog.resolve(result);
            }
            this.confirmDialog.show = false;
        },

        /**
         * Show modal
         */
        showModal(options: ModalOptions) {
            this.modal = {
                show: true,
                title: options.title,
                component: options.component,
                props: options.props || {},
                size: options.size || 'md',
                closable: options.closable !== false,
                onClose: options.onClose,
            };
        },

        /**
         * Close modal
         */
        closeModal() {
            if (this.modal.onClose) {
                this.modal.onClose();
            }
            this.modal.show = false;
            this.modal.component = null;
            this.modal.props = {};
        },

        /**
         * Show message/toast
         */
        message(options: MessageOptions) {
            const id = Date.now() + Math.random();
            const message: MessageOptions = {
                message: options.message,
                type: options.type || 'info',
                duration: options.duration !== undefined ? options.duration : 5000,
                position: options.position || 'top-right',
            };

            this.messages.push(message);

            // Auto remove after duration
            if (message.duration && message.duration > 0) {
                setTimeout(() => {
                    this.removeMessage(message);
                }, message.duration);
            }

            return () => this.removeMessage(message);
        },

        /**
         * Remove message
         */
        removeMessage(message: MessageOptions) {
            const index = this.messages.indexOf(message);
            if (index > -1) {
                this.messages.splice(index, 1);
            }
        },

        /**
         * Helper methods for common message types
         */
        success(message: string, duration?: number) {
            return this.message({ message, type: 'success', duration });
        },

        error(message: string, duration?: number) {
            return this.message({ message, type: 'error', duration: duration || 7000 });
        },

        warning(message: string, duration?: number) {
            return this.message({ message, type: 'warning', duration });
        },

        info(message: string, duration?: number) {
            return this.message({ message, type: 'info', duration });
        },
    },
});

