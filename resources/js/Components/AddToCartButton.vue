<template>
    <div class="add-to-cart-container">
        <!-- Quantity Selector -->
        <div v-if="showQuantity" class="quantity-selector mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quantity</label>
            <div class="flex items-center space-x-3">
                <button
                    @click="decrementQty"
                    :disabled="quantity <= 1"
                    class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </button>
                <input
                    v-model.number="quantity"
                    type="number"
                    min="1"
                    class="w-16 text-center rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                <button
                    @click="incrementQty"
                    class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Add to Cart Button -->
        <button
            @click="addToCart"
            :disabled="isDisabled || loading"
            class="w-full flex items-center justify-center px-6 py-4 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
            :class="{ 'animate-pulse': loading }"
        >
            <svg v-if="!loading && !added" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <svg v-if="loading" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-if="added" class="w-5 h-5 mr-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ buttonText }}</span>
        </button>

        <!-- Success Toast -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showToast" class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-xl flex items-center space-x-3 z-50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Added to cart!</span>
                <a href="/cart" class="ml-4 underline hover:no-underline">View Cart</a>
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useCartStore } from '@/Stores/cartStore';
import { useCurrency } from '@/Composables/useCurrency';

interface Product {
    id: number;
    name: string;
    price: number;
    sale_price?: number;
    image_url?: string;
    slug?: string;
    sku?: string;
    type?: string;
}

interface Service {
    id: number;
    name: string;
    price: number;
    billing_cycle?: string;
}

const props = withDefaults(defineProps<{
    product: Product;
    service?: Service | null;
    initialQuantity?: number;
    showQuantity?: boolean;
    requireService?: boolean;
}>(), {
    service: null,
    initialQuantity: 1,
    showQuantity: true,
    requireService: false,
});

const cart = useCartStore();
const { formatCurrency } = useCurrency();
const quantity = ref(props.initialQuantity);
const loading = ref(false);
const added = ref(false);
const showToast = ref(false);

const isServiceProduct = computed(() => {
    return ['service', 'license', 'software'].includes(props.product.type || '');
});

const isDisabled = computed(() => {
    // If it's a service product and requires service selection but none selected
    if (props.requireService && isServiceProduct.value && !props.service) {
        return true;
    }
    return false;
});

const resolvePrice = (): number => {
    if (isServiceProduct.value && props.service) {
        // Service/License with selected package
        return props.service.price;
    }
    // Physical product or Service without packages
    return props.product.sale_price || props.product.price;
};

const buttonText = computed(() => {
    if (loading.value) return 'Adding...';
    if (added.value) return 'Added!';
    if (isDisabled.value) return 'Select a Plan';
    
    const price = resolvePrice();
    return `Add to Cart - ${formatCurrency(price)}`;
});

const incrementQty = () => {
    quantity.value++;
};

const decrementQty = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const addToCart = async () => {
    if (isDisabled.value) return;

    loading.value = true;

    try {
        cart.addItem({
            product_id: props.product.id,
            name: props.product.name,
            price: resolvePrice(),
            quantity: quantity.value,
            image_url: props.product.image_url,
            slug: props.product.slug,
            sku: props.product.sku,
            service_id: props.service?.id || null,
            service_name: props.service?.name,
            billing_cycle: props.service?.billing_cycle,
            product_type: props.product.type,
        });

        added.value = true;
        showToast.value = true;

        // Reset after animation
        setTimeout(() => {
            added.value = false;
        }, 2000);

        setTimeout(() => {
            showToast.value = false;
        }, 3000);
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.quantity-selector input::-webkit-outer-spin-button,
.quantity-selector input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.quantity-selector input[type=number] {
    -moz-appearance: textfield;
}
</style>
