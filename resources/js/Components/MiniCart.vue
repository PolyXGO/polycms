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
                class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-indigo-600 rounded-full"
            >
                {{ cart.itemCount > 99 ? '99+' : cart.itemCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-50"
            >
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Shopping Cart
                    </h3>
                </div>

                <!-- Empty State -->
                <div v-if="cart.isEmpty" class="px-4 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Your cart is empty</p>
                </div>

                <!-- Cart Items -->
                <div v-else class="max-h-64 overflow-y-auto">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="(item, index) in displayItems"
                            :key="index"
                            class="px-4 py-3 flex items-center space-x-3"
                        >
                            <!-- Product Image -->
                            <div class="flex-shrink-0 w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded overflow-hidden">
                                <img
                                    v-if="item.image_url"
                                    :src="item.image_url"
                                    :alt="item.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Item Details -->
                            <div class="flex-1 min-w-0">
                                <a :href="item.permalink || '#'" class="text-sm font-medium text-gray-900 dark:text-white truncate hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ item.name }}
                                </a>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.quantity }} × {{ formatCurrency(item.price) }}
                                </p>
                            </div>

                            <!-- Remove Button -->
                            <button
                                @click.stop="removeItem(index)"
                                class="text-gray-400 hover:text-red-500 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </li>
                    </ul>

                    <!-- More Items Notice -->
                    <div v-if="hasMoreItems" class="px-4 py-2 bg-gray-50 dark:bg-gray-900 text-sm text-gray-500 dark:text-gray-400 text-center">
                        +{{ cart.items.length - maxDisplayItems }} more items
                    </div>
                </div>

                <!-- Subtotal -->
                <div v-if="!cart.isEmpty" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                        <span>Subtotal</span>
                        <span>{{ formatCurrency(cart.totals.subtotal || calculateSubtotal) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div v-if="!cart.isEmpty" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                    <a
                        href="/cart"
                        class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        View Cart
                    </a>
                    <a
                        href="/checkout"
                        class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors"
                    >
                        Checkout
                    </a>
                </div>
            </div>
        </transition>

        <!-- Backdrop -->
        <div
            v-if="isOpen"
            class="fixed inset-0 z-40"
            @click="closeDropdown"
        ></div>
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
