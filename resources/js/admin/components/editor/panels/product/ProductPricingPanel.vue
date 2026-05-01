<template>
    <div v-if="form" class="space-y-4">
        <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Product Type *') }}</label>
            <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" required>
                <option value="product">{{ $t('Physical Product') }}</option>
                <option value="variable">{{ $t('Variable Product') }}</option>
                <option value="service">{{ $t('Service / License') }}</option>
                <option value="digital">{{ $t('Digital Product') }}</option>
            </select>
        </div>

        <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Price *') }}</label>
            <input v-model.number="form.price" type="number" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500" required />
        </div>

        <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Sale Price') }}</label>
            <input v-model.number="form.sale_price" type="number" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Stock Quantity') }}</label>
                <input v-model.number="form.stock_quantity" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Stock Status') }}</label>
                <select v-model="form.stock_status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="in_stock">{{ $t('In Stock') }}</option>
                    <option value="out_of_stock">{{ $t('Out of Stock') }}</option>
                    <option value="on_backorder">{{ $t('On Backorder') }}</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 items-center mt-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                <input v-model="form.manage_stock" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 transition-all duration-200" />
                <span>{{ $t('Enable stock management') }}</span>
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                <input v-model="form.featured" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 transition-all duration-200" />
                <span>{{ $t('Mark as featured product') }}</span>
            </label>
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">{{ $t('Refund Policy') }}</p>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                <input
                    v-model="form.allow_refund"
                    type="checkbox"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 transition-all duration-200"
                />
                <span>{{ $t('Allow customer refund requests') }}</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $t('Refund window (days)') }}
                    </label>
                    <input
                        v-model.number="form.refund_window_days"
                        type="number"
                        min="0"
                        max="3650"
                        :disabled="!form.allow_refund"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500"
                        :placeholder="$t('Leave empty to use global policy')"
                    />
                </div>
            </div>

            <div class="space-y-1 mt-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ $t('Refund policy note') }}
                </label>
                <textarea
                    v-model="form.refund_policy_note"
                    rows="3"
                    :disabled="!form.allow_refund"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500"
                    :placeholder="$t('Shown to customers in account orders/subscriptions')"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $t('If refund is disabled, customers will see that this product does not support refunds.') }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { inject, getCurrentInstance } from 'vue';
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t;
import { EditorContextKey } from '../../../../editor/context';

const context = inject(EditorContextKey);
if (!context) {
    throw new Error('ProductPricingPanel must be used within editor context');
}

const form = context.form;

if (!form.value) {
    form.value = {
        name: '',
        slug: '',
        sku: '',
        type: context.type ?? 'product',
        status: 'draft',
        price: 0,
        sale_price: null,
        stock_quantity: 0,
        stock_status: 'in_stock',
        manage_stock: false,
        featured: false,
        allow_refund: true,
        refund_window_days: null,
        refund_policy_note: '',
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
    } as any;
}
</script>
