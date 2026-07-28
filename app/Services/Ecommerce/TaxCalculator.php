<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Facades\Hook;
use App\Models\Ecommerce\TaxRate;
use Illuminate\Support\Facades\Cache;

class TaxCalculator
{
    /**
     * Calculate tax for a subtotal based on address
     *
     * PERFORMANCE: Tax rates cached per country+state for 1 hour
     * SECURITY: Always server-side calculation, never trust client
     *
     * @param float $subtotal Amount to calculate tax on
     * @param array $address ['country' => 'VN', 'province' => 'HCM'] or ['state' => 'CA']
     * @return array ['total_tax' => float, 'rates' => [...], 'tax_inclusive' => bool]
     */
    public function calculate(float $subtotal, array $address): array
    {
        $country = $address['country'] ?? '';
        $state = $address['province'] ?? $address['state'] ?? '';

        if (empty($country)) {
            return ['total_tax' => 0, 'rates' => [], 'tax_inclusive' => false];
        }

        // PERFORMANCE: Cache tax rates (rarely change)
        $cacheKey = "tax_rates:{$country}:" . ($state ?: '__all__');
        $rates = Cache::remember($cacheKey, 3600, function () use ($country, $state) {
            return TaxRate::where('is_active', true)
                ->where('country', $country)
                ->where(function ($q) use ($state) {
                    $q->whereNull('state')
                      ->orWhere('state', '')
                      ->orWhere('state', $state);
                })
                ->orderBy('priority')
                ->get();
        });

        if ($rates->isEmpty()) {
            return ['total_tax' => 0, 'rates' => [], 'tax_inclusive' => false];
        }

        $taxAmount = 0;
        $appliedRates = [];
        $taxableAmount = $subtotal;

        foreach ($rates as $rate) {
            $lineTax = round($taxableAmount * (float) $rate->rate, 2);
            $taxAmount += $lineTax;

            $appliedRates[] = [
                'id' => $rate->id,
                'name' => $rate->name,
                'rate' => (float) $rate->rate,
                'percentage' => $rate->percentage,
                'amount' => $lineTax,
                'is_compound' => $rate->is_compound,
            ];

            // Compound: next tax applies on (subtotal + previous taxes)
            if ($rate->is_compound) {
                $taxableAmount += $lineTax;
            }
        }

        $result = [
            'total_tax' => round($taxAmount, 2),
            'rates' => $appliedRates,
            'tax_inclusive' => false, // Can be driven by site settings
        ];

        return Hook::applyFilters('tax.calculated', $result, $subtotal, $address);
    }

    /**
     * Invalidate tax cache (call when tax rates are updated)
     */
    public static function clearCache(?string $country = null, ?string $state = null): void
    {
        if ($country) {
            $cacheKey = "tax_rates:{$country}:" . ($state ?: '__all__');
            Cache::forget($cacheKey);
        }
        // Can't efficiently clear all tax caches without tracking keys;
        // In production, use tagged caches or flush on admin save.
    }
}
