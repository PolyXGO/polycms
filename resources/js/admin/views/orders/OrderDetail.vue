<template>
    <div v-if="order">
        <div class="flex justify-between items-center mb-6">
            <div>
                <button @click="router.back()" class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ $t('Back to list') }}
                </button>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('Order') }} #{{ order.code }}</h1>
                <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
            </div>
            
            <div class="flex space-x-3">
                 <!-- Status Actions -->
                 <select 
                    v-model="order.status" 
                    @change="updateStatus" 
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                     <option value="pending">{{ $t('Pending') }}</option>
                     <option value="processing">{{ $t('Processing') }}</option>
                     <option value="completed">{{ $t('Completed') }}</option>
                     <option value="cancelled">{{ $t('Cancelled') }}</option>
                 </select>
                 
                 <button
                    @click="downloadInvoice"
                    :disabled="invoiceLoading"
                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm transition-colors inline-flex items-center gap-2"
                >
                    <svg v-if="invoiceLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    {{ $t('Download Invoice') }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Items -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $t('Product') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $t('Price') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $t('Qty') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $t('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 mr-4" v-if="getProductImage(item)">
                                            <img :src="getProductImage(item)" class="h-10 w-10 rounded-full object-cover" alt="">
                                        </div>
                                        <div>
                                            <a 
                                                v-if="item.product" 
                                                :href="item.product.frontend_url" 
                                                target="_blank"
                                                class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 block"
                                            >
                                                {{ item.name }}
                                            </a>
                                            <div v-else class="text-sm font-medium text-gray-900 dark:text-white">{{ item.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400" v-if="item.metadata?.variant_label">{{ item.metadata.variant_label }}</div>
                                            <div class="text-xs text-gray-400 dark:text-gray-500" v-if="item.product">{{ item.product.slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(item.price) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">{{ item.quantity }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700/30 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Subtotal') }}</td>
                                <td class="px-6 py-3 text-right text-sm text-gray-900 dark:text-white font-medium">{{ formatCurrency(order.subtotal_amount) }}</td>
                            </tr>
                            <!-- Separate Discount Rows -->
                             <template v-if="order.coupons && order.coupons.length">
                                <tr v-for="coupon in order.coupons" :key="coupon.id">
                                    <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-red-600 dark:text-red-400">
                                        {{ $t('Discount') }} ({{ coupon.code }})
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm text-red-600 dark:text-red-400 font-medium">
                                        -{{ formatCurrency(coupon.discount_amount) }}
                                    </td>
                                </tr>
                             </template>
                             <tr v-else-if="parseFloat(order.discount_amount) > 0">
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-red-600 dark:text-red-400">
                                    {{ $t('Discount') }} ({{ order.discount_code }})
                                </td>
                                <td class="px-6 py-3 text-right text-sm text-red-600 dark:text-red-400 font-medium">
                                    -{{ formatCurrency(order.discount_amount) }}
                                </td>
                            </tr>

                            <tr v-if="parseFloat(order.tax_amount) > 0">
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Tax') }}</td>
                                <td class="px-6 py-3 text-right text-sm text-gray-900 dark:text-white font-medium">{{ formatCurrency(order.tax_amount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-base font-bold text-gray-900 dark:text-white">{{ $t('Total') }}</td>
                                <td class="px-6 py-3 text-right text-base font-bold text-indigo-600 dark:text-indigo-400">{{ formatCurrency(order.total_amount) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Transactions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ $t('Transactions') }}</h3>
                    <!-- List transactions here -->
                    <div v-if="order.transactions && order.transactions.length">
                        <div v-for="txn in order.transactions" :key="txn.id" class="flex justify-between py-3 border-b last:border-0 border-gray-100 dark:border-gray-700">
                            <div>
                                <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded px-2 py-1">{{ txn.transaction_ref }}</span>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ txn.gateway }}</span>
                            </div>
                            <div class="flex items-center">
                                <span :class="getTransactionStatusClass(txn.status)" class="text-sm font-medium mr-4">
                                    {{ getTransactionStatusLabel(txn.status) }}
                                </span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(txn.amount) }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                        {{ $t('No transactions found for this order.') }}
                    </div>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Customer Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ $t('Customer') }}</h3>
                    <div class="text-sm">
                        <p class="font-bold text-gray-900 dark:text-white">{{ order.user?.name || order.billing_address?.full_name || $t('Guest') }}</p>
                        <p class="text-gray-500 dark:text-gray-400">{{ order.user?.email || order.guest_email }}</p>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-sm">
                        <p class="font-medium text-gray-500 dark:text-gray-400 mb-2">{{ $t('Billing Address') }}</p>
                        <p v-if="order.billing_address" class="text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ order.billing_address.full_name }}<br>
                            {{ order.billing_address.address_line }}<br>
                            {{ order.billing_address.city }} {{ order.billing_address.postal_code }}, {{ order.billing_address.country }}
                        </p>
                        <p v-else class="text-gray-400 dark:text-gray-500 italic">{{ $t('No billing address') }}</p>
                    </div>
                </div>

                <!-- Invoices -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $t('Invoices') }}</h3>
                        <button v-if="canGenerateInvoice" @click="generateInvoice" type="button" class="text-sm px-3 py-1 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded hover:bg-indigo-100 dark:hover:bg-indigo-900/50">
                            {{ $t('Generate') }}
                        </button>
                    </div>
                    <div v-if="invoices && invoices.length" class="space-y-3">
                        <div v-for="inv in invoices" :key="inv.id" class="flex flex-col gap-2 p-3 border border-gray-100 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-sm text-gray-900 dark:text-white">{{ inv.invoice_number }}</span>
                                <span :class="{'text-green-600': inv.status === 'issued', 'text-red-600 line-through': inv.status === 'void'}" class="text-xs uppercase font-bold">{{ inv.status }}</span>
                            </div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs text-gray-500">{{ formatDate(inv.issued_at || inv.created_at) }}</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(inv.total_amount) }}</span>
                            </div>
                            <div class="flex gap-2 justify-end mt-2">
                                <button type="button" @click="downloadPdfInvoice(inv)" class="text-xs text-gray-600 dark:text-gray-400 hover:text-indigo-600" :disabled="invoiceLoadingId === inv.id">
                                    <span v-if="invoiceLoadingId === inv.id">{{ $t('Downloading...') }}</span>
                                    <span v-else>{{ $t('Download') }}</span>
                                </button>
                                <button v-if="inv.status !== 'void'" type="button" @click="voidInvoice(inv)" class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 ml-2">
                                    {{ $t('Void') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                        {{ $t('No invoices issued.') }}
                    </div>
                </div>

                <!-- Refund Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ $t('Refund') }}</h3>

                    <div class="space-y-3 text-sm" v-if="order">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">{{ $t('Refund status') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ order.refund_status || $t('none') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">{{ $t('Refunded total') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(order.refunded_total || 0) }}</span>
                        </div>
                    </div>

                    <div v-if="!refundableItems.length" class="text-sm text-gray-500 dark:text-gray-400 italic mt-4">
                        {{ $t('No refundable items left.') }}
                    </div>

                    <div v-else class="space-y-4 mt-4">
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">{{ $t('Reason') }}</label>
                            <textarea
                                v-model="refundForm.reason"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                :placeholder="$t('Optional refund reason')"
                            />
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="item in refundableItems"
                                :key="item.id"
                                class="flex items-center justify-between gap-3 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2"
                            >
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ item.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('Max refundable') }}: {{ item.refundable_qty }}</p>
                                </div>
                                <input
                                    v-model.number="refundForm.qty[item.id]"
                                    type="number"
                                    min="0"
                                    :max="item.refundable_qty"
                                    class="w-20 px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input v-model="refundForm.restock" type="checkbox" class="rounded border-gray-300 dark:border-gray-600" />
                                {{ $t('Restock items') }}
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input v-model="refundForm.gateway_refund" type="checkbox" class="rounded border-gray-300 dark:border-gray-600" />
                                {{ $t('Refund via gateway') }}
                            </label>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                                @click="previewRefund"
                            >
                                {{ $t('Preview Refund') }}
                            </button>
                            <button
                                type="button"
                                class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm"
                                @click="issueRefund"
                            >
                                {{ $t('Issue Refund') }}
                            </button>
                        </div>

                        <div v-if="refundPreview" class="border border-indigo-200 dark:border-indigo-800 rounded-lg p-3 bg-indigo-50 dark:bg-indigo-900/20">
                            <p class="text-sm text-indigo-900 dark:text-indigo-200">
                                {{ $t('Preview total refund') }}: <strong>{{ formatCurrency(refundPreview.total_refund || 0) }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, getCurrentInstance, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useCurrency } from '@/Composables/useCurrency';
import { useDialog } from '../../composables/useDialog';

const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || ((v: string) => v);

const route = useRoute();
const router = useRouter();
const dialog = useDialog();
const { formatCurrency } = useCurrency();
const order = ref<any>(null);
const invoices = ref<any[]>([]);
const invoiceLoadingId = ref<number | null>(null);
const invoiceLoading = ref(false);
const refundPreview = ref<any>(null);
const refundForm = ref({
    reason: '',
    restock: true,
    gateway_refund: false,
    qty: {} as Record<number, number>,
});

const refundableItems = computed(() => {
    if (!order.value?.items) return [];
    return order.value.items
        .map((item: any) => {
            const refunded = Number(item.refunded_qty || 0);
            const qty = Number(item.quantity || 0);
            return {
                ...item,
                refundable_qty: Math.max(0, qty - refunded),
            };
        })
        .filter((item: any) => item.refundable_qty > 0);
});

const canGenerateInvoice = computed(() => {
    // If order is cancelled, do not allow generating invoices
    if (order.value?.status === 'cancelled') return false;
    
    // Only allow generating if there's absolutely no valid invoice
    // And to prevent confusion after Voiding, we can restrict it to max 1 active/void cycle 
    // unless explicitly needed. But for now, let's just make it so you can only generate a new one 
    // if there are NO invoices at all, or if the user explicitly wants to re-run it when all are voided.
    // Given the user's feedback ("if already VOID => disabled?"), we'll disable it if ANY invoice exists.
    if (!invoices.value || invoices.value.length === 0) return true;
    
    return false; // Once generated, even if voided, the button stays disabled.
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleString();
};

const getProductImage = (item: any) => {
    // Check if variant image was saved in metadata at checkout
    if (item.metadata?.image_url) return item.metadata.image_url;

    // Fallback to product primary image
    const product = item.product;
    if (!product || !product.media || !product.media.length) return null;
    const img = product.media.find((m: any) => m.pivot?.is_primary) || product.media[0];
    return img?.url;
};

const getTransactionStatusClass = (status: string) => {
    if (status === 'success') return 'text-green-600 dark:text-green-400';
    if (status === 'pending') return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};

const getTransactionStatusLabel = (status: string) => {
    if (status === 'success') return $t('Success');
    if (status === 'pending') return $t('Pending');
    if (status === 'failed') return $t('Failed');
    return status;
};

const loadInvoices = async () => {
    try {
        const { data } = await axios.get(`/api/v1/orders/${route.params.id}/invoices`);
        invoices.value = data.data;
    } catch (e) {
        console.error(e);
    }
};

const loadOrder = async () => {
    try {
        const { data } = await axios.get(`/api/v1/orders/${route.params.id}`);
        order.value = data;
        refundPreview.value = null;
        const nextQty: Record<number, number> = {};
        (data.items || []).forEach((item: any) => {
            nextQty[item.id] = 0;
        });
        refundForm.value.qty = nextQty;
        refundForm.value.qty = nextQty;
        // Fetch invoices
        await loadInvoices();
    } catch (e) {
        console.error(e);
    }
};

const buildRefundPayload = () => {
    const items = refundableItems.value
        .map((item: any) => ({
            order_item_id: item.id,
            qty: Number(refundForm.value.qty[item.id] || 0),
        }))
        .filter((item: any) => item.qty > 0);

    return {
        items,
        reason: refundForm.value.reason || null,
        restock: !!refundForm.value.restock,
        gateway_refund: !!refundForm.value.gateway_refund,
    };
};

const previewRefund = async () => {
    try {
        const payload = buildRefundPayload();
        if (!payload.items.length) {
            dialog.warning($t('Please enter refund quantity for at least one item'));
            return;
        }
        const { data } = await axios.post(`/api/v1/orders/${order.value.id}/refund/preview`, payload);
        refundPreview.value = data.data;
        dialog.success($t('Refund preview generated'));
    } catch (e: any) {
        console.error(e);
        dialog.error(e?.response?.data?.message || $t('Failed to preview refund'));
    }
};

const issueRefund = async () => {
    try {
        const payload = buildRefundPayload();
        if (!payload.items.length) {
            dialog.warning($t('Please enter refund quantity for at least one item'));
            return;
        }
        const confirmed = await dialog.confirm({
            title: $t('Issue Refund'),
            message: $t('Are you sure you want to process this refund? This action cannot be undone.'),
            confirmText: $t('Refund'),
            type: 'warning',
        });
        if (!confirmed) return;

        await axios.post(`/api/v1/orders/${order.value.id}/refund`, payload);
        dialog.success($t('Refund processed successfully'));
        await loadOrder();
    } catch (e: any) {
        console.error(e);
        dialog.error(e?.response?.data?.message || $t('Failed to process refund'));
    }
};

const updateStatus = async () => {
    try {
        const confirmed = await dialog.confirm({
            message: $t('Are you sure you want to change the order status? For manual payments, marking an order as Processing or Completed will automatically confirm pending transactions.')
        });

        if (!confirmed) {
            // Restore old status if cancelled
            await loadOrder();
            return;
        }

        await axios.put(`/api/v1/orders/${order.value.id}`, { status: order.value.status });
        dialog.success($t('Order status updated successfully'));
        
        // Refresh order data to show updated transaction statuses
        await loadOrder();
    } catch (e) {
        console.error(e);
        dialog.error($t('Failed to update order status'));
    }
};

const generateInvoice = async () => {
    try {
        await axios.post(`/api/v1/orders/${order.value.id}/invoices`);
        dialog.success($t('Invoice generated successfully'));
        await loadInvoices();
    } catch (e: any) {
        console.error(e);
        dialog.error(e?.response?.data?.message || $t('Failed to generate invoice'));
    }
};

const downloadInvoice = async () => {
    const validInvoice = invoices.value.find((inv: any) => inv.status !== 'void');
    if (validInvoice) {
        invoiceLoading.value = true;
        await downloadPdfInvoice(validInvoice);
        invoiceLoading.value = false;
    } else {
        dialog.warning($t('No valid invoice found. Please generate one in the invoices section.'));
    }
};

const voidInvoice = async (inv: any) => {
    const confirmed = await dialog.confirm({
        title: $t('Void Invoice'),
        message: $t(`Are you sure you want to void invoice ${inv.invoice_number}?`),
        confirmText: $t('Void'),
        type: 'danger',
    });
    if (!confirmed) return;

    try {
        await axios.patch(`/api/v1/invoices/${inv.id}/void`);
        dialog.success($t('Invoice voided'));
        await loadInvoices();
    } catch (e: any) {
        console.error(e);
        dialog.error(e?.response?.data?.message || $t('Failed to void invoice'));
    }
};

const downloadPdfInvoice = async (inv: any) => {
    invoiceLoadingId.value = inv.id;
    try {
        const response = await axios.get(`/api/v1/invoices/${inv.id}/download`, {
            responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `Invoice-${inv.invoice_number}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (e: any) {
        console.error('Invoice download failed:', e);
        dialog.error($t('Failed to download invoice. Please make sure dompdf is installed.'));
    } finally {
        invoiceLoadingId.value = null;
    }
};

onMounted(loadOrder);
</script>
