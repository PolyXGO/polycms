<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Frontend\FrontendController;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\TemplateResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends FrontendController
{
    public function __construct(
        protected TemplateResolver $templateResolver,
    ) {}
    /**
     * Display category archive
     */
    public function show(string $slug, Request $request): View
    {
        $type = $request->get('type');
        $query = Category::where('slug', $slug);
        if ($type) {
            $query->where('type', $type);
        } else {
            $query->orderByRaw("CASE type WHEN 'post' THEN 1 WHEN 'product' THEN 2 ELSE 3 END");
        }
        $category = $query->firstOrFail();
        $isAdmin = $this->isAdmin($request);

        $data = [
            'category' => $category,
        ];

        // Determine content type from category type or request
        $type = $request->get('type', $category->type ?? 'post');

        // All category IDs including descendants (child/sub categories)
        $categoryIds = $category->allCategoryIds();

        if ($type === 'product') {
            // Show products in this category and all its descendant subcategories
            $query = Product::with([
                'categories:categories.id,categories.name,categories.slug',
                'tags:tags.id,tags.name,tags.slug',
                'media' => function ($q) {
                    $q->select(['media.id', 'media.name', 'media.file_name', 'media.disk', 'media.path', 'media.mime_type', 'media.size', 'media.type', 'media.alt_text', 'media.metadata']);
                }
            ])
                ->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });

            if (!$isAdmin) {
                $query->where('status', 'published');
            }

            // Apply filters & sorting
            $query->filterAndSort($request);

            // Paginate
            $perPage = min((int) $request->get('per_page', 12), 50);
            $products = $query->paginate($perPage)->withQueryString();

            $data['products'] = $products;
            $viewName = 'categories.show';
        } else {
            // Show posts in this category and all its descendant subcategories
            $query = Post::with([
                'user:id,name,email',
                'categories:categories.id,categories.name,categories.slug',
                'tags:post_tags.id,post_tags.name,post_tags.slug'
            ])
                ->where('type', 'post')
                ->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });

            if (!$isAdmin) {
                $query->where('status', 'published');
            }

            // Sort
            $sortBy = $request->get('sort_by', 'published_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate
            $perPage = min($request->get('per_page', 12), 50);
            $posts = $query->paginate($perPage)->withQueryString();

            // Inject effective_featured_image: own image or category default fallback
            $categoryDefaultImage = ($category->meta ?? [])['default_featured_image'] ?? null;
            foreach ($posts as $post) {
                $post->setAttribute(
                    'effective_featured_image',
                    !empty($post->featured_image) ? $post->featured_image : $categoryDefaultImage
                );
            }

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
                $childPostsQuery = Post::with(['meta'])
                    ->where('type', 'post')
                    ->whereHas('categories', fn($q) => $q->where('categories.id', $child->id));

                if (!$isAdmin) {
                    $childPostsQuery->where('status', 'published');
                }

                $child->setRelation('groupPosts',
                    $childPostsQuery->orderBy('created_at', 'asc')->get()
                );
            }

            $data['childCategories'] = $childCategories;

            // Also load posts directly in this category (not in any child)
            $directPostsQuery = Post::with(['meta'])
                ->where('type', 'post')
                ->whereHas('categories', fn($q) => $q->where('categories.id', $category->id));

            if (!$isAdmin) {
                $directPostsQuery->where('status', 'published');
            }

            $data['directPosts'] = $directPostsQuery->orderBy('created_at', 'asc')->get();
            
            // Handle loading a specific article directly within the category context
            if (request()->has('article')) {
                $activePostQuery = Post::with(['meta', 'user'])
                    ->where('slug', request('article'))
                    ->where('type', 'post');

                if (!$isAdmin) {
                    $activePostQuery->where('status', 'published');
                }

                $activePost = $activePostQuery->first();
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
        $isAdmin = $this->isAdmin($request);

        $categoryIds = $category->allCategoryIds();

        $query = Product::with(['categories', 'tags'])
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('product_categories.id', $categoryIds);
            });

        if (!$isAdmin) {
            $query->where('status', 'published');
        }

        // Apply filters & sorting (best_sellers, newest, best_rated, trending, price, featured, on_sale)
        $query->filterAndSort($request);

        $perPage = min((int) $request->get('per_page', 12), 50);
        $products = $query->paginate($perPage)->withQueryString();

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