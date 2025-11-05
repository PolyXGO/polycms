<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    /**
     * Display a listing of tags
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::query();

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
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
            $response = $this->successResponse(TagResource::collection($tags->items()));
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

        return $this->successResponse(TagResource::collection($tags));
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:post,product'],
        ]);

        // Validate unique slug per type
        $existing = Tag::where('type', $validated['type'])
            ->where('slug', $validated['slug'])
            ->exists();

        if ($existing) {
            return $this->errorResponse('Slug already exists for this tag type', 422);
        }

        $tag = Tag::create($validated);

        return $this->successResponse(
            new TagResource($tag),
            'Tag created successfully',
            201
        );
    }

    /**
     * Display the specified tag
     */
    public function show(Tag $tag): JsonResponse
    {
        return $this->successResponse(new TagResource($tag));
    }

    /**
     * Update the specified tag
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', 'string', 'in:post,product'],
        ]);

        // Validate unique slug per type (excluding current)
        if (isset($validated['slug']) || isset($validated['type'])) {
            $type = $validated['type'] ?? $tag->type;
            $slug = $validated['slug'] ?? $tag->slug;

            $existing = Tag::where('type', $type)
                ->where('slug', $slug)
                ->where('id', '!=', $tag->id)
                ->exists();

            if ($existing) {
                return $this->errorResponse('Slug already exists for this tag type', 422);
            }
        }

        $tag->update($validated);

        return $this->successResponse(
            new TagResource($tag),
            'Tag updated successfully'
        );
    }

    /**
     * Remove the specified tag
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return $this->successResponse(null, 'Tag deleted successfully', 204);
    }
}
