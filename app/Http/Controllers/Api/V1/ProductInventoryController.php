<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Ecommerce\InventoryLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductInventoryController extends Controller
{
    public function __construct(protected InventoryLedgerService $inventoryLedgerService) {}

    public function stockMovements(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !($user->hasRole(['admin', 'editor']) || $user->can('view inventory logs') || $user->can('update product'))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = Product::query()->findOrFail($id);

        $movements = $this->inventoryLedgerService->listForProduct($product->id, [
            'movement_type' => $request->query('movement_type'),
            'reference_type' => $request->query('reference_type'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ]);

        return response()->json(['data' => $movements]);
    }
}
