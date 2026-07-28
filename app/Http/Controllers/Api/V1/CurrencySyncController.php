<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CurrencySyncService;
use Illuminate\Http\Request;

class CurrencySyncController extends Controller
{
    protected CurrencySyncService $syncService;

    public function __construct(CurrencySyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Sync exchange rates for the given currencies based on API settings.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'currencies' => 'required|array',
            'api_provider' => 'required|string',
            'api_key' => 'required|string',
            'default_currency_code' => 'required|string',
        ]);

        try {
            $updatedCurrencies = $this->syncService->syncRates(
                $request->input('currencies'),
                $request->input('api_provider'),
                $request->input('api_key'),
                $request->input('default_currency_code')
            );

            return response()->json([
                'success' => true,
                'message' => _l('Exchange rates synced successfully.'),
                'data' => $updatedCurrencies
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
