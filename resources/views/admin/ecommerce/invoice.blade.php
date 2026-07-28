<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .invoice { max-width: 800px; margin: 0 auto; padding: 0; position: relative; }

        /* Layout */
        .layout-table { width: 100%; border-collapse: collapse; border: none; }
        .layout-table td { vertical-align: top; border: none; padding: 0; }

        /* Header Bar */
        .header-bar { background: #1a1a2e; padding: 18px 30px; }
        .header-bar .brand-name { font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.3px; }
        .header-bar .invoice-label { font-size: 17px; font-weight: 600; color: rgba(255,255,255,0.85); text-align: right; }
        .header-bar img.brand-logo { max-height: 40px; max-width: 180px; }

        /* Body */
        .body-content { padding: 24px 30px 16px 30px; }

        /* Invoice Meta — right-aligned block */
        .meta-table { margin-left: auto; margin-bottom: 24px; border-collapse: collapse; }
        .meta-table td { padding: 2px 0; font-size: 12px; border: none; }
        .meta-table td.mk { font-weight: 700; color: #333; padding-right: 14px; text-align: right; }
        .meta-table td.mv { color: #555; text-align: left; }

        /* Parties (Bill To + Seller) */
        .section-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #999; margin-bottom: 4px; }
        .party-name { font-weight: 700; font-size: 13px; color: #1a1a2e; margin-bottom: 2px; }
        .party-detail { font-size: 11px; color: #555; line-height: 1.65; }

        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-top: 22px; }
        .items-table th {
            text-align: left; padding: 8px 10px; font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px; color: #777;
            border-bottom: 2px solid #ddd; background: #fafafa;
        }
        .items-table th.text-right { text-align: right; }
        .items-table td { padding: 9px 10px; font-size: 12px; color: #333; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        .items-table td.text-right { text-align: right; }
        .item-name { font-weight: 600; color: #1a1a2e; }
        .item-meta { font-size: 10px; color: #999; margin-top: 1px; }

        /* Totals */
        .totals-wrapper { margin-top: 14px; }
        .totals-table { margin-left: auto; border-collapse: collapse; min-width: 250px; }
        .totals-table td { padding: 3px 0; font-size: 12px; color: #555; border: none; }
        .totals-table td.tl { text-align: left; padding-right: 24px; }
        .totals-table td.tv { text-align: right; font-weight: 500; }
        .totals-table tr.discount td { color: #c0392b; }
        .totals-table tr.grand-total td {
            padding-top: 8px; font-size: 14px; font-weight: 700; color: #1a1a2e;
            border-top: 2px solid #1a1a2e;
        }
        .paid-via { text-align: right; font-size: 11px; color: #888; margin-top: 3px; }

        /* Footer */
        .invoice-footer {
            margin-top: 36px; padding: 14px 30px; border-top: 1px solid #e8e8e8;
            text-align: center; font-size: 10px; color: #aaa;
        }

        /* Status Stamp */
        .void-stamp {
            position: absolute;
            top: 300px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 130px;
            font-weight: bold;
            color: rgba(255, 0, 0, 0.1);
            text-transform: uppercase;
            transform: rotate(-35deg);
            pointer-events: none;
            z-index: 10;
        }
    </style>
</head>
<body>
@php
    // Immutable Snapshot Data extracted from $invoice object
    $seller = $snapshot['seller'] ?? [];
    $buyer = $snapshot['buyer'] ?? [];
    $items = $snapshot['items'] ?? [];
    $coupons = $snapshot['coupons'] ?? [];
    $totals = $snapshot['totals'] ?? [];
    $format = $snapshot['currency_format'] ?? [];
    
    // Currency formatting helper using immutable properties
    $fc = function($amount) use ($format) {
        $decimals = $format['decimals'] ?? 2;
        $decimalSep = $format['decimal'] ?? '.';
        $thousandsSep = $format['thousands'] ?? ',';
        $symbol = $format['symbol'] ?? '$';
        $position = $format['position'] ?? 'before';
        $spaceOption = !empty($format['space']) ? ' ' : '';

        $formattedNum = number_format((float) $amount, $decimals, $decimalSep, $thousandsSep);
        if ($position === 'after') {
            return $formattedNum . $spaceOption . $symbol;
        }
        return $symbol . $spaceOption . $formattedNum;
    };

    // Build seller address string
    $sellerAddress = collect([
        $seller['address'] ?? null,
        $seller['city'] ?? null,
        $seller['state'] ?? null,
        $seller['country'] ?? null,
    ])->filter()->implode(', ');
@endphp

<div class="invoice">
    @if($invoice->status === 'void')
        <div class="void-stamp">VOID</div>
    @endif

    {{-- ===== DARK HEADER BAR ===== --}}
    <div class="header-bar">
        <table class="layout-table">
            <tr>
                <td style="width: 60%;">
                    @if(!empty($brandLogo))
                        <img class="brand-logo" src="{{ $brandLogo }}" alt="{{ $seller['name'] ?? 'PolyCMS' }}">
                    @else
                        <span class="brand-name">{{ $seller['name'] ?? 'PolyCMS' }}</span>
                    @endif
                </td>
                <td style="width: 40%;">
                    <div class="invoice-label">Invoice</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="body-content">
        {{-- ===== INVOICE META (right-aligned) ===== --}}
        <table class="meta-table">
            <tr>
                <td class="mk">Date:</td>
                <td class="mv">{{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : $invoice->created_at->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="mk">Invoice No:</td>
                <td class="mv">{{ $invoice->invoice_number }}</td>
            </tr>
            @if($invoice->order)
            <tr>
                <td class="mk">Reference:</td>
                <td class="mv">Order #{{ $invoice->order->code }}</td>
            </tr>
            @endif
        </table>

        {{-- ===== BILL TO + SELLER (side by side) ===== --}}
        <table class="layout-table" style="margin-bottom: 4px;">
            <tr>
                {{-- BUYER --}}
                <td style="width: 50%; padding-right: 16px;">
                    <div class="section-label">To:</div>
                    @if(!empty($buyer['billing_address']))
                        <div class="party-name">{{ $buyer['billing_address']['full_name'] ?? '' }}</div>
                        <div class="party-detail">
                            @if(!empty($buyer['billing_address']['address_line'])){{ $buyer['billing_address']['address_line'] }}<br>@endif
                            @if(!empty($buyer['billing_address']['city'])){{ $buyer['billing_address']['city'] }}@endif
                            @if(!empty($buyer['billing_address']['postal_code'])) {{ $buyer['billing_address']['postal_code'] }}@endif
                            @if(!empty($buyer['billing_address']['city']) || !empty($buyer['billing_address']['postal_code']))<br>@endif
                            {{ $buyer['billing_address']['country'] ?? '' }}
                        </div>
                    @else
                        <div class="party-name">{{ $buyer['name'] ?? 'Guest' }}</div>
                        <div class="party-detail">{{ $buyer['email'] ?? '' }}</div>
                    @endif
                </td>

                {{-- SELLER --}}
                <td style="width: 50%; padding-left: 16px;">
                    <div class="section-label">From:</div>
                    <div class="party-name">{{ !empty($seller['company']) ? $seller['company'] : ($seller['name'] ?? 'Seller Name') }}</div>
                    <div class="party-detail">
                        @if($sellerAddress){{ $sellerAddress }}<br>@endif
                        @if(!empty($seller['phone']))Phone: {{ $seller['phone'] }}<br>@endif
                        @if(!empty($seller['email'])){{ $seller['email'] }}<br>@endif
                        @if(!empty($seller['tax_id']))Tax ID: {{ $seller['tax_id'] }}@endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- ===== ITEMS TABLE ===== --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 46%;">Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item['name'] }}</div>
                        @if(!empty($item['metadata']['variant_label']))
                            <div class="item-meta">{{ $item['metadata']['variant_label'] }}</div>
                        @endif
                        @if(!empty($item['metadata']['sku']))
                            <div class="item-meta">SKU: {{ $item['metadata']['sku'] }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ $item['quantity'] }}</td>
                    <td class="text-right">{{ $fc($item['price']) }}</td>
                    <td class="text-right"><strong>{{ $fc($item['total']) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ===== TOTALS ===== --}}
        <div class="totals-wrapper">
            <table class="totals-table">
                <tr>
                    <td class="tl">Subtotal</td>
                    <td class="tv">{{ $fc($totals['subtotal_amount'] ?? 0) }}</td>
                </tr>
                @if(count($coupons) > 0)
                    @foreach($coupons as $coupon)
                    <tr class="discount">
                        <td class="tl">Discount ({{ $coupon['code'] }})</td>
                        <td class="tv">-{{ $fc($coupon['discount_amount']) }}</td>
                    </tr>
                    @endforeach
                @elseif(!empty($totals['discount_amount']) && $totals['discount_amount'] > 0)
                    <tr class="discount">
                        <td class="tl">Discount {{ !empty($totals['discount_code']) ? "({$totals['discount_code']})" : '' }}</td>
                        <td class="tv">-{{ $fc($totals['discount_amount']) }}</td>
                    </tr>
                @endif
                @if(!empty($totals['tax_amount']) && $totals['tax_amount'] > 0)
                    <tr>
                        <td class="tl">Tax</td>
                        <td class="tv">{{ $fc($totals['tax_amount']) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td class="tl">Invoice Total:</td>
                    <td class="tv">{{ $totals['currency'] ?? 'USD' }} {{ $fc($totals['total_amount'] ?? 0) }}</td>
                </tr>
            </table>
            @if(!empty($snapshot['payment_method']))
            <div class="paid-via">Paid via {{ ucfirst(str_replace('_', ' ', $snapshot['payment_method'])) }}</div>
            @endif
        </div>
    </div>

    {{-- ===== FOOTER ===== --}}
    <div class="invoice-footer">
        Thank you for your business!<br>
        {{ !empty($seller['company']) ? $seller['company'] : ($seller['name'] ?? 'PolyCMS') }} &bull; {{ $siteUrl }}
    </div>
</div>
</body>
</html>
