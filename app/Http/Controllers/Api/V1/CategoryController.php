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
        $type = $request->input('type');
        $tree = $request->boolean('tree', false);
        $rootOnly = $request->boolean('root_only', false);
        $maxDepth = $request->input('max_depth');

        // If tree structure requested
        if ($tree) {
            $categories = Category::getTree($type, $maxDepth ? (int)$maxDepth : null);
            return $this->successResponse(CategoryResource::collection($categories));
        }

        $query = Category::query();

        // Type filter (required for proper separation)
        if ($type) {
            $query->where('type', $type);
        }

        // Parent filter
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        } elseif ($rootOnly) {
            $query->roots($type);
        }

        // Depth filter
        if ($request->has('depth')) {
            $depth = $request->input('depth');
            if (is_numeric($depth)) {
                $query->atDepth((int)$depth, $type);
            }
        }

        // Descendants of a category
        if ($request->has('descendants_of')) {
            $descendantsOfId = $request->input('descendants_of');
            if (is_numeric($descendantsOfId)) {
                $parentCategory = Category::find((int)$descendantsOfId);
                if ($parentCategory) {
                    $query->descendantsOf($parentCategory);
                }
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        // Eager load relationships if requested
        if ($request->boolean('with_children')) {
            $query->with('children');
        }

        if ($request->boolean('with_parent')) {
            $query->with('parent');
        }

        // Sort
        $query->orderBy('depth')
            ->orderBy('order')
            ->orderBy('name');

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
            'slug' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:post,product'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Validate unique slug per type
        $existing = Category::where('type', $validated['type'])
            ->where('slug', $validated['slug'])
            ->exists();

        if ($existing) {
            return $this->errorResponse('Slug already exists for this category type', 422);
        }

        // Validate parent type matches
        if (isset($validated['parent_id'])) {
            $parent = Category::find($validated['parent_id']);
            if ($parent && $parent->type !== $validated['type']) {
                return $this->errorResponse('Parent category must be of the same type', 422);
            }
        }

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
            'slug' => ['sometimes', 'required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', 'string', 'in:post,product'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        // Validate unique slug per type (excluding current)
        if (isset($validated['slug']) || isset($validated['type'])) {
            $type = $validated['type'] ?? $category->type;
            $slug = $validated['slug'] ?? $category->slug;

            $existing = Category::where('type', $type)
                ->where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists();

            if ($existing) {
                return $this->errorResponse('Slug already exists for this category type', 422);
            }
        }

        // Validate parent type matches and prevent circular reference
        if (isset($validated['parent_id'])) {
            $parent = Category::find($validated['parent_id']);
            if ($parent) {
                if ($parent->type !== ($validated['type'] ?? $category->type)) {
                    return $this->errorResponse('Parent category must be of the same type', 422);
                }
                // Prevent circular reference (category cannot be its own descendant)
                if ($category->isAncestorOf($parent)) {
                    return $this->errorResponse('Cannot set category as parent: would create circular reference', 422);
                }
            }
        }

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
