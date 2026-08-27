<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use App\Services\SettingsService;
use App\Services\TemplateResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends FrontendController
{
    public function __construct(
        protected SettingsService $settingsService,
        protected TemplateResolver $templateResolver,
    ) {}

    /**
     * Display the homepage
     */
    public function index(Request $request): View
    {
        $showOnFront = $this->settingsService->get('reading_show_on_front', 'posts');
        $isAdmin = $this->isAdmin($request);

        if ($showOnFront === 'page') {
            $homepageId = $this->settingsService->get('reading_page_on_front');
            if ($homepageId) {
                $originalPageQuery = Post::withoutGlobalScope('locale')
                    ->where('id', $homepageId)
                    ->where('type', 'page');

                if (!$isAdmin) {
                    $originalPageQuery->where('status', 'published');
                }

                $originalPage = $originalPageQuery->first();

                if ($originalPage) {
                    $page = $originalPage;
                    $currentLocale = app()->getLocale();

                    if ($originalPage->locale !== $currentLocale) {
                        $translatedPage = $originalPage->getTranslation($currentLocale);
                        if ($translatedPage && ($translatedPage->status === 'published' || $isAdmin)) {
                            $page = $translatedPage;
                        }
                    }

                    $renderedContent = '';
                    $blocks = $page->content_raw;
                    if (is_array($blocks) && !empty($blocks)) {
                        $renderedContent = app(\App\Services\ContentRenderer::class)
                            ->setContext([
                                'page' => $page,
                                'post' => $page,
                            ])
                            ->render($blocks);
                    } else {
                        $renderedContent = (string) ($page->content_html ?? '');
                    }

                    $data = [
                        'page' => $page,
                        'is_homepage' => true,
                        'renderedContent' => $renderedContent,
                    ];

                    // Apply theme filter
                    $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'pages.show');

                    return view('pages.show', $data);
                }
            }
        }

        // Default: Get recent posts
        $postsQuery = Post::with([
            'user:id,name,email',
            'categories:categories.id,categories.name,categories.slug,categories.image,categories.meta,categories.depth,categories.parent_id',
            'meta',
        ])
            ->where('type', 'post');

        if (!$isAdmin) {
            $postsQuery->where('status', 'published');
        }

        $posts = $postsQuery
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Get featured products
        $productsQuery = Product::with([
            'categories:categories.id,categories.name,categories.slug,categories.image,categories.meta,categories.depth,categories.parent_id',
            'media' => function ($q) {
                $q->select(['media.id', 'media.name', 'media.file_name', 'media.disk', 'media.path', 'media.mime_type', 'media.size', 'media.type', 'media.alt_text', 'media.metadata']);
            }
        ])
            ->where('featured', true);

        if (!$isAdmin) {
            $productsQuery->where('status', 'published');
        }

        $products = $productsQuery
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Prepare data for view
        $data = [
            'posts' => $posts,
            'products' => $products,
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'home');

        // Resolve home template (supports sub-theme home templates)
        $viewName = $this->templateResolver->resolve('home', null, 'home');
        $data['__templateTheme'] = null;
        if (view()->exists($viewName)) {
            return view($viewName, $data);
        }

        // Fallback: render posts index
        $fallbackView = $this->templateResolver->resolve('posts.index', null, 'posts');
        
        if (view()->exists($fallbackView)) {
            return view($fallbackView, $data);
        }

        return view('system.welcome');
    }
}