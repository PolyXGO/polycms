<template>
    <Head :title="t('Shopping Cart')" />
    <div class="py-4 sm:py-6 transition-colors duration-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <CheckoutSteps :step="1" currentStep="cart" />
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mb-6 mt-4">Shopping Cart</h1>

            <!-- Empty Cart State -->
            <div v-if="cart.isEmpty" class="bg-white dark:bg-gray-800 shadow rounded-lg p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Your cart is empty</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Start shopping to add items to your cart.</p>
                <a :href="continueShoppingUrl" class="mt-6 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-gray-900 hover:bg-black dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
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
                                                    <a :href="item.permalink || (item.slug ? `/products/${item.slug}` : '#')" :class="{'hover:text-gray-600 dark:hover:text-gray-300': item.slug || item.permalink}">
                                                        {{ item.name }}
                                                    </a>
                                                </h3>
                                                <!-- Variant Label -->
                                                <p v-if="item.variant_label" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ item.variant_label }}
                                                </p>
                                                <!-- Package Selector Dropdown -->
                                                <div v-if="productDetails[item.product_id]?.services?.length > 0" class="mt-2 mb-2">
                                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
                                                        Selected License/Service Plan:
                                                    </label>
                                                    <select
                                                        :value="item.service_id"
                                                        @change="onServiceChange(index, Number(($event.target as HTMLSelectElement).value))"
                                                        class="mt-1 block w-full max-w-xs rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-gray-900 focus:ring-gray-900 dark:focus:border-gray-400 dark:focus:ring-gray-400 text-sm py-1.5 px-3"
                                                    >
                                                        <option
                                                            v-for="service in productDetails[item.product_id].services"
                                                            :key="service.id"
                                                            :value="service.id"
                                                        >
                                                            {{ service.name }} - {{ formatCurrency(service.price) }} ({{ getServiceCycleLabel(service) }})
                                                        </option>
                                                    </select>
                                                    
                                                    <!-- Selected Package Description & Benefits -->
                                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 max-w-md">
                                                        <span class="font-semibold text-gray-700 dark:text-gray-300">Plan benefits:</span>
                                                        <p class="mt-0.5 whitespace-pre-line leading-relaxed">{{ getSelectedServiceExplanation(item.product_id, item.service_id) }}</p>
                                                    </div>
                                                </div>
                                                <p v-else-if="item.service_name" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    Plan: {{ item.service_name }}
                                                </p>
                                                <p v-if="item.sku" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                                    SKU: {{ item.sku }}
                                                </p>
                                                <!-- Applied Offer Badge -->
                                                <div v-if="item.offer_label || item.metadata?.offer_label" 
                                                     @click="offersModalRef?.open(item.product_id)"
                                                     title="Click to view offer guidelines"
                                                     class="mt-1.5 inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 px-2.5 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800 text-xs font-semibold cursor-pointer transition-colors group">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                    <span>{{ item.offer_label || item.metadata?.offer_label }}</span>
                                                    <span v-if="item.offer_discount || item.metadata?.total_offer_discount" class="text-[11px] opacity-80">
                                                        (-{{ formatCurrency(item.offer_discount || item.metadata?.total_offer_discount) }})
                                                    </span>
                                                    <svg class="w-3 h-3 text-emerald-500 opacity-70 group-hover:opacity-100 ml-0.5 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                                    {{ formatCurrency(item.price * item.quantity) }}
                                                </p>
                                                <p v-if="(item.original_price && item.original_price > item.price) || (item.metadata?.original_price && item.metadata.original_price > item.price)" class="text-xs line-through text-gray-400 dark:text-gray-500">
                                                    {{ formatCurrency((item.original_price || item.metadata.original_price) * item.quantity) }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Stock Error -->
                                        <p v-if="item.stock_error" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.27 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                            {{ item.stock_error }}
                                        </p>

                                        <!-- Anti-Scalping Max Per Order Badge -->
                                        <div v-if="item.max_per_order && item.max_per_order > 0" class="mt-2">
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-1 rounded-md border border-amber-200/80 dark:border-amber-800/60">
                                                <span>🏷️ Max {{ item.max_per_order }} per order</span>
                                                <span v-if="item.quantity >= item.max_per_order" class="font-bold text-amber-900 dark:text-amber-200">(Limit reached)</span>
                                            </span>
                                        </div>

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
                                                    :disabled="!!(item.max_per_order && item.quantity >= item.max_per_order)"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent"
                                                    :title="item.max_per_order && item.quantity >= item.max_per_order ? 'Maximum purchase limit reached' : ''"
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
                        <a :href="continueShoppingUrl" class="text-gray-900 dark:text-white hover:text-gray-600 dark:hover:text-gray-300 font-medium">
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
                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-gray-900 focus:ring-gray-900 dark:focus:border-gray-400 dark:focus:ring-gray-400"
                                />
                                <button
                                    @click="applyCoupon"
                                    :disabled="!couponInput || cart.loading"
                                    class="px-4 py-2 bg-gray-800 dark:bg-gray-600 text-white rounded-md hover:bg-gray-700 dark:hover:bg-gray-500 disabled:opacity-50"
                                >
                                    Apply
                                </button>
                            </div>
                            <p v-if="localCouponError || cart.couponError" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ localCouponError || cart.couponError }}</span>
                            </p>

                            <!-- Coupon List -->
                            <div v-if="cart.couponCodes.length > 0" class="mt-4 space-y-2">
                                <template v-for="code in cart.couponCodes" :key="code">
                                    <div v-if="getAppliedCoupon(code)" class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 px-3 py-2 rounded-md border border-green-200 dark:border-green-800">
                                        <div class="flex items-center text-sm text-green-700 dark:text-green-400">
                                            <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
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
                                </template>
                            </div>

                            <!-- Available Coupons List (Clean & Compact) -->
                            <div v-if="availableCoupons.length > 0" class="mt-4 pt-3 border-t border-dashed border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Available Coupons</span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ availableCoupons.length }} available</span>
                                </div>
                                <div class="space-y-1.5">
                                    <div 
                                        v-for="coupon in availableCoupons" 
                                        :key="coupon.id" 
                                        class="flex items-center justify-between p-2 rounded-lg bg-gray-50/70 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700/50 text-xs transition-colors hover:bg-gray-100/70 dark:hover:bg-gray-700/60"
                                    >
                                        <div class="flex items-center gap-2 min-w-0 pr-2">
                                            <span class="font-mono font-bold text-xs px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300/60 dark:border-emerald-700/50 shrink-0">
                                                {{ coupon.code }}
                                            </span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400 shrink-0">
                                                {{ coupon.type === 'fixed_amount' ? formatCurrency(coupon.value) : Number(coupon.value) + '%' }} OFF
                                            </span>
                                            <span v-if="coupon.title || coupon.description" class="text-gray-500 dark:text-gray-400 truncate hidden sm:inline text-[11px]" :title="coupon.title || coupon.description">
                                                - {{ coupon.title || coupon.description }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button 
                                                @click="applyCoupon(coupon.code)" 
                                                type="button" 
                                                :disabled="isCouponApplied(coupon.code)"
                                                class="font-semibold transition-colors focus:outline-none text-xs"
                                                :class="isCouponApplied(coupon.code) ? 'text-gray-400 dark:text-gray-500 cursor-default' : 'text-gray-900 hover:text-black dark:text-gray-100 dark:hover:text-white cursor-pointer'"
                                            >
                                                {{ isCouponApplied(coupon.code) ? '✓ Applied' : 'Apply' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
                            <div class="flex justify-between text-base text-gray-600 dark:text-gray-400">
                                <span>Subtotal</span>
                                <span>{{ formatCurrency(cart.totals.subtotal || 0) }}</span>
                            </div>
                            <div v-if="Number(cart.totals.total_volume_discount || 0) > 0" class="flex justify-between text-base text-emerald-600 dark:text-emerald-400 font-medium">
                                <span>Volume Discount</span>
                                <span>-{{ formatCurrency(cart.totals.total_volume_discount || 0) }}</span>
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
                                class="w-full flex items-center justify-center px-6 py-4 border border-transparent text-base font-medium rounded-md !text-white hover:!text-white bg-gray-900 hover:bg-black dark:bg-gray-100 dark:!text-gray-900 dark:hover:bg-white dark:hover:!text-gray-900 shadow-sm"
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
    <OffersGuidelineModal ref="offersModalRef" />
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useCartStore } from '@/Stores/cartStore';
import { useCurrency } from '@/Composables/useCurrency';
import { useTranslation } from '@/admin/composables/useTranslation';
import CheckoutSteps from '@/Components/CheckoutSteps.vue';
import OffersGuidelineModal from '@/Components/OffersGuidelineModal.vue';
import axios from 'axios';

const { t } = useTranslation();
const offersModalRef = ref<any>(null);

const props = defineProps({
    continueShoppingUrl: {
        type: String,
        default: '/products'
    }
});

const cart = useCartStore();
const { formatCurrency } = useCurrency();
const couponInput = ref('');
const productDetails = ref<Record<number, any>>({});

const loadServicesForCartItems = async () => {
    for (const item of cart.items) {
        if (item.product_id && !productDetails.value[item.product_id]) {
            try {
                const res = await axios.get(`/api/v1/products/${item.product_id}`);
                if (res.data && res.data.data) {
                    productDetails.value[item.product_id] = res.data.data;
                }
            } catch (e) {
                console.error(`Failed to fetch product details for ${item.product_id}`, e);
            }
        }
    }
};

watch(() => cart.items, async () => {
    await loadServicesForCartItems();
}, { deep: true });

onMounted(async () => {
    // Sync with server first, then calculate totals
    await cart.syncWithServer();
    cart.calculateTotals();
    await loadServicesForCartItems();
    fetchAvailableCoupons();
});

const onServiceChange = async (index: number, serviceId: number) => {
    await cart.updateServicePackage(index, serviceId);
};

const getServiceCycleLabel = (service: any) => {
    if (!service) return '';
    if (service.access_type === 'permanent') return 'Lifetime';
    const value = service.duration_value || 1;
    const unit = service.duration_unit || 'month';
    return `${value} ${value > 1 ? unit + 's' : unit}`;
};

const getSelectedServiceExplanation = (productId: number, serviceId: number) => {
    const product = productDetails.value[productId];
    if (!product || !product.services) return '';
    const service = product.services.find((s: any) => s.id === serviceId);
    if (!service) return '';
    
    const price = service.price !== null && service.price !== undefined ? `${Number(service.price).toFixed(2)}` : '0.00';
    if (service.access_type === 'permanent') {
        return `Lifetime access (One-time payment of $${price}).`;
    }
    
    const value = service.duration_value || 1;
    const unit = service.duration_unit || 'month';
    const unitLabel = value > 1 ? `${unit}s` : unit;
    
    const recurringEn = service.is_recurring ? 'Auto-renewing subscription' : 'One-time subscription';
    let explanation = `${recurringEn} ($${price} for ${value} ${unitLabel}).`;
    
    if (service.trial_period_days && Number(service.trial_period_days) > 0) {
        explanation += `\nTrial: +${service.trial_period_days} days trial for first order only (Total first license: ${value} ${unitLabel} + ${service.trial_period_days} days). Customers are eligible for trial only once per product.`;
    }
    
    return explanation;
};

const incrementQuantity = async (index: number) => {
    try {
        await cart.updateQuantity(index, cart.items[index].quantity + 1);
    } catch (e: any) {
        cart.items[index].stock_error = e.message;
    }
};

const decrementQuantity = async (index: number) => {
    if (cart.items[index].quantity > 1) {
        try {
            await cart.updateQuantity(index, cart.items[index].quantity - 1);
        } catch (e: any) {
            cart.items[index].stock_error = e.message;
        }
    }
};

const removeItem = (index: number) => {
    cart.removeItem(index);
};

const availableCoupons = ref<any[]>([]);

const fetchAvailableCoupons = async () => {
    try {
        const response = await axios.get('/api/v1/checkout/coupons');
        availableCoupons.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching coupons', error);
    }
};

const isCouponApplied = (code: string) => {
    return (cart.totals.applied_coupons || []).some((c: any) => c.code?.toUpperCase() === code?.toUpperCase());
};

const localCouponError = ref('');

const applyCoupon = async (code: string | null = null) => {
    localCouponError.value = '';
    const codeToApply = code || couponInput.value;
    if (!codeToApply) return;
    try {
        await cart.applyCoupon(codeToApply);
        if (!code) couponInput.value = '';
    } catch (e: any) {
        localCouponError.value = e.message || cart.couponError || 'Invalid coupon code';
    }
};

const removeCoupon = async (code: string) => {
    await cart.removeCoupon(code);
};

const getAppliedCoupon = (code: string) => {
    return cart.totals.applied_coupons?.find(c => c.code === code);
};
</script>
