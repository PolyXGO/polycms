<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesPermissions;
use App\Http\Resources\Api\V1\ProductBrandResource;
use App\Models\ProductBrand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    use AuthorizesPermissions;

    /**
     * Display a listing of product brands
     */
    public function index(Request $request): JsonResponse
    {
        $tree = $request->boolean('tree', false);
        $locale = $request->input('locale');

        if ($tree) {
            $brands = ProductBrand::getTree($locale);
            return $this->successResponse(ProductBrandResource::collection($brands));
        }

        $query = ProductBrand::query();

        if ($locale) {
            $query->where('locale', $locale);
        }

        if ($request->boolean('most_used')) {
            $limit = max(1, min(100, (int) $request->input('limit', 20)));
            $query->withCount('products as usage_count')
                ->orderByDesc('usage_count')
                ->orderBy('name')
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
        $this->authorizePermission($request, 'create category');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_brands,id'],
            'order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        $locale = $validated['locale'] ?? \Illuminate\Support\Facades\App::getLocale() ?: 'en';

        // Validate unique slug per locale
        $existing = ProductBrand::where('slug', $validated['slug'])
            ->where('locale', $locale)
            ->exists();

        if ($existing) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'slug' => ['The slug has already been taken.'],
            ]);
        }

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
        $this->authorizePermission($request, 'update category');

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_brands,id'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        // Prevent setting itself as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $productBrand->id) {
            return $this->errorResponse('Cannot set brand as its own parent', 'VALIDATION_ERROR', [], 422);
        }

        if (isset($validated['slug']) || isset($validated['locale'])) {
            $slug = $validated['slug'] ?? $productBrand->slug;
            $locale = $validated['locale'] ?? $productBrand->locale ?? 'en';

            $existing = ProductBrand::where('slug', $slug)
                ->where('locale', $locale)
                ->where('id', '!=', $productBrand->id)
                ->exists();

            if ($existing) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'slug' => ['The slug has already been taken.'],
                ]);
            }
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
    public function destroy(Request $request, ProductBrand $productBrand): JsonResponse
    {
        $this->authorizePermission($request, 'delete category');

        if ($productBrand->children()->count() > 0) {
            return $this->errorResponse('Cannot delete brand with sub-brands', 'VALIDATION_ERROR', [], 422);
        }

        $productBrand->delete();
        return $this->successResponse(null, 'Product brand deleted successfully', 204);
    }

    /**
     * Translate/Duplicate the specified product brand to another locale
     */
    public function translate(Request $request, ProductBrand $productBrand): JsonResponse
    {
        $this->authorizePermission($request, ['update category', 'create category'], true);

        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10'],
        ]);

        $targetLocale = $validated['locale'];

        // Check if already exists
        $existing = ProductBrand::withoutGlobalScopes()
            ->withTrashed()
            ->where('translation_group_id', $productBrand->translation_group_id)
            ->where('locale', $targetLocale)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            return $this->successResponse(new ProductBrandResource($existing), 'Translation already exists');
        }

        $newBrand = $productBrand->replicate();
        $newBrand->locale = $targetLocale;
        $newBrand->translation_group_id = $productBrand->translation_group_id;
        $newBrand->name = $productBrand->name . ' (' . strtoupper($targetLocale) . ')';
        $newBrand->slug = $productBrand->slug;

        // Ensure slug is unique for this locale (checking trashed records)
        $baseSlug = $newBrand->slug;
        $counter = 1;
        while (ProductBrand::withoutGlobalScopes()->withTrashed()->where('slug', $newBrand->slug)->where('locale', $targetLocale)->exists()) {
            $newBrand->slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $newBrand->save();

        return $this->successResponse(new ProductBrandResource($newBrand), 'Translation created successfully', 201);
    }
}
