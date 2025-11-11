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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): ProductCollection
    {
        $query = Product::with(['user', 'categories', 'tags', 'media']);

        if ($this->supportsBrands()) {
            $query->with('brands');
        }

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
        $data = $this->normalizePublishData($request->validated());
        if (array_key_exists('compare_at_price', $data)) {
            $data['sale_price'] = $data['compare_at_price'];
            unset($data['compare_at_price']);
        }
        $categoryIds = $data['categories'] ?? [];
        $tagIds = $data['tags'] ?? [];
        $brandIds = $this->supportsBrands() ? ($data['brands'] ?? []) : [];
        $mediaIds = $data['media_ids'] ?? [];

        // Set user_id
        $data['user_id'] = $request->user()->id;

        // Remove from main data
        unset($data['categories'], $data['tags'], $data['media_ids'], $data['brands']);

        $product = $createProduct->execute($data, $categoryIds, $tagIds, $mediaIds, $brandIds);

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
        $relations = ['user', 'categories', 'tags', 'media'];
        if ($this->supportsBrands()) {
            $relations[] = 'brands';
        }

        $product->load($relations);

        return $this->successResponse(new ProductResource($product));
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductRequest $request, Product $product, UpdateProduct $updateProduct): JsonResponse
    {
        $data = $this->normalizePublishData($request->validated());
        if (array_key_exists('compare_at_price', $data)) {
            $data['sale_price'] = $data['compare_at_price'];
            unset($data['compare_at_price']);
        }
        $categoryIds = $data['categories'] ?? null;
        $tagIds = $data['tags'] ?? null;
        $brandIds = $this->supportsBrands() ? ($data['brands'] ?? null) : null;
        $mediaIds = $data['media_ids'] ?? null;

        // Remove from main data
        unset($data['categories'], $data['tags'], $data['media_ids'], $data['brands']);

        $product = $updateProduct->execute($product, $data, $categoryIds, $tagIds, $mediaIds, $brandIds);

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

    protected function supportsBrands(): bool
    {
        return Schema::hasTable('product_brand');
    }

    /**
     * Normalize published/scheduled timestamps based on status.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function normalizePublishData(array $data): array
    {
        $status = $data['status'] ?? null;
        $publishedAt = $data['published_at'] ?? null;
        $scheduledAt = $data['scheduled_at'] ?? null;
        $now = Carbon::now();

        $parse = static fn (?string $value): ?Carbon => $value ? Carbon::parse($value) : null;

        $published = $parse($publishedAt);
        $scheduled = $parse($scheduledAt);

        if ($status === 'published') {
            if ($scheduled && $scheduled->isFuture()) {
                $data['status'] = 'draft';
                $data['scheduled_at'] = $scheduled->toISOString();
                $data['published_at'] = null;
            } else {
                $data['published_at'] = ($published && $published->isPast())
                    ? $published->toISOString()
                    : $now->toISOString();
                $data['scheduled_at'] = null;
            }
        } elseif ($scheduled) {
            if ($scheduled->isPast()) {
                $data['published_at'] = $scheduled->toISOString();
                $data['scheduled_at'] = null;
                $data['status'] = 'published';
            } else {
                $data['scheduled_at'] = $scheduled->toISOString();
                $data['published_at'] = null;
            }
        } else {
            $data['published_at'] = $published?->toISOString();
            if (!empty($data['published_at']) && $status !== 'published') {
                $data['status'] = 'published';
            }
            $data['scheduled_at'] = null;
        }

        return $data;
    }
}
