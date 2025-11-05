<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request): View
    {
        $query = Post::with(['user', 'categories', 'tags'])
            ->where('status', 'published');

        // Type filter (post, page, news)
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        } else {
            $query->where('type', 'post');
        }

        // Category filter
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->get('category'));
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content_html', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = min($request->get('per_page', 12), 50);
        $posts = $query->paginate($perPage);

        $data = [
            'posts' => $posts,
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'posts.index');

        return view('posts.index', $data);
    }

    /**
     * Display a single post
     */
    public function show(string $slug): View
    {
        $post = Post::with(['user', 'categories', 'tags'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment views
        $post->increment('views');

        $data = [
            'post' => $post,
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'posts.show');

        return view('posts.show', $data);
    }
}