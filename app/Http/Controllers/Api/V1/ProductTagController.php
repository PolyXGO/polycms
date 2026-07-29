<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesPermissions;
use App\Http\Resources\Api\V1\ProductTagResource;
use App\Models\ProductTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductTagController extends Controller
{
    use AuthorizesPermissions;

    /**
     * Display a listing of product tags
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductTag::query();

        // Locale filter
        if ($request->has('locale') && !empty($request->locale)) {
            $query->where('locale', $request->locale);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $query->orderBy('name');

        // Paginate if needed
        if ($request->has('per_page')) {
            $perPage = min($request->get('per_page', 15), 100);
            $tags = $query->paginate($perPage);
            $response = $this->successResponse(ProductTagResource::collection($tags->items()));
            $response->getData()->meta = [
                'pagination' => [
                    'total' => $tags->total(),
                    'per_page' => $tags->perPage(),
                    'current_page' => $tags->currentPage(),
                    'last_page' => $tags->lastPage(),
                ],
            ];
            return $response;
        }

        $tags = $query->get();

        return $this->successResponse(ProductTagResource::collection($tags));
    }

    /**
     * Store a newly created product tag
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'create tag');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        $locale = $validated['locale'] ?? \Illuminate\Support\Facades\App::getLocale() ?: 'en';
        $slug = $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['name']);

        // Validate unique slug per locale
        $existing = ProductTag::where('slug', $slug)
            ->where('locale', $locale)
            ->exists();

        if ($existing) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'slug' => ['The slug has already been taken.'],
            ]);
        }

        $validated['slug'] = $slug;
        $tag = ProductTag::create($validated);

        return $this->successResponse(
            new ProductTagResource($tag),
            'Product tag created successfully',
            201
        );
    }

    /**
     * Display the specified product tag
     */
    public function show(ProductTag $productTag): JsonResponse
    {
        return $this->successResponse(new ProductTagResource($productTag));
    }

    /**
     * Update the specified product tag
     */
    public function update(Request $request, ProductTag $productTag): JsonResponse
    {
        $this->authorizePermission($request, 'update tag');

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        if (isset($validated['slug']) || isset($validated['locale'])) {
            $slug = $validated['slug'] ?? $productTag->slug;
            $locale = $validated['locale'] ?? $productTag->locale ?? 'en';

            $existing = ProductTag::where('slug', $slug)
                ->where('locale', $locale)
                ->where('id', '!=', $productTag->id)
                ->exists();

            if ($existing) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'slug' => ['The slug has already been taken.'],
                ]);
            }
        }

        $productTag->update($validated);

        return $this->successResponse(
            new ProductTagResource($productTag),
            'Product tag updated successfully'
        );
    }

    /**
     * Remove the specified product tag
     */
    public function destroy(Request $request, ProductTag $productTag): JsonResponse
    {
        $this->authorizePermission($request, 'delete tag');

        $productTag->delete();

        return $this->successResponse(null, 'Product tag deleted successfully', 204);
    }

    /**
     * Translate/Duplicate the specified product tag to another locale
     */
    public function translate(Request $request, ProductTag $productTag): JsonResponse
    {
        $this->authorizePermission($request, ['update tag', 'create tag'], true);

        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10'],
        ]);

        $targetLocale = $validated['locale'];

        // Check if already exists
        $existing = ProductTag::withoutGlobalScopes()
            ->withTrashed()
            ->where('translation_group_id', $productTag->translation_group_id)
            ->where('locale', $targetLocale)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            return $this->successResponse(new ProductTagResource($existing), 'Translation already exists');
        }

        $newTag = $productTag->replicate();
        $newTag->locale = $targetLocale;
        $newTag->translation_group_id = $productTag->translation_group_id;
        $newTag->name = $productTag->name . ' (' . strtoupper($targetLocale) . ')';
        $newTag->slug = $productTag->slug;

        // Ensure slug is unique for this locale (checking trashed records)
        $baseSlug = $newTag->slug;
        $counter = 1;
        while (ProductTag::withoutGlobalScopes()->withTrashed()->where('slug', $newTag->slug)->where('locale', $targetLocale)->exists()) {
            $newTag->slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $newTag->save();

        return $this->successResponse(new ProductTagResource($newTag), 'Translation created successfully', 201);
    }
}
