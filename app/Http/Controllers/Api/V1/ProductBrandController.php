<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ProductBrandResource;
use App\Models\ProductBrand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    /**
     * Display a listing of product brands
     */
    public function index(Request $request): JsonResponse
    {
        $tree = $request->boolean('tree', false);

        if ($tree) {
            $brands = ProductBrand::getTree();
            return $this->successResponse(ProductBrandResource::collection($brands));
        }

        $query = ProductBrand::query();

        if ($request->boolean('most_used')) {
            $limit = max(1, min(100, (int) $request->input('limit', 20)));
            $query->select('product_brands.*')
                ->leftJoin('product_brand', 'product_brands.id', '=', 'product_brand.brand_id')
                ->selectRaw('COUNT(DISTINCT product_brand.product_id) as usage_count')
                ->groupBy('product_brands.id')
                ->orderByDesc('usage_count')
                ->orderBy('product_brands.name')
                ->limit($limit);
            return $this->successResponse(ProductBrandResource::collection($query->get()));
        }

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
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

        $query->orderBy('order')->orderBy('name');

        if ($request->has('page') || $request->has('per_page')) {
            $perPage = max(1, min((int) $request->input('per_page', 15), 100));
            $brands = $query->paginate($perPage);

            $data = collect($brands->items())
                ->map(fn (ProductBrand $brand): array => (new ProductBrandResource($brand))->toArray($request))
                ->all();

            return response()->json([
                'data' => $data,
                'error' => null,
                'meta' => [
                    'total' => $brands->total(),
                    'per_page' => $brands->perPage(),
                    'current_page' => $brands->currentPage(),
                    'last_page' => $brands->lastPage(),
                    'from' => $brands->firstItem(),
                    'to' => $brands->lastItem(),
                ],
                'message' => 'Success',
            ]);
        }

        return $this->successResponse(ProductBrandResource::collection($query->get()));
    }

    /**
     * Store a newly created product brand
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:product_brands,slug'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_brands,id'],
            'order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        $brand = ProductBrand::create($validated);

        return $this->successResponse(
            new ProductBrandResource($brand),
            'Product brand created successfully',
            201
        );
    }

    /**
     * Display the specified product brand
     */
    public function show(ProductBrand $productBrand): JsonResponse
    {
        $productBrand->load(['parent', 'children']);
        return $this->successResponse(new ProductBrandResource($productBrand));
    }

    /**
     * Update the specified product brand
     */
    public function update(Request $request, ProductBrand $productBrand): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'unique:product_brands,slug,' . $productBrand->id],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_brands,id'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        // Prevent setting itself as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $productBrand->id) {
            return $this->errorResponse('Cannot set brand as its own parent', 422);
        }

        $productBrand->update($validated);

        return $this->successResponse(
            new ProductBrandResource($productBrand),
            'Product brand updated successfully'
        );
    }

    /**
     * Remove the specified product brand
     */
    public function destroy(ProductBrand $productBrand): JsonResponse
    {
        if ($productBrand->children()->count() > 0) {
            return $this->errorResponse('Cannot delete brand with sub-brands', 'VALIDATION_ERROR', [], 422);
        }

        $productBrand->delete();
        return $this->successResponse(null, 'Product brand deleted successfully', 204);
    }
}
