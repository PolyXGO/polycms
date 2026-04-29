<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\TemplateResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected TemplateResolver $templateResolver,
    ) {}
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

        $templateTheme = $category->template_theme ?? null;
        $resolvedView = $this->templateResolver->resolve($viewName, $templateTheme, 'categories');
        $data['__templateTheme'] = $templateTheme;

        // Enrich data for wiki-docs layout: load child categories with their posts
        // This layout is used when the theme is flexidocs
        if (str_contains($resolvedView, 'flexidocs::categories') || str_contains($resolvedView, 'wiki-docs')) {
            $childCategories = Category::where('parent_id', $category->id)
                ->where('type', $category->type)
                ->orderBy('order')
                ->orderBy('name')
                ->get();

            // Load posts for each child category
            foreach ($childCategories as $child) {
                $child->setRelation('groupPosts',
                    Post::with(['meta'])
                        ->where('status', 'published')
                        ->where('type', 'post')
                        ->whereHas('categories', fn($q) => $q->where('categories.id', $child->id))
                        ->orderBy('created_at', 'asc')
                        ->get()
                );
            }

            $data['childCategories'] = $childCategories;

            // Also load posts directly in this category (not in any child)
            $directPosts = Post::with(['meta'])
                ->where('status', 'published')
                ->where('type', 'post')
                ->whereHas('categories', fn($q) => $q->where('categories.id', $category->id))
                ->orderBy('created_at', 'asc')
                ->get();
            $data['directPosts'] = $directPosts;
            
            // Handle loading a specific article directly within the category context
            if (request()->has('article')) {
                $activePost = Post::with(['meta', 'user'])
                    ->where('slug', request('article'))
                    ->where('status', 'published')
                    ->where('type', 'post')
                    ->first();
                if ($activePost) {
                    $data['activePost'] = $activePost;
                }
            }
        }

        return view($resolvedView, $data);
    }

    /**
     * Display a specific documentation post seamlessly leveraging the category show logic.
     */
    public function showDoc(string $categorySlug, string $postSlug, Request $request): View
    {
        $request->merge(['article' => $postSlug]);
        return $this->show($categorySlug, $request);
    }

    /**
     * Display product category archive
     */
    public function showProductCategory(string $slug, Request $request): View
    {
        $category = ProductCategory::where('slug', $slug)->firstOrFail();

        $query = Product::with(['categories', 'tags'])
            ->where('status', 'published')
            ->whereHas('categories', function ($q) use ($category) {
                $q->where('product_categories.id', $category->id);
            });

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->get('per_page', 12), 50);
        $products = $query->paginate($perPage);

        $data = [
            'category' => $category,
            'products' => $products,
        ];

        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'product-categories.show');

        $templateTheme = $category->template_theme ?? null;
        $viewName = $this->templateResolver->resolve('product-categories.show', $templateTheme, 'product_categories');
        $data['__templateTheme'] = $templateTheme;
        return view($viewName, $data);
    }
}