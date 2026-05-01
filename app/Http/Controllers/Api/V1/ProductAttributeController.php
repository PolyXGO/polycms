<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Ecommerce\ProductAttribute;
use App\Models\Ecommerce\ProductAttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    public function index(): JsonResponse
    {
        $attributes = ProductAttribute::with(['values', 'group'])->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $attributes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_id' => ['nullable', 'exists:product_attribute_groups,id'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_attributes,slug'],
            'display_type' => ['required', 'string', 'in:select,color_swatch,image_swatch,button'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($validated['group_id']) && !empty($validated['group_name'])) {
            $groupName = $validated['group_name'];
            $group = \App\Models\Ecommerce\ProductAttributeGroup::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($groupName)],
                ['name' => $groupName]
            );
            $validated['group_id'] = $group->id;
        }
        unset($validated['group_name']);

        if (empty($validated['slug'])) {
            $validated['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $validated['name']));
            $idx = 1;
            $baseSlug = $validated['slug'];
            while (ProductAttribute::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $baseSlug . '_' . $idx++;
            }
        }

        $attribute = ProductAttribute::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Attribute created successfully',
            'data' => $attribute->load(['values', 'group']),
        ], 201);
    }

    public function show(ProductAttribute $attribute): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $attribute->load(['values', 'group']),
        ]);
    }

    public function update(Request $request, ProductAttribute $attribute): JsonResponse
    {
        $validated = $request->validate([
            'group_id' => ['sometimes', 'nullable', 'exists:product_attribute_groups,id'],
            'group_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'unique:product_attributes,slug,' . $attribute->id],
            'display_type' => ['sometimes', 'required', 'string', 'in:select,color_swatch,image_swatch,button'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($validated['group_id']) && !empty($validated['group_name'])) {
            $groupName = $validated['group_name'];
            $group = \App\Models\Ecommerce\ProductAttributeGroup::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($groupName)],
                ['name' => $groupName]
            );
            $validated['group_id'] = $group->id;
        }
        unset($validated['group_name']);

        $attribute->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Attribute updated successfully',
            'data' => $attribute->load(['values', 'group']),
        ]);
    }

    public function destroy(ProductAttribute $attribute): JsonResponse
    {
        $attribute->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute deleted successfully',
        ]);
    }

    // --- VALUES MANAGEMENT ---

    public function storeValue(Request $request, ProductAttribute $attribute): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'hex_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $validated['value']));
            $idx = 1;
            $baseSlug = $validated['slug'];
            while ($attribute->values()->where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $baseSlug . '_' . $idx++;
            }
        }

        // Check if value already exists for this attribute
        if ($attribute->values()->where('value', $validated['value'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This value already exists for this attribute',
            ], 422);
        }

        $value = $attribute->values()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Value added successfully',
            'data' => $value,
        ], 201);
    }

    public function updateValue(Request $request, ProductAttribute $attribute, ProductAttributeValue $value): JsonResponse
    {
        // Ensure the value belongs to the attribute
        if ($value->attribute_id !== $attribute->id) {
            return response()->json(['success' => false, 'message' => 'Value does not belong to this attribute'], 404);
        }

        $validated = $request->validate([
            'value' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255'],
            'hex_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $value->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Value updated successfully',
            'data' => $value,
        ]);
    }

    public function destroyValue(ProductAttribute $attribute, ProductAttributeValue $value): JsonResponse
    {
        if ($value->attribute_id !== $attribute->id) {
            return response()->json(['success' => false, 'message' => 'Value does not belong to this attribute'], 404);
        }

        // Soft delete the value so historical variants don't break
        $value->delete();

        return response()->json([
            'success' => true,
            'message' => 'Value removed successfully',
        ]);
    }
}
