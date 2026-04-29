<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of product categories
     */
    public function index(Request $request): JsonResponse
    {
        $tree = $request->boolean('tree', false);
        $rootOnly = $request->boolean('root_only', false);
        $maxDepth = $request->input('max_depth');

        if ($tree) {
            $categories = ProductCategory::getTree($maxDepth ? (int) $maxDepth : null);
            return $this->successResponse(ProductCategoryResource::collection($categories));
        }

        $query = ProductCategory::query();

        if ($request->boolean('most_used')) {
            $limit = max(1, min(100, (int) $request->input('limit', 20)));

            $query->select('product_categories.*')
                ->leftJoin('product_category', 'product_categories.id', '=', 'product_category.category_id')
                ->selectRaw('COUNT(DISTINCT product_category.product_id) as usage_count')
                ->groupBy('product_categories.id')
                ->orderByDesc('usage_count')
                ->orderBy('product_categories.name')
                ->limit($limit);

            return $this->successResponse(ProductCategoryResource::collection($query->get()));
        }

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        } elseif ($rootOnly) {
            $query->roots();
        }

        if ($request->has('depth')) {
            $depth = $request->input('depth');
            if (is_numeric($depth)) {
                $query->atDepth((int) $depth);
            }
        }

        if ($request->has('descendants_of')) {
            $descendantsOfId = $request->input('descendants_of');
            if (is_numeric($descendantsOfId)) {
                $parentCategory = ProductCategory::find((int) $descendantsOfId);
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
                ->map(fn (ProductCategory $category): array => (new ProductCategoryResource($category))->toArray($request))
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

        return $this->successResponse(ProductCategoryResource::collection($query->get()));
    }

    /**
     * Store a newly created product category
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:product_categories,slug'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = ProductCategory::create($validated);

        return $this->successResponse(
            new ProductCategoryResource($category),
            'Product category created successfully',
            201
        );
    }

    /**
     * Display the specified product category
     */
    public function show(ProductCategory $productCategory): JsonResponse
    {
        $productCategory->load(['parent', 'children']);
        return $this->successResponse(new ProductCategoryResource($productCategory));
    }

    /**
     * Update the specified product category
     */
    public function update(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'unique:product_categories,slug,' . $productCategory->id],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        if (isset($validated['parent_id']) && $productCategory->isAncestorOf(ProductCategory::find($validated['parent_id']))) {
            return $this->errorResponse('Cannot set category as parent: would create circular reference', 422);
        }

        $productCategory->update($validated);

        return $this->successResponse(
            new ProductCategoryResource($productCategory),
            'Product category updated successfully'
        );
    }

    /**
     * Remove the specified product category
     */
    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        if ($productCategory->children()->count() > 0) {
            return $this->errorResponse('Cannot delete category with child categories', 'VALIDATION_ERROR', [], 422);
        }

        $productCategory->delete();
        return $this->successResponse(null, 'Product category deleted successfully', 204);
    }
}
