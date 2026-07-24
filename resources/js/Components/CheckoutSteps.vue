<template>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav aria-label="Progress">
                <ol role="list" class="flex items-center w-full">
                    <!-- Step 1: Shopping Cart -->
                    <li class="relative flex flex-col items-center">
                        <a href="/cart" class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-gray-800 transition-colors duration-200" 
                           :class="[step >= 1 ? 'bg-gray-900' : 'bg-gray-200 dark:bg-gray-700']">
                            <svg class="w-4 h-4" :class="[step >= 1 ? 'text-white' : 'text-gray-400 dark:text-gray-500']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                        <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs font-semibold whitespace-nowrap" 
                              :class="[step >= 1 ? 'text-gray-900 dark:text-gray-300' : 'text-gray-500 dark:text-gray-400']">
                            Shopping Cart
                        </span>
                    </li>

                    <!-- Connector 1-2 -->
                    <li class="flex-1 h-0.5 mx-4" :class="[step > 1 ? 'bg-gray-900' : 'bg-gray-200 dark:bg-gray-700']"></li>

                    <!-- Step 2: Checkout -->
                    <li class="relative flex flex-col items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full transition-colors duration-200" 
                             :class="[step >= 2 ? 'bg-gray-900' : 'bg-gray-200 dark:bg-gray-700']">
                            <svg class="w-4 h-4" :class="[step >= 2 ? 'text-white' : 'text-gray-400 dark:text-gray-500']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs font-semibold whitespace-nowrap" 
                              :class="[step >= 2 ? 'text-gray-900 dark:text-gray-300' : 'text-gray-500 dark:text-gray-400']">
                            Checkout
                        </span>
                    </li>

                    <!-- Connector 2-3 -->
                    <li class="flex-1 h-0.5 mx-4" :class="[step > 2 ? 'bg-gray-900' : 'bg-gray-200 dark:bg-gray-700']"></li>

                    <!-- Step 3: Complete -->
                    <li class="relative flex flex-col items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full transition-colors duration-200" 
                             :class="[step >= 3 ? 'bg-gray-900' : 'bg-gray-200 dark:bg-gray-700']">
                            <svg class="w-4 h-4" :class="[step >= 3 ? 'text-white' : 'text-gray-400 dark:text-gray-500']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs font-semibold whitespace-nowrap" 
                              :class="[step >= 3 ? 'text-gray-900 dark:text-gray-300' : 'text-gray-500 dark:text-gray-400']">
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
