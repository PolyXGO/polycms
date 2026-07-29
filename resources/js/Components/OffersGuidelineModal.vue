<template>
    <Teleport to="body">
        <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm transition-opacity" @click="close"></div>

            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-zinc-800">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-zinc-800/80 bg-slate-50/50 dark:bg-zinc-800/40">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-semibold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modal-title">
                                    Exclusive Offers & Deal Guideline
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-zinc-400">
                                    Product offer details & savings breakdown
                                </p>
                            </div>
                        </div>
                        <button @click="close" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 transition-colors p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-5 max-h-[75vh] overflow-y-auto">
                        <!-- Loading State -->
                        <div v-if="loading" class="py-12 text-center text-slate-500 dark:text-zinc-400">
                            <svg class="animate-spin h-7 w-7 mx-auto text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-xs font-medium">Loading offer guideline details...</p>
                        </div>

                        <!-- Content State -->
                        <div v-else-if="product" class="space-y-6">
                            <!-- Product Name & Price Info -->
                            <div class="flex items-center gap-4 p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/50 border border-slate-200/60 dark:border-zinc-800">
                                <img v-if="product.media?.[0]?.url" :src="product.media[0].url" :alt="product.name" class="w-14 h-14 object-cover rounded-lg flex-shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ product.name }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">${{ Number(product.effective_price || product.price).toFixed(2) }}</span>
                                        <span v-if="product.price > product.effective_price" class="text-xs text-slate-400 line-through">${{ Number(product.price).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 1: Price Escalation Tiers -->
                            <div v-if="product.tier_metrics && product.tier_metrics.all_tiers?.length > 0" class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 flex items-center gap-1.5">
                                        <span>🚀 Price Escalation Tier</span>
                                    </h5>
                                    <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">
                                        {{ product.tier_metrics.sales_count || 0 }} sold
                                    </span>
                                </div>
                                <div v-if="product.tier_metrics.next_tier" class="w-full bg-slate-100 dark:bg-zinc-800 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" :style="{ width: (product.tier_metrics.progress_percentage || 0) + '%' }"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div v-for="t in product.tier_metrics.all_tiers" :key="t.id" 
                                         :class="[
                                            'p-3 rounded-xl text-center border transition-all',
                                            product.tier_metrics.current_tier?.id === t.id
                                                ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-900 dark:text-emerald-300 font-bold'
                                                : 'bg-slate-50 dark:bg-zinc-800/40 border-slate-200/60 dark:border-zinc-800 text-slate-600 dark:text-zinc-400'
                                         ]">
                                        <div class="text-[11px] font-medium text-slate-500 dark:text-zinc-400">{{ t.label || (t.min_sales + '-' + (t.max_sales || '∞')) }}</div>
                                        <div class="text-sm font-extrabold mt-1 text-slate-900 dark:text-white">${{ Number(t.calculated_price || t.price).toFixed(2) }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Volume Discounts (Buy More, Save More) -->
                            <div v-if="product.volume_discounts && product.volume_discounts.length > 0" class="space-y-3">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 flex items-center gap-1.5">
                                    <span>📦 Volume Discount Rules</span>
                                </h5>
                                <div class="divide-y divide-slate-100 dark:divide-zinc-800 rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/30 overflow-hidden text-xs">
                                    <div v-for="(v, idx) in product.volume_discounts" :key="idx" class="flex items-center justify-between p-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-[10px] flex items-center justify-center">{{ idx + 1 }}</span>
                                            <span class="font-semibold text-slate-800 dark:text-zinc-200">Buy {{ v.min_quantity }}{{ v.max_quantity ? '-' + v.max_quantity : '+' }} items</span>
                                        </div>
                                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                                            {{ v.discount_type === 'percentage' ? '-' + v.discount_value + '%' : '-$' + Number(v.discount_value).toFixed(2) }} OFF
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Package / Service Plans -->
                            <div v-if="product.services && product.services.length > 0" class="space-y-3">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 flex items-center gap-1.5">
                                    <span>💎 Available License & Service Plans</span>
                                </h5>
                                <div class="space-y-2">
                                    <div v-for="s in product.services" :key="s.id" class="p-3 rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-xs">
                                        <div class="flex items-center justify-between font-bold text-slate-900 dark:text-white">
                                            <span>{{ s.name }}</span>
                                            <span class="text-emerald-600 dark:text-emerald-400">${{ Number(s.price).toFixed(2) }}</span>
                                        </div>
                                        <div class="mt-1 text-slate-500 dark:text-zinc-400 text-[11px]">
                                            {{ s.access_type === 'subscription' ? s.duration_value + ' ' + s.duration_unit + (s.duration_value > 1 ? 's' : '') : 'Lifetime access' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-3.5 bg-slate-50 dark:bg-zinc-800/40 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
                        <button @click="close" type="button" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-900 transition-colors">
                            Got It
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const loading = ref(false);
const product = ref<any>(null);

const open = async (productId: number) => {
    isOpen.value = true;
    loading.value = true;
    product.value = null;
    try {
        const res = await axios.get(`/api/v1/products/${productId}`);
        if (res.data && res.data.data) {
            product.value = res.data.data;
        }
    } catch (e) {
        console.error('Failed to load offer guideline details', e);
    } finally {
        loading.value = false;
    }
};

const close = () => {
    isOpen.value = false;
};

defineExpose({ open, close });
</script>
