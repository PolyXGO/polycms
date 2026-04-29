<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Facades\Hook;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Product;
use App\Models\ProductTag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function showPost(string $slug, Request $request): View
    {
        $tag = PostTag::where('slug', $slug)->firstOrFail();

        $query = Post::with(['user', 'categories', 'tags'])
            ->where('status', 'published')
            ->where('type', $request->get('type', 'post'))
            ->whereHas('tags', function ($q) use ($tag) {
                $q->where('post_tags.id', $tag->id);
            });

        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->get('per_page', 12), 50);
        $posts = $query->paginate($perPage);

        $data = [
            'tag' => $tag,
            'posts' => $posts,
            'contentType' => 'post',
        ];

        $data = Hook::applyFilters('theme.view.data', $data, 'tags.show');

        return view('tags.show', $data);
    }

    public function showProduct(string $slug, Request $request): View
    {
        $tag = ProductTag::where('slug', $slug)->firstOrFail();

        $query = Product::with(['categories', 'tags'])
            ->where('status', 'published')
            ->whereHas('tags', function ($q) use ($tag) {
                $q->where('product_tags.id', $tag->id);
            });

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->get('per_page', 12), 50);
        $products = $query->paginate($perPage);

        $data = [
            'tag' => $tag,
            'products' => $products,
            'contentType' => 'product',
        ];

        $data = Hook::applyFilters('theme.view.data', $data, 'tags.show');

        return view('tags.show', $data);
    }
}

