<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateProduct;
use App\Actions\DeleteProduct;
use App\Actions\UpdateProduct;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductCollection;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): ProductCollection
    {
        $query = Product::with(['user', 'categories', 'tags', 'media']);

        // Apply filters
        $query = \App\Facades\Hook::applyFilters('product.query.builder', $query, $request);

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Featured filter
        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        // Stock status filter
        if ($request->has('stock_status')) {
            $query->where('stock_status', $request->stock_status);
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category_id));
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = min($request->get('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return new ProductCollection($products);
    }

    /**
     * Store a newly created product
     */
    public function store(StoreProductRequest $request, CreateProduct $createProduct): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['categories'] ?? [];
        $tagIds = $data['tags'] ?? [];
        $mediaIds = $data['media_ids'] ?? [];

        // Set user_id
        $data['user_id'] = $request->user()->id;

        // Remove from main data
        unset($data['categories'], $data['tags'], $data['media_ids']);

        $product = $createProduct->execute($data, $categoryIds, $tagIds, $mediaIds);

        return $this->successResponse(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['user', 'categories', 'tags', 'media']);

        return $this->successResponse(new ProductResource($product));
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductRequest $request, Product $product, UpdateProduct $updateProduct): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['categories'] ?? null;
        $tagIds = $data['tags'] ?? null;
        $mediaIds = $data['media_ids'] ?? null;

        // Remove from main data
        unset($data['categories'], $data['tags'], $data['media_ids']);

        $product = $updateProduct->execute($product, $data, $categoryIds, $tagIds, $mediaIds);

        return $this->successResponse(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product, DeleteProduct $deleteProduct): JsonResponse
    {
        $deleteProduct->execute($product);

        return $this->successResponse(null, 'Product deleted successfully', 204);
    }
}
