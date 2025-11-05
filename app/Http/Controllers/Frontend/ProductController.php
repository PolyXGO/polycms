<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): View
    {
        $query = Product::with(['categories', 'tags'])
            ->where('status', 'published');

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
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description_html', 'like', "%{$search}%");
            });
        }

        // Featured filter
        if ($request->has('featured')) {
            $query->where('featured', true);
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = min($request->get('per_page', 12), 50);
        $products = $query->paginate($perPage);

        $data = [
            'products' => $products,
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'products.index');

        return view('products.index', $data);
    }

    /**
     * Display a single product
     */
    public function show(string $slug): View
    {
        $product = Product::with(['categories', 'tags', 'media'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment views
        $product->increment('views');

        $data = [
            'product' => $product,
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'products.show');

        return view('products.show', $data);
    }
}