<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Facades\Hook;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
 
        if ($tree) {
            $categories = Category::getTree($type, $maxDepth ? (int) $maxDepth : null);
            // Eager load posts count for tree
            $ids = $categories->pluck('id')->all();
            $counts = \Illuminate\Support\Facades\DB::table('post_category')
                ->selectRaw('category_id, COUNT(DISTINCT post_id) as posts_count')
                ->whereIn('category_id', $ids)
                ->groupBy('category_id')
                ->pluck('posts_count', 'category_id');
            foreach ($categories as $cat) {
                $cat->setAttribute('posts_count', $counts[$cat->id] ?? 0);
            }
            return $this->successResponse(CategoryResource::collection($categories));
        }
 
        $query = Category::query()->withCount('posts');
 
        if ($type) {
            $query->where('type', $type);
        }
 
        if ($request->boolean('most_used')) {
            $limit = max(1, min(100, (int) $request->input('limit', 20)));
 
            $query->select('categories.*');
 
            if ($type === 'post') {
                $query
                    ->leftJoin('post_category', 'categories.id', '=', 'post_category.category_id')
                    ->selectRaw('COUNT(DISTINCT post_category.post_id) as usage_count');
            } elseif ($type === 'product_brand') {
                $query
                    ->leftJoin('product_brand', 'categories.id', '=', 'product_brand.brand_id')
                    ->selectRaw('COUNT(DISTINCT product_brand.product_id) as usage_count');
            } else {
                $query
                    ->leftJoin('product_category', 'categories.id', '=', 'product_category.category_id')
                    ->selectRaw('COUNT(DISTINCT product_category.product_id) as usage_count');
            }
 
            $categories = $query
                ->groupBy('categories.id')
                ->orderByDesc('usage_count')
                ->orderBy('categories.name')
                ->limit($limit)
                ->get();
 
            return $this->successResponse(CategoryResource::collection($categories));
        }
 
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        } elseif ($rootOnly) {
            $query->roots($type);
        }
 
        if ($request->has('depth')) {
            $depth = $request->input('depth');
            if (is_numeric($depth)) {
                $query->atDepth((int) $depth, $type);
            }
        }
 
        if ($request->has('descendants_of')) {
            $descendantsOfId = $request->input('descendants_of');
            if (is_numeric($descendantsOfId)) {
                $parentCategory = Category::find((int) $descendantsOfId);
                if ($parentCategory) {
                    $query->descendantsOf($parentCategory);
                }
            }
        }
 
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }
 
        if ($request->boolean('with_children')) {
            $query->with('children');
        }
 
        if ($request->boolean('with_parent')) {
            $query->with('parent');
        }
 
        $query->orderBy('depth')
            ->orderBy('order')
            ->orderBy('name');

        if ($request->has('page') || $request->has('per_page')) {
            $perPage = max(1, min((int) $request->input('per_page', 15), 100));
            $categories = $query->paginate($perPage);

            $data = collect($categories->items())
                ->map(fn (Category $category): array => (new CategoryResource($category))->toArray($request))
                ->all();

            return response()->json([
                'data' => $data,
                'error' => null,
                'meta' => [
                    'total' => $categories->total(),
                    'per_page' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
                'message' => 'Success',
            ]);
        }

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
            'type' => ['required', 'string', 'in:post,product,product_brand'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'template_theme' => ['nullable', 'string', 'max:100'],
            'meta' => ['nullable', 'array'],
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
        Hook::doAction('category.saved', $category, ['operation' => 'created']);

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
            'type' => ['sometimes', 'required', 'string', 'in:post,product,product_brand'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'template_theme' => ['nullable', 'string', 'max:100'],
            'meta' => ['nullable', 'array'],
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
        Hook::doAction('category.saved', $category, ['operation' => 'updated']);

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
        Hook::doAction('category.deleted', $category, ['operation' => 'deleted']);

        return $this->successResponse(null, 'Category deleted successfully', 204);
    }
}
