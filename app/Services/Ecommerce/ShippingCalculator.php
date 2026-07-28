<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Facades\Hook;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\ShippingMethod;
use App\Models\Ecommerce\ShippingZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ShippingCalculator
{
    /**
     * Get available shipping methods for an address
     *
     * PERFORMANCE: Zones cached for 1 hour (rarely change)
     * Works on both MySQL and PostgreSQL (no DB-specific JSON queries)
     */
    public function getAvailableMethods(array $address, ?Cart $cart = null): Collection
    {
        $country = $address['country'] ?? null;
        $province = $address['province'] ?? $address['state'] ?? null;

        // PERFORMANCE: Cache zones and methods for 1 hour
        $zones = Cache::remember('shipping_zones_active', 3600, function () {
            return ShippingZone::where('is_active', true)
                ->with(['methods' => fn($q) => $q->where('is_active', true)->orderBy('position')])
                ->orderBy('priority')
                ->get();
        });

        // Match zone by region (application-level JSON matching — works on all DBs)
        $matchedZone = $zones->first(function ($zone) use ($country, $province) {
            foreach ($zone->regions as $region) {
                if (($region['country'] ?? '') === $country) {
                    // If no provinces specified, entire country matches
                    if (empty($region['provinces'])) {
                        return true;
                    }
                    // Check specific province
                    if (in_array($province, $region['provinces'] ?? [])) {
                        return true;
                    }
                }
            }
            return false;
        });

        // Fallback: "Rest of World" zone (highest priority number = last)
        if (!$matchedZone) {
            $matchedZone = $zones->last();
        }

        if (!$matchedZone || $matchedZone->methods->isEmpty()) {
            return collect();
        }

        $cartTotals = $cart ? app(CartService::class)->calculateTotals($cart) : ['subtotal' => 0];

        $methods = $matchedZone->methods->map(function ($method) use ($cartTotals, $cart) {
            $cost = $this->calculateMethodCost($method, $cart, $cartTotals);

            // Hook: modules can override shipping cost
            $cost = Hook::applyFilters('shipping.calculate_cost', $cost, $method, $cart);

            return [
                'id' => $method->id,
                'name' => $method->name,
                'type' => $method->type,
                'cost' => round($cost, 2),
                'estimated_days' => $method->estimated_days,
                'is_free' => $cost <= 0,
            ];
        });

        return Hook::applyFilters('shipping.available_methods', $methods, $address, $cart);
    }

    /**
     * Calculate cost for a specific shipping method
     */
    protected function calculateMethodCost(ShippingMethod $method, ?Cart $cart, array $totals): float
    {
        // Free shipping threshold
        if ($method->free_threshold && ($totals['subtotal'] ?? 0) >= (float) $method->free_threshold) {
            return 0;
        }

        return match ($method->type) {
            'free_shipping' => 0,
            'flat_rate' => (float) $method->cost,
            'weight_based' => $this->calculateWeightBased($method, $cart),
            'price_based' => $this->calculatePriceBased($method, $totals),
            default => (float) $method->cost,
        };
    }

    /**
     * Weight-based shipping: base cost + per-kg surcharge
     */
    protected function calculateWeightBased(ShippingMethod $method, ?Cart $cart): float
    {
        if (!$cart) {
            return (float) $method->cost;
        }

        $cart->loadMissing(['items.product', 'items.variant']);
        $totalWeight = 0;

        foreach ($cart->items as $item) {
            $weight = $item->variant?->weight ?? $item->product?->weight ?? 0;
            $totalWeight += (float) $weight * $item->quantity;
        }

        $rules = $method->rules ?? [];
        $baseCost = (float) ($rules['base_cost'] ?? $method->cost);
        $costPerKg = (float) ($rules['cost_per_kg'] ?? 0);
        $minWeight = (float) ($rules['min_weight'] ?? 0);
        $maxWeight = (float) ($rules['max_weight'] ?? PHP_FLOAT_MAX);

        // Clamp weight to valid range
        $chargeableWeight = max(0, $totalWeight - $minWeight);
        if ($maxWeight > 0 && $totalWeight > $maxWeight) {
            // Over max weight — could reject or charge premium
            $chargeableWeight = $totalWeight;
        }

        return $baseCost + ($chargeableWeight * $costPerKg);
    }

    /**
     * Price-based shipping: different rates for different cart value tiers
     */
    protected function calculatePriceBased(ShippingMethod $method, array $totals): float
    {
        $subtotal = $totals['subtotal'] ?? 0;
        $rules = $method->rules ?? [];
        $tiers = $rules['tiers'] ?? [];

        if (empty($tiers)) {
            return (float) $method->cost;
        }

        // Sort tiers by min_value descending to find the matching tier
        usort($tiers, fn($a, $b) => ($b['min_value'] ?? 0) <=> ($a['min_value'] ?? 0));

        foreach ($tiers as $tier) {
            if ($subtotal >= ($tier['min_value'] ?? 0)) {
                return (float) ($tier['cost'] ?? $method->cost);
            }
        }

        return (float) $method->cost;
    }

    /**
     * Invalidate shipping cache (call when zones/methods are updated)
     */
    public static function clearCache(): void
    {
        Cache::forget('shipping_zones_active');
    }
}
