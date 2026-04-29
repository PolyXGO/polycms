<template>
    <Teleport to="body">
        <!-- Render messages grouped by position -->
        <template v-for="position in positions" :key="position">
            <div
                v-if="messagesByPosition(position).length > 0"
                :class="[
                    'fixed z-[999999] pointer-events-none',
                    getPositionClass(position)
                ]"
            >
                <TransitionGroup
                    name="message"
                    tag="div"
                    class="space-y-2"
                >
                    <div
                        v-for="(message, index) in messagesByPosition(position)"
                        :key="`${position}-${index}-${message.message}`"
                        :class="[
                            'pointer-events-auto max-w-sm w-full rounded-lg shadow-lg overflow-hidden',
                            messageBgClass(message.type)
                        ]"
                    >
                    <div class="p-4">
                        <div class="flex items-start">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <svg
                                    class="h-5 w-5"
                                    :class="messageIconClass(message.type)"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        :d="messageIconPath(message.type)"
                                    />
                                </svg>
                            </div>
                            <!-- Content -->
                            <div class="ml-3 flex-1">
                                <p
                                    class="text-sm font-medium"
                                    :class="messageTextClass(message.type)"
                                >
                                    {{ message.message }}
                                </p>
                            </div>
                            <!-- Close Button -->
                            <div class="ml-4 flex-shrink-0">
                                <button
                                    @click="dialogStore.removeMessage(message)"
                                    :class="[
                                        'inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
                                        messageCloseButtonClass(message.type)
                                    ]"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    </div>
                </TransitionGroup>
            </div>
        </template>
    </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useDialogStore, type MessageOptions } from '../../stores/dialog';

const dialogStore = useDialogStore();

const positions = ['top-right', 'top-left', 'bottom-right', 'bottom-left', 'top-center', 'bottom-center'] as const;

const messagesByPosition = (position: string) => {
    return dialogStore.messages.filter(msg => (msg.position || 'top-right') === position);
};

const getPositionClass = (position: string) => {
    const positions: Record<string, string> = {
        'top-right': 'top-4 right-4',
        'top-left': 'top-4 left-4',
        'bottom-right': 'bottom-4 right-4',
        'bottom-left': 'bottom-4 left-4',
        'top-center': 'top-4 left-1/2 transform -translate-x-1/2',
        'bottom-center': 'bottom-4 left-1/2 transform -translate-x-1/2',
    };
    return positions[position] || positions['top-right'];
};

const messageBgClass = (type?: string) => {
    const types: Record<string, string> = {
        success: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800',
        error: 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800',
        warning: 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800',
        info: 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800',
    };
    return types[type || 'info'] || types.info;
};

const messageIconClass = (type?: string) => {
    const types: Record<string, string> = {
        success: 'text-green-600 dark:text-green-400',
        error: 'text-red-600 dark:text-red-400',
        warning: 'text-yellow-600 dark:text-yellow-400',
        info: 'text-blue-600 dark:text-blue-400',
    };
    return types[type || 'info'] || types.info;
};

const messageTextClass = (type?: string) => {
    const types: Record<string, string> = {
        success: 'text-green-800 dark:text-green-200',
        error: 'text-red-800 dark:text-red-200',
        warning: 'text-yellow-800 dark:text-yellow-200',
        info: 'text-blue-800 dark:text-blue-200',
    };
    return types[type || 'info'] || types.info;
};

const messageCloseButtonClass = (type?: string) => {
    const types: Record<string, string> = {
        success: 'text-green-500 hover:text-green-700 dark:hover:text-green-300 focus:ring-green-500',
        error: 'text-red-500 hover:text-red-700 dark:hover:text-red-300 focus:ring-red-500',
        warning: 'text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-300 focus:ring-yellow-500',
        info: 'text-blue-500 hover:text-blue-700 dark:hover:text-blue-300 focus:ring-blue-500',
    };
    return types[type || 'info'] || types.info;
};

const messageIconPath = (type?: string) => {
    const types: Record<string, string> = {
        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };
    return types[type || 'info'] || types.info;
};
</script>

<style scoped>
.message-enter-active,
.message-leave-active {
    transition: all 0.3s ease;
}

.message-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.message-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.message-enter-to,
.message-leave-from {
    opacity: 1;
    transform: translateX(0);
}
</style>

