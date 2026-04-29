<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaxRateController extends Controller
{
    public function index(): JsonResponse
    {
        $taxes = TaxRate::orderBy('priority')->orderBy('country')->get();
        return response()->json([
            'success' => true,
            'data' => $taxes
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|size:2',
            'state' => 'nullable|string|max:255',
            'rate' => 'required|numeric|min:0', // e.g. 0.1000 = 10%
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ]);

        // Default constraints for checkboxes
        $validated['is_compound'] = $validated['is_compound'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 0;

        $taxRate = TaxRate::create($validated);

        return response()->json([
            'success' => true,
            'data' => $taxRate,
            'message' => 'Tax rate created successfully.',
        ]);
    }

    public function show(TaxRate $taxRate): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $taxRate,
        ]);
    }

    public function update(Request $request, TaxRate $taxRate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|size:2',
            'state' => 'nullable|string|max:255',
            'rate' => 'required|numeric|min:0',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ]);

        $validated['is_compound'] = $validated['is_compound'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 0;

        $taxRate->update($validated);

        return response()->json([
            'success' => true,
            'data' => $taxRate,
            'message' => 'Tax rate updated successfully.',
        ]);
    }

    public function destroy(TaxRate $taxRate): JsonResponse
    {
        $taxRate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tax rate deleted successfully.',
        ]);
    }
}
