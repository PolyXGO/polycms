<template>
    <div class="service-plan-selector">
        <!-- Service/License Product with Plans -->
        <div v-if="isServiceProduct && hasServices" class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Select a Plan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="service in product.services"
                    :key="service.id"
                    @click="selectPlan(service)"
                    class="relative cursor-pointer rounded-xl border-2 p-6 transition-all duration-200 hover:shadow-lg"
                    :class="[
                        selectedPlan?.id === service.id
                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 ring-2 ring-indigo-600'
                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-indigo-300 dark:hover:border-indigo-700'
                    ]"
                >
                    <!-- Selected Indicator -->
                    <div
                        v-if="selectedPlan?.id === service.id"
                        class="absolute top-4 right-4 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <!-- Popular Badge -->
                    <div
                        v-if="service.is_popular"
                        class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-xs font-bold px-4 py-1 rounded-full"
                    >
                        Most Popular
                    </div>

                    <!-- Plan Name -->
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ service.name }}
                    </h4>

                    <!-- Price -->
                    <div class="mb-4">
                        <span class="text-3xl font-extrabold text-gray-900 dark:text-white">
                            {{ formatCurrency(service.price) }}
                        </span>
                        <span v-if="service.billing_cycle" class="text-gray-500 dark:text-gray-400">
                            /{{ formatBillingCycle(service.billing_cycle) }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p v-if="service.description" class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ service.description }}
                    </p>

                    <!-- Features List -->
                    <ul v-if="service.features && service.features.length" class="space-y-2">
                        <li
                            v-for="(feature, index) in service.features"
                            :key="index"
                            class="flex items-start text-sm text-gray-600 dark:text-gray-400"
                        >
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ feature }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Physical Product: Direct Price Display -->
        <div v-else class="product-price-display">
            <div class="flex items-baseline space-x-3">
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white">
                    {{ formatCurrency(displayPrice) }}
                </span>
                <span
                    v-if="hasDiscount"
                    class="text-xl text-gray-400 line-through"
                >
                    {{ formatCurrency(product.price) }}
                </span>
                <span
                    v-if="hasDiscount"
                    class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-sm font-medium px-2.5 py-0.5 rounded"
                >
                    {{ discountPercent }}% OFF
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useCurrency } from '@/Composables/useCurrency';

interface Service {
    id: number;
    name: string;
    price: number;
    description?: string;
    billing_cycle?: string;
    features?: string[];
    is_popular?: boolean;
}

interface Product {
    id: number;
    name: string;
    price: number;
    sale_price?: number;
    type?: string;
    services?: Service[];
}

const props = defineProps<{
    product: Product;
    modelValue?: Service | null;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: Service | null): void;
    (e: 'plan-selected', value: Service): void;
}>();

const { formatCurrency } = useCurrency();
const selectedPlan = ref<Service | null>(props.modelValue || null);

const isServiceProduct = computed(() => {
    return ['service', 'license', 'software'].includes(props.product.type || '');
});

const hasServices = computed(() => {
    return props.product.services && props.product.services.length > 0;
});

const displayPrice = computed(() => {
    return props.product.sale_price || props.product.price;
});

const hasDiscount = computed(() => {
    return props.product.sale_price && props.product.sale_price < props.product.price;
});

const discountPercent = computed(() => {
    if (!hasDiscount.value) return 0;
    return Math.round((1 - (props.product.sale_price! / props.product.price)) * 100);
});

const formatBillingCycle = (cycle: string): string => {
    const cycles: Record<string, string> = {
        'monthly': 'month',
        'yearly': 'year',
        'lifetime': 'lifetime',
        'one-time': 'one-time',
        'weekly': 'week',
        'quarterly': 'quarter',
    };
    return cycles[cycle] || cycle;
};

const selectPlan = (service: Service) => {
    selectedPlan.value = service;
    emit('update:modelValue', service);
    emit('plan-selected', service);
};
</script>

<style scoped>
.service-plan-selector {
    @apply w-full;
}
</style>
