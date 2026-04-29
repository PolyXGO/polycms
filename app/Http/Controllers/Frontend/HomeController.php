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

class HomeController extends Controller
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

        if ($showOnFront === 'page') {
            $homepageId = $this->settingsService->get('reading_page_on_front');
            if ($homepageId) {
                $page = Post::where('id', $homepageId)
                    ->where('type', 'page')
                    ->where('status', 'published')
                    ->first();

                if ($page) {
                    $data = [
                        'page' => $page,
                        'is_homepage' => true,
                    ];

                    // Apply theme filter
                    $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'pages.show');

                    return view('pages.show', $data);
                }
            }
        }

        // Default: Get recent posts
        $posts = Post::with(['user', 'categories'])
            ->where('status', 'published')
            ->where('type', 'post')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Get featured products
        $products = Product::with(['categories'])
            ->where('status', 'published')
            ->where('featured', true)
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