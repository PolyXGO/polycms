<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ProductTagResource;
use App\Models\ProductTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductTagController extends Controller
{
    /**
     * Display a listing of product tags
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductTag::query();

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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_tags,slug'],
            'description' => ['nullable', 'string'],
        ]);

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
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:product_tags,slug,' . $productTag->id],
            'description' => ['nullable', 'string'],
        ]);

        $productTag->update($validated);

        return $this->successResponse(
            new ProductTagResource($productTag),
            'Product tag updated successfully'
        );
    }

    /**
     * Remove the specified product tag
     */
    public function destroy(ProductTag $productTag): JsonResponse
    {
        $productTag->delete();

        return $this->successResponse(null, 'Product tag deleted successfully', 204);
    }
}
