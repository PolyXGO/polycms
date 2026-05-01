<template>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav aria-label="Progress">
                <ol role="list" class="flex items-center w-full">
                    <!-- Step 1: Shopping Cart -->
                    <li class="relative flex flex-col items-center">
                        <a href="/cart" class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-indigo-900 transition-colors duration-200" 
                           :class="[step >= 1 ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700']">
                            <i class="fas fa-shopping-cart text-white text-xs"></i>
                        </a>
                        <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs font-semibold whitespace-nowrap" 
                              :class="[step >= 1 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400']">
                            Shopping Cart
                        </span>
                    </li>

                    <!-- Connector 1-2 -->
                    <li class="flex-1 h-0.5 mx-4" :class="[step > 1 ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700']"></li>

                    <!-- Step 2: Checkout -->
                    <li class="relative flex flex-col items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full transition-colors duration-200" 
                             :class="[step >= 2 ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700']">
                            <i class="fas fa-credit-card text-white text-xs"></i>
                        </div>
                        <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs font-semibold whitespace-nowrap" 
                              :class="[step >= 2 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400']">
                            Checkout
                        </span>
                    </li>

                    <!-- Connector 2-3 -->
                    <li class="flex-1 h-0.5 mx-4" :class="[step > 2 ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700']"></li>

                    <!-- Step 3: Complete -->
                    <li class="relative flex flex-col items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full transition-colors duration-200" 
                             :class="[step >= 3 ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700']">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                        <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs font-semibold whitespace-nowrap" 
                              :class="[step >= 3 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400']">
                            Complete
                        </span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    step: {
        type: Number,
        required: true,
        default: 1
    }
});
</script>
