<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display category archive
     */
    public function show(string $slug, Request $request): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $data = [
            'category' => $category,
        ];

        // Determine content type from category type or request
        $type = $request->get('type', $category->type ?? 'post');

        if ($type === 'product') {
            // Show products in this category
            $query = Product::with(['categories', 'tags'])
                ->where('status', 'published')
                ->whereHas('categories', function ($q) use ($category) {
                    $q->where('categories.id', $category->id);
                });

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate
            $perPage = min($request->get('per_page', 12), 50);
            $products = $query->paginate($perPage);

            $data['products'] = $products;
            $viewName = 'categories.show';
        } else {
            // Show posts in this category
            $query = Post::with(['user', 'categories', 'tags'])
                ->where('status', 'published')
                ->where('type', 'post')
                ->whereHas('categories', function ($q) use ($category) {
                    $q->where('categories.id', $category->id);
                });

            // Sort
            $sortBy = $request->get('sort_by', 'published_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate
            $perPage = min($request->get('per_page', 12), 50);
            $posts = $query->paginate($perPage);

            $data['posts'] = $posts;
            $viewName = 'categories.show';
        }

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, $viewName);

        return view($viewName, $data);
    }
}