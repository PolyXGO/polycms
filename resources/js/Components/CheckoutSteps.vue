<template>
    <div class="w-full max-w-xl mx-auto py-2 sm:py-3 select-none">
        <nav aria-label="Progress">
            <ol role="list" class="flex items-start justify-between w-full relative">
                <!-- Step 1: Shopping Cart -->
                <li class="relative z-10 flex-1 flex flex-col items-center">
                    <a href="/cart" class="flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200 cursor-pointer"
                       :class="[
                           currentStepNumber >= 1
                               ? 'bg-slate-900 text-white dark:bg-slate-200 dark:text-slate-900 ring-4 ring-gray-50 dark:ring-gray-900 shadow-sm'
                               : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700 ring-4 ring-gray-50 dark:ring-gray-900'
                       ]"
                       title="Shopping Cart">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </a>
                    <span class="mt-2 text-xs text-center transition-colors select-none"
                          :class="[
                              currentStepNumber >= 1
                                  ? 'font-bold text-slate-900 dark:text-slate-200'
                                  : 'font-medium text-slate-400 dark:text-slate-500'
                          ]">
                        Shopping Cart
                    </span>
                </li>

                <!-- Connector 1-2: Centered exactly at y=18px (icon center) -->
                <div class="absolute top-[18px] left-[16.66%] right-[50%] -translate-y-1/2 h-0.5 z-0">
                    <div class="w-full h-full transition-colors duration-200"
                         :class="[currentStepNumber > 1 ? 'bg-slate-900 dark:bg-slate-300' : 'bg-slate-200 dark:bg-slate-800']">
                    </div>
                </div>

                <!-- Step 2: Checkout -->
                <li class="relative z-10 flex-1 flex flex-col items-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200"
                         :class="[
                             currentStepNumber >= 2
                                 ? 'bg-slate-900 text-white dark:bg-slate-200 dark:text-slate-900 ring-4 ring-gray-50 dark:ring-gray-900 shadow-sm'
                                 : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700 ring-4 ring-gray-50 dark:ring-gray-900'
                         ]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="mt-2 text-xs text-center transition-colors select-none"
                          :class="[
                              currentStepNumber >= 2
                                  ? 'font-bold text-slate-900 dark:text-slate-200'
                                  : 'font-medium text-slate-400 dark:text-slate-500'
                          ]">
                        Checkout
                    </span>
                </li>

                <!-- Connector 2-3: Centered exactly at y=18px (icon center) -->
                <div class="absolute top-[18px] left-[50%] right-[16.66%] -translate-y-1/2 h-0.5 z-0">
                    <div class="w-full h-full transition-colors duration-200"
                         :class="[currentStepNumber > 2 ? 'bg-slate-900 dark:bg-slate-300' : 'bg-slate-200 dark:bg-slate-800']">
                    </div>
                </div>

                <!-- Step 3: Complete -->
                <li class="relative z-10 flex-1 flex flex-col items-center">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200"
                         :class="[
                             currentStepNumber >= 3
                                 ? 'bg-slate-900 text-white dark:bg-slate-200 dark:text-slate-900 ring-4 ring-gray-50 dark:ring-gray-900 shadow-sm'
                                 : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700 ring-4 ring-gray-50 dark:ring-gray-900'
                         ]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="mt-2 text-xs text-center transition-colors select-none"
                          :class="[
                              currentStepNumber >= 3
                                  ? 'font-bold text-slate-900 dark:text-slate-200'
                                  : 'font-medium text-slate-400 dark:text-slate-500'
                          ]">
                        Complete
                    </span>
                </li>
            </ol>
        </nav>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    step: {
        type: [Number, String],
        default: 1
    },
    currentStep: {
        type: String,
        default: ''
    }
});

const currentStepNumber = computed(() => {
    if (props.currentStep) {
        if (props.currentStep === 'cart') return 1;
        if (props.currentStep === 'checkout') return 2;
        if (props.currentStep === 'complete' || props.currentStep === 'success') return 3;
    }
    return Number(props.step) || 1;
});
</script>
