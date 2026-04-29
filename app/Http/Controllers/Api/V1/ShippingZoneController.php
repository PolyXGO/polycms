<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ShippingZoneController extends Controller
{
    public function index(): JsonResponse
    {
        $zones = ShippingZone::withCount('methods')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $zones
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'regions' => 'required|array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'methods' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $zone = ShippingZone::create([
                'name' => $validated['name'],
                'regions' => $validated['regions'],
                'is_active' => $validated['is_active'] ?? true,
                'priority' => $validated['priority'] ?? 0,
            ]);

            if (isset($validated['methods']) && is_array($validated['methods'])) {
                $this->syncMethods($zone, $validated['methods']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $zone->load('methods'),
                'message' => 'Shipping zone created successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating shipping zone: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(ShippingZone $shippingZone): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $shippingZone->load('methods'),
        ]);
    }

    public function update(Request $request, ShippingZone $shippingZone): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'regions' => 'required|array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'methods' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $shippingZone->update([
                'name' => $validated['name'],
                'regions' => $validated['regions'],
                'is_active' => $validated['is_active'] ?? true,
                'priority' => $validated['priority'] ?? 0,
            ]);

            if (isset($validated['methods']) && is_array($validated['methods'])) {
                $this->syncMethods($shippingZone, $validated['methods']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $shippingZone->load('methods'),
                'message' => 'Shipping zone updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating shipping zone: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ShippingZone $shippingZone): JsonResponse
    {
        $shippingZone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping zone deleted successfully.',
        ]);
    }

    protected function syncMethods(ShippingZone $zone, array $methodsData): void
    {
        $existingMethodIds = $zone->methods()->pluck('id')->toArray();
        $updatedMethodIds = [];

        foreach ($methodsData as $index => $methodData) {
            // Validation per method if needed, ignoring for now since it's backend trusted
            $data = [
                'name' => $methodData['name'],
                'type' => $methodData['type'] ?? 'flat_rate',
                'cost' => $methodData['cost'] ?? 0,
                'rules' => $methodData['rules'] ?? null,
                'free_threshold' => $methodData['free_threshold'] ?? null,
                'estimated_days' => $methodData['estimated_days'] ?? null,
                'is_active' => $methodData['is_active'] ?? true,
                'position' => $index,
            ];

            if (isset($methodData['id']) && in_array($methodData['id'], $existingMethodIds)) {
                $zone->methods()->where('id', $methodData['id'])->update($data);
                $updatedMethodIds[] = $methodData['id'];
            } else {
                $newMethod = $zone->methods()->create($data);
                $updatedMethodIds[] = $newMethod->id;
            }
        }

        // Delete removed methods
        $methodsToDelete = array_diff($existingMethodIds, $updatedMethodIds);
        if (!empty($methodsToDelete)) {
            $zone->methods()->whereIn('id', $methodsToDelete)->delete();
        }
    }
}
