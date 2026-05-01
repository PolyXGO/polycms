<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Ecommerce\ProductAttributeGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductAttributeGroupController extends Controller
{
    public function index(): JsonResponse
    {
        $groups = ProductAttributeGroup::orderBy('position')->get();

        return response()->json([
            'success' => true,
            'data' => $groups,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_attribute_groups,slug'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $validated['name']));
            $idx = 1;
            $baseSlug = $validated['slug'];
            while (ProductAttributeGroup::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $baseSlug . '_' . $idx++;
            }
        }

        $group = ProductAttributeGroup::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Group created successfully',
            'data' => $group,
        ], 201);
    }

    public function update(Request $request, ProductAttributeGroup $productAttributeGroup): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'unique:product_attribute_groups,slug,' . $productAttributeGroup->id],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $productAttributeGroup->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Group updated successfully',
            'data' => $productAttributeGroup,
        ]);
    }

    public function destroy(ProductAttributeGroup $productAttributeGroup): JsonResponse
    {
        // On delete, attributes tied will have group_id set to null (nullOnDelete)
        $productAttributeGroup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully',
        ]);
    }

    public function syncAttributes(Request $request, ProductAttributeGroup $productAttributeGroup): JsonResponse
    {
        $validated = $request->validate([
            'attribute_ids' => ['present', 'array'],
            'attribute_ids.*' => ['integer', 'exists:product_attributes,id'],
        ]);

        $ids = $validated['attribute_ids'];

        // Remove association for attributes that were in this group but are no longer in the list
        \App\Models\Ecommerce\ProductAttribute::where('group_id', $productAttributeGroup->id)
            ->whereNotIn('id', $ids)
            ->update(['group_id' => null]);

        // Assosciate new ones or ensure they stay assigned
        if (!empty($ids)) {
            \App\Models\Ecommerce\ProductAttribute::whereIn('id', $ids)
                ->update(['group_id' => $productAttributeGroup->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attributes successfully synced to group',
        ]);
    }
}
