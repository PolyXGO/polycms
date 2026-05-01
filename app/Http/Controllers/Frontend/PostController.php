<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Frontend\FrontendController;
use App\Models\LayoutAsset;
use App\Models\Post;
use App\Services\ContentRenderer;
use App\Services\TemplateResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PostController extends FrontendController
{
    public function __construct(
        protected TemplateResolver $templateResolver,
    ) {}
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

        $viewName = $this->templateResolver->resolve('posts.index', null, 'posts');
        $data['__templateTheme'] = null;
        return view($viewName, $data);
    }

    /**
     * Display a single post
     */
    public function show(string $slug, Request $request): View
    {
        $query = Post::with(['user', 'categories', 'tags'])
            ->where('slug', $slug);

        // Check if user is admin - allow viewing draft posts
        $isAdmin = $this->isAdmin($request);

        if (!$isAdmin) {
            // Non-admin users can only see published posts
            $query->where('status', 'published');
        }
        // Admin users can see all posts (draft, published, archived)

        $post = $query->firstOrFail();

        // Only increment views for published posts or when admin is viewing
        if ($post->status === 'published' || $isAdmin) {
            $post->increment('views');
        }

        $data = [
            'post' => $post,
            'renderedContent' => $this->resolveRenderedContent($post),
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'posts.show');

        $templateTheme = $post->template_theme ?? null;
        $viewName = $this->templateResolver->resolve('posts.show', $templateTheme, 'posts');
        $data['__templateTheme'] = $templateTheme;
        return view($viewName, $data);
    }

    protected function resolveRenderedContent(Post $post): string
    {
        if (($post->layout ?? 'default') !== 'landing') {
            return (string) ($post->content_html ?? '');
        }

        $blocks = $post->content_raw;

        if ((empty($blocks) || !is_array($blocks)) && $post->layout_template_id && $this->canUseLayoutTemplates()) {
            $template = LayoutAsset::query()
                ->templates()
                ->find($post->layout_template_id);
            $blocks = $template?->content_raw;
        }

        if (empty($blocks) || !is_array($blocks)) {
            return (string) ($post->content_html ?? '');
        }

        return app(ContentRenderer::class)
            ->setContext([
                'post' => $post,
            ])
            ->render($blocks);
    }

    protected function canUseLayoutTemplates(): bool
    {
        return Schema::hasTable('layout_assets')
            && Schema::hasTable('posts')
            && Schema::hasColumn('posts', 'layout_template_id');
    }
}
