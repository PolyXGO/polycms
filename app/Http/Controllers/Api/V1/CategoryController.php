<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Parent filter
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } elseif ($request->has('root_only') && $request->boolean('root_only')) {
            $query->whereNull('parent_id');
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $query->orderBy('order')->orderBy('name');

        $categories = $query->get();

        return $this->successResponse(CategoryResource::collection($categories));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = Category::create($validated);

        return $this->successResponse(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    /**
     * Display the specified category
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children']);

        return $this->successResponse(new CategoryResource($category));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category->update($validated);

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has children
        if ($category->children()->count() > 0) {
            return $this->errorResponse('Cannot delete category with child categories', 'VALIDATION_ERROR', [], 422);
        }

        $category->delete();

        return $this->successResponse(null, 'Category deleted successfully', 204);
    }
}
