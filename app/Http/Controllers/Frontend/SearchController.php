<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Frontend\FrontendController;
use App\Models\Post;
use App\Models\Product;
use App\Services\TemplateResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends FrontendController
{
    public function __construct(
        protected TemplateResolver $templateResolver,
    ) {}

    /**
     * Handle global or targeted frontend search.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', $request->get('q', '')));
        $target = (string) $request->get('target', $request->get('type', 'all'));
        $locale = app()->getLocale();
        $isAdmin = $this->isAdmin($request);

        $posts = collect();
        $products = collect();

        if ($search !== '') {
            $safeSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $likePattern = "%{$safeSearch}%";

            // Search Posts & Pages if target is 'all' or 'posts'
            if (in_array($target, ['all', 'posts', 'post', 'pages', 'page'], true)) {
                $postsQuery = Post::query()
                    ->where('locale', $locale)
                    ->whereIn('type', ['post', 'page'])
                    ->where(function ($q) use ($likePattern) {
                        $q->where('title', 'like', $likePattern)
                            ->orWhere('excerpt', 'like', $likePattern)
                            ->orWhere('content_html', 'like', $likePattern);
                    });

                if (!$isAdmin) {
                    $postsQuery->where('status', 'published');
                }

                $posts = $postsQuery
                    ->latest('published_at')
                    ->paginate(12, ['*'], 'posts_page')
                    ->appends($request->query());
            }

            // Search Products if target is 'all' or 'products'
            if (in_array($target, ['all', 'products', 'product'], true)) {
                $productsQuery = Product::query()
                    ->where('locale', $locale)
                    ->where(function ($q) use ($likePattern) {
                        $q->where('name', 'like', $likePattern)
                            ->orWhere('sku', 'like', $likePattern)
                            ->orWhere('short_description', 'like', $likePattern)
                            ->orWhere('description_html', 'like', $likePattern);
                    });

                if (!$isAdmin) {
                    $productsQuery->where('status', 'published');
                    $productsQuery->where('slug', 'not like', 'test-%');
                }

                $products = $productsQuery
                    ->latest('published_at')
                    ->paginate(12, ['*'], 'products_page')
                    ->appends($request->query());
            }
        }

        $totalResults = (is_object($posts) && method_exists($posts, 'total') ? $posts->total() : count($posts))
            + (is_object($products) && method_exists($products, 'total') ? $products->total() : count($products));

        $data = [
            'search' => $search,
            'target' => $target,
            'posts' => $posts,
            'products' => $products,
            'totalResults' => $totalResults,
        ];

        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'search.index');

        $viewName = $this->templateResolver->resolve('search.index', null, 'search');
        if (!view()->exists($viewName)) {
            $viewName = 'search.index';
        }

        return view($viewName, $data);
    }
}
