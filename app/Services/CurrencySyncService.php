<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class CurrencySyncService
{
    /**
     * Sync exchange rates from the chosen provider.
     *
     * @param array $currencies List of currencies from UI/DB.
     * @param string $provider 'apilayer' or 'openexchangerates'.
     * @param string $apiKey API key for the provider.
     * @param string $defaultCurrencyCode The code of the store's default currency.
     * @return array Updated list of currencies.
     * @throws Exception
     */
    public function syncRates(array $currencies, string $provider, string $apiKey, string $defaultCurrencyCode): array
    {
        if (empty($currencies)) {
            return $currencies;
        }

        // Get unique currency codes we care about
        $codes = array_map(function ($c) { return strtoupper($c['code']); }, $currencies);
        $codes = array_unique($codes);

        if (!in_array($defaultCurrencyCode, $codes)) {
            $codes[] = $defaultCurrencyCode;
        }

        $rates = []; // Base rates

        if ($provider === 'apilayer') {
            // API Layer: exchangerates_data
            $response = Http::withHeaders([
                'apikey' => $apiKey,
            ])->get('https://api.apilayer.com/exchangerates_data/latest', [
                'symbols' => implode(',', $codes),
            ]);

            if ($response->failed()) {
                throw new Exception('API Layer failed: ' . $response->body());
            }

            $data = $response->json();
            if (empty($data['success'])) {
                throw new Exception('API Layer error: ' . ($data['error']['info'] ?? 'Unknown error'));
            }

            $rates = $data['rates'] ?? [];

        } elseif ($provider === 'openexchangerates') {
            // Open Exchange Rates
            $response = Http::get('https://openexchangerates.org/api/latest.json', [
                'app_id' => $apiKey,
                'symbols' => implode(',', $codes),
            ]);

            if ($response->failed()) {
                throw new Exception('Open Exchange Rates failed: ' . $response->body());
            }

            $data = $response->json();
            if (isset($data['error']) && $data['error']) {
                throw new Exception('Open Exchange Rates error: ' . ($data['description'] ?? 'Unknown error'));
            }

            $rates = $data['rates'] ?? [];
        } else {
            throw new Exception("Unknown or manual API provider: {$provider}");
        }

        if (empty($rates)) {
            throw new Exception("No rates returned from {$provider}.");
        }

        // Ensure the default currency rate was fetched so we can calculate cross-rates
        if (!isset($rates[$defaultCurrencyCode])) {
            // If the base currency of the API IS the default currency, it might not be in the 'rates' array.
            // But usually APIs return the base currency as 1.
            // Let's handle it gracefully.
            $rates[$defaultCurrencyCode] = 1.0;
        }

        // Rebase relative to the store's default currency
        $baseRate = (float) $rates[$defaultCurrencyCode];

        if ($baseRate == 0) {
            throw new Exception("Invalid rate (0) returned for the default currency: {$defaultCurrencyCode}");
        }

        foreach ($currencies as &$currency) {
            $code = strtoupper($currency['code']);
            if (isset($rates[$code])) {
                $apiRate = (float) $rates[$code];
                
                // Cross-conversion: Store rate = API rate / API base rate
                // e.g. If Store Default is VND. API returns USD->VND = 25000, USD->EUR = 0.9
                // Store Rate for EUR = 0.9 / 25000.
                $relativeRate = $apiRate / $baseRate;
                
                // Keep precision up to 8 decimals
                $currency['rate'] = round($relativeRate, 8);
            }
        }

        return $currencies;
    }
}
