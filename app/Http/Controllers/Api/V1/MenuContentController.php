<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\EnsuresAdmin;
use App\Models\Post;
use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuContentController extends Controller
{
    use EnsuresAdmin;

    /**
     * Get posts for content browser
     */
    public function posts(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $query = Post::where('type', 'post')
            ->where('status', 'published');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Paginate
        $perPage = min($request->get('per_page', 20), 100);
        $posts = $query->select('id', 'title', 'slug', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse($posts);
    }

    /**
     * Get pages for content browser
     */
    public function pages(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $query = Post::where('type', 'page')
            ->where('status', 'published');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Paginate
        $perPage = min($request->get('per_page', 20), 100);
        $pages = $query->select('id', 'title', 'slug', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse($pages);
    }

    /**
     * Get categories for content browser
     */
    public function categories(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $query = Category::query();

        // Filter by type if provided
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

        // Paginate
        $perPage = min($request->get('per_page', 50), 100);
        $categories = $query->select('id', 'name', 'slug', 'type', 'description')
            ->orderBy('name')
            ->paginate($perPage);

        return $this->successResponse($categories);
    }

    /**
     * Get products for content browser
     */
    public function products(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $query = Product::where('status', 'published');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Paginate
        $perPage = min($request->get('per_page', 20), 100);
        $products = $query->select('id', 'name', 'slug', 'sku', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse($products);
    }

    /**
     * Get tags for content browser
     */
    public function tags(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $query = Tag::query();

        // Filter by type if provided
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

        // Paginate
        $perPage = min($request->get('per_page', 50), 100);
        $tags = $query->select('id', 'name', 'slug', 'type', 'description')
            ->orderBy('name')
            ->paginate($perPage);

        return $this->successResponse($tags);
    }
}
