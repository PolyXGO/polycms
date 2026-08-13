<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Facades\Hook;
use App\Http\Controllers\Frontend\FrontendController;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Product;
use App\Models\ProductTag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends FrontendController
{
    public function showPost(string $slug, Request $request): View
    {
        $tag = PostTag::where('slug', $slug)->firstOrFail();
        $isAdmin = $this->isAdmin($request);

        $query = Post::with(['user', 'categories', 'tags'])
            ->where('type', $request->get('type', 'post'))
            ->whereHas('tags', function ($q) use ($tag) {
                $q->where('post_tags.id', $tag->id);
            });

        if (!$isAdmin) {
            $query->where('status', 'published');
        }

        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->get('per_page', 12), 50);
        $posts = $query->paginate($perPage)->withQueryString();

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
        $isAdmin = $this->isAdmin($request);

        $query = Product::with(['categories', 'tags'])
            ->whereHas('tags', function ($q) use ($tag) {
                $q->where('product_tags.id', $tag->id);
            });

        if (!$isAdmin) {
            $query->where('status', 'published');
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->get('per_page', 12), 50);
        $products = $query->paginate($perPage)->withQueryString();

        $data = [
            'tag' => $tag,
            'products' => $products,
            'contentType' => 'product',
        ];

        $data = Hook::applyFilters('theme.view.data', $data, 'tags.show');

        return view('tags.show', $data);
    }
}

