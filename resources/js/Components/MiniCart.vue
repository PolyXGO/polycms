<template>
    <div class="mini-cart relative">
        <!-- Cart Icon Button -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            
            <!-- Item Count Badge -->
            <span
                v-if="cart.itemCount > 0"
                class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-black dark:bg-white dark:text-black rounded-full shadow-sm"
            >
                {{ cart.itemCount > 99 ? '99+' : cart.itemCount }}
            </span>
        </button>

        <!-- Slide-over Drawer / Modal Panel -->
        <teleport to="body">
            <!-- Backdrop -->
            <transition
                enter-active-class="transition-opacity ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isOpen"
                    class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[99998]"
                    @click="closeDropdown"
                ></div>
            </transition>

            <!-- Panel Drawer -->
            <transition
                enter-active-class="transition ease-in-out duration-300 transform"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition ease-in-out duration-300 transform"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <div
                    v-if="isOpen"
                    class="fixed right-0 top-0 bottom-0 w-full max-w-md bg-white dark:bg-gray-900 shadow-2xl z-[99999] flex flex-col h-full border-l border-gray-200 dark:border-gray-800"
                >
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Shopping Cart ({{ cart.items.length }})</span>
                        </h3>
                        <button
                            @click="closeDropdown"
                            class="p-2 text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            aria-label="Close cart"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto px-6 py-4 min-h-0">
                        <!-- Empty State -->
                        <div v-if="cart.isEmpty" class="h-full flex flex-col items-center justify-center text-center py-12">
                            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-5">
                                <svg class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mb-1.5">Your cart is empty</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs">Looks like you haven't added anything to your cart yet.</p>
                        </div>

                        <!-- Cart Items -->
                        <ul v-else class="divide-y divide-gray-200 dark:divide-gray-800">
                            <li
                                v-for="(item, index) in cart.items"
                                :key="index"
                                class="py-4 flex items-center space-x-4 first:pt-0 last:pb-0"
                            >
                                <!-- Product Image -->
                                <div class="flex-shrink-0 w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800">
                                    <img
                                        v-if="item.image_url"
                                        :src="item.image_url"
                                        :alt="item.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Item Details -->
                                <div class="flex-1 min-w-0">
                                    <a :href="item.permalink || '#'" class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {{ item.name }}
                                    </a>
                                    <p v-if="item.service_name" class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md inline-block">
                                        Plan: {{ item.service_name }}
                                    </p>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ formatCurrency(item.price) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Qty: {{ item.quantity }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Remove Button -->
                                <button
                                    @click.stop="removeItem(index)"
                                    class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-md transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Footer -->
                    <div v-if="!cart.isEmpty" class="px-6 py-5 border-t border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-900/60 backdrop-blur-sm">
                        <div class="flex justify-between items-center text-sm font-semibold text-gray-600 dark:text-gray-400 mb-4">
                            <span class="text-base font-bold text-gray-900 dark:text-white">Subtotal</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(cart.totals.subtotal || calculateSubtotal) }}</span>
                        </div>
                        <div class="space-y-2.5">
                            <a
                                href="/checkout"
                                class="block w-full text-center px-4 py-3 bg-gray-900 hover:bg-black dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 text-white rounded-lg text-sm font-semibold shadow-sm transition-all"
                            >
                                Checkout
                            </a>
                            <a
                                href="/cart"
                                class="block w-full text-center px-4 py-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg text-sm font-semibold text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700/80 transition-all"
                            >
                                View Cart
                            </a>
                        </div>
                    </div>
                </div>
            </transition>
        </teleport>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useCartStore } from '@/Stores/cartStore';
import { useCurrency } from '@/Composables/useCurrency';

const cart = useCartStore();
const { formatCurrency } = useCurrency();
const isOpen = ref(false);
const maxDisplayItems = 3;

const displayItems = computed(() => {
    return cart.items.slice(0, maxDisplayItems);
});

const hasMoreItems = computed(() => {
    return cart.items.length > maxDisplayItems;
});

const calculateSubtotal = computed(() => {
    return cart.items.reduce((sum, item) => sum + item.price * item.quantity, 0);
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        cart.calculateTotals();
    }
};

const closeDropdown = () => {
    isOpen.value = false;
};

const removeItem = (index: number) => {
    cart.removeItem(index);
};

// Close dropdown on escape key
const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isOpen.value) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
});
</script>

<style scoped>
.mini-cart {
    z-index: 50;
}
</style>
