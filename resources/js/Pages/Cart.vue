<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <CheckoutSteps :step="1" />
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 mt-8">Shopping Cart</h1>

            <!-- Empty Cart State -->
            <div v-if="cart.isEmpty" class="bg-white dark:bg-gray-800 shadow rounded-lg p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Your cart is empty</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Start shopping to add items to your cart.</p>
                <a :href="continueShoppingUrl" class="mt-6 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Continue Shopping
                </a>
            </div>

            <!-- Cart with Items -->
            <div v-else class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start">
                <!-- Cart Items -->
                <section class="lg:col-span-7">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                        <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <li v-for="(item, index) in cart.items" :key="index" class="p-6">
                                <div class="flex items-start">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0 w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-md overflow-hidden">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="ml-6 flex-1">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                    <a :href="item.permalink || (item.slug ? `/products/${item.slug}` : '#')" :class="{'hover:text-indigo-600 dark:hover:text-indigo-400': item.slug || item.permalink}">
                                                        {{ item.name }}
                                                    </a>
                                                </h3>
                                                <!-- Variant Label -->
                                                <p v-if="item.variant_label" class="mt-1 text-sm text-indigo-500 dark:text-indigo-400">
                                                    {{ item.variant_label }}
                                                </p>
                                                <p v-if="item.service_name" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    Plan: {{ item.service_name }}
                                                </p>
                                                <p v-if="item.sku" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                                    SKU: {{ item.sku }}
                                                </p>
                                            </div>
                                            <p class="text-lg font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(item.price * item.quantity) }}
                                            </p>
                                        </div>

                                        <!-- Stock Error -->
                                        <p v-if="item.stock_error" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.27 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                            {{ item.stock_error }}
                                        </p>

                                        <!-- Quantity Controls -->
                                        <div class="mt-4 flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <button
                                                    @click="decrementQuantity(index)"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                    </svg>
                                                </button>
                                                <span class="text-gray-900 dark:text-white font-medium w-8 text-center">
                                                    {{ item.quantity }}
                                                </span>
                                                <button
                                                    @click="incrementQuantity(index)"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <button
                                                @click="removeItem(index)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Continue Shopping -->
                    <div class="mt-6">
                        <a :href="continueShoppingUrl" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                            ← Continue Shopping
                        </a>
                    </div>
                </section>

                <!-- Order Summary -->
                <section class="mt-10 lg:mt-0 lg:col-span-5">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 sticky top-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Order Summary</h2>

                        <!-- Coupon Code -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Discount Code</label>
                            <div class="flex space-x-2">
                                <input
                                    v-model="couponInput"
                                    type="text"
                                    placeholder="Enter coupon code"
                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    @click="applyCoupon"
                                    :disabled="!couponInput || cart.loading"
                                    class="px-4 py-2 bg-gray-800 dark:bg-gray-600 text-white rounded-md hover:bg-gray-700 dark:hover:bg-gray-500 disabled:opacity-50"
                                >
                                    Apply
                                </button>
                            </div>
                            <p v-if="localCouponError" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ localCouponError }}
                            </p>

                            <!-- Coupon List -->
                            <div v-if="cart.couponCodes.length > 0" class="mt-4 space-y-2">
                                <template v-for="code in cart.couponCodes" :key="code">
                                    <div v-if="getAppliedCoupon(code)" class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 px-3 py-2 rounded-md border border-green-200 dark:border-green-800">
                                        <div class="flex items-center text-sm text-green-700 dark:text-green-400">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                            <div class="flex flex-col">
                                                <span class="font-medium uppercase">{{ code }}</span>
                                                <span v-if="getAppliedCoupon(code)?.title" class="text-xs text-green-600 dark:text-green-500">{{ getAppliedCoupon(code)?.title }}</span>
                                            </div>
                                            <span class="ml-2 font-semibold">(-{{ formatCurrency(getAppliedCoupon(code)?.discount || 0) }})</span>
                                        </div>
                                        <button @click="removeCoupon(code)" type="button" class="text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 ml-4">
                                            Remove
                                        </button>
                                    </div>
                                    <div v-else class="flex items-center justify-between bg-red-50 dark:bg-red-900/20 px-3 py-2 rounded-md border border-red-200 dark:border-red-800">
                                        <div class="flex items-center text-sm text-red-700 dark:text-red-400">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div class="flex flex-col">
                                                <span class="font-medium uppercase">{{ code }}</span>
                                                <span class="text-xs text-red-600 dark:text-red-500">Invalid code</span>
                                            </div>
                                        </div>
                                        <button @click="removeCoupon(code)" type="button" class="text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 ml-4">
                                            Remove
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
                            <div class="flex justify-between text-base text-gray-600 dark:text-gray-400">
                                <span>Subtotal</span>
                                <span>{{ formatCurrency(cart.totals.subtotal || 0) }}</span>
                            </div>
                            <div v-if="Number(cart.totals.discount || 0) > 0" class="flex justify-between text-base text-green-600 dark:text-green-400">
                                <span>Discount</span>
                                <span>-{{ formatCurrency(cart.totals.discount || 0) }}</span>
                            </div>
                            <div v-if="Number(cart.totals.tax || 0) > 0" class="flex justify-between text-base text-gray-600 dark:text-gray-400">
                                <span>Tax</span>
                                <span>{{ formatCurrency(cart.totals.tax || 0) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-4">
                                <span>Total</span>
                                <span>{{ formatCurrency(cart.totals.total || 0) }}</span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <div class="mt-6">
                            <a
                                href="/checkout"
                                class="w-full flex items-center justify-center px-6 py-4 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm"
                            >
                                Proceed to Checkout
                            </a>
                        </div>

                        <p class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Secure checkout powered by PayPal
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useCartStore } from '@/Stores/cartStore';
import { useCurrency } from '@/Composables/useCurrency';
import CheckoutSteps from '@/Components/CheckoutSteps.vue';

const props = defineProps({
    continueShoppingUrl: {
        type: String,
        default: '/products'
    }
});

const cart = useCartStore();
const { formatCurrency } = useCurrency();
const couponInput = ref('');

onMounted(async () => {
    // Sync with server first, then calculate totals
    await cart.syncWithServer();
    cart.calculateTotals();
});

const incrementQuantity = async (index: number) => {
    try {
        await cart.updateQuantity(index, cart.items[index].quantity + 1);
    } catch (e: any) {
        // Stock limit — show inline error
        cart.items[index].stock_error = e.message;
        setTimeout(() => { cart.items[index].stock_error = ''; }, 3000);
    }
};

const decrementQuantity = (index: number) => {
    if (cart.items[index].quantity > 1) {
        cart.updateQuantity(index, cart.items[index].quantity - 1);
    }
};

const removeItem = (index: number) => {
    cart.removeItem(index);
};

const localCouponError = ref('');

const applyCoupon = async () => {
    localCouponError.value = '';
    if (couponInput.value) {
        try {
            await cart.applyCoupon(couponInput.value);
            couponInput.value = '';
        } catch (e: any) {
            localCouponError.value = e.message;
        }
    }
};

const removeCoupon = async (code: string) => {
    await cart.removeCoupon(code);
};

const getAppliedCoupon = (code: string) => {
    return cart.totals.applied_coupons?.find(c => c.code === code);
};
</script>
