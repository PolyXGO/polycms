<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CurrencySyncService;
use App\Services\SettingsService;
use Exception;
use Illuminate\Support\Facades\Log;

class SyncCurrenciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecommerce:sync-currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync exchange rates from third-party APIs based on settings.';

    /**
     * Execute the console command.
     */
    public function handle(CurrencySyncService $syncService, SettingsService $settingsService)
    {
        $this->info('Starting currency sync...');

        try {
            $formattingRulesStr = $settingsService->get('currency_formatting_rules');
            if (!$formattingRulesStr) {
                $this->info('No formatting rules found. Skipping.');
                return Command::SUCCESS;
            }

            $rules = is_string($formattingRulesStr) ? json_decode($formattingRulesStr, true) : $formattingRulesStr;
            $provider = $rules['api_provider'] ?? '';
            $apiKey = $rules[$provider . '_api_key'] ?? '';

            if (empty($provider) || $provider === 'manual') {
                $this->info('Currency API provider is set to Manual. Skipping sync.');
                return Command::SUCCESS;
            }

            if (empty($apiKey)) {
                $this->error("API Key is missing for provider: {$provider}");
                return Command::FAILURE;
            }

            $currenciesStr = $settingsService->get('currencies');
            $currencies = is_string($currenciesStr) ? json_decode($currenciesStr, true) : $currenciesStr;

            if (empty($currencies) || !is_array($currencies)) {
                $this->info('No currencies configured. Skipping.');
                return Command::SUCCESS;
            }

            $defaultCode = $settingsService->get('ecommerce_currency', 'USD');

            $this->info("Provider: {$provider}");
            $this->info("Default Currency: {$defaultCode}");

            $updatedCurrencies = $syncService->syncRates($currencies, $provider, $apiKey, $defaultCode);

            // Save back to settings
            $settingsService->setMultiple([
                'currencies' => [
                    'value' => $updatedCurrencies,
                    'type' => 'array',
                ]
            ], 'ecommerce');

            $this->info('Successfully synced and updated exchange rates.');
            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Failed to sync currencies: ' . $e->getMessage());
            Log::error('Currency Sync Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
