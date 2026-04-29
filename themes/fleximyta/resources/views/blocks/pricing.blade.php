@php
    $packages = [];
    if ($product) {
        $packages = $product->services;
        if ((!$packages || $packages->isEmpty()) && isset($product->service_config)) {
            $packages = $product->service_config;
        }
    }
    $style = $attrs['style'] ?? 'cards';
    
    // Normalize style names
    if ($style === 'comparison_table') $style = 'table';

    // Extract all unique capability keys for comparison table
    $allCapabilityKeys = [];
    if ($style === 'table' && !empty($packages)) {
        foreach ($packages as $package) {
            $caps = (array)($package['capabilities'] ?? []);
            foreach (array_keys($caps) as $key) {
                if (!in_array($key, $allCapabilityKeys)) {
                    $allCapabilityKeys[] = $key;
                }
            }
        }
    }


    $margin = $attrs['margin'] ?? null;
    $padding = $attrs['padding'] ?? null;
    $inlineStyles = [];
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

@if(empty($packages) || (is_object($packages) && $packages->isEmpty()))
    <div class="text-center p-10 border-2 border-dashed border-gray-300 rounded-xl my-10">
        <p class="text-gray-500">No pricing packages configured for this product.</p>
    </div>
@else
    <section class="pricing" id="pricing-matrix-{{ $product?->id ?? 'block' }}" style="{{ $styleAttr }}">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            @if($style === 'cards')
                <div class="pricing-cards">
                    @foreach($packages as $package)
                        @php 
                            $isFeatured = $package['is_featured'] ?? false;
                            $price = $package['price'] ?? null;
                        @endphp
                        <div class="pricing-card {{ $isFeatured ? 'featured' : '' }}">
                            <div class="pricing-header">
                                <h3>{{ $package['name'] }}</h3>
                                <div class="price">
                                    {{ $price !== null ? format_currency((float)$price) : 'Free' }}
                                </div>
                                <p>
                                    @if($isFeatured)
                                        Most Popular
                                    @elseif(isset($package['duration_value']) && $package['access_type'] !== 'permanent')
                                        {{ $package['duration_value'] }} {{ $package['duration_unit'] }}{{ $package['duration_value'] > 1 ? 's' : '' }}
                                    @elseif($package['access_type'] === 'permanent')
                                        One-time payment
                                    @else
                                        Per use
                                    @endif
                                </p>
                            </div>
                            <ul class="pricing-features">
                                @php $capabilities = (array)($package['capabilities'] ?? []); @endphp
                                @foreach($capabilities as $key => $val)
                                    <li>
                                        <i class="fas fa-check"></i> 
                                        <span>
                                            @if(is_bool($val) || $val === 'true' || $val === 1 || $val === '1')
                                                {{ $key }}
                                            @else
                                                {{ $val }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="pricing-action">
                                <a href="#" 
                                   onclick="buyNow({{ json_encode([
                                       'product_id' => $product->id,
                                       'service_id' => $package['id'] ?? null,
                                       'name' => ($product->name ?? 'Product') . ' - ' . ($package['name'] ?? 'Plan'),
                                       'price' => (float)($package['price'] ?? 0),
                                       'quantity' => 1
                                   ]) }}, event)"
                                   class="primary-btn">Select {{ $package['name'] }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($style === 'table')

                <div class="pricing-table-container" style="overflow-x: auto; background: #fff; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #f1f5f9;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 600px; table-layout: fixed;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="text-align: left; padding: 2rem 1.5rem; border-bottom: 2px solid #e2e8f0; vertical-align: bottom; width: 25%;">
                                    <span style="font-size: 1.1rem; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em;">Features</span>
                                </th>
                                @foreach($packages as $package)
                                    <th style="text-align: center; padding: 2rem 1rem; border-bottom: 2px solid #e2e8f0;">
                                        <div style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">{{ $package['name'] }}</div>
                                        <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary); background: var(--gradient); -webkit-background-clip: text; background-clip: text; color: transparent;">
                                            {{ isset($package['price']) ? format_currency((float)$package['price']) : 'Free' }}
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allCapabilityKeys as $key)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1.25rem 1.5rem; font-weight: 500; color: #475569; background: #fff;">{{ $key }}</td>
                                    @foreach($packages as $package)
                                        <td style="text-align: center; padding: 1.25rem 1rem; background: #fff;">
                                            @php 
                                                $val = $package['capabilities'][$key] ?? null; 
                                            @endphp
                                            @if($val === true || $val === 'true' || $val === 1 || $val === '1')
                                                <div style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #ecfdf5; border-radius: 50%; margin: 0 auto;">
                                                    <i class="fas fa-check" style="color: #10b981; font-size: 0.875rem;"></i>
                                                </div>
                                            @elseif(!$val)
                                                <i class="fas fa-times" style="color: #cbd5e1; font-size: 1rem;"></i>
                                            @else
                                                <span style="font-weight: 500; color: #334155;">{{ $val }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc;">
                                <td style="padding: 1.5rem;"></td>
                                @foreach($packages as $package)
                                    <td style="text-align: center; padding: 1.5rem 1rem;">
                                        <a href="#" 
                                           onclick="buyNow({{ json_encode([
                                               'product_id' => $product->id,
                                               'service_id' => $package['id'] ?? null,
                                               'name' => ($product->name ?? 'Product') . ' - ' . ($package['name'] ?? 'Plan'),
                                               'price' => (float)($package['price'] ?? 0),
                                               'quantity' => 1
                                           ]) }}, event)"
                                           class="primary-btn" style="display: inline-block; padding: 12px 28px; font-weight: 600; border-radius: 0.75rem; width: 100%; max-width: 160px; text-align: center;">
                                            Select
                                        </a>
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @elseif($style === 'list')
                <div class="pricing-list" style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($packages as $package)
                        <div class="pricing-list-item" style="display: grid; grid-template-columns: auto 1fr auto auto; align-items: center; gap: 1.5rem; padding: 1.25rem 1.5rem; border-radius: 1rem; background: #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #f8fafc;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                <i class="fas fa-rocket"></i>
                            </div>
                            
                            <div style="min-width: 0;">
                                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 700; color: #1e293b;">{{ $package['name'] }}</h3>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    @php $capabilities = array_slice((array)($package['capabilities'] ?? []), 0, 3, true); @endphp
                                    @foreach($capabilities as $k => $v)
                                        <span style="font-size: 0.75rem; padding: 2px 10px; background: #f1f5f9; border-radius: 999px; color: #64748b; font-weight: 500; white-space: nowrap;">
                                            {{ $k }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div style="text-align: right; white-space: nowrap;">
                                <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); background: var(--gradient); -webkit-background-clip: text; background-clip: text; color: transparent;">
                                    {{ isset($package['price']) ? format_currency((float)$package['price']) : 'Free' }}
                                </div>
                                @if(isset($package['duration_value']))
                                    <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">
                                        / {{ $package['duration_value'] }} {{ $package['duration_unit'] }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <a href="#" 
                                   onclick="buyNow({{ json_encode([
                                       'product_id' => $product->id,
                                       'service_id' => $package['id'] ?? null,
                                       'name' => ($product->name ?? 'Product') . ' - ' . ($package['name'] ?? 'Plan'),
                                       'price' => (float)($package['price'] ?? 0),
                                       'quantity' => 1
                                   ]) }}, event)"
                                   class="primary-btn" style="display: inline-block; padding: 10px 24px; font-weight: 600; border-radius: 999px; white-space: nowrap;">
                                    Select
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
