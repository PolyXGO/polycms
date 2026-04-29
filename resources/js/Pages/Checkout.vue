<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useCartStore } from '../Stores/cartStore';
import { useCurrency } from '@/Composables/useCurrency';
import CheckoutSteps from '@/Components/CheckoutSteps.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';
import DialogProvider from '@/admin/components/dialogs/DialogProvider.vue';
import { useDialog } from '@/admin/composables/useDialog';

const dialog = useDialog();

const props = defineProps({
    user: Object,
    gateways: Array as () => any[],
});

const cart = useCartStore();
const { formatCurrency } = useCurrency();
const couponInput = ref('');
const processing = ref(false);
const availableCoupons = ref<any[]>([]);
const showCouponModal = ref(false);
const selectedCoupon = ref<any>(null);
const errors = ref<any>({});

const form = ref({
    billing_address: {
        full_name: '',
        address_line: '',
        city: '',
        postal_code: '',
        country: 'US', // Default
    },
    payment_gateway: '',
    customer_email: '',
});

onMounted(async () => {
    // Pre-fill address and email if user exists
    if (props.user) {
        form.value.billing_address.full_name = props.user.name;
        form.value.customer_email = props.user.email;
        // Logic to extract address from props.user.addresses if available
         if (props.user.addresses && props.user.addresses.length > 0) {
             const addr = props.user.addresses.find((a: any) => a.is_default) || props.user.addresses[0];
             if(addr) {
                 form.value.billing_address.address_line = addr.address_line || addr.line1;
                 form.value.billing_address.city = addr.city;
                 form.value.billing_address.postal_code = addr.postal_code;
             }
        }
    }
    
    // Select first gateway by default
    if (props.gateways && props.gateways.length > 0) {
        form.value.payment_gateway = props.gateways[0].code;
    }

    // Calculate totals on mount
    await cart.calculateTotals();
    
    // Fetch available coupons
    fetchAvailableCoupons();
});

const fetchAvailableCoupons = async () => {
    try {
        const response = await axios.get('/api/v1/checkout/coupons');
        availableCoupons.value = response.data.data;
    } catch (error) {
        console.error('Error fetching coupons', error);
    }
};

const applyCoupon = async (code: string | null = null) => {
    const codeToApply = code || couponInput.value;
    if (!codeToApply) return;
    
    await cart.applyCoupon(codeToApply);
    
    // If successful, clear input if it was manual entry
    if (!code) couponInput.value = '';
};

const removeCoupon = async (code: string) => {
    await cart.removeCoupon(code);
};

const openCouponModal = (coupon: any) => {
    selectedCoupon.value = coupon;
    showCouponModal.value = true;
};

const closeCouponModal = () => {
    showCouponModal.value = false;
    selectedCoupon.value = null;
};

const isCouponApplied = (code: string) => {
    return cart.couponCodes.includes(code);
};

const addTestItem = () => {
    cart.addItem({
        product_id: 1, // Ensure product ID 1 exists
        name: 'Test Product',
        price: 19.99,
        quantity: 1,
    });
};

const placeOrder = async () => {
    if (cart.isEmpty) return;
    errors.value = {}; // Clear previous errors

    if (!form.value.payment_gateway) {
        errors.value.payment_gateway = 'Please select a payment method';
        return;
    }
    
    if (!props.user && !form.value.customer_email) {
        errors.value.customer_email = 'Please provide an email address';
        return;
    }

    processing.value = true;
    try {
        const response = await axios.post('/api/v1/checkout/process', {
            items: cart.items,
            coupon_codes: cart.couponCodes, // Send array
            billing_address: form.value.billing_address,
            payment_gateway: form.value.payment_gateway,
            payment_data: {}, // Additional data if needed,
            customer_email: form.value.customer_email,
        });
        
        // Handle success
        cart.clear();

        // Check for SePay/QR Payment
        if (response.data.payment_result && response.data.payment_result.qr_url) {
            // Show QR Code Modal
            qrData.value = response.data.payment_result;
            showQrModal.value = true;
            createdOrder.value = response.data.order;
        } else {
            // Normal redirect
            window.location.href = '/checkout/success/' + response.data.order.code; 
        }

    } catch (error: any) {
        console.error(error);
        if (error.response && error.response.status === 422) {
             // Validation errors
             const serverErrors = error.response.data.errors;
             errors.value = Object.keys(serverErrors).reduce((acc: any, key) => {
                 acc[key] = serverErrors[key][0];
                 return acc;
             }, {});
        } else {
             dialog.error(error.response?.data?.message || 'Checkout failed');
        }
    } finally {
        processing.value = false;
    }
};

// QR Modal Logic
const showQrModal = ref(false);
const qrData = ref<any>(null);
const createdOrder = ref<any>(null);

const finishPayment = () => {
    if (createdOrder.value) {
        window.location.href = '/checkout/success/' + createdOrder.value.code;
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-12 relative">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <CheckoutSteps :step="2" />
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 mt-8">Checkout</h1>
            
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start">
                <!-- Left Column: Billing & Payment -->
                <section class="lg:col-span-7">
                    <!-- Billing Address -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Billing Details</h2>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Email -->
                            <div class="sm:col-span-6">
                                <InputLabel for="customer_email" value="Email Address" class="required" />
                                <TextInput 
                                    id="customer_email" 
                                    v-model="form.customer_email" 
                                    type="email" 
                                    class="mt-1 block w-full" 
                                    required 
                                    :disabled="!!user || processing"
                                    :class="{'bg-gray-100 cursor-not-allowed text-gray-500': !!user || processing}"
                                />
                                <InputError :message="errors.customer_email || errors['customer_email']" class="mt-2" />
                            </div>

                            <div class="sm:col-span-6">
                                <InputLabel for="full_name" value="Full Name" />
                                <TextInput id="full_name" v-model="form.billing_address.full_name" type="text" class="mt-1 block w-full" :disabled="processing" />
                                <InputError :message="errors['billing_address.full_name']" class="mt-2" />
                            </div>
                            
                            <div class="sm:col-span-6">
                                <InputLabel for="address_line" value="Address Line 1" />
                                <TextInput id="address_line" v-model="form.billing_address.address_line" type="text" class="mt-1 block w-full" :disabled="processing" />
                                <InputError :message="errors['billing_address.address_line']" class="mt-2" />
                            </div>

                             <div class="sm:col-span-3">
                                <InputLabel for="city" value="City" />
                                <TextInput id="city" v-model="form.billing_address.city" type="text" class="mt-1 block w-full" :disabled="processing" />
                                <InputError :message="errors['billing_address.city']" class="mt-2" />
                            </div>

                             <div class="sm:col-span-3">
                                <InputLabel for="postal_code" value="Postal Code" />
                                <TextInput id="postal_code" v-model="form.billing_address.postal_code" type="text" class="mt-1 block w-full" :disabled="processing" />
                                <InputError :message="errors['billing_address.postal_code']" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                         <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Payment Method</h2>
                         <div class="space-y-4">
                            <div v-for="gateway in props.gateways" :key="gateway.code" class="flex items-center">
                                <input :disabled="processing" v-model="form.payment_gateway" :value="gateway.code" :id="gateway.code" name="payment_method" type="radio" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-offset-gray-800" />
                                <label :for="gateway.code" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ gateway.name }}
                                </label>
                            </div>
                            <div v-if="props.gateways && props.gateways.length === 0" class="text-gray-500">
                                No payment gateways available.
                            </div>
                         </div>
                    </div>
                </section>

                <!-- Right Column: Order Summary -->
                <section class="lg:col-span-5">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Order Summary</h2>
                        
                        <!-- Cart Items -->
                        <div class="flow-root">
                            <ul role="list" class="-my-6 divide-y divide-gray-200 dark:divide-gray-700">
                                <li v-for="(item, index) in cart.items" :key="index" class="py-6 flex">
                                    <div class="flex-1 ml-4">
                                        <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                            <h3>
                                                <a :href="item.permalink || (item.slug ? `/products/${item.slug}` : '#')" target="_blank" :class="{'hover:text-indigo-600 dark:hover:text-indigo-400': item.slug || item.permalink}">
                                                    {{ item.name }}
                                                </a>
                                            </h3>
                                            <p class="ml-4">{{ formatCurrency(item.price) }}</p>
                                        </div>
                                         <div class="flex items-end justify-between text-sm">
                                            <p class="text-gray-500 dark:text-gray-400">Qty {{ item.quantity }}</p>
                                            <button type="button" @click="cart.removeItem(index)" class="font-medium text-indigo-600 hover:text-indigo-500">Remove</button>
                                        </div>
                                    </div>
                                </li>
                                 <li v-if="cart.isEmpty" class="py-6 text-center text-gray-500">
                                    Your cart is empty.
                                </li>
                            </ul>
                        </div>

                         <!-- Debug: Add Test Item -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button @click="addTestItem" type="button" class="text-sm text-indigo-600 mb-4">
                                + Add Test Item (Debug)
                            </button>
                        </div>

                        <!-- Coupon Section -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Discount Code</label>
                            
                            <!-- Input -->
                            <div class="mt-1 flex space-x-2">
                                <input :disabled="processing" v-model="couponInput" type="text" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Enter coupon code" />
                                <button :disabled="processing" @click="applyCoupon(null)" type="button" class="bg-gray-200 dark:bg-gray-600 px-4 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-white hover:bg-gray-300 disabled:opacity-50">Apply</button>
                            </div>
                            <p v-if="cart.couponError" class="mt-2 text-sm text-red-600">{{ cart.couponError }}</p>
                            
                            <!-- Applied Coupons Display -->
                            <div v-if="cart.totals.applied_coupons && cart.totals.applied_coupons.length > 0" class="mt-4 space-y-2">
                                <div v-for="coupon in cart.totals.applied_coupons" :key="coupon.code" class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 px-3 py-2 rounded-md border border-green-200 dark:border-green-800">
                                    <div class="flex items-center text-sm text-green-700 dark:text-green-400">
                                        <i class="fas fa-tag mr-2"></i>
                                        <div class="flex flex-col">
                                            <span class="font-medium uppercase">{{ coupon.code }}</span>
                                            <span v-if="coupon.title" class="text-xs text-green-600 dark:text-green-500">{{ coupon.title }}</span>
                                        </div>
                                        <span class="ml-2 font-semibold">(-{{ formatCurrency(Number(coupon.discount || 0)) }})</span>
                                    </div>
                                    <button @click="removeCoupon(coupon.code)" type="button" class="text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 ml-4 focus:outline-none">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Available Coupons List -->
                            <div v-if="availableCoupons.length > 0" class="mt-6">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Available Discount Codes:</h3>
                                <div class="space-y-3">
                                    <div v-for="coupon in availableCoupons" :key="coupon.id" class="border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden bg-white dark:bg-gray-800">
                                        <div class="bg-green-500 dark:bg-green-700 px-4 py-2 flex justify-between items-center text-white">
                                            <span class="font-bold font-mono">{{ coupon.code }}</span>
                                            <span class="font-bold">{{ coupon.type === 'fixed_amount' ? formatCurrency(coupon.value) : coupon.value + '%' }} OFF</span>
                                        </div>
                                        <div class="p-3">
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ coupon.title || coupon.description }}</p>
                                            <div v-if="coupon.min_order_value > 0" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                                <i class="fas fa-shopping-cart mr-1"></i> Min: {{ formatCurrency(coupon.min_order_value) }}
                                            </div>
                                            <div class="flex items-center justify-between mt-3">
                                                <button 
                                                    @click="applyCoupon(coupon.code)" 
                                                    type="button" 
                                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-1.5 rounded mr-2 disabled:opacity-50"
                                                    :disabled="isCouponApplied(coupon.code)"
                                                >
                                                    <i class="fas fa-check mr-1"></i> {{ isCouponApplied(coupon.code) ? 'Applied' : 'Apply' }}
                                                </button>
                                                <button @click="openCouponModal(coupon)" type="button" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <i class="fas fa-info-circle mr-1"></i> Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Totals -->
                        <div class="border-t border-gray-200 dark:border-gray-700 mt-6 pt-6 space-y-4">
                            <div class="flex items-center justify-between text-base font-medium text-gray-900 dark:text-white">
                                <p>Subtotal</p>
                                <p>{{ formatCurrency(cart.totals.subtotal || 0) }}</p>
                            </div>
                             <div  v-if="Number(cart.totals.discount || 0) > 0" class="flex items-center justify-between text-base font-medium text-green-600">
                                <p>Discount</p>
                                <p>-{{ formatCurrency(cart.totals.discount || 0) }}</p>
                            </div>
                             <div  v-if="Number(cart.totals.tax || 0) > 0" class="flex items-center justify-between text-base font-medium text-gray-900 dark:text-white">
                                <p>Tax</p>
                                <p>{{ formatCurrency(cart.totals.tax || 0) }}</p>
                            </div>
                             <div class="flex items-center justify-between text-lg font-bold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-4">
                                <p>Total</p>
                                <p>{{ formatCurrency(cart.totals.total || 0) }}</p>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="mt-6">
                            <button @click="placeOrder" :disabled="processing || cart.isEmpty" type="button" class="w-full bg-indigo-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                {{ processing ? 'Processing...' : 'Place Order' }}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Details Modal -->
        <div v-if="showCouponModal && selectedCoupon" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeCouponModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    {{ selectedCoupon.title }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        {{ selectedCoupon.description }}
                                    </p>
                                    <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md text-sm">
                                        <p><strong>Code:</strong> {{ selectedCoupon.code }}</p>
                                        <p><strong>Discount:</strong> {{ selectedCoupon.type === 'fixed_amount' ? formatCurrency(selectedCoupon.value) : Number(selectedCoupon.value) + '%' }} OFF</p>
                                        <p v-if="selectedCoupon.min_order_value"><strong>Min Order:</strong> {{ formatCurrency(selectedCoupon.min_order_value) }}</p>
                                        <p v-if="selectedCoupon.is_exclusive" class="text-red-500 font-bold mt-1">Exclusive Discount (Cannot combine)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="applyCoupon(selectedCoupon.code); closeCouponModal()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Apply Coupon
                        </button>
                        <button @click="closeCouponModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- QR Payment Modal -->
        <div v-if="showQrModal && qrData" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="finishPayment"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                            Scan to Pay
                        </h3>
                        <div class="flex justify-center mb-6">
                            <img :src="qrData.qr_url" alt="SePay QR" class="max-w-[250px] border-2 border-gray-200 rounded-lg">
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2 mb-6">
                            <p>Amount: <span class="font-bold text-lg text-indigo-600">{{ formatCurrency(qrData.amount) }}</span></p>
                            <p>Content: <span class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded select-all">{{ qrData.description }}</span></p>
                            <p class="text-xs text-gray-500">Please scan the QR code with your banking app to complete the payment.</p>
                        </div>
                        
                        <button @click="finishPayment" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:text-sm">
                            I Have Paid
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <DialogProvider />
</template>
