<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreatePost;
use App\Actions\DeletePost;
use App\Actions\UpdatePost;
use App\Http\Requests\Api\V1\StorePostRequest;
use App\Http\Requests\Api\V1\UpdatePostRequest;
use App\Http\Resources\Api\V1\PostCollection;
use App\Http\Resources\Api\V1\PostListResource;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request): JsonResponse
    {
        $isCompact = $request->boolean('compact');
        $query = Post::query();

        if ($isCompact) {
            $query->select([
                'id',
                'title',
                'slug',
                'type',
                'status',
                'featured_image',
                'published_at',
                'user_id',
                'locale',
                'translation_group_id',
                'created_at',
                'updated_at',
            ])->with(['user:id,name']);
        } else {
            $relations = [
                'user:id,name,email',
                'categories:categories.id,categories.name,categories.slug',
                'tags:post_tags.id,post_tags.name,post_tags.slug',
                'meta',
            ];
            if ($this->canUseLayoutTemplates()) {
                $relations[] = 'layoutTemplate';
            }
            $query->with($relations);
        }

        // Apply filters
        $query = \App\Facades\Hook::applyFilters('post.query.builder', $query, $request);

        // Locale filter
        if ($request->has('locale') && !empty($request->locale)) {
            $query->where('locale', $request->locale);
        }

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
        $sortBy = $request->get('sort_by', $request->get('sort', 'created_at'));
        $sortOrder = $request->get('sort_order', $request->get('order', 'desc'));
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $this->resolvePerPage($request);
        $posts = $query->paginate($perPage);

        if ($isCompact) {
            return PostListResource::collection($posts)->response();
        }

        return (new PostCollection($posts))->response();
    }

    /**
     * Store a newly created post
     */
    public function store(StorePostRequest $request, CreatePost $createPost): JsonResponse
    {
        $data = $request->validated();
        $categoryIds = $data['categories'] ?? [];
        $tagIds = $data['tags'] ?? [];
        $metaFields = $data['meta_fields'] ?? [];

        // Set user_id
        $data['user_id'] = $request->user()->id;

        // Remove from main data
        unset($data['categories'], $data['tags'], $data['meta_fields']);

        $post = $createPost->execute($data, $categoryIds, $tagIds, $metaFields);

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
        $relations = ['user', 'categories', 'tags', 'meta'];
        if ($this->canUseLayoutTemplates()) {
            $relations[] = 'layoutTemplate';
        }

        $post->load($relations);

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
        $metaFields = $data['meta_fields'] ?? null;

        // Remove from main data
        unset($data['categories'], $data['tags'], $data['meta_fields']);

        $post = $updatePost->execute($post, $data, $categoryIds, $tagIds, $metaFields);

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
        $this->authorize('delete', $post);

        $deletePost->execute($post);

        return $this->successResponse(null, 'Post deleted successfully', 204);
    }

    protected function canUseLayoutTemplates(): bool
    {
        return Schema::hasTable('layout_assets')
            && Schema::hasTable('posts')
            && Schema::hasColumn('posts', 'layout_template_id');
    }

    protected function resolvePerPage(Request $request): int
    {
        $requested = (int) $request->input('per_page', $request->input('limit', 15));

        return max(1, min($requested, 100));
    }

    /**
     * Translate/Duplicate the specified post to another locale
     */
    public function translate(Request $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);
        $this->authorize('create', Post::class);

        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10'],
        ]);

        $targetLocale = $validated['locale'];

        // Check if already exists
        $existing = Post::where('translation_group_id', $post->translation_group_id)
            ->where('locale', $targetLocale)
            ->first();

        if ($existing) {
            return $this->successResponse(new PostResource($existing), 'Translation already exists');
        }

        // Duplicate post
        $newPost = $post->replicate();
        $newPost->locale = $targetLocale;
        $newPost->translation_group_id = $post->translation_group_id;
        $newPost->status = 'draft';
        $newPost->title = $post->title . ' (' . strtoupper($targetLocale) . ')';
        $newPost->slug = $post->slug;
        
        // Ensure slug is unique for this locale
        $baseSlug = $newPost->slug;
        $counter = 1;
        while (Post::where('slug', $newPost->slug)->where('locale', $targetLocale)->exists()) {
            $newPost->slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $newPost->save();

        // Sync categories and tags
        $categoryIds = [];
        foreach ($post->categories as $category) {
            $translation = $category->getTranslation($targetLocale);
            $categoryIds[] = $translation ? $translation->id : $category->id;
        }
        $newPost->categories()->sync($categoryIds);

        $tagIds = [];
        foreach ($post->tags as $tag) {
            $translation = $tag->getTranslation($targetLocale);
            $tagIds[] = $translation ? $translation->id : $tag->id;
        }
        $newPost->tags()->sync($tagIds);

        // Duplicate meta fields if any
        foreach ($post->meta as $meta) {
            $newPost->meta()->create([
                'meta_key' => $meta->meta_key,
                'meta_value' => $meta->meta_value,
            ]);
        }

        return $this->successResponse(new PostResource($newPost), 'Translation created successfully', 201);
    }
}
