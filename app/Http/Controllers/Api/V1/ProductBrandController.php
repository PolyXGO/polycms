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

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        $query->orderBy('order')->orderBy('name');

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
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

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
        $productBrand->delete();
        return $this->successResponse(null, 'Product brand deleted successfully', 204);
    }
}
