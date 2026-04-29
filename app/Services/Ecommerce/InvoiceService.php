<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderInvoice;
use App\Services\SettingsService;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * Generate an immutable invoice for a given order.
     * Uses a 10-digit random number to support scaling up to billions of invoices.
     */
    public function issueInvoice(Order $order): OrderInvoice
    {
        // Don't auto-issue if an active invoice already exists
        $existing = $order->invoices()->where('status', '!=', 'void')->first();
        if ($existing) {
            return $existing;
        }

        // Build snapshot data
        $currencySymbol = $this->settings->get('ecommerce_currency_symbol', '$');
        $symbolPosition = $this->settings->get('currency_symbol_position', 'before');
        $currencySpace = (bool) $this->settings->get('currency_space', false);
        $thousandsSep = $this->settings->get('currency_thousands_separator', ',');
        $decimalSep = $this->settings->get('currency_decimal_separator', '.');
        $decimals = (int) $this->settings->get('currency_decimals', 2);

        $store = [
            'name' => $this->settings->get('ecommerce_store_name', ''),
            'company' => $this->settings->get('ecommerce_company_name', ''),
            'phone' => $this->settings->get('ecommerce_phone_number', ''),
            'email' => $this->settings->get('ecommerce_store_email', ''),
            'address' => $this->settings->get('ecommerce_address_line1', ''),
            'city' => $this->settings->get('ecommerce_address_city', ''),
            'state' => $this->settings->get('ecommerce_address_state', ''),
            'country' => $this->settings->get('ecommerce_address_country', ''),
            'tax_id' => $this->settings->get('ecommerce_tax_id', ''),
        ];

        // Format items uniformly to freeze them
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => $item->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->total,
                'metadata' => $item->metadata, // Captures sku, variant_label etc.
            ];
        }

        // Coupons
        $coupons = [];
        if ($order->coupons) {
            foreach ($order->coupons as $coupon) {
                $coupons[] = [
                    'code' => $coupon->code,
                    'discount_amount' => $coupon->discount_amount,
                ];
            }
        }

        // Buyer Data
        $buyer = [
            'name' => $order->user?->name ?? 'Guest',
            'email' => $order->user?->email ?? $order->guest_email,
        ];
        if ($order->billing_address) {
            $buyer['billing_address'] = $order->billing_address;
        }

        $snapshot = [
            'seller' => $store,
            'buyer' => $buyer,
            'items' => $items,
            'coupons' => $coupons,
            'totals' => [
                'subtotal_amount' => $order->subtotal_amount,
                'discount_amount' => $order->discount_amount,
                'discount_code' => $order->discount_code,
                'tax_amount' => $order->tax_amount,
                'total_amount' => $order->total_amount,
                'currency' => $order->currency ?? 'USD',
            ],
            'payment_method' => $order->payment_method,
            'currency_format' => [
                'symbol' => $currencySymbol,
                'position' => $symbolPosition,
                'space' => $currencySpace,
                'thousands' => $thousandsSep,
                'decimal' => $decimalSep,
                'decimals' => $decimals,
            ]
        ];

        // Generate Prefix
        $prefix = $this->settings->get('ecommerce_invoice_prefix', 'INV');

        // Generation loop to guarantee uniqueness
        $maxAttempts = 10;
        $attempt = 0;
        $invoiceNumber = '';

        while ($attempt < $maxAttempts) {
            // Generate an 8-character alphanumeric uppercase string
            // e.g. R7X9Q2YV. This provides 36^8 (2.8 trillion) combinations for a very short code.
            $random = strtoupper(Str::random(8));
            $candidate = $prefix . $random;

            if (!OrderInvoice::where('invoice_number', $candidate)->exists()) {
                $invoiceNumber = $candidate;
                break;
            }
            $attempt++;
        }

        if (empty($invoiceNumber)) {
            throw new Exception("Unable to generate a unique invoice number after {$maxAttempts} attempts.");
        }

        // Create the invoice
        return OrderInvoice::create([
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'billing_snapshot' => $snapshot,
            'total_amount' => $order->total_amount,
            'tax_amount' => $order->tax_amount ?? 0,
            'status' => 'issued',
            'issued_at' => now(),
        ]);
    }

    /**
     * Void an active invoice.
     */
    public function voidInvoice(OrderInvoice $invoice): void
    {
        $invoice->update([
            'status' => 'void'
        ]);
    }
}
