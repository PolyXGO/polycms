<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorePreset;
use App\Models\CorePresetCategory;
use Illuminate\Http\Request;

class CorePresetController extends Controller
{
    public function index(Request $request)
    {
        $query = CorePreset::with('category');

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $presets = $query->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $presets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'payload' => 'required|array',
            'category_id' => 'nullable|exists:polycms_preset_categories,id',
            'description' => 'nullable|string',
            'is_global' => 'nullable|boolean',
        ]);

        $preset = CorePreset::create([
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'payload' => $request->input('payload'),
            'category_id' => $request->input('category_id'),
            'description' => $request->input('description'),
            'is_global' => $request->input('is_global', true),
            'is_system' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Preset saved successfully.',
            'data' => $preset
        ]);
    }

    public function update(Request $request, $id)
    {
        $preset = CorePreset::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:polycms_preset_categories,id',
            'description' => 'nullable|string',
        ]);

        $preset->update([
            'name' => $request->input('name'),
            'category_id' => $request->input('category_id'),
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Preset updated successfully.',
            'data' => $preset
        ]);
    }

    public function destroy($id)
    {
        $preset = CorePreset::findOrFail($id);

        if ($preset->is_system) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete a system preset.'
            ], 403);
        }

        $preset->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Preset deleted successfully.'
        ]);
    }

    public function categories(Request $request)
    {
        $query = CorePresetCategory::query();

        // Only return root categories by default if hierarchical tree is needed,
        // or just return all for a simple flat dropdown.
        $categories = $query->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:polycms_preset_categories,id',
            'description' => 'nullable|string',
        ]);

        $category = CorePresetCategory::create($request->only('name', 'parent_id', 'description'));

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully.',
            'data' => $category
        ]);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = CorePresetCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:polycms_preset_categories,id',
            'description' => 'nullable|string',
        ]);

        $category->update($request->only('name', 'parent_id', 'description'));

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully.',
            'data' => $category
        ]);
    }

    public function destroyCategory($id)
    {
        $category = CorePresetCategory::findOrFail($id);
        
        if (strtolower($category->name) === 'default') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete the Default category.'
            ], 403);
        }

        // When deleting a category, we want child categories and presets to be nullified or handled.
        // The migration has 'set null' for both parent_id and category_id.
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully.'
        ]);
    }
}
