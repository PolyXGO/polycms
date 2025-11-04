<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreatePost;
use App\Actions\DeletePost;
use App\Actions\UpdatePost;
use App\Http\Requests\Api\V1\StorePostRequest;
use App\Http\Requests\Api\V1\UpdatePostRequest;
use App\Http\Resources\Api\V1\PostCollection;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request): PostCollection
    {
        $query = Post::with(['user', 'categories', 'tags']);

        // Apply filters
        $query = \App\Facades\Hook::applyFilters('post.query.builder', $query, $request);

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category_id));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = min($request->get('per_page', 15), 100);
        $posts = $query->paginate($perPage);

        return new PostCollection($posts);
    }

    /**
     * Store a newly created post
     */
    public function store(StorePostRequest $request, CreatePost $createPost): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['categories'] ?? [];
        $tagIds = $data['tags'] ?? [];

        // Set user_id
        $data['user_id'] = $request->user()->id;

        // Remove from main data
        unset($data['categories'], $data['tags']);

        $post = $createPost->execute($data, $categoryIds, $tagIds);

        return $this->successResponse(
            new PostResource($post),
            'Post created successfully',
            201
        );
    }

    /**
     * Display the specified post
     */
    public function show(Post $post): JsonResponse
    {
        $post->load(['user', 'categories', 'tags']);

        return $this->successResponse(new PostResource($post));
    }

    /**
     * Update the specified post
     */
    public function update(UpdatePostRequest $request, Post $post, UpdatePost $updatePost): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['categories'] ?? null;
        $tagIds = $data['tags'] ?? null;

        // Remove from main data
        unset($data['categories'], $data['tags']);

        $post = $updatePost->execute($post, $data, $categoryIds, $tagIds);

        return $this->successResponse(
            new PostResource($post),
            'Post updated successfully'
        );
    }

    /**
     * Remove the specified post
     */
    public function destroy(Post $post, DeletePost $deletePost): JsonResponse
    {
        $deletePost->execute($post);

        return $this->successResponse(null, 'Post deleted successfully', 204);
    }
}
