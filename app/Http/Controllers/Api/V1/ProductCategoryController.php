<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesPermissions;
use App\Http\Resources\Api\V1\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use AuthorizesPermissions;

    /**
     * Display a listing of product categories
     */
    public function index(Request $request): JsonResponse
    {
        $tree = $request->boolean('tree', false);
        $rootOnly = $request->boolean('root_only', false);
        $maxDepth = $request->input('max_depth');
        $locale = $request->input('locale');

        if ($tree) {
            $categories = ProductCategory::getTree($maxDepth ? (int) $maxDepth : null, $locale);
            return $this->successResponse(ProductCategoryResource::collection($categories));
        }

        $query = ProductCategory::query();

        if ($locale) {
            $query->where('locale', $locale);
        }

        if ($request->boolean('most_used')) {
            $limit = max(1, min(100, (int) $request->input('limit', 20)));

            $query->select('product_categories.*')
                ->selectRaw('(SELECT COUNT(DISTINCT product_id) FROM product_category WHERE category_id = product_categories.id) as usage_count')
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
        $this->authorizePermission($request, 'create category');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        $locale = $validated['locale'] ?? \Illuminate\Support\Facades\App::getLocale() ?: 'en';

        // Validate unique slug per locale
        $existing = ProductCategory::where('slug', $validated['slug'])
            ->where('locale', $locale)
            ->exists();

        if ($existing) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'slug' => ['The slug has already been taken.'],
            ]);
        }

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
        $this->authorizePermission($request, 'update category');

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        if (isset($validated['parent_id']) && $productCategory->isAncestorOf(ProductCategory::find($validated['parent_id']))) {
            return $this->errorResponse('Cannot set category as parent: would create circular reference', 'VALIDATION_ERROR', [], 422);
        }

        if (isset($validated['slug']) || isset($validated['locale'])) {
            $slug = $validated['slug'] ?? $productCategory->slug;
            $locale = $validated['locale'] ?? $productCategory->locale ?? 'en';

            $existing = ProductCategory::where('slug', $slug)
                ->where('locale', $locale)
                ->where('id', '!=', $productCategory->id)
                ->exists();

            if ($existing) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'slug' => ['The slug has already been taken.'],
                ]);
            }
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
    public function destroy(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $this->authorizePermission($request, 'delete category');

        if ($productCategory->children()->count() > 0) {
            return $this->errorResponse('Cannot delete category with child categories', 'VALIDATION_ERROR', [], 422);
        }

        $productCategory->delete();
        return $this->successResponse(null, 'Product category deleted successfully', 204);
    }

    /**
     * Translate/Duplicate the specified product category to another locale
     */
    public function translate(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $this->authorizePermission($request, ['update category', 'create category'], true);

        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10'],
        ]);

        $targetLocale = $validated['locale'];

        // Check if already exists
        $existing = ProductCategory::withoutGlobalScopes()
            ->withTrashed()
            ->where('translation_group_id', $productCategory->translation_group_id)
            ->where('locale', $targetLocale)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            return $this->successResponse(new ProductCategoryResource($existing), 'Translation already exists');
        }

        $newCategory = $productCategory->replicate();
        $newCategory->locale = $targetLocale;
        $newCategory->translation_group_id = $productCategory->translation_group_id;
        $newCategory->name = $productCategory->name . ' (' . strtoupper($targetLocale) . ')';
        $newCategory->slug = $productCategory->slug;

        if ($productCategory->parent_id) {
            $parentCategory = ProductCategory::find($productCategory->parent_id);
            if ($parentCategory) {
                $translatedParent = $parentCategory->getTranslation($targetLocale);
                $newCategory->parent_id = $translatedParent ? $translatedParent->id : null;
            }
        }

        // Ensure slug is unique for this locale (checking trashed records)
        $baseSlug = $newCategory->slug;
        $counter = 1;
        while (ProductCategory::withoutGlobalScopes()->withTrashed()->where('slug', $newCategory->slug)->where('locale', $targetLocale)->exists()) {
            $newCategory->slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $newCategory->save();

        return $this->successResponse(new ProductCategoryResource($newCategory), 'Translation created successfully', 201);
    }
}
