<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateProduct;
use App\Actions\DeleteProduct;
use App\Actions\UpdateProduct;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductCollection;
use App\Http\Resources\Api\V1\ProductListResource;
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
    public function index(Request $request): JsonResponse
    {
        $isCompact = $request->boolean('compact');
        $query = Product::query();

        if ($isCompact) {
            $query->select([
                'id',
                'name',
                'slug',
                'sku',
                'price',
                'status',
                'stock_quantity',
                'published_at',
                'locale',
                'translation_group_id',
                'created_at',
                'updated_at',
            ]);
            $query->with(['media' => function ($q) {
                $q->select(['media.id', 'media.name', 'media.file_name', 'media.disk', 'media.path', 'media.mime_type', 'media.size', 'media.type', 'media.alt_text', 'media.metadata', 'media.created_at', 'media.updated_at']);
            }]);
        } else {
            $query->with([
                'user:id,name,email',
                'categories:id,name,slug',
                'tags:id,name,slug',
                'media' => function ($q) {
                    $q->select(['media.id', 'media.name', 'media.file_name', 'media.disk', 'media.path', 'media.mime_type', 'media.size', 'media.type', 'media.alt_text', 'media.metadata', 'media.created_at', 'media.updated_at']);
                }
            ]);

            if ($this->supportsBrands()) {
                $query->with('brands:id,name,slug');
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('project_products') && class_exists(\Modules\Polyx\ProjectHub\Models\Project::class)) {
            $query->with(['projects' => function ($q) {
                $q->select(['projects.id', 'projects.name', 'projects.slug', 'projects.status']);
            }]);
        }

        // Apply filters
        $query = \App\Facades\Hook::applyFilters('product.query.builder', $query, $request);

        // Locale filter
        if ($request->has('locale') && !empty($request->locale)) {
            $query->where('locale', $request->locale);
        } elseif ($request->boolean('primary_locale') || $request->boolean('main_locale')) {
            $defaultLocale = 'en';
            if (\Illuminate\Support\Facades\Schema::hasTable('languages')) {
                $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?: config('app.locale', 'en');
            } else {
                $defaultLocale = config('app.locale', 'en');
            }
            $query->where('locale', $defaultLocale);
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'trash') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->status);
            }
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
            $query->whereHas('categories', fn($q) => $q->where('product_categories.id', $request->category_id));
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
        $sortBy = $request->get('sort_by', $request->get('sort', 'created_at'));
        $sortOrder = $request->get('sort_order', $request->get('order', 'desc'));
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $this->resolvePerPage($request);
        $products = $query->paginate($perPage);

        // Calculate status counts for UI tabs
        $baseCountQuery = Product::query();
        if ($request->has('locale') && !empty($request->locale)) {
            $baseCountQuery->where('locale', $request->locale);
        }

        $counts = [
            'all' => (clone $baseCountQuery)->count(),
            'published' => (clone $baseCountQuery)->where('status', 'published')->count(),
            'draft' => (clone $baseCountQuery)->where('status', 'draft')->count(),
            'archived' => (clone $baseCountQuery)->where('status', 'archived')->count(),
            'trash' => Product::onlyTrashed()->when($request->locale, fn($q) => $q->where('locale', $request->locale))->count(),
        ];

        if ($isCompact) {
            return ProductListResource::collection($products)->additional(['meta' => ['counts' => $counts]])->response();
        }

        return (new ProductCollection($products))->additional(['meta' => ['counts' => $counts]])->response();
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
        $relations = ['user', 'categories', 'tags', 'media', 'services', 'variants.image', 'variantAttributes.values'];
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
        $this->authorize('delete', $product);

        $deleteProduct->execute($product);

        return $this->successResponse(null, 'Product deleted successfully', 204);
    }

    /**
     * Restore a soft-deleted product
     */
    public function restore(int $id): JsonResponse
    {
        $product = Product::withoutGlobalScopes()->withTrashed()->findOrFail($id);
        $this->authorize('update', $product);
        $product->restore();

        return $this->successResponse(new ProductResource($product), 'Product restored successfully');
    }

    /**
     * Permanently delete a product
     */
    public function forceDelete(int $id): JsonResponse
    {
        $product = Product::withoutGlobalScopes()->withTrashed()->findOrFail($id);
        $this->authorize('delete', $product);
        $product->forceDelete();

        return $this->successResponse(null, 'Product permanently deleted', 204);
    }

    /**
     * Bulk soft delete products (Move to trash)
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $products = Product::whereIn('id', $validated['ids'])->get();
        foreach ($products as $product) {
            $this->authorize('delete', $product);
            $product->delete();
        }

        return $this->successResponse(null, 'Products moved to trash successfully');
    }

    /**
     * Bulk restore products
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $products = Product::withoutGlobalScopes()->withTrashed()->whereIn('id', $validated['ids'])->get();
        foreach ($products as $product) {
            $this->authorize('update', $product);
            $product->restore();
        }

        return $this->successResponse(null, 'Products restored successfully');
    }

    /**
     * Bulk permanently delete products
     */
    public function bulkForceDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $products = Product::withoutGlobalScopes()->withTrashed()->whereIn('id', $validated['ids'])->get();
        foreach ($products as $product) {
            $this->authorize('delete', $product);
            $product->forceDelete();
        }

        return $this->successResponse(null, 'Products permanently deleted successfully');
    }

    protected function supportsBrands(): bool
    {
        return Schema::hasTable('product_brand');
    }

    protected function resolvePerPage(Request $request): int
    {
        $requested = (int) $request->input('per_page', $request->input('limit', 15));

        return max(1, min($requested, 100));
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

    /**
     * Translate/Duplicate the specified product to another locale
     */
    public function translate(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10'],
        ]);

        $targetLocale = $validated['locale'];

        // Check if already exists (including trashed records)
        $existing = Product::withoutGlobalScopes()
            ->withTrashed()
            ->where('translation_group_id', $product->translation_group_id)
            ->where('locale', $targetLocale)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            return $this->successResponse(new ProductResource($existing), 'Translation already exists');
        }

        $newProduct = $product->replicate();
        $newProduct->locale = $targetLocale;
        $newProduct->translation_group_id = $product->translation_group_id;
        $newProduct->status = 'draft';
        $newProduct->name = $product->name . ' (' . strtoupper($targetLocale) . ')';
        $newProduct->slug = $product->slug;
        
        // Ensure slug is unique for target locale (checking trashed records as well)
        $baseSlug = $newProduct->slug;
        $counter = 1;
        while (Product::withoutGlobalScopes()->withTrashed()->where('slug', $newProduct->slug)->where('locale', $targetLocale)->exists()) {
            $newProduct->slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Product SKU is synchronized across all translations of the same product
        $newProduct->sku = $product->sku;

        $newProduct->save();

        // Sync categories, tags, brands, media
        $categoryIds = [];
        foreach ($product->categories as $category) {
            $translation = $category->getTranslation($targetLocale);
            $categoryIds[] = $translation ? $translation->id : $category->id;
        }
        $newProduct->categories()->sync($categoryIds);

        $tagIds = [];
        foreach ($product->tags as $tag) {
            $translation = $tag->getTranslation($targetLocale);
            $tagIds[] = $translation ? $translation->id : $tag->id;
        }
        $newProduct->tags()->sync($tagIds);

        if ($this->supportsBrands()) {
            $brandIds = [];
            foreach ($product->brands as $brand) {
                $translation = $brand->getTranslation($targetLocale);
                $brandIds[] = $translation ? $translation->id : $brand->id;
            }
            $newProduct->brands()->sync($brandIds);
        }
        $newProduct->media()->sync($product->media->pluck('id'));

        // Copy variants if any
        foreach ($product->variants as $variant) {
            $newVariant = $variant->replicate();
            $newVariant->product_id = $newProduct->id;
            if ($variant->sku) {
                $newVariant->sku = $variant->sku . '-' . strtoupper($targetLocale);
            }
            $newVariant->save();
        }

        return $this->successResponse(new ProductResource($newProduct), 'Translation created successfully', 201);
    }

    /**
     * Get all preview videos from products settings
     */
    public function previewVideos(Request $request): JsonResponse
    {
        $products = Product::whereNotNull('settings')->get();
        $videos = [];

        foreach ($products as $product) {
            $previewVideos = data_get($product->settings, 'preview_videos', []);
            if (is_array($previewVideos)) {
                foreach ($previewVideos as $video) {
                    $link = data_get($video, 'link');
                    if (!empty($link)) {
                        $videos[] = [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'title' => data_get($video, 'title') ?: $product->name,
                            'url' => $link,
                        ];
                    }
                }
            }
        }

        $videos = collect($videos)->unique('url')->values()->all();

        return response()->json($videos);
    }
}
