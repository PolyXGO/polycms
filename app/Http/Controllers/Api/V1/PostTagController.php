<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\PostTagResource;
use App\Models\PostTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostTagController extends Controller
{
    /**
     * Display a listing of post tags
     */
    public function index(Request $request): JsonResponse
    {
        $query = PostTag::query();

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
            $response = $this->successResponse(PostTagResource::collection($tags->items()));
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

        return $this->successResponse(PostTagResource::collection($tags));
    }

    /**
     * Store a newly created post tag
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:post_tags,slug'],
            'description' => ['nullable', 'string'],
        ]);

        $tag = PostTag::create($validated);

        return $this->successResponse(
            new PostTagResource($tag),
            'Post tag created successfully',
            201
        );
    }

    /**
     * Display the specified post tag
     */
    public function show(PostTag $postTag): JsonResponse
    {
        return $this->successResponse(new PostTagResource($postTag));
    }

    /**
     * Update the specified post tag
     */
    public function update(Request $request, PostTag $postTag): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:post_tags,slug,' . $postTag->id],
            'description' => ['nullable', 'string'],
        ]);

        $postTag->update($validated);

        return $this->successResponse(
            new PostTagResource($postTag),
            'Post tag updated successfully'
        );
    }

    /**
     * Remove the specified post tag
     */
    public function destroy(PostTag $postTag): JsonResponse
    {
        $postTag->delete();

        return $this->successResponse(null, 'Post tag deleted successfully', 204);
    }
}
